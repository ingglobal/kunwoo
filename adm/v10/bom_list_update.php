<?php
$sub_menu = "915130";
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

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        // 천단위 제거
        $_POST['bom_price'][$k] = preg_replace("/,/","",$_POST['bom_price'][$k]);
        $_POST['bom_lead_time'][$k] = preg_replace("/,/","",$_POST['bom_lead_time'][$k]);
        $_POST['bom_min_cnt'][$k] = preg_replace("/,/","",$_POST['bom_min_cnt'][$k]);
        /*
        $sql = "UPDATE {$g5['bom_table']} SET
                    bom_name = '".sql_real_escape_string($_POST['bom_name'][$k])."',
                    bom_price = '".$_POST['bom_price'][$k]."',
                    bom_lead_time = '".$_POST['bom_lead_time'][$k]."',
                    bom_min_cnt = '".$_POST['bom_min_cnt'][$k]."'
                WHERE bom_idx = '".$_POST['bom_idx'][$k]."'
        ";
        */
        $sql = "UPDATE {$g5['bom_table']} SET
                    bom_name = '".sql_real_escape_string($_POST['bom_name'][$k])."',
                    bom_std = '".trim($_POST['bom_std'][$k])."',
                    bom_pai = '".trim($_POST['bom_pai'][$k])."',
                    bom_length = '".trim($_POST['bom_length'][$k])."',
                    bom_weight = '".trim($_POST['bom_weight'][$k])."',
                    bom_price = '".trim($_POST['bom_price'][$k])."',
                    bom_press_type = '".$_POST['bom_press_type'][$k]."'
                WHERE bom_idx = '".$_POST['bom_idx'][$k]."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);

        // 가격 정보 변경
        $ar['bom_idx'] = $_POST['bom_idx'][$k];
        $ar['bom_start_date'] = G5_TIME_YMD;
        $ar['bom_price'] = $_POST['bom_price'][$k];
        bom_price_history($ar);
        unset($ar);   
        
        $hsql = " UPDATE {$g5['bom_table']} SET
                    bom_name = '".sql_real_escape_string($_POST['bom_name'][$k])."_half'
                    , bom_update_dt = '".G5_TIME_YMDHIS."'
                WHERE bom_part_no = 'H_".$_POST['bom_part_no'][$k]."'    
        ";
        sql_query($hsql,1);

    }

} else if ($_POST['act_button'] == "선택삭제") {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        // 
        $sql = "UPDATE {$g5['bom_table']} SET
                    bom_status = 'trash'
                    , bom_memo = CONCAT(bom_memo,'\n삭제 by ".$member['mb_name'].", ".G5_TIME_YMDHIS."')
                WHERE bom_idx = '".$_POST['bom_idx'][$k]."' OR bom_part_no = 'H_".$_POST['bom_part_no'][$k]."' 
        ";
        sql_query($sql,1);
    }
}

if ($msg)
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);

// exit;
$qstr .= '&sca='.$sca.'&ser_bom_type='.$ser_bom_type.'&ser_bom_press_type='.$ser_bom_press_type; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./bom_list.php?'.$qstr);
?>
