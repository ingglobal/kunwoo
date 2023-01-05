<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


/***************************
    환경설정 변수, 상수
***************************/
define('G5_USER_VER', '1.0');

// 공통 변수, 상수 선언
define('G5_USER_DIR',                       'user');
define('G5_USER_ADMIN_DIR',                 'v10');
define('G5_AJAX_DIR',                       'ajax');
define('G5_HOOK_DIR',                       'hook');
define('G5_CRON_DIR',                       'cron');
define('G5_WIDGET_DIR',                     'widget');
define('G5_DEVICE_DIR',                     'device');
define('G5_DEFAULT_COUNTRY',                'ko_KR');   //디폴트 국가_언어
define('G5_DEVICE_PATH',                    G5_PATH.'/'.G5_DEVICE_DIR);
define('G5_DEVICE_URL',                     G5_URL.'/'.G5_DEVICE_DIR);
define('G5_USER_PATH',                      G5_PATH.'/'.G5_USER_DIR);
define('G5_USER_URL',                       G5_URL.'/'.G5_USER_DIR);
define('G5_USER_CRON_PATH',                 G5_PATH.'/'.G5_USER_DIR.'/'.G5_CRON_DIR);
define('G5_USER_CRON_URL',                  G5_URL.'/'.G5_USER_DIR.'/'.G5_CRON_DIR);
define('G5_USER_THEME_PATH',                G5_THEME_PATH.'/'.G5_USER_DIR);
define('G5_USER_THEME_URL',                 G5_THEME_URL.'/'.G5_USER_DIR);
define('G5_USER_THEME_CSS_PATH',            G5_THEME_PATH.'/'.G5_USER_DIR.'/'.G5_CSS_DIR);
define('G5_USER_THEME_CSS_URL',             G5_THEME_URL.'/'.G5_USER_DIR.'/'.G5_CSS_DIR);
define('G5_USER_THEME_IMG_PATH',            G5_THEME_PATH.'/'.G5_USER_DIR.'/'.G5_IMG_DIR);
define('G5_USER_THEME_IMG_URL',             G5_THEME_URL.'/'.G5_USER_DIR.'/'.G5_IMG_DIR);
define('G5_USER_THEME_JS_PATH',             G5_THEME_PATH.'/'.G5_USER_DIR.'/'.G5_JS_DIR);
define('G5_USER_THEME_JS_URL',              G5_THEME_URL.'/'.G5_USER_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_PATH',                G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR);
define('G5_USER_ADMIN_URL',                 G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR);
define('G5_USER_ADMIN_AJAX_URL',            G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_AJAX_DIR);
define('G5_USER_ADMIN_CSS_PATH',            G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_CSS_URL',             G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_IMG_PATH',            G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_IMG_URL',             G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_JS_PATH',             G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_JS_URL',              G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_LIB_PATH',            G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_LIB_DIR);
define('G5_USER_ADMIN_LIB_URL',             G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_LIB_DIR);
define('G5_USER_ADMIN_FORM_PATH',   		G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/form');
define('G5_USER_ADMIN_FORM_URL',   		    G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/form');
define('G5_USER_ADMIN_SVG_PATH',   		    G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/svg');
define('G5_USER_ADMIN_SVG_URL',   		    G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/svg');
define('G5_USER_ADMIN_SVG_PHP_PATH',   		G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/svg_php');
define('G5_USER_ADMIN_SVG_PHP_URL',   		G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/svg_php');
define('G5_USER_ADMIN_SKIN_PATH',   		G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/skin');
define('G5_USER_ADMIN_SKIN_URL',   		    G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/skin');
define('G5_USER_ADMIN_SQL_PATH',   		    G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/sql');
define('G5_USER_ADMIN_SQL_URL',   		    G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/sql');
define('G5_USER_ADMIN_TEST_PATH',   		G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/test');
define('G5_USER_ADMIN_TEST_URL',   		    G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/test');
define('G5_USER_ADMIN_SQLS_PATH',   		G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/sqls');
define('G5_USER_ADMIN_SQLS_URL',    		G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/sqls');
define('G5_USER_ADMIN_MODAL_PATH',   		G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/modal');
define('G5_USER_ADMIN_MODAL_URL',    		G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/modal');
define('G5_USER_ADMIN_MOBILE_PATH',         G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR);
define('G5_USER_ADMIN_MOBILE_URL',          G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR);
define('G5_USER_ADMIN_MOBILE_AJAX_PATH',    G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_AJAX_DIR);
define('G5_USER_ADMIN_MOBILE_AJAX_URL',     G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_AJAX_DIR);
define('G5_USER_ADMIN_MOBILE_CSS_PATH',     G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_MOBILE_CSS_URL',      G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_CSS_DIR);
define('G5_USER_ADMIN_MOBILE_IMG_PATH',     G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_MOBILE_IMG_URL',      G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_IMG_DIR);
define('G5_USER_ADMIN_MOBILE_JS_PATH',      G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_MOBILE_JS_URL',       G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_JS_DIR);
define('G5_USER_ADMIN_MOBILE_LIB_PATH',     G5_USER_ADMIN_PATH.'/'.G5_MOBILE_DIR.'/'.G5_LIB_DIR);
define('G5_USER_ADMIN_MOBILE_LIB_URL',      G5_USER_ADMIN_URL.'/'.G5_MOBILE_DIR.'/'.G5_LIB_DIR);
define('G5_WIDGET_PATH',                    G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR);
define('G5_WIDGET_URL',                     G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR);
define('G5_WIDGET_ADMIN_PATH',              G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR.'/adm');
define('G5_WIDGET_ADMIN_URL',               G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR.'/adm');
define('G5_WIDGET_SET_PATH',                G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR.'/_set');
define('G5_WIDGET_SET_URL',                 G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR.'/_set');
define('G5_WIDGET_SKIN_PATH',               G5_ADMIN_PATH.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR.'/skin');
define('G5_WIDGET_SKIN_URL',                G5_ADMIN_URL.'/'.G5_USER_ADMIN_DIR.'/'.G5_WIDGET_DIR.'/skin');
define('G5_WIDGET_DATA_PATH',  		        G5_DATA_PATH.'/'.G5_WIDGET_DIR);
define('G5_WIDGET_DATA_URL',  		        G5_URL.'/data/'.G5_WIDGET_DIR);
define('G5_USER_CSS_PATH',                  G5_USER_PATH.'/'.G5_CSS_DIR);
define('G5_USER_CSS_URL',                   G5_USER_URL.'/'.G5_CSS_DIR);
define('G5_USER_JS_PATH',                   G5_USER_PATH.'/'.G5_JS_DIR);
define('G5_USER_JS_URL',                    G5_USER_URL.'/'.G5_JS_DIR);
define('G5_USER_IMG_URL',                   G5_USER_URL.'/'.G5_IMG_DIR);
define('G5_USER_AJAX_URL',                  G5_USER_URL.'/'.G5_AJAX_DIR);
define('G5_USER_MOBILE_PATH',               G5_USER_PATH.'/'.G5_MOBILE_DIR);
define('G5_USER_MOBILE_URL',                G5_USER_URL.'/'.G5_MOBILE_DIR);
define('G5_USER_MOBILE_SKIN_PATH',          G5_USER_PATH.'/'.G5_MOBILE_DIR.'/'.G5_SKIN_DIR);
define('G5_USER_MOBILE_SKIN_URL',           G5_USER_URL.'/'.G5_MOBILE_DIR.'/'.G5_SKIN_DIR);

define('G5_DATA_WIDGET_PERMISSION',  0707); // 디렉토리 생성시 퍼미션

// 테이블 정의
define('USER_TABLE_PREFIX',         G5_TABLE_PREFIX.'5_');
$g5['setting_table']                = USER_TABLE_PREFIX.'setting';
$g5['meta_table']                   = USER_TABLE_PREFIX.'meta';
$g5['tally_table']                  = USER_TABLE_PREFIX.'tally';
$g5['term_table']                   = USER_TABLE_PREFIX.'term';
$g5['term_relation_table']          = USER_TABLE_PREFIX.'term_relation';
$g5['ymd_table']                    = USER_TABLE_PREFIX.'ymd';
$g5['file_table']                   = USER_TABLE_PREFIX.'file';
$g5['dashboard_table']              = USER_TABLE_PREFIX.'dashboard';
$g5['mywidget_table']               = USER_TABLE_PREFIX.'mywidget';
$g5['mywidget_option_table']        = USER_TABLE_PREFIX.'mywidget_option';
$g5['widget_table']                 = USER_TABLE_PREFIX.'widget';
$g5['widget_option_table']          = USER_TABLE_PREFIX.'widget_option';
$g5['user_log_table'] 				= USER_TABLE_PREFIX.'user_log';

// kosmo 인증키
$g5['kosmo_erp_crtfckey'] = '';

$g5['wgf_svg_php_name'] = array(
	'3line_menu' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_3line_menu.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_3line_menu.php')
	,'search' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_search.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_search.php')
	,'home' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_home.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_home.php')
	,'cart' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_cart.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_cart.php')
	,'tablet' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_tablet.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_tablet.php')
	,'hp' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_hp.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_hp.php')
	,'pc' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_pc.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_pc.php')
	,'person' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_person.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_person.php')
	,'hart' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_hart.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_hart.php')
	,'close' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_close.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_close.php')
	,'arrow_up' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrow_up.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrow_up.php')
	,'arrow_down' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrow_down.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrow_down.php')
	,'arrow_left' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrow_left.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrow_left.php')
	,'arrow_right' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrow_right.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrow_right.php')
	,'arrowbar_up' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrowbar_up.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrowbar_up.php')
	,'arrowbar_down' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrowbar_down.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrowbar_down.php')
	,'arrowbar_left' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrowbar_left.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrowbar_left.php')
	,'arrowbar_right' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_arrowbar_right.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_arrowbar_right.php')
	,'rectangle' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_rectangle.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_rectangle.php')
	,'triangle_up' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_triangle.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_triangle.php')
	,'triangle_down' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_triangle_down.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_triangle_down.php')
	,'triangle_left' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_triangle_left.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_triangle_left.php')
	,'triangle_right' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_triangle_right.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_triangle_right.php')
	,'circle' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_circle.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_circle.php')
	,'reload' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_reload.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_reload.php')
	,'logout' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_logout.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_logout.php')
	,'first' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_first.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_first.php')
	,'end' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_end.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_end.php')
	,'prev' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_prev.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_prev.php')
	,'next' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_next.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_next.php')
	,'play' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_play.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_play.php')
	,'plus' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_plus.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_plus.php')
	,'minus' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_minus.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_minus.php')
	,'no_img' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_no_img.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_no_img.php')
	,'stop' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_stop.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_stop.php')
	,'pause' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_pause.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_pause.php')
	,'pen' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_pen.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_pen.php')
	,'qna' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_qna.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_qna.php')
	,'question' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_question.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_question.php')
	,'move_xy' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_move_xy.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_move_xy.php')
	,'move_x' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_move_x.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_move_x.php')
	,'move_y' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_move_y.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_move_y.php')
	,'trash' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_trash.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_trash.php')
	,'copy' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_copy.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_copy.php')
	,'today' => array('path' => G5_USER_ADMIN_SVG_PATH.'/icon_today.php', 'url' => G5_USER_ADMIN_SVG_URL.'/icon_today.php')
	,'ani_arrow_left' => array('path' => G5_USER_ADMIN_SVG_PATH.'/ani_arrow_left.php', 'url' => G5_USER_ADMIN_SVG_URL.'/ani_arrow_left.php')
	,'ani_plus' => array('path' => G5_USER_ADMIN_SVG_PATH.'/ani_icon_plus.php', 'url' => G5_USER_ADMIN_SVG_URL.'/ani_icon_plus.php')
);
$g5['wgf_check_radio_svg'] = array(
	'check' => array('path' => G5_USER_ADMIN_SVG_PATH.'/input_check.php', 'url' => G5_USER_ADMIN_SVG_URL.'/input_check.php')
	,'radio' => array('path' => G5_USER_ADMIN_SVG_PATH.'/input_radio.php', 'url' => G5_USER_ADMIN_SVG_URL.'/input_radio.php')
);


?>
