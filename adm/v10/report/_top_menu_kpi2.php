<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="'.G5_USER_ADMIN_URL.'/report/kpi_cost.php" class="btn_top_menu '.$active_kpi_cost.'">생산원가보고서</a>
	<a href="'.G5_USER_ADMIN_URL.'/report/kpi_uph2.php" class="btn_top_menu '.$active_kpi_uph2.'">UPH보고서</a>
	<a href="'.G5_USER_ADMIN_URL.'/cost_config_list.php" class="btn_top_menu '.$active_cost_config_list.'">원가설정</a>
</h2>
';
?>
