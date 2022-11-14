<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

// 게시판을 관리자단에서도 봐야 되서 추가 설정: 디폴트는 _common.php단에서 admin.lib.php 호출
//if( $board['gr_id']=='intra' && !preg_match("|\/adm\/|",trim($_SERVER['HTTP_REFERER'])) ) {
if( $board['gr_id']=='intra') {
    
    // auth_check 같은 함수 때문에 관리자단 admin.lib.php 추가함 (또 다른 문제는 $qstr을 admin.lib.php 에서 초기화한다는 게 문제다. 그래서 하단에 재설정)
    include_once(G5_ADMIN_PATH.'/admin.lib.php');
    include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');
    // 게시판 sub_menu 할당 (원래 게시판에는 sub_menu 변수가 없음)
    $sub_menu = $board['bo_1'];
    // 인트라 게시판은 직원전용
    if ($member['mb_level'] < 4)
        alert('접근이 불가능한 게시판입니다.',G5_URL);
    

    //qstr 조건 추가 { -------------------
    // 공통 qstr
    $qstr .= '&ser_com_idx='.$ser_com_idx.'&fr_date='.$fr_date.'&to_date='.$to_date.'&ser_wr_1='.$ser_wr_1.'&ser_wr_2='.$ser_wr_2.'&ser_wr_10='.$ser_wr_10;
    // 관리자단에서는 admin.lib.php에서 초기화 되므로 common.php에 있었던 부분 재선언
    if (isset($_REQUEST['sca']))  {
        $sca = clean_xss_tags(trim($_REQUEST['sca']));
        if ($sca) {
            $sca = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", $sca);
            $qstr .= '&amp;sca=' . urlencode($sca);
        }
    } else {
        $sca = '';
    }

    // AS게시판 관련
    $qstr_tables1 = array('contract');
    if( in_array($bo_table,$qstr_tables1) ) {
        $qstr .= '&ser_wr_5='.$ser_wr_5.'&ser_wr_6='.$ser_wr_6.'&ser_wr_7='.$ser_wr_7;
    }
    // 수정, 간수게시판 관련
    $qstr_tables1 = array('maintain1','maintain2');
    if( in_array($bo_table,$qstr_tables1) ) {
        $qstr .= '&pl_date='.$pl_date.'&ser_mb_name_worker='.$ser_mb_name_worker.'&ser_wr_5='.$ser_wr_5;
    }
    // 작업게시판 관련
    $qstr_tables2 = array('cart1');
    if( in_array($bo_table,$qstr_tables2) ) {
        $qstr .= '&ser_ct_id='.$ser_ct_id;
    }
    // 업체게시판 관련
    $qstr_tables3 = array('company1');
    if( in_array($bo_table,$qstr_tables3) ) {
        $qstr .= '&ser_com_idx='.$ser_com_idx;
    }
    // } qstr 조건 추가 -------------------
    

}

include_once(G5_PATH.'/head.sub.php');
//include_once(G5_USER_ADMIN_PATH.'/admin.head.php');   // 당장은 분리해서 관리할 필요 없음
//include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// jquery-ui css

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.min.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.structure.min.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.theme.min.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/bwg_timepicker.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/colpick/colpick.css">', 1);

// 사용자 설정 css
if(is_file(G5_USER_ADMIN_CSS_PATH.'/user.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/user.css">',1);
// 팝업창 관련 css
if(is_file(G5_USER_ADMIN_CSS_PATH.'/user_popup.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/user_popup.css">',1);

add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.min.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_timepicker.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/colpick/colpick.js"></script>',1);

add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/common.js"></script>', 0);
echo PHP_EOL;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>
var g5_user_admin_url = "<?php echo G5_USER_ADMIN_URL; ?>";
var g5_user_admin_ajax_url = "<?php echo G5_USER_ADMIN_AJAX_URL; ?>";
var dta_types = ['타입명','온도','토크','전류','전압','진동','소리','습도','압력','속도'];
var w = '<?php echo $w; ?>';
</script>
