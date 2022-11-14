<?php
include_once('./_common.php');

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

@include_once($board_skin_path.'/good.head.skin.php');

// 자바스크립트 사용가능할 때
if(isset($_POST['js']) && $_POST['js'] === "on") {
    $error = $count = "";

    function print_result($error, $count)
    {
        echo '{ "error": "' . $error . '", "count": "' . $count . '" }';
        if($error)
            exit;
    }

    if (!$is_member)
    {
        $error = '회원만 가능합니다.';
        print_result($error, $count);
    }

    if (!($bo_table && $wr_id)) {
        $error = '값이 제대로 넘어오지 않았습니다.';
        print_result($error, $count);
    }

    $ss_name = 'ss_view_'.$bo_table.'_'.$wr_id;
    if (!get_session($ss_name)) {
        $error = '해당 게시물에서만 추천 또는 비추천 하실 수 있습니다.';
        print_result($error, $count);
    }

    $row = sql_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} ", FALSE);
    if (!$row['cnt']) {
        $error = '존재하는 게시판이 아닙니다.';
        print_result($error, $count);
    }

    if ($good == 'good' || $good == 'nogood')
    {
        if($write['mb_id'] == $member['mb_id']) {
            $error = '자신의 글에는 추천 또는 비추천 하실 수 없습니다.';
            print_result($error, $count);
        }

        if (!$board['bo_use_good'] && $good == 'good') {
            $error = '이 게시판은 추천 기능을 사용하지 않습니다.';
            print_result($error, $count);
        }

        if (!$board['bo_use_nogood'] && $good == 'nogood') {
            $error = '이 게시판은 비추천 기능을 사용하지 않습니다.';
            print_result($error, $count);
        }

        $sql = " select bg_flag from {$g5['board_good_table']}
                    where bo_table = '{$bo_table}'
                    and wr_id = '{$wr_id}'
                    and mb_id = '{$member['mb_id']}'
                    and bg_flag in ('good', 'nogood') ";
        $row = sql_fetch($sql);
        if ($row['bg_flag'])
        {
            if ($row['bg_flag'] == 'good')
                $status = '추천';
            else
                $status = '비추천';

            $error = "이미 $status 하신 글 입니다.";
            print_result($error, $count);
        }
        else
        {
            // 추천(찬성), 비추천(반대) 카운트 증가
            sql_query(" update {$g5['write_prefix']}{$bo_table} set wr_{$good} = wr_{$good} + 1 where wr_id = '{$wr_id}' ");
            // 내역 생성
            sql_query(" insert {$g5['board_good_table']} set bo_table = '{$bo_table}', wr_id = '{$wr_id}', mb_id = '{$member['mb_id']}', bg_flag = '{$good}', bg_datetime = '".G5_TIME_YMDHIS."' ");

            $sql = " select wr_{$good} as count from {$g5['write_prefix']}{$bo_table} where wr_id = '$wr_id' ";
            $row = sql_fetch($sql);

            $count = $row['count'];
			
			

            print_result($error, $count);
        }
    }
} else {
    include_once(G5_PATH.'/head.sub.php');

    if (!($bo_table && $wr_id))
        alert('값이 제대로 넘어오지 않았습니다.');

    $ss_name = 'ss_view_'.$bo_table.'_'.$wr_id;
    if (!get_session($ss_name))
        alert('해당 게시물에서만 추천 또는 비추천 하실 수 있습니다.');

    $row = sql_fetch(" select count(*) as cnt from {$g5['write_prefix']}{$bo_table} ", FALSE);
    if (!$row['cnt'])
        alert('존재하는 게시판이 아닙니다.');

    if ($good == 'good' || $good == 'nogood')
    {
        if($write['mb_id'] == $member['mb_id'])
            alert('자신의 글에는 추천 또는 비추천 하실 수 없습니다.');

        if (!$board['bo_use_good'] && $good == 'good')
            alert('이 게시판은 추천 기능을 사용하지 않습니다.');

        if (!$board['bo_use_nogood'] && $good == 'nogood')
            alert('이 게시판은 비추천 기능을 사용하지 않습니다.');

        $sql = " select bg_flag from {$g5['board_good_table']}
                    where bo_table = '{$bo_table}'
                    and wr_id = '{$wr_id}'
                    and mb_id = '{$member['mb_id']}'
                    and bg_flag in ('good', 'nogood') ";
        $row = sql_fetch($sql);
        if ($row['bg_flag'])
        {
            if ($row['bg_flag'] == 'good')
                $status = '추천';
            else
                $status = '비추천';

            alert("이미 $status 하신 글 입니다.");
        }
        else
        {
            // 추천(찬성), 비추천(반대) 카운트 증가
            sql_query(" update {$g5['write_prefix']}{$bo_table} set wr_{$good} = wr_{$good} + 1 where wr_id = '{$wr_id}' ");
            // 내역 생성
            sql_query(" insert {$g5['board_good_table']} set bo_table = '{$bo_table}', wr_id = '{$wr_id}', mb_id = '{$member['mb_id']}', bg_flag = '{$good}', bg_datetime = '".G5_TIME_YMDHIS."' ");

            if ($good == 'good')
                $status = '추천';
            else
                $status = '비추천';

            $href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
			
			

            alert("이 글을 $status 하셨습니다.", '', false);
        }
    }
}



@include_once($board_skin_path.'/good.tail.skin.php');
