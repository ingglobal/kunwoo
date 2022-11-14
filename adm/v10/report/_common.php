<?php
define('G5_IS_ADMIN', true);
define('G5_IS_V01', true);
include_once ('../../../common.php');

include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');

//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
foreach( $_REQUEST as $key=>$val ) {
	${$key} = $_REQUEST[$key];
}
?>