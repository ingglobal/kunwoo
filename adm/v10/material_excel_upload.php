<?php
$sub_menu = "945110";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

$demo = 0;  // 데모모드 = 1
//$xls = G5_USER_ADMIN_SQL_PATH.'/xls/material_input.xlsx';

require_once G5_LIB_PATH.'/PhpSpreadsheet/vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\Spreadsheet; 
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
$upload_file_name = $_FILES['file_excel']['tmp_name'];//$xls
// print_r2($_FILES);
$file_type= pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION);
// print_r2($file_type);
if ($file_type =='xls') {
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();	
}
elseif ($file_type =='xlsx') {
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
}
else {
	echo '처리할 수 있는 엑셀 파일이 아닙니다';
	exit;
}

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
$filename = iconv("UTF-8", "EUC-KR", $upload_file_name);
//echo $filename;exit;
$up_date = G5_TIME_YMD;
$conArr = array();
try {
    // echo $filename;
    // 업로드한 PHP 파일을 읽어온다.
	$spreadsheet = $reader->load($filename);	
    $sheetCount = $spreadsheet->getSheetCount();
    if($sheetCount > 1){
        echo '엑셀시트는 단일시트로 작성해 주세요.';
        exit;
    }
    for($i=0;$i<$sheetCount;$i++) {
        $sheet = $spreadsheet->getSheet($i);
        $sheetData = $sheet->toArray(null,true,true,true);
        $allData[$i] = $sheetData;
    }	
} catch(exception $e) {
	echo $e;
}

print_r2($allData[0]);

exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./'.$file_name.'.php?'.$qstr);