<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$file_count = (int)$board['bo_upload_count'];

if ($w == '') {

}
else if ($w == 'u') {

    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count'])
        $file_count = $file['count'];
    
    
} else if ($w == 'r') {


}

?>