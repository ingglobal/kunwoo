<?php
//echo $sub_page;
include_once($head_page_path);
/*
include_once(G5_USER_ADMIN_MOBILE_PATH."/merp.".$sub_page.".php");
array('960500', 'M-ERP 보고서', G5_USER_ADMIN_URL.'/merp.php', 'merp'),
array('960510', '매출보고서', G5_USER_ADMIN_URL.'/merp.php?sub_page=output', 'merp_output',1),
array('960520', '정비및재고', G5_USER_ADMIN_URL.'/merp.php?sub_page=maintain', 'merp_maintain',1),
*/
$sub_output_show = ($sub_page != 'main' && $sub_page != 'output') ? ' style="display:none;"' : '';
$sub_maintain_show = ($sub_page != 'main' && $sub_page != 'maintain') ? ' style="display:none;"' : '';

echo '<div'.$sub_output_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/merp.output.php");
echo '</div>'.PHP_EOL;
echo '<div'.$sub_maintain_show.'>'.PHP_EOL;
include_once(G5_USER_ADMIN_MOBILE_PATH."/merp.maintain.php");
echo '</div>'.PHP_EOL;