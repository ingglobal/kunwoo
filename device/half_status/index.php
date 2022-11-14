<?php
header('Content-Type: application/json; charset=UTF-8');
include_once('./_common.php');

//환경변수 저장할 컬럼이 없으면 생성
if(!isset($config['cf_current_oop_idx'])) {
    sql_query(" ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_current_oop_idx` int(11) NOT NULL DEFAULT '0' AFTER `cf_recaptcha_secret_key` ,
                    ADD `cf_current_mtr_idx` int(11) NOT NULL DEFAULT '0' AFTER `cf_current_oop_idx` ", true);
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
else if(($getData[0]['type'] && $getData[0]['mtr_idx']) || ($getData[0]['type'] && $getData[0]['mtr_barcode'])) {
    $mtr_sch = ($getData[0]['type'] == 'reoutput') ? " mtr_idx = '{$getData[0]['mtr_idx']}' " : " mtr_barcode = '{$getData[0]['mtr_barcode']}' ";
    $mtr_sql = " SELECT
						mtr.oop_idx
						, ( SELECT orp_idx FROM {$g5['order_out_practice_table']} WHERE oop_idx = mtr.oop_idx ) AS orp_idx
						, mtr_idx
						, mtr_input_date
					FROM {$g5['material_table']} AS mtr WHERE {$mtr_sch} ";
    // echo $mtr_sql;exit;
    $sch_res = sql_fetch($mtr_sql);

    $result_arr['code'] = 200;
    $result_arr['orp_idx'] = $sch_res['orp_idx'];
    $result_arr['oop_idx'] = $sch_res['oop_idx'];
    $result_arr['mtr_idx'] = $sch_res['mtr_idx'];
    $result_arr['mtr_input_date'] = $sch_res['mtr_input_date'];

    //재출력 모드 ###################################################################
    if($getData[0]['type'] == 'reoutput') {
        //무게데이터를 변경
        $sql = " UPDATE {$g5['material_table']} SET mtr_weight = '{$getData[0]['mtr_weight']}' WHERE {$mtr_sch} ";
        sql_query($sql,1);
        $result_arr['message'] = 'Updated reoutput OK!';
        update_item_sum2(); //material 변경사항을 반영하기 위해 item_sum테이블 업데이트함
    }
    //용융기투입 모드 #################################################################
    else if($getData[0]['type'] == 'melt') {
        //환경변수 cf_current_oop_idx = 해당 oop_idx, cf_current_mtr_idx = 해당 mtr_idx를 저장
        sql_query(" UPDATE {$g5['config_table']} SET cf_current_oop_idx = '{$sch_res['oop_idx']}', cf_current_mtr_idx = '{$sch_res['mtr_idx']}' ",1);

        //해당 mtr_idx의 레코드의 mtr_status = melt로 변경
        $sql = " UPDATE {$g5['material_table']} SET
						trm_idx_location = '{$getData[0]['trm_idx_location']}'
                        , mtr_history = CONCAT(mtr_history,'\n".$getData[0]['type']."|".$sch_res['mtr_input_date']."|".G5_TIME_YMDHIS."')
                        , mtr_status = '{$getData[0]['type']}'
                        , mtr_melt_dt = '".G5_TIME_YMDHIS."'
                        , mtr_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE {$mtr_sch} ";
        sql_query($sql,1);

		$orp_sql = " UPDATE {$g5['order_practice_table']} SET 
							trm_idx_line = '{$getData[0]['trm_idx_location']}'
						WHERE orp_idx = '{$sch_res['orp_idx']}' ";
		sql_query($orp_sql,1);

        $result_arr['message'] = 'Updated melt OK!';

        update_item_sum2(); //material 변경사항을 반영하기 위해 item_sum테이블 업데이트함
    }
    //상태값변경 모드 ###################################################################
    else if($getData[0]['type'] == 'status') {
		$error_search = (preg_match('/^error_/', $getData[0]['mtr_status'])) ? ", mtr_defect = '1', mtr_defect_type = '{$g5['set_half_status_ng2_reverse'][$getData[0]['mtr_status']]}' " : ", mtr_defect = '0', mtr_defect_type = '0' ";
        //해당 mtr_idx의 레코드의 mtr_status = 해당상태값으로 변경
        $sql = " UPDATE {$g5['material_table']} SET
                        mtr_history = CONCAT(mtr_history,'\n".$getData[0]['mtr_status']."|".$sch_res['mtr_input_date']."|".G5_TIME_YMDHIS."')
                        , mtr_status = '{$getData[0]['mtr_status']}'
                        , mtr_update_dt = '".G5_TIME_YMDHIS."'
						{$error_search}
                    WHERE {$mtr_sch} ";
        sql_query($sql,1);
        $result_arr['message'] = "Updated status to '{$getData[0]['mtr_status']}' OK!";

        update_item_sum2(); //material 변경사항을 반영하기 위해 item_sum테이블 업데이트함
    }
    //검색 모드 ########################################################################
    else if($getData[0]['type'] == 'search') {
        //그냥 조건부 상단에서 바코드에 해당하는 oop_idx 와 mtr_idx만을 반환하는게 목적이다.
        $result_arr['message'] = 'Updated search OK!';
    }
}
else {
    $result_arr = array("code"=>599,"message"=>"error");
}



//테스트페이지로부터 호출되었으면 테스트 폼페이지로 이동
if($test){
    goto_url('./form.php?oop_idx='.$sch_res['oop_idx'].'&mtr_idx='.$sch_res['mtr_idx']);
}
else{
    echo json_encode( array('meta'=>$result_arr) );
}
