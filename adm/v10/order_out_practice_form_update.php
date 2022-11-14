<?php
$sub_menu = "930100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

if(!$bom_idx) alert('제품을 선택해 주세요.');
if(!$orp_start_date) alert('생산시작일을 입력해 주세요.');
if(!$oop_count) alert('지시수량을 입력해 주세요.');
if($oop_memo) $oop_memo = conv_unescape_nl(stripslashes($oop_memo));
//$content = conv_unescape_nl(stripslashes($content));

// print_r2($_POST);
// print_r2($orp_order_no);
// exit;


//동일한 조건의 생산계획이 존재하는지 확인하고 있으면 등록거부
$sql_chk = " SELECT COUNT(*) AS cnt, orp.orp_idx, oop.oop_idx FROM {$g5['order_practice_table']} AS orp
            LEFT JOIN {$g5['order_out_practice_table']} AS oop ON orp.orp_idx = oop.orp_idx
            LEFT JOIN {$g5['order_out_table']} AS oro ON oop.oro_idx = oro.oro_idx
            LEFT JOIN {$g5['order_table']} AS ord ON oop.ord_idx = ord.ord_idx
            LEFT JOIN {$g5['bom_table']} AS bom ON oop.bom_idx = bom.bom_idx
        WHERE bom.bom_idx = '{$bom_idx}'
            AND orp.cut_mms_idx = '{$cut_mms_idx}'
            AND orp.forge_mms_idx = '{$forge_mms_idx}'
            AND orp.orp_start_date = '{$orp_start_date}'
";
$old_data = sql_fetch($sql_chk);
if($old_data['cnt'] && $w == ''){
    alert('동일한 제품과 조건의 생산계획이 이미 존재합니다.\\n생산계획ID:'.$old_data['orp_idx'].' 입니다.\\n해당 ID의 데이터를 수정해 주세요.','./order_out_practice_list.php?sfl=oop.orp_idx&stx='.$old_data['orp_idx']);
}

if($w == ''){
    //먼저 order_practice 데이터부터 등록한다.
    $sql = " INSERT INTO {$g5['order_practice_table']} SET
                com_idx = '{$_SESSION['ss_com_idx']}'
                , orp_order_no = '{$orp_order_no}'
                , trm_idx_operation = ''
                , cut_mms_idx = '{$cut_mms_idx}'
                , cut_mb_id = '{$cut_mb_id}'
                , forge_mms_idx = '{$forge_mms_idx}'
                , forge_mb_id = '{$forge_mb_id}'
                , trm_idx_line = '{$g5['trms']['linemms_trm'][$cut_mms_idx.'_'.$forge_mms_idx]}'
                , shf_idx = '0'
                , orp_start_date = '{$orp_start_date}'
                , orp_done_date = '{$orp_done_date}'
                , orp_memo = ''
                , orp_status = 'ok'
                , orp_reg_dt = '".G5_TIME_YMDHIS."'
                , orp_update_dt = '".G5_TIME_YMDHIS."'
    ";

    sql_query($sql,1);
    $orp_idx = sql_insert_id();

    $sql1 = " INSERT INTO {$g5['order_out_practice_table']} SET
                ord_idx = '{$ord_idx}'
                , ori_idx = '{$ori_idx}'
                , oro_idx = '{$oro_idx}'
                , orp_idx = '{$orp_idx}'
                , bom_idx = '{$bom_idx}'
                , mtr_bom_idx = '{$mtr_bom_idx}'
                , oop_count = '{$oop_count}'
                , oop_memo = '{$oop_memo}'
                , oop_status = '{$oop_status}'
                , oop_reg_dt = '".G5_TIME_YMDHIS."'            
                , oop_update_dt = '".G5_TIME_YMDHIS."'            
                , oop_1 = '{$oop_1}'
                , oop_2 = '{$oop_2}'
    ";
    sql_query($sql1,1);
}
else if($w == 'u'){
    //먼저 order_practice 데이터부터 등록한다.
    $sql = " UPDATE {$g5['order_practice_table']} SET
                com_idx = '{$_SESSION['ss_com_idx']}'
                , trm_idx_operation = ''
                , cut_mms_idx = '{$cut_mms_idx}'
                , cut_mb_id = '{$cut_mb_id}'
                , forge_mms_idx = '{$forge_mms_idx}'
                , forge_mb_id = '{$forge_mb_id}'
                , trm_idx_line = '{$g5['trms']['linemms_trm'][$cut_mms_idx.'_'.$forge_mms_idx]}'
                , shf_idx = '0'
                , orp_start_date = '{$orp_start_date}'
                , orp_done_date = '{$orp_done_date}'
                , orp_memo = ''
                , orp_status = 'ok'
                , orp_update_dt = '".G5_TIME_YMDHIS."'
            WHERE orp_idx = '{$orp_idx}'
    ";

    sql_query($sql,1);

    $sql1 = " UPDATE {$g5['order_out_practice_table']} SET
                ord_idx = '{$ord_idx}'
                , ori_idx = '{$ori_idx}'
                , oro_idx = '{$oro_idx}'
                , orp_idx = '{$orp_idx}'
                , bom_idx = '{$bom_idx}'
                , mtr_bom_idx = '{$mtr_bom_idx}'
                , oop_count = '{$oop_count}'
                , oop_memo = '{$oop_memo}'
                , oop_status = '{$oop_status}'
                , oop_update_dt = '".G5_TIME_YMDHIS."'            
                , oop_1 = '{$oop_1}'
                , oop_2 = '{$oop_2}'
            WHERE orp_idx = '{$orp_idx}'
                AND oop_idx = '{$oop_idx}'
    ";
    sql_query($sql1,1);
}


$qstr .= ($calendar)?'&start_date='.$first_date.'&end_date='.$last_date:'';
$order_out_practice_url = ($calendar) ? './order_out_practice_calendar_list.php?'.$qstr:'./order_out_practice_list.php?'.$qstr;

goto_url($order_out_practice_url, false);
?>