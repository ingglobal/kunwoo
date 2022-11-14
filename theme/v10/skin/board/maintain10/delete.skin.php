<?php
/* 게시글삭제시 댓글 첨부파일 처리 */

$sql = " SELECT wr_id, mb_id, wr_is_comment FROM $write_table WHERE wr_parent = '$write[wr_id]' AND wr_is_comment = 1 ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    // 첨부 파일 삭제
    $sql2 = " SELECT * FROM {$g5['board_file_table']} WHERE bo_table = '".$bo_table."' AND wr_id = '".$row['wr_id']."' ";
    $row2 = sql_fetch($sql2);
    @unlink(G5_DATA_PATH."/file/$bo_table/".$row2['bf_file']);
        
    // 삭제 로그 남김
    $sql = "	UPDATE {$g5['board_file_table']} SET 
                    bf_content = CONCAT('삭제됨 (".$member['mb_id']." / ".G5_TIME_YMDHIS.")\n',bf_content)
                WHERE bo_table = '".$bo_table."' AND wr_id = '".$row['wr_id']."'
    ";
    sql_query($sql,1);
}
?>