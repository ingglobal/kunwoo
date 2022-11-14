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

// print_r2($_REQUEST);

// 그래프 내보내기
if ($aj == "put") {

	// from변수 serialize된 변수를 분리
	$params = array();
	parse_str($frm_data, $params);
	foreach($params as $key => $value) {
		${$key} = $value;
    }

    // 그래프 이름 생성
    for($i=0;$i<sizeof($data_series);$i++) {
        $dta_names[] = $data_series[$i]['name'];
    }
    $dta_name = implode(", ",$dta_names);
    // echo $dta_name.'<br>';
    
    // data_series 정보 분리
    // print_r2($data_series).'<br>';
    $data_series = json_encode($data_series, JSON_UNESCAPED_UNICODE);
    // echo $data_series.'<br>';


    // 설정 정보
    $mbd_setting = '';
    $mbd_setting = serialized_update('graph_name',$dta_name,$mbd_setting);
    $mbd_setting = serialized_update('st_date',$st_date,$mbd_setting);
    $mbd_setting = serialized_update('st_time',$st_time,$mbd_setting);
    $mbd_setting = serialized_update('en_date',$en_date,$mbd_setting);
    $mbd_setting = serialized_update('en_time',$en_time,$mbd_setting);
    $mbd_setting = serialized_update('dta_item',$dta_item,$mbd_setting);
    $mbd_setting = serialized_update('dta_unit',$dta_unit,$mbd_setting);
    $mbd_setting = serialized_update('data_series',$data_series,$mbd_setting);


    $sql_common = " mb_id = '".$member['mb_id']."'
                    , com_idx = '".$com_idx."'
                    , mms_idx = '".$mms_idx."'
                    , mbd_type = 'graph '
                    , mbd_setting = '".$mbd_setting."'
    ";

    $sql = " SELECT mbd_idx FROM {$g5['member_dash_table']} WHERE mbd_idx = '".$mbd_idx."' ";
    $mbd = sql_fetch($sql,1);
    //echo $sql.'<br>';
    if($mbd['mbd_idx']) {
        $sql = " UPDATE {$g5['member_dash_table']} SET
                    {$sql_common}
                WHERE mbd_idx = '".$mbd['mbd_idx']."'
        ";
        sql_query($sql,1);
    }
    else {
        // 기존거 다 +1 한 후에
        $sql = " UPDATE {$g5['member_dash_table']} SET
                        mbd_value = mbd_value + 1
                    WHERE mb_id = '".$member['mb_id']."'
                        AND  mms_idx = '".$mms_idx."'
                        AND mbd_type = 'graph '
        ";
        sql_query($sql,1);

        $sql = " INSERT INTO {$g5['member_dash_table']} SET
                    {$sql_common}
                    , mbd_value = '1'
                    , mbd_status = 'show'
                    , mbd_reg_dt = '".G5_TIME_YMDHIS."'
        ";
        // echo $sql.'<br>';
        sql_query($sql,1);
        $mbd['mbd_idx'] = sql_insert_id();
    }

 	$response->result = true;
	$response->mbd_idx = $mbd['mbd_idx'];
	$response->msg = "그래프 정보를 추가하였습니다.";

}
// 그래프 타이틀 수정
else if ($aj == "tit") {

    if($mbd_idx) {
        $mbd = get_table_meta('member_dash','mbd_idx',$mbd_idx);
        $mbd['sried'] = get_serialized($mbd['mbd_setting']);
        $mbd['data'] = json_decode($mbd['sried']['data_series'],true);
        for($j=0;$j<sizeof($mbd['data']);$j++) {
            // print_r2($row['data'][$j]);
        }

        // 설정 정보
        $mbd_setting = $mbd['mbd_setting'];
        $mbd_setting = serialized_update('graph_name',$mbd_title,$mbd_setting);
        
        $sql = " UPDATE {$g5['member_dash_table']} SET
                    mbd_setting = '".$mbd_setting."'
                WHERE mbd_idx = '".$mbd['mbd_idx']."'
        ";
        sql_query($sql,1);

        $response->result = true;
        $response->mbd_idx = $mbd_idx;
        $response->msg = "그래프 이름변경 완료!";	
    }
    else {
        $response->msg = "그래프 정보가 없습니다.";	
    }

}
// 그래프 삭제
else if ($aj == "del") {

    if($mbd_idx) {
        $sql = " DELETE FROM {$g5['member_dash_table']}
                WHERE mbd_idx = '".$mbd_idx."'
        ";
        sql_query($sql,1);

        $response->result = true;
        $response->mbd_idx = $mbd_idx;
        $response->msg = "그래프를 삭제하였습니다.";	
    }
    else {
        $response->msg = "그래프 정보가 없습니다.";	
    }

}
// 그래프 재정렬
else if ($aj == "srt") {

    if(is_array($mbd_idxs)) {

        for($i=0;$i<sizeof($mbd_idxs);$i++) {
            $sql = " UPDATE {$g5['member_dash_table']} SET
                        mbd_value = '".($i+1)."'
                    WHERE mbd_idx = '".$mbd_idxs[$i]."'
            ";
            sql_query($sql,1);
        }
        $response->result = true;
        $response->mbd_idx = $mbd_idx;
        $response->msg = "그래프를 재정렬하였습니다.";
    }
    else {
        $response->msg = "그래프 정보가 없습니다.";
    }

}
// 1개 정보 추출
else if ($aj == "get") {
	// 요청 필드가 없으면 전체
	$aj_field = (!$aj_field) ? '*':$aj_field;

	// 검색 조건
	$aj_search = ($aj_mb_id) ? " ct_id = '{$aj_ct_id}' " : urldecode($aj_where);

	$sql = "SELECT $aj_field 
				, ( SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) FROM {$g5['meta_table']} 
					WHERE mta_db_table = 'shop_cart' AND mta_db_id = od.ct_id ) metas
			FROM {$g5['g5_shop_cart_table']} AS od
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
// 장바구니 리스트 
else if ($aj == "list") {
	
	// 관리자 레벨이 아니면 자기 조직 것만 리스트에 나옴, 2=회원,4=업체,6=영업자,8=관리자,10=수퍼관리자
	// aj_auth 변수를 받으면 전체 리스트합니다.
	if (!$aj_auth && $member['mb_level']<8) {
		// 디폴트 그룹 접근 레벨
		$my_access_department_idx = $member['trm_idx_department'];

		// 팀장 이하는 자기 업체만 리스트, 0=사원,2=주임,4=대리,6=팀장,8=부서장,10=대표
		if ($member['mb_position'] < 6) {
			$sql_my_id .= " AND mb_id_worker = '{$member['mb_id']}' ";
		}
		else {
			// 팀장 이상이면서 상위 그룹 접근이 가능하다면..
			if ($member['mb_group_yn'] == 1)
				$my_access_department_idx = $g5['department_uptop_idx'][$member['trm_idx_department']];
		}

		$sql_my_department .= " AND trm_idx_department IN (".$g5['department_down_idxs'][$my_access_department_idx].") ";
		$sql_join = "	INNER JOIN {$g5['odpany_member_table']} AS cmm
							ON cmm.ct_id = od.ct_id " . $sql_my_department . $sql_my_id; 
		$sql_groupby = "	GROUP BY ct_id "; 
	}

	$sql_common = " FROM {$g5['g5_shop_cart_table']} AS od {$sql_join} "; 

	// 기본 조건
	$aj_search = " WHERE od_status NOT IN ('trash','delete') ".$sql_trm_idx_od_type;
	if ($aj_stx) {
		switch ($aj_sfl) {
			case 'od_name' :
				$aj_search .= " AND od_name LIKE '%".urldecode($aj_stx)."%' OR od_names LIKE '%".urldecode($aj_stx)."%' ";	//-- 한글 엔코딩
				break;
			case ( $aj_sfl == 'mb_id' || $aj_sfl == 'ct_id' ) :
				$aj_search .= " AND $aj_sfl = '".$aj_stx."' ";
				break;
			case ($aj_sfl == 'mb_id_worker' || $aj_sfl == 'mb_name_worker' ) :
				$aj_search .= " AND (mb_id_workers LIKE '%^{$aj_stx}^%') ";
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
					WHERE mta_db_table = 'shop_cart' AND mta_db_id = com.ct_id ) metas
			{$sql_common}
			$aj_search
--			GROUP BY ct_id
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