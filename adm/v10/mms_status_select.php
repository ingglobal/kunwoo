<?php
// 호출페이지들
// /adm/v10/data_downtime_form.php: 항목검색
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

$sql_common = " FROM {$g5['mms_status_table']} AS mst 
                LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = mst.mms_idx
"; 

$where = array();
// 디폴트 검색조건
$where[] = " mst_status NOT IN ('trash','delete') AND mst_type = 'offwork' ";   // 디폴트 검색조건
$where[] = " mst.mms_idx IN ( SELECT mms_idx FROM {$g5['mms_table']} WHERE mms_status NOT IN('trash','delete') AND com_idx = '".$_SESSION['ss_com_idx']."' ) ";

// 검색어 설정
if ($sch_word != "") {
    switch ($sch_field) {
		case ( $sch_field == 'com_idx' ) :
			$where[] = " com_idx = '".trim($sch_word)."' ";
            break;
        default :
			$where[] = " $sch_field LIKE '%".trim($sch_word)."%' ";
            break;
    }
}
else
    $sch_field = 'mms_name';

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


// 정렬기준
$sql_order = " ORDER BY mst_idx ";


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
// echo $sql;
$result = sql_query($sql,1);

$qstr = 'frm='.$frm.'&file_name='.$file_name;
$qstr1 = $qstr.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word);

$g5['title'] = '비가동 항목 ('.number_format($total_count).')';
include_once('./_head.sub.php');
?>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">

    <div id="scp_list_find">
        <select name="sch_field" id="sch_field">
            <option value="mms_name">설비명</option>
            <option value="mst_name">비가동항목명</option>
            <option value="mst.mms_idx">설비번호</option>
        </select>
        <script>$('select[name=sch_field]').val('<?php echo $sch_field?>').attr('selected','selected')</script>
        <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required size="20">
        <input type="submit" value="검색" class="btn_frmline">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?<?php echo $qstr?>" class="btn btn_b10">검색취소</a>
    </div>
    
    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col">설비명</th>
            <th scope="col">비가동항목명</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
        ?>
        <tr>
            <td class="td_mms_name"><?php echo $row['mms_name']; ?></td>
            <td class="td_mst_name"><?php echo $row['mst_name']; ?></td>
            <td class="td_mng td_mng_s" mst_idx="<?php echo $row['mst_idx']; ?>"
                                        mst_name="<?php echo $row['mst_name']; ?>">
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
        <button type="button" onclick="window.close();" class="btn btn_close">창닫기</button>
    </div>

    <div class="btn_fixed_top">
        <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
        <?php if($member['mb_manager_yn']) { ?>
        <a href="./company_select.popup.php?file_name=<?=$g5['file_name']?>" id="btn_company" class="btn btn_03">업체검색</a>
        <?php } ?>
    </div>

</div>

<script>
// 업체검색
$("#btn_company").click(function() {
    var href = $(this).attr("href");
    winCompany = window.open(href, "winCompany", "left=70,top=70,width=520,height=600,scrollbars=1");
    winCompany.focus();
    return false;
});

$('.btn_select').click(function(e){
    e.preventDefault();
    var mst_idx = $(this).closest('td').attr('mst_idx');
    var mst_name = $(this).closest('td').attr('mst_name');  // 

    <?php
    // 부속품 수정
    if($file_name=='data_offwork_form') {
    ?>
        $("input[name=mst_idx]", opener.document).val( mst_idx );
        $("input[name=mst_name]", opener.document).val( mst_name );
    <?php
    }
    else if($file_name=='data_downtime_form') {
    ?>
        $("input[name=mst_idx]", opener.document).val( mst_idx );
        $("input[name=mst_name]", opener.document).val( mst_name );
        
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>