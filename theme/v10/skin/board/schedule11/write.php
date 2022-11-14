<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if ($w == '') {
    // 일정구분
    $write['wr_1'] = 'sales';
    // 디폴트 상태값
    $write['wr_9'] = $board['set_default_status'];


} else if ($w == 'u') {
    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count'])
        $file_count = $file['count'];

    // For a simple view, link and file are showing only if existed.
    $link_display = (!$write['wr_link1']&&!$write['wr_link2']) ? "none":"block";
    $file_display = (!$file['count']) ? "none":"block";
    
} else if ($w == 'r') {


}

?>