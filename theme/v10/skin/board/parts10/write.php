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
    $write['wr_3'] = G5_TIME_YMD;
    $write['wr_10'] = $board['set_default_status'];

} else if ($w == 'u') {

    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count'])
        $file_count = $file['count'];
    
} else if ($w == 'r') {


}
// print_r2($write);
// print_r2( json_decode($write['wr_7']) );

// towhom_info variable
$wr_7s = json_decode($write['wr_7'], true);
if(is_array($wr_7s)) {
    foreach($wr_7s as $k1 => $v1) {
        // echo $k1.'<br>';
        // print_r2($v1);
        for($i=0;$i<sizeof($v1);$i++) {
            $towhom_li[$i][$k1] = $v1[$i];
        }
    }
}
// print_r2($towhom_li);

?>