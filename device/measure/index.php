<?php
// 크롬 요소검사 열고 확인하면 되겠습니다. 
// print_r2 안 쓰고 print_r로 확인하는 게 좋습니다.
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

// print_r($_REQUEST);exit;
//echo $_REQUEST['shf_type'][0];
$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고
// print_r($getData);
// exit;

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
        $arr['st_time'] = strtotime($arr['dta_date1']." 00:00:00"); // 해당 날짜의 시작
        $arr['en_time'] = strtotime($arr['dta_date1']." 23:59:59"); // 해당 날짜의 끝
        // echo $arr['dta_date1'].PHP_EOL;
        // echo $arr['st_time'].'~'.$arr['en_time'].PHP_EOL;
        $table_name = 'g5_1_data_measure_'.$arr['mms_idx'].'_'.$arr['dta_type'].'_'.$arr['dta_no'];


        // checkout db table exists and create if not exists.
        $sql = "SELECT EXISTS (
                    SELECT 1 FROM Information_schema.tables
                    WHERE TABLE_SCHEMA = '".G5_MYSQL_DB."'
                    AND TABLE_NAME = '".$table_name."'
                ) AS flag
        ";
        $tb1 = sql_fetch($sql,1);
        if(!$tb1['flag']) {
            $file = file('./sql_write.sql');
            $file = get_db_create_replace($file);
            $sql = implode("\n", $file);
            $source = array('/__TABLE_NAME__/', '/;/');
            $target = array($table_name, '');
            $sql = preg_replace($source, $target, $sql);
            sql_query($sql, FALSE);
        }

        // 중복체크
        $sql_dta = "   SELECT dta_idx FROM {$table_name}
                        WHERE dta_dt = '".$arr['dta_dt']."'
        ";
        //echo $sql_dta.'<br>';
		$dta = sql_fetch($sql_dta,1);
        
		// 정보 업데이트
		if($dta['dta_idx']) {
			
			$sql = "UPDATE {$table_name} SET 
                        dta_value = '".$arr['dta_value']."'
						, dta_update_dt = '".G5_SERVER_TIME."'
					WHERE dta_idx = '".$dta['dta_idx']."'";
			sql_query($sql,1);
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Updated OK!";

		}
        // 정보 입력
        else{

			$sql = "INSERT INTO {$table_name} SET 
						dta_dt = '".$arr['dta_dt']."'
                        , dta_value = '".$arr['dta_value']."'
						, dta_reg_dt = '".G5_SERVER_TIME."'
						, dta_update_dt = '".G5_SERVER_TIME."'
            ";
			sql_query($sql,1);
//            echo $sql.'<br>';
            $dta['dta_idx'] = sql_insert_id();
            $result_arr[$i]['code'] = 200;
            $result_arr[$i]['message'] = "Inserted OK!";
        
        }
        $result_arr[$i]['dta_idx'] = $dta['dta_idx'];   // 고유번호






        // 일간 sum 합계 입력
        $sum_common = " mms_idx = '".$arr['mms_idx']."'
                        AND dta_type = '".$arr['dta_type']."'
                        AND dta_no = '".$arr['dta_no']."'
        ";
        // 쿼리는 좌변을 가공하면 속도가 느리다.
        $sql = "SELECT ROUND(AVG(dta_value),2) AS dta_avg
                        , SUM(dta_value) AS dta_sum
                        , MAX(dta_value) AS dta_max
                        , MIN(dta_value) AS dta_min
                FROM {$table_name}
                WHERE dta_dt >= '".$arr['st_time']."' AND dta_dt <= '".$arr['en_time']."'
        ";
        // $sql = "SELECT ROUND(AVG(dta_value),2) AS dta_avg
        //                 , SUM(dta_value) AS dta_sum
        //                 , MAX(dta_value) AS dta_max
        //                 , MIN(dta_value) AS dta_min
        //         FROM {$table_name}
        //         WHERE FROM_UNIXTIME(dta_dt,'%Y-%m-%d') = '".$arr['dta_date1']."'
        // ";
        // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_value = 'sum: ".addslashes($sql)."' ");
        $sum1 = sql_fetch($sql,1); // 일 평균 데이터값 추출 

        // 있으면 업데이트, 없으면 생성
        $sql_sum = "   SELECT dta_idx FROM {$g5['data_measure_sum_table']} 
                        WHERE {$sum_common}
                            AND dta_date = '".$arr['dta_date1']."'
        ";
        //echo $sql_sum.'<br>';
		$sum = sql_fetch($sql_sum,1);
		// 정보 업데이트
		if($sum['dta_idx']) {
            $sql = "UPDATE {$g5['data_measure_sum_table']} SET
                        dta_avg = '".$sum1['dta_avg']."'
                        , dta_sum = '".$sum1['dta_sum']."'
                        , dta_max = '".$sum1['dta_max']."'
                        , dta_min = '".$sum1['dta_min']."'
                    WHERE {$sum_common}
                        AND dta_date = '".$arr['dta_date1']."'
            ";
            // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_value = 'up: ".addslashes($sql)."' ");
            $result = sql_query($sql);
        }
        else {
            $sql = " INSERT INTO {$g5['data_measure_sum_table']} SET
                        com_idx = '".$arr['com_idx']."'
                        , imp_idx = '".$arr['imp_idx']."'
                        , mms_idx = '".$arr['mms_idx']."'
                        , mmg_idx = '".$g5['mms'][$arr['mms_idx']]['mmg_idx']."'
                        , dta_shf_no = '".$arr['dta_shf_no']."'
                        , dta_mmi_no = '".$arr['dta_mmi_no']."'
                        , dta_group = '".$arr['dta_group']."'
                        , dta_type = '".$arr['dta_type']."'
                        , dta_no = '".$arr['dta_no']."'
                        , dta_date = '".$arr['dta_date1']."'
                        , dta_avg = '".$sum1['dta_avg']."'
                        , dta_sum = '".$sum1['dta_sum']."'
                        , dta_max = '".$sum1['dta_max']."'
                        , dta_min = '".$sum1['dta_min']."'
            ";
            // sql_query(" INSERT INTO {$g5['meta_table']} SET mta_value = 'in: ".addslashes($sql)."' ");
            $result = sql_query($sql);
        }
    
    }
	
}
else {
	$result_arr = array("code"=>599,"message"=>"error");
}

//exit;
//echo json_encode($arr);
echo json_encode( array('meta'=>$result_arr) );
?>