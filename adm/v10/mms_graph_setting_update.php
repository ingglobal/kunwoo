<?php
$sub_menu = "919110";
include_once("./_common.php");

auth_check($auth[$sub_menu],'w');

// print_r2($_REQUEST);
for($i=0;$i<sizeof($_REQUEST['dta_label']);$i++) {
    // echo $i.'. '.$_REQUEST['dta_label'][$i].'<br>';
    // label exists.
    if($_REQUEST['dta_label'][$i]) {
        // echo $_REQUEST['dta_type'][$i].'-'.$_REQUEST['dta_no'][$i].'='.$_REQUEST['dta_label'][$i].'<br>';
        $a['mta_db_table'] = 'mms';
        $a['mta_db_id'] = $mms_idx;
        $a['mta_key'] = 'dta_type_label-'.$_REQUEST['dta_type'][$i].'-'.$_REQUEST['dta_no'][$i];
        $a['mta_value'] = $_REQUEST['dta_label'][$i];
        meta_update($a);
        unset($a);
    }
}

// exit;
alert('이름표(레이블) 입력 성공!','./mms_graph_setting.php?mms_idx='.$mms_idx, false);
?>