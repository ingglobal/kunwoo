<?php
include_once('./_common.php');

$bom_idx = $_POST['bom_idx'];
$bom_part_no = trim($_POST['bom_part_no']);
$msg = '';
$sql = "select COUNT(*) AS cnt, bom_idx
        from {$g5['bom_table']}
        where bom_status NOT IN ('delete','del','trash','cancel') AND com_idx ='".$_SESSION['ss_com_idx']."'  AND bom_part_no = '".$bom_part_no."' 
";
$row = sql_fetch($sql);
/*
echo $bom_idx;
echo gettype($bom_idx);
echo $row['bom_idx'];
echo gettype($row['bom_idx']);exit;
*/
//
if($row['cnt'] == '1'){
    if($bom_idx == $row['bom_idx']){
        $msg = 'same';
    }
    else{
        $msg = 'overlap';
    }
}
else{
   $msg = 'ok'; 
}

echo $msg;