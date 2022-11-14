<style>
.bt_select{height:35px;line-height:35px;}

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
		<td colspan="2">
			<label for="com_idx" class="sound_only">업체선택</label>
			<input type="hidden" name="com_idx" id="com_idx" value="<?=$row['com_idx']?>" required class="frm_input required">
			<input type="text" id="com_name" value="<?=$row['com_name']?>" placeholder="업체선택" readonly required class="frm_input readonly required" style="width:50% !important;">
			<a href="javascript:" link="./_win_company_select.php" class="btn btn_02 com_select bt_select">업체선택</a>
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
		<td colspan="2">
			<?php
			$sbsql = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_company']}' ");
			?>
			<label scope="row" class="sound_only">견적업체담당자</label>
			<input type="hidden" name="mb_id_company" id="mb_id_company" value="<?=$row['mb_id_company']?>">
			<input type="text" value="<?=$sbsql['mb_name']?>" placeholder="견적업체담당자" id="mb_name_sb" readonly class="frm_input readonly" style="width:50% !important;">
			<a href="javascript:" link="./_win_submitter_select.php" class="btn btn_02 submitter_select bt_select">담당자선택</a>
			<script>
			$('.submitter_select').on('click',function(){
				if(!$('#com_idx').val()){
					alert('업체를 먼저 선택해 주세요.');
					$('#com_idx').focus();
					return false;
				}
				var href = $(this).attr('link')+'?com_idx='+$('#com_idx').val();
				var win_submitter_name = window.open(href,"win_submitter_select","width=400,height=640");
				win_submitter_name.focus();
				return false;
			});
			</script>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php
			$acsql = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_account']}' ");
			?>
			<label scope="row" class="sound_only">업체회계담당자</label>
			<input type="hidden" name="mb_id_account" id="mb_id_account" value="<?=$row['mb_id_account']?>">
			<input type="text" value="<?=$acsql['mb_name']?>" placeholder="업체회계담당자" id="mb_name_ac" readonly class="frm_input readonly" style="width:50% !important;">
			<a href="javascript:" link="./_win_account_select.php" class="btn btn_02 account_select bt_select">회계담당자</a>
			<script>
			$('.account_select').on('click',function(){
				if(!$('#com_idx').val()){
					alert('업체를 먼저 선택해 주세요.');
					$('#com_idx').focus();
					return false;
				}
				var href = $(this).attr('link')+'?com_idx='+$('#com_idx').val();
				var win_account_name = window.open(href,"win_account_select","width=400,height=640");
				win_account_name.focus();
				return false;
			});
			</script>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php
			$slsql = sql_fetch(" SELECT mb_name FROM {$g5['member_table']} WHERE mb_id = '{$row['mb_id_saler']}' ");
			?>
			<label scope="row" class="sound_only">영업담당자</label>
			<input type="hidden" name="mb_id_saler" id="mb_id_saler" value="<?=$row['mb_id_saler']?>" required>
			<input type="text" value="<?=$slsql['mb_name']?>" placeholder="영업담당자" id="mb_name_sl" required readonly class="frm_input required readonly" style="width:50% !important;">
			<a href="javascript:" link="./_win_saler_select.php" class="btn btn_02 saler_select bt_select">영업자선택</a>
			<script>
			$('.saler_select').on('click',function(){
				var href = $(this).attr('link');
				var win_saler_name = window.open(href,"win_saler_select","width=400,height=640");
				win_saler_name.focus();
				return false;
			});
			</script>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label scope="row" class="sound_only">발행번호</label>
			<input type="text" name="prj_doc_no" value="<?=$row['prj_doc_no']?>" placeholder="발행번호" class="frm_input" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php $preadonly = ($w != '' && $member['mb_level'] < 8) ? ' readonly' : ''; ?>
			<label scope="row" class="sound_only">프로젝트명</label>
			<input type="text" name="prj_name" value="<?=$row['prj_name']?>" placeholder="프로젝트명" required<?=$preadonly?> class="frm_input required<?=$preadonly?>" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label scope="row" class="sound_only">최종고객</label>
			<input type="text" name="prj_end_company" value="<?=$row['prj_end_company']?>" placeholder="최종고객" class="frm_input" style="width:100%;">
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<label scope="row" class="sound_only">요청날짜</label>
			<input type="text" name="prj_ask_date" id="prj_ask_date" value="<?=(($row['prj_ask_date'] == '' || $row['prj_ask_date'] == '0000-00-00') ? '' : $row['prj_ask_date'])?>" placeholder="요청일" class="frm_input" style="width:47% !important;">
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<label scope="row" class="sound_only">견적제출날짜</label>
			<input type="text" name="prj_submit_date" id="prj_submit_date" value="<?=(($row['prj_submit_date'] == '' || $row['prj_submit_date'] == '0000-00-00') ? '' : $row['prj_submit_date'])?>" placeholder="견적제출일" class="frm_input" style="width:47% !important;">
			<?php if($row['prj_qf_lst_idx']){ ?>
				<button type="button" class="btn btn_02 quot_email_send" style="height:35px;line-height:35px;">견적메일전송</button>
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<label scope="row" class="sound_only">수주날짜</label>
			<input type="text" name="prj_contract_date" id="prj_contract_date" value="<?=(($row['prj_contract_date'] == '' || $row['prj_contract_date'] == '0000-00-00') ? '' : $row['prj_contract_date'])?>" placeholder="수주일" class="frm_input" style="width:47% !important;">
		</td>
	</tr>
	<tr>
		<td style="width:50%;">
			<label scope="row" class="sound_only">제출금액</label>
			<input type="text" name="prj_price_submit" value="<?=$row['prj_price_submit']?>" placeholder="제출금액" class="frm_input" style="width:90% !important;text-align:right;">&nbsp;원
		</td>
		<td style="width:50%;">
			<label scope="row" class="sound_only">NEGO금액</label>
			<input type="text" name="prj_price_nego" value="<?=$row['prj_price_nego']?>" placeholder="NEGO금액" class="frm_input" style="width:90% !important;text-align:right;">&nbsp;원
		</td>
	</tr>
	<tr>
		<td style="width:50%;">
			<label scope="row" class="sound_only">수주금액</label>
			<input type="text" name="prj_price_order" value="<?=$row['prj_price_order']?>" placeholder="수주금액" class="frm_input" style="width:90% !important;text-align:right;">&nbsp;원
		</td>
		<!--td style="width:50%;">
			<label scope="row" class="sound_only">미수금</label>
			<input type="text" name="prj_receivable" value="<?=$row['prj_receivable']?>" placeholder="미수금" class="frm_input" style="width:90% !important;text-align:right;">&nbsp;원
		</td-->
		<td style="width:50%;">
			<label for="<?=$pre?>_status" class="sound_only">상태</label>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_prj_status_value_options']?>
			</select>
			<script>$('select[name="prj_status"]').val("<?=$row['prj_status']?>");</script>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<label for="<?=$pre?>_type" class="sound_only">프로젝트타입</label>
			<select name="<?=$pre?>_type" id="<?=$pre?>_type"
				<?php if (auth_check($auth[$sub_menu],"d",1)) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_prj_type2_options']?>
			</select>
			<script>$('select[name="prj_type"]').val("<?=$row['prj_type']?>");</script>
		</td>
	</tr>
	<tr>
		<td style="width:50%;">
			<label scope="row" class="sound_only">자사,타사구분</label>
			<select name="prj_belongto" id="prj_belongto" onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'>
				<?=$g5['set_prj_belongto_options']?>
			</select>
			<script>$('select[name="prj_status"]').val("<?=$row['prj_status']?>");</script>
			<!--input type="text" name="prj_belongto" value="<?=$row['prj_belongto']?>" placeholder="자사/타사구분" required class="frm_input required" style="width:130px;"-->
		</td>
		<td style="width:50%;">
			<label scope="row" class="sound_only">진행율</label>
			<input type="text" name="prj_percent" value="<?=$row['prj_percent']?>" placeholder="진행율" class="frm_input" style="width:20% !important;text-align:right;">&nbsp;%
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label scope="row" class="sound_only">프로젝트 지시사항</label>
			<?php echo help('프로젝트 지시사항을 입력해 주세요.') ?>
			<textarea name="prj_content" rows="5"><?=get_text(html_purifier($row['prj_content']), 0)?></textarea>
			<?php //echo editor_html('prj_content', get_text(html_purifier($row['prj_content']), 0)); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label scope="row" class="sound_only">수입지출 지시사항</label>
			<?php echo help('수입지출 지시사항을 입력해 주세요.') ?>
			<textarea name="prj_content2" rows="5"><?=get_text(html_purifier($row['prj_content2']), 0)?></textarea>
			<?php //echo editor_html('prj_content2', get_text(html_purifier($row['prj_content2']), 0)); ?>
		</td>
	</tr>
	<tr>
        <td colspan="2">
			<label for="multi_file_q" class="sound_only">견적서파일</label>
            <?php echo help('견적서 파일을 등록 해주세요.') ?>
			<input type="file" id="multi_file_q" name="prj_q_datas[]" multiple class="">
            <?php
			if(count($row['prj_f_quot'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_quot']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_quot'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
        </td>
    </tr>
	<tr>
        <td colspan="2">
			<label for="multi_file_o" class="sound_only">발주서파일</label>
            <?php echo help('발주서 파일을 등록 해주세요.') ?>
            <input type="file" id="multi_file_o" name="prj_o_datas[]" multiple class="">
			<?php
			if(count($row['prj_f_order'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_order']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_order'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
        </td>
    </tr>
	<tr>
        <td colspan="2">
			<label for="multi_file_c" class="sound_only">계약서파일</label>
            <?php echo help('계약서 파일을 등록 해주세요.') ?>
            <input type="file" id="multi_file_c" name="prj_c_datas[]" multiple class="">
			<?php
			if(count($row['prj_f_contract'])){
				echo '<ul>'.PHP_EOL;
				for($i=0;$i<count($row['prj_f_contract']);$i++) {
					echo "<li>[".($i+1).']'.$row['prj_f_contract'][$i]['file']."</li>".PHP_EOL;
				}
				echo '</ul>'.PHP_EOL;
			}
			?>
        </td>
    </tr>
	<tr>
        <td colspan="2">
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
<form id="formemail">
	<input type="hidden" name="prj_idx" value="">
	<input type="hidden" name="com_idx" value="">
	<input type="hidden" name="mb_id" value="">
	<input type="hidden" name="fle_idx" value="">
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

	$("#prj_ask_date, #prj_submit_date, #prj_contract_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    
    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name*=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

	<?php if($prj_idx && $row['prj_qf_lst_idx']){ ?>
	$('.quot_email_send').on('click',function(){
		var ajax_quot_mail_url = '<?=G5_USER_ADMIN_AJAX_URL?>/ajax_quot_email_send.php';
		var prj_idx = $('input[name="prj_idx"]').val();
		var com_idx = $('#com_idx').val();
		var mb_id = $('#mb_id_company').val();
		var fle_idx = $(this).attr('fle_idx');
		/*
		var frm = document.getElementById('formemail');
		frm.action = ajax_quot_mail_url;
		frm.method = "POST";
		frm.prj_idx.value = prj_idx;
		frm.com_idx.value = com_idx;
		frm.mb_id.value = mb_id;
		frm.fle_idx.value = fle_idx;
		frm.submit();
		return false;
		*/
		$.ajax({
			type: "POST",
			url: ajax_quot_mail_url,
			dataType: "text",
			data: {"prj_idx":prj_idx,"com_idx":com_idx,"mb_id":mb_id,"fle_idx":fle_idx},
			success:function(res) {
				//alert(res);
				if(res != 'email_success'){
					alert('메일전송에 실패했습니다. 이메일정보의 유무를 확인해 주세요.');
				}else{
					alert('메일전송에 성공했습니다.');
					$('#prj_submit_date').val(getFormattedDate(new Date()));
				}
			},
			error: function(req) {
				alert('Status: ' + req.status + ' \n\rstatusText: ' + req.statusText + ' \n\rresponseText: ' + req.responseText);
			}
		});

		//$('#prj_submit_date').val(getFormattedDate(new Date()));
	});
	<?php } //if($prj_idx && $row['prj_qf_lst_idx']){ ?>

	//견적서 멀티파일
	$('#multi_file_q').MultiFile();
	//발주서 멀티파일
	$('#multi_file_o').MultiFile();
	//계약서 멀티파일
	$('#multi_file_c').MultiFile();
	//기초자료 멀티파일
	$('#prj_ref_file').MultiFile();
});

function form01_submit(f) {
	<?php echo get_editor_js('prj_content'); ?>
	if(!f.prj_status.value){
		alert('상태값을 선택해 주세요');
		f.prj_status.focus();
		return false;
	}
	if(!f.prj_belongto.value){
		alert('자사/타사를 선택해 주세요');
		f.prj_belongto.focus();
		return false;
	}
    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>