<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 추가 검색 설정, sql_search 변수에 따라 AND 문장 여부가 결정되므로 여기 위치에 들어가야 됨 ---------------------------------------
$where2 = array();
// 관리자 레벨이 아니면 자기 조직 것만 리스트에 나옴, 2=회원,4=업체,6=영업자,8=관리자,10=수퍼관리자
if (auth_check($auth[$sub_menu],'d',1) && !$is_admin) {
    // 그룹 공지까지 다 봐야 함: 매개변수 2로 넘기면 그룹전체
    if(get_dept_idxs(2)) {
        $where2[] = " wr_7 IN (".get_dept_idxs(2).") ";
    }
}
// 추가 다른 조건이 있는 경우
if($ser_com_idx) {
    $where2[] = " wr_2 = '".$ser_com_idx."' ";
}
if($fr_date) {
    $where2[] = " wr_datetime >= '".$fr_date." 00:00:00' ";
}
if($to_date) {
    $where2[] = " wr_datetime <= '".$to_date." 23:59:59' ";
}
if($pl_date) {
    $where2[] = " wr_6 = '".$pl_date."' ";
}
if($sch_wr_10) {
    $where2[] = " wr_10 = '".$sch_wr_10."' ";
}
if($sch_mb_name_worker=='pending') {
    $where2[] = " wr_8 LIKE '%:mb_name_worker=:%' OR wr_8 = '' ";
}
else if($sch_mb_name_worker=='asigned') {
    $where2[] = " wr_8 REGEXP 'mb_name_worker=[가-힝]+:' ";
}
if($sch_wr_5) {
    $where2[] = " wr_5 = '".$sch_wr_5."' ";
}
// 최종 WHERE 생성
if ($where2) {
    //검색 조건이 이미 있으면 끝에 AND를 붙여서 sql_search와 연결해야 함
    $sql_search_and = ($sca || $stx || $stx === '0') ? ' AND ' : '';
    $sql_where2 = implode(' AND ', $where2).$sql_search_and;
}
//echo $sql_where2;
//print_r3($where2);
// 추가 검색 설정 ---------------------------------------


//qstr 조건 추가는 관리자단 /adm/v10/_head 상단에 추가되었습니다.



if ($sca || $stx || $stx === '0') {     //검색이면
    $is_search_bbs = true;      //검색구분변수 true 지정
    $sql_search = get_sql_search($sca, $sfl, $stx, $sop);

    // 가장 작은 번호를 얻어서 변수에 저장 (하단의 페이징에서 사용)
    $sql = " select MIN(wr_num) as min_wr_num from {$write_table} ";
    $row = sql_fetch($sql);
    $min_spt = (int)$row['min_wr_num'];

    if (!$spt) $spt = $min_spt;

    $sql_search .= " and (wr_num between {$spt} and ({$spt} + {$config['cf_search_part']})) ";

    // 총글수: 원글만 얻는다. (코멘트의 내용도 검색하기 위함)
    $sql = " SELECT COUNT(DISTINCT `wr_parent`) AS `cnt` FROM {$write_table} WHERE {$sql_where2} {$sql_search} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
}
// 조직 조건만 있으면 total_count 재설정
else if($sql_where2) {
    // 총글수
    $sql = " SELECT COUNT(DISTINCT `wr_parent`) AS `cnt` FROM {$write_table} WHERE {$sql_where2} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
} else {
    $sql_search = "";

    $total_count = $board['bo_count_write'];
}


if(G5_IS_MOBILE) {
    $page_rows = $board['bo_mobile_page_rows'];
    $list_page_rows = $board['bo_mobile_page_rows'];
} else {
    $page_rows = $board['bo_page_rows'];
    $list_page_rows = $board['bo_page_rows'];
}


// 기존 리스트 초기화
$list = array();
$i = 0;
$notice_count = 0;
$notice_array = array();

// 공지 처리
if (!$is_search_bbs) {
    $arr_notice = explode(',', trim($board['bo_notice']));

    // 조직 검색이 있는 경우 예외 처리를 해야 하므로 재설정
    if($sql_where2) {
        $wr_ids = sql_fetch(" select GROUP_CONCAT(wr_id) AS wr_ids from {$write_table} where {$sql_where2} AND wr_id IN (".implode(",",$arr_notice).") ");
        $arr_notice = explode(',', trim($wr_ids['wr_ids']));
    }
    //print_r2($arr_notice);
    
    $from_notice_idx = ($page - 1) * $page_rows;
    if($from_notice_idx < 0)
        $from_notice_idx = 0;
    $board_notice_count = count($arr_notice);

    for ($k=0; $k<$board_notice_count; $k++) {
        if (trim($arr_notice[$k]) == '') continue;

        $row = sql_fetch(" select * from {$write_table} where wr_id = '{$arr_notice[$k]}' ");

        if (!$row['wr_id']) continue;

        $notice_array[] = $row['wr_id'];

        if($k < $from_notice_idx) continue;

        $list[$i] = get_list($row, $board, $board_skin_url, G5_IS_MOBILE ? $board['bo_mobile_subject_len'] : $board['bo_subject_len']);
        $list[$i]['is_notice'] = true;
        // 끝에 wr_id=45&sst=text 이렇게 &가 들어가야 하는데 없어서 추가함
        $list[$i]['href'] = G5_BBS_URL.'/board.php?bo_table='.$board['bo_table'].'&amp;wr_id='.$list[$i]['wr_id'].'&'.$qstr;
        //echo $list[$i]['href'].'<br>';

        $i++;
        $notice_count++;

        if($notice_count >= $list_page_rows)
            break;
    }
}


if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

// 공지글이 있으면 변수에 반영
if(!empty($notice_array)) {
    $from_record -= count($notice_array);

    if($from_record < 0)
        $from_record = 0;

    if($notice_count > 0)
        $page_rows -= $notice_count;

    if($page_rows < 0)
        $page_rows = $list_page_rows;
}



// 정렬
// 인덱스 필드가 아니면 정렬에 사용하지 않음
//if (!$sst || ($sst && !(strstr($sst, 'wr_id') || strstr($sst, "wr_datetime")))) {
if (!$sst) {
    if ($board['bo_sort_field']) {
        $sst = $board['bo_sort_field'];
    } else {
        $sst  = "wr_num, wr_reply";
        $sod = "";
    }
} else {
    // 게시물 리스트의 정렬 대상 필드가 아니라면 공백으로 (nasca 님 09.06.16)
    // 리스트에서 다른 필드로 정렬을 하려면 아래의 코드에 해당 필드를 추가하세요.
    // $sst = preg_match("/^(wr_subject|wr_datetime|wr_hit|wr_good|wr_nogood)$/i", $sst) ? $sst : "";
    $sst = preg_match("/^(wr_datetime|wr_hit|wr_good|wr_nogood)$/i", $sst) ? $sst : "";
}

if(!$sst)
    $sst  = "wr_num, wr_reply";

if ($sst) {
    $sql_order = " order by {$sst} {$sod} ";
}

if ($is_search_bbs) {
    $sql = " select distinct wr_parent from {$write_table} where {$sql_where2} {$sql_search} {$sql_order} limit {$from_record}, $page_rows ";
} else {
    $sql_where2 = ($sql_where2) ? $sql_where2.' AND' : '';
    $sql = " select * from {$write_table} where {$sql_where2} wr_is_comment = 0 ";
    if(!empty($notice_array))
        $sql .= " and wr_id not in (".implode(', ', $notice_array).") ";
    $sql .= " {$sql_order} limit {$from_record}, $page_rows ";
}
//echo $sql.'<br>';


// 페이지의 공지개수가 목록수 보다 작을 때만 실행
if($page_rows > 0) {
    $result = sql_query($sql);

    $k = 0;

    while ($row = sql_fetch_array($result))
    {
        // 검색일 경우 wr_id만 얻었으므로 다시 한행을 얻는다
        if ($is_search_bbs)
            $row = sql_fetch(" select * from {$write_table} where wr_id = '{$row['wr_parent']}' ");

        $list[$i] = get_list($row, $board, $board_skin_url, G5_IS_MOBILE ? $board['bo_mobile_subject_len'] : $board['bo_subject_len']);
        if (strstr($sfl, 'subject')) {
            $list[$i]['subject'] = search_font($stx, $list[$i]['subject']);
        }
        $list[$i]['is_notice'] = false;
        //echo $total_count .' - ('. $page .'-1) * '. $list_page_rows .' - '. $notice_count.' - '.$k.'<br>';
        $list_num = $total_count - ($page - 1) * $list_page_rows - $notice_count;
        $list[$i]['num'] = $list_num - $k;
        // 끝에 wr_id=45&sst=text 이렇게 &가 들어가야 하는데 없어서 추가함
        $list[$i]['href'] = G5_BBS_URL.'/board.php?bo_table='.$board['bo_table'].'&amp;wr_id='.$list[$i]['wr_id'].'&'.$qstr;
        //echo $list[$i]['href'].'<br>';

        $i++;
        $k++;
    }
}

$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './board.php?bo_table='.$bo_table.'&'.$qstr.'&amp;page=');


$write_href = '';
if ($member['mb_level'] >= $board['bo_write_level']) {
    $write_href = './write.php?bo_table='.$bo_table;
    $write_href .= '&ser_com_idx='.$ser_com_idx;
}






// 디비 추가 필드 설정
$q = sql_query( 'DESCRIBE '.$write_table );
while($row = sql_fetch_array($q)) {
    // 필드 변경
    if($row['Field']=='wr_9' && $row['Type']=='varchar(255)') {
        //echo $row['Field'].' - '.$row['Type'].'<br>';
        sql_query(" ALTER TABLE `{$write_table}` CHANGE `wr_9` `wr_9` longtext ", true);
    }
    $db_fields[] = $row['Field'];
}
// 필드 추가 (뒤에 넣을 것을 먼저 선언하세요. AFTER 밖에 없어서)
if ( !in_array('wr_dept_writer',$db_fields) ) {
    sql_query(" ALTER TABLE `{$write_table}` ADD `wr_dept_writer` int(11) NOT NULL DEFAULT '0' AFTER `wr_twitter_user` ", true);
}
//if ( !in_array('wr_dept_worker',$db_fields) ) {
//    sql_query(" ALTER TABLE `{$write_table}` ADD `wr_dept_worker` int(11) NOT NULL DEFAULT '0' AFTER `wr_dept_writer` ", true);
//}


?>