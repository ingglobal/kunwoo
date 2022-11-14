<?php
$sub_menu = "930105";
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
    //for ($i=0; $i<count($_POST['chk']); $i++){
    foreach($_POST['chk'] as $orp_idx_v){
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        
        $sql = "UPDATE {$g5['order_practice_table']} SET
                    orp_done_date = '".$_POST['orp_done_date'][$orp_idx_v]."',
                    orp_update_dt = '".G5_TIME_YMDHIS."'
                WHERE orp_idx = '".$orp_idx_v."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);
    
    }

}

if ($msg)
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);

// exit;
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
goto_url('./order_practice_list.php?'.$qstr);
?>
