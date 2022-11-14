<?php
include_once('./_common.php');

$ord_date = $_POST['ord_date'];
$msg = '';
$sql = "select COUNT(*) AS cnt
        from {$g5['order_table']}
        where ord_status NOT IN ('delete','del','trash','cancel') AND com_idx ='".$_SESSION['ss_com_idx']."'  AND ord_date = '".$ord_date."' 
";
$row = sql_fetch($sql);

//
if($row['cnt']){
    $msg = 'overlap';
}
else{
    $msg = 'ok'; 
}

echo $msg;