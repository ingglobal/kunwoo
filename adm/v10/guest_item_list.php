<?php
$sub_menu = "945118";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '고객처재고관리';
// include_once('./_top_menu_guest.php');
include_once('./_head.php');
/*

*/
$sql_common = " FROM {$g5['guest_stock_table']}  AS gst
                    LEFT JOIN {$g5['bom_table']} AS bom ON gst.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['company_table']} AS com ON gst.com_idx_customer = com.com_idx
";
$where = array();

//디폴트 검색조건 
$where[] = " gst_status NOT IN ('delete','del','trash') AND gst.com_idx = '".$_SESSION['ss_com_idx']."' ";

//검색어 설정
if($stx != ''){
    switch ($sfl) {
        case ( $sfl == 'bom.bom_part_no' ) :
            $where[] = " {$sfl} LIKE '%".trim($stx)."%' ";
            break;
        default :
            $where[] = " {$sfl} LIKE '%".trim($stx)."%' ";
            break;
    }
}

if($gst_date) {
    $where[] = " gst_date = '{$gst_date}' ";
}

//최종 WHERE 생성
if($where) {
    $sql_search = ' WHERE '.implode(' AND ', $where);
}

if (!$sst) {
    $sst = "gst_date";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", bom_sort";
    $sod2 = "";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$sql = " SELECT COUNT(*) AS cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 30;//$config['cf_page_rows'];
$total_page = ceil($total_count / $rows); //전체페이지 계산
if($page < 1) $page = 1; //페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; //시작 열을 구함

$sql = " SELECT * 
    {$sql_common} {$sql_search} {$sql_order}
    LIMIT {$from_record}, {$rows}
";

$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; //추가로 확장해서 넘겨야 할 변수들
?>
<style>
.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
.td_chk{position:relative;}
.td_chk .chkdiv_btn{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,255,0,0);}
#top_form:after{display:block;visibility:hidden;clear:both;content:'';}
#top_form #fsearch{float:left;}
#top_form #finput{float:right;margin:10px 0;}

label[for="gst_date"]{position:relative;}
label[for="gst_date"] i{position:absolute;top:-10px;right:0px;z-index:2;cursor:pointer;}
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.slt_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<div id="top_form">
    <form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl">
            <option value="bom.bom_name"<?php echo get_selected($_GET['sfl'], "bom_name"); ?>>품명</option>
            <option value="bom.bom_part_no"<?php echo get_selected($_GET['sfl'], "bom_part_no"); ?>>품번</option>
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <?php
        $gst_date = ($gst_date) ? $gst_date : G5_TIME_YMD;
        ?>
        <label for="gst_date"><strong class="sound_only">확인날짜 필수</strong>
        <i class="fa fa-times" aria-hidden="true"></i>
        <input type="text" name="gst_date" value="<?php echo $gst_date ?>" placeholder="확인날짜" id="gst_date" readonly class="frm_input readonly" style="width:80px;">
        </label>
        <script>
        <?php
        $sfl = ($sfl == '') ? 'bom.bom_name' : $sfl;
        ?>
        $('#sfl').val('<?=$sfl?>');
        </script>
        <input type="submit" class="btn_submit" value="검색">
    </form>

    <form name="finput" id="finput" action="./guest_item_list_update.php" onsubmit="return input_form(this);" method="post">
        <input type="hidden" name="item_add" value="1">
        <label for="bom_name">
            <input type="hidden" name="com_idx_customer" value="">
            <input type="hidden" name="bom_idx" value="">
            <input type="text" id="bom_name" name="bom_name" link="./bom_select3.php?file_name=<?=$g5['file_name']?>" readonly class="frm_input readonly" placeholder="고객처재고상품선택(클릭!)" value="" style="width:300px;">
        </label>
        <label for="gst_date">
            <input type="text" name="gst_date" id="mtr_input_date" readonly required class="frm_input readonly required" value="<?=G5_TIME_YMD?>" style="width:80px;">
        </label>
        <label for="gst_counts" id="counts">
            <input type="text" name="gst_counts" required class="frm_input required" placeholder="고객처재고갯수" value="" style="text-align:right;width:100px;" onclick="javascript:chk_Number(this)">
        </label>
        <input type="submit" name="act_button" class="btn_input btn btn_01" onclick="document.pressed=this.value" value="고객처재고등록">
    </form>
</div>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>고객처 상품재고량을 관리하는 페이지입니다.</p>
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

<form name="form01" id="form01" action="./guest_item_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
        <th scope="col" id="mtr_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">ID</th>
        <th scope="col">분류</th>
        <th scope="col"><?php echo subject_sort_link('bom_name') ?>품명</a></th>
        <th scope="col">파트넘버</th>
        <th scope="col">재고확인날짜</th>
        <th scope="col">갯수</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $bom = sql_fetch(" SELECT bct_id FROM {$g5['bom_table']} WHERE bom_idx = '{$row['bom_idx']}' ");
        if($bom['bct_id']){
            $cat_tree = category_tree_array($bom['bct_id']);
            $row['bct_name_tree'] = '';
            for($k=0;$k<count($cat_tree);$k++){
                $cat_str = sql_fetch(" SELECT bct_name FROM {$g5['bom_category_table']} WHERE bct_id = '{$cat_tree[$k]}' ");
                $row['bct_name_tree'] .= ($k == 0) ? $cat_str['bct_name'] : ' > '.$cat_str['bct_name'];
            }
        }

        //print_r2($row);
        $s_mod = '<a href="./guest_item_form.php?'.$qstr.'&amp;w=u&amp;mtr_idx='.$row['gst_idx'].'" class="btn btn_03">수정</a>';

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['gst_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="gst_idx[<?php echo $row['gst_idx'] ?>]" value="<?php echo $row['gst_idx'] ?>" class="gst_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['bom_name']); ?> <?php echo get_text($row['mb_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['gst_idx'] ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_gst_name"><?=$row['gst_idx']?></td><!-- ID -->
        <td class="td_gst_category" style="text-align:left;"><?=$row['bct_name_tree']?></td><!-- 카테고리 -->
        <td class="td_gst_name" style="text-align:left;"><?=$row['bom_name']?></td><!-- 품명 -->
        <td class="td_gst_part_no"><?=$row['bom_part_no']?></td><!-- 파트넘버 -->
        <td class="td_gst_date"><?=$row['gst_date']?></td><!-- 재고확인날짜 -->
        <td class="td_gst_count td_gst_count_<?=$row['gst_idx']?>">
            <input type="text" name="gst_count[<?=$row['gst_idx']?>]" value="<?=$row['gst_count']?>" class="tbl_input gst_count_<?=$row['gst_idx']?>" style="width:60px;text-align:right;" onClick="javascript:chk_Number(this)">
        </td><!-- 갯수 -->
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
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button2" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <input type="submit" name="act_button2" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>


<script>
$("input[name*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

$('label[for="mtr_input_date"] i').on('click',function(){
    $(this).siblings('input').val('');
});

// 제품찾기 버튼 클릭
$("#bom_name").click(function(e) {
    e.preventDefault();
    var href = $(this).attr('link');
    winBomSelect = window.open(href, "winBomSelect", "left=300,top=150,width=650,height=600,scrollbars=1");
    winBomSelect.focus();
});
//입고날짜선택에서 X버튼 클릭시 값제거
$('label[for="gst_date"] i').on('click',function(){
    $(this).siblings('input').val('');
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


// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

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

    $('<input type="hidden" name="act_button" value="'+document.pressed+'">').prependTo('#form01');

    return true;
}



function input_form(f){
    if(!f.bom_name.value){
        alert('입고할 상품을 선택해 주세요.');
        f.bom_name.focus();
        return false;
    }

    if(!f.gst_date.value){
        alert('재고확인날짜를 선택해 주세요.');
        f.gst_date.focus();
        return false;
    }

    if(!f.gst_count.value){
        alert('고객처재고량을 입력해 주세요.');
        f.gst_count.focus();
        return false;
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');