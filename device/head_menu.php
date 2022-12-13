<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<style>
ul{list-style:none;padding:0;margin:0;}
#nav{background:#555;padding:0 10px;}
#nav:after{display:block;visibility:hidden;clear:both;content:'';}
#nav li.nav_adm a{background:blue;}
#nav li{float:left;padding-right:10px;}
#nav li a{display:block;padding:10px;text-decoration:none;}
#nav li a.focus{background:brown;}
</style>
<ul id="nav">
    <li class="nav_li nav_adm"><a href="<?php echo G5_USER_ADMIN_URL ?>" class="">관리자홈</a></li>
    <li class="nav_li nav_home"><a href="<?php echo G5_DEVICE_URL ?>" class="<?=($g5['dir_name'] == 'device')?' focus':''?>">APIs홈</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/half_reg/form.php" class="<?=($g5['dir_name'] == 'half_reg')?' focus':''?>">절단재등록</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/half_plt/form.php" class="<?=($g5['dir_name'] == 'half_plt')?' focus':''?>">절단PLT등록</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/item_reg/form.php" class="<?=($g5['dir_name'] == 'item_reg')?' focus':''?>">단조품등록</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/item_plt/form.php" class="<?=($g5['dir_name'] == 'item_plt')?' focus':''?>">단조품PLT등록</a></li>
    <li class="nav_li"><a href="<?php echo G5_DEVICE_URL ?>/jprod_plt/form.php" class="<?=($g5['dir_name'] == 'jprod_plt')?' focus':''?>">완제품PLT등록</a></li>
</ul>