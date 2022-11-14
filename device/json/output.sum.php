<?php
// 생산 자료: 일,주,월,년
// 시간단위 그룹 구분: 일간,주간,월간,년간으로 가면 쿼리가 달라짐: dta_item(minute,second) 이 아닌 경우 일자별 sum 기반 추출
// 분, 초인 경우는 output.php 쪽으로 가서 확인
// token, mms_idx, dta_group, shf_no, dta_mmi_no, dta_item(minute)
// dta_value_type(sum)
// st_date, en_date
// dta_defect = 0(정상상품만), dta_defect = 1(불량상품만), dta_defect = 0,1(정상,불량 모두다)
// 디폴트: http://bogwang.epcs.co.kr/device/json/output.sum.php?token=1099de5drf09&mms_idx=1
// 조건을 바꾸어 보세요.
// http://bogwang.epcs.co.kr/device/json/output.sum.php?token=1099de5drf09&mms_idx=1&dta_item=daily&st_date=2020-05-01&en_date=2020-05-31
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
    $graph_type = ($_REQUEST['graph_type']) ?: 'spline';
    // 0,1 이 넘어오면 정상+불량상품 모두
    if(isset($_REQUEST['dta_defect'])) {
        $dta_defects = explode(',', preg_replace("/\s+/", "", $_REQUEST['dta_defect']));
    }
    else {
        $dta_defects = array(0,1);
    }
    // print_r2($dta_defects);
    
    $where = array();
    $where[] = " mms_idx = '".$mms_idx."' AND dta_group = '".$dta_group."' AND dta_defect IN (".implode(",",$dta_defects).") ";   // 디폴트 검색조건
    // $where[] = " mms_idx = '".$mms_idx."' AND dta_group = '".$dta_group."' ";   // 디폴트 검색조건
    
    // shf_no 조건
    if($_REQUEST['shf_no']) 
        $where[] = " dta_shf_no = '".$_REQUEST['shf_no']."' ";
        
    // dta_mmi_no 기종 조건
    if(isset($_REQUEST['dta_mmi_no']) && $_REQUEST['dta_mmi_no']!='')
        $where[] = " dta_mmi_no = '".$_REQUEST['dta_mmi_no']."' ";
        
    // dta_defect_type 불량타입 조건
    if(isset($_REQUEST['dta_defect_type'])) 
        $where[] = " dta_defect_type = '".$_REQUEST['dta_defect_type']."' ";
        
    // 최종 WHERE 생성
    if ($where)
        $sql_search = ' WHERE '.implode(' AND ', $where);


    //1. 처음로딩(종료일이 없는 경우) 해당 조건에 맞는 값이 존재하는 제일 마지막 날짜를 추출해서 종료일자로 설정해 둔다.
    if(!$en_date) {
        $en1 = sql_fetch("SELECT * FROM {$g5['data_output_sum_table']} {$sql_search}
                ORDER BY dta_date DESC LIMIT 1 
        ");
        // print_r2($en1);
        $en_date = $en1['dta_date'];
        // echo $en_date.'<br>';
    }

    //2. 시작일, 종료일 둘 다 있으면
    //        시작일부터 600개가 종료 설정값을 넘어가면 자동으로 줄여버림
    //        안 넘어가면 설정한 값이 종료일자.
    if($st_date && $en_date) {
        // 조건 필요없음, 일,주간,월, 년도별은 사람들의 범위 구간 인식이 분명함 
    }

    //3. 종료일만 있으면
    //        종료일에서부터 두달 정도 앞을 시작일자로 설정을 해 준다.
    if(!$st_date && $en_date) {
        $st1 = sql_fetch(" SELECT date_add('".$en_date."', INTERVAL -2 MONTH) AS dta_date FROM dual ");
        // print_r2($st1);
        $st_date = $st1['dta_date'];
        // echo $st_date.' '.$st_time.'<br>';
    }

    // echo '시작: '.$st_date.'<br>';   //-------------------------------------
    // echo '끝: '.$en_date.'<br>';    //-------------------------------------

    // 각 item 별 구분
    if($_REQUEST['dta_item']=='weekly') {
        $sql_date = " WEEK(ymd_date,1) AS ymd_unit ";
        $sql_item = " WEEK(dta_date,1) AS ymd_unit ";
    }
    else if($_REQUEST['dta_item']=='monthly') {
        $sql_date = " substring( CAST(ymd_date AS CHAR),1,7) AS ymd_unit ";
        $sql_item = " substring( CAST(dta_date AS CHAR),1,7) AS ymd_unit ";
    }
    else if($_REQUEST['dta_item']=='yearly') {
        $sql_date = " substring( CAST(ymd_date AS CHAR),1,4) AS ymd_unit ";
        $sql_item = " substring( CAST(dta_date AS CHAR),1,4) AS ymd_unit ";
    }
    // default = 일자별
    else {
        $sql_date = " CAST(ymd_date AS CHAR) AS ymd_unit ";
        $sql_item = " CAST(dta_date AS CHAR) AS ymd_unit ";
    }

    // 
    $sql = "SELECT
                MIN(ymd_date) AS ymd_date
                , ymd_unit
                , SUM(dta_sum) AS dta_sum
                , SUM(dta_max) AS dta_max
                , SUM(dta_min) AS dta_min
                , SUM(dta_avg) AS dta_avg
                , SUM(dta_shf_no) AS dta_shf_no
                , GROUP_CONCAT(dta_mmi_no) AS dta_mmi_no
                , SUM(dta_defect) AS dta_defect
                , SUM(dta_defect_type) AS dta_defect_type
                , GROUP_CONCAT(dta_message) AS dta_message
            FROM
            (
                (
                SELECT
                    ymd_date
                    , {$sql_date}
                    , 0 AS dta_sum
                    , 0 AS dta_max
                    , 0 AS dta_min
                    , 0 AS dta_avg
                    , 0 AS dta_shf_no
                    , NULL AS dta_mmi_no
                    , 0 AS dta_defect
                    , 0 AS dta_defect_type
                    , NULL AS dta_message
                FROM {$g5['ymd_table']} AS ymd
                WHERE ymd_date BETWEEN '".$st_date."' AND '".$en_date."'
                GROUP BY ymd_unit
                ORDER BY ymd_date
                )
                UNION ALL
                (
                SELECT
                    CAST(dta_date AS CHAR) AS ymd_date
                    , {$sql_item}
                    , SUM(dta_value) AS dta_sum
                    , MAX(dta_value) AS dta_max
                    , MIN(dta_value) AS dta_min
                    , AVG(dta_value) AS dta_avg
                    , dta_shf_no
                    , dta_mmi_no
                    , dta_defect
                    , dta_defect_type
                    , dta_message
                FROM {$g5['data_output_sum_table']} AS dta
                {$sql_search}
                    AND dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                GROUP BY ymd_unit
                ORDER BY ymd_date
                )
            ) AS db_table
            GROUP BY ymd_unit
            ";
//    echo $sql.'<br>';
//    exit;
	$rs = sql_query($sql,1);
	$list = array();
	for($i=0;$row=sql_fetch_array($rs);$i++){
        $row['no'] = $i;
        $row['json']['x'] = strtotime($row['ymd_date'])*1000;    // highchart 에서는 miliseconde 이므로 *1000
        $row['json']['y'] = (int)$row['dta_sum'];   // 합계값을 넘겨준다.
        $row['json']['yraw'] = $row['json']['y'];
        $row['json']['yamp'] = 1;
        $row['json']['ymove'] = 0;
        $row['json']['dta_sum'] = (int)$row['dta_sum'];
        $row['json']['dta_max'] = (int)$row['dta_max'];
        $row['json']['dta_min'] = (int)$row['dta_min'];
        $row['json']['dta_avg'] = round($row['dta_avg'],2);
        $row['json']['dta_defect'] = $row['dta_defect'];
        $row['json']['dta_defect_type'] = $row['dta_defect_type'];
        $row['json']['dta_message'] = $row['dta_message'];

        // 각 item 별 구분
        $row['json']['unit'] = $row['ymd_date'];
        if($_REQUEST['dta_item']=='monthly') {
            $row['json']['unit'] = substr($row['ymd_date'],0,7);
        }
        else if($_REQUEST['dta_item']=='yearly') {
            $row['json']['unit'] = substr($row['ymd_date'],0,4);
        }

        $row['json']['dta_shf_no'] = (int)$row['dta_shf_no'];
        $row['json']['dta_mmi_no'] = $row['dta_mmi_no'];
        $list[$i] = $row['json'];
		// $list[$i] = $row; // <------- Debuging (주석만 제거하세요.)
	}
}

echo json_encode( $list );
?>