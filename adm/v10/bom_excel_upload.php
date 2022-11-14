<?php
$sub_menu = "915130";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1


// bom 정보 입력
// 엑셀 한 줄에 두개 정보가 있을 수 있음 (일단 함수로 만들어 두고 for 문에서 두번 호출합니다.)
if(!function_exists('func_update_bom')){
function func_update_bom($arr) {
    global $g5,$demo;

    // 거래처 정보 =======================================================
    // 자재인 경우
    if($arr['bom_type']=='material') {

        // 거래처 검색
        $sql = "SELECT com_idx AS com_idx_customer FROM {$g5['company_table']} 
                WHERE com_status NOT IN ('trash','delete')
                    AND com_idx_par = '".$_SESSION['ss_com_idx']."'
                    AND com_name = '".$arr['com_name']."'
        ";
        // print_r3($sql);
        $com = sql_fetch($sql,1);
        // 거래처 정보 없으면 생성
        if(!$com['com_idx_provider']) {
            $sql = "INSERT INTO {$g5['company_table']} SET
                        com_idx_par = '".$_SESSION['ss_com_idx']."'
                        , com_name = '".addslashes($arr['com_name'])."'
                        , com_names = ', ".$arr['com_name']."(".G5_TIME_YMD."~)'
                        , com_level = '2'
                        , com_send_type = 'email'
                        , com_status = 'ok'
                        , com_reg_dt = '".G5_TIME_YMDHIS."'
                        , com_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($sql,1);
            $com['com_idx_provider'] = sql_insert_id();
        }

    }
    // 완제품인 경우, 거래처 com_idx = MY com_idx
    else if($arr['bom_type']=='product') {
        $com['com_idx_provider'] = $_SESSION['ss_com_idx'];
    }
    // 거래처 정보 =======================================================

    //bct_id = '".$arr['bct_id']."',
    $sql_common = " com_idx = '".$_SESSION['ss_com_idx']."',
                    com_idx_provider = '".$com['com_idx_provider']."',
                    bom_name = '".addslashes($arr['bom_name'])."',
                    bom_type = '".$arr['bom_type']."',
                    bom_price = '".$arr['bom_price']."',
                    bom_update_dt = '".G5_TIME_YMDHIS."'
    ";
    
    $sql = "SELECT *
                FROM {$g5['bom_table']}
            WHERE bom_part_no = '".$arr['bom_part_no']."'
    ";
    $bom = sql_fetch($sql,1);
    if(!$bom['bom_idx']) {
        //설비라인은 정해진게 없으면 일단 1번라인으로 설정해 놓자.
        $sql = " INSERT INTO {$g5['bom_table']} SET
                    {$sql_common} 
                    , bom_part_no = '".$arr['bom_part_no']."'
                    , trm_idx_line = '".$g5['line_reverse']['1라인']."'
                    , bom_status = 'ok'
                    , bom_reg_dt = '".G5_TIME_YMDHIS."'
        ";
        if(!$demo) {sql_query($sql,1);}
        $bom['bom_idx'] = sql_insert_id();
    }
    else {
        $sql = "UPDATE {$g5['bom_table']} SET
                    {$sql_common}
                WHERE bom_idx = '".$bom['bom_idx']."'
        ";
        if(!$demo) {sql_query($sql,1);}
    }
    if($demo) {print_r3($sql);}
    // print_r3($sql);


    // 가격 정보 =======================================================
    $sql = "SELECT *
                FROM {$g5['bom_price_table']}
            WHERE bom_idx = '".$bom['bom_idx']."'
                AND bop_start_date = '2021-01-01'
    ";
    $bop = sql_fetch($sql,1);
    if(!$bop['bop_idx']) {
        $sql = " INSERT INTO {$g5['bom_price_table']} SET
                    bom_idx = '".$bom['bom_idx']."'
                    , bop_price = '".$arr['bom_price']."'
                    , bop_start_date = '2021-01-01'
                    , bop_reg_dt = '".G5_TIME_YMDHIS."'
                    , bop_update_dt = '".G5_TIME_YMDHIS."'
        ";
        if(!$demo) {sql_query($sql,1);}
    }
    else {
        $sql = "UPDATE {$g5['bom_price_table']} SET
                    bop_price = '".$arr['bom_price']."'
                    , bop_update_dt = '".G5_TIME_YMDHIS."'
                WHERE bop_idx = '".$bop['bop_idx']."'
        ";
        if(!$demo) {sql_query($sql,1);}
    }
    if($demo) {print_r3($sql);}
    // 가격 정보 =======================================================
 
    return $bom['bom_idx'];
}
}


// 엑셀 자재 삭제 처리 함후
if(!function_exists('func_delete_bom_item')){
function func_delete_bom_item($arr) {
    global $g5,$demo;

    if(!$arr['bom_idx'] || !$arr['bom_child_arr']) return false;

    $sql = "DELETE FROM {$g5['bom_item_table']}
            WHERE bom_idx = '".$arr['bom_idx']."'
                AND bom_idx_child NOT IN (".implode(',',$arr['bom_child_arr']).")
    ";
    sql_query($sql,1);

    return $arr['bom_idx'];
}
}


// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);
try {
    // 업로드한 PHP 파일을 읽어온다.
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();
    
	// 시트Sheet별로 읽기
    $allData = array();
	for($i = 0; $i < $sheetsCount; $i++) {

          $objPHPExcel -> setActiveSheetIndex($i);
          $sheet = $objPHPExcel -> getActiveSheet();
          $highestRow = $sheet -> getHighestRow();          // 마지막 행
          $highestColumn = $sheet -> getHighestColumn();    // 마지막 컬럼
          // 한줄씩 읽기
          for($row = 1; $row <= $highestRow; $row++) {
            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            // $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선안하고 담는다.
            $allData[$i][$row] = $rowData[0];
          }
	}
} catch(exception $e) {
	echo $e;
}
// print_r2($allData);
// exit;




$g5['title'] = '엑셀 업로드';
include_once('./_top_menu_shift.php');
include_once('./_head.php');
echo $g5['container_sub_title'];
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once ('./_tail.php');
?>

<?php
// time1~time2 사이값만 1교대값으로 보고 나머지는 전부 2교대값으로 봄
$time1 = '0800';
$time2 = '1930';


$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();

// print_r3($allData);
$i=0;
for($i=0;$i<=sizeof($allData[0]);$i++) {
    // print_r3($allData[0][$i]);

    // 초기화
    unset($arr);

    
    // 파트 넘버가 존재해야 함
    // 해당 라인만 추출
    if(!preg_match("/[가-힝]/",$allData[0][$i][1])
            && ( preg_match("/[-a-zA-Z]/",$allData[0][$i][2]) || preg_match("/[-a-zA-Z]/",$allData[0][$i][9]) || $allData[0][$i][9]=='라벨' || $allData[0][$i][9]=='타카' ) ) {
        // print_r3($allData[0][$i]);
        // 한줄에 두개 상품이 있는 경우가 있으므로 제품을 배열로 분리
        // 쟈재인 경우 (자재는 항상 존재하므로 배열 0번에 배치시킴)
        if($allData[0][$i][9]) {
            $arr[$i][0]['com_name'] = trim($allData[0][$i][8]);
            $arr[$i][0]['bom_part_no'] = trim($allData[0][$i][9]);
            $arr[$i][0]['bom_part_no_parent'] = trim($allData[0][$i][2]);
            $arr[$i][0]['bom_name'] = trim($allData[0][$i][10]);
            $arr[$i][0]['bom_price'] = trim($allData[0][$i][11]);
            $arr[$i][0]['bit_count'] = trim($allData[0][$i][12]);
            $arr[$i][0]['bom_type'] = 'material';
        }
        // 완제품인 경우, 배열1번
        if($allData[0][$i][2]) {
            $arr[$i][1]['cartype'] = trim($allData[0][$i][1]);
            $arr[$i][1]['bom_part_no'] = trim($allData[0][$i][2]);
            $arr[$i][1]['bom_part_no_parent'] = trim($allData[0][$i][2]);
            $arr[$i][1]['bom_name'] = trim($allData[0][$i][3]);
            $arr[$i][1]['bom_price'] = trim($allData[0][$i][4]);
            $arr[$i][1]['bom_type'] = 'product';
        }

    }
    else
        continue;
    // print_r3($arr[$i]);





    // 완제품 있으면 먼저 생성 (부모 코드가 있어야 함)
    if(is_array($arr[$i][1])) {
        // 엑셀 삭제 처리
        // print_r3($bom_childs[$bom_par]);
        $ar['bom_idx'] = $bom_par;
        $ar['bom_child_arr'] = $bom_childs[$bom_par];
        func_delete_bom_item($ar);
        unset($ar);

        // print_r3('parent');
        $ar = $arr[$i][1];
        $bom_par = func_update_bom($ar);
        unset($ar);

        $idx = -1; // 자재 일련번호(bit_num)
    }

    // print_r3('child');
    $ar = $arr[$i][0];
    $bom_child = func_update_bom($ar);
    unset($ar);

    // print_r3($bom_par);  // 부모 bom_idx
    $bom_childs[$bom_par][] = $bom_child;    

    // // 자재인 경우는 bom_item 테이블을 구성해야 함
    if($arr[$i][0]['bom_type']=='material') {
        // print_r3('자제..');

        // bom_item_table 데이터 입력 =======================
        $sql_common = " bit_count = '".$arr[$i][0]['bit_count']."'
                        , bit_num = '".$idx."'
                        , bit_reply = ''
                        , bit_update_dt = '".G5_TIME_YMDHIS."'
        ";

        // 동일날짜 값이 있으면 업데이트, 아니면 입력
        $sql = "SELECT bit_idx FROM {$g5['bom_item_table']} 
                WHERE bom_idx = '".$bom_par."'
                    AND bom_idx_child = '".$bom_child."'
        ";
        // print_r3($sql);
        // print_r3('--------------');
        $bit = sql_fetch($sql,1);
        if(!$bit['bit_idx']) {
            $sql = "INSERT INTO {$g5['bom_item_table']} SET
                        {$sql_common}
                        , bom_idx = '".$bom_par."'
                        , bom_idx_child = '".$bom_child."'                                    
                        , bit_reg_dt = '".G5_TIME_YMDHIS."'
            ";
            if(!$demo) {
                sql_query($sql,1);
            }
        }
        else {
            $sql = "UPDATE {$g5['bom_item_table']} SET
                        {$sql_common}
                    WHERE bit_idx = '".$bit['bit_idx']."'
            ";
            if(!$demo) {sql_query($sql,1);}
        }
        if($demo) {print_r3($sql);}
        // print_r3($sql);
        // bom_item_table 데이터 입력 =======================

    }
    $idx--; // 자재 일련번호 증가(음수)

    

    // 메시지 보임
    if($arr[$i][0]['bom_part_no']) {
        echo "<script> document.all.cont.innerHTML += '".$i
                .". ".$arr[$i][0]['bom_part_no']."(".$arr[$i][0]['bom_name'].") 가격: ".number_format($arr[$i][0]['bom_price'])
                ." ----------->> 완료 (완제품은 안 보임)<br>'; </script>\n";
    }
                



    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);
    
    // 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if ($i % $countgap == 0)
        echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
    
    // 화면 정리! 부하를 줄임 (화면 싹 지움)
    if ($i % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML = ''; </script>\n";

}
// 엑셀 삭제 처리, for문 전체 돌고 나서 마지막 처리
// print_r3($bom_childs[$bom_par]);
$ar['bom_idx'] = $bom_par;
$ar['bom_child_arr'] = $bom_childs[$bom_par];
func_delete_bom_item($ar);
unset($ar);









// 관리자 디버깅 메시지
if( is_array($g5['debug_msg']) ) {
    for($i=0;$i<sizeof($g5['debug_msg']);$i++) {
        echo '<div class="debug_msg">'.$g5['debug_msg'][$i].'</div>';
    }
?>
    <script>
    $(function(){
        $("#container").prepend( $('.debug_msg') );
    });
    </script>
<?php
}
?>


<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>