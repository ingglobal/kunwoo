<?php
$sub_menu = "917180";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

// 천단위 제거
$imp_price = preg_replace("/,/","",$_POST['imp_price']);


$sql_common = "  com_idx = '{$_POST['com_idx']}',
                 imp_idx2 = '{$_POST['imp_idx2']}',
                 imp_name = '{$_POST['imp_name']}',
                 imp_location = '{$_POST['imp_location']}',
                 imp_install_date = '{$_POST['imp_install_date']}',
                 imp_memo = '{$_POST['imp_memo']}',
                 imp_status = '{$_POST['imp_status']}'
";

if ($w == '') {

    $sql = " INSERT into {$g5['imp_table']} SET 
                {$sql_common} 
                , imp_reg_dt = '".G5_TIME_YMDHIS."'
                , imp_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	$imp_idx = sql_insert_id();
    
}
else if ($w == 'u') {

	$imp = get_table_meta('imp','imp_idx',$imp_idx);
    if (!$imp['imp_idx'])
		alert('존재하지 않는 자료입니다.');
 
    $sql = "	UPDATE {$g5['imp_table']} SET 
					{$sql_common}
					, imp_update_dt = '".G5_TIME_YMDHIS."'
				WHERE imp_idx = '".$imp_idx."' 
	";
    //echo $sql.'<br>';
    sql_query($sql,1);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');



//-- 필드명 추출 imp_ 와 같은 앞자리 3자 추출 --//
$r = sql_query(" desc {$g5['imp_table']} ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,3);

//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array();
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}

//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$db_fields[] = "imp_zip";	// 건너뛸 변수명은 배열로 추가해 준다.
$db_fields[] = "imp_sido_cd";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>"imp","mta_db_id"=>$imp_idx,"mta_key"=>$key,"mta_value"=>$value));
	}
}



// alert('등록되었습니다.','./imp_form.php?'.$qstr.'&amp;w=u&amp;imp_idx='.$imp_idx, false);
alert('등록되었습니다.','./imp_list.php?'.$qstr, false);
?>