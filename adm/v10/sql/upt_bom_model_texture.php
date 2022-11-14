<?php
include_once('./_head.sub.php');
//##################################################################
$demo = 1;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = G5_USER_ADMIN_SQL_PATH.'/xls/forge_standard.xls';
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
$parent_cd = '';
//거래처 테이블을 텅비우고 초기화 한다.
$truncate_sql = " TRUNCATE `g5_0_model_texture` ";
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
            if($row < 4) continue;
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);
            
            $rowData[0][0] = trim($rowData[0][0]);//절단라인
            $rowData[0][1] = trim($rowData[0][1]);//단조번호
            $rowData[0][2] = trim($rowData[0][2]);//품명
            $rowData[0][3] = trim($rowData[0][3]);//규격
            $rowData[0][4] = trim($rowData[0][4]);//모델
            $rowData[0][5] = trim($rowData[0][5]);//재질
            $rowData[0][6] = trim($rowData[0][6]);//무게
            $rowData[0][9] = trim($rowData[0][9]);//지름
            $rowData[0][10] = trim(preg_replace("/\*/","",$rowData[0][10]));//길이
            $sql = " SELECT bom_idx FROM {$g5['bom_table']} WHERE bom_std = '{$rowData[0][3]}' ";
            $res = sql_fetch($sql);

            // if(!$res['bom_idx']) array_push($noitArr,$rowData[0][3]);
            /*
            if($res['bom_idx']){
                $sql1 = " UPDATE {$g5['bom_table']} SET
                            bom_model = '{$$rowData[0][4]}'
                            ,bom_texture = '{$rowData[0][5]}'
                        WHERE bom_idx = '{$res['bom_idx']}'
                ";
                sql_query($sql1);
            }
            */
            if(!$rowData[0][2]) continue;

            $sql1 = " INSERT INTO `g5_0_model_texture` SET
                bom_idx = '{$res['bom_idx']}'
                ,mtt_cut_line = '{$rowData[0][0]}'
                ,mtt_forge_num = '{$rowData[0][1]}'
                ,mtt_itm_name = '{$rowData[0][2]}'
                ,mtt_itm_std = '{$rowData[0][3]}'
                ,mtt_itm_model = '{$rowData[0][4]}'
                ,mtt_itm_texture = '{$rowData[0][5]}'
                ,mtt_itm_weight = '{$rowData[0][6]}'
                ,mtt_itm_pai = '{$rowData[0][9]}'
                ,mtt_itm_length = '{$rowData[0][10]}'
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