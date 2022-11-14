<?php
// 호출 페이지들
// /adm/v10/bom_form.php: 제품(BOM)수정: 거래처찾기
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

$where = array();
$where[] = "com_idx = '".$_SESSION['ss_com_idx']."' AND orp_status NOT IN ('trash','delete','del','cancel') ";   // 디폴트 검색조건

//$sch_field = ($w == '') ? 'orp_idx' : $sch_field;

// 운영권한이 없으면 자기것만
/*
if (!$member['mb_manager_yn']) {
    $where[] = " mb_id_saler = '".$member['mb_id']."' ";
}
*/

$sql_common = " FROM {$g5['order_practice_table']} AS orp ";
//print_r2($g5['line_reverse']);
if ($sch_word) {
    switch ($sch_field) {
		case ( $sch_field == 'orp_idx' ) :
            $where[] = " orp_idx = '{$sch_word}' ";
            break;
        case ( $sch_field == 'orp_order_no' ) :
            $where[] = " orp_order_no = '{$sch_word}' ";
            break;
        case ( $sch_field == 'trm_idx_line_name' ) :
            $sch_word_idx = $g5['line_reverse'][$sch_word];
            $where[] = " trm_idx_line = '{$sch_word_idx}' ";
            break;
        case ( $sch_field == 'orp_start_date' ) :
            $where[] = " orp_start_date = '{$sch_word}' ";
            break;
        default :
			$where[] = " $sch_field LIKE '%".trim($sch_word)."%' ";
            break;
    }
}
else
    $sch_field = 'orp_idx';

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "orp_start_date desc, orp_reg_dt desc";
    $sod = "";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS *
		{$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows}
";
//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") );
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$config['cf_write_pages'] = $config['cf_mobile_pages'] = 5;

// counter display for manager
$total_count_display = ($member['mb_manager_account_yn']) ? ' ('.number_format($total_count).')' : '';

$g5['title'] = '생산계획 검색'.$total_count_display;
include_once('./_head.sub.php');

$qstr1 = 'frm='.$frm.'&d='.$d.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word).'&file_name='.$file_name;

add_stylesheet('<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-darkness/jquery-ui.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.structure.min.css">', 1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.min.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker-ko.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker.js"></script>',1);
?>
<style>
.td_com_tel, .td_com_president {white-space:nowrap;}
</style>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <form name="ftarget" method="get" autocomplete="off">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_GET['file_name']; ?>">
    <input type="hidden" name="d" value="<?php echo $_REQUEST['d']; ?>">

    <div id="scp_list_find">
        <select name="sch_field" id="sch_field">
            <option value="orp_idx">생산계획ID</option>
            <option value="orp_order_no">지시번호</option>
            <option value="trm_idx_line_name">설비라인</option>
            <option value="orp_start_date">생산(시작)일</option>
        </select>
        <script>$('select[name=sch_field]').val('<?php echo $sch_field?>').attr('selected','selected')</script>
        <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required size="20" autocomplate="off">
        <input type="submit" value="검색" class="btn_frmline btn btn_02" style="height:26px;">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?file_name=<?=$file_name?>" class="btn btn_b10">취소</a>
    </div>

    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col">생산계획ID</th>
            <th scope="col">지시번호</th>
            <th scope="col">설비라인</th>
            <th scope="col">생산(시작)일</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            // print_r2($row);
            // 회원정보 추출
            $mb1 = get_table_meta('member','mb_id',$row['mb_id']);
            //print_r2($mb1);

        ?>
        <tr>
            <td class="td_orp_idx"><?php echo $row['orp_idx']; ?></td>
            <td class="td_orp_order_no"><?php echo $row['orp_order_no']; ?></td>
            <td class="td_trm_idx_line"><?php echo $g5['line_name'][$row['trm_idx_line']]; ?></td>
            <td class="td_orp_start_date"><?php echo $row['orp_start_date']; ?></td>
            <td class="td_mng td_mng_s"
                orp_idx="<?php echo $row['orp_idx']; ?>"
                line_name = "<?php echo $row['orp_idx'].'-('.$g5['line_name'][$row['trm_idx_line']].')'; ?>"
                orp_order_no="<?php echo $row['orp_order_no']; ?>"
                orp_start_date="<?php echo $row['orp_start_date']; ?>">
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

    <div class="btn_fixed_top">
        <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
    </div>

</div>

<script>
schFieldDate();

$('#sch_field').on('change',function(){
    schFieldDate();
});


function schFieldDate(){
    var slt_val = $('#sch_field').val();
    if(slt_val == 'orp_start_date'){
        $('#sch_word').addClass('orp_start_date').attr('readonly',true).val('');
        $(".orp_start_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    }else{
        if($(".orp_start_date").length){
            $(".orp_start_date").datepicker("destroy");
            $('#sch_word').removeClass('orp_start_date').removeAttr('readonly',false).val('');
        }
    }
}

$('.btn_select').click(function(e){
    e.preventDefault();
    <?php
    // 이전 파일의 폼에 따라 전달 내용 변경
    if($file_name=='order_out_practice_form') {
    ?>
        // 폼이 존재하면
        if( $("form[name=<?php echo $frm;?>]", opener.document).length > 0 ) {
            $("input[name=orp_idx]", opener.document).val( $(this).closest('td').attr('orp_idx') ).attr('required',true);
            $("input[name=line_name]", opener.document).val( $(this).closest('td').attr('line_name') ).attr('required',true).addClass('required');

            //설비선택 해제
            $("#trm_idx_line", opener.document).val('').attr('required',false).removeClass('required');
            //생산담당자 해제
            $("#mb_id", opener.document).val('').attr('required',false);
            $("#mb_name", opener.document).val('').attr('required',false).removeClass('required');
            //생산일정 해제
            $("#orp_start_date", opener.document).val('').attr('required',false).removeClass('required');
            $("#orp_end_date", opener.document).val('').attr('required',false).removeClass('required');
        }
        else {
            alert('값을 전달할 폼이 존재하지 않습니다.');
        }
    <?php
    }

    // ajax 호출이 있을 때는 너무 빨리 창을 닫으면 안 됨
    if($file_name!='company_list') {
    ?>
    window.close();
    <?php
    }
    ?>
});
</script>

<?php
include_once('./_tail.sub.php');
