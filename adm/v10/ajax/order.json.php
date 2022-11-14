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

// 담당 영업자 설정
if ($aj == "s1") {

    $od = get_table_meta('g5_shop_order','od_id',$aj_od_id,'shop_order');
	$mb2 = get_member2($aj_mb_id);

    // od_keys 정보 생성
    $od_keys = keys_update('mb_name_saler',$mb2['mb_name'],$od['od_keys']);
    $od_keys = keys_update('trm_name_department',$g5['department_name'][$mb2['mb_2']],$od['od_keys']);

    // od_more 정보 설정
    $od_more = '';
    $od_more = serialized_update('mb_name_saler',$mb2['mb_name'],$od['od_more']);
    $od_more = serialized_update('trm_name_department',$g5['department_name'][$mb2['mb_2']],$od['od_more']);

    $sql = "UPDATE {$g5['g5_shop_order_table']} SET
                od_keys = '".$od_keys."'
                , mb_id_saler = '".$mb2['mb_id']."'
                , trm_idx_department = '".$mb2['mb_2']."'
            WHERE od_id = '".$od['od_id']."'
	";
    //echo $sql.'<br>';
    sql_query($sql,1);

    // 메타 검색 정보 업데이트
    $ar['mta_db_table'] = 'shop_order';
    $ar['mta_db_id'] = $od['od_id'];
    $ar['mta_key'] = 'od_more';
    $ar['mta_value'] = $od_more;
    meta_update($ar);
    unset($ar);


	$response->result = true;
	$response->mb_name_saler = $mb2['mb_name'];
	$response->msg = "영업자 정보를 변경하였습니다.";

}
// 담당 영업자 해제
else if ($aj == "d1") {

    $od = get_table_meta('g5_shop_order','od_id',$aj_od_id,'shop_order');
	$mb2 = get_member2($aj_mb_id_saler);

    // od_keys 정보 설정
    $od['od_keys'] = keys_update('mb_name_saler','',$od['od_keys']);
    $od['od_keys'] = keys_update('trm_name_department','',$od['od_keys']);

    // od_more 정보 설정
    $od['od_more'] = serialized_update('mb_name_saler','',$od['od_more']);
    $od['od_more'] = serialized_update('trm_name_department','',$od['od_more']);

    $sql = "UPDATE {$g5['g5_shop_order_table']} SET
                od_keys = '".$od['od_keys']."'
                , mb_id_saler = ''
                , trm_idx_department = ''
            WHERE od_id = '".$od['od_id']."'
	";
    //echo $sql.'<br>';
    sql_query($sql,1);

    // 메타 검색 정보 업데이트
    $ar['mta_db_table'] = 'shop_order';
    $ar['mta_db_id'] = $od['od_id'];
    $ar['mta_key'] = 'od_more';
    $ar['mta_value'] = $od['od_more'];
    meta_update($ar);
    unset($ar);

	
	$response->result = true;
	$response->mb_id_saler = '';
	$response->msg = "영업자 정보를 제거하였습니다.";

}
// 1개 정보 추출
else if ($aj == "get") {
	// 요청 필드가 없으면 전체
	$aj_field = (!$aj_field) ? '*':$aj_field;

	// 검색 조건
	$aj_search = ($aj_mb_id) ? " od_id = '{$aj_od_id}' " : urldecode($aj_where);

	$sql = "SELECT $aj_field 
				, ( SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) FROM {$g5['meta_table']} 
					WHERE mta_db_table = 'shop_order' AND mta_db_id = od.od_id ) metas
			FROM {$g5['g5_shop_order_table']} AS od
			WHERE od_status NOT IN ('trash') $aj_search ";
	$row = sql_fetch($sql,1);
	// 메타 분리
	$pieces = explode(',', $row['metas']);
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
		$sql_join = "	INNER JOIN {$g5['odpany_member_table']} AS cmm
							ON cmm.od_id = od.od_id " . $sql_my_department . $sql_my_id; 
		$sql_groupby = "	GROUP BY od_id "; 
	}

	$sql_common = " FROM {$g5['g5_shop_order_table']} AS od {$sql_join} "; 

	// 기본 조건
	$aj_search = " WHERE od_status NOT IN ('trash','delete') ".$sql_trm_idx_od_type;
	if ($aj_stx) {
		switch ($aj_sfl) {
			case 'od_name' :
				$aj_search .= " AND od_name LIKE '%".urldecode($aj_stx)."%' OR od_names LIKE '%".urldecode($aj_stx)."%' ";	//-- 한글 엔코딩
				break;
			case ( $aj_sfl == 'mb_id' || $aj_sfl == 'od_id' ) :
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
		$aj_orderby = " ORDER BY od_reg_dt DESC ";

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
					WHERE mta_db_table = 'shop_order' AND mta_db_id = com.od_id ) metas
			{$sql_common}
			$aj_search
--			GROUP BY od_id
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
		$pieces = explode(',', $row['metas']);
		foreach ($pieces as $piece) {
			list($key, $value) = explode('=', $piece);
			$row[$key] = $value;
		}
		unset($pieces);unset($piece);
		//$row['pfl_name'] = number_format( $row['rmp_price'] );
		
		//암호풀기.
		$row['od_insta_pw'] =  $row['od_insta_pw']?trim(decryption($row['od_insta_pw'])):'';
		$row['od_face_pw'] = $row['od_face_pw']?trim(decryption($row['od_face_pw'])):'';

		$response->rows[] = $row;
	}

	$response->result = true;
	$response->msg = "데이타를 성공적으로 가지고 왔습니다.";

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}


$response->sql = $sql;

echo json_encode($response);
exit;
?>