<form name="form01" id="form01" action="./mms2_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="mms_idx" value="<?php echo $mms_idx; ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>설비와 연결된 iMP 및 장비그룹을 선택해 주셔야 합니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
	</colgroup>
	<tbody>
	<tr style="display:none;">
		<th scope="row">업체명</th>
		<td>
            <input readonly type="hidden" placeholder="업체ID" name="com_idx" value="<?php echo $mms['com_idx'] ?>" id="com_idx"
                    required class="frm_input required" style="width:120px;<?=$style_company?>">
            <input readonly type="text" placeholder="업체명" name="com_name" value="<?php echo $com['com_name'] ?>" id="com_name" 
                    <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
            <a href="./company_select.popup.php?frm=form01&d=<?php echo $d;?>" class="btn btn_02" id="btn_com_idx">검색</a>
		</td>
	</tr>
	<tr>
		<th scope="row">분류</th>
		<td>
            <select name="trm_idx_category" id="trm_idx_category">
                <option value="0">분류를 선택하세요.</option>
                <?=$mms_type_form_options?>
			</select>
			<script>$('select[name="trm_idx_category"]').val('<?=$mms['trm_idx_category']?>');</script>
        </td>
	</tr>
	<tr style="display:none;">
		<th scope="row">iMP선택</th>
		<td>
            <input readonly type="hidden" placeholder="IMP선택" name="imp_idx" value="<?php echo $mms['imp_idx'] ?>" id="imp_idx"
                    required class="frm_input required" style="width:120px;<?=$style_member?>">
            <input readonly type="text" placeholder="iMP명" name="imp_name" value="<?php echo $imp['imp_name'] ?>" id="imp_name" 
                    <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
            <a href="./imp_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_imp">검색</a>
        </td>
	</tr>
	<tr style="display:none;">
		<th scope="row">그룹선택</th>
		<td>
            <input readonly type="hidden" placeholder="그룹ID" name="mmg_idx" value="<?php echo $mms['mmg_idx'] ?>" id="mmg_idx"
                    required class="frm_input required" style="width:120px;<?=$style_member?>">
            <input readonly type="text" placeholder="그룹명" name="mmg_name" value="<?php echo $mmg['mmg_name'] ?>" id="mmg_name" 
                    <?=$required_company?> class="frm_input <?=$required_company_class?>" style="width:130px;<?=$style_company?>">
            <a href="./mms_group_select.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02" id="btn_mmg">검색</a>
        </td>
	</tr>
	<tr> 
		<th scope="row">설비명</th>
		<td>
			<input type="text" name="mms_name" value="<?php echo $mms['mms_name'] ?>" id="mms_name" class="frm_input required" required>
		</td>
	</tr>
	<tr> 
	<th scope="row">모델명</th>
		<td>
			<input type="text" name="mms_model" value="<?php echo $mms['mms_model'] ?>" id="mms_model" class="frm_input required" required>
		</td>
	</tr>
	<tr>
		<th scope="row">도입가격</th>
		<td>
			<input type="text" name="mms_price" value="<?php echo $mms['mms_price'] ?>" id="mms_price" class="frm_input" style="width:100px;">
		</td>
	</tr>
	<tr style="display:none;">
		<th scope="row">생산통계기준</th>
		<td>
			<select name="mms_set_output" id="mms_set_output">
                <option value="">생산통계기준을 선택하세요.</option>
                <?=$g5['set_mms_set_data_options']?>
			</select>
			<script>$('select[name="mms_set_output"]').val('<?=$mms['mms_set_output']?>');</script>
		</td>
	</tr>
	<tr style="display:none;">
		<th scope="row">데이타 URL</th>
		<td>
			<input type="text" name="mms_data_url" value="<?php echo $mms['mms_data_url'] ?>" id="mms_data_url" class="frm_input" style="width:200px;">
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mms_img_0">대표이미지</label></th>
		<td>
			<div style="float:left;margin-right:8px;"><?=$mms['mms_img'][0]['thumbnail_img']?></div>
			<?php echo help("대표 이미지 파일을 등록해 주세요."); ?>
			<input type="file" name="mms_img_file[0]" class="frm_input">
			<?=$mms['mms_img'][0]['file']?>
		</td>
	</tr>
	<tr>
		<th scope="row"><label for="mms_memo">메모</label></th>
		<td><textarea name="mms_memo" id="mms_memo"><?php echo $mms['mms_memo'] ?></textarea></td>
	</tr>
	<tr style="display:none;">
		<th scope="row"><label for="mms_status">상태</label></th>
		<td>
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="mms_status" id="mms_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_status_options']?>
			</select>
			<script>$('select[name="mms_status"]').val('<?=$mms['mms_status']?>');</script>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fix ed_top submit_btns">
    <a href="./mms2_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
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

    // IMP
    $(document).on('click','#btn_imp',function(e){
        e.preventDefault();
        var com_idx = $('input[name=com_idx]').val();
        if(com_idx=='') {
            alert('업체를 먼저 입력하세요.');
        }
        else {
            var href = $(this).attr('href');
            winIMPSelect = window.open(href+'&com_idx='+com_idx,"winIMPSelect","left=100,top=100,width=520,height=600,scrollbars=1");
            winIMPSelect.focus();
        }
    });

    // 장비그룹
    $(document).on('click','#btn_mmg',function(e){
        e.preventDefault();
        var com_idx = $('input[name=com_idx]').val();
        if(com_idx=='') {
            alert('업체를 먼저 입력하세요.');
        }
        else {
            var href = $(this).attr('href');
            winMMSGroup = window.open(href+'&com_idx='+com_idx,"winMMSGroup","left=100,top=100,width=520,height=600,scrollbars=1");
            winMMSGroup.focus();
        }
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
