<?php
include_once('./_common.php');

//print_r2($_REQUEST);

$bo_7 = '';   // 초기화 or 기존값($bo_7);
$bo_7 = serialized_update('set_default_status',$_REQUEST['set_default_status'],$bo_7);
$bo_7 = serialized_update('set_hp_yn',$_REQUEST['set_hp_yn'],$bo_7);
$bo_7 = serialized_update('set_hp_required',$_REQUEST['set_hp_required'],$bo_7);
$bo_7 = serialized_update('set_email_required',$_REQUEST['set_email_required'],$bo_7);


// 게시판 변수 수정 (개인 정보가 포함되므로 목록을 제외한 나머지 권한을 관리권한으로 설정해서 일반인들이 못 보게 해야 됨)
$bo_level_sql = ",bo_read_level = 6
                ,bo_write_level = 6
                ,bo_reply_level = 6
                ,bo_comment_level = 6
                ,bo_link_level = 6
                ,bo_upload_level = 6
                ,bo_download_level = 6
                ,bo_html_level = 6
";


$sql = " UPDATE {$g5['board_table']} SET
                bo_1_subj = '메뉴코드(6자리)'
                , bo_7_subj = '환경설정변수'
                , bo_9_subj = '상태값'
                , bo_2 = '".$bo_2."'
                , bo_3 = '".$bo_3."'
                , bo_4 = '".$bo_4."'
                , bo_6 = '".$bo_6."'
                , bo_7 = '".$bo_7."'
                , bo_8 = '".$bo_8."'
                , bo_9 = '".$bo_9."'
                ".$bo_level_sql."
            WHERE bo_table = '".$bo_table."'
";
//echo $sql.'<br>';
sql_query($sql,1);


//exit;
goto_url('./config_form.php?bo_table='.$bo_table, false);
?>