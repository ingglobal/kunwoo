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
include_once(G5_LIB_PATH.'/mailer.lib.php');


//-- 디폴트 상태 (실패) --//
$response = new stdClass();
$response->result=false;

// $com_idx, $to_email
if ($aj == "send") {
	
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
			//오늘
			$today2 = date("Y년 m월 d일");
			
			$subject = $g5['setting']['set_data_agree_email_subject'];
			$subject = preg_replace("/{이름}/", $mb['mb_name'], $subject);
			$subject = preg_replace("/{업체명}/", $com['com_name'], $subject);
			$subject = preg_replace("/{회원아이디}/", $com['mb_id'], $subject);
			$subject = preg_replace("/{이메일}/", $to_email, $subject);

			$content = $g5['setting']['set_data_agree_email_content'];
			$content = preg_replace("/{이름}/", $mb['mb_name'], $content);
			$content = preg_replace("/{업체명}/", $com['com_name'], $content);
			$content = preg_replace("/{회원아이디}/", $com['mb_id'], $content);
			$content = preg_replace("/{이메일}/", $to_email, $content);
			$content = preg_replace("/{년월일}/", $today2, $content);
			$content = preg_replace("/{HOME_URL}/", '<a href="'.G5_URL.'">'.G5_URL.'</a>', $content);
			$content = preg_replace("/{DATA_AGREE_URL}/", '<a href="'.G5_USER_URL.'/e1.php?'.$com_idx.'" style="color:white;">자료사용동의하기</a>', $content);
//			$content = $content . "<hr size=0><p><span style='font-size:9pt; font-familye:굴림'>▶ 더 이상 정보 수신을 원치 않으시면 [<a href='".G5_BBS_URL."/email_stop.php?mb_id={$mb_id}&amp;mb_md5={$mb_md5}' target='_blank'>수신거부</a>] 해 주십시오.</span></p>";

			mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $to_email, $subject, $content, 1);

			$response->result = true;
			$response->msg = "이메일을 발송하였습니다. 고객님께 승인을 요청해 주세요.";
		
		}
	}
}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}

$response->sql = $sql;

echo json_encode($response);
exit;
?>