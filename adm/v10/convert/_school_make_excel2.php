<?
// 공통파일 추가
include_once("./_common.php");


//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
while( list($key, $val) = each($_REQUEST) ) {
	${$key} = $_REQUEST[$key];
}

//-- 엑셀 변환 파일 --//
require_once "./reader.php";
$data = new Spreadsheet_Excel_Reader();

// 여기 이부분에서 euc-kr 을 넣어 주면 한글을 이용할 수 있다.
//$data->setOutputEncoding('euc-kr');
//$data->read('./excel/m.xls');
//$data->read('./excel/e1.xls');
//$data->read('./excel/e2.xls');
//$data->read('./excel/b.xls');
$data->read('./excel/h.xls');
error_reporting(E_ALL ^ E_NOTICE);

include_once("../head.sub.php");

for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
	// 데모 테스트용은 6개만 보여주세요.
	if($i>6)
		break;
//	echo "<br><br>";

	for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
		//echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
		//echo iconv('euc-kr','utf-8',$data->sheets[0]['cells'][$i][$j])." / ";
		$item[$i][$j] = iconv('euc-kr','utf-8',$data->sheets[0]['cells'][$i][$j]);
		echo $data->sheets[0]['cells'][$i][$j].' / '.$item[$i][$j].'<br>';
	}

	//-- 첫번째 항목이 숫자인 경우만, 제대로 된 항목이라고 본다. --//
	if(!is_numeric( $item[$i][1] ))
		continue;
	else {
		// 중학교, 특수학교, 초등학교 엑셀 처리
//		$sql = " INSERT INTO d1_school_tmp SET
//						shl_type = '".$item[$i][2]."'
//						,shl_sido_cd = '".$item[$i][3]."'
//						,shl_gugun_cd = '".$item[$i][5]."'
//						,shl_office_cd = '".$item[$i][4]."'
//						,shl_name = '".trim($item[$i][6])."'
//						,shl_bonbun = '".trim($item[$i][7])."'
//						,shl_style = '".trim($item[$i][8])."'
//						,shl_kukkongsa = '".trim($item[$i][9])."'
//						,shl_zip = '".trim($item[$i][10])."'
//						,shl_addr = '".addslashes(trim($item[$i][11]))."'
//						,shl_tel = '".trim($item[$i][12])."'
//						,shl_fax = '".trim($item[$i][13])."'
//						,shl_homepage = '".trim($item[$i][14])."'
//						,shl_status = 'ok'
//						,shl_reg_dt = now()
//					";
//		sql_query($sql);
		
		// 고등학교인 경우 엑셀 처리
		$sql = " INSERT INTO d1_school_tmp SET
						shl_type = '".$item[$i][3]."'
						,shl_sido_cd = '".$item[$i][4]."'
						,shl_gugun_cd = '".$item[$i][6]."'
						,shl_office_cd = '".$item[$i][5]."'
						,shl_name = '".trim($item[$i][7])."'
						,shl_bonbun = '".trim($item[$i][8])."'
						,shl_style = '".trim($item[$i][9])."'
						,shl_kukkongsa = '".trim($item[$i][10])."'
						,shl_zip = '".trim($item[$i][11])."'
						,shl_addr = '".addslashes(trim($item[$i][12]))."'
						,shl_tel = '".trim($item[$i][13])."'
						,shl_fax = '".trim($item[$i][14])."'
						,shl_homepage = '".trim($item[$i][15])."'
						,shl_status = 'ok'
						,shl_reg_dt = now()
					";
		sql_query($sql);
		
		echo $sql.'<br><br>';
	}
}

include_once("../tail.sub.php");
?>