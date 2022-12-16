<?php
$sub_menu = "945113";
include_once('./_common.php');

check_demo();
auth_check($auth['$sub_menu'], 'w');

check_admin_token();

/*
$_POST['sst'] => orp_start_date
$_POST['sod'] => desc
$_POST['sst2'] => , oop.oop_idx
$_POST['sod2'] => desc
$_POST['sfl'] => mtr_name
$_POST['stx'] => 
$_POST['page'] => 1
$_POST['token'] => 59606c5a9c45a68bfa3184157dae7800
$_POST['oop_idx'] => 44
$_POST['bom_part_no'] => 2004340
$_POST['bom_idx'] => 1034
$_POST['bom_name'] => 외륜
$_POST['plus_modify'] => plus / modify
$_POST['from_status'] => stock
$_POST['to_status'] => finish
$_POST['count'] => 20
*/
// print_r2($_POST);
// echo $is_admin;
// exit;
if(!$oop_idx || !$bom_part_no || !$bom_idx || !$bom_name){alert('생산계획을 선택해 주세요.');}
if($plus_modify == 'modify'){
    if(!$from_status){alert('기존상태값을 선택해 주세요.');}
    if(!$to_status){alert('목표상태값을 선택해 주세요.');}
}
else{
    if(!$to_status){alert('목표상태값을 선택해 주세요.');}
}
if(!$count){alert('갯수를 입력해 주세요.');}



// $qstr .= '&cut_mms_idx='.$cut_mms_idx.'&mtr2_status='.$mtr2_status; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./half_oop_list.php?'.$qstr);