<?php
$sub_menu = "945115";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '완제품재고관리(생산별)';
include_once('./_head.php');
include_once('./_top_menu_itm.php');


$sql_common = " FROM {$g5['item_table']} itm
                    LEFT JOIN {$g5['bom_table']} bom ON itm.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['order_out_practice_table']} oop ON itm.oop_idx = oop.oop_idx
                    LEFT JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " itm.itm_status NOT IN ('delete','del','trash') ";
$where[] = " itm.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'bom_idx' || $sfl == 'itm_idx' || $sfl == 'itm_borcode' || $sfl == 'itm_lot' || $sfl == 'itm_defect_type' || $sfl == 'trm_idx_location' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
		case ( $sfl == 'bct_id' ) :
			$where[] = " {$sfl} LIKE '".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

if($mtr_date){
    $where[] = " itm_date = '".$itm_date."' ";
    $qstr .= $qstr.'&itm_date='.$itm_date;
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// $sql_group = " GROUP BY itm.bom_idx, itm_date ";
$sql_group = " GROUP BY itm.oop_idx ";

if (!$sst) {
    $sst = "orp_start_date";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", oop.oop_idx";
    $sod2 = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

// $sql = " SELECT COUNT(DISTINCT itm.bom_idx, itm_date) as cnt {$sql_common} {$sql_search} ";
$sql = " SELECT COUNT(c.bom_idx) AS cnt FROM (
            SELECT itm.bom_idx {$sql_common} {$sql_search} {$sql_group}
        ) c ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
// echo $total_count.'<br>';

$rows = 20;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = "SELECT itm.itm_name
              ,oop.oop_idx
              ,oop.orp_idx
              ,itm.bom_part_no
              ,bom.bom_std
              ,orp.orp_start_date
              ,ROW_NUMBER() OVER (ORDER BY orp_start_date, oop.oop_idx) AS itm_num
              ,ROUND(SUM(itm.itm_weight)) AS sum
              ,COUNT(*) AS cnt
              ,COUNT( CASE WHEN itm_status = 'error_size' THEN 1 END ) AS error_size
              ,COUNT( CASE WHEN itm_status = 'error_dent' THEN 1 END ) AS error_dent
              ,COUNT( CASE WHEN itm_status = 'error_bend' THEN 1 END ) AS error_bend
              ,COUNT( CASE WHEN itm_status = 'error_worker' THEN 1 END ) AS error_worker
              ,COUNT( CASE WHEN itm_status = 'error_material' THEN 1 END ) AS error_material
              ,COUNT( CASE WHEN itm_status = 'error_cut' THEN 1 END ) AS error_cut
              ,COUNT( CASE WHEN itm_status = 'error_subcontractor' THEN 1 END ) AS error_subcontractor

              ,COUNT( CASE WHEN itm_status = 'error_fold' THEN 1 END ) AS error_fold
              ,COUNT( CASE WHEN itm_status = 'error_unformed' THEN 1 END ) AS error_unformed
              ,COUNT( CASE WHEN itm_status = 'error_position' THEN 1 END ) AS error_position
              ,COUNT( CASE WHEN itm_status = 'error_crack' THEN 1 END ) AS error_crack
              ,COUNT( CASE WHEN itm_status = 'error_breakaway' THEN 1 END ) AS error_breakaway
              ,COUNT( CASE WHEN itm_status = 'error_overheat' THEN 1 END ) AS error_overheat
              ,COUNT( CASE WHEN itm_status = 'error_scale' THEN 1 END ) AS error_scale
              ,COUNT( CASE WHEN itm_status = 'error_layer' THEN 1 END ) AS error_layer
              ,COUNT( CASE WHEN itm_status = 'error_trim' THEN 1 END ) AS error_trim
              ,COUNT( CASE WHEN itm_status = 'error_sita' THEN 1 END ) AS error_sita
              ,COUNT( CASE WHEN itm_status = 'error_mold' THEN 1 END ) AS error_mold
              ,COUNT( CASE WHEN itm_status = 'error_equipment' THEN 1 END ) AS error_equipment
              ,COUNT( CASE WHEN itm_status = 'error_after' THEN 1 END ) AS error_after
              ,COUNT( CASE WHEN itm_status = 'error_claim' THEN 1 END ) AS error_claim
              ,COUNT( CASE WHEN itm_status = 'error_replace' THEN 1 END ) AS error_replace
              ,COUNT( CASE WHEN itm_status = 'error_dev' THEN 1 END ) AS error_dev
              ,COUNT( CASE WHEN itm_status = 'error_heat' THEN 1 END ) AS error_heat
              ,COUNT( CASE WHEN itm_status = 'error_lose' THEN 1 END ) AS error_lose

              ,COUNT( CASE WHEN itm_status = 'error_etc' THEN 1 END ) AS error_etc
              ,COUNT( CASE WHEN itm_status = 'error_scrap' THEN 1 END ) AS error_scrap
              ,COUNT( CASE WHEN itm_status = 'finish' THEN 1 END ) AS finish
              ,COUNT( CASE WHEN itm_status = 'delivery' THEN 1 END ) AS delivery
        {$sql_common} {$sql_search} {$sql_group}  {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// echo $sql;
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
// print_r2($g5['set_half_status_value']);
// print_r2($g5['set_half_status_ng_array']);
?>
<style>
#itm_data{position:relative;padding-bottom:10px;}
/*
#half_data #form02{position:absolute;right:0;top:-47px;}
*/
.b_fromto,.b_cnt{position:relative;top:2px;margin-right:5px;}

.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
.td_chk{position:relative;}
.td_chk .chkdiv_btn{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,255,0,0);}
.td_itm_name {text-align:left !important;}
.sp_pno{color:skyblue;font-size:0.85em;}
.sp_std{color:#e87eee;font-size:0.85em;}
.td_com_name, .td_itm_maker
,.td_itm_items, .td_itm_items_title {text-align:left !important;}
.span_itm_price {margin-left:20px;}
.span_itm_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
.td_itm_history {width:190px !important;}
label[for="itm_static_date"]{position:relative;}
label[for="itm_static_date"] i{position:absolute;top:-10px;right:0px;z-index:2;cursor:pointer;}
.slt_label{position:relative;display:inline-block;}
.slt_label i{position:absolute;top:-7px;right:0px;z-index:2;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>
<?php
echo $g5['container_sub_title'];
?>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="itm_name"<?php echo get_selected($_GET['sfl'], "itm_name"); ?>>품명</option>
    <option value="bom.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom_part_no"); ?>>품번</option>
    <option value="bom.bom_std"<?php echo get_selected($_GET['sfl'], "bom_std"); ?>>규격</option>
    <option value="oop.oop_idx"<?php echo get_selected($_GET['sfl'], "oop_idx"); ?>>생산계획    ID</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">

</label>
<script>
<?php
$sfl = ($sfl == '') ? 'itm_name' : $sfl;
?>
$('#sfl').val('<?=$sfl?>');
$('#shift').val('<?=$shift?>');
$('#itm2_status').val('<?=$itm2_status?>');
</script>
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>생산계획ID별 따른 완제품 재고조회 페이지입니다.</p>
</div>

<script>
$('.data_blank').on('click',function(e){
    e.preventDefault();
    //$(this).parent().siblings('input').val('');
    var obj = $(this).parent().next();
    if(obj.prop("tagName") == 'INPUT'){
        if(obj.attr('type') == 'hidden'){
            obj.val('');
            obj.siblings('input').val('');
        }else if(obj.attr('type') == 'text'){
            obj.val('');
        }
    }else if(obj.prop("tagName") == 'SELECT'){
        obj.val('');
    }
});
//mms_idx,bom_idx_parent,mtr_weight,mtr_heat,mtr_lot,mtr_bundle
</script>
<div id="itm_data">
    <form name="form02" id="form02" action="./item_reg_update.php" onsubmit="return form02_submit(this);" method="post" autocomplete="off">
        <strong style="position:relative;top:3px;">완제품재고 추가/변경:</strong>
        <input type="hidden" name="sst" value="<?php echo $sst ?>">
        <input type="hidden" name="sod" value="<?php echo $sod ?>">
        <input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
        <input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">
        <input type="hidden" name="token" value="">
        
        <input type="hidden" name="forge_mms_idx" value="">
        <input type="hidden" name="itm_weight" value="">
        <input type="hidden" name="itm_heat" value="">
        <input type="text" name="oop_idx" value="" class="frm_input oop_select" link="./oop_itm_select.php?fname=<?=$g5['file_name']?>" readonly placeholder="생산계획ID">
        <input type="text" name="bom_part_no" value="" class="frm_input oop_select" link="./oop_itm_select.php?fname=<?=$g5['file_name']?>" readonly placeholder="품목코드">
        <input type="hidden" name="bom_idx" value="">
        <input type="text" name="bom_name" value="" class="frm_input oop_select" link="./oop_itm_select.php?fname=<?=$g5['file_name']?>" readonly placeholder="품명" style="width:300px;">
        <select name="plus_modify" class="plus_modify">
            <option value="plus">추가하기</option>
            <option value="modify">변경하기</option>
        </select>
        <span class="sp_from">
            <select name="from_status" class="from_status">
                <option value="">::기존상태::</option>
                <?=$g5['set_itm_status_value_options']?>
            </select><b class="b_fromto b_from">(을)를</b>
        </span>
        <span>
            <select name="to_status" class="to_status">
                <option value="">::목표상태::</option>
                <?=$g5['set_itm_status_value_options']?>
                <!-- <option value="trash">삭제</option> -->
            </select><b class="b_fromto b_to">(으)로</b>
        </span>
        <input type="text" name="count" class="frm_input count" value="" style="width:60px;text-align:right;" placeholder="갯수"><b class="b_cnt">개</b>
        <input type="submit" value="적용" class="btn_submit btn">
        <a href="javascript:" class="btn btn_04 btn_no">취소</a>
    </form>
</div>
<script>
$('.sp_from').hide();
$('.plus_modify').on('change',function(){
    if($(this).val()=='plus'){
        $('.sp_from').hide();
    }
    else if($(this).val()=='modify'){
        $('.sp_from').show();
    }
});
//숫자만 입력
$('.count').on('keyup',function(){
    $(this).val($(this).val().replace(/[^0-9|-]/g,""));
});
//생산계획선택
$('.oop_select').on('click',function(e){
    e.preventDefault();
    var href = $(this).attr('link');
    var winOrpSelect = window.open(href, "winOrpSelect", "left=300,top=150,width=650,height=700,scrollbars=1");
    winOrpSelect.focus();
    return false;
});
//취소
$('.btn_no').on('click',function(){
    $('input[name="oop_idx"],input[name="bom_part_no"],input[name="bom_idx"],input[name="bom_name"]').val('');
    $('.plus_modify').val('plus');
    $('.from_status,.to_status').val('');
    $('.sp_from').hide();
    $('.count').val('');
});
</script>


<form name="form01" id="form01" action="./item_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sst2" value="<?php echo $sst2 ?>">
<input type="hidden" name="sod2" value="<?php echo $sod2 ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">생산계획ID</th>
        <th scope="col"><?php echo subject_sort_link('itm_name') ?>품명/품번/규격</a></th>
        <th scope="col">생산시작일</th>
        <?php foreach($g5['set_itm_status_ng_array'] as $ng_name){ ?>
        <th scope="col">
            <?=str_replace("불량","",$g5['set_itm_status_value'][$ng_name])?>
        </th>
        <?php } ?>
        <th scope="col" style="color:orange;">생산완료</th>
        <th scope="col" style="color:pink;">출하완료</th>
        <th scope="col" style="color:skyblue;">총생산갯수</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {

        $s_mod = '<a href="./item_form.php?'.$qstr.'&amp;w=u&amp;mtr_idx='.$row['mtr_idx'].'" class="btn btn_03">수정</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['itm_idx'] ?>">
        <td class="td_itm_num"><?=$row['itm_num']?></td><!-- 번호 -->
        <td class="td_oop_idx"><?=$row['oop_idx']?></td><!-- 생산계회ID -->
        <td class="td_itm_name">
            <b><?=$row['itm_name']?></b>
            <?php if($row['bom_part_no']){ ?>
            <br><span class="sp_pno">[ <?=$row['bom_part_no']?> ]</span>
            <?php } ?>
            <?php if($row['bom_std']){ ?>
            <br><span class="sp_std">[ <?=$row['bom_std']?> ]</span>
            <?php } ?>
        </td><!-- 품명 -->
        <td class="td_orp_start_date"><?=substr($row['orp_start_date'],2,8)?></td><!-- 생산시작일 -->
        <?php foreach($g5['set_itm_status_ng_array'] as $ng_name){ ?>
        <td class="td_itm_cnt"><?=(($row[$ng_name])?$row[$ng_name]:'-')?></td><!-- 재고개수 -->
        <?php } ?>
        <td class="td_itm_finish" style="color:orange;"><?=(($row['finish'])?$row['finish']:'-')?></td><!-- 절단완료 -->
        <td class="td_itm_delivery" style="color:pink;"><?=(($row['delivery'])?$row['delivery']:'-')?></td><!-- 사용완료개수 -->
        <td class="td_itm_total" style="color:skyblue;"><?=(($row['cnt'])?$row['cnt']:'-')?></td><!-- 총생산갯수 -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='33' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if(false){//($is_admin){ ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./item_form.php" id="member_add" class="btn btn_01">추가하기</a>
    <?php } ?>
    <?php //} ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>


<script>
$("input[name*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

$('label[for="itm_date"] i').on('click',function(){
    $(this).siblings('input').val('');
});


function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

function form02_submit(f) {
    if (!f.oop_idx.value || !f.bom_part_no.value || !f.bom_name.value || !f.bom_idx.value) {
        alert('생산계획을 선택해 주세요.');
        return false;
    }

    if(f.plus_modify.value == 'plus'){
        if(!f.to_status.value){
            alert('목표상태를 선택해 주세요.')
            return false;
        }
    }
    else if(f.plus_modify.value == 'modify'){
        if(!f.from_status.value){
            alert('기존상태를 선택해 주세요.')
            return false;
        }
        if(!f.to_status.value){
            alert('목표상태를 선택해 주세요.')
            return false;
        }
        if(f.from_status.value == f.to_status.value){
            alert('기존상태값과 목표상태값이 동일합니다.');
            return false;
        }
    }

    if(!f.count.value){
        alert('갯수를 입력해 주세요.');
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
