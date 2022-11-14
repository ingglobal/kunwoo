<?php
$sub_menu = "990145";
include_once('./_common.php');
auth_check($auth[$sub_menu], 'r');

$g5['title'] = 'MY위젯 '.($w == '' ? '등록' : '수정');
include_once('../../_head.php');
?>


<?php
include_once ('../../_tail.php');