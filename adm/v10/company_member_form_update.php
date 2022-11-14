<?php
$sub_menu = "950400";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

//check_admin_token();
$com = get_table_meta('company','com_idx',$com_idx);
//print_r2($com);
//exit;
if(!$com['com_idx'])
    alert('업체가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com['com_idx']);
//	print_r2($com);

// 회원정보
$sql_common1 = " mb_name = '{$_POST['mb_name']}'
                , mb_hp = '{$_POST['mb_hp']}'
                , mb_email = '{$_POST['mb_email']}'
                , mb_memo = '{$_POST['mb_memo']}'
                , mb_4 = '".$_POST['com_idx']."'
                , mb_5 = '".$_POST['mb_5']."'
";

// 업체담당자 테이블 정보
$sql_common2 = " com_idx = '{$_POST['com_idx']}'
                , cmm_title = '{$_POST['cmm_title']}'
                , cmm_memo = '{$_POST['cmm_memo']}'
";

if ($w == '') {
    
    // 휴대폰 번호로 중복회원 체크 (중복회원이 있으면 회원정보 생성 안함)
    $mb1 = sql_fetch(" SELECT mb_id FROM {$g5['member_table']} WHERE REGEXP_REPLACE(mb_hp,'-','') = '".preg_replace("/-/","",$_POST['mb_hp'])."' ");
    if($mb1['mb_id']) {
        $mb_id = $mb1['mb_id'];
    }
    else {
        $sql = " INSERT INTO {$g5['member_table']} SET
                        {$sql_common1}
                        , mb_id = '{$mb_id}'
                        , mb_nick = '{$mb_id}'
                        , mb_level = '4'
                        , mb_password = '".get_encrypt_string($mb_id)."'
                        , mb_datetime = '".G5_TIME_YMDHIS."'
                        , mb_ip = '{$_SERVER['REMOTE_ADDR']}'
                        , mb_email_certify = '".G5_TIME_YMDHIS."'
                        , mb_mailling = '{$_POST['mb_mailling']}'
                        , mb_sms = '{$_POST['mb_sms']}'
                        , mb_open = '{$_POST['mb_open']}'
        ";
        sql_query($sql,1);
    }

    $sql = " INSERT INTO {$g5['company_member_table']} SET
                    {$sql_common2}
                    , mb_id = '{$mb_id}'
                    , cmm_status = 'ok'
                    , cmm_reg_dt = '".G5_TIME_YMDHIS."'
                    , cmm_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql,1);
    $cmm_idx = sql_insert_id();
    
}
else if ($w == 'u') {

    $sql = "UPDATE {$g5['member_table']} SET
                {$sql_common1}
            WHERE mb_id = '{$mb_id}' ";
    sql_query($sql,1);
    

    $sql = "UPDATE {$g5['company_member_table']} SET
                {$sql_common2}
                , cmm_update_dt = '".G5_TIME_YMDHIS."'
            WHERE cmm_idx = '{$cmm_idx}' ";
    sql_query($sql,1);
    
}
else if ($w == 'd') {

    $sql = "UPDATE {$g5['company_member_table']} SET
                cmm_status = 'trash'
            WHERE cmm_idx = '{$cmm_idx}' ";
    sql_query($sql,1);
    goto_url('./company_member_list.php?com_idx='.$com_idx, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


//exit;
goto_url('./company_member_form.php?'.$qstr.'&amp;w=u&cmm_idx='.$cmm_idx, false);
?>