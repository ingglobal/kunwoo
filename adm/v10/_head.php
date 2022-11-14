<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


// 게시판을 관리자단에서도 봐야 되서 추가 설정: 디폴트는 _common.php단에서 admin.lib.php 호출
if( $board['gr_id']=='intra') {

    // auth_check 같은 함수 때문에 관리자단 admin.lib.php 추가함 (또 다른 문제는 $qstr을 admin.lib.php 에서 초기화한다는 게 문제다. 그래서 하단에 재설정)
    include_once(G5_ADMIN_PATH.'/admin.lib.php');
    include_once(G5_ADMIN_PATH.'/shop_admin/admin.shop.lib.php');

    // // 게시판 sub_menu 할당 (원래 게시판에는 sub_menu 변수가 없음) > 변수위치 이동 /extend/user.10.board.php
    // $sub_menu = $board['bo_1'];

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
    // } qstr 조건 추가 -------------------

}

if(G5_IS_MOBILE){
    include_once(G5_USER_ADMIN_MOBILE_PATH.'/admin.head.php');
}
else {
    // include_once(G5_ADMIN_PATH.'/admin.head.php');
    include_once(G5_USER_ADMIN_PATH.'/admin.head.php');
}
//include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

// jquery-ui css
//add_stylesheet('<link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" rel="stylesheet" />', 0);
//add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.min.css">', 1);
add_stylesheet('<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-darkness/jquery-ui.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.structure.min.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/bwg_timepicker.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/colpick/colpick.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/datetimepicker/jquery.datetimepicker.min.css">',1);


if( $board['gr_id']=='intra' && ($g5['file_name'] == 'board' || $g5['file_name'] == 'write')) { // 게시판인 경우
    if(is_file(G5_USER_ADMIN_CSS_PATH.'/board.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/board.css">',1);
}

add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.min.js"></script>',0);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker-ko.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_timepicker.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/colpick/colpick.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/datetimepicker/jquery.datetimepicker.full.min.js"></script>',1);
?>
<script>
var g5_user_admin_url = "<?php echo G5_USER_ADMIN_URL; ?>";
var g5_user_admin_ajax_url = "<?php echo G5_USER_ADMIN_AJAX_URL; ?>";
var dta_types = ['타입명','온도','토크','전류','전압','진동','소리','습도','압력','속도'];
var w = '<?php echo $w; ?>';
$(function(){
    // Test db display, Need to know what DB is using.
    <?php
    if(G5_MYSQL_DB!='epcs_www' && !G5_IS_MOBILE) {
        echo "$('#ft p').prepend('<span style=\"color:darkorange;\">".G5_MYSQL_DB."</span>');";
    }
    ?>
});
</script>
