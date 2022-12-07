<?php
include_once('./_common.php');

$oop_idx = $_POST['oop_idx'];
$mtr_bundle = trim($_POST['mtr_bundle']);
$msg = '';
$sql = "select COUNT(*) AS cnt, bom_idx, mtr_lot, mtr_heat, mtr_bundle
        from {$g5['material_table']}
        where mtr_status NOT IN ('delete','del','trash','cancel') AND com_idx ='".$_SESSION['ss_com_idx']."'  AND mtr_bundle = '".$mtr_bundle."' AND mtr_type = 'material'
";
$row = sql_fetch($sql);
/*
echo $oop_idx;
echo gettype($oop_idx);
echo $row['oop_idx'];
echo gettype($row['oop_idx']);exit;
*/
//
if($row['cnt']){
    $msg = 'overlap';
}
else{
   $msg = 'no'; 
}

echo $msg;