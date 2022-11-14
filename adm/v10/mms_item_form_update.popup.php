<?php
$sub_menu = "925220";
include_once("./_common.php");

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms_item';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form_update/","",$g5['file_name']); // _form_update를 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_POST[$fields[$i]] = trim($_POST[$fields[$i]]);
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]))
        $_POST[$fields[$i]] = preg_replace("/,/","",$_POST[$fields[$i]]);
}

// 공통쿼리
$skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}
$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';


if ($w == '') {
    
    // 중복체크
    $sql = "SELECT mmi_idx FROM {$g5_table_name}
            WHERE mms_idx = '".$_REQUEST['mms_idx']."'
                AND mmi_no = '".$_REQUEST['mmi_no']."'
                AND mmi_status NOT IN ('trash','delete')
    ";
    $mmi = sql_fetch($sql,1);
    if($mmi['mmi_idx'])
        alert('동일한 기종 설정값이 존재합니다. 기존 정보를 변경하세요.');


    $sql = " INSERT INTO {$g5_table_name} SET 
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
 
    $sql = "	UPDATE {$g5_table_name} SET 
					{$sql_common}
					, ".$pre."_update_dt = '".G5_TIME_YMDHIS."'
				WHERE ".$pre."_idx = '".${$pre."_idx"}."' 
	";
    //echo $sql.'<br>';
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

// Update price table info. Update for same price and date, Insert for not existing.
$sql = "SELECT * FROM {$g5['mms_item_price_table']}
        WHERE mmi_idx = '".$_POST['mmi_idx']."'
            AND mip_price = '".$_POST['mmi_price']."'
            AND mip_start_date = '".$_POST['mmi_start_date']."'
";
$mip = sql_fetch($sql,1);
if($mip['mip_idx']) {
    $sql = "UPDATE {$g5['mms_item_price_table']} SET
                mip_price = '".$_POST['mmi_price']."',
                mip_start_date = '".$_POST['mmi_start_date']."',
                mip_update_dt = '".G5_TIME_YMDHIS."'
            WHERE mip_idx = '".$mip['mip_idx']."'
    ";
    sql_query($sql,1);
}
else {
    $sql = " INSERT INTO {$g5['mms_item_price_table']} SET 
                mmi_idx = '".${$pre."_idx"}."',
                mip_price = '".$_POST['mmi_price']."',
                mip_start_date = '".$_POST['mmi_start_date']."',
                mip_reg_dt = '".G5_TIME_YMDHIS."',
                mip_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
}

// update the latest price info of mms_item table
update_mmi_price($_POST['mmi_idx']);

//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array();
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}

//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$fields[] = "mms_zip";	// 건너뛸 변수명은 배열로 추가해 준다.
$fields[] = "mms_sido_cd";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
	if(!in_array($key,$fields) && substr($key,0,3)==$pre) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>$table_name,"mta_db_id"=>${$pre."_idx"},"mta_key"=>$key,"mta_value"=>$value));
	}
}

//exit;
goto_url('./mms_item_list.popup.php?'.$qstr, false);
// goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
?>