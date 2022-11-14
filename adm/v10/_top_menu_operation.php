<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./term_operation_list.php" class="btn_top_menu '.$active_term_operation_list.'">공정정보</a>
	<a href="./term_line_list.php" class="btn_top_menu '.$active_term_line_list.'">라인정보</a>
</h2>
';
?>
