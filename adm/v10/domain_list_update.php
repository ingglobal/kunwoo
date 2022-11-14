<?php
$sub_menu = '950600';
include_once('./_common.php');

check_demo();

// 넘겨줄 변수가 추가로 있어서 qstr 추가 (한글이 있으면 encoding)
$qstr = $qstr."&amp;sfl_date=$sfl_date&amp;st_date=$st_date&amp;en_date=$en_date&amp;ser_trm_idxs=$ser_trm_idxs";

$count = count($_POST['chk']);
if (!$count)
	alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");


// 수정
if($w == 'u') {
	for ($i=0; $i<$count; $i++) {
		// 실제 번호를 넘김
		$k = $chk[$i];
		$sls = sql_fetch(" SELECT * FROM {$g5['domain_table']} WHERE dmn_idx = '".$_POST['dmn_idx'][$k]."' ");
		
		$sql = "	UPDATE {$g5['domain_table']} SET
						dmn_share = '".$_POST['dmn_share'][$k]."'
						,dmn_price_cost = '".$_POST['dmn_price_cost'][$k]."'
					WHERE dmn_idx = '".$_POST['dmn_idx'][$k]."'
		";
		sql_query($sql,1);
		//echo $sql.'<br>';
		
	}
}
// 삭제
else if($w == 'd') {
	for ($i=0; $i<$count; $i++) {
		// 실제 번호를 넘김
		$k = $chk[$i];
		$sql = "UPDATE {$g5['domain_table']} SET
					dmn_status = 'trash' 
					, dmn_admin_memo = CONCAT(dmn_admin_memo,'\n\n삭제 ".G5_TIME_YMDHIS.' by '.$member['mb_name']."')
--					, dmn_update_dt = '".G5_TIME_YMDHIS."'
				WHERE dmn_idx = '{$_POST['dmn_idx'][$k]}' ";
		sql_query($sql,1);
		echo $sql.'<br>';
	}
}

//exit;
goto_url('./domain_list.php?'.$qstr, false);
?>
