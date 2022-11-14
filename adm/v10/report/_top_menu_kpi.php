<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// 관리자인 경우만
if($member['mb_manager_yn']) {
    // $sub_title_super_list = '<a href="./data_demo_form.php" class="btn_top_menu '.$active_item_list.'">데모데이터</a>';
}

${'active_'.$g5['file_name']} = ' btn_top_menu_active';
//print_r3(${'active_'.$g5['file_name']});
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./kpi_output.php" class="btn_top_menu '.$active_kpi_output.'">생산보고서</a>
	<a href="./kpi_alarm.php" class="btn_top_menu '.$active_kpi_alarm.'">알람보고서</a>
	<a href="./kpi_downtime.php" class="btn_top_menu '.$active_kpi_downtime.'" style="display:none;">설비이상보고서(현장입력)</a>
	<a href="./kpi_error.php" class="btn_top_menu '.$active_kpi_error.'">설비이상보고서(설비자동)</a>
	<a href="./kpi_predict.php" class="btn_top_menu '.$active_kpi_predict.'">예지보고서</a>
	<a href="./kpi_defect.php" class="btn_top_menu '.$active_kpi_defect.'" style="display:none;">품질보고서(현장입력)</a>
	<a href="./kpi_quality.php" class="btn_top_menu '.$active_kpi_quality.'">품질보고서(설비자동)</a>
	<a href="./kpi_maintenance.php" class="btn_top_menu '.$active_kpi_maintenance.'">정비보고서</a>
	<a href="./kpi_uph.php" class="btn_top_menu '.$active_kpi_uph.'">UPH보고서</a>
	'.$sub_title_super_list.'
</h2>
';
/*
$g5['container_sub_title'] = '
<h2 id="container_sub_title">
	<a href="./kpi_output.php" class="btn_top_menu '.$active_kpi_output.'">생산보고서</a>
	<a href="./kpi_alarm.php" class="btn_top_menu '.$active_kpi_alarm.'">알람보고서</a>
	<a href="./kpi_downtime.php" class="btn_top_menu '.$active_kpi_downtime.'" style="display:none;">설비이상보고서(현장입력)</a>
	<a href="./kpi_error.php" class="btn_top_menu '.$active_kpi_error.'">설비이상보고서(설비자동)</a>
	<a href="./kpi_predict.php" class="btn_top_menu '.$active_kpi_predict.'">예지보고서</a>
	<a href="./kpi_defect.php" class="btn_top_menu '.$active_kpi_defect.'" style="display:none;">품질보고서(현장입력)</a>
	<a href="./kpi_quality.php" class="btn_top_menu '.$active_kpi_quality.'">품질보고서(설비자동)</a>
	<a href="./kpi_maintenance.php" class="btn_top_menu '.$active_kpi_maintenance.'">정비및재고보고서</a>
	<a href="./kpi_uph.php" class="btn_top_menu '.$active_kpi_uph.'">UPH보고서</a>
	'.$sub_title_super_list.'
</h2>
';
*/
?>
