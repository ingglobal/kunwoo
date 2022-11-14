<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$file_count = (int)$board['bo_upload_count'];


// 수정, 삭제 링크 재설정
$update_href = '';
// 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
if (($member['mb_id'] && ($member['mb_id'] === $write['mb_id'])) || preg_match("/d/",$auth[$sub_menu]) || $is_admin) {
    $update_href = './write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.$qstr;
    set_session('ss_delete_token', $token = uniqid(time()));
    $delete_href = $board_skin_url.'/delete.user.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;token='.$token.'&amp;page='.$page.urldecode($qstr);
}
else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
    $update_href = './password.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id.'&amp;page='.$page.$qstr;
    if($is_admin!='super')
        $delete_href ='';
}


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