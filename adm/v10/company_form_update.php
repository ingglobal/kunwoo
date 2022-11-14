<?php
$sub_menu = "990170";
include_once("./_common.php");
include_once(G5_LIB_PATH."/register.lib.php");

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');
//check_admin_token();

// com_send_type
for($i=0;$i<sizeof($com_send_type);$i++) {
	// echo $com_send_type[$i].'<br>';
	$com_send_type_arr[] = $com_send_type[$i];
}
$com_send_type = implode(",",$com_send_type_arr);
// echo $com_send_type.'<br>';
// print_r2($_REQUEST);
// exit;


// 검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;


// 업체정보 추출
if ($w!='')
	$com = sql_fetch(" SELECT * FROM {$g5['company_table']} WHERE com_idx = '$com_idx' ");


//지번 분리작업.
$com_zip1 = substr($_POST['com_zip'], 0, 3);
$com_zip2 = substr($_POST['com_zip'], 3);

// 업체명 히스토리
if($com['com_name'] != $com_name) {
	$com_names = $com['com_names'].', '.$com_name.'('.substr(G5_TIME_YMD,2).'~)';
    if($w == 'u')
        change_com_names($com_idx,$com['com_name']);
}
else {
	$com_names = $_POST['com_names'];
}


$sql_common = "	com_name = '".addslashes($_POST['com_name'])."'
                , com_names = '".addslashes($com_names)."'
                , com_homepage = '{$_POST['com_homepage']}'
                , com_type = '{$_POST['com_type']}'
                , com_tel = '{$_POST['com_tel']}'
                , com_fax = '{$_POST['com_fax']}'
                , com_email = '{$_POST['com_email']}'
                , com_president = '{$_POST['com_president']}'
                , com_biz_no = '{$_POST['com_biz_no']}'
                , com_biz_type1 = '{$_POST['com_biz_type1']}'
                , com_biz_type2 = '{$_POST['com_biz_type2']}'
                , com_zip1 = '$com_zip1'
                , com_zip2 = '$com_zip2'
                , com_addr1 = '{$_POST['com_addr1']}'
                , com_addr2 = '{$_POST['com_addr2']}'
                , com_addr3 = '{$_POST['com_addr3']}'
                , com_addr_jibeon = '{$_POST['com_addr_jibeon']}'
                , com_send_type = '".$com_send_type."'
                , com_memo = '{$_POST['com_memo']}'
				, com_kosmolog_key = '".trim($_POST['com_kosmolog_key'])."'
                , com_status = '{$_POST['com_status']}'
";
	
// 생성
if ($w == '') {
    // 업체 정보 생성
	$sql = " INSERT into {$g5['company_table']} SET
				{$sql_common} 
                , com_reg_dt = '".G5_TIME_YMDHIS."'
                , com_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	$com_idx = sql_insert_id();

}
// 수정
else if ($w == 'u') {

	if (!$com['com_idx'])
		alert('존재하지 않는 업체자료입니다.');
 
    $sql = "	UPDATE {$g5['company_table']} SET 
					{$sql_common}
					, com_update_dt = '".G5_TIME_YMDHIS."'
				WHERE com_idx = '{$com_idx}' 
	";
    sql_query($sql,1);
    //echo $sql.'<br>';
}
else if ($w=="d") {

	if (!$com['com_idx']) {
		alert('존재하지 않는 업체자료입니다.');
	} else {
		// 자료 삭제
		$sql = " UPDATE {$g5['company_table']} SET com_status = 'trash' WHERE com_idx = $com_idx ";
		sql_query($sql,1);
	}
	
	goto_url('./company_list.php?'.$qstr, false);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

// 영업자 연결 & 상태변경
for($i=0;$i<count($mb_id_saler);$i++) {
	//echo $mb_id_saler[$i].'<br>';
	//-- 기존 디비 삭제를 위해 배열 미리 생성 --//
	$mb_id_saler_array[] = $mb_id_saler[$i];
	
	// 업체-영업자 연결 함수(extend/user.06.intra.function.php), 있으면 UPDATE, 없으면 입력
	company_saler_update(array(
		"mb_id_saler"=>$mb_id_saler[$i]
		, "com_idx"=>$com_idx
		, "cms_status"=>$cms_status[$i]
	));
}
//-- 영업자 디비 삭제, 테이블에 정보가 없을 때는 관련 디비 전부 삭제해야 함 --//
$del_where = (sizeof($mb_id_saler_array))? " AND mb_id_saler NOT IN ('".implode("','",$mb_id_saler_array)."') ":"";
$sql = " DELETE FROM {$g5['company_saler_table']} WHERE com_idx='{$com['com_idx']}' $del_where ";
sql_query($sql,1);


// 파일 처리2 (파일 타입이 여러개면 일련번호 붙여서 확장해 주세요.) ----------------
$fle_type2 = "company_data";
for($i=0;$i<count($_FILES[$fle_type2.'_file']['name']);$i++) {
	// 삭제인 경우
	if (${$fle_type2.'_del'}[$i] == 1) {
		if($mb_id) {
			delete_jt_file(array("fle_db_table"=>"company", "fle_db_id"=>$com_idx, "fle_type"=>$fle_type2, "fle_sort"=>$i, "fle_delete"=>1));
		}
		else {
			// fle_db_id를 던져서 바로 삭제할 수도 있고 $fle_db_table, $fle_db_id, $fle_token 를 던져서 삭제할 수도 있음
			delete_jt_file(array("fle_db_table"=>"company"
								,"fle_db_id"=>$com_idx
								,"fle_type"=>$fle_type2
								,"fle_sort"=>$i
								,"fle_delete"=>1
			));
		}
	}
	// 파일 등록
	if ($_FILES[$fle_type2.'_file']['name'][$i]) {
		$upfile_info = upload_jt_file(array("fle_idx"=>$fle_idx
							,"mb_id"=>$member['mb_id']
							,"fle_src_file"=>$_FILES[$fle_type2.'_file']['tmp_name'][$i]
							,"fle_orig_file"=>$_FILES[$fle_type2.'_file']['name'][$i]
							,"fle_mime_type"=>$_FILES[$fle_type2.'_file']['type'][$i]
							,"fle_content"=>$fle_content
							,"fle_path"=>'/data/'.$fle_type2		//<---- 저장 디렉토리
							,"fle_db_table"=>"company"
							,"fle_db_id"=>$com_idx
							,"fle_type"=>$fle_type2
							,"fle_sort"=>$i
		));
		//print_r2($upfile_info);
	}
}


// 파일 처리3 (사업자 등록증) ----------------
$fle_type3 = "license_img";
for($i=0;$i<count($_FILES[$fle_type3.'_file']['name']);$i++) {
	// 삭제인 경우
	if (${$fle_type3.'_del'}[$i] == 1) {
		if($mb_id) {
			delete_jt_file(array("fle_db_table"=>"company", "fle_db_id"=>$com_idx, "fle_type"=>$fle_type3, "fle_sort"=>$i, "fle_delete"=>1));
		}
		else {
			// fle_db_id를 던져서 바로 삭제할 수도 있고 $fle_db_table, $fle_db_id, $fle_token 를 던져서 삭제할 수도 있음
			delete_jt_file(array("fle_db_table"=>"company"
								,"fle_db_id"=>$com_idx
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
							,"fle_db_table"=>"company"
							,"fle_db_id"=>$com_idx
							,"fle_type"=>$fle_type3
							,"fle_sort"=>$i
		));
		//print_r2($upfile_info);
	}
}


//-- 필드명 추출 mb_ 와 같은 앞자리 3자 추출 --//
$r = sql_query(" desc {$g5['company_table']} ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,3);

//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array();
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}

//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$db_fields[] = "mb_zip";	// 건너뛸 변수명은 배열로 추가해 준다.
$db_fields[] = "mb_sido_cd";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>"company","mta_db_id"=>$com_idx,"mta_key"=>$key,"mta_value"=>$value));
	}
}


//exit;
if($w == 'u') {
	alert('업체 정보를 수정하였습니다.','./company_form.php?'.$qstr.'&amp;w=u&amp;com_idx='.$com_idx, false);
}
else {
	alert('업체 정보를 등록하였습니다.','./company_list.php', false);
}
?>