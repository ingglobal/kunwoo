<?php
include_once('./_head.sub.php');
//##################################################################
$demo = 1;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = G5_USER_ADMIN_SQL_PATH.'/xls/material_code.xls';
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
// $objReader = PHPExcel_IOFactory::createReaderForFile($filename);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

// 전체 엑셀 데이터를 담을 배을 선언한다.
$allData = array();
$headArr = array();
$contArr = array();

try {
    // 업로드한 PHP 파일을 읽어온다.
	$objPHPExcel = PHPExcel_IOFactory::load($filename);
	$sheetsCount = $objPHPExcel -> getSheetCount();

	// 시트Sheet별로 읽기
	for($i = 0; $i < $sheetsCount; $i++) {       
        $objPHPExcel -> setActiveSheetIndex($i);
        $sheet = $objPHPExcel -> getActiveSheet();
        $highestRow = $sheet -> getHighestRow();   			           // 마지막 행
        $highestColumn = $sheet -> getHighestColumn();	// 마지막 컬럼
        // $arr = array();
        for($row = 1; $row <= $highestRow; $row++) {
            if($row < 2) continue;
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            // array_push($arr,$rowData[0]);
            $rowData[0][0] = trim($rowData[0][0]);
            $rowData[0][1] = trim($rowData[0][1]);
            $rowData[0][2] = trim($rowData[0][2]);
            $rowData[0][3] = trim($rowData[0][3]);
            $arr = array(
                'mtr_cd' => $rowData[0][0]
                ,'mtr_name' => $rowData[0][1]
                ,'mtr_no_std' => $rowData[0][2]
                ,'mtr_cd2' => $rowData[0][3]
            );
            array_push($allData,$arr);
        }
	}
} catch(exception $e) {
    echo $e;
    exit;
}


//##################################################################
// print_r2($allData);
?>
<div class="btn_box">
    <a href="<?=G5_USER_ADMIN_SQL_URL?>/add_material_direct.php?start=1" class="btn btn_02 btn_start">[시작]</a>
    <p>
        작업시작~~ <font color="crimson"><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
    </p>
</div>
<div id="cont"></div>
<?php
if($start == 1 && count($allData)) { //######################## 작업시작 ################
$countgap = 10; //몇건씩 보낼지 설정
$sleepsec = 10000; //백만분에 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
$maxscreen = 30; // 몇건씩 화면에 보여줄건지 설정

//거래처 테이블을 텅비우고 초기화 한다.
$truncate_sql = " TRUNCATE `g5_0_material` ";
sql_query($truncate_sql,1);

flush();
ob_flush();

$tcnt = 0;
for($i=0;$i<count($allData);$i++) {
    
    $sql = " INSERT INTO `g5_0_material` SET
          mtr_cd = '{$allData[$i]['mtr_cd']}'     
          , mtr_name = '{$allData[$i]['mtr_name']}'      
          , mtr_no_std = '{$allData[$i]['mtr_no_std']}'      
          , mtr_cd2 = '{$allData[$i]['mtr_cd2']}'      
    ";
    sql_query($sql,1);

    $tcnt++;
    echo "<script>document.getElementById('cont').innerHTML += '".$tcnt."개 - ".$allData[$i]['cst_name']." - 처리됨<br>';</script>\n";

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);

    //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if($tcnt % $countgap == 0){
        echo "<script>document.getElementById('cont').innerHTML += '<br>';</script>\n";
    }

    //화면 정리! 부하를 줄임 (화면을 싹 지움)
    if($tcnt % $maxscreen == 0){
        echo "<script>document.getElementById('cont').innerHTML = '';</script>\n";
    }
} //for($i=0;$i<count($allData);$i++)
?>
<script>
document.getElementById('cont').innerHTML += "<br><br>총 <?php echo number_format($tcnt); ?>건 완료<br><br><font color='crimson'><b>[끝]</b></font>";
</script>
<?php
}//if($start == 1 && count($allData)) //############################ 작업종료 ##############
else{
    echo '<div class="display_empty">[시작]버튼을 누르면 작업이 실행됩니다.</div>';
}

include_once('./_tail.sub.php');