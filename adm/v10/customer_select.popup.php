<?php
// 호출 페이지들
// theme/**/skin/board/intra21/write.skin.php 게시물 등록시 고객찾기
include_once('./_common.php');

if($member['mb_level']<6)
	alert_close('접근할 수 없는 메뉴입니다.');


$sql_common = " FROM {$g5['member_table']} AS mb 
                LEFT JOIN {$g5['company_member_table']} AS cmm ON cmm.mb_id = mb.mb_id
                LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = cmm.com_idx
"; 

// 기본 검색
$where = array();
$where[] = " mb_level = 4 ";   // 디폴트 검색조건

// 운영권한이 없으면 자기 업체만 
if (!$member['mb_manager_yn']) {
    // company_saler 교차 테이블에서 내 것만 추출
    $where[] = " cmm.com_idx IN ( SELECT com.com_idx
        FROM {$g5['company_table']} AS com
            LEFT JOIN {$g5['company_saler_table']} AS cms ON cms.com_idx = com.com_idx
        WHERE mb_id_saler = '".$member['mb_id']."'
        GROUP BY com.com_idx ) ";
}


// 검색어 설정
if ($sch_word != "") {
    switch ($sch_field) {
		case ( $sch_field == 'mb_point' ) :
			$where[] = " {$sch_field} >= '".trim($sch_word)."' ";
            break;
		case ( $sch_field == 'mb_level' ) :
			$where[] = " {$sch_field} = '".trim($sch_word)."' ";
            break;
		case ( $sch_field == 'mb_hp' ) :
			$where[] = " $sch_field LIKE '%".trim($sch_word)."%' ";
            break;
        default :
			$where[] = " $sch_field LIKE '%".trim($sch_word)."%' ";
            break;
    }
}
else 
    $sch_field = 'com_name';

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

$sql_group = " GROUP BY mb.mb_id ";

// 정렬 설정
if (!$sst)
	$sql_order = " ORDER BY mb_datetime DESC, mb_no DESC ";
else 
	$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = " SELECT SQL_CALC_FOUND_ROWS *
		{$sql_common}
		{$sql_search} {$sql_group} {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
$result = sql_query($sql,1);
//echo $sql;
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$config['cf_write_pages'] = $config['cf_mobile_pages'] = 5;

// counter display for manager
$total_count_display = ($member['mb_manager_yn']) ? ' ('.number_format($total_count).')' : '';

$g5['title'] = '고객검색'.$total_count_display;
include_once('./_head.sub.php');

$qstr1 = 'frm='.$frm.'&d='.$d.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word);
?>
<style>
.td_com_tel, .td_com_president {white-space:nowrap;}
</style>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?>
        <div class="site_intra_tabs">
        </div>
    </h1>

    <div class="local_desc01 local_desc">
        <p>등록된 고객을 검색하고 선택하세요. 연결된 업체가 없는 고객은 선택할 수 없습니다.</p>
        <p>고객관리 및 업체관리는 [고객관리] 메뉴에서 해 주시면 됩니다.</p>
    </div>

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="d" value="<?php echo $_REQUEST['d']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">

    <div id="scp_list_find">
        <select name="sch_field" id="sch_field">
            <option value="com_name">업체명</option>
            <option value="mb_name">고객명</option>
            <option value="com_president">대표자</option>
        </select>
        <script>$('select[name=sch_field]').val('<?php echo $sch_field?>').attr('selected','selected')</script>
        <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required size="20">
        <input type="submit" value="검색" class="btn_frmline btn btn_10">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?frm=<?php echo $_REQUEST['frm']?>&d=<?php echo $_REQUEST['d']?>" class="btn btn_b10">검색취소</a>
    </div>
    
    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col">고객명</th>
            <th scope="col">직함</th>
            <th scope="col">업체명</th>
            <th scope="col">날짜</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
//            print_r2($row);
        ?>
        <tr>
            <td class="td_mb_name"><?=$row['mb_name']?></td>
            <td class="td_cmm_title"><?=$g5['set_mb_ranks_value'][$row['cmm_title']]?></td>
            <td class="td_com_name"><?php echo $row['com_name']; ?></td>
            <td class="td_cmm_reg_dt"><?=substr($row['cmm_reg_dt'],2,8)?></td>
            <td class="td_mng td_mng_s">
                <button type="button" class="btn btn_03 btn_select" style="display:<?=(!$row['com_idx'])?'none':''?>;"
                    mb_id="<?php echo $row['mb_id']?>"
                    mb_name="<?php echo $row['mb_name']?>"
                    com_idx="<?php echo $row['com_idx']; ?>"
                    com_name="<?php echo $row['com_name']; ?>"
                    com_president="<?php echo $row['com_president']; ?>"
                    com_tel="<?=$row['com_tel']?>"
                    com_email="<?=$mb2['com_email']?>"
                    cmm_idx="<?php echo $row['cmm_idx']; ?>"
                    cmm_rank="<?php echo $row['cmm_title']; ?>"
                    cmm_name_rank="<?php echo $row['mb_name'].' '.$g5['set_mb_ranks_value'][$row['cmm_title']]; ?>"
                >
                선택
                </button>
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
    <?php
    // 게시판 글쓰기
    if($file_name=='write') {
    ?>
        $("input[name=cmm_idx]", opener.document).val( $(this).attr('cmm_idx') );
        $("input[name=com_idx]", opener.document).val( $(this).attr('com_idx') );
        $("input[name=com_name]", opener.document).val( $(this).attr('com_name') );
        $("input[name=mb_id]", opener.document).val( $(this).attr('mb_id') );
        $("input[name=mb_id_customer]", opener.document).val( $(this).attr('mb_id') );
        $("input[name=mb_name_customer]", opener.document).val( $(this).attr('mb_name') );
    
        // 고객 내용 입력
        customer_info = '<b>고객명:</b> '+$(this).attr('cmm_name_rank');
        customer_info += ' <span class="div_com_president">(<b>업체명:</b> '+$(this).attr('com_name');
        customer_info += ', <b>대표자:</b> '+$(this).attr('com_president')+')</span>';
        $(".customer_info", opener.document).html( customer_info );

    <?php
    }
    // 
    else if($file_name=='write2') {
    ?>
        
    <?php
    }
    ?>
    
    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>