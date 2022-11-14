<?
/* 댓글삭제시 첨부파일 처리 */

// 첨부 파일 삭제
$row = sql_fetch(" SELECT bf_file FROM {$g5['board_file_table']} WHERE bo_table = '$bo_table' AND wr_id = '$comment_id' AND bf_no = '0' ");
@unlink(G5_DATA_PATH."/file/$bo_table/".$row['bf_file']);

// 삭제 로그 남김
$sql = "UPDATE {$g5['board_file_table']} SET 
            bf_content = CONCAT('삭제됨 (".$member['mb_id']." / ".G5_TIME_YMDHIS.")\n',bf_content)
        WHERE bo_table = '$bo_table' AND wr_id = '$comment_id' AND bf_no = '0'
";
sql_query($sql,1);
?>