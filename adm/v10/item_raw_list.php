<?php
$sub_menu = "945115";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '완제품재고관리';
include_once('./_head.php');
include_once('./_top_menu_itm.php');


$sql_common = " FROM {$g5['item_table']} AS itm
                    LEFT JOIN {$g5['bom_table']} AS bom ON itm.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['order_out_practice_table']} AS oop ON itm.oop_idx = oop.oop_idx
                    LEFT JOIN {$g5['order_practice_table']} AS orp ON oop.orp_idx = orp.orp_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " itm.itm_status NOT IN ('delete','trash','used') ";
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

if($shift){
    $where[] = " itm_shift = '".$shift."' ";
    $qstr .= $qstr.'&shift='.$shift;
}
if($itm_static_date){
    $where[] = " itm_date = '".$itm_static_date."' ";
    $qstr .= $qstr.'&itm_static_date='.$itm_static_date;
}
if($itm2_status){
    $where[] = " itm_status = '".$itm2_status."' ";
    $qstr .= $qstr.'&itm_status='.$itms_status;
}
if($forge_mms_idx){
    $where[] = " itm.mms_idx = '".(($forge_mms_idx == '-1')?0:$forge_mms_idx)."' ";
    $qstr .= $qstr.'&forge_mms_idx='.$forge_mms_idx;
}
if($itm_delivery){
    $where[] = " itm_delivery = '".$itm_delivery."' ";
    $qstr .= $qstr.'&itm_delivery='.$itm_delivery;
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "itm_reg_dt";
    $sod = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql,1);
$total_count = $row['cnt'];
// echo $total_count.'<br>';

$rows = 50;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT itm_idx
        , orp.orp_idx
        , itm.oop_idx
        , itm.bom_idx
        , itm_name
        , itm.bom_part_no
        , bom.bom_std
        , itm.itm_date
        , itm.mms_idx
        , itm.itm_weight
        , itm.itm_heat
        , itm.itm_defect
        , itm.itm_defect_type
        , itm.itm_delivery
        , itm.itm_status
        , itm.itm_reg_dt
        , itm.itm_update_dt
        , ROW_NUMBER() OVER (PARTITION BY itm_date, itm.bom_part_no ORDER BY itm_reg_dt) AS itm_num
        {$sql_common} {$sql_search} {$sql_order}
        LIMIT {$from_record}, {$rows}
";
// print_r3($sql);
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

// print_r2($g5);
?>
<style>
.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
#ui-datepicker-div{z-index:100 !important;}
.td_chk{position:relative;}
.td_chk .chkdiv_btn{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,255,0,0);}
.td_itm_name {text-align:left !important;}
.sp_pno{color:skyblue;font-size:0.85em;}
.sp_std{color:#e87eee;font-size:0.85em;}
.td_itm_part_no, .td_com_name, .td_itm_maker
,.td_itm_items, .td_itm_items_title, .td_mtr_std, {text-align:left !important;}
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
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:0px;z-index:2;}
.slt_label .data_blank{position:absolute;top:3px;right:-18px;z-index:2;font-size:1.1em;cursor:pointer;}
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
    <option value="itm.oop_idx"<?php echo get_selected($_GET['sfl'], "oop_idx"); ?>>생산계획ID</option>
    <option value="itm.itm_heat"<?php echo get_selected($_GET['sfl'], "itm_heat"); ?>>히트넘버</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<!--select name="shift" id="shift">
    <option value="">::작업구간::</option>
    <?php ;//$g5['set_itm_shift2_value_options']?>
</select-->
<select name="forge_mms_idx" id="forge_mms_idx">
    <option value="">::단조설비선택::</option>
    <?=$g5['forge_options']?>
    <option value="-1">외주단조</option>
</select>
<select name="itm2_status" id="itm2_status">
    <option value="">::상태선택::</option>
    <?=$g5['set_itm_status_value_options']?>
</select>
<?php
// $itm_static_date = ($itm_static_date) ? $itm_static_date : G5_TIME_YMD;
?>
<label for="itm_static_date"><strong class="sound_only">입고일 필수</strong>
<i class="fa fa-times" aria-hidden="true"></i>
<input type="text" name="itm_static_date" value="<?php echo $itm_static_date ?>" placeholder="통계일" id="itm_static_date" readonly class="frm_input readonly" style="width:95px;">
</label>
<script>
<?php
$sfl = ($sfl == '') ? 'itm_name' : $sfl;
?>
<?php if($sfl){ ?>
    $('#sfl').val('<?=$sfl?>');
<?php } ?>
<?php if($forge_mms_idx){ ?>
    $('#forge_mms_idx').val('<?=$forge_mms_idx?>');
<?php } ?>
<?php if($itm2_status){ ?>
    $('#itm2_status').val('<?=$itm2_status?>');
<?php } ?>
</script>
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>새로운 고객을 등록</p>
</div>

<div class="select_input">
    <h3>선택목록 데이터일괄 입력</h3>
    <p style="padding:30px 0 20px">
        <label for="" class="slt_label">
            <span>상태<i class="fa fa-times data_blank" aria-hidden="true"></i></span>
            <select name="o_status" id="o_status">
                <option value="">-선택-</option>
                <?=$g5['set_itm_status_options']?>
                <?php if($is_admin){ ?>
                <option value="trash">삭제</option>
                <?php } ?>
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
<form name="form01" id="form01" action="./item_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="forge_mms_idx" value="<?php echo $forge_mms_idx ?>">
<input type="hidden" name="itm2_status" value="<?php echo $itm2_status ?>">
<input type="hidden" name="itm_static_date" value="<?php echo $itm_static_date ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" id="itm_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">ID</th>
        <th scope="col"><?php echo subject_sort_link('itm_name') ?>품명</a></th>
        <th scope="col">생산계획ID</th>
        <th scope="col">통계일</th>
        <th scope="col">설비</th>
        <th scope="col">무게(kg)</th>
        <th scope="col">등록일시</th>
        <th scope="col">수정일시</th>
        <th scope="col">출하여부</th>
        <th scope="col">상태</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);

        $s_mod = '<a href="./item_form.php?'.$qstr.'&amp;w=u&amp;itm_idx='.$row['itm_idx'].'" class="btn btn_03">수정</a>';

        // history there items form the last. It is not gooe to see if many are being seen.
        $row['itm_histories'] = explode("\n",$row['itm_history']);
        // print_r2($row['itm_histories']);
        if(sizeof($row['itm_histories']) > 2) {
            $row['itm_history_array'][0] = "...";
            $x=1;
            for($j=sizeof($row['itm_histories'])-2;$j<sizeof($row['itm_histories']);$j++) {
                $row['itm_history_array'][$x] = $row['itm_histories'][$j];
                $x++;
            }
        }
        else {
            $row['itm_history_array'] = $row['itm_histories'];
        }



        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['itm_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="itm_idx[<?php echo $row['itm_idx'] ?>]" value="<?php echo $row['itm_idx'] ?>" id="itm_idx_<?php echo $row['itm_idx'] ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['itm_name']); ?> <?php echo get_text($row['itm_nick']); ?>님</label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['itm_idx'] ?>" id="chk_<?php echo $i ?>">
            <div class="chkdiv_btn" chk_no="<?=$i?>"></div>
        </td>
        <td class="td_itm_idx"><?=$row['itm_idx']?></td><!-- ID -->
        <td class="td_itm_name">
            <b><?=$row['itm_name']?></b>
            <?php if($row['bom_part_no']){ ?>
            <br><span class="sp_pno">[ <?=$row['bom_part_no']?> ]</span>
            <?php } ?>
            <?php if($row['bom_std']){ ?>
            <br><span class="sp_std">[ <?=$row['bom_std']?> ]</span>
            <?php } ?>
        </td><!-- 품명 -->
        <td class="td_oop_idx"><?=$row['oop_idx']?></td><!-- 생산계획ID -->
        <td class="td_itm_date"><?=$row['itm_date']?></td><!-- 통계일 -->
        <td class="td_itm_mms"><?=$g5['trms']['forge_idx_arr'][$row['mms_idx']]?></td><!-- 설비 -->
        <td class="td_itm_weight" style="text-align:right;">
            <?php if($is_admin){ ?>
                <input type="text" name="itm_weight[<?=$row['itm_idx']?>]" value="<?=$row['itm_weight']?>" class="frm_input" style="width:70px;text-align:right;">
            <?php } else { ?>
                <input type="hidden" name="itm_weight[<?=$row['itm_idx']?>]" value="<?=$row['itm_weight']?>">
                <?=$row['itm_weight']?>
            <?php } ?>
        </td><!-- 무게 -->
        <td class="td_itm_reg_dt">
            <?php if($is_admin){ ?>
                <input type="text" name="itm_reg_dt[<?=$row['itm_idx']?>]" value="<?=$row['itm_reg_dt']?>" class="frm_input" style="width:160px;text-align:center;">
            <?php } else { ?>
                <input type="hidden" name="itm_reg_dt[<?=$row['itm_idx']?>]" value="<?=$row['itm_reg_dt']?>">
                <?=substr($row['itm_reg_dt'],0,19)?>
            <?php } ?>
        </td><!-- 등록일시 -->
        <td class="td_itm_upt_dt">
            <?php if($is_admin){ ?>
                <input type="text" name="itm_update_dt[<?=$row['itm_idx']?>]" value="<?=$row['itm_update_dt']?>" class="frm_input" style="width:160px;text-align:center;">
            <?php } else { ?>
                <input type="hidden" name="itm_update_dt[<?=$row['itm_idx']?>]" value="<?=$row['itm_update_dt']?>">
                <?=substr($row['itm_update_dt'],0,19)?>
            <?php } ?>
        </td><!-- 수정일시 -->
        <td class="td_itm_delivery">
            <?php
                echo ($row['itm_delivery']) ? '<span style="color:skyblue;">출하</span>' : '';
            ?>
        </td>
        <td class="td_itm_status td_itm_status_<?=$row['itm_idx']?>">
            <input type="hidden" name="itm_status[<?php echo $row['itm_idx'] ?>]" class="itm_status_<?php echo $row['itm_idx'] ?>" value="<?php echo $row['itm_status']?>">
            <input type="text" value="<?php echo $g5['set_itm_status'][$row['itm_status']]?>" readonly class="tbl_input readonly itm_status_name_<?php echo $row['itm_idx'] ?>" style="width:170px;text-align:center;">
        </td><!-- 상태 -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='11' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (false){ //($is_admin){ //(!auth_check($auth[$sub_menu],'d')) { ?>
       <a href="<?=G5_URL?>/device/itm_ing/form.php" target="_blank" class="btn btn_02">생산시작</a>
       <a href="<?=G5_URL?>/device/itm_error/form.php" target="_blank" class="btn btn_02">검수</a>
       <a href="<?=G5_URL?>/device/itm_finish/form.php" target="_blank" class="btn btn_02">완제품코드매칭</a>
       <a href="<?=G5_URL?>/device/plt_label/form.php" target="_blank" class="btn btn_02">빠레트라벨링</a>
       <a href="<?=G5_URL?>/device/plt_delivery/form.php" target="_blank" class="btn btn_02" style="margin-right:200px;">출하</a>
    <?php } ?>
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php if($is_admin){ ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <a href="./item_form.php" id="member_add" class="btn btn_01">추가하기</a>
    <?php } ?>
    <?php } ?>
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

$('label[for="itm_static_date"] i').on('click',function(){
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





// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name^=itm_price], input[name^=itm_count], input[name^=itm_lead_time]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

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

    var o_status = document.getElementById('o_status').value;
    var o_status_name = $('#o_status').find('option[value="'+o_status+'"]').text();

    for(var idx in chk_idx){
        //console.log(idx);continue;
        if(o_status){
            $('.td_itm_status_'+chk_idx[idx]).find('input[type="hidden"]').val(o_status);
            $('.td_itm_status_'+chk_idx[idx]).find('input[type="text"]').val(o_status_name);
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
