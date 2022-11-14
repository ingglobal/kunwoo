<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$file_count = (int)$board['bo_upload_count'];

if ($w == '') {
    $email = '';
    $write['wr_10'] = 'ok';

} else if ($w == 'u') {

    $file = get_file($bo_table, $wr_id);
    if($file_count < $file['count'])
        $file_count = $file['count'];
    
    // 상품 정보 추출
    $ct1 = get_table_meta('g5_shop_cart','ct_id',$write['ct_id']);
    //print_r3($ct1);
    if($ct1['ct_id']) {
        $write['ct_info'] = '<b>상품:</b> '.$ct1['it_name'].'('.number_format($ct1['ct_price']).'), <b>업체명:</b> '.$write['wr_1'].', <b>영업자:</b> '.$write['mb_name_saler'].'';
    }
    else
        $write['ct_info'] = '선택된 상품이 존재하지 않습니다. 상품을 선택하세요.';

    
} else if ($w == 'r') {


}

?>