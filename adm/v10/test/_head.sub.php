<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.sub.php');
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_TEST_URL.'/css/test.css">', 0);
add_javascript('<script src="'.G5_USER_ADMIN_TEST_URL.'/js/test.js"></script>', 0);
?>
<div id="test_head">
    <a class="<?=(($g5['file_name'] == 'index')?'focus':'')?>" href="<?=G5_USER_ADMIN_TEST_URL?>">TEST_HOME</a>
    <a class="" href="<?=G5_USER_ADMIN_URL?>">ADM_HOME</a>
</div>
<div id="test_container">