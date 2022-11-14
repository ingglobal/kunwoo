<?php
$sub_menu = "917230";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// cod_send_type
for($i=0;$i<sizeof($cod_send_type);$i++) {
	// echo $cod_send_type[$i].'<br>';
	$cod_send_type_arr[] = $cod_send_type[$i];
}
$_REQUEST['cod_send_type'] = implode(",",$cod_send_type_arr);
// echo $cod_send_type.'<br>';

//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array('cod_offline_yn','cod_quality_yn');
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}


// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'code';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_form_update/","",$g5['file_name']); // _form_update를 제외한 파일명
$qstr .= '&ser_cod_group='.$ser_cod_group.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_REQUEST[$fields[$i]] = trim($_REQUEST[$fields[$i]]);
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]))
        $_REQUEST[$fields[$i]] = preg_replace("/,/","",$_REQUEST[$fields[$i]]);
}

// in case of PLC perdict
if($_REQUEST['cod_type']=='p2') {
    $_REQUEST['cod_interval'] = 1;
    $_REQUEST['cod_count'] = 1;
}


// 공통쿼리
$skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt','cod_reports');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_REQUEST[$fields[$i]]."' ";
}

// report people array to json
$reports['r_name'] = $_REQUEST['r_name'];
$reports['r_role'] = $_REQUEST['r_role'];
$reports['r_hp'] = $_REQUEST['r_hp'];
$reports['r_email'] = $_REQUEST['r_email'];
$cod_reports = json_encode( $reports, JSON_UNESCAPED_UNICODE );
$sql_commons[] = " cod_reports = '".$cod_reports."' ";

// 공통쿼리 생성
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





// 캐시 업데이트
$cache_file = G5_DATA_PATH.'/cache/mms-code.php';
@unlink($cache_file);
    
$list = array();
$sql = "SELECT * FROM {$g5['code_table']} ORDER BY cod_idx";
$result = sql_query($sql,1);
//echo $sql;
for($i=0; $row=sql_fetch_array($result); $i++) {
    $list[$row['mms_idx']][$row['cod_code']]['trm_idx_category'] = $row['trm_idx_category'];
    $list[$row['mms_idx']][$row['cod_code']]['cod_group'] = $row['cod_group'];
    $list[$row['mms_idx']][$row['cod_code']]['cod_type'] = $row['cod_type'];
    $list[$row['mms_idx']][$row['cod_code']]['cod_interval'] = $row['cod_interval'];
    $list[$row['mms_idx']][$row['cod_code']]['cod_count'] = $row['cod_count'];
    $list[$row['mms_idx']][$row['cod_code']]['cod_count_limit'] = $row['cod_count_limit'];
}

// 캐시파일 생성
$handle = fopen($cache_file, 'w');
$cache_content = "<?php\n";
$cache_content .= "if (!defined('_GNUBOARD_')) exit;\n";
$cache_content .= "\$g5['code']=".var_export($list, true).";\n";
$cache_content .= "?>";
fwrite($handle, $cache_content);
fclose($handle);








//exit;
// goto_url('./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
goto_url('./'.$fname.'_list.php?'.$qstr, false);
?>