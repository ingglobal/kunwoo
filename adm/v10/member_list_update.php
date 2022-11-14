<?php
$sub_menu = "950300";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

auth_check($auth[$sub_menu], 'w');

check_admin_token();

if ($_POST['act_button'] == "선택수정") {

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $mb = get_member($_POST['mb_id'][$k]);

        if (!$mb['mb_id']) {
            $msg .= $mb['mb_id'].' : 회원자료가 존재하지 않습니다.\\n';
        } else if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level']) {
            $msg .= $mb['mb_id'].' : 자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.\\n';
        } else if ($member['mb_id'] == $mb['mb_id']) {
            $msg .= $mb['mb_id'].' : 로그인 중인 관리자는 수정 할 수 없습니다.\\n';
        } else {
            if($_POST['mb_certify'][$k])
                $mb_adult = (int) $_POST['mb_adult'][$k];
            else
                $mb_adult = 0;

            $sql = " update {$g5['member_table']}
                        set mb_level = '".sql_real_escape_string($_POST['mb_level'][$k])."',
                            mb_recommend = '".sql_real_escape_string($_POST['mb_recommend'][$k])."',
                            mb_1 = '".sql_real_escape_string($_POST['mb_1'][$k])."',
                            mb_2 = '".sql_real_escape_string($_POST['mb_2'][$k])."',
                            mb_3 = '".sql_real_escape_string($_POST['mb_3'][$k])."'
                        where mb_id = '".sql_real_escape_string($_POST['mb_id'][$k])."' ";
            sql_query($sql,1);
        }
    }

} else if ($_POST['act_button'] == "선택삭제") {

    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $mb = get_member($_POST['mb_id'][$k]);

        if (!$mb['mb_id']) {
            $msg .= $mb['mb_id'].' : 회원자료가 존재하지 않습니다.\\n';
        } else if ($member['mb_id'] == $mb['mb_id']) {
            $msg .= $mb['mb_id'].' : 로그인 중인 관리자는 삭제 할 수 없습니다.\\n';
        } else if (is_admin($mb['mb_id']) == 'super') {
            $msg .= $mb['mb_id'].' : 최고 관리자는 삭제할 수 없습니다.\\n';
        } else if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level']) {
            $msg .= $mb['mb_id'].' : 자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.\\n';
        } else {
			// 회원자료 삭제
			//member_delete($mb['mb_id']);
			// 직원 탈퇴 처리
			// 삭제(탈퇴)일자 입력
			$sql = " UPDATE {$g5['member_table']} SET mb_leave_date = '".date('Ymd', G5_SERVER_TIME)."' WHERE mb_id = '".$mb['mb_id']."' ";
			sql_query($sql,1);

			// 자료 초기화
			$sql = "	UPDATE {$g5['member_table']} SET 
							mb_level = 1
							, mb_memo = '".date('Y-m-d H:i', G5_SERVER_TIME)." 탈퇴처리 by ".$member['mb_name']."\n{$mb['mb_memo']}'
						WHERE mb_id = '".$mb['mb_id']."' ";
			sql_query($sql);
        }
    }
}

if ($msg)
    //echo '<script> alert("'.$msg.'"); </script>';
    alert($msg);

//exit;
goto_url('./member_list.php?'.$qstr);
?>
