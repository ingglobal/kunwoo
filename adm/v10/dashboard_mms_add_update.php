<?php
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

if (!count($_POST['chk'])) {
    alert("추가하실 설비 항목을 하나 이상 체크하세요.");
}

for ($i=0; $i<count($_POST['chk']); $i++) {
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $sql = "SELECT * FROM {$g5['member_dash_table']}
            WHERE mms_idx = '".$_POST['mms_idx'][$k]."'
                AND mb_id = '".$member['mb_id']."'
                AND com_idx = '".$_SESSION['ss_com_idx']."'
                AND mbd_type = 'list'
    ";
    // echo $sql.'<br>';
    $mbd = sql_fetch($sql,1);
    if ($mbd['mbd_idx']) {
        $mms = get_table_meta('mms','mms_idx',$mbd['mms_idx']);
        // print_r2($mms);
        $msg .= $mms['mms_name'].': 이미 추가된 설비 (추가하지 않았음)\\n';
    }
    else {
        $sql = "SELECT MAX(mbd_value) AS mbd_max FROM {$g5['member_dash_table']}
                WHERE mbd_type = 'list'
                    AND mb_id = '".$member['mb_id']."'
                    AND com_idx = '".$_SESSION['ss_com_idx']."'
        ";
        // echo $sql.'<br>';
        $mbd1 = sql_fetch($sql, 1);
        // print_r2($mbd1);
        $sql = "INSERT INTO {$g5['member_dash_table']} SET 
                    mb_id = '".$member['mb_id']."'
                    , com_idx = '".$_SESSION['ss_com_idx']."'
                    , mms_idx = '".$_POST['mms_idx'][$k]."'
                    , mbd_value = '".($mbd1['mbd_max']+1)."'
                    , mbd_status = 'show'
                    , mbd_reg_dt = '".G5_TIME_YMDHIS."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);
    }
}
	
if ($msg)
    echo '<script> alert("'.$msg.'"); </script>';
    // alert($msg);

// exit;
// 부모창 새로고침
echo '<script>
        opener.location.reload();
        window.close();
      </script>';

?>