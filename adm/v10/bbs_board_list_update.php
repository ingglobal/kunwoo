<?php
include_once('./_common.php');

$sub_menu = trim($board['bo_1']);
auth_check($auth[$sub_menu],"r");

$count = count($_POST['chk_wr_id']);

if(!$count) {
    alert($_POST['btn_submit'].' 하실 항목을 하나 이상 선택하세요.');
}

if($_POST['btn_submit'] == '선택삭제') {
    include './bbs_delete_all.php';
} else if($_POST['btn_submit'] == '선택복사') {
    $sw = 'copy';
    include './bbs_move.php';
} else if($_POST['btn_submit'] == '선택이동') {
    $sw = 'move';
    include './bbs_move.php';
} else {
    alert('올바른 방법으로 이용해 주세요.');
}