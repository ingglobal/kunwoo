<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

$rawBody = file_get_contents("php://input"); // 본문을 불러옴
//test=1&oop_idx=34&bundle=D22SAK2513-G012&number=30&token=1099de5drf09
if($test){
    $rawBody = json_encode($_POST);
}

$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고

$result_arr = array("code"=>200,"message"=>"ok");
// print_r2($getData);exit;
// 토큰 없거나 문제있으면 에러
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
//oop_idx가 없으면 에러
if(!$getData[0]['oop_idx']){
    $result_arr = array("code"=>599,"message"=>"oop_idx error");
}
//설비ID가 없으면 에러
if(!$getData[0]['mms_idx']){
    $result_arr = array("code"=>688,"message"=>"mms_idx error");
}
//번들넘버가 없으면 에러
if(!$getData[0]['bundle']){
    $result_arr = array("code"=>699,"message"=>"bundle error");
}
//테스트 버전에서 문제가 있으면 경고창 표시하고 이전페이지로 이동
if($test && $result_arr['message'] != 'ok'){
    alert($result_arr['message']);
}

if($result_arr['message'] == 'ok'){
    //원자재 mtr_heat번호를 추출
    $mtr = sql_fetch(" SELECT mtr_heat, mtr_lot FROM {$g5['material_table']} WHERE mtr_bundle = '{$getData[0]['bundle']}' AND mtr_status NOT IN ('delete','del','trash')
    ");
    //히트넘버거 없으면 에러
    if(!$mtr['mtr_heat']){
        $result_arr = array("code"=>799,"message"=>"mtr_heat error");
        //테스트버전에서는 히트넘버가 없으니 경고창 표시하고 이전페이지 이동
        if($test){
            alert($result_arr['message']);
        }
    }

    //oop_idx의 절단재 정보를 추출
    $oop_sql = " SELECT oop.bom_idx AS bom_idx_parent
                    , orp.orp_start_date AS orp_start_date
                    , cut.bom_idx AS mtr_idx
                    , cut.bom_name AS mtr_name
                    , cut.bom_part_no AS mtr_part_no
                    , cut.bom_length AS mtr_length
                    , cut.bom_weight AS mtr_weight
                FROM {$g5['order_out_practice_table']} oop
                    INNER JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
                    INNER JOIN {$g5['bom_table']} bom ON oop.bom_idx = bom.bom_idx
                    INNER JOIN {$g5['bom_item_table']} boi ON bom.bom_idx = boi.bom_idx
                    INNER JOIN {$g5['bom_table']} cut ON boi.bom_idx_child = cut.bom_idx
                WHERE  oop.oop_idx = '{$getData[0]['oop_idx']}'
    ";
    // echo $oop_sql;exit;
    $oop = sql_fetch($oop_sql);
    //절단재 정보가 없으면 에러
    if(!$oop['mtr_idx']){
        $result_arr = array("code"=>899,"message"=>"mtr_idx error");
        //테스트버전에서 절단재 정보가 없으므로 경고창 표시와 이전페이지 이동
        if($test){
            alert($result_arr['message']);
        }
    }  
    
    $sql = " INSERT INTO {$g5['material_table']} SET
                com_idx = '{$_SESSION['ss_com_idx']}'
                , mms_idx = '{$getData[0]['mms_idx']}'
                , bom_idx = '{$oop['mtr_idx']}'
                , bom_idx_parent = '{$oop['bom_idx_parent']}'
                , oop_idx = '{$getData[0]['oop_idx']}'
                , bom_part_no = '{$oop['mtr_part_no']}'
                , mtr_name = '".addslashes($oop['mtr_name'])."'
                , mtr_weight = '{$oop['mtr_weight']}'
                , mtr_type = 'half'
                , mtr_heat = '{$mtr['mtr_heat']}'
                , mtr_lot = '{$mtr['mtr_lot']}'
                , mtr_bundle = '{$getData[0]['bundle']}'
                , mtr_status = 'stock'
    ";

    if(!$test){
        $sql .= "
            , mtr_input_date = '".substr(G5_TIME_YMDHIS,0,10)."'
            , mtr_reg_dt = '".G5_TIME_YMDHIS."'
            , mtr_update_dt = '".G5_TIME_YMDHIS."'
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
