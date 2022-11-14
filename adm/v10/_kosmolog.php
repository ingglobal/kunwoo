<?php
// if(!preg_match('/^iframe/',$g5['file_name']) && !preg_match('/^order_item_list/',$g5['file_name']) && preg_match('/list$/i',$g5['file_name']) || preg_match('/form$/i',$g5['file_name']) || preg_match('/board$/i',$g5['file_name']) || preg_match('/write$/i',$g5['file_name'])){
if(preg_match('/list$/i',$g5['file_name']) || preg_match('/form$/i',$g5['file_name']) || preg_match('/board$/i',$g5['file_name']) || preg_match('/write$/i',$g5['file_name'])){
	if($board['bo_1']){
		$access_menu_cd = $board['bo_1'];
	} else if($sub_menu){
		$access_menu_cd = $sub_menu;
	} else {
		$access_menu_cd = '960100';
	}
	// print_r2($access_menu_cd);
	if(preg_match('/list$/',$g5['file_name']) || preg_match('/board$/',$g5['file_name'])){
		if($stx || $sfl || count($_GET)) $user_status = '검색';
		else $user_status = '검색';
		// print_r2($user_status."1");
	}
	else if(preg_match('/form$/i',$g5['file_name']) || preg_match('/write$/i',$g5['file_name'])){
		if(!$w || $w == 'c') $user_status = '등록';
		else if($w == 'u') $user_status = '수정';
		else $user_status = '수정';
		// print_r2($user_status."2");
	}

	// print_r2($user_status);
	$darr = array(
		'crtfcKey' => $g5['setting']['set_userlog_crtfckey'],
		'logDt' => G5_TIME_YMDHIS.'.000',
		'useSe' => $user_status,
		'sysUser' => $member['mb_id'],
		'conectIp' => $member['mb_login_ip'],
		'dataUsgqty' => ''
	);
	// print_r2($darr);

	$sql = " INSERT INTO {$g5['user_log_table']} SET
			mb_id = '{$member['mb_id']}',
			usl_menu_cd = '{$access_menu_cd}',
			usl_type = '{$user_status}',
			usl_reg_dt = '".G5_TIME_YMDHIS."'
	";
	// print_r2($sql);exit;
	sql_query($sql);
	if($g5['setting']['set_userlog_crtfckey']){
	?>
	<script>
	var lnk = 'https://log.smart-factory.kr/apisvc/sendLogData.json';
	var crtfcKey = '<?=$g5['setting']['set_userlog_crtfckey']?>';
	var logDt = '<?=G5_TIME_YMDHIS?>.000';
	var useSe = '<?=$user_status?>';
	var sysUser = '<?=$member['mb_id']?>';
	var conectIp = '<?=$member['mb_login_ip']?>';
	var dataUsgqty = '0';

	var param = {
		'crtfcKey' : crtfcKey,
		'logDt' : logDt,
		'useSe' : useSe,
		'sysUser' : sysUser,
		'conectIp' : conectIp,
		'dataUsgqty' : dataUsgqty
	}
	// console.log(param);
	$.ajax({
		type : "POST",
		url : lnk,
		cache : false,
		timeout : 360000,
		data : param,
		dataType : "json",
		contentType : "application/x-www-form-urlencoded; charset=utf-8",
		success : function(data, textStatus, jqXHR){
			var result = data.result;
			// console.log(result);
		},
		error : function(jqXHR, textStatus, errorThrown){

		}
	});
	</script>
<?php
	}
}
?>
