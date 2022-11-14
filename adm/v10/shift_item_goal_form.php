<?php
// 이건 구지 안 해도 될 듯 해서 일단 중지합니다.
$sub_menu = "950135";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'shift_item_goal';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&ser_cod_group='.$ser_cod_group.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들


if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    ${$pre}[$pre.'_status'] = 'ok';
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    $shf = get_table_meta('shift','shf_idx',${$pre}['shf_idx']);
    $mmi = get_table_meta('mms_item','mmi_idx',${$pre}['mmi_idx']);
    $mms = get_table_meta('mms','mms_idx',$shf['mms_idx']);
    // print_r3($shf);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


    // 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '기종별목표 '.$html_title;
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

?>
<style>
.mms_name {margin-left:15px;}
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
<input type="hidden" name="ser_cod_group" value="<?php echo $ser_cod_group ?>">
<input type="hidden" name="ser_cod_type" value="<?php echo $ser_cod_type ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>각종 고유번호(업체번호, IMP번호..)들은 내부적으로 다른 데이타베이스 연동을 통해서 정보를 가지고 오게 됩니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_1" style="width:12%;">
        <col class="grid_3">
    </colgroup>
	<tbody>
	<tr> 
		<th scope="row">교대선택</th>
		<td>
            <input type="hidden" name="mms_idx" value="<?=$shf['mms_idx']?>"><!-- 설비번호 -->
            <input type="hidden" name="shf_idx" value="<?=$sig['shf_idx']?>"><!-- 교대고유번호 -->
            <input type="hidden" name="mms_name" value="<?=$mms['mms_name']?>"><!-- 설비명 -->
			<input type="text" name="sig_shf_no" value="<?php echo $sig['sig_shf_no'] ?>" id="sig_shf_no" class="frm_input required" style="width:40px;" required readonly> 교대
            <a href="./shift_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_shift">교대찾기</a>
            <span class="mms_name"><?=$mms['mms_name']?></span>
		</td>
    </tr>
	<tr>
		<th scope="row">기종선택</th>
		<td>
            <input type="hidden" name="mmi_idx" value="<?=$sig['mmi_idx']?>"><!-- 기종고유번호 -->
			<input type="text" name="mmi_name" value="<?php echo $mmi['mmi_name'] ?>" id="mmi_name" class="frm_input required" required readonly>
            <a href="./mmi_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_item">기종찾기</a>
		</td>
	</tr>
	<tr>
        <th scope="row">목표</th>
		<td>
			<input type="text" name="sig_item_target" value="<?php echo $sig['sig_item_target'] ?>" id="sig_item_target" class="frm_input required" required style="width:70px;" onKeyUp="javascript:chk_Number(this);">
            / <span class="shift_goal"><?=number_format($shf['shf_target_'.$sig['sig_shf_no']])?></span>
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
	// 교대찾기 버튼 클릭
	$("#btn_shift").click(function(e) {
		e.preventDefault();
		var url = g5_user_admin_url+"/shift_select.php?frm=fwrite&file_name=<?php echo $g5['file_name']?>";
		winShftSelect = window.open(url, "winShftSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winShftSelect.focus();
	});

    // 기종찾기
    $(document).on('click','#btn_item',function(e){
        e.preventDefault();
        var mms_idx = $('input[name=mms_idx]').val();
        if(mms_idx=='') {
            alert('교대를 먼저 선택하세요.');
        }
        else {
            var href = $(this).attr('href');
            winItemSelect = window.open(href+'&mms_idx='+mms_idx,"winItemSelect","left=100,top=100,width=520,height=600,scrollbars=1");
            winItemSelect.focus();
        }
    });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_target]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

// 숫자만 입력
function chk_Number(object){
$(object).keyup(function(){
      $(this).val($(this).val().replace(/[^0-9|-]/g,""));
  });
}

function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
