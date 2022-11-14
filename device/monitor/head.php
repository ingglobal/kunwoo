<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<link rel="stylesheet" href="<?=G5_DEVICE_URL?>/monitor/css/default.css">
<?php
$reload = $g5['setting']['set_monitor_reload'];//페이지리로딩간격(5000:5초)
?>
</head>
<body>
