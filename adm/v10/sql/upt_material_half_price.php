<?php
include_once('./_head.sub.php');
//##################################################################
$demo = 1;  // 데모모드 = 1

// ref: https://github.com/PHPOffice/PHPExcel
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel.php"; // PHPExcel.php을 불러옴.
$objPHPExcel = new PHPExcel();
require_once G5_LIB_PATH."/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php"; // IOFactory.php을 불러옴.
$filename = G5_USER_ADMIN_SQL_PATH.'/xls/bom_cost_price.xls';
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
$truncate_sql = " TRUNCATE `g5_0_costprice` ";
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
            // array_push($arr,$rowData[0]);
            $rowData[0][0] = preg_replace("/,/","",trim($rowData[0][0]));
            $rowData[0][5] = trim($rowData[0][5]);
            $rowData[0][7] = preg_replace("/,/","",trim($rowData[0][7]));
            $rowData[0][10] = trim($rowData[0][10]);
            $rowData[0][11] = preg_replace("/,/","",trim($rowData[0][11]));
            $rowData[0][12] = preg_replace("/,/","",trim($rowData[0][12]));
            //------------------------------------------------------------
            $rowData[0][0] = preg_replace("/\\s/"," ",$rowData[0][0]);
            $rowData[0][7] = preg_replace("/\\s/"," ",$rowData[0][7]);
            $rowData[0][10] = preg_replace("/\\s/"," ",$rowData[0][10]);
            $rowData[0][11] = preg_replace("/\\s/"," ",$rowData[0][11]);
            $rowData[0][12] = preg_replace("/\\s/"," ",$rowData[0][12]);
            //------------------------------------------------------------
            $prc_arr = explode(' ',$rowData[0][0]);
            $prc_arr0 = explode(' ',$rowData[0][7]);
            $prc_arr1 = explode(' ',$rowData[0][10]);
            $prc_arr2 = explode(' ',$rowData[0][11]);
            $prc_arr3 = explode(' ',$rowData[0][12]);
            $rowData[0][0] = $prc_arr[0];
            $rowData[0][7] = $prc_arr0[0];
            $rowData[0][10] = $prc_arr1[0];
            $rowData[0][11] = $prc_arr2[0];
            $rowData[0][12] = $prc_arr3[0];
            //------------------------------------------------------------
            if($rowData[0][0] && $parent_cd != $rowData[0][0]){
                $parent_cd = $rowData[0][0];
            }
            else{
                $rowData[0][0] = $parent_cd;
            }
            //------------------------------------------------------------
            if($rowData[0][10] == '진양공업(주)') $rowData[0][10] = '진양공업(주) 신공장';
            else if($rowData[0][10] == '(주)베어링') $rowData[0][10] = '(주)베어링 아트';
            else if($rowData[0][10] == 'SeAH') $rowData[0][10] = 'SeAH 세아베스틸';
            else if($rowData[0][10] == '선테크') $rowData[0][10] = '선테크 영천';
            else if($rowData[0][10] == '신영스틸') $rowData[0][10] = '신영스틸(주)';
            else if($rowData[0][10] == '') $rowData[0][10] = '건우금속';
            //------------------------------------------------------------
            /*
            $rowData[0][10]
            */
            if( 
                $rowData[0][5]
                && !preg_match("/[0-9]/m",$rowData[0][10])
                && $rowData[0][10] != '원자재'
            )
            {
                $sql = " SELECT com_idx FROM {$g5['company_table']} WHERE com_name = '{$rowData[0][10]}' ";
                $res = sql_fetch($sql);

                $insql = " INSERT INTO `g5_0_costprice` SET
                    itm_parent = '{$rowData[0][0]}'              
                    ,com_idx = '{$res['com_idx']}'              
                    ,itm_cd = '{$rowData[0][5]}'              
                    ,itm_unit_weight = '{$rowData[0][7]}'              
                    ,itm_com_name = '{$rowData[0][10]}'              
                    ,itm_buy_price = '{$rowData[0][11]}'              
                    ,itm_cost_price = '{$rowData[0][12]}'              
                ";
                sql_query($insql);

                usleep(10000);
            }
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
// print_r2($nocomArr);
// print_r2($diffData);
// print_r2($contData);

include_once('./_tail.sub.php');