<?php
$sub_menu = "945113";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '절단품재고관리(생산별)';
include_once('./_head.php');
include_once('./_top_menu_half.php');


$sql_common = " FROM {$g5['material_table']} mtr
                    LEFT JOIN {$g5['bom_table']} bom ON mtr.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['order_out_practice_table']} oop ON mtr.oop_idx = oop.oop_idx
                    LEFT JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " mtr.mtr_status NOT IN ('delete','del','trash') ";
$where[] = " mtr.mtr_type = 'half' ";
$where[] = " mtr.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'bom_idx' || $sfl == 'mtr_idx' || $sfl == 'mtr_borcode' || $sfl == 'mtr_lot' || $sfl == 'mtr_defect_type' || $sfl == 'trm_idx_location' ) :
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
    $where[] = " mtr_input_date = '".$mtr_input_date."' ";
    $qstr .= $qstr.'&mtr_input_date='.$mtr_input_date;
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// $sql_group = " GROUP BY mtr.bom_idx, mtr_input_date ";
$sql_group = " GROUP BY mtr.oop_idx ";

if (!$sst) {
    $sst = "orp_start_date";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", oop.oop_idx";
    $sod2 = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

// $sql = " SELECT COUNT(DISTINCT mtr.bom_idx, mtr_input_date) as cnt {$sql_common} {$sql_search} ";
$sql = " SELECT COUNT(c.bom_idx) AS cnt FROM (
            SELECT mtr.bom_idx {$sql_common} {$sql_search} {$sql_group}
        ) c ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
// echo $total_count.'<br>';

$rows = 20;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = "SELECT mtr.mtr_name
              ,oop.oop_idx
              ,oop.orp_idx
              ,mtr.bom_part_no
              ,bom.bom_std
              ,orp.orp_start_date
              ,ROW_NUMBER() OVER (ORDER BY orp_start_date, oop.oop_idx) AS mtr_num
              ,ROUND(SUM(mtr.mtr_weight)) AS sum
              ,COUNT(*) AS cnt

              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_size') ) AS error_size
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_dent') ) AS error_dent
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_bend') ) AS error_bend
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_worker') ) AS error_worker
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_material') ) AS error_material
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_cut') ) AS error_cut
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_subcontractor') ) AS error_subcontractor
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('error_etc') ) AS error_etc

              ,( SELECT ROUND(SUM(mtr_weight)) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('stock') ) AS sum
              ,( SELECT COUNT(mtr_idx) FROM {$g5['material_table']} WHERE bom_idx = mtr.bom_idx AND mtr_status IN ('stock') ) AS cnt
        {$sql_common} {$sql_search} {$sql_group}  {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// echo $sql;
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
?>
<style>
.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
.td_chk{position:relative;}
.td_chk .chkdiv_btn{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,255,0,0);}
.td_mtr_name {text-align:left !important;}
.sp_pno{color:skyblue;font-size:0.85em;}
.sp_std{color:#e87eee;font-size:0.85em;}
.td_com_name, .td_mtr_maker
,.td_mtr_items, .td_mtr_items_title {text-align:left !important;}
.span_mtr_price {margin-left:20px;}
.span_mtr_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
.td_mtr_history {width:190px !important;}
label[for="mtr_static_date"]{position:relative;}
label[for="mtr_static_date"] i{position:absolute;top:-10px;right:0px;z-index:2;cursor:pointer;}
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
    <option value="mtr_name"<?php echo get_selected($_GET['sfl'], "mtr_name"); ?>>품명</option>
    <option value="bom.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom_part_no"); ?>>품번</option>
    <option value="bom.bom_std"<?php echo get_selected($_GET['sfl'], "bom_std"); ?>>규격</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<?php
// $mtr_input_date = ($mtr_input_date) ? $mtr_input_date : G5_TIME_YMD;
?>
<!-- <label for="mtr_input_date" class="slt_label"><strong class="sound_only">통계일 필수</strong>
<i class="fa fa-times" aria-hidden="true"></i>
<input type="text" name="mtr_input_date" value="<?php //echo $mtr_input_date ?>" placeholder="통계일" id="mtr_input_date" readonly class="frm_input readonly" style="width:95px;"> -->
</label>
<script>
<?php
$sfl = ($sfl == '') ? 'mtr_name' : $sfl;
?>
$('#sfl').val('<?=$sfl?>');
$('#shift').val('<?=$shift?>');
$('#mtr2_status').val('<?=$mtr2_status?>');
</script>
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>생산일에 따른 반제품별 재고조회 페이지입니다.</p>
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
</script>
<form name="form01" id="form01" action="./half_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
        <!-- <th scope="col">카테고리</th> -->
        <th scope="col"><?php echo subject_sort_link('mtr_name') ?>품명/품번/규격</a></th>
        <th scope="col">생산시작일</th>
        <th scope="col">재고무게(kg)</th>
        <th scope="col">재고갯수</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {

        $s_mod = '<a href="./half_form.php?'.$qstr.'&amp;w=u&amp;mtr_idx='.$row['mtr_idx'].'" class="btn btn_03">수정</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['mtr_idx'] ?>">
        <td class="td_mtr_num"><?=$row['mtr_num']?></td><!-- 번호 -->
        <td class="td_mtr_name">
            <b><?=$row['mtr_name']?></b>
            <?php if($row['bom_part_no']){ ?>
            <br><span class="sp_pno">[ <?=$row['bom_part_no']?> ]</span>
            <?php } ?>
            <?php if($row['bom_std']){ ?>
            <br><span class="sp_std">[ <?=$row['bom_std']?> ]</span>
            <?php } ?>
        </td><!-- 품명 -->
        <td class="td_orp_start_date"><?=$row['orp_start_date']?></td><!-- 생산시작일 -->
        <td class="td_mtr_sum"><?=(($row['sum'])?$row['sum']:0)?></td><!-- 재고무게(kg) -->
        <td class="td_mtr_cnt"><?=$row['cnt']?></td><!-- 재고개수 -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='7' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if(false){//($is_admin){ ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./half_form.php" id="member_add" class="btn btn_01">추가하기</a>
    <?php } ?>
    <?php //} ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./item_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>


<script>
$("input[name*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

$('label[for="mtr_input_date"] i').on('click',function(){
    $(this).siblings('input').val('');
});

// 엑셀등록 버튼
$( "#btn_excel_upload" ).on( "click", function() {
    $( "#modal01" ).dialog( "open" );
});
$( "#modal01" ).dialog({
    autoOpen: false
    , position: { my: "right-10 top-10", of: "#btn_excel_upload"}
});


// 마우스 hover 설정
$(".tbl_head01 tbody tr").on({
    mouseenter: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');

    },
    mouseleave: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    }
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
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
