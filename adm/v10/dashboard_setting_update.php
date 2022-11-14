<?php
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

if (!count($_POST['chk'])) {
    alert("설정 항목을 하나 이상 체크하세요.");
}

for ($i=0; $i<count($_POST['chk']); $i++) {
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];
    $mms_array[$i] = $_POST['mms_idx'][$k];

    $sql = "UPDATE {$g5['member_dash_table']} SET 
                mbd_value = '".$i."'
                , mbd_status = '".$_POST['mbd_status'][$k]."'
            WHERE mb_id = '".$member['mb_id']."'
                AND mbd_type = 'list'
                AND mms_idx = '".$_POST['mms_idx'][$k]."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

}
// print_r2($mms_array);
	
// 설정에 없는 설비 삭제
$sql = "DELETE FROM {$g5['member_dash_table']} 
        WHERE mb_id = '".$member['mb_id']."'
            AND com_idx = '".$_SESSION['ss_com_idx']."'
            AND mbd_type = 'list'
            AND mms_idx NOT IN (".implode(",",$mms_array).")
";
// echo $sql.'<br>';
sql_query($sql,1);


// exit;
goto_url('./'.$file_name.'.php', false);
?>