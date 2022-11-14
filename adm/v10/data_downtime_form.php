<?php
$sub_menu = "915170";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'data_downtime';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&ser_mms_idx='.$ser_mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// print_r3($member);
// print_r3($_SESSION);

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}['com_idx'] = $_SESSION['ss_com_idx'];
    ${$pre}['mst_type'] = 'quality';    // 품질
    ${$pre}['dta_start_dt'] = G5_SERVER_TIME;
    ${$pre}['dta_end_dt'] = G5_SERVER_TIME;
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u' || $w == 'c') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    // 비가동 항목명 추출
	$mst = get_table_meta('mms_status', 'mst_idx', ${$pre}['mst_idx']);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$html_title = ($w=='c')?'복제':$html_title; 
$g5['title'] = '비가동데이터 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

?>
<style>
.frm_date {width:75px;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">
<input type="hidden" name="com_idx" value="<?$_SESSION['ss_com_idx']?>">
<input type="hidden" name="ser_mms_idx" value="<?php echo $ser_mms_idx ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>각종 고유번호(업체번호, IMP번호..)들은 내부적으로 다른 데이타베이스 연동을 통해서 정보를 가지고 오게 됩니다.</p>
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
		<th scope="row">설비선택</th>
		<td colspan="3">
            <input type="hidden" name="mms_idx" value="<?=$dta['mms_idx']?>"><!-- 설비번호 -->
			<input type="text" name="mms_name" value="<?php echo $g5['mms'][$dta['mms_idx']]['mms_name'] ?>" id="mms_name" class="frm_input required" required readonly>
            <a href="./mms_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mms">설비찾기</a>
		</td>
    </tr>
	<tr>
        <th scope="row">비가동항목</th>
		<td colspan="3">
            <input type="hidden" name="mst_idx" value="<?=$dta['mst_idx']?>"><!-- 비가동번호 -->
			<input type="text" name="mst_name" value="<?php echo $mst['mst_name'] ?>" id="mst_name" class="frm_input required" required readonly>
            <a href="./mms_status_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mst">항목찾기</a>
		</td>
    </tr>
	<tr>
        <th scope="row">시작시간</th>
		<td>
            <input type="text" name="dta_start_dt" value="<?php echo date("Y-m-d H:i:s",$dta['dta_start_dt']) ?>" id="dta_start_dt" class="frm_input">
            <input type="checkbox" value="<?php echo date("Y-m-d H:i:s"); ?>" id="dta_start_dt_set_today" onclick="if (this.form.dta_start_dt.value==this.form.dta_start_dt.defaultValue) {
this.form.dta_start_dt.value=this.value; } else { this.form.dta_start_dt.value=this.form.dta_start_dt.defaultValue; }">
            <label for="dta_start_dt_set_today">현재시점</label>
		</td>
        <th scope="row">종료시간</th>
		<td>
            <input type="text" name="dta_end_dt" value="<?php echo date("Y-m-d H:i:s",$dta['dta_end_dt']) ?>" id="dta_end_dt" class="frm_input">
            <input type="checkbox" value="<?php echo date("Y-m-d H:i:s"); ?>" id="dta_end_dt_set_today" onclick="if (this.form.dta_end_dt.value==this.form.dta_end_dt.defaultValue) {
this.form.dta_end_dt.value=this.value; } else { this.form.dta_end_dt.value=this.form.dta_end_dt.defaultValue; }">
            <label for="dta_end_dt_set_today">현재시점</label>
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

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

    // 설비찾기 버튼 클릭
	$("#btn_mms").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winShftSelect = window.open(href, "winShftSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winShftSelect.focus();
	});

    // 비가동항목 찾기버튼 클릭
	$("#btn_mst").click(function(e) {
		e.preventDefault();

        if( $('input[name=mms_idx]').val() == '' ) {
            alert('설비를 먼저 선택하세요.');
            return false;
        }

        var href = $(this).attr('href')+'&sch_field=mst.mms_idx&sch_word='+$('input[name=mms_idx]').val();
		winShftSelect = window.open(href, "winShftSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winShftSelect.focus();
	});


});

function form01_submit(f) {
    // 교대시간 체크

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
