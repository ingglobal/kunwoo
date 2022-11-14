<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

include_once('../_head.php');

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/chart.css">', 2);
add_javascript('<script src="https://code.highcharts.com/highcharts.js"></script>',2);
add_javascript('<script src="https://code.highcharts.com/modules/exporting.js"></script>',2);
add_javascript('<script src="https://code.highcharts.com/modules/export-data.js"></script>',2);
add_javascript('<script src="https://code.highcharts.com/modules/accessibility.js"></script>',2);
add_javascript('<script src="https://cdnjs.cloudflare.com/ajax/libs/highcharts/9.3.2/themes/high-contrast-dark.min.js"></script>',2);
?>