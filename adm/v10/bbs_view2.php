<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 게시판에서 두단어 이상 검색 후 검색된 게시물에 코멘트를 남기면 나오던 오류 수정
$sop = strtolower($sop);
if ($sop != 'and' && $sop != 'or')
    $sop = 'and';

$sql_search = "";
// 검색이면
if ($sca || $stx || $stx === '0') {
    // where 문을 얻음
    $sql_search = get_sql_search($sca, $sfl, $stx, $sop);
    $search_href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;page='.$page.'&amp;'.$qstr;
    $list_href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table;
} else {
    $search_href = '';
    $list_href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;'.$qstr;
}

if (!$board['bo_use_list_view']) {
    if ($sql_search)
        $sql_search = " and " . $sql_search;
}


// 쓰기 링크
$write_href = '';
if ($member['mb_level'] >= $board['bo_write_level']) {
    $write_href = short_url_clean(G5_USER_ADMIN_URL.'/bbs_write.php?bo_table='.$bo_table);
}

// 수정, 삭제 링크
$update_href = $delete_href = '';
// 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
if (($member['mb_id'] && ($member['mb_id'] === $write['mb_id'])) || $is_admin) {
    $update_href = short_url_clean(G5_USER_ADMIN_URL.'/bbs_write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr);
    set_session('ss_delete_token', $token = uniqid(time()));
    $delete_href = G5_USER_ADMIN_URL.'/bbs_delete.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$token.'&amp;page='.$page.'&amp;'.urldecode($qstr);
}
else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
    $update_href = G5_USER_ADMIN_URL.'/bbs_password.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr;
    $delete_href = G5_USER_ADMIN_URL.'/bbs_password.php?w=d&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr;
}
/*
// 최고, 그룹관리자라면 글 복사, 이동 가능
$copy_href = $move_href = '';
if ($write['wr_reply'] == '' && ($is_admin == 'super' || $is_admin == 'group')) {
    $copy_href = G5_USER_ADMIN_URL.'/bbs_move.php?sw=copy&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr;
    $move_href = G5_USER_ADMIN_URL.'/bbs_move.php?sw=move&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.'&amp;'.$qstr;
}
*/
$view = get_view2($write, $board, $board_skin_path);
//print_r3($view);
/*
if (strstr($sfl, 'subject'))
    $view['subject'] = search_font($stx, $view['subject']);

$html = 0;
if (strstr($view['wr_option'], 'html1'))
    $html = 1;
else if (strstr($view['wr_option'], 'html2'))
    $html = 2;

$view['content'] = conv_content($view['wr_content'], $html);
if (strstr($sfl, 'content'))
    $view['content'] = search_font($stx, $view['content']);

//$view['rich_content'] = preg_replace("/{이미지\:([0-9]+)[:]?([^}]*)}/ie", "view_image(\$view, '\\1', '\\2')", $view['content']);
function conv_rich_content($matches)
{
    global $view;
    return view_image($view, $matches[1], $matches[2]);
}
$view['rich_content'] = preg_replace_callback("/{이미지\:([0-9]+)[:]?([^}]*)}/i", "conv_rich_content", $view['content']);
*/

//별도의 메타테이블에 저장된 데이터를 합친다
$mrow = get_meta('board/'.$bo_table,$wr_id);//,$code64=1
if(@count($mrow)){
    foreach($mrow as $mk => $mv){
        $view[$mk] = $mv;
    }
}

//관련파일 추출
$sql = "SELECT * FROM {$g5['file_table']} 
WHERE fle_db_table = 'board/{$bo_table}' AND fle_type = 'ref' AND fle_db_id = '".$view['wr_id']."' ORDER BY fle_reg_dt DESC ";
$rs = sql_query($sql,1);
//echo $rs->num_rows;echo "<br>";
$row['bo_f_ref'] = array();
$row['bo_ref_fidxs'] = array();//게시판 파일번호(fle_idx) 목록이 담긴 배열
for($i=0;$row2=sql_fetch_array($rs);$i++) {
    $file_down_del = (is_file(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name'])) ? $row2['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row2['fle_path'].'/'.$row2['fle_name']).'&file_name_orig='.$row2['fle_name_orig'].'" file_path="'.$row2['fle_path'].'" style="color:blue;">[파일다운로드]</a>':''.PHP_EOL;
    @array_push($row['bo_f_'.$row2['fle_type']],array('file'=>$file_down_del));
    @array_push($row['bo_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
}


echo $field_prop;
include_once($board_skin_path.'/view.skin.php');