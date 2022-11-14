<?php
// 생산 목표
// if more than dialy, go to the file: output.target.sum.php
// token, mms_idx, dta_group, shf_no, dta_mmi_no, dta_item(minute), dta_unit(10)
// st_date, st_time, en_date, en_time
// http://bogwang.epcs.co.kr/adm/v10/ajax/output.target.php?token=1099de5drf09&mms_idx=1&st_date=2020-04-01&st_time=10:00:00&en_date=2020-04-25&en_time=23:59:59
// http://bogwang.epcs.co.kr/adm/v10/ajax/output.target.php?token=1099de5drf09&mms_idx=1&dta_item=minute&dta_unit=30&st_date=2020-04-01&st_time=10:00:00&en_date=2020-04-25&en_time=23:59:59
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

if(!$_REQUEST['token']){
//	$list = array('');
}
else if($_REQUEST['mms_idx']){

    // 토큰 비교
    $to[] = substr($_REQUEST['token'],0,2);
    $to[] = substr($_REQUEST['token'],2,2);
    $to[] = substr($_REQUEST['token'],-2);
    $to[] = substr((string)((int)$to[0]+(int)$to[1]),-2);
    //print_r2($to);
    if($to[2]!=$to[3]) {
        $list = array("code"=>499,"message"=>"token error");
        echo json_encode( array($list) );
		exit;
    }

    // 시작일과 종료일자가 항상 있어야 함(생산을 따라 표현해야 하므로.)
    if(!$st_date||!$en_date) {
        $list = array("code"=>498,"message"=>"no st_date or no en_date");
        echo json_encode( array($list) );
		exit;
    }

    // 기본 설정
    $dta_group = ($_REQUEST['dta_group']) ?: 'product';
    $st_time = ($_REQUEST['st_time']) ?: '00:00:00';
    $en_time = ($_REQUEST['en_time']) ?: '23:59:59';

    // 그룹 선택에 따른 초기 ser1, ser2 설정 (user.07.intra.default.php 참조)
    $ser1 = ($_REQUEST['dta_item']) ? $_REQUEST['dta_item'] : $g5['set_graph_'.$dta_group]['default0'];
    $ser2 = ($_REQUEST['dta_unit']) ? $_REQUEST['dta_unit'] : $g5['set_graph_'.$dta_group]['default1'];
    $ser2 = ($ser2) ?: 1;   // ser2가 환경설정값에도 없으면 1로 디폴트 설정
    if($ser1!='minute' && $ser1!='second') // 분,초가 아니면 무조건 1
        $ser2 = 1;
    // echo $ser2.'<br>';
    
    $where = array();
    $where[] = " mms_idx = '".$mms_idx."' AND shf_status = 'ok' ";   // 디폴트 검색조건
    
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);


    // 최종 날짜 조건
    $start = strtotime($st_date.' '.$st_time);
    $end = strtotime($en_date.' '.$en_time);
    // echo $st_date.' '.$st_time.'~'.$en_date.' '.$en_time.'<br>';
    // echo $start.'~'.$end.'<br>';

    // 끝자리 단위값 조정
    $byunit = $seconds[$ser1][0]*$ser2;
    // echo $byunit.'초 단위<br>';
    $ix1 = floor($start/$byunit);   // 시작값은 내림으로 (애매한 소수점 처리를 위해)
    $ix2 = ceil($end/$byunit);  // 종료값을 올림으로
    $idx1 = $ix1*$byunit; // 다시 단위값을 곱해서 timestamp로 변환
    $idx2 = $ix2*$byunit;
    $dt1 = date("Y-m-d H:i:s",$idx1); // 끝자리 처리한 후 시작일시
    $dt2 = date("Y-m-d H:i:s",$idx2); // 종료일시
    // echo '시작: '.$idx1.' / '.date("Y-m-d H:i:s",$idx1).'<br>';   //-------------------------------------
    // echo '끝: '.$idx2.' / '.date("Y-m-d H:i:s",$idx2).'<br>';    //-------------------------------------

	// 생산목표 추출
    // $sql = "SELECT mms_idx, shf_idx, shf_range_1, shf_range_2, shf_range_3
    //             , shf_target_1, shf_target_2, shf_target_3
    //             -- , shf_start_dt
    //             , GREATEST('".$dt1."', shf_start_dt ) AS shf_start_dt
    //             -- , shf_end_dt
    //             , LEAST('".$dt2."', shf_end_dt ) AS shf_end_dt
    //         FROM {$g5['shift_table']} {$sql_search}
    //             AND shf_end_dt >= '".$dt1."'
    //             AND shf_start_dt <= '".$dt2."'
    //         ORDER BY shf_start_dt
    // ";
    $sql = "SELECT mms_idx, shf_idx, shf_range_1, shf_range_2, shf_range_3
                , shf_target_1, shf_target_2, shf_target_3
                , GREATEST('".$dt1."', shf_start_dt ) AS shf_start_dt
                , LEAST('".$dt2."', shf_end_dt ) AS shf_end_dt
            FROM {$g5['shift_table']} {$sql_search}
                AND shf_end_dt >= '".$dt1."'
                AND shf_start_dt <= '".$dt2."'
            ORDER BY shf_start_dt
    ";
//    echo $sql.'<br>';
//    exit;
	$rs = sql_query($sql,1);
	for($i=0;$row=sql_fetch_array($rs);$i++){
        // print_r2($row);
        for($j=1;$j<=4;$j++) {
            $row['range'][$j] = $row['shf_range_'.$j];
            $row['target'][$j] = $row['shf_target_'.$j];
            // 교대 시작~종료 시간 분리 배열
            $row['shift'][$j] = explode("~",$row['range'][$j]);
            // echo $j.'교대: '.$row['shift'][$j][0].' ~ ';             // ------------------
            // echo $row['shift'][$j][1].'<br>';                       // ------------------
        }
        // echo '---- 교대시간 추출해 둔 후 하단에서 배열생성 ----------<br>';  // ------------------
        // print_r2($row);
        // print_r2($row['shift']);
        // echo $row['shf_idx'].'. '.$row['shf_start_dt'].' ~ '.$row['shf_end_dt'].'<br>'; // 교대 시간 범위
        $ts1 = strtotime($row['shf_start_dt']);    // 시작 timestamp
        // 시작지점 재설정 (단위 간격이 있으므로 아무데서나 시작하면 해당배열이 존재하지 않아서 표현이 안 되요.)
        $ts1 = ceil($ts1/$byunit);   // 시작값은 내림으로 (애매한 소수점 처리를 위해)
        $ts1 = $ts1*$byunit; // 다시 단위값을 곱해서 timestamp로 변환
        $ts2 = strtotime($row['shf_end_dt']);    // 종료 timestamp
        // 날짜범위를 for 돌면서 해당시간을 찾아서 배열변수 생성
        $cnt2 = 0;
        for($k=$ts1;$k<=$ts2;$k+=$byunit) {
            $cnt2++;
            // echo $cnt2.'. '.date("Y-m-d H:i:s",$k).'<br>';                              // ------------------
            // echo $cnt2.'. '.preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$k)).'<br>';   // ------------------
            $val1 = preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$k)); // 날짜중에서 숫자만 추출하여 배열키값으로!
            $time1 = date("H:i:s",$k); // 날짜중에서 시간만 추출하여 교대값 찾기
            // $k값의 시간값 비교 (몇 교대인지 파악해서 목표값 할당)
            // 단, 교대종료시간이 24시가 넘는 익일을 포함한 교대수는 별도 계산해야 함
            for($x=1;$x<5;$x++) {
                // 교대 시작 시간이 없으면 통과
                if(!$row['shift'][$x][0])
                    break;
                if($time1 >= $row['shift'][$x][0] && $time1 <= $row['shift'][$x][1]) { // 해당 교대 범위 안에 있으면
                    // echo $x.'교대'.$row['shift'][$x][1].'<br>';
                    $dta1[$val1]['y'] = $row['shf_target_'.$x];  // 해당 교대목표값 할당
                    $shift1[$val1]['shift_no'] = $x;  // 교대값 할당
                }
                // 1교대 시작 시간 이전이면서 24시간이 넘는 익일 종료시간인 경우
                if($time1<$row['shift'][1][0] && $row['shift'][$x][1]>'24:00:00') {
                    $t1 = sprintf("%02d",substr($row['shift'][$x][1],0,2)-24); // 24시간을 뺀 시간
                    $t2 = $t1.substr($row['shift'][$x][1],2);// 종료시간 재설정
                    if($time1 >= '00:00:00' && $time1 <= $t2) { // 해당 교대 범위 안에 있으면
                        // echo $x.'교대'.$row['shift'][$x][1].'<br>';
                        $dta1[$val1]['y'] = $row['shf_target_'.$x];  // 해당 교대목표값 할당
                        $shift1[$val1]['shift_no'] = $x;  // 교대값 할당
                    }
                    // echo $x.'넘는다............'.$t2.'....<br>';
                }
            }
        }
        // echo '<br>--------------<br>';
    }
    // print_r2($dta1);
    // print_r2($shift1);


    // 실제 좌표값 생성
    $cnt=0;
	$list = array();
    for($i=$idx1;$i<=$idx2;$i+=$byunit) {
        // echo $cnt.'. '.date("Y-m-d H:i:s",$i).'<br>';
        // echo $cnt.'. '.preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$i)).'<br>';
        $val1 = preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$i)); // 날짜중에서 숫자만 추출
        $row['json']['x'] = strtotime($val1)*1000;
        $row['json']['y'] = (int)$dta1[$val1]['y'] ?: null; // 숫자표현에 아주 예민하네!!
        $row['json']['yraw'] = ($row['json']['y']) ?: null;
        $row['json']['yamp'] = 1;
        $row['json']['ymove'] = 0;
        $row['json']['dta_date'] = date("Y-m-d H:i:s",$i);

        $list[$cnt] = $row['json'];
        $cnt++;
    }

}

echo json_encode( $list );
// echo $_GET['callback']. '('. json_encode($list) . ')';
?>