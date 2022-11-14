<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//DB에 대시보드테이블(dashboard)이 존재하는지 확인하고 없으면 설치
$result_dsh = @sql_query(" DESC ".$g5['dashboard_table']." ", false);
if(!$result_dsh){
	// 테이블 생성 ------------------------------------
	include_once(G5_WIDGET_SET_PATH.'/set_db_dashboard.php');
}

//DB에 마이위젯테이블(mywidget)이 존재하는지 확인하고 없으면 설치
$result_myw = @sql_query(" DESC ".$g5['mywidget_table']." ", false);
if(!$result_myw){
	// 테이블 생성 ------------------------------------
	include_once(G5_WIDGET_SET_PATH.'/set_db_mywidget.php');
}

//DB에 마이위젯옵션테이블(mywidget_option)이 존재하는지 확인하고 없으면 설치
$result_mwo = @sql_query(" DESC ".$g5['mywidget_option_table']." ", false);
if(!$result_mwo){
	// 테이블 생성 ------------------------------------
	include_once(G5_WIDGET_SET_PATH.'/set_db_mywidget_option.php');
}

//DB에 위젯테이블(widget)이 존재하는지 확인하고 없으면 설치
$result_wig = @sql_query(" DESC ".$g5['widget_table']." ", false);
if(!$result_wig){
	// 테이블 생성 ------------------------------------
	include_once(G5_WIDGET_SET_PATH.'/set_db_widget.php');
}

//DB에 위젯옵션테이블(widget_option)이 존재하는지 확인하고 없으면 설치
$result_wio = @sql_query(" DESC ".$g5['widget_option_table']." ", false);
if(!$result_wio){
	// 테이블 생성 ------------------------------------
	include_once(G5_WIDGET_SET_PATH.'/set_db_widget_option.php');
}

//data폴더에 위젯관련 폴더(각종파일을 저장하는 디렉토리) 생성
$data_widget_dir_path = G5_DATA_PATH.'/widget';
$widget_permision_str = "chmod 707 -R ".$data_widget_dir_path;
if(!is_dir($data_widget_dir_path)){
	@mkdir($data_widget_dir_path, G5_DATA_WIDGET_PERMISSION);
	@chmod($data_widget_dir_path, G5_DATA_WIDGET_PERMISSION);
	
	$data_widget_config_dir_path = $data_widget_dir_path.'/config';
	@mkdir($data_widget_config_dir_path, G5_DIR_PERMISSION);
	@chmod($data_widget_config_dir_path, G5_DIR_PERMISSION);
	
	$data_widget_file_dir_path = $data_widget_dir_path.'/file';
	@mkdir($data_widget_file_dir_path, G5_DIR_PERMISSION);
	@chmod($data_widget_file_dir_path, G5_DIR_PERMISSION);
	
	$data_widget_board_dir_path = $data_widget_dir_path.'/board';
	@mkdir($data_widget_board_dir_path, G5_DIR_PERMISSION);
	@chmod($data_widget_board_dir_path, G5_DIR_PERMISSION);
	
	$data_widget_content_dir_path = $data_widget_dir_path.'/content';
	@mkdir($data_widget_content_dir_path, G5_DIR_PERMISSION);
	@chmod($data_widget_content_dir_path, G5_DIR_PERMISSION);
	
	$data_widget_seo_dir_path = $data_widget_dir_path.'/seo';
	@mkdir($data_widget_seo_dir_path, G5_DIR_PERMISSION);
	@chmod($data_widget_seo_dir_path, G5_DIR_PERMISSION);
	
	$data_widget_shop_dir_path = $data_widget_dir_path.'/shop';
	@mkdir($data_widget_shop_dir_path, G5_DIR_PERMISSION);
	@chmod($data_widget_shop_dir_path, G5_DIR_PERMISSION);
	/*
	$data_widget_temp_dir_path = $data_widget_dir_path.'/temp';
	@mkdir($data_widget_temp_dir_path, G5_DIR_PERMISSION);
	@chmod($data_widget_temp_dir_path, G5_DIR_PERMISSION);
	
	$fp = fopen($data_widget_dir_path.'/temp.json','w');
	//fwrite($fp,json_encode($polyArr));
	fclose($fp);
	*/
	exec($widget_permision_str);
}
//만약 기존의 data폴더 안에 bpwidget폴더가 있으면 그안에 혹시 새로운 파일/폴더가 있을수 있으니 하위파일들까지 권한 재설정을 하자
else{
	exec($widget_permision_str);
}

//--- language 설정 ---//
if ($_SESSION['ss_country']) {
	//-- language 재설정 --//
	$g5['setting']['set_default_country'] = $_SESSION['ss_country'];
}
else {
	if (G5_DEFAULT_COUNTRY) {
		if(is_writable(G5_SESSION_PATH)) {
			//-- 세션에 lang_code를 저장 --//
	        set_session("ss_country", G5_DEFAULT_COUNTRY);
			$g5['setting']['set_default_country'] = G5_DEFAULT_COUNTRY;	
//	        echo "<meta http-equiv='content-type' content='text/html; charset=utf-8'><script type='text/javascript'> window.location.reload(); </script>";
//	        exit;
		}
		else {
	        //echo "<meta http-equiv='content-type' content='text/html; charset=utf-8'><script type='text/javascript'> alert('Session folder mismatch error.');history.back(); </script>";
			die ("Session folder mismatch error");
			exit;
		}
	}
}
//-- 언어설정 변경 시 --//
if($_GET['chg_country']) {
	//-- 세션에 lang_code를 저장 --//
	set_session("ss_country", $_GET['chg_country']);
	$g5['setting']['set_default_country'] = $_GET['chg_country'];	
}

// App 변수 설정 (최초 접속 시 설정)
if ($_SESSION['ss_mobile_os']) {
	$g5['setting']['set_mobile_os'] = $_SESSION['ss_mobile_os'];
	$g5['setting']['set_mobile_version'] = $_SESSION['ss_mobile_version'];
}
else if ($from && $from == 'app') {
	if ($os) {
		if(is_writable(G5_SESSION_PATH)) {
	        set_session("ss_mobile_os", $os);
	        set_session("ss_mobile_version", $ver);
			$g5['setting']['set_mobile_os'] = $os;
			$g5['setting']['set_mobile_version'] = $ver;
		}
		else {
			die ("Session folder mismatch error");
			exit;
		}
	}
}


// 번역 문장 포함
@include_once(G5_USER_PATH.'/localize/lang/'.$g5['setting']['set_default_country'].'.php');



// 디비 테이블 메타 확장 -----------------
//설정 테이블 추출 ($g5['setting'] 과 같은 환경설정 변수를 저장합니다.)
$result = sql_query(" SELECT set_name,set_value FROM {$g5['setting_table']} WHERE set_key = 'ypage' AND set_auto_yn = '1' AND (set_country = '".$g5['setting']['set_default_country']."' OR set_country = 'global') ");
for ($i=0; $row=sql_fetch_array($result); $i++) {
	$g5['setting'][$row['set_name']] = $row['set_value'];
	// A=B 형태를 가지고 있으면 자동 할당
	$set_values = explode(',', preg_replace("/\s+/", "", $g5['setting'][$row['set_name']]));
	$k = 0;
	foreach ($set_values as $set_value) {
		//변수가 (,),(=)로 구분되어 있을때
		if( preg_match("/=/",$set_value) ) {
			list($key, $value) = explode('=', $set_value);
			$g5[$row['set_name']][$key] = $value.' ('.$key.')';
			$g5[$row['set_name'].'_value'][$key] = $value;
			$g5[$row['set_name'].'_reverse'][$value] = $key;
			$g5[$row['set_name'].'_arr'][] = $key;
			$g5[$row['set_name'].'_radios'] .= '<label for="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'" style="'.(($k)?'margin-left:10px;':'').'"><input type="radio" id="'.$row['set_name'].'_'.$key.'" name="'.$row['set_name'].'" value="'.$key.'">'.$value.'('.$key.')</label>';
			$g5[$row['set_name'].'_radios0'] .= '<label for="'.$row['set_name'].'_'.$key.'" class="'.$row['set_name'].'" style="'.(($k)?'margin-left:10px;':'').'"><input type="radio" id="'.$row['set_name'].'_'.$key.'" name="'.$row['set_name'].'" value="'.$key.'">'.$value.'</label>';
			$g5[$row['set_name'].'_checkboxs'] .= '<label for="set_status_'.$key.'" class="set_status"><input type="hidden" name="set_status_'.$key.'" value=""><input type="checkbox" id="set_status_'.$key.'">'.$value.'('.$key.')</label>';
			$g5[$row['set_name'].'_options'] .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
			$g5[$row['set_name'].'_value_options'] .= '<option value="'.trim($key).'">'.trim($value).'</option>';
			$k++;
		}
		//변수가 (,)로만 구분되어 있을때
		else {
			$g5[$row['set_name'].'_array'][] = $set_value;
		}
	}
	unset($k);
	// unset($set_values);unset($set_value);
}



//브라우저
$g5['browser_name'] = wgf_browserCheck();
//echo $g5['browser_name'];
//익스여부
$g5['is_explorer'] = wgf_is_explorer();
//echo $g5['is_explorer'];
//익스버전
$g5['ie_version'] = 0;
if (preg_match("/ie/", $g5['browser_name']) && $g5['is_explorer']){
	$g5['ie_version'] = (int) substr($g5['browser_name'],2);
}

//실제모바일 디바이스여부
$g5['is_real_mobile'] = is_mobile();

//브라우저 기본정보
$g5['user_agent'] = $_SERVER["HTTP_USER_AGENT"];
//echo $g5['user_agent'];

//디바이스 타입
$g5['device_type'] = wgf_deviceCheck();
//echo $g5['device_type'];

//PC유사한 디바이스인가?
$g5['is_device_etc'] = ($g5['device_type'] == 'etc') ? 1 : 0;

//안드로이드 디바이스인가?
$g5['is_device_android'] = ($g5['device_type'] == 'android') ? 1 : 0;

//iphone 디바이스인가?
$g5['is_device_iphone'] = ($g5['device_type'] == 'iphone') ? 1 : 0;

//ipad 디바이스인가?
$g5['is_device_ipad'] = ($g5['device_type'] == 'ipad') ? 1 : 0;

//ipod 디바이스인가?
$g5['is_device_ipod'] = ($g5['device_type'] == 'ipod') ? 1 : 0;

//ios 디바이스인가?
$g5['is_device_ios'] = ($g5['device_type'] == 'iphone' || $g5['device_type'] == 'ipad' || $g5['device_type'] == 'ipod') ? 1 : 0;

//blackberry 디바이스인가?
$g5['is_device_blackberry'] = ($g5['device_type'] == 'blackberry') ? 1 : 0;




//플러그인 테이블 추출 ($plugin 과 같은 환경설정 변수를 저장합니다.)
$result = sql_query(" SELECT set_name,set_value FROM {$g5['setting_table']} WHERE set_key = 'plugin' AND set_auto_yn = '1' AND (set_country = '".$g5['setting']['set_default_country']."' OR set_country = 'global') ");
for ($i=0; $row=sql_fetch_array($result); $i++)
	$g5['plugin'][$row['set_name']] = $row['set_value'];



	
//회원 테이블 메타 확장
if ($_SESSION['ss_mb_id']) { // 로그인중이라면
	$result = sql_query(" SELECT mta_key,mta_value FROM {$g5['meta_table']} WHERE mta_db_table = 'member' AND mta_db_id='".$member['mb_id']."' ");
	for ($i=0; $row=sql_fetch_array($result); $i++)
		$member[$row['mta_key']] = $row['mta_value'];
}

//그룹 테이블 메타 확장
if ($gr_id) {
	$result = sql_query(" SELECT mta_key,mta_value FROM {$g5['meta_table']} WHERE mta_db_table = 'group' AND mta_db_id='$gr_id' ");
	for ($i=0; $row=sql_fetch_array($result); $i++)
		$group[$row["mta_key"]] = $row["mta_value"];
}

//게시판 테이블 메타 확장
if ($bo_table) {
	$result = sql_query(" SELECT mta_key,mta_value FROM {$g5['meta_table']} WHERE mta_db_table = 'board' AND mta_db_id='$bo_table' ");
	for ($i=0; $row=sql_fetch_array($result); $i++)
		$board[$row["mta_key"]] = $row["mta_value"];
	
	//게시물 메타 확장
	if ($wr_id) {
		$result = sql_query(" SELECT mta_key,mta_value FROM {$g5['meta_table']} WHERE mta_db_table = 'board/".$bo_table."' AND mta_db_id='$wr_id' ");
		for ($i=0; $row=sql_fetch_array($result); $i++)
			$write[$row["mta_key"]] = $row["mta_value"];
	}
}



// URL에서 디렉토리명, 파일명 추출
//echo basename($_SERVER["SCRIPT_FILENAME"]);
$path_info=pathinfo($_SERVER['SCRIPT_FILENAME']);
$path_info['dirname'] = preg_replace("/\\\/", "/", $path_info['dirname']);
$g5['dir_name'] = substr($path_info['dirname'],strrpos($path_info['dirname'],'/')+1,strlen($path_info['dirname']));
$g5['dir_path'] = preg_replace("|".G5_PATH."|", "", $path_info['dirname']);
$g5['file_name'] = $path_info['filename'];
$g5['file_path'] = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/'.$g5['file_name']));
//$g5['hook_file_path'] = (preg_match("|/adm/|",$g5['file_path'].'/')) ? preg_replace("|/adm|", "/adm/".G5_USER_ADMIN_DIR."/".G5_HOOK_DIR, $g5['file_path']) : preg_replace("|".G5_PATH."|", G5_PATH."/".G5_USER_DIR."/".G5_HOOK_DIR, $g5['file_path']) ;

//후킹파일이 관리자 단일때
if(preg_match("|/adm/|",$g5['file_path'].'/')){
	//후킹파일이 /adm/안에 있는 자식 폴더안에 있는 파일이 타겟일때
	if($g5['dir_name'] != 'adm') {
		$g5['hook_file_path'] = preg_replace("|/adm".$g5['dir_name']."|", "/adm/".G5_USER_ADMIN_DIR."/".G5_HOOK_DIR."/adm/".$g5['dir_name'], $g5['file_path']);
	}
	//후킹파일이 /adm/안에 있는 파일이 타겟일때
	else {
		$g5['hook_file_path'] = preg_replace("|/adm|", "/adm/".G5_USER_ADMIN_DIR."/".G5_HOOK_DIR."/adm", $g5['file_path']);
	}
}
//후킹파일이 사용자 단일때
else{	
	//루트파일이 아닌 서브폴더에 있는 파일을 후킹처리하려고 할때
	if(preg_match("|/".$g5['dir_name']."/|",$g5['file_path'].'/')){
		$g5['hook_file_path'] = preg_replace("|".G5_PATH."/".$g5['dir_name']."|", G5_PATH."/".G5_USER_DIR."/".G5_HOOK_DIR."/".$g5['dir_name'], $g5['file_path']);
	}
	//루트파일을 후킹처리 하려고 할때
	else {
		$g5['hook_file_path'] = preg_replace("|".G5_PATH."|", G5_PATH."/".G5_USER_DIR."/".G5_HOOK_DIR, $g5['file_path']);
	}
}

//echo $g5['dir_name'].'<br>';
//echo $g5['dir_path'].'<br>';
//echo $g5['file_name'].'<br>';
//echo $g5['file_path'].'<br>';
//echo $g5['hook_file_path'].'<br>';




// 회원인 경우 체크사항
if ($is_member) {
    // 읽지 않은 쪽지가 있다면
    $sql = " select count(*) as cnt from {$g5['memo_table']} where me_recv_mb_id = '{$member['mb_id']}' and me_read_datetime = '0000-00-00 00:00:00' ";
    $row = sql_fetch($sql);
    $memo_not_read = $row['cnt'];

    // 관리 권한이 주어졌다면
    $is_auth = false;
    $sql = " select count(*) as cnt from {$g5['auth_table']} where mb_id = '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if ($row['cnt']||$member['mb_level']>8)
        $is_auth = true;
}


//-- 월화수목금토일 한글값
$g5['week_names'] = array(
	"0"=>"일"
	,"1"=>"월"
	,"2"=>"화"
	,"3"=>"수"
	,"4"=>"목"
	,"5"=>"금"
	,"6"=>"토"
);
$g5['week_names2'] = array(
	"0"=>"월"
	,"1"=>"화"
	,"2"=>"수"
	,"3"=>"목"
	,"4"=>"금"
	,"5"=>"토"
	,"6"=>"일"
);
$g5['write_default_fields'] = array(
	"wr_id" => "int(11) NOT NULL"
	,"wr_num" => "int(11) NOT NULL DEFAULT 0"
	,"wr_reply" => "varchar(10) NOT NULL"
	,"wr_parent" => "int(11) NOT NULL DEFAULT 0"
	,"wr_is_comment" => "tinyint(4) NOT NULL DEFAULT 0"
	,"wr_comment" => "int(11) NOT NULL DEFAULT 0"
	,"wr_comment_reply" => "varchar(5) NOT NULL"
	,"ca_name" => "varchar(255) NOT NULL"
	,"wr_option" => "set('html1','html2','secret','mail') NOT NULL"
	,"wr_subject" => "varchar(255) NOT NULL"
	,"wr_content" => "text NOT NULL"
	,"wr_seo_title" => "varchar(255) NOT NULL DEFAULT ''"
	,"wr_link1" => "text NOT NULL"
	,"wr_link2" => "text NOT NULL"
	,"wr_link1_hit" => "int(11) NOT NULL DEFAULT 0"
	,"wr_link2_hit" => "int(11) NOT NULL DEFAULT 0"
	,"wr_hit" => "int(11) NOT NULL DEFAULT 0"
	,"wr_good" => "int(11) NOT NULL DEFAULT 0"
	,"wr_nogood" => "int(11) NOT NULL DEFAULT 0"
	,"mb_id" => "varchar(20) NOT NULL"
	,"wr_password" => "varchar(255) NOT NULL"
	,"wr_name" => "varchar(255) NOT NULL"
	,"wr_email" => "varchar(255) NOT NULL"
	,"wr_homepage" => "varchar(255) NOT NULL"
	,"wr_datetime" => "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'"
	,"wr_file" => "tinyint(4) NOT NULL DEFAULT 0"
	,"wr_last" => "varchar(19) NOT NULL"
	,"wr_ip" => "varchar(255) NOT NULL"
	,"wr_facebook_user" => "varchar(255) NOT NULL"
	,"wr_twitter_user" => "varchar(255) NOT NULL"
	,"wr_1" => "varchar(255) NOT NULL"
	,"wr_2" => "varchar(255) NOT NULL"
	,"wr_3" => "varchar(255) NOT NULL"
	,"wr_4" => "varchar(255) NOT NULL"
	,"wr_5" => "varchar(255) NOT NULL"
	,"wr_6" => "varchar(255) NOT NULL"
	,"wr_7" => "varchar(255) NOT NULL"
	,"wr_8" => "varchar(255) NOT NULL"
	,"wr_9" => "varchar(255) NOT NULL"
	,"wr_10" => "varchar(255) NOT NULL"
);


if (isset($_REQUEST['sfl2']))  {
    $sfl2 = trim($_REQUEST['sfl2']);
    $sfl2 = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*\s]/", "", $sfl2);
    if ($sfl2)
        $qstr .= '&amp;sfl=' . urlencode($sfl2); // search field (검색 필드)
} else {
    $sfl2 = '';
}


if (isset($_REQUEST['stx2']))  { // search text (검색어)
    $stx2 = get_search_string(trim($_REQUEST['stx2']));
    if ($stx2 || $stx2 === '0')
        $qstr .= '&amp;stx=' . urlencode(cut_str($stx2, 20, ''));
} else {
    $stx2 = '';
}

if (isset($_REQUEST['sst2']))  {
    $sst2 = trim($_REQUEST['sst2']);
    $sst2 = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*\s]/", "", $sst2);
    if ($sst2)
        $qstr .= '&amp;sst2=' . urlencode($sst2); // search sort (검색 정렬 필드)
} else {
    $sst2 = '';
}

if (isset($_REQUEST['sod2']))  { // search order (검색 오름, 내림차순)
    $sod2 = preg_match("/^(asc|desc)$/i", $sod2) ? $sod2 : '';
    if ($sod2)
        $qstr .= '&amp;sod2=' . urlencode($sod2);
} else {
    $sod2 = '';
}


if (isset($_REQUEST['sst3']))  {
    $sst3 = trim($_REQUEST['sst3']);
    $sst3 = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*\s]/", "", $sst3);
    if ($sst3)
        $qstr .= '&amp;sst3=' . urlencode($sst3); // search sort (검색 정렬 필드)
} else {
    $sst3 = '';
}

if (isset($_REQUEST['sod3']))  { // search order (검색 오름, 내림차순)
    $sod3 = preg_match("/^(asc|desc)$/i", $sod3) ? $sod3 : '';
    if ($sod3)
        $qstr .= '&amp;sod3=' . urlencode($sod3);
} else {
    $sod3 = '';
}

// 로그인을 할 때마다 로그 파일 삭제해야 용량을 확보할 수 있음 
if(basename($_SERVER["SCRIPT_FILENAME"]) == 'login_check.php') {
	// 지난시간을 초로 계산해서 적어주시면 됩니다.
	$del_time_interval = 3600 * 12;	// Default = 12 시간
	$thumb_del_time_interval = 3600 * 240;	// Default = 240 시간 (10일)

	// 세선 파일 삭제 adm/session_file_delete.php 참고했습니다.
	if ($dir=@opendir(G5_DATA_PATH.'/session')) {
	    while($file=readdir($dir)) {
	        if (!strstr($file,'sess_')) continue;
	        if (strpos($file,'sess_')!=0) continue;
	        $session_file = G5_DATA_PATH.'/session/'.$file;

	        if (!$atime=@fileatime($session_file))
	            continue;
	        if (time() > $atime + $del_time_interval)
	            unlink($session_file);
	    }
    }
	
	
	// 캐시 파일 삭제 adm/cache_file_delete.php, captch_file_deelte 참고했습니다.
	if ($dir=@opendir(G5_DATA_PATH.'/cache')) {
		// latest 파일 삭제
		$latest_files = glob(G5_DATA_PATH.'/cache/latest-*');
		if (is_array($latest_files)) {
		    foreach ($latest_files as $latest_file) {
		        if (!$atime=@fileatime($latest_file))
		            continue;
		        if (time() > $atime + $del_time_interval)
		            unlink($latest_file);
		    }
		}

		// captcha 파일 삭제
		$captcha_files = glob(G5_DATA_PATH.'/cache/kcaptcha-*');
		if (is_array($captcha_files)) {
		    foreach ($captcha_files as $captcha_file) {
		        if (!$atime=@fileatime($captcha_file))
		            continue;
		        if (time() > $atime + $del_time_interval)
		            unlink($captcha_file);
		    }
		}

		// banner 파일 삭제
		$banner_files = glob(G5_DATA_PATH.'/cache/banner-*');
		if (is_array($banner_files)) {
		    foreach ($banner_files as $banner_file) {
		        if (!$atime=@fileatime($banner_file))
		            continue;
		        if (time() > $atime + $del_time_interval)
		            unlink($banner_file);
		    }
		}
		
	}

	// 썸네일 파일 삭제 adm/thumbnail_file_delete.php 참고했습니다.
	$directory = array();
	$dl = array('file', 'editor');
	foreach($dl as $val) {
	    if($handle = opendir(G5_DATA_PATH.'/'.$val)) {
	        while(false !== ($entry = readdir($handle))) {
	            if($entry == '.' || $entry == '..')
	                continue;
	
	            $path = G5_DATA_PATH.'/'.$val.'/'.$entry;
	
	            if(is_dir($path))
	                $directory[] = $path;
	        }
	    }
	}
	if (!empty($directory)) {
		foreach($directory as $dir) {
		    $thumb_files = glob($dir.'/thumb-*');
		    if (is_array($thumb_files)) {
		        foreach($thumb_files as $thumb_file) {
			        if (!$atime=@fileatime($thumb_file))
			            continue;
			        if (time() > $atime + $thumb_del_time_interval)
			            unlink($thumb_file);
		        }
		    }
		}
	}
	
}


// 설정파일 삽입 후킹 (관리자단, 사용자단 분리)
//@include_once($g5['hook_file_path'].'/u.'.$g5['file_name'].'.config.php');


// 동일파일 대체 후킹 (관리자단, 사용자단 분리)
//@include_once($g5['hook_file_path'].'/u.'.$g5['file_name'].'.php');
@include_once($g5['hook_file_path'].'/'.$g5['file_name'].'.php');


// header hooking
add_event('common_header', 'u_common_header',10);
function u_common_header(){
	global $g5,$member,$default,$config,$sub_menu,$board,$board_skin_path,$board_skin_url;
	if($g5['dir_name'] == 'adm' && $g5['file_name'] == 'index' && G5_IS_MOBILE){
		Header("Location:".G5_ADMIN_URL.'/v10');
	}
    $g5['hook_file_header'] = $g5['hook_file_path'].'/'.$g5['file_name'].'.head.php';
    // 모바일 후킹 파일이 있으면 포함, 없으면 디폴트 후킹 파일 포함
    if(G5_IS_MOBILE && is_file($g5['hook_file_header']))
        @include_once($g5['hook_file_header']);
    else
        @include_once(preg_replace("|/".G5_HOOK_DIR."/mobile|", "/".G5_HOOK_DIR, $g5['hook_file_path']).'/'.$g5['file_name'].'.head.php');

    // 관리자단 게시판 스킨 설정
    $fr_adm = preg_match("/\/adm\/v10/",$_SERVER['HTTP_REFERER']);
    if (defined('G5_IS_ADMIN') || $fr_adm) {
        // 관리자 스킨
        $unser = unserialize(stripslashes($board['bo_7']));
        if( is_array($unser) ) {
            foreach ($unser as $k1=>$v1) {
                $board[$k1] = htmlspecialchars($v1, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
            }    
        }
        // print_r3($board);
        // 모바일은 없음
        if($board['set_skin_adm']) {
            $board_skin_path    = get_skin_path('board', 'theme/'.$board['set_skin_adm']);
            $board_skin_url     = get_skin_url('board', 'theme/'.$board['set_skin_adm']);
        }
    }    
	
}



// 인트라 게시판인 경우 관리자단이라고 봐야 함, 스타일 호출 위치 때문에 여기 선언해야 됨
// 모바일인 경우는 제외(관리자단에서 게시판을 보이지 않음)
// if($board['gr_id']=='intra'&&!G5_IS_MOBILE) {	// 모바일도 관리자단 접속을 하게 함
if($board['gr_id']=='intra') {
    define('G5_IS_ADMIN', true);
}

if($bo_table){
	@include($board_skin_path.'/_set/change_fields.skin.php');
	if(isset($write_change_sort_fields) && count($write_change_sort_fields)){
		add_replace('get_board_sort_fields', 'u_get_board_sort_fields', 10, 5);

		function u_get_board_sort_fields($array, $board, $make_key_return){
			global $board_skin_path;
			include($board_skin_path.'/_set/change_fields.skin.php');
			return $write_change_sort_fields;
		}
	}
}

// Admin mode default hooking
if(defined('G5_IS_ADMIN')){
	add_event('adm_board_form_before', 'u_adm_board_form_before', 10);
	add_event('tail_sub', 'u_tail_sub', 10);
	
	if(G5_IS_MOBILE){
		@include_once(G5_USER_ADMIN_MOBILE_LIB_PATH.'/common.lib.php');
		add_replace('head_css_url','get_mobile_admin_css',10,1);
	}
	
	function u_adm_board_form_before(){
		global $g5;
		$column_query_arr = array(
			" SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_1' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_2' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_3' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_4' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_5' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_6' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_7' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_8' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_9' "
			," SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_10' "
		);
		//print_r3($column_query_arr);
		for($i=0;$i<count($column_query_arr);$i++){
			$n = $i+1;
			${'colt'.$i} = sql_fetch($column_query_arr[$i]);
			if(${'colt'.$i}['Type'] != 'longtext'){
				sql_query(" ALTER TABLE `{$g5['board_table']}` MODIFY `bo_{$n}` longtext ",1);
			}
		}
	}

	function u_tail_sub(){
		global $g5,$member,$default,$config,$board,$menu,$w,$print_version,$board_skin_path,$board_skin_url;
		if($g5['file_name'] == 'contentform') global $co,$readonly;
		if($g5['file_name'] == 'itemform') global $it;

		// 
        echo '<script>'.PHP_EOL;
		echo 'var file_name = "'.$g5['file_name'].'";'.PHP_EOL;
		echo 'var dir_path = "'.$g5['dir_path'].'";'.PHP_EOL;
		echo 'var mb_id = "'.$member['mb_id'].'";'.PHP_EOL;
		echo 'var mb_name = "'.$member['mb_name'].'";'.PHP_EOL;
		echo 'var mb_level = "'.$member['mb_level'].'";'.PHP_EOL;
		echo 'var g5_community_use = "'.G5_COMMUNITY_USE.'";'.PHP_EOL;
		echo 'var g5_user_url = "'.G5_USER_URL.'";'.PHP_EOL;
		echo 'var g5_user_admin_url = "'.G5_USER_ADMIN_URL.'";'.PHP_EOL;
		echo 'var g5_user_admin_mobile_url = "'.G5_USER_ADMIN_MOBILE_URL.'";'.PHP_EOL;
		echo 'var g5_print_version = "'.$print_version.'";'.PHP_EOL;
		echo 'var get_device_change_url = "'.get_device_change_url().'"'.PHP_EOL;
		echo 'var cf_company_title = "'.$config['cf_title'].'"'.PHP_EOL;
		echo '</script>'.PHP_EOL;

		if(G5_IS_MOBILE) {
			//기존 admin.css 추가적인 스타일을 위해서 adm.css를 추가
			if(is_file(G5_USER_ADMIN_MOBILE_CSS_PATH.'/adm.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_MOBILE_CSS_URL.'/adm.css">',0);
			if(is_file(G5_USER_ADMIN_MOBILE_CSS_PATH.'/user.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_MOBILE_CSS_URL.'/user.css">',0);
			// 팝업창 관련 css
			if(is_file(G5_USER_ADMIN_MOBILE_CSS_PATH.'/user_popup.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_MOBILE_CSS_URL.'/user_popup.css">',1);
			// 사용자 정의 css, 파일명과 같은 css가 있으면 자동으로 추가됨
			if(is_file(G5_USER_ADMIN_MOBILE_CSS_PATH.'/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_MOBILE_CSS_URL.'/'.$g5['file_name'].'.css">',0);

			// js 추가
			if(is_file(G5_USER_ADMIN_MOBILE_JS_PATH.'/function.js')) add_javascript('<script src="'.G5_USER_ADMIN_MOBILE_JS_URL.'/function.js"></script>',0);
			if(is_file(G5_USER_ADMIN_MOBILE_JS_PATH.'/common.js')) add_javascript('<script src="'.G5_USER_ADMIN_MOBILE_JS_URL.'/common.js"></script>',0);
			// 사용자 정의 함수, 파일명과 같은 js가 있으면 자동으로 추가됨
			if(is_file(G5_USER_ADMIN_MOBILE_JS_PATH.'/'.$g5['file_name'].'.js')) echo '<script src="'.G5_USER_ADMIN_MOBILE_JS_URL.'/'.$g5['file_name'].'.js"></script>'.PHP_EOL;
			if(is_file(G5_USER_ADMIN_MOBILE_JS_PATH.'/tail.js')) echo '<script src="'.G5_USER_ADMIN_MOBILE_JS_URL.'/tail.js"></script>'.PHP_EOL;
		}
		else {
			//기존 admin.css 추가적인 스타일을 위해서 adm.css를 추가
			if(is_file(G5_USER_ADMIN_CSS_PATH.'/adm.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/adm.css">',0);
			if(is_file(G5_USER_ADMIN_CSS_PATH.'/user.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/user.css">',0);
			// 팝업창 관련 css
			if(is_file(G5_USER_ADMIN_CSS_PATH.'/user_popup.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/user_popup.css">',1);
			
			// 사용자 정의 css, 파일명과 같은 css가 있으면 자동으로 추가됨
			if(is_file(G5_USER_ADMIN_CSS_PATH.'/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/'.$g5['file_name'].'.css">',0);

			// js 추가
			if(is_file(G5_USER_ADMIN_JS_PATH.'/function.js')) add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/function.js"></script>',0);
			if(is_file(G5_USER_ADMIN_JS_PATH.'/common.js')) add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/common.js"></script>',0);
			// 사용자 정의 함수, 파일명과 같은 js가 있으면 자동으로 추가됨
			if(is_file(G5_USER_ADMIN_JS_PATH.'/'.$g5['file_name'].'.js')) echo '<script src="'.G5_USER_ADMIN_JS_URL.'/'.$g5['file_name'].'.js"></script>'.PHP_EOL;
			if(is_file(G5_USER_ADMIN_JS_PATH.'/tail.js')) echo '<script src="'.G5_USER_ADMIN_JS_URL.'/tail.js"></script>'.PHP_EOL;
		}
		
		// 후킹 추가
        @include_once($g5['hook_file_path'].'/'.$g5['file_name'].'.tail.php');
		@include_once(G5_USER_ADMIN_PATH.'/_kosmolog.php');
        
        // 관리자 디버깅 메시지 (있는 경우만 나타남)
        if( is_array($g5['debug_msg']) ) {
            for($i=0;$i<sizeof($g5['debug_msg']);$i++) {
                echo '<div class="debug_msg">'.$g5['debug_msg'][$i].'</div>';
            }
            echo '
            <script>
            $(function(){
                $("#container").prepend( $(".debug_msg") );
            });
            </script>';
        }
        
	}
	
    function get_mobile_admin_css(){
		return G5_USER_ADMIN_MOBILE_CSS_URL.'/admin.css?ver='.G5_CSS_VER;
	}
}
// User mode default hooking
else{

    add_event('shop_head_end','u_shop_head_end',10);
	add_event('tail_sub', 'u_tail_sub', 10);
	add_event('password_lost2_after', 'u_password_lost2_after', 10);

	function u_password_lost2_after(){
		global $email;
		
		alert($email.' 메일로 회원아이디와 비밀번호를 인증할 수 있는 메일이 발송 되었습니다2.\\n\\n메일을 확인하여 주십시오.');
		/*
		if(G5_IS_MOBILE) {
			alert($email.' 메일로 회원아이디와 비밀번호를 인증할 수 있는 메일이 발송 되었습니다.\\n\\n메일을 확인하여 주십시오.',G5_URL.'?device=mobile');
		}else{
			alert($email.' 메일로 회원아이디와 비밀번호를 인증할 수 있는 메일이 발송 되었습니다2.\\n\\n메일을 확인하여 주십시오.');
		}
		*/
	}

	function u_shop_head_end(){
		global $g5,$config,$default,$tmp_cart_id,$od_id,$s_cart_id,$member;
        @include_once($g5['hook_file_path'].'/u.'.$g5['file_name'].'.shop_head.php');
	}

	function u_tail_sub(){
		global $g5,$member,$default,$config,$is_admin,$w;
		if($g5['file_name'] == 'content') global $co,$co_id;
        
        // 
        echo '<script>'.PHP_EOL;
        echo 'var file_name = "'.$g5['file_name'].'";'.PHP_EOL;
        echo 'var dir_path = "'.$g5['dir_path'].'";'.PHP_EOL;
        echo 'var mb_id = "'.$member['mb_id'].'";'.PHP_EOL;
        echo 'var mb_name = "'.$member['mb_name'].'";'.PHP_EOL;
        echo 'var mb_level = "'.$member['mb_level'].'";'.PHP_EOL;
        echo 'var g5_community_use = "'.G5_COMMUNITY_USE.'"'.PHP_EOL;
        echo 'var g5_user_url = "'.G5_USER_URL.'"'.PHP_EOL;
        echo 'var g5_user_admin_url = "'.G5_USER_ADMIN_URL.'"'.PHP_EOL;
		echo 'var get_device_change_url = "'.get_device_change_url().'"'.PHP_EOL;
        echo '</script>'.PHP_EOL;

		if(is_file(G5_USER_CSS_PATH.'/default_reset.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_CSS_URL.'/default_reset.css">',0);
        
        if(G5_IS_MOBILE) {
            if(is_file(G5_THEME_PATH.'/css/mobile2.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/css/mobile2.css">',0);
            // 사용자 정의 css, 파일명과 같은 css가 있으면 자동으로 추가됨
            if(is_file(G5_THEME_PATH.'/mobile/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/mobile/css/'.$g5['file_name'].'.css">',0);
        }
        else {
            if(is_file(G5_THEME_PATH.'/css/default2.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/css/default2.css">',0);
            // 사용자 정의 css, 파일명과 같은 css가 있으면 자동으로 추가됨
            if(is_file(G5_THEME_PATH.'/css/'.$g5['file_name'].'.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_THEME_URL.'/css/'.$g5['file_name'].'.css">',0);
        }
		if(is_file(G5_USER_CSS_PATH.'/common.css')) add_stylesheet('<link rel="stylesheet" href="'.G5_USER_CSS_URL.'/common.css">',0);
		
//		if(is_file(G5_USER_JS_PATH.'/jquery_ui/jquery-ui.min.js')) add_javascript('<script src="'.G5_USER_JS_URL.'/jquery_ui/jquery-ui.min.js"></script>',0);
		//if(is_file(G5_USER_JS_PATH.'/bootstrap/bootstrap.min.js')) add_javascript('<script src="'.G5_USER_JS_URL.'/bootstrap/bootstrap.min.js"></script>',0);
		if(is_file(G5_USER_JS_PATH.'/common.js')) add_javascript('<script src="'.G5_USER_JS_URL.'/common.js"></script>',0);
		if(is_file(G5_USER_JS_PATH.'/datepicker.js')) add_javascript('<script src="'.G5_USER_JS_URL.'/datepicker.js"></script>',0);
		//if(is_file(G5_USER_JS_PATH.'/slick181/slick.js')) add_javascript('<script src="'.G5_USER_JS_URL.'/slick181/slick.js"></script>',0);
		//if(is_file(G5_THEME_PATH.'/js/jquery.cookie.js')) add_javascript('<script src="'.G5_THEME_URL.'/js/jquery.cookie.js"></script>',0);
        
        if(G5_IS_MOBILE) {
            if(is_file(G5_THEME_PATH.'/js/mobile.theme.common.js')) add_javascript('<script src="'.G5_THEME_JS_URL.'/mobile.theme.common.js"></script>',0);
        }
        else {
            if(is_file(G5_THEME_PATH.'/js/theme.common.js')) add_javascript('<script src="'.G5_THEME_JS_URL.'/theme.common.js"></script>',0);
        }
		
		// 후킹 추가
        @include_once($g5['hook_file_path'].'/'.$g5['file_name'].'.tail.php');

	}
}
unset($set_values);unset($set_value);

//사용자 로그 테이블(g5_5_user_log)이 존재하는지 확인하고 없으면 설치
$user_log_tbl = @sql_query(" DESC ".$g5['user_log_table']." ", false);
if(!$user_log_tbl){
	include_once(G5_USER_ADMIN_SQLS_PATH.'/create_user_log.php');
}

?>