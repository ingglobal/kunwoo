<?php
// 생산 자료: 디폴트 (초기 시간 설정)
// token, mms_idx
// http://bogwang.epcs.co.kr/device/json/output.default.php?token=1099de5drf09&mms_idx=2
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
    $dta_group = ($_REQUEST['dta_group']) ?: 'product';
    $dta_value_type = ($_REQUEST['dta_value_type']) ?: 'sum';
    $graph_type = ($_REQUEST['graph_type']) ?: 'spline';
    // 0,1 이 넘어오면 정상+불량상품 모두
    $dta_defects = array(0,1);
    
    // 그룹 선택에 따른 초기 ser1, ser2 설정 (user.07.intra.default.php 참조)
    $ser1 = ($_REQUEST['dta_item']) ?: $g5['set_graph_'.$dta_group]['default0'];
    $ser2 = ($_REQUEST['dta_unit']) ?: $g5['set_graph_'.$dta_group]['default1'];
    $ser2 = ($ser2) ?: 1;   // ser2가 환경설정값에도 없으면 1로 디폴트 설정
    if($ser1!='minute' && $ser1!='second') // 분,초가 아니면 무조건 1
        $ser2 = 1;

    $where = array();
    $where[] = " dta_defect IN (".implode(",",$dta_defects).") ";   // 디폴트 검색조건
    
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);

    //1. 제일 마지막 날짜를 추출해서 종료일자로 설정해 둔다.
    $sql = "SELECT * FROM g5_1_data_output_".$mms_idx." {$sql_search}
            ORDER BY dta_dt DESC LIMIT 1 
    ";
    // echo $sql.'<br>';
    $en1 = sql_fetch($sql,1);
    // print_r2($en1);
    $en_date = $en1['dta_dt'] ? date("Y-m-d",$en1['dta_dt']) : date("Y-m-d",G5_SERVER_TIME);
    $en_time = $en1['dta_dt'] ? date("H:i:s",$en1['dta_dt']) : date("H:i:s",G5_SERVER_TIME);

    //2. 종료일만 있으면
    //        종료일에서부터 검색항목별 설정값(daily,1,30 = 일별,1일단위,30일치 등..)을 계산한 후
    //        시작일자로 설정을 해 준다.
    $en_timestamp = strtotime($en_date.' '.$en_time);
    // echo $en_date.' '.$en_time.'<br>';
    $seconds[$ser1][1] = ($seconds[$ser1][1]) ?: $ser2;// 단위 선택값이 없으면 폼에서 선택된 값을 참조
    $st_timestamp = $en_timestamp - ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['set_graph_'.$dta_group]['default2']);
    $st_date = date("Y-m-d",$st_timestamp);
    $st_time = date("H:i:s",$st_timestamp);
    // echo $st_date.' '.$st_time.'<br>';

    // 최종 날짜 조건
    $start = strtotime($st_date.' '.$st_time);
    $end = strtotime($en_date.' '.$en_time);
    //echo $st_date.' '.$st_time.'~'.$en_date.' '.$en_time.'<br>';
    //echo $start.'~'.$end.'<br>';

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


    $list = array();
    $list['st_date'] = date("Y-m-d",$idx1);
    $list['st_time'] = date("H:i:s",$idx1);
    $list['en_date'] = $en_date;
    $list['en_time'] = $en_time;
    $list['dta_item'] = $ser1;
    $list['dta_unit'] = $ser2;

}

echo json_encode( $list );
?>