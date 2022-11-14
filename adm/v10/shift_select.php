<?php
// 호출페이지들
// /adm/v10/order_out_practice_form.php: 실행계획폼
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

$sql_common = " FROM {$g5['shift_table']} AS shf
                    LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = shf.mms_idx
";

$where = array();
// 디폴트 검색조건
$where[] = " shf_status NOT IN ('trash','delete') ";

// 업체조건 (관리 권한이 있는 경우)
if($member['mb_manager_yn']) {
    $com_idx = $_REQUEST['com_idx'] ?: $_SESSION['ss_com_idx'];
    $where[] = " shf.com_idx = '".$com_idx."' ";
}
else {
    $where[] = " shf.com_idx = '".$member['com_idx']."' ";
}

// 검색어 설정
if ($sch_word != "") {
    switch ($sch_field) {
		case ( $sch_field == 'mms_type' ) :
			$where[] = " mms_keys REGEXP 'mms_type=[가-힝]*(".trim($sch_word).")+[가-힝]*:' ";
            break;
		case ( $sch_field == 'mms_idx' ) :
			$where[] = " shf.mms_idx = '".trim($sch_word)."' ";
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
$sql_order = " ORDER BY shf.mms_idx, shf_start_dt ";


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

$qstr = 'frm='.$frm.'&file_name='.$file_name.'&com_idx='.$com_idx;
$qstr1 = $qstr.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word);

$g5['title'] = '작업구간 ('.number_format($total_count).')';
include_once('./_head.sub.php');
?>
<style>
.td_shf_name,
.td_mms_name {text-align:left !important;}
</style>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">

    <div id="scp_list_find">
        <select name="sch_field" id="sch_field">
            <option value="mms_name">설비명</option>
            <option value="mms_model">모델명</option>
            <option value="mms_memo">설비메모</option>
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
            <th scope="col">구간명</th>
            <th scope="col">설비명</th>
            <th scope="col">적용기간</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            // 적용기간
            $row['shf_period'] = $row['shf_period_type'] ? '전체기간' : $row['shf_start_dt'].'<br>~'.$row['shf_end_dt'];

            // 설비명
            $row['mms_name'] = !$row['mms_idx'] ? '전체' : $row['mms_name'];

        ?>
        <tr>
            <td class="td_shf_name"><?php echo $row['shf_name']; ?></td>
            <td class="td_mms_name"><?php echo $row['mms_name']; ?></td>
            <td class="td_period">
                <?php echo $row['shf_period']; ?>
            </td>
            <td class="td_select">
                <button type="button" class="btn btn_03 btn_select"
                    mms_idx="<?=$row['mms_idx']?>"
                    mms_name="<?=$row['mms_name']?>"
                    shf_name="<?=$row['shf_name']?>"
                    shf_idx="<?=$row['shf_idx']?>">선택</button>
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
    </div>

</div>

<script>
$('.btn_select').click(function(e){
    e.preventDefault();
    var mms_idx = $(this).attr('mms_idx');
    var mms_name = $(this).attr('mms_name');  // 
    var shf_idx = $(this).attr('shf_idx');
    var shf_name = $(this).attr('shf_name');

    <?php
    // 기종별목표설정 수정
    if($file_name=='shift_item_goal_form') {
    ?>
        $("input[name=mms_idx]", opener.document).val( mms_idx );
        $("input[name=mms_name]", opener.document).val( mms_name );
        $("input[name=shf_idx]", opener.document).val( shf_idx );
        $("input[name=sig_shf_no]", opener.document).val( shf_no );
        $(".mms_name", opener.document).text( mms_name );
        $(".shift_goal", opener.document).text( thousand_comma(shf_target) );
        
    <?php
    }
    // 실행계획 폼
    if($file_name=='order_out_practice_form') {
    ?>
        $("input[name=shf_idx]", opener.document).val( shf_idx );
        $("input[name=shf_name]", opener.document).val( shf_name );
    <?php
    }
    // 게시판 글쓰기
    if($file_name=='write'||$file_name=='error_code_form') {
    ?>
        $("input[name=com_idx]", opener.document).val( com_idx );
        $("input[name=com_name]", opener.document).val( com_name );
        $("input[name=mms_idx]", opener.document).val( mms_idx );
        $("input[name=mms_name]", opener.document).val( mms_name );
        $("#mms_info", opener.document).hide();
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>