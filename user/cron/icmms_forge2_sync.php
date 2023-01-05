<?php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1

$g5['title'] = '동기화';
include_once('./_head.sub.php');
include_once('./_head.icmms.php');
//http://myadmin.icmms.co.kr/db_sql.php?db=icmms_www
//-- 화면 표시
$countgap = ($demo||$db_id) ? 10 : 20;    // 몇건씩 보낼지 설정
$maxscreen = ($demo||$db_id) ? 30 : 100;  // 몇건씩 화면에 보여줄건지?/
$sleepsec = 200;     // 천분의 몇초간 쉴지 설정 (1sec=1000)
/*
g5_1_data_output_67 (필요없음)

g5_1_data_measure_67_%  

g5_1_data_downtime      (com_idx=14, imp_idx=35, mms_idx=67)
g5_1_data_error         (com_idx=14, imp_idx=35, mms_idx=67)
g5_1_data_error_sum     (com_idx=14, imp_idx=35, mms_idx=67)
*/

// $icsql1 = " SHOW TABLES LIKE g5_1_data_measure_67_% ";
$icsql1 = " SHOW TABLES LIKE 'g5_1_data_measure_67_%' ";
$res1 = $icmms_connect_db_pdo->query($icsql1);
$tbl_list = array();
while($row1 = $res1->fetch(PDO::FETCH_NUM)){
    array_push($tbl_list,$row1[0]);
}
print_r2($tbl_list);

// print_r2($g5['pdo_yn']);exit;
//show tables LIKE 'g5_1_data_measure_67%' AS press2
$table1 = 'g5_1_data_measure_67_9_1';
$icsql = " SELECT * FROM {$table1} LIMIT 5 ";
$res = $icmms_connect_db_pdo->query($icsql, PDO::FETCH_ASSOC);
// print_r2($res);
foreach($res as $row){
    print_r2($row);
}


include_once ('./_tail.icmms.php');
?>
