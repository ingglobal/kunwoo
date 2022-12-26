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
	$result_arr = array("code"=>199,"message"=>"token error");
}
//oop_idx가 없으면 에러
if(!$getData[0]['oop_idx']){
    $result_arr = array("code"=>299,"message"=>"oop_idx error");
}
//test모드가 아닐 경우 plt_barcode이 없으면 에러
if(!$test && !$getData[0]['plt_barcode']){
    $result_arr = array("code"=>399,"message"=>"plt_barcode error");
}
//heat(히트넘버)가 없으면 에러
if(!$getData[0]['heat']){
    $result_arr = array("code"=>499,"message"=>"heat error");
}
//선택bom_idx가 없으면 에러
if(!$getData[0]['bom_idx']){
    $result_arr = array("code"=>599,"message"=>"bom_idx error");
}
//plt_cnt가 없으면 에러 (test모드에서는 PLT갯수, 아닐경우 PLT안에 수량)
if(!$getData[0]['plt_cnt']){
    $result_arr = array("code"=>699,"message"=>"plt_cnt error");
}
//test모드의 경우 itm_total이 없으면 에러
if($test && !$getData[0]['itm_total']){
    $result_arr = array("code"=>799,"message"=>"itm_total error");
}
//test모드의 경우 itm_total이 plt_cnt보다 작으면 에러
if($test && $getData[0]['itm_total'] < $getData[0]['plt_cnt']){
    $result_arr = array("code"=>899,"message"=>"itm_total less than plt_cnt error");
}
//테스트 버전에서 문제가 있으면 경고창 표시하고 이전페이지로 이동
if($test && $result_arr['message'] != 'ok'){
    alert($result_arr['message']);
}

if($result_arr['message'] == 'ok'){
    /*
    oop_idx / cut_total / heat / plt_cnt / token
    ----------------------------------------------
    oop_idx
    bom_idx
    bom_part_no
    bom_std
    plt_barcode >> 만들어라
    plt_heat
    plt_lot ---제외
    plt_date
    plt_count
    plt_type
    plt_status
    plt_reg_dt
    plt_update_dt
    */
    
    //생산계획(oop_idx)의 제품정보를 호출 
    $bom_sql = " SELECT bom.com_idx
                        , bom.bom_idx
                        , bom.bom_part_no
                        , bom.bom_std
                        , bom.bom_press_type
                        , bom.com_idx_customer
                    FROM {$g5['bom_table']} bom
                        LEFT JOIN {$g5['company_table']} com ON bom.com_idx_customer = com.com_idx
                    WHERE  bom.bom_idx = '{$getData[0]['bom_idx']}'
    ";
    $bom = sql_fetch($bom_sql);

    // g5_1_item테이블에 등록된 해당 oop_idx AND 해당 bom_idx AND ('stock','finish')의 총 갯수
    $orp = sql_fetch(" SELECT forge_mms_idx FROM {$g5['order_out_practice_table']} oop
        LEFT JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx 
        WHERE oop.oop_idx = '{$getData[0]['oop_idx']}' AND oop_status NOT IN ('delete','del','trash') ");
    
    // echo $bom_sql."<br>";
    // print_r2($bom);exit;
    $sql = " INSERT INTO {$g5['pallet_table']} SET
                com_idx = '{$bom['com_idx']}'
                , oop_idx = '{$getData[0]['oop_idx']}'
                , bom_idx = '{$getData[0]['bom_idx']}'
                , bom_part_no = '{$bom['bom_part_no']}'
                , bom_std = '".addslashes($bom['bom_std'])."'
                , plt_heat = '{$getData[0]['heat']}'
                , plt_type = 'product'
                , plt_status = 'pending'
    ";
    //날짜 데이터 입력
    if(!$test){
        $sql .= " , plt_barcode = '{$getData[0]['plt_barcode']}'
                  , plt_count = '{$getData[0]['plt_cnt']}'
                  , plt_date = '".substr(G5_TIME_YMDHIS,0,10)."'
                  , plt_reg_dt = '".G5_TIME_YMDHIS."'
                  , plt_update_dt = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql,1);
    }
    else{
        $start_date = $cut['orp_start_date'].' '.substr(G5_TIME_YMDHIS,-8);
        $date_minus = strtotime($start_date."+2 days");
        $start_dt = date('Y-m-d H:i:s',$date_minus);
        $sql .= " , plt_date = '".substr($start_dt,0,10)."'
                  , plt_reg_dt = '{$start_dt}'
                  , plt_update_dt = '{$start_dt}'
        ";
        $tmp_date = substr($start_dt,0,10);
        $bcd_date = str_replace('-','',$tmp_date); //2022-11-09 => 20221109 로 치환
        
        $plt_num_chk_sql = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['pallet']}
            WHERE plt_date = '{$tmp_date}' 
                AND oop_idx = '{$getData[0]['oop_idx']}'
                AND bom_idx = '{$getData[0]['bom_idx']}'
                AND plt_type = 'product'
                AND plt_status NOT IN ('delete','del','trash','cancel')
        ");
        $pltnum = $plt_num_chk_sql['cnt'];

        $rest_cnt = $getData[0]['itm_total'] % $getData[0]['plt_cnt'];
        $per_cnt = ($getData[0]['itm_total'] - $rest_cnt) / $getData[0]['plt_cnt'];
        $rest_num = $rest_cnt;
        
        for($i=0;$i<$getData[0]['plt_cnt'];$i++){
            $in_cnt = ($rest_num > 0) ? $per_cnt + 1 : $per_cnt;
            $bcd_cnt = sprintf("%03d",$pltnum+$i+1);
            //바코드 생성
            $plt_brc = $bcd_date.$bcd_cnt.'_'.$orp['forge_mms_idx'].'_P_'.$getData[0]['oop_idx'].'_'.$bom['bom_part_no'].'_'.$in_cnt;
            $sql_init = $sql;
            $sql_add = " , plt_barcode = '{$plt_brc}'
                         , plt_count = '{$in_cnt}'
            ";
            $sql_init .= $sql_add;
            // print_r2($sql_init);
            sql_query($sql_init);
            $rest_num--;
        }   
    }
}
// exit;
//테스트페이지로부터 호출되었으면 테스트 폼페이지로 이동
if($test){
    goto_url('./form.php?'.$qstr.'&oop_idx='.$oop_idx);
}
else{
    echo json_encode( array('meta'=>$result_arr) );
}