<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-darkness/jquery-ui.css">
<link rel="stylesheet" href="<?=G5_USER_ADMIN_JS_URL?>/jquery-ui-1.12.1/jquery-ui.structure.min.css">
<link rel="stylesheet" href="<?=G5_USER_ADMIN_JS_URL?>/datetimepicker/jquery.datetimepicker.min.css">
<link rel="stylesheet" href="<?=G5_JS_URL?>/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?=G5_USER_URL?>/css/default.css">
<link rel="stylesheet" href="<?=G5_USER_URL?>/css/worker_plan.css">
<?php if(is_file(G5_USER_PATH.'/modal/css/default_modal.css')) { ?>
<link rel="stylesheet" href="<?=G5_USER_URL?>/modal/css/default_modal.css">
<?php } ?>
<?php if(is_file(G5_USER_PATH.'/modal/css/'.$g5['file_name'].'_modal.css')) { ?>
<link rel="stylesheet" href="<?=G5_USER_URL?>/modal/css/<?=$g5['file_name']?>_modal.css">
<?php } ?>
<script src="<?=G5_JS_URL?>/jquery-1.12.4.min.js"></script>
<script src="<?=G5_JS_URL?>/jquery-migrate-1.4.1.min.js"></script>
<script src="<?=G5_USER_ADMIN_JS_URL?>/jquery-ui-1.12.1/jquery-ui.min.js"></script>
<script src="<?=G5_USER_ADMIN_JS_URL?>/function.date.js"></script>
<title>생산계획(날짜/설비)</title>
<?php
$reload = $g5['setting']['set_monitor_reload'];//페이지리로딩간격(5000:5초)
?>
</head>
<body>
