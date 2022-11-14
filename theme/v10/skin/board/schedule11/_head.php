<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

if( $board['gr_id']=='intra')
    include_once(G5_USER_ADMIN_PATH.'/_head.php');
else
    include_once(G5_BBS_PATH.'/_head.php');
?>