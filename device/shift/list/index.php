<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

//print_r2($_REQUEST);exit;
//echo $_REQUEST['mms_idx'];
$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고
//print_r2($getData);

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if($getData[0]['mms_idx']) {

    $mms = get_table_meta('mms','mms_idx',$getData[0]['mms_idx']);
    $com = get_table_meta('company','com_idx',$mms['com_idx']);
    $result_arr['mms_idx'] = $mms['mms_idx'];
    $result_arr['imp_idx'] = $mms['imp_idx'];
    $result_arr['mms_name'] = $mms['mms_name'];
    $result_arr['com_idx'] = $com['com_idx'];
    $result_arr['com_name'] = $com['com_name'];
    
    $sql = "SELECT *
            FROM {$g5['shift_table']}
            WHERE shf_status = 'ok'
                AND mms_idx = '".$getData[0]['mms_idx']."'
            ORDER BY shf_start_dt
    ";
    $rs = sql_query($sql,1);
    //echo $sql.'<br>';
    $list = array();
    for($i=0;$row=sql_fetch_array($rs);$i++) {
        //print_r2($row);
        $row1['shf_start_dt'] = $row['shf_start_dt'];
        $row1['shf_end_dt'] = $row['shf_end_dt'];
        $row1['shf_range_1'] = $row['shf_range_1'];
        $row1['shf_range_2'] = $row['shf_range_2'];
        $row1['shf_range_3'] = $row['shf_range_3'];
        $row1['shf_target_1'] = $row['shf_target_1'];
        $row1['shf_target_2'] = $row['shf_target_2'];
        $row1['shf_target_3'] = $row['shf_target_3'];

        $list[] = $row1;
    }
    $result_arr['list'] = $list;
    
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>