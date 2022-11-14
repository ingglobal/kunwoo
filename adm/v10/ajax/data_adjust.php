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

// 합산 자료 업데이트
if ($aj == "set") {

    if($dta_idx) {

        $db_table = 'data_'.$g5['setting']['set_json_file_'.$dta_group]; // data_error, data_run..
        $dta = get_table_meta($db_table.'_sum','dta_idx',$dta_idx);
        // print_r2($dta);
        $dta['st_dt'] = strtotime($dta['dta_date'].' 00:00:00');
        $dta['en_dt'] = strtotime($dta['dta_date'].' 23:59:59');

        if($dta_group=='err' || $dta_group=='run') {
    
            if($dta_group=='err') {
                $sql_more = "   AND dta_code = '".$dta['dta_code']."' ";
                $sel_select = ' COUNT(dta_idx) AS dta_value_sum ';
            }
            else {
                $sql_more = "   AND dta_mmi_no = '".$dta['dta_mmi_no']."' ";
                $sel_select = ' SUM(dta_value) AS dta_value_sum ';
            }
            
            $sql1 = "SELECT ".$sel_select."
                    FROM ".$g5[$db_table.'_table']."
                    WHERE dta_status = 0
                        AND dta_dt >= '".$dta['st_dt']."'
                        AND dta_dt <= '".$dta['en_dt']."'
                        AND mms_idx = '".$dta['mms_idx']."'
                        AND dta_shf_no = '".$dta['dta_shf_no']."'
                        AND dta_group = '".$dta['dta_group']."'
                        {$sql_more}
            ";
            // echo $sql1;
            $sum1 = sql_fetch($sql1,1);
            // print_r2($sum1);
    
            $sql = " UPDATE {$g5[$db_table.'_sum_table']} SET
                        dta_value = '".$sum1['dta_value_sum']."'
                    WHERE dta_idx = '".$dta_idx."'
            ";
            sql_query($sql,1);
        }
        else if($dta_group=='product') {

            $sql1 = "SELECT SUM(dta_value) AS dta_value_sum
                    FROM g5_1_data_output_".$dta['mms_idx']."
                    WHERE dta_dt >= '".$dta['st_dt']."'
                        AND dta_dt <= '".$dta['en_dt']."'
                        AND dta_shf_no = '".$dta['dta_shf_no']."'
                        AND dta_mmi_no = '".$dta['dta_mmi_no']."'
                        AND dta_defect = '".$dta['dta_defect']."'
                        AND dta_defect_type = '".$dta['dta_defect_type']."'
            ";
            // echo $sql1.'<br>';
            $sum1 = sql_fetch($sql1,1);
            // print_r2($sum1);
            
            $sql = " UPDATE {$g5[$db_table.'_sum_table']} SET
                        dta_value = '".$sum1['dta_value_sum']."'
                    WHERE dta_idx = '".$dta_idx."'
            ";
            sql_query($sql,1);

        }
        else if($dta_group=='mea') {

            $sql1 = "SELECT SUM(dta_value) AS dta_value_sum
                        , MAX(dta_value) AS dta_value_max
                        , MIN(dta_value) AS dta_value_min
                        , ROUND(AVG(dta_value),2) AS dta_value_avg
                    FROM g5_1_data_measure_".$dta['mms_idx']."_".$dta['dta_type']."_".$dta['dta_no']."
                    WHERE dta_dt >= '".$dta['st_dt']."'
                        AND dta_dt <= '".$dta['en_dt']."'
            ";
            echo $sql1.'<br>';
            $sum1 = sql_fetch($sql1,1);
            // print_r2($sum1);
            
            $sql = " UPDATE {$g5[$db_table.'_sum_table']} SET
                        dta_sum = '".$sum1['dta_value_sum']."'
                        , dta_max = '".$sum1['dta_value_max']."'
                        , dta_min = '".$sum1['dta_value_min']."'
                        , dta_avg = '".$sum1['dta_value_avg']."'
                    WHERE dta_idx = '".$dta_idx."'
            ";
            sql_query($sql,1);

        }

        $response->result = true;
        $response->dta_idx = $dta_idx;
        $response->msg = "조정 완료";	
    }
    else {
        $response->msg = "dta_idx 정보가 없습니다.";	
    }

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}


$response->sql = $sql;

echo json_encode($response);
exit;
?>