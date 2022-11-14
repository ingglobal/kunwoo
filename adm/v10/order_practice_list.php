<?php
$sub_menu = "930105";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '생산실행계획';
// include_once('./_top_menu_orp.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];
/*
$sql_common = " FROM {$g5['order_practice_table']} AS orp
    LEFT JOIN {$g5['order_out_practice_table']} AS oop ON orp.orp_idx = oop.orp_idx
    LEFT JOIN {$g5['order_out_table']} AS oro ON oop.oro_idx = oro.oro_idx
"; 
*/
$sql_common = " FROM {$g5['order_practice_table']} AS orp
"; 

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " orp_status NOT IN ('del','delete','trash') AND orp.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'orp_id' ) :
			$where[] = " {$sfl} = '".trim($stx)."' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

if($trm_idx_line){
    $where[] = " orp.trm_idx_line = '".$trm_idx_line."' ";
}

if($orp_start_date && $orp_done_date){
    $where[] = " orp.orp_start_date >= '".$orp_start_date."' ";
    $where[] = " orp.orp_done_date <= '".$orp_done_date."' ";
}
else if($orp_start_date && !$orp_done_date){
    $where[] = " orp.orp_start_date = '".$orp_start_date."' ";
}
else if(!$orp_start_date && $orp_done_date){
    $where[] = " orp.orp_done_date = '".$orp_start_date."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "orp.orp_start_date";
    $sod = "desc";
}

if (!$sst2) {
    $sst2 = ", orp.trm_idx_line";
    $sod2 = "";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";
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
.td_orp_part_no, .td_com_name, .td_orp_maker
,.td_orp_items, .td_orp_items_title {text-align:left !important;}
.td_orp_count{text-align:right !important;}
.span_orp_price {margin-left:20px;}
.span_orp_price b, .span_bit_count b {color:#737132;font-weight:normal;}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
.div_product_detail {margin-top:-5px;font-size:0.8em;}
.span_oop_count {margin-left:10px;color:yellow;}
.span_oro_date_plan {margin-left:10px;}
.span_oro_date_plan:before {content:'~';}

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
        <option value="orp_idx"<?php echo get_selected($_GET['sfl'], "orp_idx"); ?>>생산계획ID</option>
        <option value="orp_order_no"<?php echo get_selected($_GET['sfl'], "orp_order_no"); ?>>지시번호</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
    <label for="trm_idx_line" class="sch_label">
        <span>설비라인<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <select name="trm_idx_line" id="trm_idx_line">
            <option value="">라인선택</option>
            <?=$line_form_options?>
        </select>
    </label>
    <label for="orp_start_date" class="sch_label">
        <span>시작일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <input type="text" name="orp_start_date" value="<?php echo $orp_start_date ?>" id="orp_start_date" readonly class="frm_input readonly" placeholder="시작일" style="width:100px;" autocomplete="off">
    </label>
    <label for="orp_done_date" class="sch_label">
        <span>완료일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
        <input type="text" name="orp_done_date" value="<?php echo $orp_done_date ?>" id="orp_done_date" readonly class="frm_input readonly" placeholder="완료일" style="width:100px;" autocomplete="off">
    </label>
    <input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p style="display:none;">지시수량에 필요한 자재가 부족한 경우 <span class="color_red">빨간색</span>으로 표시됩니다. 자재 창고위치에 따라 현장 오차가 있을 수 있으므로 반드시 확인하시고 진행하세요.</p>
    <p>생산계획 등록후 생산자/완료일/메모 외의 정보는 수정할 수 없습니다.</p>
    <p style="display:none;">'생산수량' 항목의 값은 생산이 진행중일 때 표시됩니다.</p>
</div>

<div class="select_input" style="display:no ne;">
    <h3>선택목록 데이터일괄 입력</h3>
    <p style="padding:30px 0 20px">
        <label for="" class="slt_label" style="display:none;">
            <span>라인<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select id="o_line">
                <option value="">-라인선택-</option>
                <?=$line_form_options?>
            </select>
        </label>
        <label for="" class="slt_label">
            <span>완료일<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <input type="text" id="o_date" value="" class="tbl_input o_date_end" style="width:80px;" autocomplete="off">
        </label>
        <label for="" class="slt_label" style="display:none;">
            <span>상태<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select name="o_status" id="o_status">
                <option value="">-선택-</option>
                <?=$g5['set_oro_status_value_options']?>
            </select>
        </label>
        <input type="button" id="slt_input" onclick="slet_input(document.getElementById('form01'));" value="선택항목 일괄입력" class="btn btn_02">
    </p>
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
<form name="form01" id="form01" action="./order_practice_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
        <th scope="col" id="orp_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">ID</th>
        <th scope="col">지시번호</th>
        <th scope="col">라인</th>
        <th scope="col">시작일</th>
        <th scope="col">완료일</th>
        <th scope="col">계획무게</th>
        <!--th scope="col">실시간생산</th-->
        <!--th scope="col">수주/출하</th-->
        <th scope="col">상태</th>
        <th scope="col">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
        <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $oop = sql_fetch(" SELECT SUM(oop_count) AS cnt FROM {$g5['order_out_practice_table']} WHERE orp_idx = '{$row['orp_idx']}' ");
        $s_mod = '<a href="./order_practice_form.php?'.$qstr.'&amp;w=u&amp;orp_idx='.$row['orp_idx'].'" class="btn btn_03">수정</a>';
        $s_copy = '<a href="./order_practice_form.php?'.$qstr.'&w=c&orp_idx='.$row['orp_idx'].'" class="btn btn_03" style="margin-right:5px;">복제</a>';

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['orp_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="orp_idx[<?php echo $row['orp_idx'] ?>]" value="<?php echo $row['orp_idx'] ?>" class="orp_idx_<?php echo $row['orp_idx'] ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['orp_name']); ?> <?php echo get_text($row['orp_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['orp_idx'] ?>" id="chk_<?php echo $i ?>">
            <div class="chkdiv_btn" chk_no="<?=$i?>"></div>
        </td>
        <td class="td_orp_id"><?=$row['orp_idx']?></td>
        <td class="td_orp_order_no"><?=$row['orp_order_no']?></td><!-- 지시번호 -->
        <td class="td_orp_operation_line"><?=$g5['line_name'][$row['trm_idx_line']]?></td><!-- 공정/라인 -->
        <td class="td_orp_start_date"><?=$row['orp_start_date']?></td><!-- 시작일 -->
        <td class="td_orp_done_date td_orp_done_date_<?=$row['orp_idx']?>""><!-- 완료일 -->
            <?php $row['orp_done_date'] = ($row['orp_done_date']=='0000-00-00'||!$row['orp_done_date'])?'':$row['orp_done_date']; ?>
            <input type="text" name="orp_done_date[<?=$row['orp_idx']?>]" orp="1" orp_idx="<?=$row['orp_idx']?>" value="<?=$row['orp_done_date']?>" readonly class="tbl_input shf_one orp_done_date_<?=$row['orp_idx']?>" style="width:80px;">
        </td>
        <td class="td_orp_count"><!-- 지시수량 -->
        <?=number_format($oop['cnt'])?>&nbsp;&nbsp;kg
        </td>
        <td class="td_orp_status"><?=$g5['set_orp_status_value'][$row['orp_status']]?></td><!-- 상태 -->
        <td class="td_mng">
			<?=$s_mod?>
		</td>
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
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>
    <?php if($is_admin){ ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>
</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>


<script>
$("input[name=orp_start_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=orp_done_date]").datepicker('option','minDate',selectedDate);} });
$("input[name=orp_done_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=orp_start_date]").datepicker('option','maxDate',selectedDate);} });


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
