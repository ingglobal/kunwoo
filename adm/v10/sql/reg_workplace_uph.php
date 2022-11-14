<?php
include_once('./_head.sub.php');
//##################################################################
$demo = 1;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = G5_USER_ADMIN_SQL_PATH.'/xls/production_process_workings_item.xls';
PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

// 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
// $objReader = PHPExcel_IOFactory::createReaderForFile($filename);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $filename);

// 전체 엑셀 데이터를 담을 배을 선언한다.
$allData = array();
$noitArr = array();
$diffArr = array();
$contArr = array();
$comArr = array();
$comidArr = array();
$nocomArr = array();
$place_cd = '';
$place_name = '';
//거래처 테이블을 텅비우고 초기화 한다.
$truncate_sql = " TRUNCATE `g5_0_workingplace_uphtime` ";
sql_query($truncate_sql,1);
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
            
            $rowData[0][0] = trim($rowData[0][0]);//작업장코드
            $rowData[0][1] = trim($rowData[0][1]);//작업장명
            $rowData[0][2] = trim($rowData[0][2]);//품목코드
            $rowData[0][3] = trim($rowData[0][3]);//품목명
            $rowData[0][4] = trim($rowData[0][4]);//규격
            $rowData[0][5] = trim($rowData[0][5]);//단위
            $rowData[0][6] = trim($rowData[0][6]);//공정코드
            $rowData[0][7] = trim($rowData[0][7]);//공정명
            $rowData[0][8] = trim($rowData[0][8]);//표준UPH
            $rowData[0][9] = trim($rowData[0][9]);//준비시간
            $rowData[0][10] = trim($rowData[0][10]);//CycleTime
            $rowData[0][11] = trim($rowData[0][11]);//생산효율
            $rowData[0][12] = trim($rowData[0][12]);//Mhr배분

            $rowData[0][7] = preg_replace("/[0-9]{2}\:/","",$rowData[0][7]);

            if($rowData[0][0] && $parent_cd != $rowData[0][0]){
                $parent_cd = $rowData[0][0];
                $parent_name = $rowData[0][1];
            }
            else{
                $rowData[0][0] = $parent_cd;
                $rowData[0][1] = $parent_name;
            }

            $sql = " SELECT bom_idx FROM {$g5['bom_table']} WHERE bom_part_no = '{$rowData[0][2]}' ";
            $res = sql_fetch($sql);

            if(!$rowData[0][2]) continue;

            $sql1 = " INSERT INTO `g5_0_workingplace_uphtime` SET
                wut_place_cd = '{$rowData[0][0]}'
                ,wut_place_name = '{$rowData[0][1]}'
                ,bom_idx = '{$res['bom_idx']}'
                ,wut_part_no = '{$rowData[0][2]}'
                ,wut_itm_name = '{$rowData[0][3]}'
                ,wut_itm_std= '{$rowData[0][4]}'
                ,wut_unit= '{$rowData[0][5]}'
                ,wut_process_cd= '{$rowData[0][6]}'
                ,wut_process_name= '{$rowData[0][7]}'
                ,wut_std_uph= '{$rowData[0][8]}'
                ,wut_ready_time= '{$rowData[0][9]}'
                ,wut_cycle_time= '{$rowData[0][10]}'
                ,wut_efficiency= '{$rowData[0][11]}'
                ,wut_mhr= '{$rowData[0][12]}'
                
            ";
            sql_query($sql1);


            usleep(10000);
        }
	}
} catch(exception $e) {
    echo $e;
    exit;
}

/*
for($i=0;$i<count($allData);$i++){
    
    usleep(10000);
}
*/
//##################################################################
// echo count($allData)."<br>";
// print_r2($allData);
// print_r2($comArr);
// print_r2($comidArr);
// print_r2($noitArr);
// print_r2($nocomArr);
// print_r2($diffData);
// print_r2($contData);

include_once('./_tail.sub.php');