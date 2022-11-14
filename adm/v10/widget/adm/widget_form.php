<?php
$sub_menu = "990150";
include_once('./_common.php');
auth_check($auth[$sub_menu], 'r');

$g5['title'] = '위젯 '.($w == '' ? '등록' : '수정');
include_once('../../_head.php');
$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_wg_basic">기본 설정</a></li>
</ul>';
//환경설정 set_name값을 일반변수로 변경 예)$g5['setting']['set_country'] => $set_country
foreach ( $g5['setting'] as $key => $val ) $$key = $val;
if($w == 'u'){
	$wigs = sql_fetch(" SELECT * FROM {$g5['widget_table']} WHERE wig_idx = '{$wig_idx}' ");
	foreach($wigs as $key => $val){ $$key = $val; }
}
//수정모드에서 비활성화할 선택박스에 사용하는 변수
$select_wu_disable_flag = ($w == 'u') ? 1 : 0;
//수정모드에서 비활성화할 라디오버튼박스에 사용하는 변수
$radio_wu_disable_flag = ($w == 'u') ? 1 : 0;

$colspan7=7;
$colspan5=5;
$colspan3=3;

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/multifile/jquery.MultiFile.css">', 1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/multifile/jquery.MultiFile.min.js"></script>',1);
?>
<div id="wg_frm" class="wg_frm">
<!-- name="fwidgetform" id="fwidgetform" action="./board_form_update.php" onsubmit="return fwidgetform_submit(this)" method="post" enctype="multipart/form-data" -->
<form name="fwidgetform" id="fwidgetform" onsubmit="return fwidgetform_submit(this)" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="wig_idx" value="<?php echo $wig_idx ?>">
<input type="hidden" name="token" value="">

<section id="anc_wg_basic">
	<h2 class="h2_frm">위젯 설정
		<?php if($w == 'u'){ ?>
		<p id="wg_dt" class="wg_fs085 wg_fcgray wg_hide">
			<span id="wg_updata_dt" class="wg_fcgray wg_ml10">최종수정 : <?=$wig_update_dt?></span>
			<span id="wg_updata_dt" class="wg_fcgray wg_ml10">최초등록 : <?=$wig_reg_dt?></span>
		</p>
		<?php } ?>
	</h2>
    <?php //echo $pg_anchor; ?>
	<table class="tbl_frm">
		<colgroup>
			<col span="1" width="85">
			<col span="1" width="185">
			<col span="1" width="85">
			<col span="1" width="185">
			<col span="1" width="85">
			<col span="1" width="185">
			<col span="1" width="85">
			<col span="1" width="185">
		</colgroup>
		<tbody>
			<tr>
				<th>위젯코드</th>
				<td colspan="<?=$colspan3?>" class="wg_help wg_wdx70" style="position:relative;">
					<?php
					if($w == ''){$cd_required = ' required';$cd_readonly = '';echo wgf_help("위젯을 서로 구분하는 고유 식별코드입니다. <br>중복되지 않게 영문 및 숫자로 입력하세요.\r\n(예: top01, top02, left01, left02 등)",1,'#f9fac6','#333333');}
					else{$cd_required = '';$cd_readonly = ' readonly';}
					?>
					<input type="text" name="wig_cd" id="wig_cd"<?=$cd_required.$cd_readonly?> class="wg_wdp62<?=$cd_required?>" value="<?=$wig_cd?>" size="30">
					<span id="wig_cd_chk" class="wg_fs100" style="position:absolute;top:5px;right:5px;" state="<?php echo ($w != '') ? 1 : 0; ?>"></span>
				</td>
				<th>언어환경</th>
				<td>
					<?php
					$lng_disabled = ($w == 'u') ? 1 : 0;
					?>
					<?php echo wgf_select_selected($set_language, 'wig_country', $wig_country, 0, 1,$lng_disabled);//인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status(name속성)','ok(값)',0(값없음활성화),1(필수여부)) ?>
				</td>
				<th>사용범주</th>
				<td class="wg_help ok">
					<?php echo wgf_help(($w == 'u') ? "수정모드에서는 기존범주를 변경할 수 없습니다." : "위젯을 사용할 범주를 설정해 주세요.",1,'#f9fac6','#333333'); ?>
					<?php
					$cate_disabled = ($w == 'u') ? 1 : 0;
					?>
					<?php echo wgf_select_selected($set_purpose, 'wig_db_category', $wig_db_category, 0, 1, $cate_disabled);// $select_wu_disable_flag);//인수('pending=대기,ok=정상,hide=숨김,trash=삭제','set_status(name속성)','ok(값)',0(값없음활성화),1(필수여부)) ?>
				</td>
			</tr>
			<tr>
				<th>위젯이름</th>
				<td class="bwg_help" colspan="<?=$colspan3?>">
					<?php echo wgf_help("식별하기 쉬운 위젯이름을 기입해 주세요.",1,'#f9fac6','#333333'); ?>
					<input type="text" name="wig_name" id="wig_name" required class="wg_wdp100 required" value="<?=$wig_name?>" size="30">
				</td>
				<th>위젯설명</th>
				<td class="bwg_help" colspan="<?=$colspan3?>">
					<?php echo wgf_help("위젯에 대한 간단한 설명을 기입해 주세요.",1,'#f9fac6','#333333'); ?>
					<input type="text" name="wig_desc" id="wig_desc" class="wg_wdp100" value="<?=$wig_desc?>" size="30">
				</td>
			</tr>
			<tr>
				<th>상태</th>
				<td colspan="<?=$colspan3?>"><?php echo wgf_radio_checked(common_status, 'wig_status', $wig_status);//인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status(name속성)','ok(값)') ?></td>
				<th>사용TABLE</th>
				<td class="bwg_help">
					<?php echo wgf_help("위젯에서 사용할 디비 테이블(영문)을 기입해 주세요.(필수아님)<br>(위젯관련TABLE 외의 TABLE명을 기입)",1,'#f9fac6','#333333'); ?>
					<input type="text" name="wig_db_table" id="wig_db_table" class="wg_wdp100" value="<?=$wig_db_table?>" size="30">
				</td>
				<th>사용ID</th>
				<td class="bwg_help">
					<?php echo wgf_help("위젯에서 사용할 디비 테이블의 idx(숫자)를 기입해 주세요.(필수아님)",1,'#f9fac6','#333333'); ?>
					<input type="text" name="wig_db_idx" id="wig_db_idx" class="wg_wdp100" value="<?=$wig_db_idx?>" size="30">
				</td>
			</tr>
			<tr class="date_contain">
				<th>디바이스</th>
				<td class="bwg_help wg_wdx130">
					<?php echo ($w == '') ? wgf_help("위젯을 표시할 디바이스를 설정해 주세요.",1,'#f9fac6','#333333') : wgf_help("수정모드에서는 디바이스를 변경할 수 없습니다.",1,'#f9fac6','#333333'); ?>
					<?php echo wgf_radio_checked(device, 'wig_device', $wig_device, $radio_wu_disable_flag);//인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status(name속성)','ok(값)',0(값없음활성화),1(필수여부)) ?>
				</td>
				<th>스킨</th>
				<td class="bwg_help" style="position:relative;">
					<?php if($w != 'u'){?>
						<?php echo wgf_help("위젯의 스킨을 설정해 주세요.",1,'#f9fac6','#333333'); ?>
						<div id="td_widget_skin"></div>
						<div id="td_widget_skin_btn"></div>
					<?php }else{ ?>
						<input type="hidden" name="wig_skin" value="<?=$wig_skin?>">
						<?php echo $wig_skin;?>
					<?php } ?>
				</td>
				<th>시작일시</th>
				<td class="bwg_help">
					<?php echo wgf_help("위젯표시의 시작일시를 설정해 주세요.<br>시작일시를 설정하지 않으면 상태값이 '표시'일때 등록과 동시에 노출됩니다.",1,'#f9fac6','#333333'); ?>
					<div>
					<input type="text" value="<?=substr($wig_begin_dt,0,10)?>" class="bwg_from_date wg_wdx80"><select class="bwg_from_time wg_ml3" val="<?=substr($wig_begin_dt,11,5)?>"></select>
					</div>
					<input type="hidden" name="wig_begin_dt" id="wig_begin_dt" old-dt="<?=$wig_begin_dt?>" value="<?=$wig_begin_dt?>" class="wg_datetime wg_wdp100">
				</td>
				<th>종료일시</th>
				<td class="bwg_help">
					<?php echo wgf_help("위젯표시의 종료일시를 설정해 주세요.<br>종료일시를 설정하지 않으면 상태값이 '표시'일때 영구적으로 노출됩니다.",1,'#f9fac6','#333333'); ?>
					<div>
					<input type="text" value="<?=substr($wig_end_dt,0,10)?>" class="bwg_to_date wg_wdx80"><select class="bwg_to_time wg_ml3" val="<?=substr($wig_end_dt,11,5)?>"></select>
					</div>
					<input type="hidden" name="wig_end_dt" id="wig_end_dt" old-dt="<?=$wig_end_dt?>" value="<?=$wig_end_dt?>" class="wg_datetime wg_wdp100">
				</td>
			</tr>
			<tr>
				<th>위젯메뉴얼 URL</th>
				<td class="bwg_help" colspan="<?=$colspan7?>">
					<?php echo wgf_help("해당 위젯의 메뉴얼 URL입력란입니다. 데이터가 삭제되지 않도록 주의해 주세요.",1,'#f9fac6','#333333'); ?>
					<?php
					$wig_manual_url = bwg_g5_url_check($wig_manual_url);
					?>
					<input type="text" name="wig_manual_url" id="wig_manual_url" class="wg_wdx500" value="<?=$wig_manual_url?>" size="30">
					<?php if($wig_manual_url){ ?><a href="<?=$wig_manual_url?>" target="_blank" class="wg_btn_m_primary" style="position:relative;top:2px;"><?=$wig_cd?> 메뉴얼</a><?php } ?>
				</td>
			</tr>
		</tbody>
	</table>
</section><!--#anc_wg_basic-->

<!--해당스킨파일의 해당하는 설정폼스킨을 표시하는 영역-->
<section id="anc_wg_option">	
</section><!--#anc_wg_option-->

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
	<a class="btn btn_02" href="<?=G5_widget_ADMIN_URL?>/widget_list.php?<?php echo $qstr ?>">목록</a>
</div>

</form><!--#fboardform-->
</div><!--#wg_frm-->

<script>
var w = '<?=$w?>';
var wig_idx = '<?=$wig_idx?>';
var device = ('<?=$wig_device?>' == '') ? 'pc' : '<?=$wig_device?>';//$('input[name="wig_device"]').val();
var skin = "<?=$wig_skin?>";

timePicker($('.bwg_from_time'),12);
timePicker($('.bwg_to_time'),12);

$(function(){
	eventOn();
	$('.bwg_from_date, .bwg_from_time, .bwg_to_date, .bwg_to_time').on('change',function(e){
		e.stopPropagation();
		e.preventDefault();
		var inputdate = null;
		var inputtime = null;
		var inputdatetime = $(this).parent().siblings('.wg_datetime');
		if($(this).hasClass('bwg_dt')){//현재 날짜입력일때
			inputdate = $(this);
			inputtime = $(this).siblings('.bwg_time');
			if(inputdate.val()){
				if(bwg_dt_valid(inputdate.val())){//날짜 입력값이 올바르면
					inputdatetime_val = inputdate.val()+' '+inputtime.val()+':00';
				}else{ //날짜입력이잘못되었으면
					alert('올바른 날짜입력이 아닙니다.');
					inputdatetime_val = '';
					inputtime.find('option').attr('selected',false);
					inputtime.find('option[value="00:00"]').attr('selected',true);
					inputdate.val('').focus();
				}
			}else{
				inputdatetime_val = '';
				inputtime.find('option').attr('selected',false);
				inputtime.find('option[value="00:00"]').attr('selected',true);
			}
			inputdatetime.val(inputdatetime_val);
		}else if($(this).hasClass('bwg_time')){ //현재 시간입력일때
			inputdate = $(this).siblings('.bwg_dt');
			inputtime = $(this);
			//만약 날짜에 입력이 없으면 날짜부터 입력하라는 경고창을 표시한다.
			if(!inputdate.val()){//시간 앞 날짜입력에 값이 없으면
				alert('날짜부터 입력하세요');
				inputtime.find('option').attr('selected',false);
				inputtime.find('option[value="00:00"]').attr('selected',true);
				inputdate.focus();
				return false;
			}else{ //시간 앞 날짜입력에 값이 있으면
				inputdatetime.val(inputdate.val()+' '+inputtime.val()+':00');
			}
		}
	});
	if(w == 'u'){
		widget_skin_select(device,skin,wig_idx);
	}else{
		widget_skin_select(device);
	}
});

//위젯 위치코드 입력시 중복여부를 체크하는 함수
function widget_code_repetition_check(wig_cd){
	if(wig_cd == ''){
		$('#wig_cd_chk').text('');
		return false;
	}
	$.ajax({
		type:"POST",
		url:"<?=G5_widget_ADMIN_AJAX_URL?>/widget_code_repetition_check.php",
		dataType:"text",
		data:{'wig_cd':wig_cd},
		success:function(response){
			if(response){
				response = Number(response);
				if(response != 0){
					$('#wig_cd_chk').text('입력불가').attr('state',0).css('color','red');
				}
				else
					$('#wig_cd_chk').text('입력가능').attr('state',1).css('color','blue');
			}
		},
		error:function(e){
			alert(e.responseText);
		}
	});
}

//위젯을 표시하는 스킨 선택박스를 호출하는 함수
function widget_skin_select(devc,checkskin,wig_idx){
	//eventOff();
	var category = $('#wig_db_category').val();
	$.ajax({
		type:"POST",
		url:"<?=G5_widget_ADMIN_AJAX_URL?>/widget_skin_select.php",
		dataType:"html",
		data:{'w':w, 'category':category, 'device':devc, 'skin':checkskin, 'wig_idx':wig_idx},
		success:function(response){
			$('#td_widget_skin').html(response);
			//console.log($('select[name=wig_skin]').val() != null);
			if(w != '') call_skin_config(devc,checkskin,w,wig_idx);

			//안에 스킨 선택 요소가 존재하면 스킨선택 모달창에 목록을 셋팅한다.
			if($('#td_widget_skin').find('select').find('option').length > 0){
				skin_select_modal_setting($('#td_widget_skin').find('select'));
			}
			eventOn();
			//alert($('select[name=wig_skin]').length);
		},
		error:function(e){
			alert(e.responseText);
		}
	});
}
//이벤트 일괄 활성화
function eventOn(){
	//console.log(6);
	//배너 코드를 입력하고 있을때 코드의 중복여부를 첵크함
	if(w == ''){
		$('#wig_cd').on('keyup blur',function(e){
			var inputVal = $.trim($(this).val());
			$(this).val(inputVal);
			var strReg = /[ㄱ-ㅎ|ㅏ-ㅣ|가-힣]/;
			var chk_han = strReg.test(inputVal);
			//console.log(chk_han);
			if(chk_han){
				$(this).siblings('#wig_cd_chk').attr('state',0).text('영문숫자만 입력').css({'color':'red'});
				$(this).val('').focus();
				return false;
			}
			widget_code_repetition_check($(this).val());
		});
	}
	
	<?php if($w != 'u'){ //신규등록시만 적용(부터) ?>
	//사용범주 변경시
	$('select[name="wig_db_category"]').on('change',function(e){
		e.stopImmediatePropagation();
		var dvc = $('input[name="wig_device"]').val();
		//alert(dvc);
		//디바이스 유형에 맞는 스킨 목록을 호출
		widget_skin_select(dvc);
		$('#anc_wg_option').empty();
	});
	
	//디바이스유형 변경시
	$('input[name="wig_device"]').on('change',function(){
		$(this).attr('checked',true).parent().siblings('label').find('input').attr('checked',false);
		var dvc = $(this).val();
		//alert(dvc);
		//디바이스 유형에 맞는 스킨 목록을 호출
		widget_skin_select(dvc);
		$('#anc_wg_option').empty();
	});
	<?php } //신규등록시만 적용(까지) ?>
	
	//스킨선택 버튼에 의해 스킨선택 모달을 표시한다.
	$('#td_widget_skin_btn').on('click',function(){
		$('#skin_select_modal').show();
	});
	
	//스킨선택 모달 닫기
	$('#skin_select_bg, #skin_select_modal_close').on('click',function(){
		$('#skin_select_modal').hide();
	});
	
	
	//스킨선택박스에 변경이벤트 발생
	$('select[name=wig_skin]').on('change',function(e){
		e.stopImmediatePropagation();
		if(w != ''){
			if($(this).val() != ''){ //선택박스 값이 존재하는 것 중에 다른값을 선택했다면
				//if(!confirm('변경시 해당 위젯코드의 기존 데이터는 전부 삭제됩니다.\r\n정말로 삭제 하시겠습니까?')){
				if(!confirm('정말로 변경 하시겠습니까?')){
					$('input[id="wig_device_'+'<?=$wig_device?>'+'"]').attr('checked',true);
					widget_skin_select('<?=$wig_device?>','<?=$wig_skin?>','<?=$wig_idx?>');
					return false;
				}
				
				//console.log($('.wig_device').find('input[checked="checked"]').val()+':'+$(this).val());
				var dvc = $('.wig_device').find('input[checked="checked"]').val();
				var skn = $(this).val();
				call_skin_config(dvc,skn,w,wig_idx);
			}else{ //선택박스 값이 존재하지 않는 것을 선택했다면
				//$('input[id="wig_device_'+'<?=$wig_device?>'+'"]').attr('checked',true);
				//widget_skin_select('<?=$wig_device?>','<?=$wig_skin?>','<?=$wig_idx?>');
				$('#anc_wg_option').empty();
				return false;
			}
		}else{
			if($(this).val() != ''){
				//새로운 옵션설정 스킨을 불러온다.
				
				//console.log($('.wig_device').find('input[checked="checked"]').val()+':'+$(this).val());
				var dvc = $('.wig_device').find('input[checked="checked"]').val();
				var skn = $(this).val();
				call_skin_config(dvc,skn);
			}else{
				$('#anc_wg_option').empty();
				return false;
			}
		}
	});
	
	//스킨선택 모달에서 스킨을 선택했을때 이벤트
	$('.skin_lst').on('click',function(e){
		if(!$(this).hasClass('selected')){
			$('.skin_lst').removeClass('selected');
			$(this).addClass('selected');
			
			$('#wig_skin option').attr('selected',false);
			$('#wig_skin option[value="'+$(this).attr('value')+'"]').attr('selected',true);
			$('#skin_select_modal_close').trigger('click');
			$('select[name=wig_skin]').trigger('change');
		}
	});
}
//이벤트 일괄 비활성화
function eventOff(){
	if(w == ''){
		$('#wig_cd').off('keyup blur');
	}
	$('select[name="wig_db_category"]').off('change');
	$('input[name="wig_device"]').off('change');
	$('#td_widget_skin_btn').off('click');
	$('#skin_select_bg, #skin_select_modal_close').off('click');
	$('select[name="wig_skin"]').off('change');
	$('.skin_lst').off('click');
}

//해당 스킨의 설정페이지스킨을 호출하는 함수
function call_skin_config(dvc,skn,w,wig_idx){
	//console.log("새거:"+dvc+":"+skn);
	//console.log("기존:"+device+":"+skin);
	//console.log(skn == undefined);
	if(skn != undefined){
		eventOff();
		//console.log(skn);return;
		$.ajax({
			type:"POST",
			url:"<?=G5_widget_ADMIN_AJAX_URL?>/widget_call_skin_config.php",
			dataType:"html",
			data:{'device':dvc, 'skin':skn, 'w':w, 'wig_idx':wig_idx},
			success:function(response){
				$('#anc_wg_option').empty();
				$(response).appendTo('#anc_wg_option');
				if($('#anc_wg_option div').length == 0 || $('#anc_wg_option table').length == 0){
					$('#anc_wg_option').empty();
				}
				
				eventOn();
				
			},
			error:function(e){
				$('#anc_wg_option').empty();
				alert(e.responseText);
			}
		});
	}
}
//모달창에 스킨선탠 목록을 셋팅하는 함수 
function skin_select_modal_setting(selectObj){
	$('#skin_select_title').text('');
	$('#skin_select_con').empty();
	$('#skin_select_title').text($('#wig_skin').attr('device').toUpperCase()+'위젯 스킨선택');
	selectObj.find('option').each(function(){
		var selected = ($(this).is(':selected')) ? ' selected' : '';
		$('<div class="skin_lst'+selected+'" value="'+($(this).text() != '사용안함' ? $(this).text() : '')+'"><img src="'+$(this).attr('thumb')+'"><div class="skin_name">'+$(this).text()+'</div></div>').appendTo('#skin_select_con');
	});
}


function fwidgetform_submit(f){
	//해당 위젯스킨의 _set/wg_form.skin.php의 validate함수로 정의가 있으면 사용
	if(typeof(fwidgetoptionform_submit) == 'function'){
		if(fwidgetoptionform_submit() == false)
			return false;
	}

    f.action = "<?=G5_widget_ADMIN_URL?>/widget_form_update.php";
	
    return true;
}
</script>

<?php
include_once ('../../_tail.php');