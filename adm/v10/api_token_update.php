<?php
// 모바일용입니다. 피씨는 없습니다.
$sub_menu = "955610";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 토큰 생성
$token = make_token1();
// echo $token.'<br>';

$ar['mta_db_table'] = 'member';
$ar['mta_db_id'] = $member['mb_id'];
$ar['mta_key'] = 'mb_api_token';
$ar['mta_value'] = $token;
meta_update($ar);
unset($ar);


// exit;
// goto_url('./api_guide.php', false);
alert("토큰이 새로 발급되었습니다.",'./api_guide.php');
?>
