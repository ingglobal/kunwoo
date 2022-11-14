<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 정렬
// 인덱스 필드가 아니면 정렬에 사용하지 않음
//if (!$sst || ($sst && !(strstr($sst, 'wr_id') || strstr($sst, "wr_datetime")))) {
if (!$sst) {
    if ($board['bo_sort_field']) {
        $sst = $board['bo_sort_field'];
    } else {
        $sst  = "";
        $sod = "";
    }
} else {
    $board_sort_fields = get_board_sort_fields($board, 1);
    if (!$sod && array_key_exists($sst, $board_sort_fields)) {
        $sst = $board_sort_fields[$sst];
    } else {
        // 게시물 리스트의 정렬 대상 필드가 아니라면 공백으로 (nasca 님 09.06.16)
        // 리스트에서 다른 필드로 정렬을 하려면 아래의 코드에 해당 필드를 추가하세요.
        // $sst = preg_match("/^(wr_subject|wr_datetime|wr_hit|wr_good|wr_nogood)$/i", $sst) ? $sst : "";
        $sst = "";
    }
}

if(!$sst)
    $sst  = "";

if ($sst) {
    $sql_order = " order by {$sst} {$sod} ";
}


$sql = " select * from {$write_table} where (1) ";
if(!empty($notice_array))
    $sql .= " and wr_id not in (".implode(', ', $notice_array).") ";
$sql .= " {$sql_order} limit {$from_record}, $page_rows ";

//echo $sql;
// 페이지의 공지개수가 목록수 보다 작을 때만 실행
if($page_rows > 0) {
    $result = sql_query($sql);

    $k = 0;

    while ($row = sql_fetch_array($result))
    {
        // 검색일 경우 wr_id만 얻었으므로 다시 한행을 얻는다
        if ($is_search_bbs)
            $row = sql_fetch(" select * from {$write_table} where wr_id = '{$row['wr_id']}' ");

        $list[$i] = get_list3($row, $board, $board_skin_url, G5_IS_MOBILE ? $board['bo_mobile_subject_len'] : $board['bo_subject_len']);

        if (strstr($sfl, 'subject')) {
            $list[$i]['subject'] = search_font($stx, $list[$i]['subject']);
        }
        $list[$i]['is_notice'] = false;
        $list_num = $total_count - ($page - 1) * $list_page_rows - $notice_count;
        $list[$i]['num'] = $list_num - $k;

        $list[$i]['icon_file'] = '<i class="fa fa-files-o" aria-hidden="true"></i>';

        //별도의 메타테이블에 저장된 데이터를 합친다
        $mrow = get_meta('board/'.$bo_table,$list[$i]['wr_id']);//,$code64=1
        if(@count($mrow)){
            foreach($mrow as $mk => $mv){
                $list[$i][$mk] = $mv;
            }
        }

        //관련파일 추출
        $sql = " SELECT * FROM {$g5['file_table']} 
                WHERE fle_db_table = 'board/{$bo_table}' AND fle_type = 'ref' AND fle_db_id = '".$list[$i]['wr_id']."' ORDER BY fle_reg_dt DESC ";
        $rs = sql_query($sql,1);
        $list[$i]['fcnt'] = $rs->num_rows;

        $i++;
        $k++;
    }
}
//print_r3($list);
g5_latest_cache_data($board['bo_table'], $list);

$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;page='.'&amp;'.$qstr);

$list_href = '';
$prev_part_href = '';
$next_part_href = '';
if ($is_search_bbs) {
    $list_href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table;

    $patterns = array('#&amp;page=[0-9]*#', '#&amp;spt=[0-9\-]*#');

    //if ($prev_spt >= $min_spt)
    $prev_spt = $spt - $config['cf_search_part'];
    if (isset($min_spt) && $prev_spt >= $min_spt) {
        $qstr1 = preg_replace($patterns, '', $qstr);
        $prev_part_href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;'.$qstr1.'&amp;spt='.$prev_spt.'&amp;page=1';
        $write_pages = page_insertbefore($write_pages, '<a href="'.$prev_part_href.'" class="pg_page pg_prev">이전검색</a>');
    }

    $next_spt = $spt + $config['cf_search_part'];
    if ($next_spt < 0) {
        $qstr1 = preg_replace($patterns, '', $qstr);
        $next_part_href = G5_USER_ADMIN_URL.'/bbs_board.php?bo_table='.$bo_table.'&amp;'.$qstr1.'&amp;spt='.$next_spt.'&amp;page=1';
        $write_pages = page_insertafter($write_pages, '<a href="'.$next_part_href.'" class="pg_page pg_end">다음검색</a>');
    }
}


$write_href = '';
if ($member['mb_level'] >= $board['bo_write_level']) {
    $write_href = short_url_clean(G5_USER_ADMIN_URL.'/bbs_write.php?bo_table='.$bo_table);
}

$nobr_begin = $nobr_end = "";
if (preg_match("/gecko|firefox/i", $_SERVER['HTTP_USER_AGENT'])) {
    $nobr_begin = '<nobr>';
    $nobr_end   = '</nobr>';
}

// RSS 보기 사용에 체크가 되어 있어야 RSS 보기 가능 061106
$rss_href = '';
/*
if ($board['bo_use_rss_view']) {
    $rss_href = G5_BBS_URL.'/rss.php?bo_table='.$bo_table;
}
*/
$stx = get_text(stripslashes($stx));
echo $field_prop;
include_once($board_skin_path.'/list.skin.php');