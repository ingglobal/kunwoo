<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
// add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/index.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/'.((G5_IS_MOBILE)?'bbs_adm_m':'bbs_adm').'.css">', 2);
if($g5['file_name'] == 'bbs_write'){
    add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>', 2);
}