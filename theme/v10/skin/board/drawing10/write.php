<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$file_count = (int)$board['bo_upload_count'];

// 설비 정보 추출
$mms = get_table_meta('mms','mms_idx',$write['wr_2']);
$com = get_table_meta('company','com_idx',$mms['com_idx']);
//print_r3($mms);
if(!$mms['mms_idx']) {
    $write['mms_info'] = '선택된 설비가 없습니다. 설비를 선택하세요.';
}
// print_r2($mms);

if ($w == '') {
    $write['mms_idx'] = 0;
    $write['wr_10'] = 'ok';

} else if ($w == 'u') {

    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count'])
        $file_count = $file['count'];
    
} else if ($w == 'r') {


}
// print_r2($write);
?>