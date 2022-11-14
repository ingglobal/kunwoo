<?php
include_once('./_common.php');

if(!$ser_com_idx)
    return;

$com = get_table_meta('company','com_idx',$ser_com_idx);
//print_r2($com);
?>
<div class="title_com_name"><b>업체명</b>: <?php echo $com['com_name']?></div>
