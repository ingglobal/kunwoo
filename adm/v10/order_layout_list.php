<?php
$sub_menu = "920105";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');
$g5['title'] = '수주관리목록';
include_once('./_head.php');
//echo $g5['container_sub_title'];
$pno_arr = array();
$sql_pno = " SELECT DISTINCT bom.bom_idx,bom.bom_part_no,bom.bom_name FROM {$g5['bom_table']} AS bom
                LEFT JOIN {$g5['order_item_table']} AS ori ON bom.bom_idx = ori.bom_idx
                LEFT JOIN {$g5['order_table']} AS ord ON ori.ord_idx = ord.ord_idx 
            WHERE  ord.ord_date >= DATE_ADD(NOW(),interval-7 day)
                AND ord.ord_status NOT IN('trash','delete','del','cancel')
                AND ori.ori_status NOT IN('trash','delete','del','cancel')            
";
//echo $sql_pno;
$p_result = sql_query($sql_pno,1);
print_r2($p_result);
for($i=0;$prow=sql_fetch_array($p_result);$i++){
    print_r2($prow);
}
?>



<?php
include_once('./_tail.php');

