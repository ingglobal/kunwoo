<?php
// wr_id 값이 있으면 글읽기
if (isset($wr_id) && $wr_id) {
    // 글이 없을 경우 해당 게시판 목록으로 이동
    if (!$write['wr_id']) {
        $msg = '데이터가 존재하지 않습니다.\\n\\n데이터가 삭제된 경우입니다.';
        alert($msg);
    }

    //$g5['title'] = strip_tags(conv_subject($write['wr_subject'], 255))." > ".$g5['board_title'];
    $g5['title'] = $g5['board_title'];
} else {
    if ($member['mb_level'] < $board['bo_list_level']) {
        if ($member['mb_id'])
            alert('목록을 볼 권한이 없습니다.', G5_URL);
    }

    if (!isset($page) || (isset($page) && $page == 0)) $page = 1;

    $g5['title'] = $g5['board_title'].' '.$page.' 페이지';
}
include_once('./_head.php');
include_once('./bbs_common_head.php');
echo $g5['container_sub_title'];

$width = $board['bo_table_width'];
if ($width <= 100)
    $width .= '%';
else
    $width .='px';


$admin_href = "";
// 최고관리자 또는 그룹관리자라면
if ($member['mb_id'] && ($is_admin === 'super' || $group['gr_admin'] === $member['mb_id']))
    $admin_href = G5_ADMIN_URL.'/board_form.php?w=u&amp;bo_table='.$bo_table;


// 게시물 아이디가 있다면 게시물 보기를 INCLUDE
if (isset($wr_id) && $wr_id) {
    include_once(G5_USER_ADMIN_PATH.'/bbs_view.php');
    
    echo <<<HEREDOC
    <script>
    $('<input type="hidden" name="token" value="">').insertAfter('input[name="page"]');
    </script>
    HEREDOC;
}

// 전체목록보이기 사용이 "예" 또는 wr_id 값이 없다면 목록을 보임
//if ($board['bo_use_list_view'] || empty($wr_id))
if ($member['mb_level'] >= $board['bo_list_level'] && $board['bo_use_list_view'] || empty($wr_id))
    include_once (G5_USER_ADMIN_PATH.'/bbs_list.php');

echo "\n<!-- 사용스킨 : ".$board['bo_skin']." -->\n";


include_once('./bbs_common_tail.php');
include_once ('./_tail.php');