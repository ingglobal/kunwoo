<?php
$sub_menu = "919110";
include_once("./_common.php");

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_update/","",$g5['file_name']); // _form_update를 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

// 변수 재설정
for($i=0;$i<sizeof($fields);$i++) {
    // 공백 제거
    $_POST[$fields[$i]] = trim($_POST[$fields[$i]]);
    // 천단위 제거
    if(preg_match("/_price$/",$fields[$i]))
        $_POST[$fields[$i]] = preg_replace("/,/","",$_POST[$fields[$i]]);
}
// print_r2($_POST);

// 공통쿼리
$skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt','com_idx','imp_idx'
                ,'mms_set_error','mms_data_url','mms_sort','mms_memo');
for($i=0;$i<sizeof($fields);$i++) {
    if(in_array($fields[$i],$skips)) {continue;}
    $sql_commons[] = " ".$fields[$i]." = '".$_POST[$fields[$i]]."' ";
}
$sql_common = (is_array($sql_commons)) ? implode(",",$sql_commons) : '';

// exit;
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
    // echo $sql.'<br>';
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


// 대표이미지 ----------------
$fle_type3 = "mms_img";
for($i=0;$i<count($_FILES[$fle_type3.'_file']['name']);$i++) {
	// 삭제인 경우
	if (${$fle_type3.'_del'}[$i] == 1) {
		if($mb_id) {
			delete_jt_file(array("fle_db_table"=>"mms", "fle_db_id"=>$mms_idx, "fle_type"=>$fle_type3, "fle_sort"=>$i, "fle_delete"=>1));
		}
		else {
			// fle_db_id를 던져서 바로 삭제할 수도 있고 $fle_db_table, $fle_db_id, $fle_token 를 던져서 삭제할 수도 있음
			delete_jt_file(array("fle_db_table"=>"mms"
								,"fle_db_id"=>$mms_idx
								,"fle_type"=>$fle_type3
								,"fle_sort"=>$i
								,"fle_delete"=>1
			));
		}
	}
	// 파일 등록
	if ($_FILES[$fle_type3.'_file']['name'][$i]) {
		$upfile_info = upload_jt_file(array("fle_idx"=>$fle_idx
							,"mb_id"=>$member['mb_id']
							,"fle_src_file"=>$_FILES[$fle_type3.'_file']['tmp_name'][$i]
							,"fle_orig_file"=>$_FILES[$fle_type3.'_file']['name'][$i]
							,"fle_mime_type"=>$_FILES[$fle_type3.'_file']['type'][$i]
							,"fle_content"=>$fle_content
							,"fle_path"=>'/data/'.$fle_type3		//<---- 저장 디렉토리
							,"fle_db_table"=>"mms"
							,"fle_db_id"=>$mms_idx
							,"fle_type"=>$fle_type3
							,"fle_sort"=>$i
		));
		//print_r2($upfile_info);
	}
}


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
	if(!is_array(${$key}) && !in_array($key,$fields) && substr($key,0,3)==$pre) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>$table_name,"mta_db_id"=>${$pre."_idx"},"mta_key"=>$key,"mta_value"=>$value));
	}
}

// exit;
goto_url('./'.$fname.'.php?'.$qstr.'&w=u&'.$pre.'_idx='.${$pre."_idx"}, false);
?>