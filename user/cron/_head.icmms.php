<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// icmms 서버 연결 함수
if(!function_exists('icmms_server_connect')){
function icmms_server_connect()
{
	global $g5,$icmms_connect_db_pdo;

	// // 기존 디비 연결 해제
	// $link = $g5['connect_db'];
	// if( function_exists('mysqli_query') )
	// 	$result = mysqli_close($link) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
	// else
	// 	$result = mysql_close($link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");

	// $icmmsDbHost="61.83.89.58"; // 공인아이피
	$icmmsDbHost="116.120.58.58";
	$icmmsDbUser="icmms";
	$icmmsDbPass="icmms@ingglobal";
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


	// // 영카트 디비 재연결
    // $connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    // $select_db  = sql_select_db(G5_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');
    // $g5['connect_db'] = $connect_db;
    // sql_set_charset('utf8', $connect_db);

}
}

// DB 연결
icmms_server_connect();


// 관련 설정
$icmms = array();
$icmms['company_table']	= 'company';
$icmms['member_table']	= 'member';
$icmms['send_table']	= 'z_send';
$icmms['manufacture_table']	= 'manufacture';
$icmms['payment_detail_table']	= 'payment_detail';
$icmms['live_web_table']	= 'live_web_info_190716';
$icmms['mes_cast_shot_sub_table']	= 'mes_cast_shot_sub';


// 주야간
$icmms['set_work_shift'] = array(
	"1"=>"주간"
	,"2"=>"야간"
);




?>
