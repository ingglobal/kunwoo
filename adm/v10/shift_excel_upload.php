<?php
$sub_menu = "950130";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}
if( $_SESSION['ss_com_idx']!=1 ) {
    alert('업체 정보가 잘못되었습니다.');
}

$demo = 0;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = $_FILES['file_excel']['tmp_name'];
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);


// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
$objReader = PHPExcel_IOFactory::createReaderForFile($filename);

// 읽기전용으로 설정
//$objReader->setReadDataOnly(true);

// 엑셀파일을 읽는다
$objExcel = $objReader->load($filename);

// 첫번째 시트를 선택
$objExcel->setActiveSheetIndex(0);

$objWorksheet = $objExcel->getActiveSheet();

$rowIterator = $objWorksheet->getRowIterator();
foreach ($rowIterator as $row) { // 모든 행에 대해서
	$cellIterator = $row->getCellIterator();
	$cellIterator->setIterateOnlyExistingCells(false); 
}
$maxRow = $objWorksheet->getHighestRow();
$maxColumn = $objWorksheet->getHighestDataColumn();
//echo $maxRow.'<br>';
//echo $maxColumn.'<br>';


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

$eidx = 0;  // 엑셀 카운터

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();

$arr = array();
for($i = 2 ; $i <= $maxRow ; $i++) {
    $cnt++;

    // from A to Z
    //'선택','라인','표기품번'....
    for($j=65;$j<=90;$j++) {
        // echo chr($j);
        if($objWorksheet->getCell(chr($j).$i)->getValue()!='') {
            ${'data'.chr($j)}[$i] = $objWorksheet->getCell(chr($j).$i)->getValue();
            ${'data'.chr($j)}[$i] = trim(${'data'.chr($j)}[$i]);
            // print_r3(chr($j).': '.${'data'.chr($j)}[$i]);
        }
    }
    
    // 변수 생성
    // $dataA[$i] = ($dataA[$i]) ? $dataA[$i] : $item['매출기준일'][$i];

    if( is_numeric($dataA[$i]) ) {
        $date[$i] = substr($dataM[$i],0,6);

        // 0800-0840 devides by - unit. 0800 is start time.
        $time_arr[$i] = explode("-",$dataJ[$i]);
        // 1shift
        if($time_arr[$i][0] >= $time1 && $time_arr[$i][0] < $time2) {
            $arr[$dataB[$i]][$date[$i]][0] += $dataH[$i];
        }
        // 2shift
        else {
            $arr[$dataB[$i]][$date[$i]][1] += $dataH[$i];
        }

        // // 메시지 보임
        // echo "<script> document.all.cont.innerHTML += '".$cnt
        //         .". ".$dataB[$i].", 지시수량:".$dataH[$i].", 작업시간:".$dataJ[$i].", 지시번호(날짜):".$dataM[$i]
        //         ." >> 엑셀추출완료<br>'; </script>\n";
        
        // flush();
        // ob_flush();
        // ob_end_flush();
        // usleep($sleepsec);
        
        // // 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
        // if ($cnt % $countgap == 0)
        //     echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
        
        // // 화면 정리! 부하를 줄임 (화면 싹 지움)
        // if ($cnt % $maxscreen == 0)
        //     echo "<script> document.all.cont.innerHTML = ''; </script>\n";
        
    }

}

// print_r3($arr);
$i=0;
foreach($arr as $k1 => $v1) {
    $i++;
    // print_r3($k1);
    // print_r3($v1);
    $line[$i] = $k1; // something like PR-F, PR-G
    foreach($v1 as $k2 => $v2) {
        // print_r3($k2);
        // print_r3($v2);
        $date[$i] = $k2;        // date like 201201
        // $date1[$i] = '20'.substr($k2,0,2).'-'.substr($k2,2,2).'-'.substr($k2,4,2);        // date like 201201
        $date1[$i] = date("Y-m-d", strtotime('20'.$date[$i])); // 적용시작일
        $date2[$i] = date("Y-m-d", strtotime($date1[$i])+86400); // 적용종료는 다음날
        $output1[$i] = $v2[0];  // output for 1shift
        $output2[$i] = $v2[1];  // output for 2shift
    }
    // print_r3('line: '.$line[$i]);
    // print_r3('date: '.$date[$i]);
    // print_r3('1shift output: '.$output1[$i]);
    // print_r3('2shift output: '.$output2[$i]);

    // 라인코드와 일치하는 설비가 있는지 파악해서 1/n 으로 수량 설정해서 체크
    $sql = "SELECT COUNT(mms_idx) AS mms_cnt
                , GROUP_CONCAT(mms_idx) AS mms_idxs
            FROM {$g5['mms_table']}
            WHERE mms_status NOT IN ('trash','delete')
                AND mms_linecode = '".$line[$i]."'
            GROUP BY mms_linecode
    ";
    // print_r3($sql);
    $mms = sql_fetch($sql,1);
    // print_r3($mms['mms_cnt']);
    // if related 설비s are existed.
    if($mms['mms_cnt']>0) {
        $output1_devided[$i] = $output1[$i]/$mms['mms_cnt'];
        $output2_devided[$i] = $output2[$i]/$mms['mms_cnt'];
        // print_r3($line[$i].' 1교대에서 '.$output1[$i].' devided by '.$mms['mms_cnt'].' = '.$output1_devided[$i]);
        // print_r3($line[$i].' 2교대에서 '.$output2[$i].' devided by '.$mms['mms_cnt'].' = '.$output2_devided[$i]);

        $mmses = explode(",",$mms['mms_idxs']);
        for($j=0;$j<sizeof($mmses);$j++) {
            // print_r3($mmses[$j]);

            // 전체기간 설정에 같은 목표수량이 있으면 입력없이 통과
            $sql = "SELECT shf_idx FROM {$g5['shift_table']} 
                    WHERE shf_status NOT IN ('trash','delete')
                        AND mms_idx = '".$mmses[$j]."'
                        AND shf_period_type = 1
                        AND shf_target_1 = '".$output1_devided[$i]."'
                        AND shf_target_2 = '".$output2_devided[$i]."'
            ";
            // print_r3($sql);
            $shf1 = sql_fetch($sql,1);
            if(!$shf1['shf_idx']) {

                $sql_common = " shf_target_1 = '".$output1_devided[$i]."'
                                , shf_target_2 = '".$output2_devided[$i]."'
                ";

                // 동일날짜 값이 있으면 업데이트, 아니면 입력
                $sql = "SELECT shf_idx FROM {$g5['shift_table']} 
                        WHERE shf_status NOT IN ('trash','delete')
                            AND mms_idx = '".$mmses[$j]."'
                            AND shf_period_type = 0
                            AND SUBSTRING(shf_start_dt,1,10) = '".$date1[$i]."'
                            AND SUBSTRING(shf_end_dt,1,10) = '".$date2[$i]."'
                ";
                // print_r3($sql);
                $shf2 = sql_fetch($sql,1);
                if(!$shf2['shf_idx']) {
                    $sql = "INSERT INTO {$g5['shift_table']} SET
                                com_idx = '1'
                                , mms_idx = '".$mmses[$j]."'
                                , shf_range_1 = '07:50:00~20:10:00'
                                , shf_range_2 = '20:20:00~31:49:59'
                                , shf_period_type = 0
                                , shf_start_dt = '".$date1[$i]." 07:50:00'
                                , shf_end_dt = '".$date2[$i]." 07:40:00'
                                , shf_status = 'ok'
                                , shf_reg_dt = '".G5_TIME_YMDHIS."'
                                , shf_update_dt = '".G5_TIME_YMDHIS."',
                                {$sql_common}
                    ";
                    if(!$demo) {sql_query($sql,1);}
                }
                else {
                    $sql = "UPDATE {$g5['shift_table']} SET
                                {$sql_common}
                            WHERE shf_idx = '".$shf2['shf_idx']."'
                    ";
                    if(!$demo) {sql_query($sql,1);}
                }
                if($demo) {print_r3($sql);}
            }

        }
        $result_msg = '처리완료';
    }
    else {
        $result_msg = '관련설비 없음';
    }

    // 메시지 보임
    echo "<script> document.all.cont.innerHTML += '".$i
            .". ".$line[$i]."(".$date[$i].") 1교대: ".number_format($output1[$i]).", 2교대: ".number_format($output2[$i])
            ." ----------->> ".$result_msg."<br>'; </script>\n";
    
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