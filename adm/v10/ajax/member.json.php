<?php
header("Content-Type: text/plain; charset=utf-8");
include_once('./_common.php');
if(isset($_SERVER['HTTP_ORIGIN'])){
 header("Access-Control-Allow-Origin:{$_SERVER['HTTP_ORIGIN']}");
 header("Access-Control-Allow-Credentials:true");
 header("Access-Control-Max-Age:86400"); //cache for 1 day
}

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
 if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
  header("Access-Control-Allow-Methods:GET,POST,OPTIONS");
 if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
  header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
 exit(0);
}

//-- 디폴트 상태 (실패) --//
$response = new stdClass();
$response->result=false;

// 회원 1명 정보 추출
if ($aj == "get") {
	// 요청 필드가 없으면 전체
	$aj_field = (!$aj_field) ? '*':$aj_field;

	// 검색 조건
	$aj_search = ($aj_mb_id) ? " mb_id = '{$aj_mb_id}' " : stripslashes(urldecode($aj_where));

	$sql = "SELECT $aj_field 
				, ( SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) FROM {$g5['meta_table']} 
					WHERE mta_db_table = 'member' AND mta_db_id = mbr.mb_id ) metas
			FROM {$g5['member_table']} AS mbr
			WHERE  $aj_search ";
	$row = sql_fetch($sql,1);
	// 회원메타 분리
	$pieces = explode(',', $row['metas']);
	foreach ($pieces as $piece) {
		list($key, $value) = explode('=', $piece);
		$row[$key] = $value;
	}
	unset($pieces);unset($piece);
	//$row['pfl_name'] = number_format( $row['rmp_price'] );
	unset($row['mb_password']);

	$response->row = $row;

	$response->result = true;
	$response->msg = "데이타를 성공적으로 가지고 왔습니다.";

}
// 퇴사처리(탈퇴)
else if ($aj == "lea") {

    // 삭제(탈퇴)일자 입력
	$leave_date = ($aj_leave_date) ? preg_replace("/-/","",$aj_leave_date) : date('Ymd', G5_SERVER_TIME);
	$sql = " UPDATE {$g5['member_table']} SET mb_leave_date = '".$leave_date."' WHERE mb_id = '".$aj_mb_id."' ";
	sql_query($sql,1);

	// 회원자료 초기화
	$sql = "	UPDATE {$g5['member_table']} SET 
					mb_password = ''
					, mb_level = 1
					, mb_memo = '".date('Y-m-d H:i', G5_SERVER_TIME)." 탈퇴처리 by ".$member['mb_name']."\n{$mb['mb_memo']}'
				WHERE mb_id = '{$aj_mb_id}' ";
	sql_query($sql);

	// 회원자료 삭제
	//member_delete($aj_mb_id);

	$response->row = $row;

	$response->result = true;
	$response->msg = "회원 정보를 퇴사(탈퇴) 처리하였습니다.";

}
// 회원 리스트 
else if ($aj == "list") {

	// 기본 조건
	$aj_default = ($lv) ? " AND mb_level IN (".$lv.")" : "";

	// 검색 조건
	$aj_search = " WHERE mb_leave_date = '' {$aj_default} ";
	if ($aj_stx) {
		switch ($aj_sfl) {
			case ('mb_id' or 'mb_nick' or 'mb_sex'):
				$aj_search .= " AND $aj_sfl = '".urldecode($aj_stx)."' ";	//-- 한글 엔코딩
				break;
			case "mb_name" :
				$aj_search .= " AND $aj_sfl LIKE '%".urldecode($aj_stx)."%' ";	
				break;
			default :
				$aj_search .= " AND $aj_sfl LIKE '".urldecode($aj_stx)."%' ";	
				break;	
		}
	}

	if($aj_orderby)
		$aj_orderby = " ORDER BY ".stripslashes( urldecode($aj_orderby) );
	else 
		$aj_orderby = " ORDER BY mb_no DESC ";

	$rows = 10;
	if (!$pagenum) $pagenum = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($pagenum - 1) * $rows; // 시작 열을 구함

	$sql = "SELECT SQL_CALC_FOUND_ROWS mbr.*
			, ( SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) FROM {$g5['meta_table']} 
				WHERE mta_db_table = 'member' AND mta_db_id = mbr.mb_id ) metas
			FROM {$g5['member_table']} AS mbr
			$aj_search
			$aj_orderby
			LIMIT {$from_record}, {$rows}
			";
	$rs = sql_query($sql,1);
	$sql_2 = "SELECT FOUND_ROWS() as total";
	$rs_2 = sql_query($sql_2);
	$count = sql_fetch_array($rs_2);
	$response->total = $count['total'];
	$response->total_page = ceil($count['total'] / $rows);  // 전체 페이지
	//while($row = sql_fetch_array($rs)) { $response->rows[] = $row; }
	while($row = sql_fetch_array($rs)) {
		// 메타 분리
		$pieces = explode(',', $row['metas']);
		foreach ($pieces as $piece) {
			list($key, $value) = explode('=', $piece);
			$row[$key] = $value;
		}
		unset($pieces);unset($piece);
		//$row['pfl_name'] = number_format( $row['rmp_price'] );
		
		// 관리자 아니면 휴대폰&이메일 별표 처리
		if($member['mb_1'] < 10) {
			$row['mb_email_array'] = explode('@', $row['mb_email']);
			$row['mb_email2'] = substr($row['mb_email_array'][0],0,-3).'***@'.$row['mb_email_array'][1];
			$row['middle_hp'] = (strlen(preg_replace('/-/','',$row['mb_hp'])) >= 11) ? '****':'***';  	
			$row['mb_hp2'] = substr($row['mb_hp'],0,3).'-'.$row['middle_hp'].'-'.substr($row['mb_hp'],-4);			
		}

		$response->rows[] = $row;
	}

	$response->result = true;
	$response->msg = "데이타를 성공적으로 가지고 왔습니다.";

}
// 제작관리:원고접수자변경
else if ($aj == "s1") {

    // 영업자
    $mb2 = get_table_meta('member','mb_id',$aj_mb_id);
    
    // 원고접수자
    if($tar1=='mb_name_wongo') {
        $sql_common = "mb_id_wongo = '".$aj_mb_id."'
                        , mb_name_wongo = '".$mb2['mb_name']."'
        ";
    }
    // 기획자
    else if($tar1=='mb_name_plan') {
        $sql_common = "mb_id_plan = '".$aj_mb_id."'
                        , mb_name_plan = '".$mb2['mb_name']."'
        ";
    }
    // 디자이너
    else if($tar1=='mb_name_designer') {
        $sql_common = "mb_id_designer = '".$aj_mb_id."'
                        , mb_name_designer = '".$mb2['mb_name']."'
        ";
    }
    // 개발자
    else if($tar1=='mb_name_developer') {
        $sql_common = "mb_id_developer = '".$aj_mb_id."'
                        , mb_name_developer = '".$mb2['mb_name']."'
        ";
    }

    $sql = " UPDATE {$g5['site_table']} SET
                    {$sql_common}
                WHERE sit_idx = '".$sit_idx."'
    ";
    sql_query($sql,1);

	$response->result = true;
	$response->msg = "당당자 정보를 변경하였습니다.";

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}


$response->sql = $sql;

echo json_encode($response);
exit;
?>