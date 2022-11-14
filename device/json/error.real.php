<?php
// 에러 자료: 실측정
// 실측정 데이터, 그룹핑 데이터 구분: dta_value_type(count,sum,max,min,avg)값이 없으면 실측정 데이터
// 시간단위 그룹 구분: 일간,주간,월간,년간으로 가면 쿼리가 달라짐: dta_item(minute,second) 이 아닌 경우는 월기반
// 파일분리? error.sum.php ---------------------------------------------------------------------
// 그래프별로 구분: 꺽은선, 막대는 합계로, 파이차트는 통계로 쿼리를 뽑아야 함
// 이것도 분리? error.line.php, error.pie.php, error.spline.php ---------------------------------
// token, mms_idx, dta_group, shf_no, dta_code, dta_item(minute), dta_unit(10)
// dta_value_type(count), graph_type(spline)
// st_date, st_time, en_date, en_time
// 디폴트(실측정): http://bogwang.epcs.co.kr/device/json/error.php?token=1099de5drf09&mms_idx=1&dta_group=err
// 조건을 바꾸어 보세요.
// http://bogwang.epcs.co.kr/device/json/error.php?token=1099de5drf09&mms_idx=1&dta_group=err&shf_no=1&dta_code=M0101&dta_item=minute&dta_unit=10&&st_date=2020-04-29&st_time=10:00:00&end_date=2020-04-30&end_time=23:59:59
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
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
    $dta_group = ($_REQUEST['dta_group']) ?: 'err';
    $graph_type = ($_REQUEST['graph_type']) ?: 'spline';
    
    // 그룹 선택에 따른 초기 ser1, ser2 설정 (user.07.intra.default.php 참조)
    $ser1 = ($_REQUEST['dta_item']) ? $_REQUEST['dta_item'] : $g5['set_graph_'.$dta_group]['default0'];
    $ser2 = ($_REQUEST['dta_unit']) ? $_REQUEST['dta_unit'] : $g5['set_graph_'.$dta_group]['default1'];
    $ser2 = ($ser2) ?: 1;   // ser2가 환경설정값에도 없으면 1로 디폴트 설정
    if($ser1!='minute' && $ser1!='second') // 분,초가 아니면 무조건 1
        $ser2 = 1;
    // echo $ser2.'<br>';

    $where = array();
    $where[] = " mms_idx = '".$mms_idx."' AND dta_group = '".$dta_group."' AND dta_status = 0 ";   // 디폴트 검색조건
    
    // shf_no 조건
    if($_REQUEST['shf_no']) 
        $where[] = " dta_shf_no = '".$_REQUEST['shf_no']."' ";
        
    // dta_code 조건
    if($_REQUEST['dta_code']) 
        $where[] = " dta_code = '".$_REQUEST['dta_code']."' ";
        
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);


    //1. 처음로딩(종료일이 없는 경우) 해당 조건에 맞는 값이 존재하는 제일 마지막 날짜를 추출해서 종료일자로 설정해 둔다.
    if(!$en_date) {
        $en1 = sql_fetch("SELECT * FROM {$g5['data_error_table']} {$sql_search}
                ORDER BY dta_dt DESC LIMIT 1 
        ");
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
        // 그룹핑인 경우
        if($_REQUEST['dta_value_type']) {
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
        // 실측정값인 경우
        else {
            // max 값이 limit 보다 먼저 실행되는 관계로 한번더 감쌌습니다.
            $en1 = sql_fetch("  SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
                                FROM
                                (   SELECT dta_dt FROM {$g5['data_error_table']} {$sql_search}
                                        AND dta_dt > '".$st_timestamp."'
                                    ORDER BY dta_dt ASC
                                    LIMIT 600
                                ) AS tb1 ORDER BY dta_dt DESC LIMIT 1
            ");
            // print_r2($en1);
            $en_date = date("Y-m-d",$en1['dta_dt']);
            $en_time = date("H:i:s",$en1['dta_dt']);
            // echo $st_date.' '.$st_time.'<br>';
        }
    }

    //3. 종료일만 있으면
    //        종료일에서부터 검색항목별 설정값(daily,1,30 = 일별,1일단위,30일치 등..)을 계산한 후
    //        시작일자로 설정을 해 준다.
    if(!$st_date && $en_date) {
        $en_timestamp = strtotime($en_date.' '.$en_time);
        // echo $en_date.' '.$en_time.'<br>';
        // 그룹핑인 경우
        if($_REQUEST['dta_value_type']) {
            $seconds[$ser1][1] = ($seconds[$ser1][1]) ?: $ser2;// 단위 선택값이 없으면 폼에서 선택된 값을 참조
        //    $st_timestamp = $en_timestamp - ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['setting']['set_graph_max']); // 일별인 경우 -600일이 너무 커서 변경
        //    echo $g5['set_graph_'.$dta_group]['default2'].'<br>';
            $st_timestamp = $en_timestamp - ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['set_graph_'.$dta_group]['default2']);
            $st_date = date("Y-m-d",$st_timestamp);
            $st_time = date("H:i:s",$st_timestamp);
        }
        // 실측정값인 경우
        else {

            // minx 값이 limit 보다 먼저 실행되는 관계로 한번더 감쌌습니다.
            $sql = "  SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
                                FROM
                                (   SELECT dta_dt FROM {$g5['data_error_table']} {$sql_search}
                                        AND dta_dt < '".$en_timestamp."'
                                    ORDER BY dta_dt DESC
                                    LIMIT 600
                                ) AS tb1 ORDER BY dta_dt LIMIT 1
                                ";
            // echo $sql.'<br>';
            $st1 = sql_fetch($sql);
            // print_r2($st1);
            $st_date = date("Y-m-d",$st1['dta_dt']);
            $st_time = date("H:i:s",$st1['dta_dt']);
            // echo $st_date.' '.$st_time.'<br>';
        }
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


    // 그룹핑 조건인 경우
    if($_REQUEST['dta_value_type']) {
        $sql_select = " , ".strtoupper($_REQUEST['dta_value_type'])."(dta_idx) AS dta_value
                        , (dta_dt DIV ".$byunit.") AS dta_divided
                        , (dta_dt DIV ".$byunit.")*".$byunit." AS dta_made_timestamp
                        , FROM_UNIXTIME((dta_dt DIV ".$byunit.")*".$byunit.",'%Y-%m-%d %H:%i:%s') AS dta_made_dt
                        ";
        $sql_group = " GROUP BY dta_dt DIV ".$byunit;
    }

	// 
    $sql = "SELECT dta_idx, dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
                    , dta_shf_no, dta_code, dta_message
                {$sql_select}
            FROM {$g5['data_error_table']} {$sql_search}
                AND dta_dt >= '".$idx1."'
                AND dta_dt <= '".$idx2."'
            {$sql_group}
            ORDER BY dta_dt ASC
            LIMIT 600
            ";
//    echo $sql.'<br>';
//    exit;
	$rs = sql_query($sql,1);
	$list = array();
	for($i=0;$row=sql_fetch_array($rs);$i++){
        $row['no'] = $i;
        $row['dta_idx'] = $row['dta_idx'];
//         $row['dta_timestamp'] = $row['dta_dt']*1000;    // highchart 에서는 miliseconde 이므로 *1000
// //		$list[$i] = array($row['dta_timestamp'],number_format($row['dta_value'],2));
// 		$list[$i] = array($row['dta_timestamp'],(int)$row['dta_value']);
        // 그룹핑 조건인 경우
        if($_REQUEST['dta_value_type']) {
            $row['err']['x'] = (int)preg_replace("/[ :-]/","",$row['dta_made_dt'])*1000;    // 날짜중에서 숫자만 추출*1000
            $row['err']['y'] = (int)$row['dta_value'];
            $row['err']['yrow'] = (int)$row['dta_value'];
        }
        // 실측정값인 경우
        else {
            $row['err']['x'] = (int)preg_replace("/[ :-]/","",$row['dta_dt'])*1000;    // 날짜중에서 숫자만 추출*1000
            $row['err']['dta_shf_no'] = (int)$row['dta_shf_no'];
            $row['err']['dta_code'] = $row['dta_code'];
            $row['err']['dta_message'] = $row['dta_message'];
        }
        $list[$i] = $row['err'];
		// $list[$i] = $row; // <------- Deguging (주석만 제거하세요.)
	}
}

echo json_encode( $list );
?>