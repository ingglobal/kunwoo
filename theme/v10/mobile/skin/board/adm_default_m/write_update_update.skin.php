<?php
$update_str = "
    ca_name = '{$ca_name}',
    wr_option = '{$html},{$secret},{$mail}',
    wr_subject = '{$wr_subject}',
    wr_content = '{$wr_content}',
    wr_seo_title = '$wr_seo_title',
    wr_link1 = '{$wr_link1}',
    wr_link2 = '{$wr_link2}',
    mb_id = '{$mb_id}',
    wr_name = '{$wr_name}',
    wr_email = '{$wr_email}',
    wr_homepage = '{$wr_homepage}',
    wr_1 = '{$wr_1}',
    wr_2 = '{$wr_2}',
    wr_3 = '{$wr_3}',
    wr_4 = '{$wr_4}',
    wr_5 = '{$wr_5}',
    wr_6 = '{$wr_6}',
    wr_7 = '{$wr_7}',
    wr_8 = '{$wr_8}',
    wr_9 = '{$wr_9}',
    wr_10= '{$wr_10}'
";
if(!$update_str || $update_str == "" || $update_str == " ")
    alert('수정(UPDATE)을 위한 쿼리명령이 완성되지 않습니다.');
else
    $sql .= $update_str;