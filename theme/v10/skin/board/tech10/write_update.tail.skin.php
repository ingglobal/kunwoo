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


if($ser_com_idx)
    $com = get_table_meta('company','com_idx',$ser_com_idx);


// 공통 쿼리
//  이름, 이메일, 홈페이지를 별도로 입력해야 하는 경우라면..
//$sql_common = " wr_name = '".$_POST['wr_name']."'
//				, wr_email = '".$_POST['wr_email']."'
//				, wr_homepage = '".$_POST['wr_homepage']."'
//                , wr_7 = '".$wr_7."'
//";
$sql_common = " wr_1 = '".$com['com_name']."'
				, wr_2 = '".$com['com_idx']."'
";
if ($w == 'u') {
	$sql = "UPDATE {$write_table} SET 
                {$sql_common}
            WHERE wr_id = '{$wr['wr_id']}'
	";
    //echo $sql.'<br>';
    sql_query($sql,1);
}
else if ($w == '') {
    // 초기 입력값 설정
	$sql = "UPDATE {$write_table} SET 
                {$sql_common}
                , wr_dept_writer = '".$member['mb_2']."'
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
$qstr .= '&fr_date='.$fr_date.'&to_date='.$to_date.'&sch_wr_10='.$sch_wr_10;
$qstr .= '&pl_date='.$pl_date.'&sch_mb_name_worker='.$sch_mb_name_worker.'&sch_wr_5='.$sch_wr_5.'&ser_com_idx='.$ser_com_idx;


delete_cache_latest($bo_table);

if ($file_upload_msg)
    alert($file_upload_msg, G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.$qstr);
else
    goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.$qstr);

exit;
?>
