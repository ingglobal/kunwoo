<?php
include_once('./_common.php');


$q = sql_query( 'DESCRIBE '.$g5['board_table'] );
while($row = sql_fetch_array($q)) {
//    if($row['Field']=='bo_6' && $row['Type']=='varchar(255)') {
//        //echo $row['Field'].' - '.$row['Type'].'<br>';
//        sql_query(" ALTER TABLE `{$g5['board_table']}` CHANGE `bo_6` `bo_6` TEXT ", true);
//    }
    if($row['Field']=='bo_7' && $row['Type']=='varchar(255)') {
        //echo $row['Field'].' - '.$row['Type'].'<br>';
        sql_query(" ALTER TABLE `{$g5['board_table']}` CHANGE `bo_7` `bo_7` longtext ", true);
    }
}


// 불가능 요일 설정
for($i=0;$i<7;$i++) {
    $_REQUEST['week_disables'] .= ( $set_week_disable[$i] ) ? "1|" : "0|";
}

//print_r2($_REQUEST);

$a['set_time_unit'] = $_REQUEST['set_time_unit'];
$a['set_max_time_apply'] = $_REQUEST['set_max_time_apply'];
$a['set_max_apply'] = $_REQUEST['set_max_apply'];
$a['set_default_status'] = $_REQUEST['set_default_status'];
$a['set_notin_status'] = $_REQUEST['set_notin_status'];
$a['set_stat_show'] = $_REQUEST['set_stat_show'];
$a['set_holiday_apply_yn'] = $_REQUEST['set_holiday_apply_yn'];
$a['week_disables'] = $_REQUEST['week_disables'];
$a['set_name_type'] = $_REQUEST['set_name_type'];
$a['start_time'] = ($_REQUEST['start_time']) ? date("H:i:s", strtotime($_REQUEST['start_time'])) : '' ;
$a['end_time'] = ($_REQUEST['end_time']) ? date("H:i:s", strtotime($_REQUEST['end_time'])) : '' ;
$a['break_start_time'] = ($_REQUEST['break_start_time']) ? date("H:i:s", strtotime($_REQUEST['break_start_time'])) : '' ;
$a['break_end_time'] = ($_REQUEST['break_end_time']) ? date("H:i:s", strtotime($_REQUEST['break_end_time'])) : '' ;
$a['set_policy_yn'] = $_REQUEST['set_policy_yn'];
$a['set_hp_yn'] = $_REQUEST['set_hp_yn'];
$a['set_hp_required'] = $_REQUEST['set_hp_required'];
$a['set_email_required'] = $_REQUEST['set_email_required'];
$a['set_policy_content'] = base64_encode($_REQUEST['set_policy_content']);
$bo_7 = addslashes(serialize($a));
unset($a);



// 게시판 변수 수정 (개인 정보가 포함되므로 목록을 제외한 나머지 권한은 무조건 8 이상으로 설정해서 일반인들이 못 보게 해야 됨)
//$sql = " UPDATE {$g5['board_table']} SET 
//                bo_read_level = 8
//                , bo_write_level = 8
//                , bo_reply_level = 8
//                , bo_comment_level = 8
//                , bo_link_level = 8
//                , bo_upload_level = 8
//                , bo_download_level = 8
//                , bo_html_level = 8
//                , bo_6 = '".$bo_6."'
//                , bo_7 = '".$bo_7."'
//                , bo_8 = '".$bo_8."'
//                , bo_9 = '".$bo_9."'
//            WHERE bo_table = '".$bo_table."'
//";
$sql = " UPDATE {$g5['board_table']} SET 
                bo_6 = '".$bo_6."'
                , bo_7 = '".$bo_7."'
                , bo_8 = '".$bo_8."'
                , bo_9 = '".$bo_9."'
            WHERE bo_table = '".$bo_table."'
";
//echo $sql.'<br>';
sql_query($sql,1);


//exit;
goto_url('./config_form.php?bo_table='.$bo_table, false);
?>