<?php
// 그래프 하단에 표현할 코드 추출, 교대별 or 코드별 or 교대별+코드별
// dta_groupby = shf_no, dta_code, shf_no_dat_code
// token, mms_idx, dta_group
// st_date, st_time, en_date, en_time
// http://bogwang.epcs.co.kr/device/json/error.code.php?token=1099de5drf09&mms_idx=1&dta_group=err&st_date=2020-02-29&st_time=00:00:00&end_date=2020-04-30&end_time=23:59:59
// 조건을 바꾸어 보세요.
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
    
    $where = array();
    $where[] = " mms_idx = '".$mms_idx."' AND dta_group = '".$dta_group."' AND dta_status = 0 ";   // 디폴트 검색조건
    
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);


    // 최종 날짜 조건
    $start = strtotime($st_date.' '.$st_time);
    $end = strtotime($en_date.' '.$en_time);
    //echo $st_date.' '.$st_time.'~'.$en_date.' '.$en_time.'<br>';
    //echo $start.'~'.$end.'<br>';

    // GROUP BY 조건
    if($_REQUEST['dta_groupby'] == 'shf_no_dta_code') {
        $sql_groupby = " dta_shf_no, dta_code ";
        $sql_orderby = " dta_shf_no, dta_code ";
    }
    else if($_REQUEST['dta_groupby'] == 'shf_no') {
        $sql_groupby = " dta_shf_no ";
        $sql_orderby = " dta_shf_no ";
    }
    else if($_REQUEST['dta_groupby'] == 'dta_code') {
        $sql_groupby = " dta_code ";
        $sql_orderby = " dta_count DESC ";
    }

	// 
    $sql = "SELECT dta_shf_no, dta_code, COUNT(dta_idx) AS dta_count, dta_message
            FROM {$g5['data_error_table']} {$sql_search}
                AND dta_dt >= '".$start."'
                AND dta_dt <= '".$end."'
            GROUP BY ".$sql_groupby."
            ORDER BY ".$sql_orderby."
            ";
//    echo $sql.'<br>';
//    exit;
	$rs = sql_query($sql,1);
	$list = array();
	for($i=0;$row=sql_fetch_array($rs);$i++){

        // 
        $row['item']['shf_no'] = $row['dta_shf_no'];
        $row['item']['dta_code'] = $row['dta_code'];
        $row['item']['dta_count'] = $row['dta_count'];
        $row['item']['dta_message'] = $row['dta_message'];
        $list[$i] = $row['item'];
		// $list[$i] = $row; // <------- Deguging (주석만 제거하세요.)
	}
}

echo json_encode( $list );
?>