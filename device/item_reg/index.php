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
    //bom1_idx의 절단재 정보를 추출
    $bom1_sql = " SELECT orp.com_idx
                    , oop.bom_idx
                    , oop.oop_onlythis_yn
                    , orp.orp_start_date
                    , bom.bom_name
                    , bom.bom_part_no
                    , bom.bom_std
                    , bom.bom_press_type
                    , bom.bom_weight
                    , bom.bom_price
                    , mtr.bom_idx AS mtr_idx
                    , mtr.bom_part_no AS mtr_part_no
                FROM {$g5['order_out_practice_table']} oop
                    INNER JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
                    INNER JOIN {$g5['bom_table']} bom ON oop.bom_idx = bom.bom_idx
                    INNER JOIN {$g5['bom_item_table']} boi ON bom.bom_idx = boi.bom_idx
                    INNER JOIN {$g5['bom_table']} mtr ON boi.bom_idx_child = mtr.bom_idx
                WHERE  oop.oop_idx = '{$getData[0]['oop_idx']}'
    ";
    // echo $bom1_sql;exit;
    $bom1 = sql_fetch($bom1_sql);

    if($bom1['oop_onlythis_yn'] == 0 && $bom1['bom_press_type'] == '2_2'){
        $bom2_sql = " SELECT bom.com_idx
                    , bom.bom_idx
                    , bom.bom_name
                    , bom_part_no
                    , bom_std
                    , bom_press_type
                    , bom_weight
                    , bom_price
            FROM {$g5['bom_item_table']} boi
                INNER JOIN {$g5['bom_table']} bom ON bom.bom_idx = boi.bom_idx
                WHERE  boi.bom_idx_child = '{$bom1['mtr_idx']}'
                    AND bom.bom_idx != '{$bom1['bom_idx']}'
                LIMIT 1
        ";
        // print_r2($bom2_sql); 
        $bom2 = sql_fetch($bom2_sql);
    }

    //press_type상관없이 본제품만 생성할것인가
    /*
    com_idx = '{$oop['com_idx']}'
                , mms_idx = '{$getData[0]['mms_idx']}'
                , bom_idx = '{$oop['bom_idx']}'
                , oop_idx = '{$getData[0]['oop_idx']}'
                , bom_part_no = '{$oop['bom_part_no']}'
                , itm_name = '".addslashes($oop['itm_name'])."'
                , itm_weight = '{$oop['itm_weight']}'
                , itm_heat = '{$mtr['mtr_heat']}'
                , itm_status = 'finish'
    */
    if(!$test){
        if($bom1['oop_onlythis_yn']
            || !$bom1['oop_onlythis_yn'] && $bom1['bom_press_type'] == '0_1'
            || !$bom1['oop_onlythis_yn'] && $bom1['bom_press_type'] == '1_1'
            || !$bom1['oop_onlythis_yn'] && $bom1['bom_press_type'] == '2_1'){
            $sql = " INSERT INTO {$g5['item_table']} ( com_idx, mms_idx, bom_idx, oop_idx, bom_part_no, itm_name, itm_weight, itm_price, itm_heat, itm_status, itm_date, itm_reg_dt, itm_update_dt ) VALUES 
            ( '{$bom1['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom1['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom1['bom_part_no']}', '".addslashes($bom1['bom_name'])."', '{$bom1['bom_weight']}', '{$bom1['bom_price']}', '{$getData[0]['heat']}', 'finish', '".substr(G5_TIME_YMDHIS,0,10)."', '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."' )
            ";
        }
        //press_type 규정에 반영하여 생성할것인가
        else {
            if($bom1['bom_press_type'] == '2_2'){
                $sql = " INSERT INTO {$g5['item_table']} ( com_idx, mms_idx, bom_idx, oop_idx, bom_part_no, itm_name, itm_weight, itm_heat, itm_price, itm_status, itm_date, itm_reg_dt, itm_update_dt ) VALUES 
                ( '{$bom1['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom1['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom1['bom_part_no']}', '".addslashes($bom1['bom_name'])."', '{$bom1['bom_weight']}', '{$getData[0]['heat']}', '{$bom1['bom_price']}', 'finish', '".substr(G5_TIME_YMDHIS,0,10)."', '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."' )
    
                , ( '{$bom2['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom2['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom2['bom_part_no']}', '".addslashes($bom2['bom_name'])."', '{$bom2['bom_weight']}', '{$getData[0]['heat']}', '{$bom2['bom_price']}', 'finish', '".substr(G5_TIME_YMDHIS,0,10)."', '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."' )
                ";
            }
            else {
                $tp_arr = explode('_',$bom1['bom_press_type']);
                $cp_num = $tp_arr[1]; //복제갯수
                $sql = " INSERT INTO {$g5['item_table']} ( com_idx, mms_idx, bom_idx, oop_idx, bom_part_no, itm_name, itm_weight, itm_price, itm_heat, itm_status, itm_date, itm_reg_dt, itm_update_dt ) VALUES ";
                $sql_loop = '';
                for($i=0;$i<$cp_num;$i++){
                    $sql_loop .= (($i==0)?'':',')." ( '{$bom1['com_idx']}', '{$getData[0]['mms_idx']}', '{$bom1['bom_idx']}', '{$getData[0]['oop_idx']}', '{$bom1['bom_part_no']}', '".addslashes($bom1['bom_name'])."', '{$bom1['bom_weight']}', '{$bom1['bom_price']}', '{$getData[0]['heat']}', 'finish', '".substr(G5_TIME_YMDHIS,0,10)."', '".G5_TIME_YMDHIS."', '".G5_TIME_YMDHIS."' ) ";
                }
                $sql = $sql.$sql_loop;
            }
        }
        sql_query($sql,1);

        $half_sql = " UPDATE {$g5['material_table']} SET mtr_status = 'finish'
            WHERE oop_idx = '{$getData[0]['oop_idx']}'
                AND mtr_type = 'half'
                AND mtr_status = 'stock'
            ORDER BY mtr_idx
            LIMIT 1
        ";
        sql_query($half_sql);
    }
    //테스트(데이터생성)모드일때
    else{
        include_once('./insert.php');
    }   
}

//테스트페이지로부터 호출되었으면 테스트 폼페이지로 이동
if($test){
    goto_url('./form.php?'.$qstr.'&oop_idx='.$oop_idx.'&page='.$page);
}
else{
    echo json_encode( array('meta'=>$result_arr) );
}
