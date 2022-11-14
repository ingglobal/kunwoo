<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// 운영권한이 있는 사람에게만 보임
if($member['mb_manager_yn']) {
    $sub_title_list = '
        <a href="./company_list.php" class="btn_top_menu '.$active_order_list.'">업체관리</a>
    ';
}

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./certify_list.php" class="btn_top_menu '.$active_order_list.'">인증관리</a>
	<a href="'.G5_THEME_URL.'/skin/board/schedule11/list.calendar.php?bo_table=schedule" class="btn_top_menu '.$active_order_product_select.'">일정관리</a>
	'.$sub_title_list.'
</h2>
';
?>
