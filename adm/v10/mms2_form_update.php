<?php
$sub_menu = "955600";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

check_admin_token();

// 천단위 제거
$mms_price = preg_replace("/,/","",$_POST['mms_price']);


$sql_common = "  com_idx = '{$_POST['com_idx']}'
                , mms_idx2 = '{$_POST['mms_idx2']}'
                , imp_idx = '{$_POST['imp_idx']}'
                , mmg_idx = '{$_POST['mmg_idx']}'
                , trm_idx_category = '{$_POST['trm_idx_category']}'
                , mms_name = '{$_POST['mms_name']}'
                , mms_model = '{$_POST['mms_model']}'
                , mms_price = '{$mms_price}'
                , mms_install_date = '{$_POST['mms_install_date']}'
                , mms_set_output = '{$_POST['mms_set_output']}'
                , mms_set_error = '{$_POST['mms_set_error']}'
                , mms_data_url = '{$_POST['mms_data_url']}'
                , mms_linecode = '{$_POST['mms_linecode']}'
                , mms_sort = '{$_POST['mms_sort']}'
                , mms_memo = '{$_POST['mms_memo']}'
                , mms_status = '{$_POST['mms_status']}'
";

if ($w == '') {

    $sql = " INSERT into {$g5['mms_table']} SET 
                {$sql_common} 
                , mms_reg_dt = '".G5_TIME_YMDHIS."'
                , mms_update_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	$mms_idx = sql_insert_id();
    
}
else if ($w == 'u') {

	$mms = get_table_meta('mms','mms_idx',$mms_idx);
    if (!$mms['mms_idx'])
		alert('존재하지 않는 자료입니다.');
 
    $sql = "	UPDATE {$g5['mms_table']} SET 
					{$sql_common}
					, mms_update_dt = '".G5_TIME_YMDHIS."'
				WHERE mms_idx = '".$mms_idx."' 
	";
    // echo $sql.'<br>';
    sql_query($sql,1);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


// 파일 처리2 (파일 타입이 여러개면 일련번호 붙여서 확장해 주세요.) ----------------
$fle_type2 = "mms_data";
for($i=0;$i<count($_FILES[$fle_type2.'_file']['name']);$i++) {
	// 삭제인 경우
	if (${$fle_type2.'_del'}[$i] == 1) {
		if($mb_id) {
			delete_jt_file(array("fle_db_table"=>"mms", "fle_db_id"=>$mms_idx, "fle_type"=>$fle_type2, "fle_sort"=>$i, "fle_delete"=>1));
		}
		else {
			// fle_db_id를 던져서 바로 삭제할 수도 있고 $fle_db_table, $fle_db_id, $fle_token 를 던져서 삭제할 수도 있음
			delete_jt_file(array("fle_db_table"=>"mms"
								,"fle_db_id"=>$mms_idx
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
							,"fle_db_table"=>"mms"
							,"fle_db_id"=>$mms_idx
							,"fle_type"=>$fle_type2
							,"fle_sort"=>$i
		));
		//print_r2($upfile_info);
	}
}


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



//-- 필드명 추출 mms_ 와 같은 앞자리 3자 추출 --//
$r = sql_query(" desc {$g5['mms_table']} ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,3);

//-- 체크박스 값이 안 넘어오는 현상 때문에 추가, 폼의 체크박스는 모두 배열로 선언해 주세요.
$checkbox_array=array();
for ($i=0;$i<sizeof($checkbox_array);$i++) {
	if(!$_REQUEST[$checkbox_array[$i]])
		$_REQUEST[$checkbox_array[$i]] = 0;
}

//-- 메타 입력 (디비에 있는 설정된 값은 입력하지 않는다.) --//
$db_fields[] = "mms_zip";	// 건너뛸 변수명은 배열로 추가해 준다.
$db_fields[] = "mms_sido_cd";	// 건너뛸 변수명은 배열로 추가해 준다.
foreach($_REQUEST as $key => $value ) {
	//-- 해당 테이블에 있는 필드 제외하고 테이블 prefix 로 시작하는 변수들만 업데이트 --//
	if(!in_array($key,$db_fields) && substr($key,0,3)==$db_prefix) {
		//echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>"mms","mta_db_id"=>$mms_idx,"mta_key"=>$key,"mta_value"=>$value));
	}
}



// 캐시 업데이트
$cache_file = G5_DATA_PATH.'/cache/mms-setting.php';
@unlink($cache_file);
    
$list = array();
$sql = "SELECT * FROM {$g5['mms_table']} ORDER BY mms_idx";
$result = sql_query($sql,1);
//echo $sql;
for($i=0; $row=sql_fetch_array($result); $i++) {
    $list[$row['mms_idx']]['mmg_idx'] = $row['mmg_idx'];
    $list[$row['mms_idx']]['mms_name'] = $row['mms_name'];
    $list[$row['mms_idx']]['output'] = $row['mms_set_output'];
    $list[$row['mms_idx']]['imp_idx'] = $row['imp_idx'];
    $list[$row['mms_idx']]['trm_idx_category'] = $row['trm_idx_category'];

    // 교대시간
    $sql = "SELECT *
            FROM {$g5['shift_table']}
            WHERE shf_status = 'ok'
                AND mms_idx = '".$row['mms_idx']."'
            ORDER BY shf_start_dt
    ";
    $rs = sql_query($sql,1);
    //echo $sql.'<br>';
    $list1 = array();
    for($j=0;$row1=sql_fetch_array($rs);$j++) {
        //print_r2($row);
        $row2['shf_start_dt'] = $row1['shf_start_dt'];
        $row2['shf_end_dt'] = $row1['shf_end_dt'];
        $row2['shf_range_1'] = $row1['shf_range_1'];
        $row2['shf_range_2'] = $row1['shf_range_2'];
        $row2['shf_range_3'] = $row1['shf_range_3'];
        $row2['shf_target_1'] = $row1['shf_target_1'];
        $row2['shf_target_2'] = $row1['shf_target_2'];
        $row2['shf_target_3'] = $row1['shf_target_3'];
        $list1[] = $row2;
    }
    $list[$row['mms_idx']]['shift'] = $list1;
    // print_r2($list[$row['mms_idx']]['shift']);

}
// print_r2($list);

// 캐시파일 생성
$handle = fopen($cache_file, 'w');
$cache_content = "<?php\n";
$cache_content .= "if (!defined('_GNUBOARD_')) exit;\n";
$cache_content .= "\$g5['mms']=".var_export($list, true).";\n";
$cache_content .= "?>";
fwrite($handle, $cache_content);
fclose($handle);




// exit;
// goto_url('./mms_form.php?'.$qstr.'&amp;w=u&amp;mms_idx='.$mms_idx, false);
// alert('등록되었습니다.','./mms_form.php?'.$qstr.'&amp;w=u&amp;mms_idx='.$mms_idx, false);
alert('등록되었습니다.','./mms2_list.php?'.$qstr, false);
?>