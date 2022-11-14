<?php
$sub_menu = "920110";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

//print_r2($_POST);exit;
/*
[w] =>
[oro_1] => 123
[oro_2] => 
[oro_3] => 
[oro_4] => 
[oro_5] => 
[oro_6] => 
[oro_7] => 
[oro_8] => 
[sca] => 
[com_idx_customer] => 34
[com_name] => (주)다스
[ord_idx] => 40
[ori_idx] => 
[bom_idx] => 3376
[bom_name] => 바이오나파블랙_B/COVER-FRT
[oro_count] => 123
[oro_date_plan] => 2021-12-30
[oro_date] => 
[com_idx_shipto] => 34
[com_name2] => (주)다스
[oro_status] => pending
[oro_memo] =>
*/

//고객처를 입력해주세요
// if(!$_POST['com_idx_customer'])
//     alert('고객처를 선택해 주세요.');

//수주idx가 반드시 넘어와야 한다
if(!$_POST['ord_idx'])
    alert('수주번호를 입력해 주세요.');

//BOMidx가 반드시 넘어와야 한다
if(!$_POST['bom_idx'])
    alert('상품을 선택해 주세요.');

//출하수량 반드시 넘어와야 한다
if(!$_POST['oro_count'])
    alert('출하수량을 입력해 주세요.');

//출하예정일을 입력해 주세요
if(!$_POST['oro_date_plan'] || $_POST['oro_date_plan'] == '0000-00-00')
    alert('출하예정일을 입력해 주세요');

//출하처를 입력해주세요
if(!$_POST['com_idx_shipto'])
    alert('출하처를 선택해 주세요.');

//해당 BOM데이터 추출
$bom = sql_fetch(" SELECT * FROM {$g5['bom_table']} WHERE bom_idx = '{$bom_idx}' ");
//해당 수주ID(ord_idx)의 ori_idx가 존재하는지 확인하고 없으면 등록해라
if($w == ''){
    $ori = sql_fetch(" SELECT ori_idx FROM {$g5['order_item_table']}
            WHERE ord_idx = '{$ord_idx}'
                AND ori_status NOT IN('delete','del','trash')
                AND bom_idx = '{$bom_idx}'    
    ");

    if(!$ori['ori_idx']) {
        $ori_sql = " INSERT INTO {$g5['order_item_table']} SET
            com_idx = '{$_SESSION['ss_com_idx']}'
            ,com_idx_customer = '{$com_idx_customer}'
            ,ord_idx = '{$ord_idx}'
            ,bom_idx = '{$bom_idx}'
            ,ori_count = '{$oro_count}'
            ,ori_price = '{$bom['bom_price']}'
            ,ori_status = 'ok'
            ,ori_reg_dt = '".G5_TIME_YMDHIS."'
            ,ori_update_dt = '".G5_TIME_YMDHIS."'
        ";
        //echo $ori_sql;
        sql_query($ori_sql,1);
        $ori_idx = sql_insert_id();
    }
    //선택한 상품이 해당 수주번호로 등록된 출하목록중에 포함되어 있는 상품은 등록할 수 없다.
    $oro = sql_fetch(" SELECT COUNT(ori_idx) AS cnt FROM {$g5['order_out_table']} 
                WHERE ord_idx = '{$ord_idx}'
                    AND ori_idx = '{$ori_idx}'
                    AND oro_status NOT IN('delete','del','trash','cancel')
    ");
    if($oro['cnt'])
        alert("선택하신 제품이 동일한 수주번호의 출하목록에 이미 포함되어 있습니다.");
}


$sql_common = "
    com_idx = '{$_SESSION['ss_com_idx']}'
    ,com_idx_customer = '{$com_idx_customer}'
    ,ord_idx = '{$ord_idx}'
    ,ori_idx = '{$ori_idx}'
    ,oro_count = '{$oro_count}'
    ,oro_date_plan = '{$oro_date_plan}'
    ,oro_date = '{$oro_date}'
    ,oro_memo = '{$oro_memo}'
    ,com_idx_shipto = '{$com_idx_shipto}'
    ,oro_status = '{$oro_status}'
    ,oro_update_dt = '".G5_TIME_YMDHIS."'
    ,oro_1 = '{$oro_1}'
    ,oro_2 = '{$oro_2}'
    ,oro_3 = '{$oro_3}'
    ,oro_4 = '{$oro_4}'
    ,oro_5 = '{$oro_5}'
    ,oro_6 = '{$oro_6}'
    ,oro_7 = '{$oro_7}'
    ,oro_8 = '{$oro_8}'
";

if($w == '' || $w == 'c'){
    $sql = "INSERT INTO {$g5['order_out_table']} SET 
               {$sql_common} 
                , oro_reg_dt = '".G5_TIME_YMDHIS."'
	";
    sql_query($sql,1);
	$oro_idx = sql_insert_id();
}
else if ($w == 'u') {

	$oro = get_table_meta('order_out', 'oro_idx', $oro_idx);
    if (!$oro['oro_idx'])
		alert('존재하지 않는 자료입니다.');
 
    $sql = "UPDATE {$g5['order_out_table']} SET 
                {$sql_common}
            WHERE oro_idx = '{$oro_idx}' 
	";
    // echo $sql.'<br>';
    sql_query($sql,1);
        
}
else if ($w == 'd') {

    $sql = "UPDATE {$g5_table_name} SET
                oro_status = 'trash'
            WHERE oro_idx = '{$oro_idx}'
    ";
    sql_query($sql,1);
    goto_url('./order_out_list.php?'.$qstr, false);
    
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


if($schrows)
    $qstr .= '&schrows='.$schrows;

// exit;
goto_url('./order_out_list.php?'.$qstr.'&w=u&oro_idx='.$oro_idx, false);
?>