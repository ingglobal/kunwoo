<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 관리자인 경우만 접근 가능함, 관리자 아이디가 없으면 super 계정을 자동 입력함
// 목록보기 및 글쓰기 권한도 전부 8 이상이어야 함
// 한 페이지 리스트도 200개 정도로 넉넉하게 보여져야 함




// 기존 리스트 초기화
$list = array();
$i = 0;

if ($sca || $stx || $stx === '0') {     //검색이면
    $is_search_bbs = true;      //검색구분변수 true 지정
    $sql_search = get_sql_search($sca, $sfl, $stx, $sop);

    // 가장 작은 번호를 얻어서 변수에 저장 (하단의 페이징에서 사용)
    $sql = " select MIN(wr_num) as min_wr_num from {$write_table} ";
    $row = sql_fetch($sql);
    $min_spt = (int)$row['min_wr_num'];

    if (!$spt) $spt = $min_spt;

    $sql_search .= " and (wr_num between {$spt} and ({$spt} + {$config['cf_search_part']})) ";

    // 원글만 얻는다. (코멘트의 내용도 검색하기 위함)
    // 라엘님 제안 코드로 대체 http://sir.kr/g5_bug/2922
    $sql = " SELECT COUNT(DISTINCT `wr_parent`) AS `cnt` FROM {$write_table} WHERE {$sql_search} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
    /*
    $sql = " select distinct wr_parent from {$write_table} where {$sql_search} ";
    $result = sql_query($sql);
    $total_count = sql_num_rows($result);
    */
} else {
    $sql_search = "";

    $total_count = $board['bo_count_write'];
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
    $sst  = "wr_num DESC, wr_reply";

if ($sst) {
    $sql_order = " order by {$sst} {$sod} ";
}

if ($is_search_bbs) {
    $sql = " select distinct wr_parent from {$write_table} where {$sql_search} {$sql_order} limit {$from_record}, $page_rows ";
} else {
    $sql = " select * from {$write_table} where wr_is_comment = 0 ";
    if(!empty($notice_array))
        $sql .= " and wr_id not in (".implode(', ', $notice_array).") ";
    $sql .= " {$sql_order} limit {$from_record}, $page_rows ";
}


// 페이지의 공지개수가 목록수 보다 작을 때만 실행
//if($page_rows > 0) {
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
        $list_num = $total_count - ($page - 1) * $list_page_rows - $notice_count;
        $list[$i]['num'] = $list_num - $k;

        $i++;
        $k++;
    }
//}

$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, './board.php?bo_table='.$bo_table.$qstr.'&amp;page=');










// 게시물 메뉴 추출 재선언 쿼리(extend 확장함수와 동일)
$list = array();

// 게시물 추출
$tmp_write_table = $write_table; // 게시판 테이블 전체이름
$sql = "	SELECT wr1.wr_id, wr1.wr_reply, wr1.wr_subject, wr1.wr_link1, wr1.wr_1, wr1.wr_2, wr1.wr_3, wr1.wr_4, wr1.wr_5, wr1.wr_10
				,GROUP_CONCAT(wr2.wr_subject ORDER BY wr2.wr_reply SEPARATOR '^') AS group_subject
				,GROUP_CONCAT(wr2.wr_content ORDER BY wr2.wr_reply SEPARATOR '^') AS group_content
				,GROUP_CONCAT(wr2.wr_link1 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_link1
				,GROUP_CONCAT(wr2.wr_1 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_1
				,GROUP_CONCAT(wr2.wr_2 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_2
				,GROUP_CONCAT(wr2.wr_3 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_3
				,GROUP_CONCAT(wr2.wr_4 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_4
				,GROUP_CONCAT(wr2.wr_5 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_5
				,GROUP_CONCAT(wr2.wr_6 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_6
				,GROUP_CONCAT(wr2.wr_10 ORDER BY wr2.wr_reply SEPARATOR '^') AS group_wr_10
				,COUNT(wr2.wr_id) AS group_count
			FROM {$tmp_write_table} AS wr1
				JOIN {$tmp_write_table} AS wr2
			WHERE wr1.wr_is_comment = 0
                AND wr1.wr_9 = '' AND wr2.wr_9 = ''
				AND wr1.wr_num = wr2.wr_num
				AND wr2.wr_reply LIKE CONCAT(wr1.wr_reply,'%')
			GROUP BY wr1.wr_num, wr1.wr_reply
			ORDER BY wr1.wr_num DESC, wr1.wr_reply
";
$result = sql_query($sql);
for ($i=0; $row = sql_fetch_array($result); $i++) {
	if ($subject_len)
		$row['wr_subject'] = conv_subject($row['wr_subject'], $subject_len, '…');
	else
		$row['wr_subject'] = conv_subject($row['wr_subject'], $board['bo_subject_len'], '…');

	// 단계
	$row['wr_depth'] = strlen($row['wr_reply']);
	
	// 그룹 배열 
	$row['group_subject_items'] = explode('^', $row['group_subject']);
	$row['group_content_items'] = explode('^', $row['group_content']);
	for ($j=0; $j<count($row['group_content_items']); $j++) {
		$row['group_content_items'][$j] = unserialize($row['group_content_items'][$j]);
	}
	$row['group_link1_items'] = explode('^', $row['group_link1']);
	$row['group_wr_1_items'] = explode('^', $row['group_wr_1']);
	$row['group_wr_2_items'] = explode('^', $row['group_wr_2']);
	$row['group_wr_3_items'] = explode('^', $row['group_wr_3']);
	$row['group_wr_4_items'] = explode('^', $row['group_wr_4']);
	$row['group_wr_5_items'] = explode('^', $row['group_wr_5']);
	$row['group_wr_10_items'] = explode('^', $row['group_wr_10']);
	
    $list[$i] = $row;
}



// 네비 정보 출력 (관리자)
if(!function_exists('put_navi_item')){
function put_navi_item($row)
{
    // 배열전체를 복사
    $list = $row;
    unset($row);
//    print_r2($list);
    $list['wr_link1_display'] = (!$list['wr_link1']) ? 'none':'';
    $list['wr_link1'] = add_g5_url($list['wr_link1']);
    $list['wr_link1_text'] = '<a href="'.$list['wr_link1'].'" target="_blank" style="display:'.$list['wr_link1_display'].';"><img src="https://icongr.am/clarity/pop-out.svg?size=15&color=444444"></a>';
    
    // 항목 출력
    echo '
        <li class="dd-item dd3-item" data-id="'.$list['idx'].'" data-depth="'.$list['wr_depth'].'" data-wr_id="'.$list['wr_id'].'">
            <div class="dd-handle dd3-handle">Drag</div>
            <div class="dd3-content" wr_link1="'.$list['wr_link1'].'" wr_1="'.$list['wr_1'].'" wr_2="'.$list['wr_2'].'" wr_3="'.$list['wr_3'].'" wr_4="'.$list['wr_4'].'" wr_5="'.$list['wr_5'].'" wr_10="'.$list['wr_10'].'">'
                .'<span title="'.$list['wr_link1'].'">'.$list['wr_subject'].'</span>'
                .$list['wr_link1_text']
            .'</div>
    '.PHP_EOL;

    return $list;
}
}


// 게시판 reply 생성 함수
// 초기값 정의
//$g5['navi_re']['num'] = array();
//$g5['navi_re']['reply'] = array();
//$g5['navi_wr_num'] = 0;
if(!function_exists('get_reply')){
function get_reply($idx, $parent, $depth) {
    global $g5;
    
    // parent=0이면 num--
    if(!$parent)
        $g5['navi_wr_num']--;
    
    // reply 코드 앞부분은 부모코드
    $reply_char1 = $g5['navi_re']['reply'][$parent];

    // 부모코드로 시작 & 한단계 높은(정규식 regexp="/^정규식.$/") 배열들 전부 추출
    // reply 코드 뒷부분은 같은 단계의 맨 끝값을 추출해서 나중에 +1 코드로 만들어야 함
    foreach($g5['navi_re']['reply'] as $key=>$val) if(preg_match('/^'.$reply_char1.'.$/', $val)) {
        //echo $key.'='.$val.'<br>';
        //echo $g5['navi_re']['num'][$key].'<>'.$g5['navi_wr_num'].'<br>';
        // 같은 wr_num 그룹안에서만 찾아야 함
        if( $g5['navi_re']['num'][$key]==$g5['navi_wr_num'] ) {
            $reply_last = $val;
        }
    }
    // 같은 단계값이 없으면 초기값, 있으면 마지막 한문자값+1
    if (!$reply_last)
        $reply_char2 = 'A';
    else
        $reply_char2 = chr(ord( substr($reply_last,-1) ) + 1);

    $g5['navi_re']['num'][$idx] = $g5['navi_wr_num'];
    $g5['navi_re']['reply'][$idx] = ($depth) ? $reply_char1.$reply_char2 : '';
    
    return array($g5['navi_re']['num'][$idx], $g5['navi_re']['reply'][$idx]);
    
}
}


// 카테고리 코드 생성 함수
//$g5['navi_code'] = array();
if(!function_exists('get_code')){
function get_code($idx, $parent) {
    global $g5;
    
    // reply 코드 앞부분은 부모코드의 앞부분
//    $reply_char1 = substr($g5['navi_code'][$parent],0,-2);
    $reply_char1 = $g5['navi_code'][$parent];

    // 부모코드로 시작 + 두글자(정규식 regexp="/^정규식..$/") 배열들 전부 추출
    // reply 코드 뒷부분은 같은 단계의 맨 끝값을 추출해서 나중에 +00 코드로 만들어야 함
//    foreach($g5['navi_re']['reply'] as $key=>$val) if(preg_match('/^'.$reply_char1.'.$/', $val)) {
    foreach($g5['navi_code'] as $key=>$val) if(preg_match('/^'.$reply_char1.'..$/', $val)) {
        //echo $key.'='.$val.'<br>';
        $reply_last = $val;
    }
    // 같은 단계값이 없으면 초기값, 있으면 마지막 두문자값+00
    if (!$reply_last)
        $reply_char2 = '10';
    else {
        $reply_char2 = base_convert($reply_last, 36, 10);
        $reply_char2 += 36;
        $reply_char2 = base_convert($reply_char2, 10, 36);
        $reply_char2 = substr($reply_char2, -2);
    }

    $g5['navi_code'][$idx] = $reply_char1.$reply_char2;
    //print_r2($g5['navi_code']);

    return $g5['navi_code'][$idx];
    
}
}



// 디비에 정보 입력
if(!function_exists('update_navi')){
function update_navi($row) {
    global $g5;

    $list = $row; unset($row);
    
    $sql_common = "
                    wr_num = '".$list['wr_num']."',
                    wr_reply = '".$list['wr_reply']."',
                    wr_comment = 0,
                    wr_subject = '".$list['wr_subject']."',
                    wr_content = '".$list['wr_content']."',
                    wr_link1 = '".strip_g5_url($list['wr_link1'])."',
                    wr_link2 = '".strip_g5_url($list['wr_link2'])."',
                    mb_id = '".$member['mb_id']."',
                    wr_password = '".$member['mb_password']."',
                    wr_name = '".addslashes($member['mb_name'])."',
                    wr_last = '".G5_TIME_YMDHIS."',
                    wr_ip = '".$_SERVER['REMOTE_ADDR']."',
                    wr_1 = '".$list['wr_1']."',
                    wr_2 = '".$list['wr_2']."',
                    wr_3 = '".$list['wr_3']."',
                    wr_4 = '".$list['wr_4']."',
                    wr_5 = '".$list['wr_5']."',
                    wr_6 = '".$list['wr_6']."',
                    wr_7 = '".$list['wr_7']."',
                    wr_8 = '".$list['wr_8']."',
                    wr_9 = '".$list['wr_9']."',
                    wr_10 = '".$list['wr_10']."'
    ";
    
    $write_table = $g5['write_prefix'].$list['bo_table'];
    $sql = " SELECT *
                FROM {$write_table}
                WHERE wr_id = '".$list['wr_id']."'
    ";
    $wr1 = sql_fetch($sql,1);
    if(!$wr1['wr_id'] || !$list['wr_id']) {
        $sql = " INSERT INTO {$write_table} SET
                    {$sql_common} 
                    , wr_datetime = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql,1);
        $wr_id = sql_insert_id();
    }
    else {
        $sql = " UPDATE {$write_table} SET
                    {$sql_common}
                    WHERE wr_id = '".$wr1['wr_id']."' 
        ";
        sql_query($sql,1);
        $wr_id = $wr1['wr_id'];
    }
//	echo $sql.'<br>';

    // wr_parent 업데이트
    $sql = " UPDATE {$write_table} SET wr_parent = wr_id ";
    sql_query($sql,1);
 
    return $wr_id;
}
}



?>