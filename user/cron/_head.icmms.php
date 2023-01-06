<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// icmms 서버 연결 함수
if(!function_exists('icmms_server_connect')){
function icmms_server_connect()
{
	global $g5,$icmms_connect_db_pdo;

	$icmmsDbHost="116.120.58.58";
	$icmmsDbUser="icmms"; //root
	$icmmsDbPass="icmms@ingglobal"; //super@ingglobal
	$icmmsDbName="icmms_www";
	try {
		$icmms_connect_db_pdo = new PDO('mysql:host='.$icmmsDbHost.';port=3306;dbname='.$icmmsDbName, $icmmsDbUser, $icmmsDbPass);
		$g5['pdo_yn'] = 1;
	}
	catch( PDOException $Exception ) {
		$icmms_connect_db_pdo  = @mysql_connect($icmmsDbHost,$icmmsDbUser,$icmmsDbPass) or die("DB connent Error... Check your database setting");
		mysql_select_db($icmmsDbName,$icmms_connect_db_pdo);
		$g5['pdo_yn'] = 0;
	}

}
}

// icmms 서버 연결 종료
if(!function_exists('icmms_server_close')){
function icmms_server_close()
{
	global $g5,$icmms_connect_db_pdo;

	// 종료
	if($g5['pdo_yn'])
		$icmms_connect_db_pdo = null;
	else
		mysql_close($icmms_connect_db_pdo);

}
}

// DB 연결
icmms_server_connect();


// 관련 변수설정

$tbl_downtime = 'g5_1_data_downtime'; //(com_idx=14, imp_idx=35, mms_idx=67)
$tbl_error = 'g5_1_data_error'; //(com_idx=14, imp_idx=35, mms_idx=67)
$tbl_error_sum = 'g5_1_data_error_sum'; //(com_idx=14, imp_idx=35, mms_idx=67)
?>
