<?php
include_once('./_head.sub.php');
if($test){
    include_once(G5_USER_ADMIN_TEST_PATH.'/'.$test.'.php');
}
else {
    include_once(G5_USER_ADMIN_TEST_PATH.'/test.php');
}
include_once('./_tail.sub.php');
