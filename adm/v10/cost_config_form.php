<?php
// 이건 구지 안 해도 될 듯 해서 일단 중지합니다.
$sub_menu = "950170";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'cost_config';
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
else if ($w == 'u'||$w == 'c') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    $mms = get_table_meta('mms','mms_idx',$csc['mms_idx']);
    // print_r3($mms);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


    // 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'추가':'수정'; 
$g5['title'] = '원가설정 '.$html_title;
// include_once('./report/_top_menu_kpi2.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

?>
<style>
    .mms_name {margin-left:15px;}
    .div_mip {color:#818181;}
    .mip_price {font-size:0.8em;color:#a9a9a9;margin-left:10px;}
    .btn_mip_delete {border:solid 1px #ddd;border-radius:3px;padding:1px 4px;font-size:0.7em;margin-left:10px;}
    .div_empty {color:#818181;}
    .btn_mip_delete {cursor:pointer;}
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
<input type="hidden" name="com_idx" value="<?=$_SESSION['ss_com_idx']?>">
<input type="hidden" name="ser_cod_group" value="<?php echo $ser_cod_group ?>">
<input type="hidden" name="ser_cod_type" value="<?php echo $ser_cod_type ?>">

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
            <input type="hidden" name="mms_idx" value="<?=$mms['mms_idx']?>"><!-- 설비번호 -->
			<input type="text" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name" class="frm_input required" required readonly>
            <a href="./mms_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mms">설비찾기</a>
		</td>
    </tr>
    <tr>
        <th scope="row">타입</th>
        <td colspan="3">
            <select name="<?=$pre?>_type" id="<?=$pre?>_type" class="required" required>
                <option value="">타입선택</option>
                <?=$g5['set_csc_type_options']?>
            </select>
            <script>$('select[name="<?=$pre?>_type"]').val('<?=${$pre}[$pre.'_type']?>');</script>
        </td>
    </tr>
    <?php
    $ar['id'] = 'csc_ym';
    $ar['name'] = '적용월';
    $ar['type'] = 'input';
    $ar['value'] = substr($csc['csc_ym'],0,7);
    $ar['help'] = 'YYYY-MM 형태로 입력하세요.';
    $ar['required'] = 'required';
    $ar['width'] = '60px';
    $ar['colspan'] = '3';
    echo create_tr_input($ar);
    unset($ar);
    ?>
    <tr>
    <?php
    $ar['id'] = 'csc_price';
    $ar['name'] = '가격';
    $ar['type'] = 'input';
    $ar['value'] = $csc['csc_price'];
    $ar['help'] = '전기세, 소모품, 유류대는 월비용, 작업자 인건비는 시간당 비용을 입력하세요.';
    $ar['required'] = 'required';
    $ar['width'] = '100px';
    $ar['value_type'] = 'number';
    $ar['form_script'] = 'onClick="javascript:chk_Number(this)"';
    $ar['colspan'] = '3';
    echo create_tr_input($ar);
    unset($ar);
    ?>
    </tr>
    <?php
    $ar['id'] = 'csc_memo';
    $ar['name'] = '메모';
    $ar['type'] = 'textarea';
    $ar['value'] = $csc['csc_memo'];
    $ar['colspan'] = 3;
    echo create_tr_input($ar);
    unset($ar);
    ?>
    <tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>">
        <th scope="row">상태</th>
        <td colspan="3">
            <select name="<?=$pre?>_status" id="<?=$pre?>_status"
                <?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
                <?=$g5['set_status_options']?>
            </select>
            <script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
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

    // 설비찾기 버튼 클릭
	$("#btn_mms").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('href');
		winShftSelect = window.open(href, "winShftSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winShftSelect.focus();
	});

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
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
