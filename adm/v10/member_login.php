<?php
$sub_menu = "950300";
include_once("./_common.php");

if(!$member['mb_manager_yn']) {
    alert('접근이 금지된 페이지입니다.');
}


$mb = get_table_meta('member','mb_id',$mb_id);

// 회원아이디 세션 생성
set_session('ss_mb_id', $mb_id);
// FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));


run_event('member_login_check');

//exit;
goto_url(G5_URL);
?>