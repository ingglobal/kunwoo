<?php
// http://localhost/icmms/adm/v10/ajax/mms.group.php?aj=grp&com_idx=9999
// http://localhost/icmms/adm/v10/ajax/mms.group.php?aj=grp&com_idx=9999&up_idx=16
// http://localhost/icmms/adm/v10/ajax/mms.group.php?aj=grp&com_idx=9999&up_idx=17

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

// 해당 단계 그룹만 출력
if ($aj == "grp") {

    //-- 카테고리 구조 추출 --//
    $sql = "SELECT 
                mmg_idx
                , GROUP_CONCAT(name) AS mmg_name
                , mmg_type
                , mmg_memo
                , mmg_status
                , GROUP_CONCAT(cast(depth as char)) AS depth
                , GROUP_CONCAT(up_idxs) AS up_idxs
                , SUBSTRING_INDEX(SUBSTRING_INDEX(up_idxs, ',', GROUP_CONCAT(cast(depth as char))),',',-1) AS up1st_idx
                , GROUP_CONCAT(up_names) AS up_names
                , GROUP_CONCAT(down_idxs) AS down_idxs
                , GROUP_CONCAT(down_names) AS down_names
                , leaf_node_yn
                , SUM(table_row_count) AS table_row_count
            FROM (	(
                    SELECT mmg.mmg_idx
                        , CONCAT( REPEAT('   ', COUNT(parent.mmg_idx) - 1), mmg.mmg_name) AS name
                        , mmg.mmg_type
                        , mmg.mmg_memo
                        , mmg.mmg_status
                        , (COUNT(parent.mmg_idx) - 1) AS depth
                        , GROUP_CONCAT(cast(parent.mmg_idx as char) ORDER BY parent.mmg_left) AS up_idxs
                        , GROUP_CONCAT(parent.mmg_name ORDER BY parent.mmg_left SEPARATOR '|') AS up_names
                        , NULL down_idxs
                        , NULL down_names
                        , (CASE WHEN mmg.mmg_right - mmg.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
                        , 0 AS table_row_count
                        , mmg.mmg_left
                        , 1 sw
                    FROM {$g5['mms_group_table']} AS mmg,
                            {$g5['mms_group_table']} AS parent
                    WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
                        AND mmg.com_idx = '".$com_idx."'
                        AND parent.com_idx = '".$com_idx."'
                        AND mmg.mmg_status NOT IN ('trash','delete') AND parent.mmg_status NOT IN ('trash','delete')
                    GROUP BY mmg.mmg_idx
                    ORDER BY mmg.mmg_left
                    )
                UNION ALL
                    (
                    SELECT parent.mmg_idx
                        , NULL name
                        , mmg.mmg_type
                        , mmg.mmg_memo
                        , mmg.mmg_status
                        , NULL depth
                        , NULL up_idxs
                        , NULL up_names
                        , GROUP_CONCAT(cast(mmg.mmg_idx as char) ORDER BY mmg.mmg_left) AS down_idxs
                        , GROUP_CONCAT(mmg.mmg_name ORDER BY mmg.mmg_left SEPARATOR '|') AS down_names
                        , (CASE WHEN parent.mmg_right - parent.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
                        , SUM(mmg.mmg_count) AS table_row_count
                        , parent.mmg_left
                        , 2 sw
                    FROM {$g5['mms_group_table']} AS mmg
                            , {$g5['mms_group_table']} AS parent
                    WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
                        AND mmg.com_idx = '".$com_idx."'
                        AND parent.com_idx = '".$com_idx."'
                        AND mmg.mmg_status NOT IN ('trash','delete') AND parent.mmg_status NOT IN ('trash','delete')
                    GROUP BY parent.mmg_idx
                    ORDER BY parent.mmg_left
                    )
                ) db_table
            GROUP BY mmg_idx
            ORDER BY mmg_left
    ";
    // echo $sql.'<br>';
    $result = sql_query($sql,1);
    $total_count = sql_num_rows($result);
    $list = array();
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
        $row['mmg_name'] = trim($row['mmg_name']);
        $list[] = $row;
    }
    // print_r2($list);

    $arr = array();
    for ($i=0; $i<sizeof($list); $i++) {
        // print_r2($list[$i]);
        // 부모값이 있는 하위 자식 노드 그룹만 추출해서 배열화
        if( $up_idx ) {
            if( $up_idx == $list[$i]['up1st_idx'] ) {
                $arr[] = $list[$i];
            }
            // get values for mms setting.
            if($up_idx == $list[$i]['mmg_idx']) {
                $up_leaf_node_yn[$up_idx] = $list[$i]['leaf_node_yn'];
                $up_depth[$up_idx] = $list[$i]['depth'];
                // echo $up_idx.'/'.$up_depth[$up_idx].'<br>';
            }
        } 
        // up_idx가 없으면 최상위 그룹만 추출
        else {
            if($list[$i]['depth']==0)
                $arr[] = $list[$i];
        }
    }
    // print_r2($arr);
    // print_r2($up_leaf_node_yn);
    // print_r2($up_depth);
    
    // leaf_node이면서 하위 그룹이 없는 경우는 mms_idx를 추출해서 넘겨줌
    // Select box values are like 6-6 or 6-7.. 
    // this way you will know it is mms value which is not for mmg group.
    if($up_leaf_node_yn[$up_idx]==1 && @sizeof($arr)==0) {
        // mms_idxes 설정
        $sql = "SELECT CONCAT('".$up_idx."','-',mms_idx) AS mmg_idx
                    , mms_name AS mmg_name
                FROM {$g5['mms_table']}
                WHERE mms_status NOT IN ('trash','delete') 
                    AND com_idx = '".$com_idx."'
                    AND mmg_idx = '".$up_idx."'
        ";
        // echo $sql.'<br>';
        $result = sql_query($sql,1);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $row['depth'] = $up_depth[$up_idx]+1;
            // print_r2($row);
            $arr[] = $row;
        }
        if($i) {
            $response->mms_flag = true;
        }
    }


 	$response->result = true;
 	$response->list = $arr;
	$response->msg = "그룹 정보 호출 성공!";

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}


$response->sql = $sql;

echo json_encode($response);
exit;
?>