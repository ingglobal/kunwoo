<?php
include_once ('../../../common.php');

//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
while( list($key, $val) = each($_REQUEST) ) {
	${$key} = $_REQUEST[$key];
//	echo $_REQUEST[$key].'<br>';
}
?>