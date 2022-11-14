<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
//include_once($board_skin_path.'/_common.php'); // common.php를 불러오면 wr_id 값이 초기화되어 버려서 안 되요. 


// 게시판 환경설정값 추출
if ($bo_table) {
    $board = get_board($bo_table);

    // wr_id 가 있으면 $write 배열 확장(+serialized 변수들)
    if($wr_id && is_serialized($write['wr_9'])) {
        $write = array_merge($write, get_serialized($write['wr_9']));
    }
}

$wr_3 = preg_replace("/,/g","",$wr_3);


//print_r2($_REQUEST);
// wr_8 => 검색키로 사용(:mb_name_saler=홍길동:,:mb_name_worker=작업자명:,...)
$wr_8_new = ''; // 초기화 or 기존값($write['wr_8']);
$wr_8_new = keys_update('com_name',$_REQUEST['com_name'],$wr_8_new);
$wr_8_new = keys_update('mms_name',$_REQUEST['mms_name'],$wr_8_new);

//wr_9 => more 자료들 serialized 값으로 들어감
//> 검색키들 + 추가필드들(trm_idx_department_worker=작업자조직코드,com_name=장바구니번호,com_idx=업체번호,)
$wr_9_new = ''; // 초기화 or 기존값($write['wr_9']);
$wr_9_new = serialized_update('com_name',$_REQUEST['com_name'],$wr_9_new);
$wr_9_new = serialized_update('mms_name',$_REQUEST['mms_name'],$wr_9_new);


// 상태값
$wr_10 = ($_REQUEST['wr_10']) ? $_REQUEST['wr_10'] : $board['set_default_status'];

// 영업자 조직이 없는 경우 현재 인트라에서 이름기준으로 찾아서 입력
if(!$_POST['trm_idx_department_saler']) {
    $_POST['trm_idx_department_saler'] = get_saler_idx($_REQUEST['mb_name_saler'],$_REQUEST['mb_intra_saler'],$_REQUEST['mb_intra_id']);
}


// 공통 쿼리
//  이름, 이메일, 홈페이지를 별도로 입력해야 하는 경우라면..
//$sql_common = " wr_name = '".$_POST['wr_name']."'
//				, wr_email = '".$_POST['wr_email']."'
//				, wr_homepage = '".$_POST['wr_homepage']."'
//                , wr_7 = '".$wr_7."'
//";
$sql_common = " wr_1 = '".$_POST['com_idx']."'
				, wr_2 = '".$_POST['mms_idx']."'
				, wr_3 = '".$_POST['wr_3']."'
				, wr_4 = '".$_POST['wr_4']."'
				, wr_5 = '".$_POST['wr_5']."'
				, wr_8 = '".$wr_8_new."'
				, wr_9 = '".$wr_9_new."'
				, wr_10 = '".$wr_10."'
";
if ($w == 'u') {
	$sql = "UPDATE {$write_table} SET 
                {$sql_common}
            WHERE wr_id = '{$wr['wr_id']}'
	";
    //echo $sql.'<br>';
    sql_query($sql,1);
    //exit;
}
else if ($w == '') {
    // 초기 입력값 설정
	$sql = "UPDATE {$write_table} SET 
                {$sql_common}
            WHERE wr_id = '{$wr_id}'
	";
    //echo $sql.'<br>';
	sql_query($sql,1);
    
    // 관리자가 아닌 일반인이 글을 쓰면 리스트 페이지로 이동
    //if( !$is_admin ) {
    //    alert("신청이 접수되었습니다. 감사합니다.", G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
    //}
}




//-- 필드명 추출 wr_ 와 같은 앞자리 3자 추출 --//
$r = sql_query(" desc {$write_table} ");
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
		echo $key."=".$_REQUEST[$key]."<br>";
		meta_update(array("mta_db_table"=>"board/".$bo_table,"mta_db_id"=>$wr_id,"mta_key"=>$key,"mta_value"=>$value));
	}
}


// qstr 조건을 추가해서 넘겨야 하는데 없어서 write_update.php 파일 끝 부분 가지고 와서 재설정해서 넘김
$qstr .= '&fr_date='.$fr_date.'&to_date='.$to_date.'&ser_wr_10='.$ser_wr_10;
$qstr .= '&ser_com_idx='.$ser_com_idx.'&ser_mb_name_worker='.$ser_mb_name_worker.'&ser_wr_5='.$ser_wr_5;


delete_cache_latest($bo_table);

// 무조건 리스트로 이동
goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.$qstr);
exit;

if ($file_upload_msg)
    alert($file_upload_msg, G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.$qstr);
else
    goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.$qstr);

exit;
?>
