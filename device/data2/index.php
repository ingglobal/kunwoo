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

        $arr['dta_dt'] = preg_replace('/\./','-',$arr['dta_date'])." ".$arr['dta_time'];
        $arr['dta_status'] = 'ok';
        
        // 공통요소
        $sql_common[$i] = " com_idx = '".$arr['com_idx']."'
                        , imp_idx = '".$arr['imp_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , dta_shf_no = '".$arr['dta_shf_no']."'
                        , dta_shf_max = '".$arr['dta_shf_max']."'
                        , dta_code = '".$arr['dta_code']."'
                        , dta_group = '".$arr['dta_group']."'
                        , dta_type = '".$arr['dta_type']."'
                        , dta_no = '".$arr['dta_no']."'
                        , dta_name = '".$arr['dta_name']."'
                        , dta_dt = '".$arr['dta_dt']."'
                        , dta_value = '".$arr['dta_value']."'
                        , dta_message = '".$arr['dta_message']."'
                        , dta_unit = '".$arr['dta_unit']."'
                        , dta_status = '".$arr['dta_status']."'
        ";

        //com_idx, imp_idx, mms_idx, dta_code, dta_group, dta_type, dta_no, dta_date, dta_time
        // 상기 8개 값 체크를 해서 같은 값이 들어오면 중복으로 본다. (업데이트)
        $sql_dta = "   SELECT dta_idx FROM {$g5['data_table']} 
                        WHERE com_idx = '".$arr['com_idx']."'
                            AND imp_idx = '".$arr['imp_idx']."'
                            AND mms_idx = '".$arr['mms_idx']."'
                            AND dta_code = '".$arr['dta_code']."'
                            AND dta_group = '".$arr['dta_group']."'
                            AND dta_type = '".$arr['dta_type']."'
                            AND dta_no = '".$arr['dta_no']."'
                            AND dta_dt = '".$arr['dta_dt']."'
        ";
        //echo $sql_dta.'<br>';
		$dta = sql_fetch($sql_dta,1);
        
		// 정보 업데이트
		if($dta['dta_idx']) {
			
			$sql = "UPDATE {$g5['data_table']} SET 
						{$sql_common[$i]}
						, dta_update_dt = '".G5_TIME_YMDHIS."'
					WHERE dta_idx = '".$dta['dta_idx']."'";
			sql_query($sql,1);
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Updated OK!";

		}
        // 정보 입력
        else{

			$sql = "INSERT INTO {$g5['data_table']} SET 
						{$sql_common[$i]}
						, dta_reg_dt = '".G5_TIME_YMDHIS."'
            ";
			sql_query($sql,1);
            //echo $sql.'<br>';
            $dta['dta_idx'] = sql_insert_id();
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Inserted OK!";
        
        }

        $result_arr[$i]['dta_idx'] = $dta['dta_idx'];
    
    }
	
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>