<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 목록 링크 재설정
$list_href = './board.php?bo_table='.$bo_table.'&amp;page='.$page.$qstr;

// 쓰기 링크
$write_href = '';
if ($member['mb_level'] >= $board['bo_write_level'])
    $write_href = './write.php?bo_table='.$bo_table.'&ct_id='.$ct_id;


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


// 최고, 그룹관리자라면 글 복사, 이동 가능
$copy_href = $move_href = '';
if ($write['wr_reply'] == '' && ($is_admin == 'super' || $is_admin == 'group' || in_array($member['mb_2'],$board['worker_trm_idxs']) )) {
    $copy_href = './move.php?sw=copy&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
    $move_href = './move.php?sw=move&amp;bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
}


?>