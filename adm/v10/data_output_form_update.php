<?php
$sub_menu = "960100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$g5_table_name = 'g5_1_data_output_'.$ser_mms_idx;
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form_update/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= "&st_date=$st_date&st_time=$st_time&en_date=$en_date&en_time=$en_time&ser_mms_idx=$ser_mms_idx";
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_POST[$fields[$i]] = trim($_POST[$fields[$i]]);
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]))
        $_POST[$fields[$i]] = preg_replace("/,/","",$_POST[$fields[$i]]);
}

// date to timestamp
$_POST['dta_dt'] = strtotime($_POST['dta_dt']);


// 공통쿼리
$skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}
$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';


if ($w == '') {
    
    $sql = " INSERT into {$g5_table_name} SET 
                {$sql_common}
                , ".$pre."_reg_dt = '".G5_TIME_YMDHIS."'
                , ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	${$pre."_idx"} = sql_insert_id();
    
}
else if ($w == 'u') {

	$sql = "SELECT * FROM {$g5_table_name} WHERE dta_idx = '".${$pre."_idx"}."' ";
    ${$pre} = sql_fetch($sql,1);
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
 
    $sql = "	UPDATE {$g5_table_name} SET 
					{$sql_common}
					, ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
				WHERE ".$pre."_idx = '".${$pre."_idx"}."' 
	";
    //echo $sql.'<br>';
    sql_query($sql,1);
        
}
else if ($w == 'd') {

    $sql = "DELETE FROM {$g5_table_name}
            WHERE ".$pre."_idx = '".${$pre."_idx"}."'
    ";
    sql_query($sql,1);
    goto_url('./'.$fname.'_list.php?'.$qstr, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 합계 정보 입력
// mms_idx, shift_no, item_no, dta_group, dta_defect, dta_defect_type, stat_date
$ar['mms_idx'] = $ser_mms_idx;
$ar['shift_no'] = $_POST['dta_shf_no'];
$ar['item_no'] = $_POST['dta_mmi_no'];
$ar['dta_group'] = $_POST['dta_group'];
$ar['dta_defect'] = $_POST['dta_defect'];
$ar['dta_defect_type'] = $_POST['dta_defect_type'];
$ar['stat_date'] = $_POST['dta_date'];
update_output_sum($ar);
unset($ar);


// exit;
goto_url('./'.$fname.'_list.php?'.$qstr, false);
// goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
?>