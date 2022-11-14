<?php
// MMS 관련 정보
// token, mms_idx
// localhost/icmms/device/json/mms.php?token=1099de5drf09&mms_idx=1
// http://bogwang.epcs.co.kr/device/json/mms.php?token=1099de5drf09&mms_idx=1
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
$list = array();
$list['result']=false;
$true_false_arr = array(0,1);
$mms_run0_arr = array('',0,1);

if(!$_REQUEST['token']){
//	$list = array('');
    $list['msg']='token error!';
}
else if($_REQUEST['mms_idx']){

    // 토큰 비교
    if(!check_token1($_REQUEST['token'])) {
        $list = array("code"=>499,"message"=>"token error");
        echo json_encode( array($list) );
		exit;
    }
    
    // 기본 설정
    $mms_idx = ($_REQUEST['mms_idx']) ?: 1;
//    echo $mms_idx;
    // print_r2($g5['mms'][$mms_idx]);
	for($i=0;$i<sizeof($g5['mms'][$mms_idx]['shift']);$i++){
        $row = $g5['mms'][$mms_idx]['shift'][$i];
        // print_r2($row);
        // 나랑 관련 있는 배열만 추출
        if(G5_TIME_YMDHIS >= $row['shf_start_dt'] && G5_TIME_YMDHIS <= $row['shf_end_dt']) {
            // print_r2($row);
            for($j=1;$j<=4;$j++) {
                $row['range'][$j] = $row['shf_range_'.$j];
                $row['target'][$j] = $row['shf_target_'.$j];
                // 교대 시작~종료 시간 분리 배열
                $row['shift'][$j] = explode("~",$row['range'][$j]);
                // echo $j.'교대: '.$row['shift'][$j][0].' ~ ';             // ------------------
                // echo $row['shift'][$j][1].'<br>';                       // ------------------

                // next day included?
                if($row['shift'][$j][1]>'24:00:00') {
                    $t1 = sprintf("%02d",substr($row['shift'][$j][1],0,2)-24); // 24시간을 뺀 시간
                    $t2 = $t1.substr($row['shift'][$j][1],2);// 종료시간 재설정
                    $mms_date2 = date("Y-m-d",G5_SERVER_TIME).' '.$t2;
                }

                // target output sum
                $mms_target += $row['target'][$j];
            }
        }
    }
    // print_r2($row['target']);
    // echo $mms_target.'<br>';
    // echo $mms_date2.'<br>';

    // 일생산, 달성율 depends on setting (shift or date)
    // 장비이상, 예지알람 does not depend on setting.
    $mms_date = date("Y-m-d", G5_SERVER_TIME);
    $mms_date1 = (G5_TIME_YMDHIS <= $mms_date2) ? date("Y-m-d", G5_SERVER_TIME-86400) : $mms_date;

    // 생산기종
    $item = sql_fetch("SELECT dta_mmi_no
                        FROM g5_1_data_output_".$mms_idx."
                        ORDER BY dta_idx DESC
                        LIMIT 1
    ");
    $list['item_no'] = $item['dta_mmi_no'];

    // 일 생산 갯수
    $sql = "SELECT SUM(itm_count) AS dta_sum
                , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ok_array'])."') THEN itm_count ELSE 0 END ) AS dta_sum_success
                , SUM( CASE WHEN itm_status IN ('".implode("','",$g5['set_itm_status_ng_array'])."') THEN itm_count ELSE 0 END ) AS dta_sum_defect
            FROM {$g5['item_sum_table']}
            WHERE mms_idx = '".$mms_idx."'
                AND itm_date = '".$mms_date1."'
    ";
    // echo $sql.'<br>';
    $output = sql_fetch($sql,1);
    $list['output_count'] = $output['dta_sum'];
    $list['output_count_success'] = $output['dta_sum_success'];
    $list['output_count_defect'] = $output['dta_sum_defect'];
    
    // 달성율
    $list['output_rate'] = ($mms_target)? round($list['output_count']/$mms_target*100) : '';
    
    // 장비이상
    $error = sql_fetch("   SELECT SUM(dta_value) AS dta_sum
                            FROM {$g5['data_error_sum_table']}
                            WHERE mms_idx = '".$mms_idx."'
                                AND dta_group = 'err'
                                AND dta_date = '".$mms_date."'
    ");
    $list['error_count'] = $error['dta_sum'];
    
    // 예지알람
    $alarm = sql_fetch("   SELECT SUM(dta_value) AS dta_sum
                            FROM {$g5['data_error_sum_table']}
                            WHERE mms_idx = '".$mms_idx."'
                                AND dta_group = 'pre'
                                AND dta_date = '".$mms_date."'
    ");
    $list['alarm_count'] = $alarm['dta_sum'];
    
    // 가동상태 추출
    // 0~5분 안에 데이터
    $run1 = sql_fetch("SELECT mms_status
            FROM {$g5['data_run_real_table']} 
            WHERE dta_dt >= date_add(now(), INTERVAL -300 SECOND)
                AND mms_idx = '".$mms_idx."'
            ORDER BY dta_idx DESC
            LIMIT 1
    ");
    $list['mms_status'] = $run1['mms_status'];

    // Run time(Hour) total today.
    $runt = sql_fetch("   SELECT SUM(dta_value) AS dta_sum
                            FROM {$g5['data_run_sum_table']}
                            WHERE mms_idx = '".$mms_idx."'
                                AND dta_group = 'run'
                                AND dta_date = '".$mms_date."'
    ");
    $list['run_time_total'] = $runt['dta_sum'];
    $list['run_time_hour'] = round($runt['dta_sum']/3600,2);

    
    
    $list['result']=true;
    $list['msg']='설비 정보 호출 성공!';

}
else {
    $list['msg']='설비 정보가 존재하지 않습니다.';
}

echo json_encode( $list );
?>