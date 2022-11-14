<?php
$sub_menu = "945125";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '파렛트조회';
// include_once('./_top_menu_orp.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];
/*
$sql_common = " FROM {$g5['order_practice_table']} AS orp
    LEFT JOIN {$g5['order_out_practice_table']} AS oop ON orp.orp_idx = oop.orp_idx
    LEFT JOIN {$g5['order_out_table']} AS oro ON oop.oro_idx = oro.oro_idx
"; 
*/
$sql_common = " FROM {$g5['pallet_table']} AS plt
                    LEFT JOIN {$g5['bom_table']} AS bom ON plt.bom_idx = bom.bom_idx
"; 

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " plt_status NOT IN ('del','delete','trash') ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'plt_idx' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
        case ( $sfl == 'bom_part_no' ) :
            $where[] = " {$sfl} = '".trim($stx)."' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

if($plt_status){
    $where[] = " plt_status = '".trim($plt_status)."' ";
    $qstr .= $qstr.'&plt_status='.$plt_status;
}

if($plt_reg_dt){
    $where[] = " plt_reg_dt LIKE '".trim($plt_reg_dt)."%' ";
    $qstr .= $qstr.'&plt_reg_dt='.$plt_reg_dt;
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "plt.plt_idx";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";
$sql_group = ""; //" GROUP BY orp.orp_idx ";
$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT *
        {$sql_common} {$sql_search} {$sql_group} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);//exit;
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들
?>
<style>
.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
.td_bom_name {text-align:left !important;}
.td_bom_part_no {text-align:left !important;}
.td_plt_count{text-align:right !important;}

.sp_cat{color:orange;font-size:0.85em;}
.sp_pno{color:skyblue;}

.sch_label{position:relative;}
.sch_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.sch_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.slt_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="plt_idx"<?php echo get_selected($_GET['sfl'], "plt_idx"); ?>>파렛트ID</option>
        <option value="plt_barcode"<?php echo get_selected($_GET['sfl'], "plt_barcode"); ?>>파렛트바코드</option>
        <option value="bom_part_no"<?php echo get_selected($_GET['sfl'], "bom_part_no"); ?>>품번</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <label for="plt_status" class="sch_label">
        <span>상태<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <select name="plt_status" id="plt_status">
            <option value="">::상태선택::</option>
            <?=$g5['set_plt_status_value_options']?>
        </select>
    </label>
    <label for="orp_start_date" class="sch_label">
        <span>등록일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <input type="text" name="plt_reg_dt" value="<?php echo $plt_reg_dt ?>" id="plt_reg_dt" readonly class="frm_input readonly" placeholder="등록일" style="width:100px;" autocomplete="off">
    </label>
    <input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>파렛트를 관리하는 페이지 입니다.</p>
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
<form name="form01" id="form01" action="./pallet_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">파렛트ID</th>
        <th scope="col">카테고리</th>
        <th scope="col">품명</th>
        <th scope="col">품번</th>
        <th scope="col">파렛트바코드</th>
        <th scope="col">적재수량</th>
        <th scope="col">등록일시</th>
        <th scope="col">수정일시</th>
        <th scope="col">상태</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
        <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r3($row);
        if($row['bct_id']){
            $cat_tree = category_tree_array($row['bct_id']);
            $row['bct_name_tree'] = '';
            for($k=0;$k<count($cat_tree);$k++){
                $cat_str = sql_fetch(" SELECT bct_name FROM {$g5['bom_category_table']} WHERE bct_id = '{$cat_tree[$k]}' ");
                $row['bct_name_tree'] .= ($k == 0) ? $cat_str['bct_name'] : ' > '.$cat_str['bct_name'];
            }
        }
        //$s_mod = '<a href="./order_practice_form.php?'.$qstr.'&amp;w=u&amp;orp_idx='.$row['orp_idx'].'" class="btn btn_03">수정</a>';
        //$s_copy = '<a href="./order_practice_form.php?'.$qstr.'&w=c&orp_idx='.$row['orp_idx'].'" class="btn btn_03" style="margin-right:5px;">복제</a>';

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['orp_idx'] ?>">
        <td class="td_plt_idx"><?=$row['plt_idx']?></td>
        <td class="td_plt_idx_parent"><?=$row['plt_idx_parent']?></td>
        <td class="td_bom_name">
            <?php if($row['bct_name_tree']){ ?>
                <span class="sp_cat"><?=$row['bct_name_tree']?></span><br>
            <?php } ?>
            <?=$row['bom_name']?>
        </td><!-- 품명 -->
        <td class="td_bom_part_no"><?=$row['bom_part_no']?></td><!-- 품번 -->
        <td class="td_plt_barcode" style="text-align:left;"><?=$row['plt_barcode']?></td><!-- 파렛트바코드 -->
        <td class="td_plt_count"><?=number_format($row['plt_count'])?></td><!-- 적재수량 -->
        <td class="td_plt_reg_dt"><?=$row['plt_reg_dt']?></td>
        <td class="td_plt_update_dt"><?=$row['plt_update_dt']?></td>
        <td class="td_plt_status"><?=$g5['set_plt_status'][$row['plt_status']]?></td><!-- 상태 -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='9' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (false){ //(!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>
    <?php if(false){//($is_admin){ ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>
</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>


<script>
$("input[name*=_date],input[id*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });


var first_no = '';
var second_no = '';
$('.chkdiv_btn').on('click',function(e){
    //시프트키 또는 알트키와 클릭을 같이 눌렀을 경우
    if(e.shiftKey || e.altKey){
        //first_no정보가 없으면 0번부터 shift+click한 체크까지 선택을 한다.
        if(first_no == ''){
            first_no = 0;
        }
        //first_no정보가 있으면 first_no부터 second_no까지 체크를 선택한다.
        else{
            ;
        }
        second_no = Number($(this).attr('chk_no'));
        var key_type = (e.shiftKey) ? 'shift' : 'alt';
        //multi_chk(first_no,second_no,key_type);
        (function(first_no,second_no,key_type){
            //console.log(first_no+','+second_no+','+key_type+':func');return;
            var start_no = (first_no < second_no) ? first_no : second_no;
            var end_no = (first_no < second_no) ? second_no : first_no;
            //console.log(start_no+','+end_no);return;
            for(var i=start_no;i<=end_no;i++){
                if(key_type == 'shift')
                    $('.chkdiv_btn[chk_no="'+i+'"]').siblings('input[type="checkbox"]').attr('checked',true);
                else
                    $('.chkdiv_btn[chk_no="'+i+'"]').siblings('input[type="checkbox"]').attr('checked',false);
            }

            first_no = '';
            second_no = '';
        })(first_no,second_no,key_type);
    }
    //클릭만했을 경우
    else{
        //이미 체크되어 있었던 경우 체크를 해제하고 first_no,second_no를 초기화해라
        if($(this).siblings('input[type="checkbox"]').is(":checked")){
            first_no = '';
            second_no = '';
            $(this).siblings('input[type="checkbox"]').attr('checked',false);
        }
        //체크가 안되어 있는 경우 체크를 넣고 first_no에 해당 체크번호를 대입하고, second_no를 초기화한다.
        else{
            $(this).siblings('input[type="checkbox"]').attr('checked',true);
            first_no = $(this).attr('chk_no');
            second_no = '';
        }
    }
});


$('.shf_one').on('keyup',function(e){
    var ask = e.keyCode;
    var oro_idx = $(e.target).attr('orp_idx');
    var oro_n = $(e.target).attr('orp');


    if(ask == 38){ //위쪽 화살표 눌렀을 경우
        var trobj = $(this).parent().parent();
        if(trobj.prev().find('td').find('input[orp="'+oro_n+'"]').length)
            trobj.prev().find('td').find('input[orp="'+oro_n+'"]').focus();
        return false;
    }
    else if(ask == 40){ //아래쪽 화살표를 눌렀을 경우
        var trobj = $(this).parent().parent();
        if(trobj.next().find('td').find('input[orp="'+oro_n+'"]').length)
            trobj.next().find('td').find('input[orp="'+oro_n+'"]').focus();
        return false;
    }
    else if((ask < 48 || ask > 57) && (ask < 96 || ask > 105) && (ask < 37 || ask > 40) && ask != 16 && ask != 9 && ask != 46 && ask != 8){
        $(this).val('');
        return false;
    }
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

// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name^=orp_price], input[name^=orp_count], input[name^=orp_lead_time]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}
  

function slet_input(f){
    var chk_count = 0;
    var chk_idx = [];
    //var dt_pattern = new RegExp("^(\d{4}-\d{2}-\d{2})$");
    var dt_pattern = /^(\d{4}-\d{2}-\d{2})$/;
    for(var i=0; i<f.length; i++){
        if(f.elements[i].name == "chk[]" && f.elements[i].checked){
            chk_idx.push(f.elements[i].value);
            chk_count++;
        }
    }
    if (!chk_count) {
        alert("일괄입력할 출하목록을 하나 이상 선택하세요.");
        return false;
    }



    var o_date = $.trim(document.getElementById('o_date').value);
    //완료일의 날짜 형식 체크
    if(!dt_pattern.test(o_date) && o_date != ''){
        alert('날짜 형식에 맞는 데이터를 입력해 주세요.\r\n예)2021-02-05');
        document.getElementById('o_date').value = '0000-00-00';
        document.getElementById('o_date').focus();
        return false;
    }
    
    //console.log(chk_idx);return;
    for(var idx in chk_idx){
        //console.log(idx);continue;
        if(o_date){
            $('.td_orp_done_date_'+chk_idx[idx]).find('input[type="text"]').val(o_date);
        }
    }
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
?>
