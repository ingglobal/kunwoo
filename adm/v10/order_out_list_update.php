<?php
$sub_menu = "920110";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

// print_r2($_POST);
// exit;
auth_check($auth[$sub_menu], 'w');

check_admin_token();

//print_r2($_POST['chk']);exit;

if ($_POST['act_button'] == "선택수정") {

    foreach($_POST['chk'] as $oro_idx_v){
        $_POST['oro_count'][$oro_idx_v] = preg_replace("/,/","",$_POST['oro_count'][$oro_idx_v]);


        $sql = "UPDATE {$g5['order_out_table']} SET
                    oro_count = '".sql_real_escape_string($_POST['oro_count'][$oro_idx_v])."',
                    oro_date_plan = '".$_POST['oro_date_plan'][$oro_idx_v]."',
                    oro_date = '".$_POST['oro_date'][$oro_idx_v]."',
                    com_idx_shipto = '".$_POST['com_idx_shipto'][$oro_idx_v]."',
                    oro_status = '".$_POST['oro_status'][$oro_idx_v]."',
                    oro_update_dt = '".G5_TIME_YMDHIS."',
                    oro_1 = '".$_POST['oro_1'][$oro_idx_v]."',
                    oro_2 = '".$_POST['oro_2'][$oro_idx_v]."',
                    oro_3 = '".$_POST['oro_3'][$oro_idx_v]."',
                    oro_4 = '".$_POST['oro_4'][$oro_idx_v]."',
                    oro_5 = '".$_POST['oro_5'][$oro_idx_v]."',
                    oro_6 = '".$_POST['oro_6'][$oro_idx_v]."'
                WHERE oro_idx = '".$oro_idx_v."'
        ";

        sql_query($sql,1);
    }

} else if ($_POST['act_button'] == "선택삭제") {

    foreach($_POST['chk'] as $oro_idx_v){
        //1. 생산실행에 ord_idx가 있으면 삭제할 수 없다.
        $ori_sql = " SELECT COUNT(*) AS cnt FROM {$g5['order_out_practice_table']} WHERE oro_idx = '".$oro_idx_v."' AND oop_status NOT IN('del','delete','cancel','trash') ";
        $ori = sql_fetch($ori_sql);
        if($ori['cnt']){
            alert('생산실행에 등록된 출하데이터는 삭제할 수 없습니다.');
            exit;
        }
        /*
        $sql = "UPDATE {$g5['order_out_table']} SET
                    oro_status = 'trash'
                    , oro_history = CONCAT(oro_history,'\n삭제 by ".$member['mb_name'].", ".G5_TIME_YMDHIS."')
                WHERE oro_idx = '".$oro_idx_v."'
        ";
        */
        $sql = "UPDATE {$g5['order_out_table']} SET
                    oro_status = 'trash'
                WHERE oro_idx = '".$oro_idx_v."'
        ";
        sql_query($sql,1);
    }

}


if ($msg)
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);

//exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
if($schrows)
    $qstr .= '&schrows='.$schrows;
goto_url('./order_out_list.php?'.$qstr);
?>
