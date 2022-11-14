<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');

set_session('ss_admin_token', '');

$error = admin_referer_check(true);
$error = admin_referer_check2(true);    // <=========================================== 함수변경
if($error)
    die(json_encode(array('error'=>$error, 'url'=>G5_URL)));

$token = get_admin_token();

// 사용자단에서 관리자단 게시판을 쓴다면 get_write_token($bo_table) 함수를 사용해야 함
$referer = trim($_SERVER['HTTP_REFERER']);
$bo_table = parse_url2($referer,"bo_table");
if($bo_table && !preg_match("|\/adm\/|",$referer)) {
    $token = get_write_token($bo_table);
}

die(json_encode(array('error'=>'', 'token'=>$token, 'url'=>'')));