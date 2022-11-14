<?php
$sub_menu = "945115";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

// print_r2($_POST);
// exit;
auth_check($auth[$sub_menu], 'w');

check_admin_token();

if ($_POST['act_button'] == "선택수정") {

    foreach($_POST['chk'] as $itm_idx_v){
        if($_POST['itm_status'] == 'trash') {
            $history = " CONCAT(itm_history,'\n".$_POST['itm_status'][$itm_idx_v]." by ".$member['mb_name'].", ".G5_TIME_YMDHIS."') ";
        }
        else {
            $history = " CONCAT(itm_history,'\n".$_POST['itm_status'][$itm_idx_v]."|".G5_TIME_YMDHIS."') ";
        }

        $error_search = (preg_match('/^error_/', $_POST['itm_status'][$itm_idx_v])) ? ", itm_defect = '1', itm_defect_type = '".$g5['set_itm_status_ng2_reverse'][$_POST['itm_status'][$itm_idx_v]]."' " : ", itm_defect = '0', itm_defect_type = '0' ";
	    $delivery_search = ($_POST['itm_status'][$itm_idx_v] == 'delivery') ? ", itm_delivery = '1' " : ", itm_delivery = '0' ";

        $sql = " UPDATE {$g5['item_table']} SET
                    itm_status = '".$_POST['itm_status'][$itm_idx_v]."'
                    {$error_search}
                    {$delivery_search}
                    ,itm_history = ".$history."
                    ,itm_weight = '".$_POST['itm_weight'][$itm_idx_v]."'
                    ,itm_reg_dt = '".$_POST['itm_reg_dt'][$itm_idx_v]."'
                    ,itm_update_dt = '".$_POST['itm_update_dt'][$itm_idx_v]."'
                WHERE itm_idx = '".$itm_idx_v."'
        ";
        /*
        $sql = " UPDATE {$g5['item_table']} SET
                    itm_status = '".$_POST['itm_status'][$itm_idx_v]."'
                    {$error_search}
                    {$delivery_search}
                    ,itm_history = ".$history."
                    ,itm_weight = '".$_POST['itm_weight'][$itm_idx_v]."'
                    ,itm_reg_dt = '".$_POST['itm_reg_dt'][$itm_idx_v]."'
                    ,itm_update_dt = '".G5_TIME_YMDHIS."'
                WHERE itm_idx = '".$itm_idx_v."'
        ";
        */
        // echo $sql.'<br>';
        sql_query($sql,1);
    }
} else if ($_POST['act_button'] == "선택삭제") {
    foreach($_POST['chk'] as $itm_idx_v){
        $sql = " UPDATE {$g5['item_table']} SET
                    itm_status = 'trash'
                    , itm_history = CONCAT(itm_history,'\ntrash by ".$member['mb_name'].", ".G5_TIME_YMDHIS."')
                WHERE itm_idx = '".$itm_idx_v."'
        ";
        sql_query($sql,1);
    }
}

if ($msg)
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);


update_item_sum2(); //item 변경사항을 반영하기 위해 item_sum테이블 업데이트함    
// exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./item_row_list.php?'.$qstr);
?>
