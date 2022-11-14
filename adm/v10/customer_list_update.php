<?php
$sub_menu = "915120";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

if($w == 'u') {
    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
		$com = sql_fetch(" SELECT * FROM {$g5['company_table']} WHERE com_idx = '".$_POST['com_idx'][$k]."' ");
		$mb = get_member($com['mb_id']);
        /*
        if (!$mb['mb_id']) {
            $msg .= $mb['mb_id'].' : 회원자료가 존재하지 않습니다.\\n';
        } else if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level']) {
            $msg .= $mb['mb_id'].' : 자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.\\n';
        } else {
			$sql = " UPDATE {$g5['company_table']} SET
						com_status = '{$_POST['com_status'][$k]}'
					WHERE com_idx = '{$_POST['com_idx'][$k]}' ";
			sql_query($sql,1);
        }
        */
        $sql = " UPDATE {$g5['company_table']} SET
                    com_status = '{$_POST['com_status'][$k]}'
                WHERE com_idx = '{$_POST['com_idx'][$k]}' ";
        sql_query($sql,1);
    }

}
// 삭제할 때
else if($w == 'd') {
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
		$com = sql_fetch(" SELECT * FROM {$g5['company_table']} WHERE com_idx = '".$_POST['com_idx'][$k]."' ");
		$mb = get_member($com['mb_id']);
        /*
        if (!$mb['mb_id']) {
            $msg .= $mb['mb_id'].' : 회원자료가 존재하지 않습니다.\\n';
        } else if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level']) {
            $msg .= $mb['mb_id'].' : 자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.\\n';
        } else {
            // 레코드 삭제
			$sql = " UPDATE {$g5['company_table']} SET com_status = 'trash' WHERE com_idx = '{$_POST['com_idx'][$k]}' ";
			sql_query($sql,1);

			// 연결 영업자 정보 업데이트
			$pieces = explode(',', $com['mb_id_salers']);
			foreach ($pieces as $piece) {
				list($mb_1, $mb_saler_id, $mb_saler_name, $mb_saler_trm_idx) = explode('^', $piece);
				if($mb_saler_id) {
					company_member_update(array(
						"mb_id_saler"=>$mb_saler_id
						, "com_idx"=>$_POST['com_idx'][$k]
						, "cmm_status"=>"trash"
					));
				}
			}
			unset($pieces);unset($piece);
        }
        */
        // 레코드 삭제
        $sql = " UPDATE {$g5['company_table']} SET com_status = 'trash' WHERE com_idx = '{$_POST['com_idx'][$k]}' ";
        sql_query($sql,1);

        // 연결 영업자 정보 업데이트
        $pieces = explode(',', $com['mb_id_salers']);
        foreach ($pieces as $piece) {
            list($mb_1, $mb_saler_id, $mb_saler_name, $mb_saler_trm_idx) = explode('^', $piece);
            if($mb_saler_id) {
                company_member_update(array(
                    "mb_id_saler"=>$mb_saler_id
                    , "com_idx"=>$_POST['com_idx'][$k]
                    , "cmm_status"=>"trash"
                ));
            }
        }
        unset($pieces);unset($piece);
    }
}

if ($msg)
    alert($msg);
    //echo '<script> alert("'.$msg.'"); </script>';
	
goto_url('./customer_list.php?'.$qstr.'&amp;ser_trm_idx_com_type='.$ser_trm_idx_com_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea, false);
?>
