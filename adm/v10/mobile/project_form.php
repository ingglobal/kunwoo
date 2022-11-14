<style>
/*멀티파일관련*/
input[type="file"]{position:relative;width:250px;height:80px;border-radius:10px;overflow:hidden;cursor:pointer;}
input[type="file"]::before{display:block;content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:#ddd;opacity:1;z-index:3;}
input[type="file"]::after{display:block;content:'파일선택\A(드래그앤드롭 가능)';position:absolute;z-index:4;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;}
.MultiFile-wrap ~ ul{margin-top:10px;}
.MultiFile-wrap ~ ul > li{margin-top:10px;}
.MultiFile-wrap .MultiFile-list{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label{position:relative;padding-left:25px;margin-top:10px;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove{position:absolute;top:0;left:0;font-size:0;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove::after{content:'×';display:block;position:absolute;left:0;top:0;width:20px;height:20px;border:1px solid #ccc;border-radius:50%;font-size:14px;line-height:20px;text-align:center;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span span.MultiFile-label{font-size:14px;border:1px solid #ccc;background:#eee;padding:2px 5px;border-radius:3px;line-height:1.2em;}
</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo $prj_idx; ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적추가 페이지입니다.</p>
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
		<td>
			<label for="com_idx" class="sound_only">업체선택</label>
			<input type="hidden" name="com_idx" id="com_idx" value="<?=$row['com_idx']?>" required class="frm_input required">
			<input type="text" id="com_name" value="<?=$row['com_name']?>" placeholder="업체선택" readonly required class="frm_input readonly required" style="width:50% !important;">
			<a href="javascript:" link="./_win_company_select.php" class="btn btn_02 com_select" style="height:35px;line-height:35px;">업체선택</a>
			<script>
			$('.com_select').on('click',function(){
				var href = $(this).attr('link');
				var win_com_name = window.open(href,"win_com_select","width=400,height=600");
				win_com_select.focus();
				return false;
			});
			</script>
		</td>
	</tr>
	<tr>
		<td>
			<label scope="row" class="sound_only">최종고객</label>
			<input type="text" name="prj_end_company" value="<?=$row['prj_end_company']?>" placeholder="최종고객" class="frm_input" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td>
			<select name="<?=$pre?>_type" id="<?=$pre?>_type">
				<?=$g5['set_prj_type_options']?>
			</select>
			<script>$('select[name="prj_type"]').val("<?=$row['prj_type']?>");</script>
			
			<label for="com_status" class="sound_only">상태</label>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<option value="">상태선택</option>
				<?=$g5['set_prj_status_options']?>
			</select>
			<script>$('select[name="prj_status"]').val("<?=$row['prj_status']?>");</script>
			
			<input type="text" name="prj_percent" value="<?=$row['prj_percent']?>" placeholder="진행율" class="frm_input" style="width:40px !important;text-align:right;">&nbsp;%
		</td>
	</tr>
	<tr>
		<td>
			<label scope="row" class="sound_only">프로젝트명</label>
			<?php $preadonly = ($w != '' && $member['mb_level'] < 8) ? ' readonly' : ''; ?>
			<input type="text" name="prj_name" value="<?=$row['prj_name']?>" placeholder="프로젝트명" required<?=$preadonly?> class="frm_input required<?=$preadonly?>" style="width:100%;">
		</td>
	</tr>
	<?php if($w != '') { ?>
	<tr>
		<td>
			<label scope="row" class="sound_only">프로젝트명 수정요청</label>
			<?php echo help("프로젝트명을 아래와 같이 수정 요청드립니다.<br>(수정하셨으면 아래 입력란은 <span style='color:red;'>공란</span>으로 만들어 주셔야 <span style='color:red;'>알람이 사라집니다</span>.)"); ?>
			<input type="text" name="prj_name_req" value="<?=$row['prj_name_req']?>" class="frm_input" style="width:250px;">
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td>
			<?php //echo editor_html('prj_content', get_text(html_purifier($row['prj_content']), 0)); ?>
			<textarea name="prj_content" row="5" placeholder="프로젝트 지시사항"><?=get_text(html_purifier($row['prj_content']))?></textarea>
		</td>
	</tr>
	<tr>
		<td>
			<?php //echo editor_html('prj_content', get_text(html_purifier($row['prj_content2']), 0)); ?>
			<textarea name="prj_content2" row="5" placeholder="수입지출 지시사항"><?=get_text(html_purifier($row['prj_content2']))?></textarea>
		</td>
	</tr>
	<tr>
        <td>
			<label for="prj_ref_file" class="sound_only">기초자료파일</label>
            <?php echo help('프로젝트 관련해서 참고 할 자료가 있으면 등록 해주세요.') ?>
            <input type="file" id="prj_ref_file" name="prj_ref_files[]" multiple class="">
			<?php
			if(count($row['prj_f_ref'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_ref']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_ref'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
        </td>
    </tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
    //$(document).on('click','.btn_item_target',function(e){
    //    var shf_idx = $(this).attr('shf_idx');
    //    var shf_no = $(this).attr('shf_no');
    //    // alert( shf_idx +'/'+ shf_no );
	//	var url = "./shift_item_goal_list.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
	//	win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
    //    win_item_goal.focus();
    //});

	$("#prj_ask_date, #prj_submit_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    
    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name*=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});
	
	//기초자료 멀티파일
	$('#prj_ref_file').MultiFile();
});

function form01_submit(f) {
	<?php //echo get_editor_js('prj_content'); ?>
	
	if(!f.prj_type.value){
		alert('프로젝트타입을 선택하세요.');
		f.prj_type.focus();
		return false;
	}
	
	if(!f.prj_status.value){
		alert('상태값을 선택하세요.');
		f.prj_status.focus();
		return false;
	}
	
    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>