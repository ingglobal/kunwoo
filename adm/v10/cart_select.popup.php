<?php
// 호출페이지들
// theme/**/skin/board/intra20/write.skin.php 게시물 등록시 (주문)상품찾기
// /adm/v10/domain_form.php: 상품찾기
// /adm/v10/site_form.php: 사이트관리 (신청)상품찾기
include_once('./_common.php');

if($member['mb_level']<6)
	alert_close('접근할 수 없는 메뉴입니다.');

$sql_common = " FROM {$g5['g5_shop_cart_table']} ";

$where = array();
// 디폴트 검색조건
$where[] = " ct_status NOT IN ('쇼핑') ";

// 운영자 그룹은 전체 주문상품 모두
if($member['mb_manager_yn']||$member['mb_account_yn']) {
    
}
else if(get_dept_idxs($level)) {
    $where[] = " trm_idx_department IN (".get_dept_idxs($level).") ";
}
// 회원인 경우는 자기 것만
else {
    $where[] = " mb_id_saler = '".$member['mb_id']."' ";
}

// 검색어 설정
if ($sch_word != "") {
    switch ($sch_field) {
		case ( $sch_field == 'com_name' ) :
			$where[] = " ct_keys REGEXP 'com_name=[가-힝]*(".trim($sch_word).")+[가-힝]*:' ";
            break;
		case ( $sch_field == 'mb_name_saler' ) :
			$where[] = " ct_keys REGEXP 'mb_name_saler=[가-힝]*(".trim($sch_word).")+[가-힝]*:' ";
            break;
		case ( $sch_field == 'com_idx' || $sch_field == 'od_id' ) :
			$where[] = " $sch_field = '".trim($sch_word)."' ";
            break;
        default :
			$where[] = " $sch_field LIKE '%".trim($sch_word)."%' ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


// 정렬기준
$sql_order = " ORDER BY ct_id DESC ";


// 테이블의 전체 레코드수
$sql = " SELECT COUNT(*) AS cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$config['cf_write_pages'] = $config['cf_mobile_pages'] = 5;

// 리스트 쿼리
$sql = "SELECT *
        " . $sql_common . $sql_search . $sql_order . "
        LIMIT $from_record, $rows
";
//echo $sql;
$result = sql_query($sql);

$qstr1 = 'frm='.$frm.'&file_name='.$file_name.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word);

$g5['title'] = '(신청)상품검색 ('.number_format($total_count).')';
include_once('./_head.sub.php');
?>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">

    <div id="scp_list_find">
        <select name="sch_field" id="sch_field">
            <option value="com_name">업체명</option>
            <option value="it_name">상품명</option>
            <option value="mb_name_saler">영업자</option>
            <option value="mb_id_saler">영업자아이디</option>
            <option value="mb_id">고객아이디</option>
            <option value="com_idx">업체고유번호</option>
            <option value="od_id">접수번호</option>
        </select>
        <script>$('select[name=sch_field]').val('<?php echo $sch_field?>').attr('selected','selected')</script>
        <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required size="20">
        <input type="submit" value="검색" class="btn_frmline">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?frm=<?php echo $_REQUEST['frm']?>&is_admin2=<?php echo $_REQUEST['is_admin2']?>" class="btn btn_b10">검색취소</a>
    </div>
    
    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col">업체명</th>
            <th scope="col">상품명</th>
            <th scope="col">가격</th>
            <th scope="col">상태</th>
            <th scope="col">영업자</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $row['mta'] = get_meta('shop_cart',$row['ct_id']);
            //print_r2($row['mta']);
        ?>
        <tr>
            <td class="td_com_name"><?php echo $row['mta']['com_name']; ?></td>
            <td class="td_it_name"><?php echo $row['it_name']; ?></td>
            <td class="td_ct_price"><?php echo number_format($row['ct_price']); ?></td>
            <td class="td_ct_status"><?php echo $row['ct_status']; ?></td>
            <td class="td_mb_name_saler"><?php echo $row['mta']['mb_name_saler']; ?></td>
            <td class="td_mng td_mng_s" ct_id="<?php echo $row['ct_id']; ?>"
                                        mb_id="<?php echo $row['mb_id']; ?>"
                                        com_idx="<?php echo $row['com_idx']; ?>"
                                        com_name="<?php echo $row['mta']['com_name']; ?>"
                                        mb_id_saler="<?php echo $row['mb_id_saler']; ?>"
                                        mb_name_saler="<?php echo $row['mta']['mb_name_saler']; ?>"
                                        trm_idx_department_saler="<?php echo $row['trm_idx_department'];?>">
                <button type="button" class="btn btn_03 btn_select">선택</button>
            </td>
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="6" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr1.'&amp;page='); ?>

    <div class="win_btn ">
        <button type="button" onclick="window.close();" class="btn btn-secondary">닫기</button>
    </div>
</div>

<script>
$('.btn_select').click(function(e){
    e.preventDefault();
    var it_name = $(this).closest('tr').find('.td_it_name').text(); // 상품명
    var it_price = $(this).closest('tr').find('.td_ct_price').text(); // 가격
    var ct_status = $(this).closest('tr').find('.td_ct_status').text(); // 상태
    var com_idx = $(this).closest('td').attr('com_idx');
    var com_name = $(this).closest('td').attr('com_name');  // 업체명
    var mb_id = $(this).closest('td').attr('mb_id');    // 고객 아이디
    var mb_id_saler = $(this).closest('td').attr('mb_id_saler');    // 영업자 아이디
    var mb_name_saler = $(this).closest('td').attr('mb_name_saler');    // 영업자명
    var trm_idx_department_saler = $(this).closest('td').attr('trm_idx_department_saler');
    var ct_id = $(this).closest('td').attr('ct_id');
    <?php
    // 게시판 글쓰기
    if($file_name=='write') {
        ?>

        // 폼이 존재하면
        if( $("form[name=<?php echo $frm;?>]", opener.document).length > 0 ) {
            $("input[name=ct_id]", opener.document).val( ct_id );
            $("input[name=com_idx]", opener.document).val( com_idx );
            $("input[name=com_name]", opener.document).val( com_name );
            $("input[name=mb_id]", opener.document).val( mb_id );
            $("input[name=mb_id_saler]", opener.document).val( mb_id_saler );
            $("input[name=mb_name_saler]", opener.document).val( mb_name_saler );
            $("input[name=trm_idx_department_saler]", opener.document).val( trm_idx_department_saler );
            ct_info = '<b>상품:</b> '+it_name+'('+it_price+'), ';
            ct_info += '<b>업체명:</b> '+com_name+', ';
            ct_info += '<b>영업자:</b> '+mb_name_saler;
            $("#ct_info", opener.document).html( ct_info );

            console.log(ct_info);
        }
        else {
            alert('값을 전달할 폼이 존재하지 않습니다.');
        }

        <?php
    }
    // 사이트 등록, 수정
    else if($file_name=='site_form') {
    ?>
        $("input[name=ct_id]", opener.document).val( ct_id );
        $("input[name=com_idx]", opener.document).val( com_idx );
        $("input[name=com_name]", opener.document).val( com_name );
        $("input[name=mb_id]", opener.document).val( mb_id );
        $("input[name=mb_id_saler]", opener.document).val( mb_id_saler );
        $("input[name=mb_name_saler]", opener.document).val( mb_name_saler );
        $("input[name=trm_idx_department]", opener.document).val( trm_idx_department_saler );
        ct_info = '<span style="font-size:1.6em;"> '+it_name+'</span> ('+it_price+', '+ct_status+')';
        ct_info += '<br><b>업체명:</b> '+com_name;
        ct_info += '<br>영업자: <b>'+mb_name_saler+'</b>';
        $("#ct_info", opener.document).html( ct_info );
        
    <?php
    }
    // 도메인 등록, 수정
    else if($file_name=='domain_form') {
    ?>
        $("input[name=ct_id]", opener.document).val( ct_id );
        $("input[name=com_idx]", opener.document).val( com_idx );
        $("input[name=com_name]", opener.document).val( com_name );
        $("input[name=mb_id]", opener.document).val( mb_id );
        $("input[name=mb_id_saler]", opener.document).val( mb_id_saler );
        $("input[name=mb_name_saler]", opener.document).val( mb_name_saler );
        $("input[name=trm_idx_department]", opener.document).val( trm_idx_department_saler );
        ct_info = '<span style="font-size:1.6em;"> '+it_name+'</span> ('+it_price+', '+ct_status+')';
        ct_info += '<br><b>업체명:</b> '+com_name;
        ct_info += '<br>영업자: <b>'+mb_name_saler+'</b>';
        $("#ct_info", opener.document).html( ct_info );
        
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>