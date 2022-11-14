<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// 관리자인 경우만
if($member['mb_manager_yn']) {
    $sub_title_term_list = '<a href="./data_demo_form.php" class="btn_top_menu '.$active_data_demo_form.'">데모데이터</a>';
    $sub_title_term_list = '<a href="./data_list.php" class="btn_top_menu '.$active_data_list.'" style="display:none;">기존데이터</a>';
}

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
/*
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./data_item_sum_list.php" class="btn_top_menu '.$active_data_item_sum_list.'">일별생산합계</a>
	<a href="./data_item_list.php" class="btn_top_menu '.$active_data_item_list.'">상세생산데이터</a>
	<a href="./data_error_sum_list.php" class="btn_top_menu '.$active_data_error_sum_list.'">일별에러합계</a>
	<a href="./data_error_list.php" class="btn_top_menu '.$active_data_error_list.'">상세에러데이터</a>
	<a href="./data_run_sum_list.php" class="btn_top_menu '.$active_data_run_sum_list.'">일별가동데이터합계</a>
	<a href="./data_run_list.php" class="btn_top_menu '.$active_data_run_list.'">상세가동데이터</a>
	<a href="./data_measure_sum_list.php" class="btn_top_menu '.$active_data_measure_sum_list.'">일별측정합계</a>
	<a href="./data_measure_list.php" class="btn_top_menu '.$active_data_measure_list.'">상세측정데이터</a>
	'.$sub_title_term_list.'
</h2>
';
*/
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./data_item_sum_list.php" class="btn_top_menu '.$active_data_item_sum_list.'">일별생산합계</a>
	<a href="./data_item_list.php" class="btn_top_menu '.$active_data_item_list.'">상세생산데이터</a>
	<a href="./data_error_sum_list.php" class="btn_top_menu '.$active_data_error_sum_list.'">일별에러합계</a>
	<a href="./data_error_list.php" class="btn_top_menu '.$active_data_error_list.'">상세에러데이터</a>
	<a href="./data_run_sum_list.php" class="btn_top_menu '.$active_data_run_sum_list.'">일별가동데이터합계</a>
	<a href="./data_run_list.php" class="btn_top_menu '.$active_data_run_list.'">상세가동데이터</a>
	<a href="./data_measure_sum_list.php" class="btn_top_menu '.$active_data_measure_sum_list.'">일별측정합계</a>
	'.$sub_title_term_list.'
</h2>
';
?>
