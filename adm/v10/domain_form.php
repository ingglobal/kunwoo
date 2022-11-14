<?php
$sub_menu = '950600';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

// 수정 권한 설정 (영업자는 하단 상태일때만 수정 가능)
$is_modify_array = array('pending','reject','retry');

if ($w == 'u') {
	$html_title = '수정';

	$dmn = sql_fetch(" SELECT * FROM {$g5['domain_table']} WHERE dmn_idx = '$dmn_idx' ");
	if (!$dmn['dmn_idx'])
		alert('존재하지 않는 도메인입니다.');
	
	$mb = get_member($dmn['mb_id']);
	if (!$mb['mb_id'])
		alert('존재하지 않는 회원자료입니다.');

	$ct = sql_fetch(" SELECT * FROM {$g5['g5_shop_cart_table']} where ct_id = '".$dmn['ct_id']."' ");
	$od = sql_fetch(" SELECT * FROM {$g5['g5_shop_order_table']} where od_id = '".$ct['od_id']."' ");
	$it = sql_fetch(" SELECT * FROM {$g5['product_table']} where it_id = '".$ct['it_id']."' ");
	$com = sql_fetch(" SELECT * FROM {$g5['company_table']} where com_idx = '".$dmn['com_idx']."' ");
	$mb1 = get_member($dmn['mb_id']);		// 고객정보
	$mb2 = get_saler($dmn['mb_id_saler']);	// 영업자정보

	
	$style_mb_id = 'background-color:#dadada;';
	$style_mb_id_saler = 'background-color:#dadada;';
	$style_mb_name = 'background-color:#dadada;';
	$style_mb_name_saler = 'background-color:#dadada;';
	
	// 소유자 추가 정보 (serialize)
	// 주민등록번호+사업자등록번호+주소+영문주소
	$dmn_owner_infos = unserialize($dmn['dmn_owner_info']);
	//print_r2($dmn_owner_infos);
	if(is_array($dmn_owner_infos)) {
		foreach($dmn_owner_infos as $key => $value) {
			//echo $key.$value.'<br>';
			$dmn[$key] = $value;
		}
	}

	// 이전도메인등록기관정보 (serialize)
	$dmn_registerer_infos = unserialize($dmn['dmn_registerer_old']);
	//print_r2($dmn_registerer_infos);
	if(is_array($dmn_registerer_infos)) {
		foreach($dmn_registerer_infos as $key => $value) {
			//echo $key.$value.'<br>';
			$dmn[$key] = $value;
		}
	}

	// 메타 확장값(들) 추출
	$sql = " SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) AS mbr_metas FROM {$g5['meta_table']} 
				WHERE mta_db_table = 'domain' AND mta_db_id = '".$dmn_idx."' ";
	$mta2 = sql_fetch($sql,1);
	$pieces = explode(',', $mta2['mbr_metas']);
	foreach ($pieces as $piece) {
		list($key, $value) = explode('=', $piece);
		$dmn[$key] = $value;
	}
	unset($pieces);unset($piece);

	// 장바구니 정보가 있는 경우만
	if($ct['ct_id']) {
		$dmn['ct_info'] = '<span style="font-size:1.6em;">'.$ct['it_name'].'</span> ('.number_format($ct['ct_price']).', '.$ct['ct_status'].')'
								.'<br><b>업체명</b>: '.$ct['com_name'].' (대표자: '.$com['com_president'].')'
								.'<br>영업자: '.$ct['mb_name_saler'];
	}

}
else if ($w == '') {
    $html_title = '추가';

	if($ct_id) {
		$ct = sql_fetch(" SELECT * FROM {$g5['g5_shop_cart_table']} where ct_id = '".$ct_id."' ");
		$od = sql_fetch(" SELECT * FROM {$g5['g5_shop_order_table']} where od_id = '".$ct['od_id']."' ");
		$it = sql_fetch(" SELECT * FROM {$g5['product_table']} where it_id = '".$ct['it_id']."' ");
		$com = sql_fetch(" SELECT * FROM {$g5['company_table']} where com_idx = '".$ct['com_idx']."' ");
		$mb1 = get_member($ct['mb_id']);		// 고객정보
		$mb2 = get_saler($ct['mb_id_saler']);	// 영업자정보

		// 장바구니 정보가 있는 경우만
		if($ct['ct_id']) {
			$dmn['ct_info'] = '<span style="font-size:1.6em;">'.$ct['it_name'].'</span> ('.number_format($ct['ct_price']).', '.$ct['ct_status'].')'
									.'<br><b>업체명</b>: '.$ct['com_name'].' (대표자: '.$com['com_president'].')'
									.'<br>영업자: '.$ct['mb_name_saler'];
		}
									
		$dmn['ct_id'] = $ct_id;
		$dmn['mb_id'] = $ct['mb_id'];
		$dmn['com_idx'] = $com['com_idx'];
		$dmn['mb_id_saler'] = $ct['mb_id_saler'];
	}

    $sound_only = '<strong class="sound_only">필수</strong>';

	if(!$member['mb_manager_yn']) {
		$style_mb_id = 'background-color:#dadada;';
		$style_mb_id_saler = 'background-color:#dadada;';
		$style_mb_name = 'background-color:#dadada;';
		$style_mb_name_saler = 'background-color:#dadada;';
	}

	$dmn['dmn_status'] = 'pending';
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 만료일
$dmn['dmn_expire_date'] = ($dmn['dmn_expire_date'] == '9999-12-31' || $dmn['dmn_expire_date'] == '0000-00-00') ? '' : $dmn['dmn_expire_date'];


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_sex');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$dmn[$check_array[$i]]} = ' checked';
}

// 넘겨줄 변수가 추가로 있어서 qstr 추가 (한글이 있으면 encoding)
$qstr = $qstr."&amp;sfl_date=$sfl_date&amp;st_date=$st_date&amp;en_date=$en_date&amp;ser_trm_idxs=$ser_trm_idxs";

$g5['title'] = '도메인 정보 '.$html_title;
include_once('./_top_menu_work.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="form01" id="form01" action="./domain_form_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="dmn_idx" value="<?php echo $dmn_idx; ?>">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs ?>">

<div class="local_desc01 local_desc">
	<p>
		 현재 페이지에서는 업체 정보만 관리할 수 있습니다. 회원과 관련한 기타 다른 정보들은 [회원관리] 메뉴를 이용해 주세요.
		 <br>
		 업체명 변경은 관리자에게 문의하세요. 업체명 변경에 따른 혼란이 많으므로 관리자를 통해 업체명을 변경해 주시기 바랍니다.
	</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
		<col class="grid_4" style="width:10%;">
		<col style="width:35%;">
		<col>
	</colgroup>
	<tbody>
	<tr> 
		<th scope="row">상품정보</th>
		<td colspan="3">
			<input type="hidden" name="ct_id" value="<?=$dmn['ct_id']?>"><!-- 장바구니번호 -->
			<input type="hidden" name="com_idx" value="<?=$dmn['com_idx']?>"><!-- 업체번호 -->
			<input type="hidden" name="mb_id" value="<?=$dmn['mb_id']?>"><!--  업체아이디 -->
			<input type="hidden" name="mb_id_saler" value="<?=$dmn['mb_id_saler']?>"><!-- 영업자아이디 -->
			<?=help('상품을 선택하세요. 나의 고객 상품들 중에서만 선택할 수 있습니다.')?>
			<button type="button" class="btn_frmline" id="btn_cart">상품찾기</button>
			<div id="ct_info" style="margin-top:10px;line-height:180%;"><?=$dmn['ct_info']?></div>
		</td>
	</tr>
	<tr>
		<th scope="row">희망도메인</th>
		<td colspan="3">
			<?php echo help("한글 도메인은 DNS 연동 시 문제 발생 가능성이 존재합니다. (해외접속도 불가함) 가능하면 <span style='color:red;'>영문 도메인</span>으로 신청하세요."); ?>
			<input type="text" name="dmn_domain1" value="<?php echo $dmn['dmn_domain1'] ?>" class="frm_input">
			<input type="text" name="dmn_domain2" value="<?php echo $dmn['dmn_domain2'] ?>" class="frm_input">
		</td>
	</tr>
	<?php if($w=='u' && $member['mb_manager_yn']) { ?>
	<tr>
		<th scope="row">실도메인</th>
		<td>
			<?php echo help("실제 등록 도메인을 입력하세요."); ?>
			<input type="text" name="dmn_domain" value="<?php echo $dmn['dmn_domain'] ?>" class="frm_input">
		</td>
		<th scope="row">만료일</th>
		<td>
			<input type="text" name="dmn_expire_date" value="<?php echo $dmn['dmn_expire_date'] ?>" class="frm_input" style="width:85px;">
		</td>
	</tr>
	<?php } ?>
	<tr>
		<th scope="row">퓨니코드</th>
		<td colspan="3">
			<?php echo help("한글 도메인인 경우 퓨니코드를 입력해 주세요."); ?>
			<input type="text" name="dmn_punicode" value="<?php echo $dmn['dmn_punicode'] ?>" class="frm_input" style="width:30%;">
			<a href="http://domain.blueweb.co.kr/pop_puny.html" id="btn_puny" class="btn btn_02">퓨니코드찾기</a>
		</td>
	</tr>
	<tr>
		<th scope="row">도메인관리업체</th>
		<td colspan="3">
			<?php echo help("현재 도메인을 관리하고 있는 업체를 입력하세요. ex. 가비아, 카페24, 후이즈 등.."); ?>
			<input type="text" name="dmn_registerer" value="<?php echo $dmn['dmn_registerer'] ?>" class="frm_input" style="width:30%;">
		</td>
	</tr>
	<tr>
		<th scope="row">이전 등록업체 정보</th>
		<td colspan="3" style="line-height:250%;">
			<?php echo help("도메인 관리 이전을 위해서 관리하던 업체의 이전 정보가 필요합니다. 없는 경우는 공백으로 그냥 두세요."); ?>
			도메인등록기관: <input type="text" name="dmn_registerer_company" value="<?=$dmn['dmn_registerer_company']?>" class="frm_input">
			<br>
			등록기관 ID: <input type="text" name="dmn_registerer_id" value="<?=$dmn['dmn_registerer_id']?>" class="frm_input">
			<br>
			등록기관 PW: <input type="text" name="dmn_registerer_pw" value="<?=$dmn['dmn_registerer_pw']?>" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">이름</th>
		<td>
			<input type="text" name="dmn_name" value="<?=$dmn['dmn_name']?>" class="frm_input">
		</td>
		<th scope="row">영문이름</th>
		<td>
			<input type="text" name="dmn_name_eng" value="<?=$dmn['dmn_name_eng']?>" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">연락처</th>
		<td>
			<input type="text" name="dmn_tel" value="<?=$dmn['dmn_tel']?>" class="frm_input">
		</td>
		<th scope="row">사업자등록번호</th>
		<td>
			<input type="text" name="dmn_biz_no" value="<?=$dmn['dmn_biz_no']?>" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">신청인</th>
		<td>
			<input type="text" name="dmn_apply_name" value="<?=$dmn['dmn_apply_name']?>" class="frm_input">
		</td>
		<th scope="row">신청인연락처</th>
		<td>
			<input type="text" name="dmn_apply_tel" value="<?=$dmn['dmn_apply_tel']?>" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">주소</th>
		<td colspan="3" class="td_addr_line" style="line-height:220%;">
			<label for="dmn_zip" class="sound_only">우편번호</label>
			<input type="text" name="dmn_zip" value="<?php echo $dmn['dmn_zip1'].$dmn['dmn_zip2']; ?>" id="dmn_zip" class="frm_input readonly" maxlength="6" style="width:50px;">
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'dmn_zip', 'dmn_addr1', 'dmn_addr2', 'dmn_addr3', 'dmn_addr_jibeon');">주소 검색</button><br>
			<input type="text" name="dmn_addr1" value="<?php echo $dmn['dmn_addr1'] ?>" id="dmn_addr1" class="frm_input readonly" size="40">
			<label for="dmn_addr1">기본주소</label><br>
			<input type="text" name="dmn_addr2" value="<?php echo $dmn['dmn_addr2'] ?>" id="dmn_addr2" class="frm_input" size="40">
			<label for="dmn_addr2">상세주소</label>
			<br>
			<input type="text" name="dmn_addr3" value="<?php echo $dmn['dmn_addr3'] ?>" id="dmn_addr3" class="frm_input" size="40">
			<label for="dmn_addr3">참고항목</label>
			<input type="hidden" name="dmn_addr_jibeon" value="<?php echo $dmn['dmn_addr_jibeon']; ?>"><br>
		</td>
	</tr>
	<tr>
		<th scope="row">영문주소</th>
		<td colspan="3">
			<input type="text" name="dmn_address_eng" value="<?=$dmn['dmn_address_eng']?>" style="width:70%;" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">요청사항</th>
		<td colspan="3"><textarea name="dmn_memo" id="dmn_memo"><?php echo $dmn['dmn_memo'] ?></textarea></td>
	</tr>
	<tr style="display:none;">
		<th scope="row">반려 메모</th>
		<td colspan="3">
			<?php echo help("반려 시 전달사항을 작성하세요."); ?>
			<input type="text" name="dmn_reject_memo" value="<?=$dmn['dmn_reject_memo']?>" style="width:70%;" class="frm_input">
		</td>
	</tr>
	<tr style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
		<th scope="row">관리자 메모</th>
		<td colspan="3"><textarea name="dmn_admin_memo"><?php echo $dmn['dmn_admin_memo'] ?></textarea></td>
	</tr>
	<tr>
		<th scope="row"><label for="dmn_status">상태</label></th>
		<td colspan="3">
			<select name="dmn_status" id="dmn_status"
				<?php if (!$member['mb_manager_yn']) { ?>onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_dmn_status_options']?>
			</select>
			<script>$('select[name="dmn_status"]').val('<?=$dmn['dmn_status']?>');</script>
		</td>
	</tr>
	</tbody>
	</table>
</div>


<div class="btn_fixed_top">
    <?php
	// 운영자는 수정 가능
	if( $member['mb_manager_yn'] ) {
		echo '<input type="submit" value="확인" class="btn_submit btn" accesskey="s":>';
	}
	// 영업자는 수정 수정가능한 상태값들인 경우만 수정 가능
	else {
		if( in_array($dmn['dmn_status'],$is_modify_array) )
			echo '<input type="submit" value="확인" class="btn_submit btn" accesskey="s":>';
	}
	?>
    <a href="./domain_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
</div>
</form>

<script>
$(function() {

	// 퓨니코드 검색
	$("#btn_puny").click(function() {
		var href = $(this).attr("href");
		punywin = window.open(href, "punywin", "left=100, top=100, width=460, height=400, scrollbars=0");
		punywin.focus();
		return false;
	});
	
	// 한글 도메인 체크
	$(document).on('blur','input[name=dmn_domain1]',function(e){
		check = /[ㄱ-ㅎ|ㅏ-ㅣ|가-힣]/;
		if(check.test($("input[name=dmn_domain1]").val()))
			alert("한글 도메인은 문제가 있을 수 있습니다.\n그래도 구지 신청하시는 거 맞으시죠?");	
	});
	
    $("input[name$=_date]").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        //maxDate: "+0d"
    });
	
	// 신청상품찾기 클릭
	$(document).on('click','#btn_cart',function(e) {
		e.preventDefault();
		var url = g5_user_admin_url+"/cart_select.popup.php?frm=form01&file_name=<?php echo $g5['file_name']?>";
		win_cart_select = window.open(url, "win_cart_select", "left=500,top=50,width=520,height=600,scrollbars=1");
        win_cart_select.focus();
	});
	
});


function form01_submit(f) {

	if( $("input[name=ct_id]").val() == '' ) {
		alert('상품을 선택하세요. 상품을 선택하신 후 신청해 주세요.');
		return false;
	}
	
	if( $("input[name=dmn_domain1]").val() == '' ) {
		alert('희망 도메인을 입력하세요.');
		$("input[name=dmn_domain1]").select().focus();
		return false;
	}
	
	//  상태값이 registering, ok 인 경우는 실도메인이 있어야 한다.
	if( $("select[name=dmn_status]").val() == 'registering' || $("select[name=dmn_status]").val() == 'ok' ) {
		if( $("input[name=dmn_domain]").val() == '' ) {
			alert('실도메인을 입력하세요.');
			$("input[name=dmn_domain]").select().focus();
			return false;
		}
		// 도메인이 있으면 만료일도 넣어야 한다.
		else if( $("input[name=dmn_expire_date]").val() == '' ) {
			alert('도메인 만료일을 입력하세요.');
			$("input[name=dmn_expire_date]").select().focus();
			return false;
		}
	}

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
