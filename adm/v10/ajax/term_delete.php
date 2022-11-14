<?php
//header("Content-Type: text/plain; charset=utf-8");
include_once('./_common.php');
/*
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
//-- 하위 카테고리(들) 추출
$sub_idxs_fetch = sql_fetch(" SELECT GROUP_CONCAT(DISTINCT cast(terms.trm_idx as char)) trm_idxs
									, GROUP_CONCAT(DISTINCT terms.trm_name) trm_names
									, 1 sw
								FROM {$g5['term_table']} AS terms,
								        {$g5['term_table']} AS parent,
								        {$g5['term_table']} AS sub_parent,
								        (
									        SELECT terms.trm_idx, terms.trm_name, (COUNT(parent.trm_idx) - 1) AS depth
									        FROM {$g5['term_table']} AS terms,
									        {$g5['term_table']} AS parent
									        WHERE terms.trm_left BETWEEN parent.trm_left AND parent.trm_right
									        AND terms.trm_idx = '".$trm_idx."'
									        GROUP BY terms.trm_idx
									        ORDER BY terms.trm_left
								        )AS sub_tree
								WHERE terms.trm_left BETWEEN parent.trm_left AND parent.trm_right
								        AND terms.trm_left BETWEEN sub_parent.trm_left AND sub_parent.trm_right
								        AND sub_parent.trm_idx = sub_tree.trm_idx
								        AND terms.trm_taxonomy = '$taxonomy'
								GROUP BY sw
								ORDER BY terms.trm_left
");
//-- 조직구조 삭제인 경우
if ($taxonomy == "department") {

	//-- 직원 탈퇴 처리
	sql_query("UPDATE {$g5['member_table']} mbr INNER JOIN {$g5['term_relation_table']} tmr
					ON mbr.mb_id = tmr.tmr_db_id
						AND tmr.tmr_db_table = 'member'
						AND tmr.trm_idx in (".$sub_idxs_fetch[trm_idxs].")
					SET mbr.mb_leave_date = '".date('Ymd', G5_SERVER_TIME)."'
				");
	
	//-- 교차 테이블에서 레코드 삭제
	$sql = " DELETE FROM {$g5['term_relation_table']} WHERE tmr_db_table = 'member' AND trm_idx in (".$sub_idxs_fetch[trm_idxs].") ";
	sql_query($sql);
	
}
//-- 상품 카테고리 삭제인 경우
else if ($taxonomy == "product_category") {
	
	//-- 상품 삭제 처리
	sql_query("UPDATE {$g5['product_table']} prd INNER JOIN {$g5['term_relation_table']} tmr
					ON prd.prd_idx = tmr.tmr_db_id
						AND tmr.tmr_db_table = 'product'
						AND tmr.trm_idx in (".$sub_idxs_fetch[trm_idxs].")
					SET prd.prd_status = 'trash'
				");
	
	//-- 교차 테이블에서 레코드 삭제
	$sql = " DELETE FROM {$g5['term_relation_table']} WHERE tmr_db_table = 'product' AND trm_idx in (".$sub_idxs_fetch[trm_idxs].") ";
	sql_query($sql);
	
}
//-- 카테고리 삭제인 경우
else if ($taxonomy == "category") {
	
	//-- 교차 테이블에서 레코드만 삭제하멘 됨
	$sql = " DELETE FROM {$g5['term_relation_table']} WHERE tmr_db_table = 'car' AND trm_idx in (".$sub_idxs_fetch[trm_idxs].") ";
	sql_query($sql);
	
}
*/							
//-- 라인 삭제인 경우
if ($taxonomy == "line") {

	$sql_trm = " UPDATE {$g5['term_table']} SET trm_status = 'trash' WHERE trm_taxonomy = 'line' AND trm_idx = '{$trm_idx}' ";
	sql_query($sql_trm,1);
	//-- 교차 테이블에서 레코드만 삭제하멘 됨
	// $sql = " DELETE FROM {$g5['term_relation_table']} WHERE tmr_db_table = 'car' AND trm_idx in (".$sub_idxs_fetch[trm_idxs].") ";
	// sql_query($sql);
	
}	
/*
//-- 관련 카테고리 모두 삭제 & left, right 업데이트
sql_query(" SELECT @myLeft := trm_left, @myRight := trm_right, @myWidth := trm_right - trm_left + 1
				FROM {$g5['term_table']}
				WHERE trm_idx = '".$trm_idx."' 
			");
if($delete == 1) {	// 완전 삭제인 경우
	sql_query(" DELETE FROM {$g5['term_table']} WHERE trm_left BETWEEN @myLeft AND @myRight AND trm_taxonomy = '$taxonomy' ");
}
else {
	sql_query(" UPDATE {$g5['term_table']} SET trm_status = 'trash' WHERE trm_left BETWEEN @myLeft AND @myRight AND trm_taxonomy = '$taxonomy' ");
}

sql_query(" UPDATE {$g5['term_table']} SET trm_right = trm_right - @myWidth WHERE trm_right > @myRight AND trm_taxonomy = '$taxonomy' ");
sql_query(" UPDATE {$g5['term_table']} SET trm_left = trm_left - @myWidth WHERE trm_left > @myRight AND trm_taxonomy = '$taxonomy' ");
*/

// 캐시 파일 삭제 (초기화)
unlink(G5_DATA_PATH.'/cache/term-line.php');

//$response->result=true;
echo 'ok';
exit;

?>