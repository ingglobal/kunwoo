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
    $weight = trim($weight);
    $getData = array();
    $getData[0] = array();
    $getData[0] = $_POST;
    $getData[0]['weight'] = $weight;
}

// 토큰 비교
if(!check_token1($getData[0]['token'])) {
	$result_arr = array("code"=>499,"message"=>"token error");
}
else if($getData[0]['bom_part_no']) {
	$ims_arr = explode(',',$g5['line_key'][$getData[0]['trm_idx_location']]['trm_name2']);
	$ims = array();
	foreach($ims_arr as $imv){
		$imv_arr = explode('=',$imv);
		$ims[$imv_arr[0]] = $imv_arr[1];
	}
	// print_r2($ims);exit;
	$bom = get_table_meta('bom','bom_idx',$getData[0]['bom_idx']);
	$arr = $getData[0];
	$mtr_lot = substr($arr['mtr_barcode'],0,6);
	$shift = item_shif_date_return2(G5_TIME_YMDHIS);
	$sql = " INSERT INTO {$g5['material_table']} SET
		com_idx = '{$bom['com_idx']}'
		, imp_idx = '{$ims['imp_idx']}'
		, mms_idx = '{$ims['mms_idx']}'
		, bom_idx = '{$arr['bom_idx']}'
		, bom_idx_parent = '{$arr['bom_idx_parent']}'
		, oop_idx = '{$arr['oop_idx']}'
		, bom_part_no = '{$arr['bom_part_no']}'
		, mtr_name = '{$arr['mtr_name']}'
		, mtr_barcode = '{$arr['mtr_barcode']}'
		, mtr_type = 'half'
		, mtr_weight = '{$arr['weight']}'
		, mtr_lot = '{$mtr_lot}'
		, mtr_price = '{$bom['bom_price']}'
		, trm_idx_location = '{$arr['trm_idx_location']}'
		, mtr_shift = '{$shift}'
		, mtr_history = 'finish|".G5_TIME_YMD."|".G5_TIME_YMDHIS."'
		, mtr_status = 'finish'
		, mtr_input_date = '".G5_TIME_YMD."'
		, mtr_reg_dt = '".G5_TIME_YMDHIS."'
		, mtr_update_dt = '".G5_TIME_YMDHIS."'
	";
	sql_query($sql,1);
	$mtr_idx = sql_insert_id();
	$result_arr['code'] = 200;
	$result_arr['message'] = 'Inserted OK!';
	$result_arr['mtr_idx'] = $mtr_idx;
	$result_arr['mtr_status'] = 'finish';
	update_item_sum2(); //material 변경사항을 반영하기 위해 item_sum테이블 업데이트함
}
else{
    $result_arr = array("code"=>599,"message"=>"error");
}

//테스트페이지로부터 호출되었으면 테스트 폼페이지로 이동
if($test){
    goto_url('./form.php?oop_idx='.$oop_idx);
}
else{
    echo json_encode( array('meta'=>$result_arr) );
}
