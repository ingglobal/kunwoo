<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

//환경변수 저장할 컬럼이 없으면 생성
if(!isset($config['cf_current_oop_idx'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_current_oop_idx` int(11) NOT NULL DEFAULT '0' AFTER `cf_recaptcha_secret_key` ,
                    ADD `cf_current_mtr_idx` int(11) NOT NULL DEFAULT '0' AFTER `cf_current_oop_idx` ", true);
}

//$g5['cf_current_oop_idx'], $g5['cf_current_mtr_idx'],
$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고

if($test){
    $weight = trim($weight);
    $getData = array();
    $getData[0] = array();
    $getData[0] = $_POST;
}

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if($getData[0]['oop_idx']) {
    $sql = " UPDATE {$g5['order_out_practice_table']} AS oop SET
                oop_itm_weight = ( SELECT SUM(itm_weight) FROM {$g5['item_table']} WHERE oop_idx = '{$getData[0]['oop_idx']}' )
                , oop_itm_enddt = '".G5_TIME_YMDHIS."'
            WHERE oop.oop_idx = '{$getData[0]['oop_idx']}'
    ";
    sql_query($sql,1);
    $result_arr['code'] = 200;
    $result_arr['message'] = 'Updated OK!';
    $result_arr['oop_idx'] = $getData[0]['oop_idx'];
}
else {
    $result_arr = array("code"=>599,"message"=>"error");
}


//테스트페이지로부터 호출되었으면 테스트 폼페이지로 이동
if($test){
    goto_url('./form.php?oop_idx='.$oop_idx);
}
else{
    echo json_encode( array('meta'=>$result_arr) );
}
