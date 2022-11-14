<?php
$sub_menu = "985300";
include_once('./_common.php');

// print_r3($_SESSION['ss_com_idx']);
if($_SESSION['ss_com_idx']&&$member['mb_level']>=8) {
    $com = get_table_meta('company','com_idx',$_SESSION['ss_com_idx']);
    // print_r2($com);
    $com_name = $com['com_name'] ? ' ('.$com['com_name'].')' : '';
}

$g5['title'] = '데이터생성 대시보드'.$com_name;
//include_once('./_top_menu_default.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}


?>



<?php
include_once ('./_tail.php');