<?php
//echo $sub_page;
include_once($head_page_path);

/*
include_once(G5_USER_ADMIN_MOBILE_PATH."/kpi.".$sub_page.".php");
array('960400', 'KPI 보고서', G5_USER_ADMIN_URL.'/kpi.php', 'kpi'),
array('960410', '생산보고서', G5_USER_ADMIN_URL.'/kpi.php?sub_page=output', 'kpi_output',1),
array('960420', '알람보고서', G5_USER_ADMIN_URL.'/kpi.php?sub_page=alarm', 'kpi_alarm',1),
array('960430', '비가동보고서', G5_USER_ADMIN_URL.'/kpi.php?sub_page=offwork', 'kpi_offwork',1),
array('960440', '예지보고서', G5_USER_ADMIN_URL.'/kpi.php?sub_page=predict', 'kpi_predict',1),
array('960450', '품질보고서', G5_USER_ADMIN_URL.'/kpi.php?sub_page=quality', 'kpi_quality',1),
array('960460', '정비및재고', G5_USER_ADMIN_URL.'/kpi.php?sub_page=maintain', 'kpi_maintain',1),
*/
$sub_output_show = ($sub_page != 'main' && $sub_page != 'output') ? ' style="display:none;"' : '';
$sub_alarm_show = ($sub_page != 'main' && $sub_page != 'alarm') ? ' style="display:none;"' : '';
$sub_offwork_show = ($sub_page != 'main' && $sub_page != 'offwork') ? ' style="display:none;"' : '';
$sub_predict_show = ($sub_page != 'main' && $sub_page != 'predict') ? ' style="display:none;"' : '';
$sub_quality_show = ($sub_page != 'main' && $sub_page != 'quality') ? ' style="display:none;"' : '';
$sub_maintain_show = ($sub_page != 'main' && $sub_page != 'maintain') ? ' style="display:none;"' : '';

echo '<div'.$sub_output_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/kpi.output.php");//생산보고서
echo '</div>'.PHP_EOL;
echo '<div'.$sub_alarm_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/kpi.alarm.php");//알람보고서
echo '</div>'.PHP_EOL;
echo '<div'.$sub_offwork_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/kpi.offwork.php");//설비이상보고서
echo '</div>'.PHP_EOL;
echo '<div'.$sub_predict_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/kpi.predict.php");//예지보고서
echo '</div>'.PHP_EOL;
echo '<div'.$sub_quality_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/kpi.quality.php");//품질(불량)보고서
echo '</div>'.PHP_EOL;
echo '<div'.$sub_maintain_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/kpi.maintain.php");//정비 및 재고
echo '</div>'.PHP_EOL;

//@include_once (G5_USER_ADMIN_PATH.'/mobile/chart/kpi/chart_kpi_'.$g5['file_name'].'.php');