<?php
$sub_menu = "920110";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

check_admin_token();
// print_r2($_POST);exit;
$com_idx = $_POST['com_idx'];
$chk_arr = $_POST['chk'];

$oro_date_plan_arr = $_POST['oro_date_plan'];
$oro_date_arr = $_POST['oro_date'];
$com_idx_shipto_arr = $_POST['com_idx_shipto'];
$oro_status_arr = $_POST['oro_status'];
$oro_1_arr = $_POST['oro_1'];
$oro_2_arr = $_POST['oro_2'];

$ord_idx_arr = $_POST['ord_idx'];
$ori_idx_arr = $_POST['ori_idx'];
$oro_idx_arr = $_POST['oro_idx'];
$oro_count_arr = $_POST['oro_count'];
$bom_idx_arr = $_POST['bom_idx'];

//$g5['order_practice_table']
//$g5['order_out_practice_table']
$cnt = 1;
foreach($chk_arr as $oro_idx_v){
    //삭제,취소 등의 상태값이 아닌 생산실행 레코드가 있으면 중복 레코드를 생성하면 안된다.
    $chk_sql = " SELECT COUNT(*) AS cnt FROM {$g5['order_practice_table']} AS orp 
                    LEFT JOIN {$g5['order_out_practice_table']} AS oop ON orp.orp_idx = oop.orp_idx
                        WHERE oop.oro_idx = '{$oro_idx_v}' AND orp.orp_status NOT IN('trash','del','delete','cancel') ";
    $chk_result = sql_fetch($chk_sql);
    //기존 생산실행 레코드가 있으면 다음루프로 넘어간다.
    if($chk_result['cnt'])
        continue;

    //ori_idx에 해당하는 bom_idx를 조회
    $bom_idx_sql = sql_fetch(" SELECT bom_idx FROM {$g5['order_item_table']} WHERE ori_idx = '".$ori_idx_arr[$oro_idx_v]."' ");
    $bom_idx = $bom_idx_sql['bom_idx'];
    $zero_cnt = sprintf("%03d",$cnt);
    $tdcode = preg_replace('/[ :-]*/','',G5_TIME_YMDHIS);
    $orp_order_no = "ORP-".$tdcode.$zero_cnt;
    //orp테이블에 등록
    $sql1 = " INSERT {$g5['order_practice_table']} SET
                com_idx = '".$com_idx."',
                orp_order_no = '".$orp_order_no."',
                trm_idx_operation = '',
                trm_idx_line = '',
                shf_idx = '',
                mb_id = '',
                orp_start_date = '0000-00-00',
                orp_done_date = '0000-00-00',
                orp_memo = '',
                orp_status = 'confirm',
                orp_reg_dt = '".G5_TIME_YMDHIS."',
                orp_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql1,1);
    $orp_idx = sql_insert_id();

    //천단위 제거 
    $oro_count_arr[$oro_idx_v] = preg_replace("/,/","",$oro_count_arr[$oro_idx_v]);
    //oop테이블에 등록
    $sql2 = " INSERT {$g5['order_out_practice_table']} SET
                ord_idx = '".$ord_idx_arr[$oro_idx_v]."',
                ori_idx = '".$ori_idx_arr[$oro_idx_v]."',
                oro_idx = '".$oro_idx_arr[$oro_idx_v]."',
                orp_idx = '".$orp_idx."',
                bom_idx = '".$bom_idx_arr[$oro_idx_v]."',
                oop_count = '".$oro_count_arr[$oro_idx_v]."',
                oop_history = '',
                oop_status = 'pending',
                oop_reg_dt = '".G5_TIME_YMDHIS."',
                oop_update_dt = '".G5_TIME_YMDHIS."',
                oop_1 = '".$oro_count_arr[$oro_idx_v]."'
    ";
    sql_query($sql2,1);

    $sql3 = " UPDATE {$g5['order_out_table']} SET
                oro_count = '".sql_real_escape_string($oro_count_arr[$oro_idx_v])."',
                oro_date_plan = '".$oro_date_plan_arr[$oro_idx_v]."',
                oro_date = '".$oro_date_arr[$oro_idx_v]."',
                com_idx_shipto = '".$com_idx_shipto_arr[$oro_idx_v]."',
                oro_status = '".$oro_status_arr[$oro_idx_v]."',
                oro_update_dt = '".G5_TIME_YMDHIS."',
                oro_1 = '".$oro_1_arr[$oro_idx_v]."',
                oro_2 = '".$oro_2_arr[$oro_idx_v]."'
            WHERE oro_idx = '".$oro_idx_v."'
    ";

    sql_query($sql3,1);

    $cnt++;
}

$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
if($schrows)
    $qstr .= '&schrows='.$schrows;
goto_url('./order_out_list.php?'.$qstr);