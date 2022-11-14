<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<style>
ul{list-style:none;padding:0;margin:0;}
#nav{background:#555;padding:0 10px;}
#nav:after{display:block;visibility:hidden;clear:both;content:'';}
#nav li{float:left;padding-right:10px;}
#nav li a{display:block;padding:10px;text-decoration:none;}
#nav li a.focus{background:brown;}
</style>
<ul id="nav">
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/half_output/form.php" class="tnb_sql<?=($g5['dir_name'] == 'half_output')?' focus':''?>">반제품출력</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/half_end/form.php" class="tnb_sql<?=($g5['dir_name'] == 'half_end')?' focus':''?>">반제품종료</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/half_status/form.php" class="tnb_sql<?=($g5['dir_name'] == 'half_status')?' focus':''?>">반제품상태</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/item_output/form.php" class="tnb_sql<?=($g5['dir_name'] == 'item_output')?' focus':''?>">완제품출력</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/item_end/form.php" class="tnb_sql<?=($g5['dir_name'] == 'item_end')?' focus':''?>">완제품종료</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/item_status/form.php" class="tnb_sql<?=($g5['dir_name'] == 'item_status')?' focus':''?>">완제품상태</a></li>
</ul>