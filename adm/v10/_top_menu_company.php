<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./company_list.php" class="btn_top_menu '.$active_order_list.'">업체관리</a>
	<a href="./member_list.php" class="btn_top_menu '.$active_item_list.'">담당자관리</a>
</h2>
';
?>
