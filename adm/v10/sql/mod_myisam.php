<?php
include_once('./_head.sub.php');

$sql = " SHOW TABLES ";
$result = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($result);$i++){
    // print_r2($row['Tables_in_epcs_kunwoo']);
    $sql1 = " alter table {$row['Tables_in_epcs_kunwoo']} engine=myisam; ";
    sql_query($sql1);
}

include_once('./_tail.sub.php');
?>