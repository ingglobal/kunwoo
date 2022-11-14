<?php
// 측정 자료: 그룹핑
// token, mms_idx, dta_group, shf_no, dta_type, dat_no, dta_item(minute), dta_unit(10)
// dta_value_type(count), graph_type(spline)
// st_date, st_time, en_date, en_time
// 디폴트(카운터그룹핑): http://bogwang.epcs.co.kr/device/json/error.php?token=1099de5drf09&mms_idx=1&dta_group=err&dta_value_type=count
// 조건검색: &dta_value_type=count&shf_no=1&dta_code=M0101&dta_item=minute&dta_unit=10&&st_date=2020-04-29&st_time=10:00:00&end_date=2020-04-30&end_time=23:59:59
// http://bogwang.epcs.co.kr/device/json/error.php?token=1099de5drf09&mms_idx=1&dta_group=err&dta_value_type=count&shf_no=1&dta_code=M0101&dta_item=minute&dta_unit=10&&st_date=2020-04-29&st_time=10:00:00&end_date=2020-04-30&end_time=23:59:59
// 조건을 바꾸어 보세요.
// http://bogwang.epcs.co.kr/device/json/error.php?token=1099de5drf09&mms_idx=2&dta_group=err&shf_no=1&dta_code=M0101&dta_item=minute&dta_unit=10&&st_date=2020-04-29&st_time=10:00:00&end_date=2020-04-30&end_time=23:59:59
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
    if(!check_token1($_REQUEST['token'])) {
        $list = array("code"=>499,"message"=>"token error");
        echo json_encode( array($list) );
		exit;
    }

    // 기본 설정
    $dta_group = ($_REQUEST['dta_group']) ?: 'mea';
    $dta_value_type = ($_REQUEST['dta_value_type']) ?: 'avg';
    $graph_type = ($_REQUEST['graph_type']) ?: 'spline';
    $table_name = 'g5_1_data_measure_'.$_REQUEST['mms_idx'].'_'.$_REQUEST['dta_type'].'_'.$_REQUEST['dta_no'];
    
    // 그룹 선택에 따른 초기 ser1, ser2 설정 (user.07.intra.default.php 참조)
    $ser1 = ($_REQUEST['dta_item']) ? $_REQUEST['dta_item'] : $g5['set_graph_'.$dta_group]['default0'];
    $ser2 = ($_REQUEST['dta_unit']) ? $_REQUEST['dta_unit'] : $g5['set_graph_'.$dta_group]['default1'];
    $ser2 = ($ser2) ?: 1;   // ser2가 환경설정값에도 없으면 1로 디폴트 설정
    if($ser1!='minute' && $ser1!='second') // 분,초가 아니면 무조건 1
        $ser2 = 1;
    // echo $ser2.'<br>';

    $where = array();
    // $where[] = " mms_idx = '".$mms_idx."' AND dta_group = '".$dta_group."' AND dta_status = 0 ";   // 디폴트 검색조건
    $where[] = " (1) ";   // 디폴트 검색조건
    
    // shf_no 조건
    // if($_REQUEST['shf_no']) 
    //     $where[] = " dta_shf_no = '".$_REQUEST['shf_no']."' ";
        
    // dta_type 조건
    // if(isset($_REQUEST['dta_type'])) 
    //     $where[] = " dta_type = '".$_REQUEST['dta_type']."' ";
        
    // dta_no 조건
    // if(isset($_REQUEST['dta_no'])) 
    //     $where[] = " dta_no = '".$_REQUEST['dta_no']."' ";
        
    // dta_mmi_no 기종 조건
    // if(isset($_REQUEST['dta_mmi_no']) && $_REQUEST['dta_mmi_no']!='')
    //     $where[] = " dta_mmi_no = '".$_REQUEST['dta_mmi_no']."' ";
        
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);


    //1. 처음로딩(종료일이 없는 경우) 해당 조건에 맞는 값이 존재하는 제일 마지막 날짜를 추출해서 종료일자로 설정해 둔다.
    if(!$en_date) {
        // $sql = "SELECT * FROM {$g5['data_measure_table']} {$sql_search}
        //         ORDER BY dta_dt DESC LIMIT 1 
        // ";
        $sql = "SELECT * FROM {$table_name} {$sql_search}
                ORDER BY dta_dt DESC LIMIT 1 
        ";
        // echo $sql.'<br>';
        $en1 = sql_fetch($sql,1);
        // print_r2($en1);
        $en_date = date("Y-m-d",$en1['dta_dt']);
        $en_time = date("H:i:s",$en1['dta_dt']);
        // echo $en_date.' '.$en_time.'<br>';
    }

    //2. 시작일, 종료일 둘 다 있으면
    //        시작일부터 600개가 종료 설정값을 넘어가면 자동으로 줄여버림
    //        안 넘어가면 설정한 값이 종료일자.
    if($st_date && $en_date) {
        $st_timestamp = strtotime($st_date.' '.$st_time);
        $en_timestamp = strtotime($en_date.' '.$en_time);
        $seconds[$ser1][1] = ($seconds[$ser1][1]) ?: $ser2;// 단위 선택값이 없으면 폼에서 선택된 값을 참조
        // 분(60초)*단위(60개)*600개(환경설정값) [분선택, 60개 선택인 경우]
        // 초(1초)*단위(10개)*600개 [초선택,10개 선택인 경우]
        // 달(2592000초)*단위(무조건 1개)*600개 [달선택,1개 선택인 경우], 이건 말이 좀 안 되지!
        $en_timestamp_max = $st_timestamp + ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['setting']['set_graph_max']);
        if($en_timestamp > $en_timestamp_max) {
            $en_date = date("Y-m-d",$en_timestamp_max);
            $en_time = date("H:i:s",$en_timestamp_max);
        }
    }

    //3. 종료일만 있으면
    //        종료일에서부터 검색항목별 설정값(daily,1,30 = 일별,1일단위,30일치 등..)을 계산한 후
    //        시작일자로 설정을 해 준다.
    if(!$st_date && $en_date) {
        $en_timestamp = strtotime($en_date.' '.$en_time);
        // echo $en_date.' '.$en_time.'<br>';
        $seconds[$ser1][1] = ($seconds[$ser1][1]) ?: $ser2;// 단위 선택값이 없으면 폼에서 선택된 값을 참조
        // $st_timestamp = $en_timestamp - ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['setting']['set_graph_max']); // 일별인 경우 -600일이 너무 커서 변경
        // echo $g5['set_graph_'.$dta_group]['default2'].'<br>';
        // echo $seconds[$ser1][0].'<br>';  // 초로 통일한 값
        $st_timestamp = $en_timestamp - ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['set_graph_'.$dta_group]['default2']);
        $st_date = date("Y-m-d",$st_timestamp);
        $st_time = date("H:i:s",$st_timestamp);
        // echo $st_date.' '.$st_time.'<br>';
    }


    // 최종 날짜 조건
    $start = strtotime($st_date.' '.$st_time);
    $end = strtotime($en_date.' '.$en_time);
    //echo $st_date.' '.$st_time.'~'.$en_date.' '.$en_time.'<br>';
    //echo $start.'~'.$end.'<br>';

    // 끝자리 단위값 조정
    $byunit = $seconds[$ser1][0]*$ser2;
    //echo $byunit.'초 단위<br>';
    $ix1 = floor($start/$byunit);   // 시작값은 내림으로 (애매한 소수점 처리를 위해)
    $ix2 = ceil($end/$byunit);  // 종료값을 올림으로
    $idx1 = $ix1*$byunit; // 다시 단위값을 곱해서 timestamp로 변환
    $idx2 = $ix2*$byunit;
    $dt1 = date("Y-m-d H:i:s",$idx1); // 끝자리 처리한 후 시작일시
    $dt2 = date("Y-m-d H:i:s",$idx2); // 종료일시
    // echo '시작: '.$idx1.' / '.date("Y-m-d H:i:s",$idx1).'<br>';   //-------------------------------------
    // echo '끝: '.$idx2.' / '.date("Y-m-d H:i:s",$idx2).'<br>';    //-------------------------------------

	// 측정 추출
    // $sql = "SELECT SQL_NO_CACHE dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
    // $sql = "SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
    //                 , dta_shf_no, dta_mmi_no, dta_type, dta_no
    //                 , ".strtoupper($dta_value_type)."(dta_value) AS dta_value
    //                 , (dta_dt DIV ".$byunit.") AS dta_divided
    //                 , (dta_dt DIV ".$byunit.")*".$byunit." AS dta_made_timestamp
    //                 , FROM_UNIXTIME((dta_dt DIV ".$byunit.")*".$byunit.",'%Y-%m-%d %H:%i:%s') AS dta_made_dt
    //         FROM {$g5['data_measure_table']} {$sql_search}
    //             AND dta_dt >= '".$idx1."'
    //             AND dta_dt <= '".$idx2."'
    //             GROUP BY dta_dt DIV ".$byunit."
    //         ORDER BY dta_dt ASC
    //         LIMIT 600
    // ";
    $sql = "SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
                    , ".strtoupper($dta_value_type)."(dta_value) AS dta_value
                    , (dta_dt DIV ".$byunit.") AS dta_divided
                    , (dta_dt DIV ".$byunit.")*".$byunit." AS dta_made_timestamp
                    , FROM_UNIXTIME((dta_dt DIV ".$byunit.")*".$byunit.",'%Y-%m-%d %H:%i:%s') AS dta_made_dt
            FROM {$table_name} {$sql_search}
                AND dta_dt >= '".$idx1."'
                AND dta_dt <= '".$idx2."'
                GROUP BY dta_dt DIV ".$byunit."
            ORDER BY dta_dt ASC
            LIMIT 600
    ";
//    echo $sql.'<br>';
//    exit;
	$rs = sql_query($sql,1);
	for($i=0;$row=sql_fetch_array($rs);$i++){
        $row['no'] = $i;
        $val1 = preg_replace("/[ :-]/","",$row['dta_made_dt']); // 날짜중에서 숫자만 추출
        $dta1[$val1]['x'] = $row['dta_made_timestamp']*1000;
        // $dta1[$val1]['y'] = (float)$row['dta_value'];
        $dta1[$val1]['y'] = round((float)$row['dta_value'],2);
        $dta1[$val1]['dta_shf_no'] = (int)$row['dta_shf_no'];
        $dta1[$val1]['dta_mmi_no'] = (int)$row['dta_mmi_no'];
        $dta1[$val1]['dta_type'] = $row['dta_type'];
        $dta1[$val1]['dta_no'] = $row['dta_no'];
    }
    

    // 실제 좌표값 생성
    $cnt=0;
	$list = array();
    for($i=$idx1;$i<=$idx2;$i+=$byunit) {
        //    echo $cnt.'. '.date("Y-m-d H:i:s",$i).'<br>';
        //    echo $cnt.'. '.preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$i)).'<br>';
        $val1 = preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$i)); // 날짜중에서 숫자만 추출
        $dta1[$val1]['x'] = strtotime($val1)*1000;
        $dta1[$val1]['y'] = ($dta1[$val1]['y']) ?: 0;
        $dta1[$val1]['yraw'] = ($dta1[$val1]['y']) ?: 0;
        $dta1[$val1]['yamp'] = 1;
        $dta1[$val1]['ymove'] = 0;
        $dta1[$val1]['dta_type'] = $dta1[$val1]['dta_type'];
        $dta1[$val1]['dta_no'] = $dta1[$val1]['dta_no'];
        $dta1[$val1]['dta_shf_no'] = $dta1[$val1]['dta_shf_no'];
        // 좌표값
        $list[$cnt] = $dta1[$val1];
        $cnt++;
    }
    //print_r2($data1);

}

echo json_encode( $list );
?>