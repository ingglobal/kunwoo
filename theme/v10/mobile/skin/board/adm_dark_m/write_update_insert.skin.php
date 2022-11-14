<?php
$insert_str = "
    wr_num = '$wr_num',
    wr_reply = '$wr_reply',
    wr_comment = 0,
    ca_name = '$ca_name',
    wr_option = '$html,$secret,$mail',
    wr_subject = '$wr_subject',
    wr_content = '$wr_content',
    wr_seo_title = '$wr_seo_title',
    wr_link1 = '$wr_link1',
    wr_link2 = '$wr_link2',
    wr_link1_hit = 0,
    wr_link2_hit = 0,
    wr_hit = 0,
    wr_good = 0,
    wr_nogood = 0,
    mb_id = '{$member['mb_id']}',
    wr_password = '$wr_password',
    wr_name = '$wr_name',
    wr_email = '$wr_email',
    wr_homepage = '$wr_homepage',
    wr_datetime = '".G5_TIME_YMDHIS."',
    wr_last = '".G5_TIME_YMDHIS."',
    wr_ip = '{$_SERVER['REMOTE_ADDR']}',
    wr_1 = '$wr_1',
    wr_2 = '$wr_2',
    wr_3 = '$wr_3',
    wr_4 = '$wr_4',
    wr_5 = '$wr_5',
    wr_6 = '$wr_6',
    wr_7 = '$wr_7',
    wr_8 = '$wr_8',
    wr_9 = '$wr_9',
    wr_10 = '$wr_10'
";
if(!$insert_str || $insert_str == "" || $insert_str == " ")
    alert('등록(INSERT)을 위한 쿼리명령이 완성되지 않습니다.');
else
    $sql .= $insert_str;