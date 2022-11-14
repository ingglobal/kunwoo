<?php
// http://localhost/icmms/adm/v10/ajax/mms_item.php?aj=del&mip_idx=3

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

//-- 디폴트 상태 (실패) --//
$response = new stdClass();
$response->result=false;

// print_r2($_REQUEST);

// delete the price info
if ($aj == "del") {

    $mip = get_table_meta('mms_item_price','mip_idx',$mip_idx);

    $sql = "DELETE FROM {$g5['mms_item_price_table']} WHERE mip_idx = '".$mip_idx."'";
    // echo $sql.'<br>';
    sql_query($sql,1);

    // update the latest price info of mms_item table
    update_mmi_price($mip['mmi_idx']);

    $response->result = true;
 	$response->list = $arr;
	$response->msg = "정보 처리 성공!";

}
else {
	$response->err_code='E00';
	$response->msg = "잘못된 접근입니다. \n정상적인 방법으로 이용해 주세요.";
}


$response->sql = $sql;

echo json_encode($response);
exit;
?>