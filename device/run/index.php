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

        $arr['dta_status'] = '0';
        $arr['dta_dt'] = strtotime(preg_replace('/\./','-',$arr['dta_date'])." ".$arr['dta_time']);
        $arr['dta_date1'] = date("Y-m-d",$arr['dta_dt']);   // 2 or 4 digit format(20 or 2020) no problem.
        $arr['dta_dt2'] = strtotime(preg_replace('/\./','-',$arr['dta_date2'])." 00:00:00");    // statistics date
        $arr['dta_date_stat'] = date("Y-m-d",$arr['dta_dt2']);   // 2 or 4 digit format(20 or 2020) no problem.

        // 통계일자 추출을 위한 교대기준 (data/cache/mms-setting.php, 설정은 user.07.default.php)
        $arr['mms_set_output'] = $g5['mms'][$arr['mms_idx']]['output'];
        if($arr['dta_date_stat'] == '0000-00-00') {
            // shift(교대기준) && 2교대 이상 && 오전입력 이라면 전일 통계일자로 합산해야 함
            if($arr['mms_set_output'] == 'shift' && $arr['dta_shf_no'] > 1 && substr($arr['dta_time'],0,2) < 12) {
                $arr['dta_date_stat'] = date("Y-m-d",$arr['dta_dt2']-86400);    // 하루를 뺴야 함
            }
        }
        
        // 공통요소
        $sql_common[$i] = " com_idx = '".$arr['com_idx']."'
                        , imp_idx = '".$arr['imp_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , dta_shf_no = '".$arr['dta_shf_no']."'
                        , dta_shf_max = '".$arr['dta_shf_max']."'
                        , dta_mmi_no = '".$arr['dta_mmi_no']."'
                        , dta_group = '".$arr['dta_group']."'
                        , dta_dt = '".$arr['dta_dt']."'
                        , dta_date = '".$arr['dta_date_stat']."'
                        , dta_value = '".$arr['dta_value']."'
                        , dta_status = '".$arr['dta_status']."'
        ";
        
        //com_idx, imp_idx, mms_idx, dat_shf_no, dta_code, dta_group, dta_type, dta_no, dta_date, dta_time
        // 상기 8개 값 체크를 해서 같은 값이 들어오면 중복으로 본다. (업데이트)
        $sql_dta = "   SELECT dta_idx FROM {$g5['data_run_table']} 
                        WHERE com_idx = '".$arr['com_idx']."'
                            AND imp_idx = '".$arr['imp_idx']."'
                            AND mms_idx = '".$arr['mms_idx']."'
                            AND dta_shf_no = '".$arr['dta_shf_no']."'
                            AND dta_mmi_no = '".$arr['dta_mmi_no']."'
                            AND dta_group = '".$arr['dta_group']."'
                            AND dta_dt = '".$arr['dta_dt']."'
        ";
        //echo $sql_dta.'<br>';
		$dta = sql_fetch($sql_dta,1);
        
		// 정보 업데이트
		if($dta['dta_idx']) {
			
			$sql = "UPDATE {$g5['data_run_table']} SET 
						{$sql_common[$i]}
						, dta_update_dt = '".G5_SERVER_TIME."'
					WHERE dta_idx = '".$dta['dta_idx']."'";
			sql_query($sql,1);
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Updated OK!";

		}
        // 정보 입력
        else{

			$sql = "INSERT INTO {$g5['data_run_table']} SET 
						{$sql_common[$i]}
						, dta_reg_dt = '".G5_SERVER_TIME."'
						, dta_update_dt = '".G5_SERVER_TIME."'
            ";
            $result = sql_query($sql, FALSE);
            // echo $sql.'<br>';
            $dta['dta_idx'] = sql_insert_id();
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Inserted OK!";



            // // 정보 입력 시 합계를 넣어줍니다.
            // // 수정시 하게 되면 key 중복이 일어날 가능성이 높아서 안 합니다. 나중에 별도로 쿼리 수정해 주세요.
            // // 정상으로 INSERT 되었다면 방문자 합계에 반영
            // if ($result) {

            //     // 2교대 이상으로 돌아갈 경우 전일로 합산처리해야 함(dta_shf_no > 1 && 오전 날짜 데이터)
            //     if($arr['dta_shf_no'] > 1 && substr($arr['dta_time'],0,2) < 12) {
            //         $arr['dta_date1'] = date("Y-m-d",$arr['dta_dt']-86400);
            //     }

            //     $sql = " INSERT INTO {$g5['data_run_sum_table']} SET
            //                 com_idx = '".$arr['com_idx']."'
            //                 , imp_idx = '".$arr['imp_idx']."'
            //                 , mms_idx = '".$arr['mms_idx']."'
            //                 , dta_shf_no = '".$arr['dta_shf_no']."'
            //                 , dta_mmi_no = '".$arr['dta_mmi_no']."'
            //                 , dta_group = '".$arr['dta_group']."'
            //                 , dta_date = '".$arr['dta_date1']."'
            //                 , dta_value = '".$arr['dta_value']."'
            //     ";
            //     $result = sql_query($sql);
            //     // DUPLICATE 오류가 발생한다면 이미 날짜별 행이 생성되었으므로 UPDATE 실행
            //     if (!$result) {
            //         $sql = "UPDATE {$g5['data_run_sum_table']} SET
            //                     dta_value = dta_value + ".$arr['dta_value']."
            //                 WHERE mms_idx = '".$arr['mms_idx']."'
            //                     AND dta_shf_no = '".$arr['dta_shf_no']."'
            //                     AND dta_group = '".$arr['dta_group']."'
            //                     AND dta_mmi_no = '".$arr['dta_mmi_no']."'
            //                     AND dta_date = '".$arr['dta_date1']."'
            //         ";
            //         $result = sql_query($sql,1);
            //     }
            // }
                
        }
        $result_arr[$i]['dta_idx'] = $dta['dta_idx'];   // 고유번호



        // 일간 sum 합계 입력
        $sum_common = " mms_idx = '".$arr['mms_idx']."'
                        AND dta_shf_no = '".$arr['dta_shf_no']."'
                        AND dta_mmi_no = '".$arr['dta_mmi_no']."'
                        AND dta_group = '".$arr['dta_group']."'
        ";
        $sql = "SELECT SUM(dta_value) AS dta_sum
                FROM {$g5['data_run_table']} 
                WHERE dta_status = 0
                    AND {$sum_common}
                    AND FROM_UNIXTIME(dta_dt,'%Y-%m-%d') = '".$arr['dta_date_stat']."'
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='sum_calculate',  mta_value = '".addslashes($sql)."' ");
        $sum1 = sql_fetch($sql,1); // 일 평균 데이터값 추출 

        // 있으면 업데이트, 없으면 생성
        $sql_sum = "   SELECT dta_idx FROM {$g5['data_run_sum_table']} 
                        WHERE {$sum_common}
                            AND dta_date = '".$arr['dta_date_stat']."'
        ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='sum_check', mta_value = '".addslashes($sql_sum)."' ");
        //echo $sql_sum.'<br>';
		$sum = sql_fetch($sql_sum,1);
		// 정보 업데이트
		if($sum['dta_idx']) {
            $sql = "UPDATE {$g5['data_run_sum_table']} SET
                        dta_value = '".$sum1['dta_sum']."'
                    WHERE {$sum_common}
                        AND dta_date = '".$arr['dta_date_stat']."'
            ";
            // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='update', mta_value = '".addslashes($sql)."' ");
            $result = sql_query($sql);
        }
        else {
            $sql = " INSERT INTO {$g5['data_run_sum_table']} SET
                        com_idx = '".$arr['com_idx']."'
                        , imp_idx = '".$arr['imp_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , mmg_idx = '".$g5['mms'][$arr['mms_idx']]['mmg_idx']."'
                        , dta_shf_no = '".$arr['dta_shf_no']."'
                        , dta_mmi_no = '".$arr['dta_mmi_no']."'
                        , dta_group = '".$arr['dta_group']."'
                        , dta_date = '".$arr['dta_date_stat']."'
                        , dta_value = '".$sum1['dta_sum']."'
            ";
            // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_key ='insert', mta_value = '".addslashes($sql)."' ");
            $result = sql_query($sql);
        }

        // 가동상태 입력 (설비상태 추출)
        $sql = " INSERT INTO {$g5['data_run_real_table']} SET
                    mms_idx = '".$arr['mms_idx']."'
                    , mms_status = '".$arr['mms_status']."'
                    , dta_dt = '".G5_TIME_YMDHIS."'
        ";
        $result = sql_query($sql);
    
    }
	
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>