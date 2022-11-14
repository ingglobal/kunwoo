<?php
// 호출 파일들
// /adm/v10/member_select.php 업체선택 버튼
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

// 1개 정보 추출
if ($aj == "get") {
	// 요청 필드가 없으면 전체
	$aj_field = (!$aj_field) ? '*':$aj_field;

	// 검색 조건
	$aj_search = ($aj_mb_id) ? " com_idx = '{$aj_com_idx}' " : urldecode($aj_where);

	$sql = "SELECT $aj_field 
				, ( SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) FROM {$g5['meta_table']} 
					WHERE mta_db_table = 'company' AND mta_db_id = com.com_idx ) com_metas
			FROM {$g5['company_table']} AS com
			WHERE com_status NOT IN ('trash') $aj_search ";
	$row = sql_fetch($sql,1);
	// 메타 분리
	$pieces = explode(',', $row['com_metas']);
	foreach ($pieces as $piece) {
		list($key, $value) = explode('=', $piece);
		$row[$key] = $value;
	}
	unset($pieces);unset($piece);
	//$row['pfl_name'] = number_format( $row['rmp_price'] );
	//unset($row['mb_password']);

	$response->row = $row;

	$response->result = true;
	$response->msg = "데이타를 성공적으로 가지고 왔습니다.";

}
// 회원 리스트 
else if ($aj == "list") {
	
	// 관리자 레벨이 아니면 자기 조직 것만 리스트에 나옴, 2=회원,4=업체,6=영업자,8=관리자,10=수퍼관리자
	// aj_auth 변수를 받으면 전체 리스트합니다.
	if (!$aj_auth && $member['mb_level']<8) {
		// 디폴트 그룹 접근 레벨
		$my_access_department_idx = $member['trm_idx_department'];

		// 팀장 이하는 자기 업체만 리스트, 0=사원,2=주임,4=대리,6=팀장,8=부서장,10=대표
		if ($member['mb_position'] < 6) {
			$sql_my_id .= " AND mb_id_saler = '{$member['mb_id']}' ";
		}
		else {
			// 팀장 이상이면서 상위 그룹 접근이 가능하다면..
			if ($member['mb_group_yn'] == 1)
				$my_access_department_idx = $g5['department_uptop_idx'][$member['trm_idx_department']];
		}

		$sql_my_department .= " AND trm_idx_department IN (".$g5['department_down_idxs'][$my_access_department_idx].") ";
		$sql_join = "	INNER JOIN {$g5['company_saler_table']} AS cms
							ON cms.com_idx = com.com_idx " . $sql_my_department . $sql_my_id; 
		$sql_groupby = "	GROUP BY com_idx "; 
	}

	$sql_common = " FROM {$g5['company_table']} AS com {$sql_join} "; 

	// 기본 조건
	$aj_search = " WHERE com_status NOT IN ('trash','delete') ".$sql_trm_idx_com_type;
	if ($aj_stx) {
		switch ($aj_sfl) {
			case 'com_name' :
				$aj_search .= " AND com_name LIKE '%".urldecode($aj_stx)."%' OR com_names LIKE '%".urldecode($aj_stx)."%' ";	//-- 한글 엔코딩
				break;
			case ( $aj_sfl == 'mb_id' || $aj_sfl == 'com_idx' ) :
				$aj_search .= " AND $aj_sfl = '".$aj_stx."' ";
				break;
			case ($aj_sfl == 'mb_id_saler' || $aj_sfl == 'mb_name_saler' ) :
				$aj_search .= " AND (mb_id_salers LIKE '%^{$aj_stx}^%') ";
				break;
			default :
				$aj_search .= " AND ({$aj_sfl} LIKE '%{$aj_stx}%') ";
				break;
		}
	}

	if($aj_orderby)
		$aj_orderby = " ORDER BY ".stripslashes( urldecode($aj_orderby) );
	else 
		$aj_orderby = " ORDER BY com_reg_dt DESC ";

	$rows = 10;
	if (!$pagenum) $pagenum = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($pagenum - 1) * $rows; // 시작 열을 구함

	// GROUP BY까지 하면 속도가 너무 느립니다.
	$sql = "SELECT SQL_CALC_FOUND_ROWS com.*
				, ( SELECT CONCAT( 'mb_name=', mb_name
									, ',mb_nick=', mb_nick
									, ',mb_tel=', mb_tel
									, ',mb_hp=', mb_hp
									, ',mb_email=', mb_email
									)
					FROM {$g5['member_table']}  WHERE mb_id = com.mb_id ) AS mbr_info
				, ( SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) FROM {$g5['meta_table']} 
					WHERE mta_db_table = 'company' AND mta_db_id = com.com_idx ) com_metas
			{$sql_common}
			$aj_search
--			GROUP BY com_idx
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
		// 회원 정보 분리
		$pieces = explode(',', $row['mbr_info']);
		foreach ($pieces as $piece) {
			list($key, $value) = explode('=', $piece);
			$row[$key] = $value;
		}
		unset($pieces);unset($piece);

		// 메타 분리
		$pieces = explode(',', $row['com_metas']);
		foreach ($pieces as $piece) {
			list($key, $value) = explode('=', $piece);
			$row[$key] = $value;
		}
		unset($pieces);unset($piece);
		//$row['pfl_name'] = number_format( $row['rmp_price'] );
		
		//암호풀기.
		$row['com_insta_pw'] =  $row['com_insta_pw']?trim(decryption($row['com_insta_pw'])):'';
		$row['com_face_pw'] = $row['com_face_pw']?trim(decryption($row['com_face_pw'])):'';

		$response->rows[] = $row;
	}

	$response->result = true;
	$response->msg = "데이타를 성공적으로 가지고 왔습니다.";

}
// 영업자 상태 변경
else if ($aj == "sales") {

	$sql = "UPDATE {$g5['company_saler_table']} SET
				cms_status = '".$cms_status."'
				, cms_reg_dt = '".G5_TIME_YMDHIS."'
			WHERE cms_idx = '".$cms_idx."'
    ";
	sql_query($sql,1);
	
	$response->result = true;
	$response->msg = "영업자 정보를 변경하였습니다.";
	$response->cms_status = $cms_status;
	$response->cms_status_text = $g5['set_cms_status_value'][$cms_status];
	$response->cms_reg_dt = G5_TIME_YMD;

}
// add a saler for new company register (adm/v10/member_select.php)
else if ($aj == "s1") {

	//-- 함수 호출(extend/u.project.php), 있으면 UPDATE, 없으면 입력 --//
	$cms_idx = company_member_update(array(
		"mb_id_saler"=>$mb_id_saler
		, "com_idx"=>$com_idx
		, "cms_status"=>'ok'
	));
	
	if($cms_idx) {
        $response->result = true;
        $response->msg = "업체 정보를 추가하였습니다.";
    }
	else {
        $response->msg = "정보 저장 실패! 관리자에게 문의해 주세요.";
    }

}
// default com_idx change (adm/v10/company_select.popup.php)
else if ($aj == "c1") {

	$sql = " UPDATE {$g5['member_table']} SET mb_4 = '".$com_idx."' WHERE mb_id = '".$member['mb_id']."' ";
	sql_query($sql,1);

    set_session('ss_com_idx', $com_idx);
	
	$response->result = true;
	$response->msg = "업체를 변경하였습니다.";

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}


$response->sql = $sql;

echo json_encode($response);
exit;
?>