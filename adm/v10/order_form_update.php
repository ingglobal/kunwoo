<?php
$sub_menu = "920100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

/*
Array
(
    [w] => 
    [sfl] => 
    [stx] => 
    [sst] => 
    [sod] => 
    [page] => 
    [token] => ab7b7f9a4497338722f5b5f3a196e026
    [com_idx] => 8
    [ord_idx] => ord_idx
    [sca] => 
    [com_idx_customer] => 9
    [com_name] => 거래처2
    [ord_price] => 61,374
    [ord_ship_date] => 2021-07-31
    [ord_type] => ok
    [serialized] => [{\"id\":1,\"depth\":0,\"bom_name\":\"FRT 고정 그레이 쌍침\",\"bom_idx_child\":\"1\",\"bit_count\":\"1\"},{\"id\":2,\"depth\":0,\"bom_name\":\"FRT 고정 블랙 쌍침\",\"bom_idx_child\":\"7\",\"bit_count\":\"2\"},{\"id\":3,\"depth\":0,\"bom_name\":\"FRT 고정 블랙 쌍침(PE)\",\"bom_idx_child\":\"9\",\"bit_count\":\"3\"}]
)
Array
(
    [0] => Array
        (
            [id] => 1
            [depth] => 0
            [bom_name] => FRT 고정 그레이 쌍침
            [bom_idx_child] => 1
            [bit_count] => 1
        )

    [1] => Array
        (
            [id] => 2
            [depth] => 0
            [bom_name] => FRT 고정 블랙 쌍침
            [bom_idx_child] => 7
            [bit_count] => 2
        )

    [2] => Array
        (
            [id] => 3
            [depth] => 0
            [bom_name] => FRT 고정 블랙 쌍침(PE)
            [bom_idx_child] => 9
            [bit_count] => 3
        )

)

Array
(
    [ori_idx] => 2
    [com_idx] => 8
    [com_idx_customer] => 0
    [ord_idx] => 1
    [bom_idx] => 7
    [ori_count] => 2
    [ori_price] => 10204
    [ori_status] => ok
    [ori_reg_dt] => 2021-07-22 16:26:42
    [ori_update_dt] => 2021-07-22 16:26:42
)
*/

$data = json_decode(stripslashes($_POST['serialized']),true);
$data_k = array();
foreach($data as $dta){
    $data_k[$dta['bom_idx_child']] = $dta;
}
//print_r2($data_k);

if(count($data) == 0) alert('적어도 상품 한 개 이상은 등록해 주세요.');

$ord_price = str_replace(',','',trim($ord_price));

$sql_common = " com_idx = '{$com_idx}',
                com_idx_customer = '{$com_idx_customer}',
                ord_price = '{$ord_price}',
                ord_ship_date = '{$ord_ship_date}',
                ord_status = '{$ord_status}',
                ord_date = '{$ord_date}',
";

if($w == ''){
    $sql_common .= " ord_reg_dt = '".G5_TIME_YMDHIS."',ord_update_dt = '".G5_TIME_YMDHIS."' ";

    $sql = " INSERT into {$g5['order_table']} SET
                {$sql_common}
    ";
    sql_query($sql,1);
	$ord_idx = sql_insert_id();
}
else if($w == 'u'){
    $sql_common .= " ord_update_dt = '".G5_TIME_YMDHIS."' ";

    $sql = " UPDATE {$g5['order_table']} SET
                {$sql_common}
            WHERE ord_idx = '{$ord_idx}'
    ";
    sql_query($sql,1);
}
else if($w == 'd'){
    $sql = " UPDATE {$g5['order_table']} SET
                ord_status = 'trash'
            WHERE ord_idx = '{$ord_idx}'
    ";
    sql_query($sql,1);
    $sql_ori = " UPDATE {$g5['order_item_table']} SET
                    ori_status = 'trash'
                WHERE ord_idx = '{$ord_idx}'  
    ";
    sql_query($sql_ori,1);
}

if($w != 'd'){
    $old_boms = array();
    $tmp_boms = array();
    $old_r = sql_query(" SELECT bom_idx FROM {$g5['order_item_table']} WHERE ord_idx = '{$ord_idx}' ");
    for($i=0;$old=sql_fetch_array($old_r);$i++) array_push($old_boms,$old['bom_idx']);
    foreach($data as $dv) array_push($tmp_boms,$dv['bom_idx_child']);
    //print_r2($old_boms);
    //print_r2($tmp_boms);

    $del_boms = array_diff($old_boms,$tmp_boms);//삭제할 데이터
    //print_r2($del_boms); 
    
    $add_boms = array_diff($tmp_boms,$old_boms);//추가될 데이터
    //print_r2($add_boms); 
    
    $mod_boms = array_diff($old_boms,$del_boms);//수정할 데이터
    //print_r2($mod_boms); 

    if(count($del_boms)){ //삭제해야 할 데이터가 있다면
        foreach($del_boms as $delb){
            $sql = " UPDATE {$g5['order_item_table']} SET
                        ori_status = 'trash'
                    WHERE ord_idx = '{$ord_idx}' AND bom_idx = '{$delb}'
            ";
            sql_query($sql,1);
        }
    }

    if(count($add_boms)){ //추가해야할 데이터가 있다면
        foreach($add_boms as $addb){
            $sql = " INSERT into {$g5['order_item_table']} SET
                        com_idx = '{$com_idx}',
                        com_idx_customer = '{$com_idx_customer}',
                        ord_idx = '{$ord_idx}',
                        bom_idx = '{$data_k[$addb]['bom_idx_child']}',
                        ori_count = '{$data_k[$addb]['bit_count']}',
                        ori_price = '{$data_k[$addb]['ori_price']}',
                        ori_status = 'ok',
                        ori_reg_dt = '".G5_TIME_YMDHIS."',
                        ori_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($sql,1);
			$ori_idx = sql_insert_id();
			
            /*
			//바로 order_item 레코드에 해당 하는 출하order_out 테이블의 레코드를 1:1로 미리 생성한다.
			$sql_ot = " INSERT into {$g5['order_out_table']} SET
					com_idx = '{$com_idx}',
					com_idx_customer = '{$com_idx_customer}',
					ord_idx = '{$ord_idx}',
					ori_idx = '{$ori_idx}',
					oro_count = '{$data_k[$addb]['bit_count']}',
					oro_date_plan = '',
					oro_date = '',
					oro_memo = '',
                    com_idx_shipto = '{$com_idx_customer}',
					oro_status = 'pending',
					oro_reg_dt = '".G5_TIME_YMDHIS."',
					oro_update_dt = '".G5_TIME_YMDHIS."' 
			";
			sql_query($sql_ot,1);
            */
        }
    }

    if(count($mod_boms)){ //수정해야할 데이터가 있다면
        foreach($mod_boms as $modb){
            $sql = " UPDATE {$g5['order_item_table']} SET
                        com_idx = '{$com_idx}',
                        com_idx_customer = '{$com_idx_customer}',
                        ord_idx = '{$ord_idx}',
                        bom_idx = '{$data_k[$modb]['bom_idx_child']}',
                        ori_count = '{$data_k[$modb]['bit_count']}',
                        ori_price = '{$data_k[$modb]['ori_price']}',
                        ori_status = 'ok',
                        ori_update_dt = '".G5_TIME_YMDHIS."'
                    WHERE ord_idx = '{$ord_idx}' AND bom_idx = '{$modb}'
            ";
            sql_query($sql,1);
        }
    }
}

$qstr .= '&sca='.$sca; //.'&file_name='.$file_name 추가로 확장해서 넘겨야 할 변수들
//goto_url('./order_list.php?'.$qstr, false);
goto_url('./order_form.php?'.$qstr.'&w=u&ord_idx='.$ord_idx, false);