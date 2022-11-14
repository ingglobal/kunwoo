<?php
//$g5['write_default_fields']
//$g5['write_default_columns']

if(change_fields_admboard_skin_flag()){
	include_once($board_skin_path.'/_set/change_fields_update.skin.php');
	$tbl_fields = sql_field_names2($write_table);
	$field_prop = <<<_HTML_
	<div id="field_prop_box">
	<div id="field_prop_box_bg"></div>
	<div id="field_prop_box_in">
	<button type="button" id="field_prop_close"><i class="fa fa-times" aria-hidden="true"></i></button>
	<div id="field_wrap">
	<table id="field_prop">
	<thead>
	<tr>
	<th>번호</th>
	<th>필드명</th>
	<th>유형</th>
	<th>코멘트</th>
	</tr>
	</thead>
	<tbody>
	_HTML_;
	$fnum = 0;
	foreach($tbl_fields as $fk => $fv){
		$fvarr = explode('_',$fv);
		$ft = $fvarr[0];
		$fc = $fvarr[1];
		$fnum++;
		$ftr = ($fnum % 2 == 1) ? 'odd':'';
		$field_prop .= <<<_HTML_
		<tr class="$ftr">
		<td class="f_num">$fnum</td>
		<td class="f_name">$fk</td>
		<td class="f_type">$ft</td>
		<td class="f_coment">$fc</td>
		</tr>
		_HTML_;
	}
	$field_prop .= <<<_HTML_
	</tbody>
	</table>
	</div>
	</div>
	</div>
	<script>
	$('#field_prop_box').prependTo('body');
	$('#field_prop_box_bg,#field_prop_close').on('click',function(){ $('#field_prop_box').removeClass('focus'); });
	if(g5_is_admin){ $('<button type="button" id="field_prop_open">FieldProp</button>').prependTo('body'); }
	if($('#field_prop_open').length){ $('#field_prop_open').on('click',function(){ $('#field_prop_box').addClass('focus'); }); }
	</script>
	_HTML_;
	// = nl2br($field_prop);
	if($g5['file_name'] == 'bbs_board'){
		include_once(G5_USER_ADMIN_PATH.'/bbs_board2.php');
		return;
	}
	else if($g5['file_name'] == 'bbs_write'){
		include_once(G5_USER_ADMIN_PATH.'/bbs_write2.php');
		return;
	}
}
else{
	//print_r3(array('no'));
	$write_fields = sql_field_names($write_table);
	$default_fields_flag = write_default_columns_flag($write_fields);
	$tbl_fields = sql_field_names2($write_table);
	$field_prop = <<<_HTML_
	<div id="field_prop_box">
	<div id="field_prop_box_bg"></div>
	<div id="field_prop_box_in">
	<button type="button" id="field_prop_close"><i class="fa fa-times" aria-hidden="true"></i></button>
	<div id="field_wrap">
	<table id="field_prop">
	<thead>
	<tr>
	<th>번호</th>
	<th>필드명</th>
	<th>유형</th>
	<th>코멘트</th>
	</tr>
	</thead>
	<tbody>
	_HTML_;
	$fnum = 0;
	foreach($tbl_fields as $fk => $fv){
		$fvarr = explode('_',$fv);
		$ft = $fvarr[0];
		$fc = $fvarr[1];
		$fnum++;
		$ftr = ($fnum % 2 == 1) ? 'odd':'';
		$field_prop .= <<<_HTML_
		<tr class="$ftr">
		<td class="f_num">$fnum</td>
		<td class="f_name">$fk</td>
		<td class="f_type">$ft</td>
		<td class="f_coment">$fc</td>
		</tr>
		_HTML_;
	}
	$field_prop .= <<<_HTML_
	</tbody>
	</table>
	</div>
	</div>
	</div>
	<script>
	$('#field_prop_box').prependTo('body');
	$('#field_prop_box_bg,#field_prop_close').on('click',function(){ $('#field_prop_box').removeClass('focus'); });
	if(g5_is_admin){ $('<button type="button" id="field_prop_open">FieldProp</button>').prependTo('body'); }
	if($('#field_prop_open').length){ $('#field_prop_open').on('click',function(){ $('#field_prop_box').addClass('focus'); }); }
	</script>
	_HTML_;
	// = nl2br($field_prop);
	if(!$default_fields_flag){
		alert('변경된 필드 구조에 맞지 않은 스킨입니다.');
	}
}
