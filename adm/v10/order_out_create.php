<?php
$sub_menu = "920100";
include_once('./_common.php');

if( auth_check($auth[$sub_menu],"w",1) ) {
    alert('메뉴 접근 권한이 없습니다.');
}

if(!$ord_idx)
    alert('주문번호가 제대로 넘어오지 않았습니다.');
/*
$sql = " SELECT * FROM {$g5['order_table']} WHERE ord_idx = '{$ord_idx}' ";
$ord = sql_fetch($sql);
*/

//최조 출하생성인 경우
if(!$add){
	//$ord_idx로 이미 등록된 출하계획 데이터가 존재하는지 확인하고 있으면 튕겨낸다.
	$oro_sql = " SELECT COUNT(oro_idx) AS cnt FROM {$g5['order_out_table']} WHERE oro_status NOT IN('delete','del','trash') AND ord_idx = '{$ord_idx}' ";
	$oro_cnt = sql_fetch($oro_sql);
	if($oro_cnt['cnt']){
		alert('선택하신 수주ID에 해당하는 출하계획 데이터가 이미 존재합니다.');
	}

	$sql_it = " SELECT * FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord_idx}' AND ori_status NOT IN('trash','delete','del','cancel') ORDER BY ori_idx,ori_reg_dt DESC ";
}
//추가적인 출하생성인 경우
else {
	//출하계획에 잡히지 않은 ori가 레코드가 존재하는지 확인하고 없으면 튕겨낸다.
	$addSql = " SELECT COUNT(*) AS cnt ,(
					SELECT COUNT(*) FROM {$g5['order_out_table']} WHERE ord_idx = '{$ord_idx}'
						AND oro_status NOT IN('delete','del','trash')
				) AS cnt2 
				FROM {$g5['order_item_table']} AS ori
				LEFT JOIN {$g5['order_out_table']} AS oro ON ori.ori_idx = oro.ori_idx
					WHERE ori.ord_idx = '{$ord_idx}' 
						AND oro.oro_idx IS NULL 
						AND ori.ori_status NOT IN('delete','del','trash')
	";
	$addOri = sql_fetch($addSql);
	if(!$addOri['cnt']){
		alert('선택하신 수주ID에 관하여 출하계획으로 추가해야할 상품이 더이상 존재하지 않습니다.\n방금전 누군가에 의해 추가등록 되었을지도 모릅니다.');
	}

	$sql_it = " SELECT ori.ori_idx, ori.com_idx, ori.com_idx_customer, ori.ord_idx, ori.ori_count
					FROM {$g5['order_item_table']} AS ori
					LEFT JOIN {$g5['order_out_table']} AS oro ON ori.ori_idx = oro.ori_idx
					 	WHERE ori.ord_idx = '{$ord_idx}' 
							AND oro.oro_idx IS NULL
							AND ori.ori_status NOT IN('trash','delete','del','cancel')
						ORDER BY ori.ori_idx,ori.ori_reg_dt DESC ";
}


$result = sql_query($sql_it,1);
//$total_count = sql_num_rows($result);
// echo $sql_it."<br>";
// print_r2($result);
// exit;
$oro_date_plan = ($g5['setting']['set_oro_date_plan_cnt'])?get_dayAddDate($ord_date,$g5['setting']['set_oro_date_plan_cnt']):get_dayAddDate($ord_date,5);
for($i=0;$row=sql_fetch_array($result);$i++){
	$customer_com_idx = ($row['com_idx_customer']) ? $row['com_idx_customer'] : $_SESSION['ss_com_idx'];
    $sql_ot = " INSERT into {$g5['order_out_table']} SET
					com_idx = '{$row['com_idx']}',
					com_idx_customer = '{$customer_com_idx}',
					ord_idx = '{$ord_idx}',
					ori_idx = '{$row['ori_idx']}',
					oro_count = '{$row['ori_count']}',
					oro_date_plan = '{$oro_date_plan}',
					oro_date = '',
					oro_memo = '',
                    com_idx_shipto = '{$customer_com_idx}',
					oro_status = 'pending',
					oro_reg_dt = '".G5_TIME_YMDHIS."',
					oro_update_dt = '".G5_TIME_YMDHIS."',
					oro_1 = '{$row['ori_count']}'
    ";
    //print_r2($sql_ot);
    sql_query($sql_ot,1);
}

goto_url('./order_list.php?'.$qstr, false);