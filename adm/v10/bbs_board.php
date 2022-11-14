<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

if(!$bo_table){
    $msg = "bo_table 값이 넘어오지 않았습니다.\\n\\nbbs_board.php?bo_table=code 와 같은 방식으로 넘겨 주세요.";
    alert($msg);
}

if (!$board['bo_table']) {
    alert('존재하지 않는 게시판입니다.', G5_USER_ADMIN_URL);
}


if($board['gr_id'] != 'intra'){
    $msg = '관리자페이지에서 사용할 수 없는 게시판입니다.\\게시판그룹을 확인해 주세요.';
    alert($msg);
}
if($board['bo_1_subj'] != 'sub_menu'){
    $msg = '관리자페이지에서 사용할 수 없는 게시판입니다.\\n게시판관리에서 [sub_menu]지정이 되어 있는지 확인해 주세요.';
    alert($msg);
}

if(!$board['bo_1']){
    $msg = '관리자페이지에서 사용할 수 없는 게시판입니다.\\n게시판관리에서 [sub_menu]의 코드지정이 되어 있는지 확인해 주세요.';
    alert($msg);
}

$sub_menu = trim($board['bo_1']);
auth_check($auth[$sub_menu],"r");

$g5['board_title'] = $board['bo_subject'];

@include($board_skin_path.'/_set/change_fields.skin.php');
include_once(G5_USER_ADMIN_PATH.'/bbs_board2_default_check.php');


// wr_id 값이 있으면 글읽기
if (isset($wr_id) && $wr_id) {
    // 글이 없을 경우 해당 게시판 목록으로 이동
    if (!$write['wr_id']) {
        $msg = '글이 존재하지 않습니다.\\n\\n글이 삭제되었거나 이동된 경우입니다.';
        alert($msg);
    }

    // 자신의 글이거나 관리자라면 통과
    if (($write['mb_id'] && $write['mb_id'] === $member['mb_id']) || $is_admin) {
        ;
    } else {
        // 비밀글이라면
        if (strstr($write['wr_option'], "secret"))
        {
            // 회원이 비밀글을 올리고 관리자가 답변글을 올렸을 경우
            // 회원이 관리자가 올린 답변글을 바로 볼 수 없던 오류를 수정
            $is_owner = false;
            if ($write['wr_reply'] && $member['mb_id'])
            {
                $sql = " select mb_id from {$write_table}
                            where wr_num = '{$write['wr_num']}'
                            and wr_reply = ''
                            and wr_is_comment = 0 ";
                $row = sql_fetch($sql);
                if ($row['mb_id'] === $member['mb_id'])
                    $is_owner = true;
            }

            $ss_name = 'ss_secret_'.$bo_table.'_'.$write['wr_num'];

            if (!$is_owner)
            {
                //$ss_name = "ss_secret_{$bo_table}_{$wr_id}";
                // 한번 읽은 게시물의 번호는 세션에 저장되어 있고 같은 게시물을 읽을 경우는 다시 비밀번호를 묻지 않습니다.
                // 이 게시물이 저장된 게시물이 아니면서 관리자가 아니라면
                //if ("$bo_table|$write['wr_num']" != get_session("ss_secret"))
                if (!get_session($ss_name))
                    goto_url(G5_USER_ADMIN_URL.'/bbs_password.php?w=s&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;'.$qstr);
            }

            set_session($ss_name, TRUE);
        }
    }

    // 한번 읽은글은 브라우저를 닫기전까지는 카운트를 증가시키지 않음
    $ss_name = 'ss_view_'.$bo_table.'_'.$wr_id;
    if (!get_session($ss_name))
    {
        sql_query(" update {$write_table} set wr_hit = wr_hit + 1 where wr_id = '{$wr_id}' ");

        // 자신의 글이면 통과
        if ($write['mb_id'] && $write['mb_id'] === $member['mb_id']) {
            ;
        } else if ($is_guest && $board['bo_read_level'] == 1 && $write['wr_ip'] == $_SERVER['REMOTE_ADDR']) {
            // 비회원이면서 읽기레벨이 1이고 등록된 아이피가 같다면 자신의 글이므로 통과
            ;
        } else {
            // 글읽기 포인트가 설정되어 있다면-> 무조건 통과
            ;
        }

        set_session($ss_name, TRUE);
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

// IP보이기 사용 여부
$ip = "";
$is_ip_view = $board['bo_use_ip_view'];
if ($is_admin) {
    $is_ip_view = true;
    if ($write && array_key_exists('wr_ip', $write)) {
        $ip = $write['wr_ip'];
    }
} else {
    // 관리자가 아니라면 IP 주소를 감춘후 보여줍니다.
    if (isset($write['wr_ip'])) {
        $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", G5_IP_DISPLAY, $write['wr_ip']);
    }
}

// 분류 사용
$is_category = false;
$category_name = '';
if ($board['bo_use_category']) {
    $is_category = true;
    if (array_key_exists('ca_name', $write)) {
        $category_name = $write['ca_name']; // 항목명
    }
}
$notice_arr = ($board['bo_notice']) ? explode(',',$board['bo_notice']) : array();
$category_arr = ($board['bo_category_list']) ? explode('|',$board['bo_category_list']) : array();
$category_link = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;sca=';
$catelink_arr = array();
$notice_per_category = array();
foreach($category_arr as $cv){
    $catelink_arr[$cv] = $category_link.urlencode($cv);
    $ca_sql = sql_fetch(" SELECT GROUP_CONCAT(wr_id) AS wr_id_str FROM {$write_table} WHERE ca_name = '{$cv}' GROUP BY ca_name ");
    $notice_per_category[$cv] = ($ca_sql['wr_id_str']) ? explode(',',$ca_sql['wr_id_str']) : array();
}



// 추천 사용
$is_good = false;
if ($board['bo_use_good'])
    $is_good = true;

// 비추천 사용
$is_nogood = false;
if ($board['bo_use_nogood'])
    $is_nogood = true;

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