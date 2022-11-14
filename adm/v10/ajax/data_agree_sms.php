<?php
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
$response->result=false;

// $com_idx, $to_number
if ($aj == "send") {

	$to_number = hyphen_hp_number($to_number);
	$from_number = hyphen_hp_number($g5['setting']['set_sms_callback']);
	
	$com = sql_fetch(" select * from {$g5['company_table']} where com_idx = '$com_idx' ");
	if (!$com['com_idx']) {
		$response->err_code='E01';
		$response->msg = "존재하지 않는 업체자료입니다.";
	}
	else {

		$mb = get_member($com['mb_id']);
		if (!$mb['mb_id']) {
			$response->err_code='E01';
			$response->msg = "존재하지 않는 회원자료입니다.";
		}
		else {

			$content = $g5['setting']['set_data_agree_sms_content'];
			$content = preg_replace("/{이름}/", $mb['mb_name'], $content);
			$content = preg_replace("/{업체명}/", $com['com_name'], $content);
			$content = preg_replace("/{회원아이디}/", $com['mb_id'], $content);
			$content = preg_replace("/{HOME_URL}/", '<a href="'.G5_URL.'">'.G5_URL.'</a>', $content);
			$content = preg_replace("/{DATA_AGREE_URL}/", G5_USER_URL.'/e1.php?'.$com_idx, $content);

			// 엔씨티 서버를 이용한 SMS 문자 발송 
			sms_nct(array("to_number"=>$to_number
							,"from_number"=>$from_number
							,"content"=>$content
			));
			
			$response->result = true;
			$response->msg = "메시지를 발송하였습니다. 고객님께 승인을 요청해 주세요.";
		
		}
	}
}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}
//mysql_close($connect_db_sms);


$response->sql = $sql;
$response->content = $content;

echo json_encode($response);
exit;
?>