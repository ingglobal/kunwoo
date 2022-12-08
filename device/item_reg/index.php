<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

$rawBody = file_get_contents("php://input"); // 본문을 불러옴
//test=1&oop_idx=34&mms_idx=68&heat=SM46A&number=30&token=1099de5drf09
if($test){
    $rawBody = json_encode($_POST);
}

$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고

$result_arr = array("code"=>200,"message"=>"ok");
// print_r2($getData);exit;
// 토큰 없거나 문제있으면 에러
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>199,"message"=>"token error");
}
//oop_idx가 없으면 에러
if(!$getData[0]['oop_idx']){
    $result_arr = array("code"=>299,"message"=>"oop_idx error");
}
//설비ID가 없으면 에러
if(!$getData[0]['mms_idx']){
    $result_arr = array("code"=>399,"message"=>"mms_idx error");
}
//히트넘버가 없으면 에러
if(!$getData[0]['heat']){
    $result_arr = array("code"=>499,"message"=>"heat error");
}
//test모드에서 생성수량이 없으면 에러
if($test && !$getData[0]['number']){
    $result_arr = array("code"=>599,"message"=>"number error");
}
//테스트 버전에서 문제가 있으면 경고창 표시하고 이전페이지로 이동
if($test && $result_arr['message'] != 'ok'){
    alert($result_arr['message']);
}

if($result_arr['message'] == 'ok'){
    //oop_idx의 절단재 정보를 추출
    $oop_sql = " SELECT orp.com_idx
                    , oop.bom_idx
                    , orp.orp_start_date
                    , bom.bom_name
                    , bom.bom_part_no
                    , bom.bom_std
                    , bom.bom_press_type
                    , bom.bom_weight
                    , mtr.bom_idx AS mtr_idx
                    , mtr.bom_part_no AS mtr_part_no
                FROM {$g5['order_out_practice_table']} oop
                    INNER JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
                    INNER JOIN {$g5['bom_table']} bom ON oop.bom_idx = bom.bom_idx
                    INNER JOIN {$g5['bom_item_table']} boi ON bom.bom_idx = boi.bom_idx
                    INNER JOIN {$g5['bom_table']} mtr ON boi.bom_idx_child = mtr.bom_idx
                WHERE  oop.oop_idx = '{$getData[0]['oop_idx']}'
    ";
    // echo $oop_sql;exit;
    $oop = sql_fetch($oop_sql);
    if($oop['bom_press_type'] == '2_2'){
        $oop2_sql = " SELECT bom.com_idx
                    , bom.bom_idx
                    , bom.bom_name
                    , bom_part_no
                    , bom_std
                    , bom_press_type
                    , bom_weight
            FROM {$g5['bom_item_table']} boi
                INNER JOIN {$g5['bom_table']} bom ON bom.bom_idx = boi.bom_idx
                WHERE  boi.bom_idx_child = '{$oop['mtr_idx']}'
                    AND bom.bom_idx != '{$oop['bom_idx']}'
                LIMIT 1
        ";
        // print_r2($oop2_sql); 
        $bom2 = sql_fetch($oop2_sql);
    }
    //여기까지 2_2타입의 다른 BOM데이터추출까지 작업했다 아래부터 작업 이어가세요.

    $sql = " INSERT INTO {$g5['item_table']} SET
                com_idx = '{$oop['com_idx']}'
                , mms_idx = '{$getData[0]['mms_idx']}'
                , bom_idx = '{$oop['bom_idx']}'
                , oop_idx = '{$getData[0]['oop_idx']}'
                , bom_part_no = '{$oop['bom_part_no']}'
                , itm_name = '".addslashes($oop['itm_name'])."'
                , itm_weight = '{$oop['itm_weight']}'
                , itm_heat = '{$mtr['mtr_heat']}'
                , itm_status = 'finish'
    ";

    if(!$test){
        $sql .= "
            , itm_date = '".substr(G5_TIME_YMDHIS,0,10)."'
            , itm_reg_dt = '".G5_TIME_YMDHIS."'
            , itm_update_dt = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql,1);
        $mtr_idx = sql_insert_id();
        $result_arr['mtr_idx'] = $mtr_idx;
    }
    else{
        $start_date = $oop['orp_start_date'].' '.substr(G5_TIME_YMDHIS,-8);
        for($i=0;$i<$getData[0]['number'];$i++){
            $date_plus = strtotime($start_date."+".($i*5)." second");
            $start_date = date('Y-m-d H:i:s',$date_plus);
            $date_minus = strtotime($start_date."-2 days");
            $start_dt = date('Y-m-d H:i:s',$date_minus);
            $sql_plus = $sql;
            $sql_plus .= "
                , mtr_input_date = '{$oop['orp_start_date']}'
                , mtr_reg_dt = '{$start_dt}'
                , mtr_update_dt = '{$start_dt}'
            ";
            sql_query($sql_plus,1);
        }
    }   
}

//테스트페이지로부터 호출되었으면 테스트 폼페이지로 이동
if($test){
    goto_url('./form.php?'.$qstr.'&oop_idx='.$oop_idx);
}
else{
    echo json_encode( array('meta'=>$result_arr) );
}
