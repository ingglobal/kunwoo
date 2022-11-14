<?php
$sub_menu = "950300";
include_once('./_common.php');

//auth_check($auth[$sub_menu],'d');
if(!$member['mb_manager_yn']) {
    alert_close('메뉴 접근 권한이 없습니다.');
}

check_admin_token();

if(!$mb_id_saler)
    alert('아이디를 입력하세요.');

$mb1 = get_table_meta('member','mb_id',$mb_id_saler);

if(!$mb1['mb_id'])
    alert('존재하지 않는 회원입니다.');



$sql = " SELECT * FROM {$g5['auth_table']} WHERE mb_id = '".$mb_id_saler."' ";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    //print_r2($row);

    $au1 = sql_fetch(" SELECT * FROM {$g5['auth_table']} WHERE mb_id = '".$mb_id."' AND au_menu = '".$row['au_menu']."' ",1);
    // 존재하면 업데이트
    if($au1['au_menu']) {
        $sql = "UPDATE {$g5['auth_table']} SET
                    au_auth = '".$row['au_auth']."'
                WHERE mb_id = '".$mb_id."' AND au_menu = '".$row['au_menu']."'
        ";
        //echo $sql.'<br>';
        sql_query($sql,1);
    }
    // 없으면 생성
    else {
        $sql = "INSERT INTO {$g5['auth_table']} SET
                    mb_id = '".$mb_id."'
                    , au_menu = '".$row['au_menu']."'
                    , au_auth = '".$row['au_auth']."'
        ";
        //echo $sql.'<br>';
        sql_query($sql,1);
    }
}

//exit;
goto_url('./manager_auth_setting.php?mb_id='.$mb_id);
?>