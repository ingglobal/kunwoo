<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
/*
if ($file_upload_msg){
    alert($file_upload_msg, G5_HTTP_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;w=u&amp;wr_id='.$wr_id.$qstr);
}
else{
    goto_url(G5_HTTP_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;w=u&amp;wr_id='.$wr_id.$qstr);
}
exit;
*/
if ($file_upload_msg){
    alert($file_upload_msg, G5_HTTP_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;w=u&amp;wr_id='.$wr_id.$qstr);
}
else{
    //goto_url(G5_HTTP_BBS_URL.'/write.php?bo_table='.$bo_table.'&amp;w=u&amp;wr_id='.$wr_id.$qstr);
    alert('데이터가 등록되었습니다.',G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.$qstr);
}
exit;
?>