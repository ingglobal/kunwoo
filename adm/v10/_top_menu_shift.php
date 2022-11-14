<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// 관리자인 경우만
if($member['mb_manager_yn']) {
    $sub_title_term_list = '<a href="./data_list.php" class="btn_top_menu '.$active_item_list.'" style="display:none;">기존데이터</a>';
}

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./shift_list.php" class="btn_top_menu '.$active_shift_list.'">교대및목표설정</a>
	<a href="./offwork_list.php" class="btn_top_menu '.$active_offwork_list.'">공제시간설정</a>
</h2>
';
?>
