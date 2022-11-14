<?php
$sub_menu = "945113";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'material';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_start_date'] = G5_TIME_YMD;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    // print_r3(${$pre});

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '반제품재고 '.$html_title;
include_once ('./_head.php');
?>
<style>
    .bop_price {font-size:0.8em;color:#a9a9a9;margin-left:10px;}
    .btn_bop_delete {color:#0c55a0;cursor:pointer;margin-left:20px;}
    a.btn_price_add {color:#3a88d8 !important;cursor:pointer;}
</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>가격 변경 이력을 관리합니다. (가격 변동 날짜 및 가격을 지속적으로 기록하고 관리합니다.)</p>
    <p>가격이 변경될 미래 날짜를 지정해 두면 해당 날짜부터 변경될 가격이 적용됩니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4" style="width:15%;">
		<col style="width:35%;">
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
    </colgroup>
	<tbody>
    <tr>
        <tr>
            <th>품명</th>
            <td>
                <input type="hidden" name="bom_idx" value="<?=${$pre}['bom_idx']?>">
                <input type="text" name="mtr_name" value="<?=${$pre}['mtr_name']?>" required readonly class="frm_input required readonly" style="width:200px;">
                <?php if($w == ''){ ?>
                <a href="javascript:" link="./bom_select3.php?file_name=<?=$g5['file_name']?>" id="btn_bom" class="btn btn_01">상품선택</a>
                <?php } ?>
                </td>
            <th>단가</th>
            <td>
                <?php echo help("단가수정은 BOM관리에서 해 주셔야 합니다.") ?>
                <input type="text" name="mtr_price" readonly class="frm_input readonly" value="<?=number_format(${$pre}['mtr_price'])?>" style="width:80px;text-align:right;"> 원
            </td>
        </tr>
    </tr>
    <tr>
        <th>파트넘버(P/NO)</th>
        <td>
            <input type="text" name="bom_part_no" value="<?=${$pre}['bom_part_no']?>" required readonly class="frm_input required readonly" style="width:200px;">
        </td>
        <th>상품바코드</th>
        <td>
            <input type="text" name="mtr_barcode" value="<?=${$pre}['mtr_barcode']?>" required readonly class="frm_input required readonly" style="width:350px;">
        </td>
    </tr>
    <tr>
        <th>무게(kg)</th>
        <td>
            <input type="text" name="mtr_weight" value="<?=${$pre}['mtr_weight']?>" readonly class="frm_input readonly" style="width:60px;text-align:right;" onclick="javascript:chk_Number(this);"> kg
        </td>
        <th>생산라인</th>
        <td><?=$g5['line_name'][${$pre}['trm_idx_location']]?></td>
    </tr>
    <tr>
        <th>생산시간대번호</th>
        <td>
            <select name="mtr_shift" id="mtr_shift" class=""
            <?php if (!$is_admin) { ?>
                onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                <?=$g5['set_itm_shift4_value_options']?>
            </select>
            <script>
            <?php
            $mtr_shift = ($w == '') ? 1 : ${$pre}['mtr_shift'];
            ?>
            $('#mtr_shift').val('<?=$mtr_shift?>');
            </script>
        </td>
        <th scope="row">상태</th>
        <td>
            <select name="<?=$pre?>_status" id="<?=$pre?>_status" required class="required"
            <?php if (auth_check($auth[$sub_menu],"w",1)) { ?>
                onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                <?=$g5['set_half_status_options']?>
                <?php if($is_admin){ ?>
                <option value="trash">삭제</option>
                <?php } ?>
            </select>
            <script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
        </td>
    </tr>
    <tr>
        <th>메모</th>
        <td colspan="3">
            <textarea name="mtr_memo" class="frm_input" rows="7"><?=${$pre}['mtr_memo']?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row">히스토리</th>
        <td colspan="3">
            <?=nl2br(${$pre}[$pre.'_history'])?>
        </td>
    </tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 불량타입 숨김,보임
	$("input[name=mtr_defect]").click(function(e) {
        if( $(this).val() == 1 ) {
            $('#mtr_defect_type').show();
        }
        else
           $('#mtr_defect_type').hide();
	});

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price], #bom_moq, #bom_lead_time',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

// 제품찾기 버튼 클릭
$("#btn_bom").click(function(e) {
    e.preventDefault();
    var href = $(this).attr('link');
    winBomSelect = window.open(href, "winBomSelect", "left=300,top=150,width=650,height=600,scrollbars=1");
    winBomSelect.focus();
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function form01_submit(f) {
    if(!f.mtr_name.value) {
        alert('상품을 선태해 주세요.');
        f.mtr_name.focus();
        return false;
    }

    if(!f.bom_part_no.value) {
        alert('품번을 입력해 주세요.');
        f.bom_part_no.focus();
        return false;
    }

    if(!f.mtr_shift.value) {
        alert('작업구간을 입력해 주세요.');
        f.mtr_shift.focus();
        return false;
    }

    if(!f.mtr_status.value) {
        alert('상태값을 입력해 주세요.');
        f.mtr_status.focus();
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>