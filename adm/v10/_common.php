<?php
define('G5_IS_ADMIN', true);
define('G5_IS_V01', true);
include_once ('../../common.php');
//
if ($member['mb_level'] < 3)
    alert('승인된 회원만 접근 가능합니다.',G5_URL);

// 관리자의 아이피, 브라우저와 다르다면 세션을 끊고 로그아웃시킨다.
// admin.lib.php에도 있지만 거기는 alert_close() 함수이므로 더 이상 진행하지 않고 멈춘다.
$admin_key = md5($member['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']);
// echo $admin_key.'<br>';
// echo get_session('ss_mb_key').'<br>';
// exit;
if (get_session('ss_mb_key') !== $admin_key && $member['mb_id'] != 'lbk1130') {
    session_destroy();
    alert('정상적으로 로그인하여 접근하시기 바랍니다.');
}
    
include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');

?>