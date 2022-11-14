<style>
.tbl_frm01 td input[type="text"]{width:100%;}
.tbl_frm01 td input[type="text"]#com_zip{width:65px;}
</style>
<form name="form01" id="form01" action="./company_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="com_idx" value="<?php echo $com_idx; ?>">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs ?>">
<input type="hidden" name="ser_com_type" value="<?php echo $ser_com_type ?>">
<input type="hidden" name="ser_trm_idx_salesarea" value="<?php echo $ser_trm_idx_salesarea ?>">

<div class="local_desc01 local_desc" class="sound_only">
    <p>업체명이 변경되는 경우 기존 정보와 혼란이 생길 수 있으므로 업체명이 바뀌면 히스토리에 저장됩니다. (히스토리 항목은 수정할 수 없습니다.)</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<tbody>
	<tr>
		<td>
			<label for="com_name" class="sound_only">업체명</label>
			<input type="text" name="com_name" value="<?php echo $com['com_name'] ?>" placeholder="업체명" id="com_name" required class="frm_input required" <?=$saler_readonly?>>
				<?=$saler_mark?>
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_type" class="sound_only">업종구분</label>
			<select name="com_type" id="com_type" title="업종구분" required class="">
				<option value="">업종구분을 선택하세요.</option>
				<?php echo $g5['set_com_type_options']?>
			</select>
			<script>$('select[name=com_type]').val("<?=$com['com_type']?>").attr('selected','selected');</script>
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_class" class="sound_only">업체구분</label>
			<select name="com_class" id="com_class" title="업체구분" required class="">
				<option value="">업체구분을 선택하세요.</option>
				<?php echo $g5['set_com_class_options']?>
			</select>
			<script>$('select[name=com_class]').val("<?=$com['com_class']?>").attr('selected','selected');</script>
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_names" class="sound_only">업체명 히스토리</label>
			<?php echo help("업체명이 바뀌면 자동으로 히스토리가 기록됩니다. 업체명 검색 시 나타나지 않는 경우가 있어서 자동으로 기록을 남깁니다."); ?>
			<input type="<?=($is_admin=='super')?'text':'hidden';?>" name="com_names" value="<?php echo $com['com_names'] ?>" placeholder="업체명 히스토리" id="com_names" class="frm_input" <?=($is_admin!='super')?'readonly':''?>>
            <span style="display:<?=($is_admin=='super')?'none':'';?>"><?php echo $com['com_names'] ?></span>
		</td>
	</tr>
	<tr> 
		<td>
			<label for="com_email" class="sound_only">대표이메일</label>
			<?php echo help("세금계산서, 계약서, 약정서 등 모든 거래 시 소통할 수 있는 이메일 정보를 필수로 등록하세요."); ?>
			<input type="text" name="com_email" value="<?php echo $com['com_email'] ?>" placeholder="대표이메일" id="com_email" class="frm_input required" required <?=$saler_readonly?>>
			<?=$saler_mark?>
		</td>
	</tr>
	<tr> 
		<td>
			<label for="com_homepage" class="sound_only">홈페이지주소</label>
			<?php echo help("http(s):// 없이 그냥 홈페이지 주소만 입력해 주세요. ex. www.naver.com "); ?>
			<input type="text" name="com_homepage" value="<?php echo $com['com_homepage'] ?>" placeholder="홈페이지주소" id="com_homepage" class="frm_input">
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_president" class="sound_only">대표자<strong>필수</strong></label>
			<input type="text" name="com_president" value="<?php echo $com['com_president'] ?>" placeholder="대표자" id="com_president" required class="required frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_tel" class="sound_only">업체전화<strong>필수</strong></label><br>
			<input type="text" name="com_tel" value="<?php echo $com['com_tel'] ?>" placeholder="업체전화" id="com_tel" required class="required frm_input" size="20" minlength="2" maxlength="30" <?=$saler_readonly?>>
			<?=$saler_mark?>
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_biz_no" class="sound_only">사업자등록번호</label>
			<input type="text" name="com_biz_no" value="<?=$com['com_biz_no']?>" placeholder="사업자등록번호" id="com_biz_no" class="frm_input" size="20" minlength="2" maxlength="30" <?=$saler_readonly?>>
			<?=$saler_mark?>

		</td>
	</tr>
	<tr>
		<td>
			<label for="com_fax" class="sound_only">팩스</label>
			<input type="text" name="com_fax" value="<?php echo $com['com_fax'] ?>" placeholder="팩스번호" id="com_fax" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_biz_type1" class="sound_only">업태</label>
			<input type="text" name="com_biz_type1" value="<?=$com['com_biz_type1']?>" placeholder="업태" id="com_biz_type1" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_biz_type2" class="sound_only">업종</label>
			<input type="text" name="com_biz_type2" value="<?=$com['com_biz_type2']?>" placeholder="업종" id="com_biz_type2" class="frm_input" size="20" minlength="2" maxlength="30">
		</td>
	</tr>	
	<tr>
		<td class="td_addr_line" style="line-height:280%;">
			<label for="com_zip" class="sound_only">사업장 주소 <?=$saler_mark?></label>
			<?php echo help("사업장주소:[주소검색]을 통해 정확히 입력."); ?>
			<label for="com_zip" class="sound_only">우편번호</label>
			<input type="text" name="com_zip" value="<?php echo $com['com_zip1'].$com['com_zip2']; ?>" placeholder="우편번호" id="com_zip" class="frm_input readonly" maxlength="6" style="width:100px !important;" <?=$saler_readonly?>>
			<?php if(!auth_check($auth[$sub_menu],'d',1) || $w=='') { ?>
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'com_zip', 'com_addr1', 'com_addr2', 'com_addr3', 'com_addr_jibeon');">주소 검색</button>
			<?php } ?>
			<br>
			<input type="text" name="com_addr1" value="<?php echo $com['com_addr1'] ?>" placeholder="기본주소" id="com_addr1" class="frm_input readonly" size="40" <?=$saler_readonly?>>
			<label for="com_addr1" class="sound_only">기본주소</label>
			<input type="text" name="com_addr2" value="<?php echo $com['com_addr2'] ?>" placeholder="상세주소" id="com_addr2" class="frm_input" size="40" <?=$saler_readonly?>>
			<label for="com_addr2" class="sound_only">상세주소</label>
			<input type="text" name="com_addr3" value="<?php echo $com['com_addr3'] ?>" placeholder="참고항목" id="com_addr3" class="frm_input" size="40" <?=$saler_readonly?>>
			<label for="com_addr3" class="sound_only">참고항목</label>
			<input type="hidden" name="com_addr_jibeon" value="<?php echo $com['com_addr_jibeon']; ?>" id="com_addr_jibeon" <?=$saler_readonly?>>
		</td>
	</tr>	
	<tr style="display:<?=(!$member['mb_manager_account_yn'])?'none':''?>">
		<td>
			<label for="com_memo" class="sound_only">메모</label>
			<textarea name="com_memo" placeholder="메모" id="com_memo"><?php echo $com['com_memo'] ?></textarea>
		</td>
	</tr>
	<?php if($w == 'u') { ?>
	<tr>
		<td>
			<label for="license_img_file0" class="sound_only">사업자등록증 파일</label>
			<div style="float:left;margin-right:8px;"><?=$com['license_img'][0]['thumbnail_img']?></div>
			<?php echo help("사업자 등록증 이미지 파일을 등록해 주세요."); ?>
			<input type="file" name="license_img_file[0]" id="license_img_file0"><br>
			<?=$com['license_img'][0]['file']?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td>
			<label for="company_data_file0" class="sound_only">첨부 파일#1</label>
			<?php echo help("업체관련해서 추가로 관리해야 할 자료를 등록하세요."); ?>
			<input type="file" name="company_data_file[0]" id="company_data_file0"><br>
			<?=$com['company_data'][0]['file']?>
		</td>
	</tr>
	<tr>
		<td>
			<label for="company_data_file1" class="sound_only">첨부 파일#2</label>
			<?php echo help("업체관련해서 추가로 관리해야 할 자료를 등록하세요."); ?>
			<input type="file" name="company_data_file[1]" for="company_data_file1"><br>
			<?=$com['company_data'][1]['file']?>
		</td>
	</tr>
	<tr>
		<td>
			<label for="com_status" class="sound_only">상태</label>
			<?php echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="com_status" id="com_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_com_status_options']?>
			</select>
			<script>$('select[name="com_status"]').val('<?=$com['com_status']?>');</script>
			<?=$saler_mark?>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./company_list.php?<?php echo $qstr ?>" class="btn btn_02"><i class="fa fa-list" aria-hidden="true"></i></a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {

    // 영업자검색 클릭
    $("#btn_member").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=600,scrollbars=1");
        memberwin.focus();
        return false;
    });
	
	// 영업자 상태값 변경
	$(document).on('click','.set_cms_status',function(e) {
		e.preventDefault();
		var target_div = $(this).closest('div.div_salesman');
		var cms_idx = target_div.find('input[name^=cms_idx]').val();
		var cms_status = $(this).attr('cms_status');
		//alert(cms_idx +': '+ cms_status);

		if(confirm('영업자 상태값을 변경하시겠습니까?')) {
			// 로딩중 표시
			target_div.find('.img_cms_loading').show();

			//-- 디버깅 Ajax --//
			$.ajax({
				url:g5_user_admin_url+'/ajax/company.json.php',
				data:{"aj":"sales","cms_idx":cms_idx,"cms_status":cms_status},
				dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
			//$.getJSON(g5_user_admin_url+'/ajax/company.json.php',{"aj":"sales","cms_idx":cms_idx,"cms_status":cms_status},function(res) {
				//alert(res.sql);
				if(res.result == true) {
					//alert(res.msg);
					//alert(res.cms_status_text);
					target_div.find('input[name^=cms_status]').val( res.cms_status );
					target_div.find('.span_cms_status_text').text( res.cms_status_text );
					target_div.find('.span_cms_reg_dt').text( res.cms_reg_dt );
				}
				else {
					alert(res.msg);
				}
				
				// 로딩중 표시 숨김
				target_div.find('.img_cms_loading').hide();

				}, error:this_ajax_error	//<-- 디버깅 Ajax --//
			});
		}
	});

	// 추가된 영업자 div 삭제
	$(document).on('click','.span_saler_delete',function(e) {
		e.preventDefault();
		$(this).closest('div.div_salesman').remove();
	});

});

function form01_submit(f) {

    // 이메일 검증에 사용할 정규식
    var regExp = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
    if (f.com_email.value.match(regExp) != null) {
        //alert('Good!');
    }
    else {
        alert("올바른 이메일 주소가 아닙니다.");
        f.com_email.focus();
        return false; 
    }

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>