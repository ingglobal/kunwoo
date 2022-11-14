<?php
include_once('./_common.php');

//print_r2($_REQUEST);
// 변수 재설정
if($_REQUEST['break_start_time']) {
    $_REQUEST['break_start_time'] = date("H:i:s", strtotime($_REQUEST['break_start_time']));
}
if($_REQUEST['set_policy_content']) {
    $_REQUEST['set_policy_content'] = base64_encode($_REQUEST['set_policy_content']);
}

// REQUEST 변수 재정의
while( list($key, $val) = each($_REQUEST) ) {
    // echo $key.'='.$_REQUEST[$key].'<br>';
	if(substr($key,0,3)=='set') {
    	$a[$key] = $_REQUEST[$key];
    }
}
$bo_7 = addslashes(serialize($a));
unset($a);


// 게시판 변수 수정 (개인 정보가 포함되므로 목록을 제외한 나머지 권한은 무조건 8 이상으로 설정해서 일반인들이 못 보게 해야 됨)
// 필요 시 입력해 주세요.
$sql_board = '  bo_read_level = 8
                , bo_write_level = 8
                , bo_reply_level = 8
                , bo_comment_level = 8
                , bo_link_level = 8
                , bo_upload_level = 8
                , bo_download_level = 8
                , bo_html_level = 8
';

$sql = "UPDATE {$g5['board_table']} SET 
            bo_6_subj = 'bo_6 설정'
            , bo_6 = '".$bo_6."'
            , bo_7_subj = '환경설정 확장(serial)'
            , bo_7 = '".$bo_7."'
            , bo_8_subj = '타입 설정'
            , bo_8 = '".$bo_8."'
            , bo_9_subj = '상태값 설정'
            , bo_9 = '".$bo_9."'
        WHERE bo_table = '".$bo_table."'
";
//echo $sql.'<br>';
sql_query($sql,1);


//exit;
goto_url('./config_form.php?bo_table='.$bo_table, false);
?>