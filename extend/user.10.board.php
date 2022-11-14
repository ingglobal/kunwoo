<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 인트라넷 게시판 아이디들 배열 설정
$bo_ids = sql_fetch(" SELECT GROUP_CONCAT(bo_table) AS bo_tables FROM {$g5['board_table']} WHERE gr_id = 'intra' GROUP BY gr_id ");
$g5['bo_table_intra'] = explode(",",$bo_ids['bo_tables']);
//print_r3($g5['bo_table_intra']);
//exit;

    
// 인트라 게시판인 경우 관리자단이라고 봐야 함
if($board['gr_id']=='intra') {

    // intra board is located in admin mode, you have to set $auth array to begin with.
    $auth = array();
    $sql = " select au_menu, au_auth from {$g5['auth_table']} where mb_id = '{$member['mb_id']}' ";
    $result = sql_query($sql);
    for($i=0; $row=sql_fetch_array($result); $i++)
    {
        $auth[$row['au_menu']] = $row['au_auth'];
    }
    // print_r2($auth);

    // 게시판 sub_menu 할당 (원래 게시판에는 sub_menu 변수가 없음)
    $sub_menu = $board['bo_1'];

    // 운영관리 조직코드 배열
    // print_r3($board);
    $admin_trm_idxs = explode(',', preg_replace("/\s+/", "", $board['bo_3']).',super');
    $admin_trm_idxs = array_filter($admin_trm_idxs); // 빈배열 제거

    // 게시판 운영관리 권한이 있으면 게시판 그룹 관리자라고 강제설정 (_head 보다 앞단에 와야 하므로 extend에 위치해야 함)
    if( $is_admin!='super' && $is_admin!='group' && in_array($member['mb_2'],$admin_trm_idxs) ) {
        $is_admin = 'group';
        $board['bo_admin'] = $member['mb_id'];
        $group['gr_admin'] = $member['mb_id'];
    }

    // if you have "d" auth, you are $is_admin = 'board';
    // echo $auth[$sub_menu];
    // exit;
    if( $w == 'u' && preg_match("/d/",$auth[$sub_menu]) ) {
        $is_admin = 'board';
        $board['bo_admin'] = $member['mb_id'];
    }
    if( $g5['file_name'] == 'delete.user' && preg_match("/d/",$auth[$sub_menu]) ) {
        $is_admin = 'board';
        $board['bo_admin'] = $member['mb_id'];
    }
    
    //qstr 조건 추가는 관리자단 /adm/v10/_head 상단에 추가되었습니다.

    // 글삭제 권한이 있는 경우는 $id_admin 권한을 부여한다.
    // /bbs/write.php 파일 내부 line 34 is referred.
    // run_event('bbs_write', $board, $wr_id, $w);
	// add_event('bbs_write', 'u_bbs_write', 10, 3); // 3 is the count of variables passed.
	// function u_bbs_write($board, $wr_id, $w){
    //     global $member,$is_admin,$sub_menu;
    //     $is_admin = $member['mb_id'];
    //     // print_r2($board);
    //     // echo $wr_id.'<br>';
    //     // echo $w.'<br>';
    //     echo $sub_menu.' << <br>';
    //     exit;

    // }

}


// 관리자단일 때 설정
if(defined('G5_IS_ADMIN')) {



}

?>