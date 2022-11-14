<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
include_once(G5_PATH.'/_head.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>
<!-- 체크박스 공통 라이브러리 -->
<link rel="stylesheet" href="<?=G5_USER_JS_URL?>/Custom-jQuery-Form-Elements/style.css">
<style>
    .form-element {display:inline-block;width:auto;margin-bottom:0px;} /* overwriding */
    .form-element span.checkbox-btn {height: 17px !important;width: 17px !important;margin: 0 6px 0 0 !important;}
</style>


