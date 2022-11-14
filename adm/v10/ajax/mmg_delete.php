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

//-- 하위 카테고리(들) 추출
$sub_idxs_fetch = sql_fetch(" SELECT GROUP_CONCAT(DISTINCT cast(mmg.mmg_idx as char)) mmg_idxs
									, GROUP_CONCAT(DISTINCT mmg.mmg_name) mmg_names
									, 1 sw
								FROM {$g5['mms_group_table']} AS mmg,
								        {$g5['mms_group_table']} AS parent,
								        {$g5['mms_group_table']} AS sub_parent,
								        (
									        SELECT mmg.mmg_idx, mmg.mmg_name, (COUNT(parent.mmg_idx) - 1) AS depth
									        FROM {$g5['mms_group_table']} AS mmg,
									           {$g5['mms_group_table']} AS parent
									        WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
									           AND mmg.mmg_idx = '".$mmg_idx."'
									        GROUP BY mmg.mmg_idx
									        ORDER BY mmg.mmg_left
								        )AS sub_tree
								WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
								        AND mmg.mmg_left BETWEEN sub_parent.mmg_left AND sub_parent.mmg_right
								        AND sub_parent.mmg_idx = sub_tree.mmg_idx
								        AND mmg.com_idx = '$com_idx'
								GROUP BY sw
								ORDER BY mmg.mmg_left
");


//-- MMS 상태값 변경
$sql = " UPDATE {$g5['mms_table']} WHERE com_idx = '".$com_idx."' AND mmg_idx in (".$sub_idxs_fetch[mmg_idxs].") ";
sql_query($sql);
	

//-- 관련 카테고리 모두 삭제 & left, right 업데이트
sql_query(" SELECT @myLeft := mmg_left, @myRight := mmg_right, @myWidth := mmg_right - mmg_left + 1
				FROM {$g5['mms_group_table']}
				WHERE mmg_idx = '".$mmg_idx."' 
			");
if($delete == 1) {	// 완전 삭제인 경우
	sql_query(" DELETE FROM {$g5['mms_group_table']} WHERE mmg_left BETWEEN @myLeft AND @myRight AND com_idx = '$com_idx' ");
}
else {
	sql_query(" UPDATE {$g5['mms_group_table']} SET mmg_status = 'trash' WHERE mmg_left BETWEEN @myLeft AND @myRight AND com_idx = '$com_idx' ");
}

sql_query(" UPDATE {$g5['mms_group_table']} SET mmg_right = mmg_right - @myWidth WHERE mmg_right > @myRight AND com_idx = '$com_idx' ");
sql_query(" UPDATE {$g5['mms_group_table']} SET mmg_left = mmg_left - @myWidth WHERE mmg_left > @myRight AND com_idx = '$com_idx' ");


echo json_encode($response);
exit;

?>