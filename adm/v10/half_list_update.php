<?php
$sub_menu = "945113";
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

    foreach($_POST['chk'] as $mtr_idx_v){
        if($_POST['mtr_status'] == 'trash') {
            $history = " CONCAT(mtr_history,'\n".$_POST['mtr_status'][$mtr_idx_v]." by ".$member['mb_name'].", ".G5_TIME_YMDHIS."') ";
        }
        else {
            $history = " CONCAT(mtr_history,'\n".$_POST['mtr_status'][$mtr_idx_v]."|".G5_TIME_YMDHIS."') ";
        }

        $error_search = (preg_match('/^error_/', $_POST['mtr_status'][$mtr_idx_v])) ? ", mtr_defect = '1', mtr_defect_type = '".$g5['set_half_status_ng2_reverse'][$_POST['mtr_status'][$mtr_idx_v]]."' " : ", mtr_defect = '0', mtr_defect_type = '0' ";

        $sql = " UPDATE {$g5['material_table']} SET
                    mtr_status = '".$_POST['mtr_status'][$mtr_idx_v]."'
                    {$error_search}
                    ,mtr_history = ".$history."
                    ,mtr_weight = '".$_POST['mtr_weight'][$mtr_idx_v]."'
                    ,mtr_melt_dt = '".$_POST['mtr_melt_dt'][$mtr_idx_v]."'
                    ,mtr_reg_dt = '".$_POST['mtr_reg_dt'][$mtr_idx_v]."'
                    ,mtr_update_dt = '".G5_TIME_YMDHIS."'
                WHERE mtr_idx = '".$mtr_idx_v."' AND mtr_type = 'half'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);
    }
} else if ($_POST['act_button'] == "선택삭제") {
    foreach($_POST['chk'] as $mtr_idx_v){
        $sql = " UPDATE {$g5['material_table']} SET
                    mtr_status = 'trash'
                    , mtr_history = CONCAT(mtr_history,'\ntrash by ".$member['mb_name'].", ".G5_TIME_YMDHIS."')
                WHERE mtr_idx = '".$mtr_idx_v."' AND mtr_type = 'half'
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
goto_url('./half_row_list.php?'.$qstr);
?>
