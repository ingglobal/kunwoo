<?php
// 크롬 요소검사 열고 확인하면 되겠습니다. 
// print_r2 안 쓰고 print_r로 확인하는 게 좋습니다.
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

//print_r2($_REQUEST);exit;
//echo $_REQUEST['shf_type'][0];
$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if(is_array($getData[0]['list'])) {

    for($i=0;$i<sizeof($getData[0]['list']);$i++) {
        $arr = $getData[0]['list'][$i];

        $arr['shf_status'] = 'ok';
        
        // 공통요소
        $sql_common[$i] = " com_idx = '".$arr['com_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , shf_name = '".$arr['shf_name']."'
                        , shf_no = '".$arr['shf_no']."'
                        , shf_start_time = '".$arr['shf_start_time']."'
                        , shf_end_time = '".$arr['shf_end_time']."'
                        , shf_target = '".$arr['shf_target']."'
                        , shf_start_dt = '".$arr['shf_start_dt']."'
                        , shf_status = '".$arr['shf_status']."'
        ";

        //com_idx, imp_idx, mms_idx, shf_code, shf_group, shf_type, shf_no, shf_date, shf_time
        // 상기 8개 값 체크를 해서 같은 값이 들어오면 중복으로 본다. (업데이트)
        $sql_shf = "   SELECT shf_idx FROM {$g5['shift_table']} 
                        WHERE com_idx = '".$arr['com_idx']."'
                            AND mms_idx = '".$arr['mms_idx']."'
                            AND shf_no = '".$arr['shf_no']."'
                            AND shf_start_dt = '".$arr['shf_start_dt']."'
        ";
        //echo $sql_shf.'<br>';
		$shf = sql_fetch($sql_shf,1);
        
		// 정보 업데이트
		if($shf['shf_idx']) {
			
			$sql = "UPDATE {$g5['shift_table']} SET 
						{$sql_common[$i]}
						, shf_update_dt = '".G5_TIME_YMDHIS."'
					WHERE shf_idx = '".$shf['shf_idx']."'";
			sql_query($sql,1);
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Updated OK!";

		}
        // 정보 입력
        else{

			$sql = "INSERT INTO {$g5['shift_table']} SET 
						{$sql_common[$i]}
						, shf_reg_dt = '".G5_TIME_YMDHIS."'
            ";
			sql_query($sql,1);
            //echo $sql.'<br>';
            $shf['shf_idx'] = sql_insert_id();
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Inserted OK!";
        
        }

        $result_arr[$i]['shf_idx'] = $shf['shf_idx'];
    
    }
	
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>