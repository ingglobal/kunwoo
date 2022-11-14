<?php
$sub_menu = "945118";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '고객처재고관리';
include_once('./_head.php');
echo $g5['container_sub_title'];



?>





<?php
include_once ('./_tail.php');