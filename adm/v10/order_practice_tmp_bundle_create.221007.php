<?php
$sub_menu = "920110";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

//print_r2($_POST);exit;

check_admin_token();

$com_idx = $_POST['com_idx'];
$orp_order_no = $_POST['orp_order_no'];
$orp_status = 'confirm';//$_POST['orp_status']; 생산계획,생산계획상품 전부 상태값은 confirm 으로 한다.

$chk_arr = $_POST['chk'];

$oro_date_plan_arr = $_POST['oro_date_plan'];
$oro_date_arr = $_POST['oro_date'];
$com_idx_shipto_arr = $_POST['com_idx_shipto'];
$oro_status_arr = $_POST['oro_status'];
$oro_1_arr = $_POST['oro_1'];
$oro_2_arr = $_POST['oro_2'];
$oro_3_arr = $_POST['oro_3'];
$oro_4_arr = $_POST['oro_4'];
$oro_5_arr = $_POST['oro_5'];
$oro_6_arr = $_POST['oro_6'];

$ord_idx_arr = $_POST['ord_idx'];
$ori_idx_arr = $_POST['ori_idx'];
$oro_idx_arr = $_POST['oro_idx'];
$oro_count_arr = $_POST['oro_count'];
$bom_idx_arr = $_POST['bom_idx'];

$overlap_bom = '';
//기존 생산계획의 작업지시번호를 기준으로 추가적으로 생산상품을 등록할때
if($orp_no_old){
    //print_r2($chk_arr);exit;
    foreach($chk_arr as $oro_idx_v1){
        //삭제,취소 등의 상태값이 아닌 생산실행 상품레코드가 있으면 중복 레코드를 생성하면 안된다.
        $chk_sql = " SELECT COUNT(*) AS cnt FROM {$g5['order_out_practice_table']} AS oop
                        LEFT JOIN {$g5['order_practice_table']} AS orp ON oop.orp_idx = orp.orp_idx
                            WHERE orp.orp_order_no = '{$orp_order_no}' 
                                AND oop.bom_idx = '{$bom_idx_arr[$oro_idx_v1]}'
                                AND oop.oop_status NOT IN('trash','del','delete','cancel') ";
        $chk_result = sql_fetch($chk_sql);

        $bom = sql_fetch(" SELECT bom_name,bom_part_no FROM {$g5['bom_table']} WHERE bom_idx = '{$bom_idx_arr[$oro_idx_v1]}' ");
        if($bom['bom_part_no']){
            $overlap_bom .= '('.$bom_idx_arr[$oro_idx_v1].')['.$bom['bom_part_no'].']('.$bom['bom_name'].')\\n';
        }
        //기존 생산실행 상품레코드가 있으면 정지.
        if($chk_result['cnt']){
            alert('선택하신 항목중에 \\n'.$overlap_bom.'\\n상품이 이미 생산계획에 등록되어 있네요.\\n다시 확인하시기 바랍니다.');
            exit;
        }
    }

    //orp_idx를 조회한다.
    $orp = sql_fetch(" SELECT orp_idx FROM {$g5['order_practice_table']} 
                            WHERE orp_status NOT IN('trash','del','delete','cancel') 
                                AND orp_order_no = '{$orp_order_no}'
    ");

    if(!$orp['orp_idx'])
        alert('해당 생산지시번호의 생산계획이 존재하지 않습니다.');

    foreach($chk_arr as $oro_idx_v){
        //천단위 제거
        $oro_count_arr[$oro_idx_v] = preg_replace("/,/","",$oro_count_arr[$oro_idx_v]);
    
        $sql = " UPDATE {$g5['order_out_table']} SET
                    oro_count = '".sql_real_escape_string($oro_count_arr[$oro_idx_v])."',
                    oro_date_plan = '".$oro_date_plan_arr[$oro_idx_v]."',
                    oro_date = '".$oro_date_arr[$oro_idx_v]."',
                    com_idx_shipto = '".$com_idx_shipto_arr[$oro_idx_v]."',
                    oro_status = '".$oro_status_arr[$oro_idx_v]."',
                    oro_update_dt = '".G5_TIME_YMDHIS."',
                    oro_1 = '".$oro_1_arr[$oro_idx_v]."',
                    oro_2 = '".$oro_2_arr[$oro_idx_v]."',
                    oro_3 = '".$oro_3_arr[$oro_idx_v]."',
                    oro_4 = '".$oro_4_arr[$oro_idx_v]."',
                    oro_5 = '".$oro_5_arr[$oro_idx_v]."',
                    oro_6 = '".$oro_6_arr[$oro_idx_v]."'
                WHERE oro_idx = '".$oro_idx_v."'
        ";
    
        sql_query($sql,1);
    
        //oop테이블에 등록
        $sql2 = " INSERT {$g5['order_out_practice_table']} SET
                    ord_idx = '".$ord_idx_arr[$oro_idx_v]."',
                    ori_idx = '".$ori_idx_arr[$oro_idx_v]."',
                    oro_idx = '".$oro_idx_arr[$oro_idx_v]."',
                    orp_idx = '".$orp['orp_idx']."',
                    bom_idx = '".$bom_idx_arr[$oro_idx_v]."',
                    oop_count = '".$oro_count_arr[$oro_idx_v]."',
                    oop_history = '',
                    oop_status = '".$orp_status."',
                    oop_reg_dt = '".G5_TIME_YMDHIS."',
                    oop_update_dt = '".G5_TIME_YMDHIS."',
                    oop_1 = '".$oro_count_arr[$oro_idx_v]."'
        ";
        sql_query($sql2,1);
    }
}
//신규 생간계획의 작업지시번호를 기준으로 새롭게 생산상품을 등록할때
else {
    $tmp_cut_forge = $_POST['cut_mms_idx'].'_'.$_POST['forge_mms_idx'];
    $trm_idx_line = $g5['trms']['linemms_trm'][$tmp_cut_forge];
    $mb_id = $_POST['mb_id'];
    $orp_start_date = $_POST['orp_start_date'];
    $orp_done_date = $_POST['orp_done_date'];
    
    
    
    //이미 등록된 동일한 지시번호가 존재하는지 확인한다.
    $ord_no_sql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['order_practice_table']} WHERE orp_order = '{$orp_order_no}' AND orp_status NOT IN('delete','del','trash') ");
    if($ord_no_sql['cnt'])
        alert('동일한 지시번호가 이미 존재합니다. 다른 지시번호를 입력해 주세요.');
    
    //동일한 생산시작일에 같은 설비라인으로 등록하려는지 체크한다.
    $ord_chk_sql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['order_practice_table']} WHERE orp_start_date = '{$orp_start_date}' AND trm_idx_line = '{$trm_idx_line}' AND orp_status NOT IN('delete','del','trash') ");
    if($ord_chk_sql['cnt'])
        alert('동일한 생산시작일에 지정하신 설비 '.$g5['line_name'][$trm_idx_line].'의 생산계획이 이미 존재합니다.\n[찾기]에서 해당 생산계획을 찾아서 추가 등록해 주세요.');

    foreach($chk_arr as $oro_idx_v1){
        //삭제,취소 등의 상태값이 아닌 생산실행 레코드가 있으면 중복 레코드를 생성하면 안된다.
        $chk_sql = " SELECT COUNT(*) AS cnt FROM {$g5['order_out_practice_table']} AS oop
                        LEFT JOIN {$g5['order_practice_table']} AS orp ON oop.orp_idx = orp.orp_idx
                            WHERE oop.bom_idx = '{$bom_idx_arr[$oro_idx_v1]}'
                            AND orp.orp_order_no = '{$orp_order_no}'
                            AND oop.oop_status NOT IN('trash','del','delete','cancel')
        ";
        $chk_result = sql_fetch($chk_sql);

        $bom = sql_fetch(" SELECT bom_name,bom_part_no FROM {$g5['bom_table']} WHERE bom_idx = '{$bom_idx_arr[$oro_idx_v1]}' ");
        if($bom['bom_part_no']){
            $overlap_bom .= '('.$bom_idx_arr[$oro_idx_v1].')['.$bom['bom_part_no'].']('.$bom['bom_name'].')\\n';
        }
        //기존 생산실행 상품레코드가 있으면 정지.
        if($chk_result['cnt']){
            alert('선택하신 항목중에 \\n'.$overlap_bom.'\\n상품이 이미 생산계획에 등록되어 있네요.\\n다시 확인하시기 바랍니다.');
            exit;
        }
    }
    
    //orp테이블에 1개의 레코드를 등록 (상태값은 무조건 confirm)
    $sql1 = " INSERT {$g5['order_practice_table']} SET
                com_idx = '".$com_idx."',
                orp_order_no = '".$orp_order_no."',
                trm_idx_operation = '',
                trm_idx_line = '".$trm_idx_line."',
                shf_idx = '',
                mb_id = '".$member['mb_id']."',
                orp_start_date = '".$orp_start_date."',
                orp_done_date = '".$orp_done_date."',
                orp_memo = '',
                orp_status = '".$orp_status."',
                orp_reg_dt = '".G5_TIME_YMDHIS."',
                orp_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql1,1);
    $orp_idx = sql_insert_id();
    
    foreach($chk_arr as $oro_idx_v){
        //천단위 제거
        $oro_count_arr[$oro_idx_v] = preg_replace("/,/","",$oro_count_arr[$oro_idx_v]);
    
        $sql = " UPDATE {$g5['order_out_table']} SET
                    oro_count = '".sql_real_escape_string($oro_count_arr[$oro_idx_v])."',
                    oro_date_plan = '".$oro_date_plan_arr[$oro_idx_v]."',
                    oro_date = '".$oro_date_arr[$oro_idx_v]."',
                    com_idx_shipto = '".$com_idx_shipto_arr[$oro_idx_v]."',
                    oro_status = '".$oro_status_arr[$oro_idx_v]."',
                    oro_update_dt = '".G5_TIME_YMDHIS."',
                    oro_1 = '".$oro_1_arr[$oro_idx_v]."',
                    oro_2 = '".$oro_2_arr[$oro_idx_v]."',
                    oro_3 = '".$oro_3_arr[$oro_idx_v]."',
                    oro_4 = '".$oro_4_arr[$oro_idx_v]."',
                    oro_5 = '".$oro_5_arr[$oro_idx_v]."',
                    oro_6 = '".$oro_6_arr[$oro_idx_v]."'
                WHERE oro_idx = '".$oro_idx_v."'
        ";
    
        sql_query($sql,1);
    
        //oop테이블에 등록
        $sql2 = " INSERT {$g5['order_out_practice_table']} SET
                    ord_idx = '".$ord_idx_arr[$oro_idx_v]."',
                    ori_idx = '".$ori_idx_arr[$oro_idx_v]."',
                    oro_idx = '".$oro_idx_arr[$oro_idx_v]."',
                    orp_idx = '".$orp_idx."',
                    bom_idx = '".$bom_idx_arr[$oro_idx_v]."',
                    oop_count = '".$oro_count_arr[$oro_idx_v]."',
                    oop_history = '',
                    oop_status = '".$orp_status."',
                    oop_reg_dt = '".G5_TIME_YMDHIS."',
                    oop_update_dt = '".G5_TIME_YMDHIS."',
                    oop_1 = '".$oro_count_arr[$oro_idx_v]."'
        ";
        sql_query($sql2,1);
    }
}

$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
if($schrows)
    $qstr .= '&schrows='.$schrows;
goto_url('./order_out_list.php?'.$qstr);
