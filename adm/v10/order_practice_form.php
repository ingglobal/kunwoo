<?php
$sub_menu = "930105";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'order_practice';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&sca='.$sca.'&ser_bom_type='.$ser_bom_type; // 추가로 확장해서 넘겨야 할 변수들

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김

    ${$pre}[$pre.'_count'] = 1;
    ${$pre}[$pre.'_start_date'] = G5_TIME_YMD;
    ${$pre}[$pre.'_status'] = 'pending';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    // print_r3(${$pre});
    $bom = get_table_meta('bom','bom_idx',${$pre}['bom_idx']);    // BOM
    $shf = get_table_meta('shift','shf_idx',${$pre}['shf_idx']);    // 작업구간
    $mb1 = get_table_meta('member','mb_id',${$pre}['mb_id']);    // 생산자

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '실행계획 '.$html_title;
include_once ('./_head.php');


?>
<style>
.span_oop_count {margin-left:20px;color:yellow;}
.span_com_idx_customer {margin-left:30px;}
.span_oro_date_plan {margin-left:20px;}
.div_oop_count {display:inline-block;float:right;color:yellow;}

.tbl_frm01:after {display:block;visibility:hidden;clear:both;content:'';}
.div_wrapper {display:inline-block;background:#1e2531;width:49.5%;}
.dd {min-width: 100%;}
.div_title {background:#000204;padding:15px;}
.div_title .bom_title {color:#00ffe7;font-weight:bold;}
.bom_detail:before {content:"(";margin-left:10px;}
.bom_detail:after {content:")";}
#del-item {margin-top:-6px;}
#nestable3 {padding:10px 20px;min-height:616px;}
.div_bom_list {min-height:600px;padding:10px 20px;}
#frame_bom_list {width:100%;min-height:600px;}
.empty_table {background:#1e2531;}
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
<input type="hidden" name="ser_bom_type" value="<?php echo $ser_bom_type ?>">
<input type="hidden" name="orp_order_no" value="<?=${$pre}['orp_order_no']?>">
<input type="hidden" name="orp_status" value="<?=${$pre}['orp_status']?>">
<input type="hidden" name="trm_idx_line" value="<?=${$pre}['trm_idx_line']?>">
<input type="hidden" name="orp_start_date" value="<?=${$pre}['orp_start_date']?>">


<div class="local_desc01 local_desc" style="display:no ne;">
    <p>확정된 실행계획은 수정할 수 없습니다. 생산에 사용할 자재가 할당되어 있기 때문에 변경하게 되면 재고 수량에 혼란이 생길 수 있습니다.</p>
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
        <th scope="row">작업지시번호</th>
        <td><?=${$pre}['orp_order_no']?></td>
        <th scope="row">상태</th>
        <td><?=$g5['set_orp_status_value'][${$pre}[$pre.'_status']]?></td>
    </tr>
	<tr>
        <th scope="row">라인설비</th>
		<td><?=$g5['line_name'][${$pre}['trm_idx_line']]?></td>
        <th scope="row">생산자</th>
		<td>
            <input type="hidden" name="mb_id" id="mb_id" value="<?=${$pre}['mb_id']?>">
			<input type="text" name="mb_name" id="mb_name" value="<?php echo $mb1['mb_name'] ?>" id="mb_name" class="frm_input" readonly>
            <a href="./member_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_member">찾기</a>
		</td>
    </tr>
    <tr>
        <?php
        $ar['id'] = 'orp_start_date';
        $ar['name'] = '생산시작일';
        $ar['type'] = 'input';
        $ar['width'] = '95px';
        $ar['readonly'] = 'readonly';
        $ar['required'] = 'required';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
        <?php
        $ar['id'] = 'orp_done_date';
        $ar['name'] = '생산종료일';
        $ar['type'] = 'input';
        $ar['width'] = '95px';
        $ar['readonly'] = 'readonly';
        $ar['required'] = 'required';
        $ar['value'] = ${$pre}[$ar['id']];
        echo create_td_input($ar);
        unset($ar);
        ?>
    </tr>
    <tr>
        <?php
        $ar['id'] = 'orp_memo';
        $ar['name'] = '메모';
        $ar['type'] = 'textarea';
        $ar['value'] = ${$pre}[$ar['id']];
        $ar['colspan'] = 3;
        echo create_tr_input($ar);
        unset($ar);
        ?> 
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
    $("input[name=orp_start_date],input[name=orp_done_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

        
    // 생산자
	$("#btn_member").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winMember = window.open(href, "winMember", "left=300,top=150,width=550,height=600,scrollbars=1");
        winMember.focus();
	});

});


// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

function form01_submit(f) {
    // 폼에 input 박스가 한개라도 있으면 안 된다.
    // input 처리를 하고 return false
    if(!f.mb_name.value){
        alert('생산자를 입력해 주세요.');
        f.mb_name.focus();
        return false;
    }

    if(!f.orp_done_date.value){
        alert('생산종료일을 입력해 주세요.');
        f.orp_done_date.focus();
        return false;
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
