<?php
// 생산 목표(more than daily)
// token, mms_idx, dta_item(daily, weekly, monthly, yearly)
// st_date, en_date => two values must be not existed.
// http://bogwang.epcs.co.kr/adm/v10/ajax/output.target.sum.php?token=1099de5drf09&mms_idx=1&dta_item=daily&st_date=2020-03-27&en_date=2020-04-25
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

    // 시작일과 종료일자가 항상 있어야 함
    if(!$st_date||!$en_date) {
        $list = array("code"=>498,"message"=>"no st_date or no en_date");
        echo json_encode( array($list) );
		exit;
    }

    // 기본 설정
    $byunit = 86400;    // 하루 단위로 증가


    $where = array();
    $where[] = " mms_idx = '".$mms_idx."' AND shf_status = 'ok' ";   // 디폴트 검색조건
    
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);

    // echo '시작: '.$st_date.'<br>';   //-------------------------------------
    // echo '끝: '.$en_date.'<br>';    //-------------------------------------

    // 생산목표 미리 추출 (뒷 부분에 날짜값 돌면서 해당값 할당 예정)
    $sql = "SELECT mms_idx
                , shf_target_1, shf_target_2, shf_target_3, shf_target_4
                , GREATEST('".$st_date." 00:00:00', shf_start_dt ) AS shf_start_dt
                , LEAST('".$en_date." 23:59:59', shf_end_dt ) AS shf_end_dt
            FROM {$g5['shift_table']} {$sql_search}
                AND shf_end_dt >= '".$st_date."  00:00:00'
                AND shf_start_dt <= '".$en_date." 23:59:59'
            ORDER BY shf_start_dt
    ";
    // echo $sql.'<br>';
    // exit;
    $rs = sql_query($sql,1);
	for($i=0;$row=sql_fetch_array($rs);$i++){
        // print_r2($row);
        $row['shf_target_all'] = $row['shf_target_1'] + $row['shf_target_2'] + $row['shf_target_3'] + $row['shf_target_4'];
        $ts1 = strtotime($row['shf_start_dt']);    // 시작 timestamp
        $ts2 = strtotime($row['shf_end_dt']);    // 종료 timestamp
        // 날짜범위를 for 돌면서 배열변수 생성
        for($k=$ts1;$k<=$ts2;$k+=$byunit) {
            $cnt2++;
            // echo $cnt2.'. '.preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$k)).'<br>';   // ------------------
            $val1 = preg_replace("/[ :-]/","",date("Y-m-d",$k)); // 날짜중에서 숫자만 추출하여 배열키값으로!
            $dta1[$val1] = $row['shf_target_all'];  // 일별 생산 목표값
        }
        // echo '<br>--------------<br>';
    }
    // print_r2($dta1);
    // exit;
    // 3교대 설정 때문에 날짜 설정이 겹칠 수 있어서 for 문장 밖에서 한번 더 계산해야 함
    // 일간, 주간, 월간, 년간 배열 한꺼번에 생성해 놓고 아래 부분에서 필요한 영역에 가져다 쓰면 됨
    if(is_array($dta1)) {
        foreach($dta1 as $k1 => $v1) {
            // echo $k1. ': '.$v1.'<br>';
            // 일 단위
            $unit_day = date("Y-m-d",strtotime($k1));  // 월요일부터 시작
            $dta1[$unit_day] += $v1;

            // 주간 단위
            $unit_week = date("W",strtotime($k1));  // 월요일부터 시작
            $dta1[$unit_week] += $v1;

            // 월간 단위
            $unit_month = date("Y-m",strtotime($k1));
            $dta1[$unit_month] += $v1;

            // 년간 단위
            $unit_year = date("Y",strtotime($k1));
            $dta1[$unit_year] += $v1;

        }
    }
    // print_r2($dta1);
    // exit;


    // 각 item 별 구분
    if($_REQUEST['dta_item']=='weekly') {
        $sql_date = " WEEK(ymd_date,1) AS ymd_unit ";
    }
    else if($_REQUEST['dta_item']=='monthly') {
        $sql_date = " substring( CAST(ymd_date AS CHAR),1,7) AS ymd_unit ";
    }
    else if($_REQUEST['dta_item']=='yearly') {
        $sql_date = " substring( CAST(ymd_date AS CHAR),1,4) AS ymd_unit ";
    }
    // default = 일자별
    else {
        $sql_date = " CAST(ymd_date AS CHAR) AS ymd_unit ";
    }

    // 
    $sql = "SELECT
                MIN(ymd_date) AS ymd_date
                , ymd_unit
            FROM
            (
                SELECT
                    ymd_date
                    , {$sql_date}
                FROM {$g5['ymd_table']} AS ymd
                WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                GROUP BY ymd_unit
                ORDER BY ymd_date
            ) AS db_table
            GROUP BY ymd_unit
    ";
    // echo $sql.'<br>';
    // exit;
	$rs = sql_query($sql,1);
	$list = array();
	for($i=0;$row=sql_fetch_array($rs);$i++){
        $row['no'] = $i;
        $row['json']['x'] = strtotime($row['ymd_date'])*1000;    // highchart 에서는 miliseconde 이므로 *1000
        $row['json']['y'] = $dta1[$row['ymd_unit']];    // 검색 항목별 합계값
        $row['json']['yraw'] = $row['json']['y'];
        $row['json']['unit'] = $row['ymd_unit'];
        $row['json']['date'] = $row['ymd_date'];

        $list[$i] = $row['json'];
		// $list[$i] = $row; // <------- Debuging (주석만 제거하세요.)
	}
}

echo json_encode( $list );
?>