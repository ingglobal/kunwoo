<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// 최고관리자인 경우만
if($member['mb_level']>=9) {
    // $sub_menu_list = '<a href="./term_list.php'.'" class="btn_top_menu '.$active_term_list.'">분류관리</a>';
}

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	
'.$sub_menu_list.'
</h2>
';
?>
