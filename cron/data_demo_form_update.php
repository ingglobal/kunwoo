#!/opt/php/bin/php -q
<?php
include_once('/home/icmms/demo/common.php');


// How many seconds far from current month-01(first day)
$date20 = date("Y-m-01 00:00:00", G5_SERVER_TIME);
$time20 = strtotime($date20);   // current month start timestamp
$time21 = G5_SERVER_TIME;       // current timestamp
$time2_diff = $time21-$time20;
// echo date("Y-m-d H:i:s",$time21).'<br>';

// Compare to 2020-07 data
$time10 = strtotime("2020-07-01 00:00:00");
$time11 = $time10+$time2_diff;
// echo date("Y-m-d H:i:s",$time11).'<br>';

// 0=알수없음,전원OFF / 1=수동 / 2=자동 / 3=이상
$mms_status_array = array(0,1,2,2,2,2,2,2,2,3);



// error data process. ------------------------------------------------------------------
// 1. get the last error data of this month.
$sql = "SELECT dta_idx, dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_date
        FROM {$g5['data_error_table']} ORDER BY dta_idx DESC LIMIT 1
";
// echo $sql.'<br>';
$row1 = sql_fetch($sql,1);
$cnt1 = $row1['dta_count'];
// print_r2($row1);

// 2. get the second difference from this month's first timestamp to the last timestamp.
$time_diff = $row1['dta_dt']-$time20;
// echo sectohis($time_diff).'<br>';

// 3. Copy data from 2020-07 as many as the amount which differ from from this month's first timestamp to the last timestamp.
$time30 = $time10 + $time_diff; // from time
$time31 = $time10 + $time2_diff;    // to time

$g5_table_name = $g5['data_error_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));

$sql = "SELECT *
        FROM {$g5['data_error_table']}
        WHERE dta_dt >= ".$time30." AND dta_dt <= ".$time31."
";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r2($row);
    // echo $row['dta_dt'].'<br>';

    // input query 1st set
    $skips = array($pre.'_idx',$pre.'_dt',$pre.'_reg_dt',$pre.'_update_dt');
    for($i=0;$i<sizeof($fields);$i++) {
        if(in_array($fields[$i],$skips)) {continue;}
        $row['sql'][] = " ".$fields[$i]." = '".$row[$fields[$i]]."' ";
    }

    // new setting variables.
    // get timestamp from stored data.
    $row['new_dta_dt'] = $time20 + ($row['dta_dt']-$time10);
    $row['sql'][] = " dta_dt = '".$row['new_dta_dt']."' ";

    // final query.
    $row['sql_common'] = (is_array($row['sql'])) ? implode(",",$row['sql']) : '';

    $sql = " INSERT INTO {$g5['data_error_table']} SET 
                {$row['sql_common']} 
                , ".$pre."_reg_dt = '".G5_SERVER_TIME."'
                , ".$pre."_update_dt = '".G5_SERVER_TIME."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // echo date("Y-m-d H:i:s",$row['dta_dt']).' > ';
    // echo date("Y-m-d H:i:s",$row['new_dta_dt']).'<br>';
    // echo '----------------<br>';
}
// 합계 데이터 입력
sql_query("TRUNCATE {$g5['data_error_sum_table']} ",1);
$sql = "INSERT INTO {$g5['data_error_sum_table']} (com_idx, imp_idx, mms_idx, shf_idx, cod_idx, dta_shf_no, dta_group, dta_code, dta_date, dta_value)
        SELECT com_idx, imp_idx, mms_idx, shf_idx, cod_idx, dta_shf_no, dta_group, dta_code
        , FROM_UNIXTIME(dta_dt,'%Y-%m-%d') AS dta_date
        , COUNT(dta_idx) AS dta_count_sum
        FROM {$g5['data_error_table']}
        WHERE dta_status = 0
        GROUP BY mms_idx, dta_shf_no, dta_group, dta_code, dta_date
        ORDER BY dta_date ASC 
";
sql_query($sql,1);
// sum count = 107865
// error row count = 435206
// error data process ends here. ----------------------------------------------------------------




// measure data process. ------------------------------------------------------------------
// 1. get the last measure data of this month.
$sql = "SELECT dta_idx, dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_date
        FROM {$g5['data_measure_table']} ORDER BY dta_idx DESC LIMIT 1
";
// echo $sql.'<br>';
$row1 = sql_fetch($sql,1);
$cnt1 = $row1['dta_count'];
// print_r2($row1);

// 2. get the second difference from this month's first timestamp to the last timestamp.
$time_diff = $row1['dta_dt']-$time20;
// echo sectohis($time_diff).'<br>';

// 3. Copy data from 2020-07 as many as the amount which differ from from this month's first timestamp to the last timestamp.
$time30 = $time10 + $time_diff; // from time
$time31 = $time10 + $time2_diff;    // to time

$g5_table_name = $g5['data_measure_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));

$sql = "SELECT *
        FROM {$g5['data_measure_table']}
        WHERE dta_dt >= ".$time30." AND dta_dt <= ".$time31."
";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r2($row);
    // echo $row['dta_dt'].'<br>';

    // input query 1st set
    $skips = array($pre.'_idx',$pre.'_dt',$pre.'_reg_dt',$pre.'_update_dt');
    for($i=0;$i<sizeof($fields);$i++) {
        if(in_array($fields[$i],$skips)) {continue;}
        $row['sql'][] = " ".$fields[$i]." = '".$row[$fields[$i]]."' ";
    }

    // new setting variables.
    // get timestamp from stored data.
    $row['new_dta_dt'] = $time20 + ($row['dta_dt']-$time10);
    $row['sql'][] = " dta_dt = '".$row['new_dta_dt']."' ";

    // final query.
    $row['sql_common'] = (is_array($row['sql'])) ? implode(",",$row['sql']) : '';

    $sql = " INSERT INTO {$g5['data_measure_table']} SET 
                {$row['sql_common']} 
                , ".$pre."_reg_dt = '".G5_SERVER_TIME."'
                , ".$pre."_update_dt = '".G5_SERVER_TIME."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // echo date("Y-m-d H:i:s",$row['dta_dt']).' > ';
    // echo date("Y-m-d H:i:s",$row['new_dta_dt']).'<br>';
    // echo '----------------<br>';
}
// 합계 데이터 입력
sql_query("TRUNCATE g5_1_data_measure_sum",1);
$sql = "INSERT INTO g5_1_data_measure_sum (com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_type, dta_no, dta_date, dta_sum, dta_max, dta_min, dta_avg)
		SELECT com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_type, dta_no
		, FROM_UNIXTIME(dta_dt,'%Y-%m-%d') AS dta_date
		, SUM(dta_value) AS dta_value_sum
		, MAX(dta_value) AS dta_value_max
		, MIN(dta_value) AS dta_value_min
		, ROUND(AVG(dta_value),2) AS dta_value_avg
		FROM g5_1_data_measure 
		WHERE dta_status = 0
		GROUP BY mms_idx, dta_shf_no, dta_mmi_no, dta_type, dta_no, dta_date
		ORDER BY dta_date ASC, mms_idx, dta_shf_no, dta_mmi_no, dta_type, dta_no
";
sql_query($sql,1);
// sum count = 107865
// measure row count = 435206
// measure data process ends here. ----------------------------------------------------------------



// output data process. ------------------------------------------------------------------
// 1. get the last output data of this month.
$sql = "SELECT dta_idx, dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_date
        FROM {$g5['data_output_table']} ORDER BY dta_idx DESC LIMIT 1
";
// echo $sql.'<br>';
$row1 = sql_fetch($sql,1);
$cnt1 = $row1['dta_count'];
// print_r2($row1);

// 2. get the second difference from this month's first timestamp to the last timestamp.
$time_diff = $row1['dta_dt']-$time20;
// echo sectohis($time_diff).'<br>';

// 3. Copy data from 2020-07 as many as the amount which differ from from this month's first timestamp to the last timestamp.
$time30 = $time10 + $time_diff; // from time
$time31 = $time10 + $time2_diff;    // to time

$g5_table_name = $g5['data_output_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));

$sql = "SELECT *
        FROM {$g5['data_output_table']}
        WHERE dta_dt >= ".$time30." AND dta_dt <= ".$time31."
";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r2($row);
    // echo $row['dta_dt'].'<br>';

    // input query 1st set
    $skips = array($pre.'_idx',$pre.'_dt',$pre.'_reg_dt',$pre.'_update_dt');
    for($i=0;$i<sizeof($fields);$i++) {
        if(in_array($fields[$i],$skips)) {continue;}
        $row['sql'][] = " ".$fields[$i]." = '".$row[$fields[$i]]."' ";
    }

    // new setting variables.
    // get timestamp from stored data.
    $row['new_dta_dt'] = $time20 + ($row['dta_dt']-$time10);
    $row['sql'][] = " dta_dt = '".$row['new_dta_dt']."' ";

    // final query.
    $row['sql_common'] = (is_array($row['sql'])) ? implode(",",$row['sql']) : '';

    $sql = " INSERT INTO {$g5['data_output_table']} SET 
                {$row['sql_common']} 
                , ".$pre."_reg_dt = '".G5_SERVER_TIME."'
                , ".$pre."_update_dt = '".G5_SERVER_TIME."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // echo date("Y-m-d H:i:s",$row['dta_dt']).' > ';
    // echo date("Y-m-d H:i:s",$row['new_dta_dt']).'<br>';
    // echo '----------------<br>';
}
// 합계 데이터 입력
sql_query("TRUNCATE g5_1_data_output_sum",1);
$sql = "INSERT INTO g5_1_data_output_sum (com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_defect, dta_defect_type, dta_message, dta_date, dta_value)
        SELECT com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_defect, dta_defect_type, dta_message
        , FROM_UNIXTIME(dta_dt,'%Y-%m-%d') AS dta_date
        , SUM(dta_value) AS dta_value_sum
        FROM g5_1_data_output 
        WHERE dta_status = 0
        GROUP BY mms_idx, dta_shf_no, dta_mmi_no, dta_defect, dta_defect_type, FROM_UNIXTIME(dta_dt,'%Y-%m-%d')
        ORDER BY dta_date ASC, mms_idx, dta_shf_no, dta_mmi_no, dta_defect, dta_defect_type
";
sql_query($sql,1);
// sum count = 107865
// output row count = 435206
// output data process ends here. ----------------------------------------------------------------




// run data process. ------------------------------------------------------------------
// 1. get the last run data of this month.
$sql = "SELECT dta_idx, dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_date
        FROM {$g5['data_run_table']} ORDER BY dta_idx DESC LIMIT 1
";
// echo $sql.'<br>';
$row1 = sql_fetch($sql,1);
$cnt1 = $row1['dta_count'];
// print_r2($row1);

// 2. get the second difference from this month's first timestamp to the last timestamp.
$time_diff = $row1['dta_dt']-$time20;
// echo sectohis($time_diff).'<br>';

// 3. Copy data from 2020-07 as many as the amount which differ from from this month's first timestamp to the last timestamp.
$time30 = $time10 + $time_diff; // from time
$time31 = $time10 + $time2_diff;    // to time

$g5_table_name = $g5['data_run_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));

$sql = "SELECT *
        FROM {$g5['data_run_table']}
        WHERE dta_dt >= ".$time30." AND dta_dt <= ".$time31."
";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r2($row);
    // echo $row['dta_dt'].'<br>';

    // input query 1st set
    $skips = array($pre.'_idx',$pre.'_dt',$pre.'_reg_dt',$pre.'_update_dt');
    for($i=0;$i<sizeof($fields);$i++) {
        if(in_array($fields[$i],$skips)) {continue;}
        $row['sql'][] = " ".$fields[$i]." = '".$row[$fields[$i]]."' ";
    }

    // new setting variables.
    // get timestamp from stored data.
    $row['new_dta_dt'] = $time20 + ($row['dta_dt']-$time10);
    $row['sql'][] = " dta_dt = '".$row['new_dta_dt']."' ";

    // final query.
    $row['sql_common'] = (is_array($row['sql'])) ? implode(",",$row['sql']) : '';

    $sql = " INSERT INTO {$g5['data_run_table']} SET 
                {$row['sql_common']} 
                , ".$pre."_reg_dt = '".G5_SERVER_TIME."'
                , ".$pre."_update_dt = '".G5_SERVER_TIME."'
    ";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // 가동상태 입력 (설비상태 추출)
    $sql = " INSERT INTO {$g5['data_run_real_table']} SET
                mms_idx = '".$row['mms_idx']."'
                , mms_status = '".$mms_status_array[rand(0,sizeof($mms_status_array)-1)]."'
                , dta_dt = '".date("Y-m-d H:i:s",$row['new_dta_dt'])."'
    ";
    $result = sql_query($sql,1);


    // echo date("Y-m-d H:i:s",$row['dta_dt']).' > ';
    // echo date("Y-m-d H:i:s",$row['new_dta_dt']).'<br>';
    // echo '----------------<br>';
}
// 합계 데이터 입력
sql_query("TRUNCATE g5_1_data_run_sum",1);
$sql = "INSERT INTO g5_1_data_run_sum (com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_date, dta_value)
		SELECT com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group
		, FROM_UNIXTIME(dta_dt,'%Y-%m-%d') AS dta_date
		, SUM(dta_value) AS dta_value_sum
		FROM g5_1_data_run 
		WHERE dta_status = 0
		GROUP BY mms_idx, dta_shf_no, dta_mmi_no, dta_group, FROM_UNIXTIME(dta_dt,'%Y-%m-%d')
		ORDER BY dta_date ASC
";
sql_query($sql,1);
// sum count = 107865
// run row count = 435206
// run data process ends here. ----------------------------------------------------------------






exit;
// $fname = preg_replace("/_form_update/","",$g5['file_name']); // _form_update를 제외한 파일명
// alert('데모 데이터를 생성하였습니다.','./'.$fname.'_form.php');
?>