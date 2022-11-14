<?php
$sub_menu = "950400";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');

check_admin_token();

$mb_id = trim($_REQUEST['mb_id']);

$mb = get_member($mb_id);
if (!$mb['mb_id'])
    alert('존재하지 않는 회원아이디입니다.');

$sql_common = " com_idx = '{$_POST['com_idx']}'
                , cmm_title = '{$_POST['cmm_title']}'
                , cmm_memo = '{$_POST['cmm_memo']}'
";

if ($w == '') {
    $sql = " INSERT INTO {$g5['company_member_table']} SET
                    mb_id = '{$mb_id}'
                    , cmm_status = 'ok'
                    , cmm_reg_dt = '".G5_TIME_YMDHIS."'
                , {$sql_common}
    ";
    sql_query($sql,1);
    $cmm_idx = sql_insert_id();
}
else if ($w == 'u') {
    $sql = "UPDATE {$g5['company_member_table']} SET
                cmm_update_dt = '".G5_TIME_YMDHIS."'
                , {$sql_common}
            WHERE cmm_idx = '{$cmm_idx}' ";
    sql_query($sql,1);
}
else if ($w == 'd') {
    $sql = "UPDATE {$g5['company_member_table']} SET
                cmm_status = 'trash'
            WHERE cmm_idx = '{$cmm_idx}' ";
    sql_query($sql,1);
    goto_url('./member_company_list.php?mb_id='.$mb_id, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 해당 회원의 업체 정보 변경
$sql = "UPDATE {$g5['member_table']} SET mb_4 = '".$_POST['com_idx']."' WHERE mb_id = '{$mb_id}' ";
sql_query($sql,1);

//exit;
goto_url('./member_company_form.php?'.$qstr.'&amp;w=u&amp;cmm_idx='.$cmm_idx, false);
?>