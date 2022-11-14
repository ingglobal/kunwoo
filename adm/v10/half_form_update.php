<?php
$sub_menu = "945113";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'material';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form_update/","",$g5['file_name']); // _form_update를 제외한 파일명
$qstr .= '&sca='.$sca.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_POST[$fields[$i]] = trim($_POST[$fields[$i]]);
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]) || $fields[$i]=='mtr_moq' || $fields[$i]=='mtr_lead_time')
        $_POST[$fields[$i]] = preg_replace("/,/","",$_POST[$fields[$i]]);
}

// prior post value setting
$_POST['com_idx'] = $_SESSION['ss_com_idx'];


// 공통쿼리
//$skips = array($pre.'_idx','com_idx','bom_idx',$pre.'_history',$pre.'_reg_dt',$pre.'_update_dt');
$skips = array($pre.'_idx','com_idx','bom_idx',$pre.'_history',$pre.'_reg_dt',$pre.'_update_dt',$pre.'_shift',$pre.'_input_date',$pre.'_defect',$pre.'_defect_type','oop_idx','itm_lot','trm_idx_location','imp_idx','mms_idx');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}
// print_r2($sql_commons);
// exit;
// after sql_common value setting
// $sql_commons[] = " com_idx = '".$_SESSION['ss_com_idx']."' ";

// 공통쿼리 생성
$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';


if ($w == '') {
    
    $sql = "INSERT INTO {$g5_table_name} SET 
               {$sql_common} 
                , ".$pre."_reg_dt = '".G5_TIME_YMDHIS."'
                , ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	${$pre."_idx"} = sql_insert_id();
    
}
else if ($w == 'u') {

	${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
    if (!${$pre}[$pre.'_idx'])
		alert('존재하지 않는 자료입니다.');
    

    $error_search = (preg_match('/^error_/', ${$pre."_status"})) ? ", mtr_defect = '1', mtr_defect_type = '".$g5['set_half_status_ng2_reverse'][${$pre."_status"}]."' " : ", mtr_defect = '0', mtr_defect_type = '0' ";
		
    $sql = "UPDATE {$g5_table_name} SET 
                {$sql_common}
                {$error_search}
                , ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
            WHERE ".$pre."_idx = '".${$pre."_idx"}."'
	";
    // echo $sql.'<br>';
    // exit;
    sql_query($sql,1);
        
}
else if ($w == 'd') {

    $sql = "UPDATE {$g5_table_name} SET
                ".$pre."_status = 'trash'
            WHERE ".$pre."_idx = '".${$pre."_idx"}."'
    ";
    sql_query($sql,1);
    goto_url('./'.$fname.'_list.php?'.$qstr, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


update_item_sum2(); //item 변경사항을 반영하기 위해 item_sum테이블 업데이트함

//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array();
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}

//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$fields[] = "mtr_start_date";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
	if(!in_array($key,$fields) && substr($key,0,3)==$pre) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>$table_name,"mta_db_id"=>${$pre."_idx"},"mta_key"=>$key,"mta_value"=>$value));
	}
}

// exit;
goto_url('./half_row_list.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
// goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
?>