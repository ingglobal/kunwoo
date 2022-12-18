<?php
$sub_menu = "945115";
include_once('./_common.php');

// print_r2($auth);exit;
check_demo();
auth_check($auth[$sub_menu], 'w');

// check_admin_token();

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
/*
$_POST['oop_idx'] => 44
$_POST['bom_part_no'] => 2004340
$_POST['bom_idx'] => 1034
$_POST['bom_name'] => 외륜
$_POST['plus_modify'] => plus / modify
$_POST['from_status'] => stock
$_POST['to_status'] => finish
$_POST['count'] => 20

$_POST['forge_mms_idx']
$_POST['itm_weight']
$_POST['itm_heat']
*/
if($plus_modify == 'plus'){
    $sql = " INSERT INTO {$g5['item_table']}
    (com_idx,mms_idx,bom_idx,oop_idx,bom_part_no,itm_name,itm_weight,itm_heat,itm_status,itm_date,itm_reg_dt,itm_update_dt) VALUES ";
    $vals = " ('{$_SESSION['ss_com_idx']}','{$cut_mms_idx}','{$bom_idx}','{$oop_idx}','{$bom_part_no}','{$bom_name}','{$itm_weight}','{$itm_heat}','{$to_status}','".G5_TIME_YMD."','".G5_TIME_YMDHIS."','".G5_TIME_YMDHIS."') ";
    for($i=0;$i<$count;$i++){
        $sql .= ($i==0)?$vals:','.$vals;
    }
}
else if($plus_modify == 'modify'){
    $condition = " WHERE oop_idx = '{$oop_idx}'
                    AND bom_part_no = '{$bom_part_no}'
                    AND bom_idx = '{$bom_idx}'
                    AND itm_status = '{$from_status}' ";

    //변경할 기존 절단재 재고가 있는지 확인
    $exist = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['item_table']}
            {$condition} ");

    if(!$exist['cnt']) 
        alert('변경할 재고데이터가 없습니다.');

    $mod_cnt = ($exist['cnt'] < $count) ? $exist['cnt'] : $count;

    $sql = " UPDATE {$g5['item_table']} SET itm_status = '{$to_status}'
        {$condition} 
        LIMIT {$mod_cnt}
    ";
}
sql_query($sql,1);

// $qstr .= '&forge_mms_idx='.$forge_mms_idx; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./item_oop_list.php?'.$qstr);