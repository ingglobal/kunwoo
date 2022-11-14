<?php
// 호출 페이지들
// /adm/v10/bom_form.php: 제품(BOM)수정: 거래처찾기
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

$sql_common = " FROM {$g5['order_table']}

";

$where = array();
//디폴트 조건
$where[] = " ord_status NOT IN('delete','del','trash') AND com_idx = '{$_SESSION['ss_com_idx']}' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'bom_part_no' ) :
			$where[] = " {$sfl} LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}


// 최종 WHERE 생성
if ($where){
    $sql_search = ' WHERE '.implode(' AND ',$where);
}


if (!$sst) {
    $sst = "ord_date";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$rows = 50;//10
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT *
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);
$result = sql_query($sql,1);

$qstr .= '&sca='.$sca.'&file_name='.$file_name; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '생산계획 검색'.$total_count_display;
include_once('./_head.sub.php');

add_stylesheet('<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-darkness/jquery-ui.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.structure.min.css">', 1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.min.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker-ko.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker.js"></script>',1);
?>
<style>
.scp_frame {padding:10px;}
.new_frame_con {margin-top:10px;height:484px;overflow-y:auto;padding-bottom:25px;}
</style>
<div id="sch_target_frm" class="new_win scp_frame">
    <form name="ftarget" method="get">
    <div id="div_search">
        <select name="sfl" id="sfl">
            <option value="ord_idx"<?php echo get_selected($_GET['sfl'], "ord_idx"); ?>>수주ID번</option>
            <option value="ord_date"<?php echo get_selected($_GET['sfl'], "ord_date"); ?>>수주일</option>
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="width:160px;">
        <input type="submit" value="검색" class="btn_frmline">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?file_name=<?=$file_name?>" class="btn btn_b10">취소</a>
    </div>
    </form>
    <div class="tbl_head01 tbl_wrap new_frame_con">
    <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col"><?php echo subject_sort_link('bom_name') ?>수주ID</a></th>
            <th scope="col">수주일</th>
            <th scope="col">타입</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
           <?php
           for ($i=0; $row=sql_fetch_array($result); $i++){
               $bg = 'bg'.($i%2);
           ?>
           <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['ord_idx'] ?>">
                <td class="td_ord_idx"><?=$row['ord_idx']?></td><!--수주ID-->
                <td class="td_ord_date"><?=$row['ord_date']?></td><!--수주일-->
                <td class="td_ord_status"><?=$g5['set_ord_status_value'][$row['ord_status']]?></td><!--수주상태-->
                <td class="td_mng td_mng_s">
                    <button type="button" class="btn btn_03 btn_select" ord_date="<?=$row['ord_date']?>" ord_idx="<?=$row['ord_idx']?>">선택</button>
                </td>
           </tr>
           <?php
           }
           if($i ==0)
                echo '<tr><td colspan="5" class="empty_table">검색된 자료가 없습니다.</td></tr>';
            ?>
        </tbody>
    </div><!--//.tbl_head01-->

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
</div><!--//#sch_target_frm-->


<script>
schFieldDate();

$('#sfl').on('change',function(){
    schFieldDate();
});


function schFieldDate(){
    var slt_val = $('#sfl').val();
    if(slt_val == 'ord_date'){
        $('#stx').addClass('ord_date').attr('readonly',true).val('');
        $(".ord_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    }else{
        if($(".ord_date").length){
            $(".ord_date").datepicker("destroy");
            $('#stx').removeClass('ord_date').removeAttr('readonly',false).val('');
        }
    }
}

$('.btn_select').click(function(e){
    e.preventDefault();
    var ord_date = $(this).attr('ord_date');
    var ord_idx = $(this).attr('ord_idx');
    <?php
    if($file_name=='order_out_form'){
    ?>
        var opn_ord_date = $("input[name=ord_date]", opener.document).val();
        var opn_ord_idx = $("input[name=ord_idx]", opener.document).val();
        if(opn_ord_date == ord_date && opn_ord_idx == ord_idx){
            $("input[name=ord_date]", opener.document).val( ord_date );
            $("input[name=ord_idx]", opener.document).val( ord_idx );
        } else {
            $("input[name=ord_date]", opener.document).val( ord_date );
            $("input[name=ord_idx]", opener.document).val( ord_idx );
            $('input[name=ori_idx]', opener.document).val('');
            $('input[name=bom_idx]', opener.document).val('');
            $('input[name=bom_name]', opener.document).val('');
            $('input[name=com_idx_customer]', opener.document).val('');
            $('input[name=com_name]', opener.document).val('');
            $('input[name=com_idx_shipto]', opener.document).val('');
            $('input[name=ship_name]', opener.document).val('');
        }
    <?php
    }
    ?>
    window.close();
});
</script>
<?php
include_once('./_tail.sub.php');