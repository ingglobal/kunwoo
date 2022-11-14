<?php
include('./_common.php');
$len = strlen($val);
$csql = " SELECT bct_id,bct_name FROM {$g5['bom_category_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bct_id REGEXP '^.{".($len+2)."}$' AND bct_id LIKE '{$val}%' ";
//echo $csql;exit;
$result = sql_query($csql,1);
$arr = array("error"=>"none");
if($result->num_rows){
    $arr = array();
    for($i=0;$row=sql_fetch_array($result);$i++){
        $arr[$row['bct_id']] = $row['bct_name'];
    }
}

echo json_encode($arr);