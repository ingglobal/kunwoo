<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// 관리자인 경우만
if($member['mb_manager_yn']) {
	;
    // $sub_title_term_list = '<a href="./data_demo_form.php" class="btn_top_menu '.$active_data_demo_form.'">데모데이터</a>';
    // $sub_title_term_list = '<a href="./data_list.php" class="btn_top_menu '.$active_data_list.'" style="display:none;">기존데이터</a>';
}

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./order_out_practice_list.php" class="btn_top_menu '.$active_order_out_practice_list.'">생산계획</a>
	<a href="./order_out_practice_calendar_list.php" class="btn_top_menu '.$active_order_out_practice_calendar_list.'">생산계획(날짜/설비별)</a>
	'.$sub_title_term_list.'
</h2>
';
?>