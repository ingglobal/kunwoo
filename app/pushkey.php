<?php
// 푸시값 입력
// id, pushkey
// http://bogwang.epcs.co.kr/app/pushkey.php?id=jamesjoa&pushkey=abcd12345
// http://localhost/icmms/app/pushkey.php?id=jamesjoa&pushkey=abcd12345
header("Content-Type: text/plain; charset=utf-8");
include_once('./_common.php');
if(isset($_SERVER['HTTP_ORIGIN'])){
	header("Access-Control-Allow-Origin:{$_SERVER['HTTP_ORIGIN']}");
	header("Access-Control-Allow-Credentials:true");
	header("Access-Control-Max-Age:86400"); //cache for 1 day
}

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
	if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
		header("Access-Control-Allow-Methods:GET,POST,OPTIONS");
	if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
		header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	exit(0);
}

//-- 디폴트 상태 (실패) --//
$response = new stdClass();
$list = array();
$list['result']=false;

// print_r2($_REQUEST);

// 끝에 아이디(mb_id)만 추출 
// $uri_array = explode("?",$_SERVER['REQUEST_URI']);
// $mb_id = $uri_array[1];

if(!$id) {
    $list['msg']='아이디 값이 존재하지 않습니다.';
}
else {
	$mb = get_member($id);
	// print_r2($mb);
	if(!$mb['mb_id']) {
		$list['msg']='존재하지 않는 회원입니다.';
	}
	else {
		// 정보 수정
		$sql = " UPDATE {$g5['member_table']} SET mb_6 = '".$pushkey."', mb_7 = '".$version."' WHERE mb_id = '".$id."' ";
		// echo $sql.'<br>';
		sql_query($sql,1);
		$list['mb_id']=$mb['mb_id'];
		$list['mb_name']=$mb['mb_name'];
		$list['result']=true;
		$list['msg']='푸시키값을 입력하였습니다.';
	}
}

echo json_encode( $list );
?>