<?php
// 측정 자료: 실측 데이터 추출
// token, mms_idx, dta_type, dta_no, shf_no
// st_date, st_time, en_date, en_time
// 디폴트(실측정): http://bogwang.epcs.co.kr/device/json/measure.real.php?token=1099de5drf09&mms_idx=1
// http://bogwang.epcs.co.kr/device/json/measure.real.php?token=1099de5drf09&mms_idx=1&dta_type=1&dta_no=0&st_date=2020-04-23&st_time=10:18:49&en_date=2020-04-23&en_time=21:13:09
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
    $dta_limit = $_REQUEST['dta_limit'] ?: 600;
    $dta_type = $_REQUEST['dta_type'] ?: 1;
    $dta_no = $_REQUEST['dta_no'] ?: 2;
    $table_name = 'g5_1_data_measure_'.$_REQUEST['mms_idx'].'_'.$dta_type.'_'.$dta_no;
    
    $where = array();
    $where[] = " (1) ";   // 디폴트 검색조건
    
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);


    //1. 처음로딩(종료일이 없는 경우) 해당 조건에 맞는 값이 존재하는 제일 마지막 날짜를 추출해서 종료일자로 설정해 둔다.
    if(!$en_date) {
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
        // echo $st_date.' '.$st_time.'<br>';
        // max 값이 limit 보다 먼저 실행되는 관계로 한번더 감쌌습니다.
        $sql = "SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
                FROM
                (   SELECT dta_dt FROM {$table_name} {$sql_search}
                        AND dta_dt > '".$st_timestamp."'
                    ORDER BY dta_dt ASC
                    LIMIT ".$dta_limit."
                ) AS tb1
                ORDER BY dta_dt DESC LIMIT 1
        ";
        // echo $sql.'<br>';
        $en1 = sql_fetch($sql);
        // print_r2($en1);
        // 600개를 넘어가면 임의 조정
        // echo $en1['dta_ymdhis'].'<'.$en_date.' '.$en_time.'<br>';
        if($en1['dta_ymdhis'] < $en_date.' '.$en_time) {
            $en_date = date("Y-m-d",$en1['dta_dt']);
            $en_time = date("H:i:s",$en1['dta_dt']);
        }
        // echo $en_date.' '.$en_time.'<br>';
    }

    //3. 종료일만 있으면
    //        종료일에서부터 앞으로 600개를 계산한 후
    //        시작일자로 설정을 해 준다.
    if(!$st_date && $en_date) {
        $en_timestamp = strtotime($en_date.' '.$en_time);
        // echo $en_date.' '.$en_time.'<br>';
        // minx 값이 limit 보다 먼저 실행되는 관계로 한번더 감쌌습니다.
        $sql = "SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
                FROM
                (   SELECT dta_dt FROM {$table_name} {$sql_search}
                        AND dta_dt < '".$en_timestamp."'
                    ORDER BY dta_dt DESC
                    LIMIT ".$dta_limit."
                ) AS tb1
                ORDER BY dta_dt LIMIT 1
        ";
        // echo $sql.'<br>';
        $st1 = sql_fetch($sql);
        // print_r2($st1);
        $st_date = date("Y-m-d",$st1['dta_dt']);
        $st_time = date("H:i:s",$st1['dta_dt']);
        // echo $st_date.' '.$st_time.'<br>';
    }


    // 최종 날짜 조건
    $start = strtotime($st_date.' '.$st_time);
    $end = strtotime($en_date.' '.$en_time);
    // echo $st_date.' '.$st_time.'~'.$en_date.' '.$en_time.'<br>';
    // echo $start.'~'.$end.'<br>';

	// 
    $sql = "SELECT dta_idx, dta_dt
                    , FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_ymdhis
                    , dta_value
            FROM {$table_name} {$sql_search}
                AND dta_dt >= '".$start."'
                AND dta_dt <= '".$end."'
            ORDER BY dta_dt ASC
    ";
//    echo $sql.'<br>';
//    exit;
	$rs = sql_query($sql,1);
	$list = array();
	for($i=0;$row=sql_fetch_array($rs);$i++){
        $row['no'] = $i;
        $row['dta_idx'] = $row['dta_idx'];

        $row['item']['x'] = (int)preg_replace("/[ :-]/","",$row['dta_dt'])*1000;    // 날짜중에서 숫자만 추출*1000
        $row['item']['y'] = (int)$row['dta_value'];
        $row['item']['yraw'] = (int)$row['dta_value'];
        $row['item']['yamp'] = 1;
        $row['item']['ymove'] = 0;
        $row['item']['dta_type'] = (int)$dta_type;
        $row['item']['dta_no'] = (int)$dta_no;

        $list[$i] = $row['item'];
		// $list[$i] = $row; // <------- Deguging (주석만 제거하세요.)
	}
}

echo json_encode( $list );
?>