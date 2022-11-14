<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/* 댓글첨부파일 처리 */
$file_upload_msg = "";
$upload = array();
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));
$wr_id = $comment_id;
$i = 0;

// 삭제에 체크가 되어있다면 파일을 삭제합니다.
if ($_POST['wr_commentfile_del']) {
    $upload[$i]['del_check'] = true;
    
    // 첨부 파일 삭제
    $row = sql_fetch(" SELECT bf_file FROM {$g5['board_file_table']} WHERE bo_table = '$bo_table' AND wr_id = '$wr_id' AND bf_no = '$i' ");
    @unlink(G5_DATA_PATH."/file/$bo_table/".$row['bf_file']);
    
    // 디비는 삭제 안 하고..
//	$sql = " DELETE FROM {$g5['board_file_table']} WHERE bo_table = '$bo_table' AND wr_id = '$wr_id' AND bf_no = '$i' ";
//	sql_query($sql,1);
    // 삭제 로그만 남김
    $sql = "	UPDATE {$g5['board_file_table']} SET 
                    bf_content = CONCAT('삭제됨 (".$member['mb_id']." / ".G5_TIME_YMDHIS.")\n',bf_content)
                WHERE bo_table = '$bo_table' AND wr_id = '$wr_id' AND bf_no = '$i'
    ";
    sql_query($sql,1);
}
else
    $upload[$i]['del_check'] = false;


$tmp_file = $_FILES['wr_commentfile']['tmp_name'];
$filename = $_FILES['wr_commentfile']['name'];
$filesize = $_FILES['wr_commentfile']['size'];
//print_r2($_FILES['wr_commentfile']);

// 서버에 설정된 값보다 큰파일을 업로드 한다면
if ($filename) {
    if ($_FILES['wr_commentfile']['error'] == 1) {
        alert($filename." 파일의 용량이 서버에 설정(".$upload_max_filesize.")된 값보다 크므로 업로드 할 수 없습니다.");
    }
    else if ($_FILES['wr_commentfile']['error'] != 0) {
        alert($filename." 파일이 정상적으로 업로드 되지 않았습니다.");
    }
}

if (is_uploaded_file($tmp_file)) {
    // 관리자가 아니면서 설정한 업로드 사이즈보다 크다면 건너뜀
    if (!$is_admin && $filesize > $board['bo_upload_size']) {
        alert($filename." 파일의 용량(".number_format($filesize)." 바이트)이 게시판에 설정(".number_format($board['bo_upload_size'])." 바이트)된 값보다 크므로 업로드 하지 않습니다.");
    }
    
    $timg = @getimagesize($tmp_file);
    $upload[$i]['image'] = $timg;
    
    // 4.00.11 - 글답변에서 파일 업로드시 원글의 파일이 삭제되는 오류를 수정
    if ($w == 'u') {
        // 존재하는 파일이 있다면 삭제합니다.
        $row = sql_fetch(" SELECT bf_file FROM {$g5['board_file_table']} WHERE bo_table = '$bo_table' AND wr_id = '$wr_id' AND bf_no = '$i' ");
        @unlink(G5_DATA_PATH."/file/$bo_table/".$row['bf_file']);
    }
    
    // 프로그램 원래 파일명
    $upload[$i]['source'] = $filename;
    $upload[$i]['filesize'] = $filesize;
    
    // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
    $filename = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);
    
    // 접미사를 붙인 파일명
    shuffle($chars_array);
    $shuffle = implode("", $chars_array);
    
    // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
    //$upload[$i]['file'] = abs(ip2long($_SERVER[REMOTE_ADDR])).'_'.substr($shuffle,0,8).'_'.str_replace('%', '', urlencode($filename)); 
    $upload[$i]['file'] = abs(ip2long($_SERVER[REMOTE_ADDR])).'_'.substr($shuffle,0,8).'_'.str_replace('%', '', urlencode(str_replace(' ', '_', $filename))); 
    
    $dest_file = G5_DATA_PATH."/file/$bo_table/" . $upload[$i]['file'];
    
    // 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
    $error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES['wr_commentfile']['error']);
    
    // 올라간 파일의 퍼미션을 변경합니다.
    chmod($dest_file, 0707);
    
    if (!get_magic_quotes_gpc()) {
        $upload[$i]['source'] = addslashes($upload[$i]['source']);
    }
    
    $row = sql_fetch(" SELECT count(*) AS cnt FROM {$g5['board_file_table']} WHERE bo_table = '$bo_table' AND wr_id = '$wr_id' AND bf_no = '$i' ");
    if ($row[cnt]) {
        // 삭제에 체크가 있거나 파일이 있다면 업데이트를 합니다.
        // 그렇지 않다면 내용만 업데이트 합니다.
        if ( $upload[$i]['file']) 
        {
            $sql = " UPDATE {$g5['board_file_table']}
                        SET bf_source = '{$upload[$i]['source']}',
                            bf_file = '{$upload[$i]['file']}',
                            bf_content = '{$bf_content[$i]}',
                            bf_filesize = '{$upload[$i]['filesize']}',
                            bf_width = '{$upload[$i]['image'][0]}',
                            bf_height = '{$upload[$i]['image'][1]}',
                            bf_type = '{$upload[$i]['image'][2]}',
                            bf_datetime = '".G5_TIME_YMDHIS."'
                    WHERE bo_table = '$bo_table'
                        AND wr_id = '$wr_id'
                        AND bf_no = '$i'
            ";
            sql_query($sql,1);
        }
        else {
            $sql = " UPDATE {$g5['board_file_table']}
                        SET bf_content = '{$bf_content[$i]}' 
                    WHERE bo_table = '$bo_table'
                        AND wr_id = '$wr_id'
                        AND bf_no = '$i'
            ";
            sql_query($sql,1);
        }
    } 
    else {
        $sql = " INSERT INTO {$g5['board_file_table']}
                    SET bo_table = '$bo_table',
                        wr_id = '$wr_id',
                        bf_no = '$i',
                        bf_source = '{$upload[$i]['source']}',
                        bf_file = '{$upload[$i]['file']}',
                        bf_content = '{$bf_content[$i]}',
                        bf_download = 0,
                        bf_filesize = '{$upload[$i]['filesize']}',
                        bf_width = '{$upload[$i]['image'][0]}',
                        bf_height = '{$upload[$i]['image'][1]}',
                        bf_type = '{$upload[$i]['image'][2]}',
                        bf_datetime = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql,1);
    }
}

// qstr 조건을 추가해서 넘겨야 하는데 없어서 write_comment_update.php 파일 끝 부분 가지고 와서 재설정해서 넘김
$qstr .= '&fr_date='.$fr_date.'&to_date='.$to_date.'&sch_wr_10='.$sch_wr_10;
$qstr .= '&pl_date='.$pl_date.'&sch_mb_name_worker='.$sch_mb_name_worker.'&sch_wr_5='.$sch_wr_5.'&ser_com_idx='.$ser_com_idx;

delete_cache_latest($bo_table);

goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr['wr_parent'].'&amp;'.$qstr.'&amp;#c_'.$comment_id);

//echo $sql;
//exit;
?>