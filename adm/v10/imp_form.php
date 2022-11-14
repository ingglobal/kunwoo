<?php
$sub_menu = "917180";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$html_title = ($w=='')?'추가':'수정'; 

$g5['title'] = 'iMP '.$html_title;
//include_once('./_top_menu_imp.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    $imp['com_idx'] = $_SESSION['ss_com_idx'];
	$com = get_table_meta('company','com_idx',$imp['com_idx']);
    $imp['imp_status'] = 'ok';
    $html_title = '추가';
    
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	$imp = get_table_meta('imp','imp_idx',$imp_idx);
	if (!$imp['imp_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',$imp['com_idx']);
	
	$html_title = '수정';
	
	$imp['imp_price'] = number_format($imp['imp_price']);

}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$imp[$check_array[$i]]} = ' checked';
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
//add_javascript(G5_USER_ADMIN_URL.'/js/imp_form.js', 0);
?>

<form name="form01" id="form01" action="./imp_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="imp_idx" value="<?php echo $imp_idx; ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>업체관리 및 업체담당자 관리는 업체관리 메뉴에 있습니다.</p>
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
	<tr style="display:none;">
		<th scope="row">업체명</th>
		<td colspan="3">
            <input readonly type="hidden" placeholder="업체ID" name="com_idx" value="<?php echo $imp['com_idx'] ?>" id="com_idx"
                    required class="frm_input required" style="width:120px;<?=$style_company?>">
            <input readonly type="text" placeholder="업체명" name="com_name" value="<?php echo $com['com_name'] ?>" id="com_name" 
                    <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
            <a href="./company_select.popup.php?frm=form01&d=<?php echo $d;?>" class="btn btn_02" id="btn_com_idx">검색</a>
		</td>
	</tr>
	<tr> 
		<th scope="row">iMP명</th>
		<td>
			<input type="text" name="imp_name" value="<?php echo $imp['imp_name'] ?>" id="imp_name" class="frm_input required" required style="width:200px;">
			<?=$saler_mark?>
		</td>
		<th scope="row">관리번호</th>
		<td>
			<input type="text" name="imp_idx2" value="<?php echo $imp['imp_idx2'] ?>" id="imp_idx2" class="frm_input required" required style="width:30px;">
			<?=$saler_mark?>
		</td>
	</tr>
	<tr>
		<th scope="row">도입날자</th>
		<td>
			<input type="text" name="imp_install_date" value="<?=(is_null_time($imp['imp_install_date']))?'':$imp['imp_install_date']?>" id="imp_install_date" class="frm_input" style="width:80px;">
		</td>
		<th scope="row">위치</th>
		<td>
			<input type="text" name="imp_location" value="<?php echo $imp['imp_location'] ?>" id="imp_location" class="frm_input required" required style="width:200px;">
			<?=$saler_mark?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="imp_memo">메모</label></th>
		<td colspan="3"><textarea name="imp_memo" id="imp_memo"><?php echo $imp['imp_memo'] ?></textarea></td>
	</tr>
	<tr>
		<th scope="row"><label for="imp_status">상태</label></th>
		<td colspan="3">
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="imp_status" id="imp_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_status_options']?>
			</select>
			<script>$('select[name="imp_status"]').val('<?=$imp['imp_status']?>');</script>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./imp_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
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

    // 업체검색
    $("#btn_com_idx").click(function(e) {
        e.preventDefault();
        var href = $(this).attr("href");
        companyselectwin = window.open(href, "companyselectwin", "left=100,top=100,width=520,height=600,scrollbars=1");
        companyselectwin.focus();
        return false;
    });

});

function form01_submit(f) {


    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
