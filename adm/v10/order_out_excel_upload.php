<?php
$sub_menu = "920110";
include_once('./_common.php');

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
	$sheetsName = $objPHPExcel -> getSheetNames();
  print_r2($sheetsName);
  echo '<br>-----------------<br>';
  
	// 시트Sheet별로 읽기
  $allData = array();
	for($i = 0; $i < $sheetsCount; $i++) {

          $objPHPExcel -> setActiveSheetIndex($i);
          // print_r2($objPHPExcel); // value:PHPExcel_Cell:private 이 변수에 뭔가가 담긴다.
          $sheet = $objPHPExcel -> getActiveSheet();
          $highestRow = $sheet -> getHighestRow();          // 마지막 행
          $highestColumn = $sheet -> getHighestColumn();    // 마지막 컬럼
          // 한줄씩 읽기
          for($row = 1; $row <= $highestRow; $row++) {

            // $rowData가 한줄의 데이터를 셀별로 배열처리 된다.
            $rowFormula = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, FALSE, FALSE);
            $rowData = $sheet -> rangeToArray("A" . $row . ":" . $highestColumn . $row, NULL, TRUE, FALSE);

            // $rowData에 들어가는 값은 계속 초기화 되기때문에 값을 담을 새로운 배열을 선언하고 담는다.
            $allFormula[$i][$row] = $rowFormula[0];
            $allData[$i][$row] = $rowData[0];
          }
	}
} catch(exception $e) {
	echo $e;
}

print_r2($allFormula);
echo '<br>-----------------<br>';
print_r2($allData);
exit;

