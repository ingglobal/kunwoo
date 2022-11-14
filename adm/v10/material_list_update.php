<?php
$sub_menu = "945110";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

// print_r2($_POST);
// exit;
auth_check($auth[$sub_menu], 'w');

check_admin_token();
//print_r2($_POST);exit;
if ($_POST['act_button'] == "선택수정") {
    foreach($_POST['chk'] as $mtr_idx_v) {

        // 천단위 제거
        $_POST['mtr_price'][$mtr_idx_v] = preg_replace("/,/","",$_POST['mtr_price'][$mtr_idx_v]);

        if($_POST['mtr_status'] == 'trash') {
            $history = " CONCAT(mtr_history,'\n삭제 by ".$member['mb_name'].", ".G5_TIME_YMDHIS."') ";
        }
        else {
            $history = " CONCAT(mtr_history,'\n".$_POST['mtr_status'][$mtr_idx_v]."|".G5_TIME_YMDHIS."') ";
        }
        $sql = " UPDATE {$g5['material_table']} SET
                    mtr_status = '".$_POST['mtr_status'][$mtr_idx_v]."',
                    mtr_history = ".$history.",
                    mtr_update_dt = '".G5_TIME_YMDHIS."'
                WHERE mtr_idx = '".$mtr_idx_v."'
        ";
        //echo $sql.'<br>';
        sql_query($sql,1);
    }
    //exit;
} else if ($_POST['act_button'] == "선택삭제") {
    foreach($_POST['chk'] as $mtr_idx_v){
        $sql = " UPDATE {$g5['material_table']} SET
                    mtr_status = 'trash'
                    , mtr_history = CONCAT(mtr_history,'\n삭제 by ".$member['mb_name'].", ".G5_TIME_YMDHIS."')
                WHERE mtr_idx = '".$mtr_idx_v."'
        ";
        sql_query($sql,1);
    }
}

if ($msg)
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);

// exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./material_list.php?'.$qstr);
?>
