<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

//환경변수 저장할 컬럼이 없으면 생성
if(!isset($config['cf_current_oop_idx'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_current_oop_idx` int(11) NOT NULL DEFAULT '0' AFTER `cf_recaptcha_secret_key` ,
                    ADD `cf_current_itm_idx` int(11) NOT NULL DEFAULT '0' AFTER `cf_current_oop_idx` ", true);
}


$rawBody = file_get_contents("php://input"); // 본문을 불러옴
$getData = array(json_decode($rawBody,true)); // 데이터를 변수에 넣고

if($test){
    $getData = array();
    $getData[0] = array();
    $getData[0] = $_POST;
}

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if(($getData[0]['type'] && $getData[0]['itm_idx']) || ($getData[0]['type'] && $getData[0]['itm_barcode'])) {
    $itm_sch = ($getData[0]['type'] == 'reoutput') ? " itm_idx = '{$getData[0]['itm_idx']}' " : " itm_barcode = '{$getData[0]['itm_barcode']}' ";
    $itm_sql = " SELECT oop_idx, itm_idx, itm_date FROM {$g5['item_table']} WHERE {$itm_sch} ";
    // echo $itm_sql;exit;
    $sch_res = sql_fetch($itm_sql);

    $result_arr['code'] = 200;
    $result_arr['oop_idx'] = $sch_res['oop_idx'];
    $result_arr['itm_idx'] = $sch_res['itm_idx'];
    $result_arr['itm_date'] = $sch_res['itm_date'];

    //재출력 모드 ###################################################################
    if($getData[0]['type'] == 'reoutput') {
        //무게데이터를 변경
        $sql = " UPDATE {$g5['item_table']} SET itm_weight = '{$getData[0]['itm_weight']}' WHERE {$itm_sch} ";
        sql_query($sql,1);
        $result_arr['message'] = 'Updated reoutput OK!';
        update_item_sum2(); //item 변경사항을 반영하기 위해 item_sum테이블 업데이트함
    }
    //상태값변경 모드 ###################################################################
    else if($getData[0]['type'] == 'status') {
		$error_search = (preg_match('/^error_/', $getData[0]['itm_status'])) ? ", itm_defect = '1', itm_defect_type = '{$g5['set_itm_status_ng2_reverse'][$getData[0]['itm_status']]}' " : ", itm_defect = '0', itm_defect_type = '0' ";
		$delivery_search = ($getData[0]['itm_status'] == 'delivery') ? ", itm_delivery = '1' " : ", itm_delivery = '0' ";
		//해당 itm_idx의 레코드의 itm_status = 해당상태값으로 변경
        $sql = " UPDATE {$g5['item_table']} SET
                        itm_history = CONCAT(itm_history,'\n".$getData[0]['itm_status']."|".$sch_res['itm_date']."|".G5_TIME_YMDHIS."')
                        , itm_status = '{$getData[0]['itm_status']}'
                        , itm_update_dt = '".G5_TIME_YMDHIS."'
						{$error_search}
						{$delivery_search}
                    WHERE {$itm_sch} ";
        sql_query($sql,1);
        $result_arr['message'] = "Updated status to '{$getData[0]['itm_status']}' OK!";
        update_item_sum2(); //item 변경사항을 반영하기 위해 item_sum테이블 업데이트함
    }
    //검색 모드 ########################################################################
    else if($getData[0]['type'] == 'search') {
        //그냥 조건부 상단에서 바코드에 해당하는 oop_idx 와 itm_idx만을 반환하는게 목적이다.
        $result_arr['message'] = 'Updated search OK!';
    }
}
else {
    $result_arr = array("code"=>599,"message"=>"error");
}



//테스트페이지로부터 호출되었으면 테스트 폼페이지로 이동
if($test){
    goto_url('./form.php?oop_idx='.$sch_res['oop_idx'].'&itm_idx='.$sch_res['itm_idx']);
}
else{
    echo json_encode( array('meta'=>$result_arr) );
}
