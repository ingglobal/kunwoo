<?php
$sub_menu = "985310";
include_once('./_common.php');

$sql = " SELECT bom_idx,bom_name,bct_id 
            FROM {$g5['bom_table']} 
            WHERE bct_id = '' 
                AND bom_type = 'product' 
                AND bom_status NOT IN ('delete','trash')
                AND com_idx = 8
";
$result = sql_query($sql,1);
//print_r2($result->num_rows);
//echo "<br>";
$i = 0;
if($result->num_rows){
    for($i=0;$row=sql_fetch_array($result);$i++){
        //print_r2($row);
        $c1 = rand(1,8).'0';
        $c2 = rand(1,2).'0';
        $c3 = rand(1,2).'0';
        $c4 = rand(1,4).'0';
        $cat_num = $c1.$c2.$c3.$c4;
        $u_sql = " UPDATE {$g5['bom_table']} SET bct_id = '{$cat_num}' WHERE bom_idx = '{$row['bom_idx']}' ";
        sql_query($u_sql,1);
    }
}
echo $i."개 실행완료";