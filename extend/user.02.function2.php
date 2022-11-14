<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//-- BP위젯 테이블 저장 --
if(!function_exists('wgf_widget_update')){
function wgf_widget_update($bwgs_array){
	global $g5;
	$wig_id = 0;
	
	if(!$bwgs_array['wig_cd']) return 0;//실패하면 0값을 반환한다.
	
	$row1 = sql_fetch(" SELECT * FROM {$g5['widget_table']}
							WHERE wig_cd = '{$bwgs_array['wig_cd']}' ");
	
	if($row1['wig_idx']){
		$wig_id = $row1['wig_idx'];
		$sql = " UPDATE {$g5['widget_table']} SET
					wig_cd = '{$bwgs_array['wig_cd']}'
					,wig_name = '{$bwgs_array['wig_name']}'
					,wig_content = '{$bwgs_array['wig_content']}'
					,wig_db_category = '{$bwgs_array['wig_db_category']}'
					,wig_db_table = '{$bwgs_array['wig_db_table']}'
					,wig_db_id = '{$bwgs_array['wig_db_id']}'
					,wig_device = '{$bwgs_array['wig_device']}'
					,wig_skin = '{$bwgs_array['wig_skin']}'
					,wig_manual_url = '{$bwgs_array['wig_manual_url']}'
					,wig_level = '{$bwgs_array['wig_level']}'
					,wig_status = '{$bwgs_array['wig_status']}'
					,wig_reg_dt = '{$bwgs_array['wig_reg_dt']}'
					,wig_update_dt = '".G5_TIME_YMDHIS."'
				WHERE wig_idx = '".$row1['wig_idx']."' ";
		sql_query($sql);
	} else {
		$sql = " INSERT INTO {$g5['widget_table']} SET
					wig_cd = '{$bwgs_array['wig_cd']}'
					,wig_name = '{$bwgs_array['wig_name']}'
					,wig_content = '{$bwgs_array['wig_content']}'
					,wig_db_category = '{$bwgs_array['wig_db_category']}'
					,wig_db_table = '{$bwgs_array['wig_db_table']}'
					,wig_db_id = '{$bwgs_array['wig_db_id']}'
					,wig_device = '{$bwgs_array['wig_device']}'
					,wig_skin = '{$bwgs_array['wig_skin']}'
					,wig_manual_url = '{$bwgs_array['wig_manual_url']}'
					,wig_level = '{$bwgs_array['wig_level']}'
					,wig_status = '{$bwgs_array['wig_status']}'
					,wig_reg_dt = '".G5_TIME_YMDHIS."'
					,wig_update_dt = '".G5_TIME_YMDHIS."' ";
		sql_query($sql);
		
		$wig_id = sql_insert_id();
	}
	
	return $wig_id;//성공하면 bwgs_idx값을 반환한다.
}	
}

//-- BP위젯추가옵션 레코드 저장/업데이트/삭제 --
if(!function_exists('wgf_widget_option_update')){
function wgf_widget_option_update($bwgo_array,$wig_idx){
	global $g5;
	
	if(!isset($bwgo_array)) $bwgo_array = array();
	
	$old_arr = array();
	$sql = " SELECT * FROM {$g5['widget_option_table']} WHERE wig_idx = '{$wig_idx}' ";
	$result = sql_query($sql);
	if($result->num_rows){
		for($i=0;$row=sql_fetch_array($result);$i++){
			$old_arr[$row['wio_name']] = $row['wio_value'];
		}
	}
	
	$new_arr = $bwgo_array; //새롭게 들어온 데이터 배열
	$ins_arr = $new_arr; //인서트 데이터
	$upd_arr = $new_arr; //업데이트 데이터
	
	$meg_arr = array_merge($old_arr,$new_arr);
	$del_arr = $meg_arr; //삭제해야할 데이터
	//기존 데이터 중에 삭제해야할 요소만 남기고 제거한 배열
	foreach($new_arr as $k=>$v){
		unset($del_arr[$k]);
	}
	//새롭게 추가된 요소만 남기고 제거한 배열
	foreach($old_arr as $k=>$v){
		unset($ins_arr[$k]);
	}
	//업데이트 해야할 요소만 남기고 제거한 배열
	foreach($ins_arr as $k=>$v){
		unset($upd_arr[$k]);
	}
	
	//print_r2($new_arr);
	//print_r2($ins_arr);
	//print_r2($del_arr);
	//exit;
	
	//업데이트 해야할 요소가 존재하면 요소를 업데이트(DB반영)
	if(count($upd_arr)){
		foreach($upd_arr as $k=>$v){
			$upd_sql = " UPDATE {$g5['widget_option_table']} SET wio_value = '{$v}' WHERE wig_idx = '{$wig_idx}' AND wio_name = '{$k}' ";
			//echo $upd_sql."<br>";
			sql_query($upd_sql,1);
		}
	}
	
	//추가될 새로운 요소가 존재하면 요소를 인서트(DB반영)
	if(count($ins_arr)){
		$ins_sql = " INSERT INTO {$g5['widget_option_table']} (wig_idx,wio_name,wio_value) VALUES ";
		$a = 0;
		foreach($ins_arr as $k=>$v){
			$ins_sql .= ($a == 0) ? " ({$wig_idx},'{$k}','{$v}') " : " ,({$wig_idx},'{$k}','{$v}') ";
			$a++;
		}
		//echo $ins_query."<br>";
		sql_query($ins_sql,1);
	}
	
	//삭제해야할 필요없는 요소가 존재하면 요소를 삭제(DB반영)
	if(count($del_arr)){
		foreach($del_arr as $k=>$v){
			$del_sql = " DELETE FROM {$g5['widget_option_table']} WHERE wig_idx = '{$wig_idx}' AND wio_name = '{$k}' AND wio_value = '{$v}' ";
			//echo $del_sql."<br>";
			sql_query($del_sql,1);
		}
		
		//해당 bwgs_idx와 관련된 옵션레코드가 1개도 존재하지 않으면 옵션관련 첨부폴더 통채로 삭제
		$opt = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['widget_option_table']} WHERE wig_idx = '{$wig_idx}' ");
		if(!$opt['cnt']){
			$con_atm_path = G5_WIDGET_DATA_PATH.'/file/'.$wig_idx.'/option';
			if(is_dir($con_atm_path))
				rm_rf($con_atm_path);
		}
	}
}
}


//-- BP위젯환경설정 첨부파일 등록저장
if(!function_exists('wgf_widget_configfile_reg')){
function wgf_widget_configfile_update($_files,$fle_idx=array(),$del_idx=array()){
	global $g5,$config;

	//환경설정관련 첨부파일이 있을것을 대비해서 파일을 저장할 경로에 폴더를 생성하고, 권한 설정을 한다.
	$config_data_path = G5_WIDGET_DATA_PATH.'/config';
	$config_data_permision_str = "chmod 707 -R ".$config_data_path;
	
	@mkdir($config_data_path, G5_DATA_WIDGET_PERMISSION);
	@chmod($config_data_path, G5_DATA_WIDGET_PERMISSION);
	
	exec($config_data_permision_str);
	
	$upload_max_filesize = ini_get('upload_max_filesize');
	$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));
	
	//g5_1_widget_option 테이블에 기존 레코드가 한 개도 없으면 무조건 시퀀스는 1부터 시작한다.
	wgf_dbtable_sequence_reset($g5['file_table']);
	
	$xml_regex = "/(\.xml)$/i";
	$image_regex = "/(\.(gif|jpe?g|png))$/i";
	
	if(count($del_idx)){
		foreach($del_idx as $k=>$v){
			if($v){ //이전파일 삭제, 이전데이터 수정(이전파일만 삭제)
				$del_file = sql_fetch(" SELECT fle_path,fle_name FROM {$g5['file_table']} WHERE fle_type = 'config' AND fle_idx = '{$v}' AND fle_copyright = '{$k}' ");
				wgf_delete_widget_config_thumbnail($del_file['fle_name']);
				@unlink(G5_PATH.$del_file['fle_path'].'/'.$del_file['fle_name']);
			}
		}
	}
	
	foreach($_files as $k=>$v){
		if($k == 'sitemap' && preg_match($xml_regex, $v['name'])){
			if($v['name']){
				$xml_name = ($v['name'] == 'sitemap.xml') ? $v['name'] : 'sitemap.xml';
				$dest_path = G5_WIDGET_DATA_PATH.'/seo/'.$xml_name;
				move_uploaded_file($v['tmp_name'], $dest_path);
				chmod($dest_path, G5_FILE_PERMISSION);
			}
		}
		else{
			if(!$v['name'] && $del_idx[$k]){
				//레코드 삭제
				$del_sql = " DELETE FROM {$g5['file_table']} WHERE fle_idx = '{$del_idx[$k]}' ";
				sql_query($del_sql,1);
				
			}else{
				if(is_uploaded_file($v['tmp_name'])){
					// 가변 파일 업로드
					$file_upload_msg = '';
					$upload = array();
					$upload['file']     = '';
					$upload['source']   = '';
					$upload['filesize'] = 0;
					$upload['mime_type'] = '';
					$upload['image']    = array();
					$upload['image'][0] = '';
					$upload['image'][1] = '';
					$upload['image'][2] = '';
					
					$tmp_file  = $v['tmp_name'];
					$filesize  = $v['size'];
					$upload['mime_type']  = $v['type'];
					$filename  = $v['name'];
					$filename  = get_safe_filename($filename);
					
					// 서버에 설정된 값보다 큰파일을 업로드 한다면
					if ($filename) {
						if ($v['error'] == 1) {
							$file_upload_msg .= '\"'.$filename.'\" 파일의 용량이 서버에 설정('.$upload_max_filesize.')된 값보다 크므로 업로드 할 수 없습니다.\\n';
							continue;
						}
						else if ($v['error'] != 0) {
							$file_upload_msg .= '\"'.$filename.'\" 파일이 정상적으로 업로드 되지 않았습니다.\\n';
							continue;
						}
					}
					
					if(is_uploaded_file($tmp_file)){
						// 090714
						// 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
						// 에러메세지는 출력하지 않는다.
						$timg = @getimagesize($tmp_file);
						// image type
						if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) ||
							 preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
							if ($timg['2'] < 1 || $timg['2'] > 16)
								continue;
						}
						
						$upload['image'] = $timg;
						
						// 프로그램 원래 파일명
						$upload['source'] = $filename;
						$upload['filesize'] = $filesize;
						
						// 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
						$filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);
						
						shuffle($chars_array);
						$shuffle = implode('', $chars_array);
						
						// 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
						$upload['file'] = abs(ip2long($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);
						
						$dest_file = $config_data_path.'/'.$upload['file'];
						
						// 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
						$error_code = move_uploaded_file($tmp_file, $dest_file) or die($v['error']);

						// 올라간 파일의 퍼미션을 변경합니다.
						chmod($dest_file, G5_FILE_PERMISSION);
					}//if(is_uploaded_file($tmp_file))
						
					// 나중에 테이블에 저장하는 이유는 $bwga_idx 값을 저장해야 하기 때문입니다.
					if (!get_magic_quotes_gpc()) {
						$upload['source'] = addslashes($upload['source']);
						
						if($fle_idx[$k]){
							$del_file = sql_fetch(" SELECT fle_path,fle_name FROM {$g5['file_table']} WHERE fle_type = 'config' AND fle_idx = '{$fle_idx[$k]}' AND fle_copyright = '{$k}' ");
							wgf_delete_widget_config_thumbnail($del_file['fle_name']);
							@unlink(G5_PATH.$del_file['fle_path'].'/'.$del_file['fle_name']);
							
							$sql_first = " UPDATE {$g5['file_table']} ";
							$sql_last = " WHERE fle_idx='{$fle_idx[$k]}' ";
						}else{
							$sql_first = " INSERT INTO {$g5['file_table']} ";
							$sql_last = '';
						}
						
						//멀티업로드일때(이미지 새롭게 업로드)########################################################
						if($upload['file']){
							//
							$sql = " {$sql_first}
										SET fle_type = 'config',
											fle_path = '/data/widget/config',
											fle_copyright = '{$k}',
											fle_name = '{$upload['file']}',
											fle_name_orig = '{$upload['source']}',
											fle_content = '',
											fle_width = '{$upload['image']['0']}',
											fle_height = '{$upload['image']['1']}',
											fle_filesize = '{$upload['filesize']}',
											fle_sort = '999',
											fle_mime_type = '{$upload['mime_type']}',
											fle_status = 'ok',
											fle_reg_dt = '".G5_TIME_YMDHIS."' {$sql_last} ";
							sql_query($sql);
						}//if($upload['file'])	
					}
				}
			}
		}
	}
}
}

//-- BP위젯옵션 첨부파일 등록저장
if(!function_exists('wgf_widget_optfile_reg')){
function wgf_widget_optfile_reg($_files,$wig_idx=0){//인수($_files, BP위젯idx)
	global $g5,$config;

	//옵션관련 첨부파일이 있을것을 대비해서 파일을 저장할 경로에 폴더를 생성하고, 권한 설정을 한다.
	$opt_atm_idx_path = G5_WIDGET_DATA_PATH.'/file/'.$wig_idx;
	$opt_atm_type_path = G5_WIDGET_DATA_PATH.'/file/'.$wig_idx.'/option';
	$opt_atm_idx_permision_str = "chmod 707 -R ".$opt_atm_idx_path;
	$opt_atm_type_permision_str = "chmod 707 -R ".$opt_atm_type_path;
	
	@mkdir($opt_atm_idx_path, G5_DATA_WIDGET_PERMISSION);
	@chmod($opt_atm_idx_path, G5_DATA_WIDGET_PERMISSION);
	
	@mkdir($opt_atm_type_path, G5_DIR_PERMISSION);
	@chmod($opt_atm_type_path, G5_DIR_PERMISSION);
	
	exec($opt_atm_idx_permision_str);
	
	//echo $opt_atm_type_path."<br>";
	if(count($_files)){
		$upload_max_filesize = ini_get('upload_max_filesize');
		$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));
		
		//g5_1_bpwidget_option 테이블에 기존 레코드가 한 개도 없으면 무조건 시퀀스는 1부터 시작한다.
		wgf_dbtable_sequence_reset($g5['file_table']);
		
		foreach($_files as $k=>$v){
			// 파일개수 체크
			$file_count   = 0;
			$upload_count = count($_files[$k]['name']);
			
			for($i=0;$i<$upload_count;$i++){
				if($_files[$k]['name'][$i] && is_uploaded_file($_files[$k]['tmp_name'][$i]))
					$file_count++;
			}
			
			// 가변 파일 업로드
			$file_upload_msg = '';
			$upload = array();
			
			for($i=0;$i<$upload_count;$i++){
				$upload[$i]['file']     = '';
				$upload[$i]['source']   = '';
				$upload[$i]['filesize'] = 0;
				$upload[$i]['mime_type'] = '';
				$upload[$i]['image']    = array();
				$upload[$i]['image'][0] = '';
				$upload[$i]['image'][1] = '';
				$upload[$i]['image'][2] = '';
				
				$tmp_file  = $_files[$k]['tmp_name'][$i];
				$filesize  = $_files[$k]['size'][$i];
				$upload[$i]['mime_type']  = $_files[$k]['type'][$i];
				$filename  = $_files[$k]['name'][$i];
				$filename  = get_safe_filename($filename);
				
				// 서버에 설정된 값보다 큰파일을 업로드 한다면
				if ($filename) {
					if ($_files[$k]['error'][$i] == 1) {
						$file_upload_msg .= '\"'.$filename.'\" 파일의 용량이 서버에 설정('.$upload_max_filesize.')된 값보다 크므로 업로드 할 수 없습니다.\\n';
						continue;
					}
					else if ($_files[$k]['error'][$i] != 0) {
						$file_upload_msg .= '\"'.$filename.'\" 파일이 정상적으로 업로드 되지 않았습니다.\\n';
						continue;
					}
				}
				
				if(is_uploaded_file($tmp_file)){
					// 090714
					// 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
					// 에러메세지는 출력하지 않는다.
					$timg = @getimagesize($tmp_file);
					// image type
					if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) ||
						 preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
						if ($timg['2'] < 1 || $timg['2'] > 16)
							continue;
					}
					
					$upload[$i]['image'] = $timg;
					
					// 프로그램 원래 파일명
					$upload[$i]['source'] = $filename;
					$upload[$i]['filesize'] = $filesize;
					
					// 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
					$filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);
					
					shuffle($chars_array);
					$shuffle = implode('', $chars_array);
					
					// 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
					$upload[$i]['file'] = abs(ip2long($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);
					
					$dest_file = $opt_atm_type_path.'/'.$upload[$i]['file'];
					
					// 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
					$error_code = move_uploaded_file($tmp_file, $dest_file) or die($_files[$k]['error'][$i]);

					// 올라간 파일의 퍼미션을 변경합니다.
					chmod($dest_file, G5_FILE_PERMISSION);
				}//if(is_uploaded_file($tmp_file))
			}//for($i=0;$i<$upload_count;$i++)
			
			// 나중에 테이블에 저장하는 이유는 $sto_idx 값을 저장해야 하기 때문입니다.
			for ($i=0; $i<count($upload); $i++){
				if (!get_magic_quotes_gpc()) {
					$upload[$i]['source'] = addslashes($upload[$i]['source']);
				}
				
				//멀티업로드일때(이미지 새롭게 업로드)########################################################
				if($upload[$i]['file']){
					//
					$sql = " INSERT INTO {$g5['bpwidget_attachment_table']}
								SET fle_db_table = 'widget',
									fle_db_id = '{$wig_idx}',
									fle_type = 'option',
									fle_path = '/data/widget/file/".$wig_idx."/option',
									fle_copyright = '{$k}',
									fle_name = '{$upload[$i]['file']}',
									fle_name_orig = '{$upload[$i]['source']}',
									fle_content = '',
									fle_width = '{$upload[$i]['image']['0']}',
									fle_height = '{$upload[$i]['image']['1']}',
									fle_filesize = '{$upload[$i]['filesize']}',
									fle_sort = '999',
									fle_mime_type = '{$upload[$i]['mime_type']}',
									fle_status = 'ok',
									fle_reg_dt = '".G5_TIME_YMDHIS."' ";
					sql_query($sql);
				}//if($upload[$i]['file'])
			}//for ($i=0; $i<count($upload); $i++)
		}//foreach($_files as $k=>$v)
	}//if(count($_files))
}
}

// Post File 정보 배열을 얻는다.
if(!function_exists('wgf_get_widget_file_list')){
function wgf_get_widget_file_list($fle_db_table, $fle_db_id)
{
    global $g5;

    $sql = " SELECT * FROM {$g5['file_table']} WHERE fle_db_table = '$fle_db_table' AND fle_db_id = '$fle_db_id' AND fle_status = 'publish' ORDER BY fle_sort, fle_idx DESC ";
    $result = sql_query($sql);
	for($i=0;$row = sql_fetch_array($result);$i++)
	{
		$row['fle_host'] = ($row['fle_host'] == 'localhost') ? G5_URL:$row['fle_host'];

		$file[$row['fle_sort']]['href'] = $row['fle_host'].'/'.$row['fle_path'].'/'.$row['fle_name'];
		$file[$row['fle_sort']]['download'] = $row['fle_down_count'];
		$file[$row['fle_sort']]['path'] = $row['fle_path'];
		$file[$row['fle_sort']]['name'] = $row['fle_name'];
		$file[$row['fle_sort']]['name_orig'] = addslashes($row['fle_name_orig']);
		$file[$row['fle_sort']]['filesize'] = get_filesize($row['fle_filesize']);
		$file[$row['fle_sort']]['reg_dt'] = $row['fle_reg_dt'];
		$file[$row['fle_sort']]['content'] = get_text($row['fle_content']);
		$file[$row['fle_sort']]['width'] = $row['fle_width'];
		$file[$row['fle_sort']]['height'] = $row['fle_height'];
		$file[$row['fle_sort']]['type'] = $row['fle_type'];
		$file[$row['fle_sort']]['sort'] = $row['fle_sort'];
		$file[$row['fle_sort']]['mime_type'] = $row['fle_mime_type'];
	}

    return $file;
}
}

// Post File 1개 정보를 얻는다.
if(!function_exists('wgf_get_widget_file')){
function wgf_get_widget_file($fle_idx, $fields='*')
{
    global $g5;

	//-- 기본 정보 추출 --//
	$pfl = sql_fetch(" SELECT $fields FROM {$g5['file_table']} where fle_idx = '$fle_idx' ");

    //-- 메타 데이타 추출 --//
	/*
	$result = sql_query(" SELECT mta_key,mta_value FROM {$g5['meta_table']} WHERE mta_db_table = 'jt_file' AND mta_db_id='$bwef_idx' ");
	for ($i=0; $row=sql_fetch_array($result); $i++)
		$pst[$row['mta_key']] = $row['mta_value'];
	*/
	
    return $pfl;
}
}

// Post File 디비로부터 썸네일 생성하고 디비 업데이트 or 디비 생성
// 변수: bwef_idx, target_file_name, target_width, target_height(초기값=0), bwef_type(없으면 이전값), bwef_exist(초기값=0,원본삭제)
if(!function_exists('wgf_make_widget_file_thumbnail')){
function wgf_make_widget_file_thumbnail($bwef_array) {
	global $g5;

	// 파일명이 존재하지 않으면 리턴
	if(!$bwef_array['target_file_name'])
		return false;

	$pfl = sql_fetch(" SELECT * FROM {$g5['file_table']} WHERE fle_idx = '{$bwef_array['fle_idx']}' ");

	// 디비가 존재하지 않으면 리턴
	if(!$pfl['fle_idx'])
		return false;
		
	// 썸네일 생성
    $thumb = thumbnail($pfl['fle_name'], G5_PATH.$pfl['fle_path'], G5_PATH.$pfl['fle_path'], $bwef_array['target_width'], $bwef_array['target_height'], false, false, 'center', true, $um_value='80/0.5/3');
    	    		
    // 파일 크기 추출
	$dir = G5_PATH.$pfl['fle_path'];
    $file = $dir.'/'.$thumb;
    if(is_file($file)) {
    	$size = @getimagesize($file);
    	$file_size = filesize($file);
		$file_parts = pathinfo($file);
    }
	else
		return false;
    
	// 파일명 변경 (변경하려고 하는 파일명이 있으면 일련번호롤 붙여준다.)
	$target_name = $bwef_array['target_file_name'];	// 확장자 없는 파일명
	if(file_exists(rtrim($dir,'/').'/'.$file_parts['filename'])) {
		$a = glob($dir.'/'.$target_name.'*');
		natcasesort($a);
		$i=0;
		foreach($a as $key => $val) {
			$b[$i] = $val;
			$i++;
		}
		if(sizeof($b) > 1) {
			preg_match_all('/(\([0-9]+\))/',$b[sizeof($b)-2],$match);
			$rows = count($match,0);
			$cols = (count($match,1)/count($match,0))-1;
			$file_no = substr($match[$rows-1][$cols-1],1,-1)+1;
		}
		else
			$file_no = 1;
		
		//-- 파일명 재 설정 --//
		$new_thumb = $target_name.'('.$file_no.').'.$file_parts['extension'];
	}
	else
		$new_thumb = $target_name.'.'.$file_parts['extension'];
	
	// 썸네일 파일명 변경
	@copy($dir.'/'.$thumb, $dir.'/'.$new_thumb);
	@unlink($dir.'/'.$thumb);		

	// bwef_type 없으면
	if(!$bwef_array['fle_type'])
		$bwef_array['fle_type'] = $pfl['fle_type'];

	$sql = " INSERT INTO {$g5['file_table']} SET
					mb_id='$pfl[mb_id]'
					, fle_db_table='$pfl[fle_db_table]'
					, fle_db_id='$pfl[fle_db_id]'
					, fle_type='$bwef_array[fle_type]'
					, fle_host='$pfl[fle_host]'
					, fle_path='$pfl[fle_path]'
					, fle_name='".$new_thumb."'
					, fle_name_orig='$pfl[fle_name_orig]'
					, fle_width='".$size[0]."'
					, fle_height='".$size[1]."'
					, fle_content='$pfl[fle_content]'
					, fle_password='$pfl[fle_password]'
					, fle_down_level='$pfl[fle_down_level]'
					, fle_down_max='$pfl[fle_down_max]'
					, fle_expire_date='$pfl[fle_expire_date]'
					, fle_sort='$pfl[fle_sort]'
					, fle_mime_type='$pfl[fle_mime_type]'
					, fle_filesize='".$file_size."'
					, fle_token='$pfl[fle_token]'
					, fle_status='publish'
					, fle_reg_dt='".G5_TIME_YMDHIS."' ";
	sql_query($sql);
	$pfl['fle_idx'] = sql_insert_id();

	// 디비 삭제, 파일 삭제 (기본 동작은 삭제!)
	if(!$bwef_array['fle_exist']) {
		delete_jt_file( array("fle_idx"=>$bwef_array['fle_idx'],"fle_delete"=>1) );
	}
    
    return array("upfile_name"=>$new_thumb
					,"upfile_width"=>$size[0]
					,"upfile_height"=>$size[1]
					,"upfile_filesize"=>$file_size
					,"upfile_fle_idx"=>$pfl['fle_idx']
					,"upfile_fle_sort"=>$pfl['fle_sort']
					);
}
}

// Post File 이미지 썸네일 생성
// 변수: $bwef_path, $bwef_file, $target_width, $target_height(초기값=0), $bwef_id, $bwef_more
if(!function_exists('wgf_get_widget_file_thumbnail')){
function wgf_get_widget_file_thumbnail($bwef_thumb_array)
{
    $str = '';
    $file = G5_PATH.'/'.$bwef_thumb_array['fle_path'].'/'.$bwef_thumb_array['fle_file'];
    if(is_file($file))
        $size = @getimagesize($file);

    if($size[2] < 1 || $size[2] > 3)
        return '';
    $img_width = $size[0];
    $img_height = $size[1];
    $filename = basename($file);
    $filepath = dirname($file);

	$bwef_thumb_array['target_width'] = (!$bwef_thumb_array['target_width']) ? $img_width:$bwef_thumb_array['target_width'];
	$bwef_thumb_array['target_height'] = (!$bwef_thumb_array['target_height']) ? 0:$bwef_thumb_array['target_height'];

    if($bwef_thumb_array['target_width'] && !$bwef_thumb_array['target_height']) {
        $bwef_thumb_array['target_height'] = round(($bwef_thumb_array['target_width'] * $img_height) / $img_width);
    }
	
    $thumb = thumbnail($filename, $filepath, $filepath, $bwef_thumb_array['target_width'], $bwef_thumb_array['target_height'], false, false, 'center', true, $um_value='80/0.5/3');

    if($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath.'/'.$thumb);
        $str = '<img src="'.$file_url.'" width="'.$bwef_thumb_array['target_width'].'" height="'.$bwef_thumb_array['target_height'].'"';
        if($bwef_thumb_array['fle_idx'])
            $str .= ' id="'.$bwef_thumb_array['fle_idx'].'"';
        if($bwef_thumb_array['fle_more'])
            $str .= ' '.$bwef_thumb_array['fle_more'];
        $str .= ' alt="">';
    }

    return $str;
}
}


// bpwidget file 삭제 - 해당 디비 & 해당 row 관련 파일 전체 삭제
//관련 변수: $bwef_db_table, $bwef_db_id
// $bwef_delete (1이면 DB까지 완전삭제)
// $bwef_delete (0이면 상태값만 변경)
// -- $bwef_delete_file (1이면 파일만 삭제, 상태값 trash)
// ---- $bwef_thumb_exist (1이면 썸네일 유지, 상태값 trash & 파일은 존재)
if(!function_exists('wgf_delete_widget_files')){
function wgf_delete_widget_files($bwef_array)
//function delete_bpwidget_files($bwef_db_table, $bwef_db_id, $bwef_delete=0)
{
    global $g5;

    if(!$bwef_array['fle_db_table'] || !$bwef_array['fle_db_id'])
        return;

    $sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = '".$bwef_array['fle_db_table']."' AND fle_db_id = '".$bwef_array['fle_db_id']."' ORDER BY fle_sort, fle_idx DESC ";
    $result = sql_query($sql);
    while ( $row = sql_fetch_array($result) ) {

		//-- 완전삭제
		if($bwef_array['fle_delete'] == 1) {
			//-- 해당 파일 삭제
			@unlink(G5_PATH.$row['fle_path'].'/'.$row['fle_name']);
			if(!$bwef_array['fle_thumb_exist'])
				wgf_delete_widget_file_thumbnail($row['fle_path'], $row['fle_name']);
			sql_query(" DELETE FROM {$g5['file_table']} WHERE fle_idx = '{$row['fle_idx']}' ");
		}
		else {
			//-- 파일만 삭제
			if($bwef_array['fle_delete_file'] == 1) {
				//-- 해당 파일 삭제
				@unlink(G5_PATH.$row['fle_path'].'/'.$row['fle_name']);
				if(!$bwef_array['fle_thumb_exist'])
					wgf_delete_widget_file_thumbnail($row['fle_path'], $row['fle_name']);
			}
			sql_query(" UPDATE {$g5['file_table']} SET fle_status = 'trash' WHERE fle_idx = '{$row['fle_idx']}' ");
		}
    }
}
}

// bpwidget file 삭제 - 한개
//관련 변수: $bwef_idx, $bwef_db_table, $bwef_db_id, $bwef_sort, 
// $bwef_delete (1이면 DB까지 완전삭제)
// $bwef_delete (0이면 상태값만 변경)
// -- $bwef_delete_file (1이면 파일만 삭제, 상태값 trash)
// ---- $bwef_thumb_exist (1이면 썸네일 유지, 상태값 trash & 파일은 존재)
if(!function_exists('wgf_delete_widget_file')){
function wgf_delete_widget_file($bwef_array)
{
    global $g5;
	
    if(!$bwef_array['fle_idx'] && !$bwef_array['fle_db_table'])
        return;

    //-- 파일 idx가 있는 경우
    if($bwef_array['fle_idx']) {
    	$pfl = sql_fetch(" SELECT * FROM {$g5['file_table']} WHERE bwef_idx = '{$bwef_array['fle_idx']}' ");
    }
    //-- 없으면 해당 db_table, db_id, sort 조건으로 추출
	else if($bwef_array['fle_db_table'] && $bwef_array['fle_db_id']) {
    	$pfl = sql_fetch("	SELECT * FROM {$g5['file_table']} 
							WHERE fle_db_table = '{$bwef_array['fle_db_table']}' 
								AND fle_db_id = '{$bwef_array['fle_db_id']}' 
								AND fle_type = '{$bwef_array['fle_type']}' 
								AND fle_sort = '{$bwef_array['fle_sort']}' ");
	}

	//-- 완전삭제
	if($bwef_array['fle_delete'] == 1) {
		//-- 해당 파일 삭제
		@unlink(G5_PATH.'/'.$pfl['fle_path'].'/'.$pfl['fle_name']);
		if(!$bwef_array['fle_thumb_exist'])
			wgf_delete_widget_file_thumbnail($pfl['fle_path'], $pfl['fle_name']);
		sql_query(" DELETE FROM {$g5['file_table']} WHERE fle_idx = '{$pfl['fle_idx']}' ");
	}
	else {
		//-- 파일만 삭제
		if($bwef_array['fle_delete_file'] == 1) {
			//-- 해당 파일 삭제
			@unlink(G5_PATH.'/'.$pfl['fle_path'].'/'.$pfl['fle_name']);
			if(!$bwef_array['fle_thumb_exist'])
				wgf_delete_widget_file_thumbnail($pfl['fle_path'], $pfl['fle_name']);
		}
		sql_query(" UPDATE {$g5['file_table']} SET fle_status = 'trash' WHERE fle_idx = '{$pfl['fle_idx']}' ");
	}
}
}

// bpwidget file 관련 썸네일 이미지 삭제
if(!function_exists('wgf_delete_widget_file_thumbnail')){
function wgf_delete_widget_file_thumbnail($path, $file)
{
    if(!$path || !$file)
        return;
	
	$path = G5_PATH.'/'.$path;

    $filename = preg_replace("/\.[^\.]+$/i", "", $file); // 확장자제거
    $files = glob($path.'/thumb-'.$filename.'*');
    if(is_array($files)) {
        foreach($files as $thumb_file) {
            @unlink($thumb_file);
        }
    }
}
}

// Post File 업로드 함수
//설정 변수: mb_id, bwef_src_file, bwef_orig_file, bwef_mime_type, bwef_path, bwef_db_table, bwef_db_id, bwef_sort .... 
if(!function_exists('wgf_upload_widget_file')){
function wgf_upload_widget_file($bwef_array)
{
	global $g5,$config,$member;
	
	//-- 원본 파일명이 없으면 리턴 
    if($bwef_array['fle_orig_file'] == "") 
    	return false;
	
	//-- 파일명 재설정, 한글인 경우는 변경
    $bwef_array['fle_dest_file'] = preg_replace("/\s+/", "", $bwef_array['fle_orig_file']);
    $bwef_array['fle_dest_file'] = preg_replace("/[#\&\+\-%@=\/\\:;,'\"\^`~\|\!\?\*\$#<>\(\)\[\]\{\}]/", "", $bwef_array['fle_dest_file']);
    $bwef_array['fle_dest_file'] = preg_replace_callback(
                          "/[가-힣]+/",
                          create_function('$matches', 'return base64_encode($matches[0]);'),
                          $bwef_array['fle_dest_file']);
	$bwef_array['fle_dest_file'] = preg_replace("/\+/", "", $bwef_array['fle_dest_file']);	// 한글변환후 + 기호가 있으면 제거해야 함
	$bwef_array['fle_dest_file'] = preg_replace("/\//", "", $bwef_array['fle_dest_file']);	// 한글변환후 / 기호가 있으면 제거해야 함
	
	// 상태값이 있으면 업데이트
	if($bwef_array['fle_status'])
		$sql_status = ", fle_status='".$bwef_array['fle_status']."' ";
	else 
		$sql_status = ", fle_status='ok' ";

	//-- 파일 업로드 처리
	$upload_file = upload_common_file($bwef_array['fle_src_file'], $bwef_array['fle_dest_file'], $bwef_array['fle_path']);
    //print_r2($upload_file);

	$pfl = sql_fetch(" SELECT * FROM {$g5['file_table']} 
						WHERE fle_db_table = '{$bwef_array['fle_db_table']}' 
							AND fle_type = '{$bwef_array['fle_type']}' 
							AND fle_db_id = '{$bwef_array['fle_db_id']}' AND fle_sort = '{$bwef_array['fle_sort']}' ");
	if($pfl['fle_idx']) {
		//-- 파일이 존재하면 기존 파일만 삭제 (같은 파일명이 로칼, 서버에 모두 존재하면 삭제가 되어 버리는군.)
		//delete_jt_file( array("bwef_idx"=>$pfl['bwef_idx'],"bwef_delete_file"=>1) );
		
		//-- 관련 디비 업데이트
		$sql = " UPDATE {$g5['file_table']} SET 
						fle_name='".$upload_file[0]."'
						, fle_name_orig='{$bwef_array['fle_orig_file']}'
						, fle_width='".$upload_file[1]."'
						, fle_height='".$upload_file[2]."'
						, fle_filesize='".$upload_file[3]."'
						{$sql_status}
						WHERE fle_idx='".$pfl['fle_idx']."' ";
		sql_query($sql);
	}
	else {
		
		//-- pst_host 설정
		$bwef_array['fle_host'] = ($bwef_array['fle_host']) ? $bwef_array['fle_host']:'localhost'; 
		
		//-- pst_expire_date 설정
		$bwef_array['fle_expire_date'] = ($bwef_array['fle_expire_date']) ? $bwef_array['fle_expire_date']:'9999-12-31';
		
        //$filename = preg_replace("/\.[^\.]+$/i", "", $file); // 확장자제거
		// 파일의 mime_type 추출
		if(!$bwef_array['fle_mime_type'])
			$bwef_array['fle_mime_type'] = mime_content_type($filename); 

		$sql = " INSERT INTO {$g5['file_table']} SET
						mb_id='$bwef_array[mb_id]'
						, fle_db_table='$bwef_array[fle_db_table]'
						, fle_db_id='$bwef_array[fle_db_id]'
						, fle_type='$bwef_array[fle_type]'
						, fle_host='$bwef_array[fle_host]'
						, fle_path='$bwef_array[fle_path]'
						, fle_name='".$upload_file[0]."'
						, fle_name_orig='$bwef_array[fle_orig_file]'
						, fle_width='".$upload_file[1]."'
						, fle_height='".$upload_file[2]."'
						, fle_content='$bwef_array[fle_content]'
						, fle_password='$bwef_array[fle_password]'
						, fle_down_level='$bwef_array[fle_down_level]'
						, fle_down_max='$bwef_array[fle_max]'
						, fle_expire_date='$bwef_array[fle_expire_date]'
						, fle_sort='$bwef_array[fle_sort]'
						, fle_mime_type='$bwef_array[fle_mime_type]'
						, fle_filesize='".$upload_file[3]."'
						, fle_token='$bwef_array[fle_token]'
						{$sql_status}
						, fle_reg_dt='".G5_TIME_YMDHIS."' ";
		sql_query($sql);
		$pfl['fle_idx'] = sql_insert_id();
	}

    //$bwef_return[0] = $upload_file[0];
    //$bwef_return[1] = $upload_file[1];
    //$bwef_return[2] = $upload_file[2];
    //$bwef_return[3] = $upload_file[3];
    //$bwef_return[4] = $pfl['bwef_idx'];
    //return $bwef_return;
    return array("upfile_name"=>$upload_file[0]
					,"upfile_width"=>$upload_file[1]
					,"upfile_height"=>$upload_file[2]
					,"upfile_filesize"=>$upload_file[3]
					,"upfile_fle_idx"=>$pfl['fle_idx']
					,"upfile_fle_sort"=>$bwef_array['fle_sort']
					);
}
}

//BP위젯 환경설정 해당 첨부파일의 썸네일 삭제
if(!function_exists('wgf_delete_widget_config_thumbnail')){
function wgf_delete_widget_config_thumbnail($file){
	if(!$file) return;
	$fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_WIDGET_DATA_PATH.'/config/thumb-'.$fn.'*');
    if (is_array($files)) {
        foreach ($files as $filename)
            unlink($filename);
    }
}
}

//BP위젯 해당 첨부파일의 썸네일 삭제
if(!function_exists('wgf_delete_widget_thumbnail')){
function wgf_delete_widget_thumbnail($wig_idx, $fle_type, $file)
{
    if(!$wig_idx || !$fle_type || !$file)
        return;

    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_WIDGET_DATA_PATH.'/file/'.$wig_idx.'/'.$fle_type.'/thumb-'.$fn.'*');
    if (is_array($files)) {
        foreach ($files as $filename)
            unlink($filename);
    }
}
}

//BP위젯 해당 콘텐츠(bwgc_idx)의 첨부파일과 썸네일 삭제
if(!function_exists('wgf_delete_widget_content_files')){
function wgf_delete_widget_content_files($wig_idx, $fle_type, $file)
{	
    if(!$wig_idx || !$fle_type || !$file)
        return;

	$fnp = G5_WIDGET_DATA_PATH.'/file/'.$wig_idx.'/'.$fle_type.'/'.$file;
	unlink($fnp);
	
    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(G5_WIDGET_DATA_PATH.'/file/'.$wig_idx.'/'.$fle_type.'/thumb-'.$fn.'*');
    if (is_array($files)) {
        foreach ($files as $filename)
            unlink($filename);
    }
}
}


//-- BP위젯 해당 위치코드의  레코드와 관련된 BP위젯내용들을 삭제 --//
if(!function_exists('wgf_delete_widget_cd')){
function wgf_delete_widget_cd($wig_cd)
{
	global $g5;
	
	$wig_id_sql = sql_fetch(" SELECT wig_idx FROM {$g5['widget_table']} WHERE wig_cd = '{$wig_cd}' ");
	$wig_id = $wig_id_sql['wig_idx'];
	//해당 BP위젯레코드 삭제
	sql_query(" DELETE FROM {$g5['widget_table']} WHERE wig_cd = '$wig_cd' ");
	//해당 DATA폴더 안에 있는 BP위젯 코드 폴더와 파일을 통채로 삭제
	$wig_cd_data_path = G5_WIDGET_DATA_PATH.'/'.$wig_cd;
	wgf_delete_all_file_widget_cd($wig_cd_data_path);
}
}

//삭제하고자 하는 BP위젯 위치코드가 있으면 해당 data폴더 안에 있는 파일들을 전부 삭제하는 함수
//인수는 해당 위치코드 폴더의 절대경로를 대입해라 ex)G5_DATA_PATH.'/bpwidget/'.$bwgs_cd
if(!function_exists('wgf_delete_all_file_widget_cd')){
function wgf_delete_all_file_widget_cd($wig_cd_data_path){
	//인수가 반드시 있어야 한다.
    if(!$wig_cd_data_path)
        return;
	//path의 최종이름이 파일이라면
    if(is_file($wig_cd_data_path)) {
		//파일을 바로 삭제한다.
		return unlink($wig_cd_data_path);
    }
	//path의 최종이름이 디렉토리이며 존재하면
	else if(is_dir($wig_cd_data_path)){
		//이 디렉토리 안에 있는 파일들의 목록을 불러온다.
		$scan = glob(rtrim($wig_cd_data_path,'/').'/*');
		//파일목록들로 루프를 돌린다.
		foreach($scan as $index=>$path){
			//루프를 돌리면서 자기 함수를 반복해서 호출하며 파일들을 삭제한다.
			wgf_delete_all_file_widget_cd($path);
		}
		//위 실행이 다 끝나면 최종 자신의 폴더를 삭제한다.
		return @rmdir($wig_cd_data_path);
	}
}
}

//환경선택박스
if(!function_exists('wgf_select_selected')){
function wgf_select_selected($field, $name, $val, $no_val=0, $required=0, $disable=0){ //인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status','ok',0,1)
    $bwgf_values = explode(',', preg_replace("/\s+/", "", $field));
	if(!count($bwgf_values)) return false;
	$readonly_str = ($disable) ? 'readonly onFocus="this.initialSelect=this.selectedIndex;" onChange="this.selectedIndex=this.initialSelect;"' : '';
	if($disable)
		$select_tag = '<select '.$readonly_str.' name="'.$name.'" id="'.$name.'"'.(($required) ? ' required':'').' class="'.(($required) ? 'required':'').'">'.PHP_EOL;
	else
		$select_tag = '<select name="'.$name.'" id="'.$name.'"'.(($required) ? ' required':'').' class="'.(($required) ? 'required':'').'">'.PHP_EOL;
		
	$i = 0;
	if($no_val){ //값없는 항목이 존재할때
		$select_tag .= '<option value=""'.((!$val) ? ' selected="selected"' : '').'>선택안됨</option>'.PHP_EOL;
		$i++;
	}
	foreach ($bwgf_values as $bwgf_value) {
		list($key, $value) = explode('=', $bwgf_value);
		$selected = '';
		if($val){ //수정값이 존재하면
			if(is_int($key)){
				$selected = ((int) $val===$key) ? ' selected="selected"' : '';
			}else{
				$selected = ($val===$key) ? ' selected="selected"' : '';
			}
		}else{ //등록 또는 수정값이 존재하지 않은면
			if(!$no_val){//값없는 항목이 존재하지 않을때
				if($i == 0) $selected = ' selected="selected"';
			}
		}
		$select_tag .= '<option value="'.trim($key).'"'.$selected.'>'.trim($value).'</option>'.PHP_EOL;
		$i++;
	}
	$select_tag .= '</select>'.PHP_EOL;
	$i = 0;
	return $select_tag;
}
}

//환경라디오박스
if(!function_exists('wgf_radio_checked')){
function wgf_radio_checked($field, $name, $val, $disable=0){ //인수('pending=대기,ok=정상,hide=숨김,trash=삭제','bwgf_status','ok',1)	
	$bwgf_values = explode(',', preg_replace("/\s+/", "", $field));
	if(!count($bwgf_values)) return false;
	$i = 0;
	$name = ' '.$name;
	$radio_tag = (count($bwgf_values) >= 2) ? '<div style="display:inline-block;">' : '';
	foreach ($bwgf_values as $bwgf_value) {
		list($key, $value) = explode('=', $bwgf_value);
		$checked = '';
		$first_child = ($i == 0) ? ' first_child' : '';
		if($val){ //수정값이 존재하면
			if(is_int($key)){
				$checked = ((int) $val===$key) ? ' checked="checked"' : '';
			}else{
				$checked = ($val===$key) ? ' checked="checked"' : '';
			}
		}else{ //등록 또는 수정값이 존재하지 않은면
			if($i == 0) $checked = ' checked="checked"';
		}
		
		$disabled = ($disable) ? ' onclick="return(false);"' : '';
		
		$radio_tag .= '<label for="'.trim($name).'_'.$key.'" class="label_radio'.$first_child.$name.'"><input type="radio" id="'.trim($name).'_'.$key.'" name="'.trim($name).'" value="'.$key.'"'.$checked.$disabled.'><strong></strong><span>'.$value.'</span></label>'.PHP_EOL;
		$i++;
	}
	$radio_tag .= (count($bwgf_values) >= 2) ? '</div>' : '';
	$i = 0;
	return $radio_tag;
}
}
//환경체크박스
if(!function_exists('wgf_check_checked')){
function wgf_check_checked($name, $label, $val, $default_chk=0){ //네임속성값,라벨텍스트,값,기본값on/off(값이 없을때)
    global $w;
	
	$checked = '';
	if($val){ //수정값이 존재하면
		if($val == 1 || $val == 'on' || $val == 'ON' || $val == 'checked' || $val == 'CHECKED' || $val == 'check' || $val == 'CHECK' || $val == '체크' || $val == '첵크' || $val == 'ok' || $val == 'OK' || $val >= 2)
			$checked = ' checked="checked"';
	}else{ //등록 또는 수정값이 존재하지 않은면
		if($w == '' && $default_chk == 1) $checked = ' checked="checked"';
	}
	$check_tag = '<label for="'.$name.'" class="label_checkbox '.$name.'"><input type="checkbox" id="'.$name.'" name="'.$name.'" value="'.$val.'"'.$checked.'><strong></strong><span>'.$label.'</span></label>'.PHP_EOL;
	return $check_tag;
}
}

// 입력 폼 안내문
if(!function_exists('wgf_help')){	
function wgf_help($help="",$iup=0,$bgcolor='#ffffff',$fontcolor='#555555'){
    global $g5;
	$iupclass = ($iup) ? "iup" : 'idown';
	$str = ($help) ? '<div class="bwg_info_box"><p class="bwg_info '.$iupclass.'" style="background:'.$bgcolor.';color:'.$fontcolor.';">'.str_replace("\n", "<br>", $help).'</p></div>' : '';
    return $str;
}
}

// 디비 테이블의 시퀀스 초기화 함수
if(!function_exists('wgf_dbtable_sequence_reset')){
function wgf_dbtable_sequence_reset($table_name){
	$tbl_exist = @sql_query(" DESC ".$table_name." ",false);
	if($tbl_exist){
		$record_exist = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$table_name} ");
		if(!$record_exist['cnt']) sql_query(" ALTER TABLE {$table_name} auto_increment = 1 ");
	}
}
}

//색상코드 16진수를 rgb로, rgb를 16진수로 반환해주는 함수
if(!function_exists('wgf_rgb2hex2rgb')){
function wgf_rgb2hex2rgb($color){ //인수에 '#ff0000' 또는 '255,0,0'를 넣어 호출하면 된다.
	if(!$color) return false; 
	$color = trim($color); 
	$result = false; 
	if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $color)){
		$hex = str_replace('#','', $color);
		if(!$hex) return false;
		if(strlen($hex) == 3):
			$result['r'] = hexdec(substr($hex,0,1).substr($hex,0,1));
			$result['g'] = hexdec(substr($hex,1,1).substr($hex,1,1));
			$result['b'] = hexdec(substr($hex,2,1).substr($hex,2,1));
		else:
			$result['r'] = hexdec(substr($hex,0,2));
			$result['g'] = hexdec(substr($hex,2,2));
			$result['b'] = hexdec(substr($hex,4,2));
		endif;
		$result = $result['r'].','.$result['g'].','.$result['b']; //텍스트(255,0,0)로 표시하고 싶으면 주석 해제해라
	}elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $color)){ 
		$color = str_replace(' ','',$color);
		$rgbstr = str_replace(array(',',' ','.'), ':', $color); 
		$rgbarr = explode(":", $rgbstr);
		$result = '#';
		$result .= str_pad(dechex($rgbarr[0]), 2, "0", STR_PAD_LEFT);
		$result .= str_pad(dechex($rgbarr[1]), 2, "0", STR_PAD_LEFT);
		$result .= str_pad(dechex($rgbarr[2]), 2, "0", STR_PAD_LEFT);
		$result = strtoupper($result); 
	}else{
		$result = false;
	}

	return $result; 
}
}

//내부url은 G5_URL을 추가해서 반환
if(!function_exists('wgf_g5_url_check')){
function wgf_g5_url_check($url){
	$complete_url = $url;
	if(substr($url,0,1) == '/' || substr($url,0,1) == '#' || substr($url,0,1) == '?'){
		$complete_url = G5_URL.$url;
	}
	
	return $complete_url;
}
}


//js 파일 인쿠르드 함수
if(!function_exists('wgf_cache_data')){	
function wgf_cache_data($wig_cd, $cache_list=array(), $find_mb_id=0){
    static $cache = array();
	
	$wig_cd .= 'wgf_'.$wig_cd;
	
    if( $wig_cd && $cache_list && ! isset($cache[$wig_cd]) ){
        foreach( (array) $cache_list as $mb ){
            if( empty($mb) || ! isset($mb['mb_id']) ) continue;
            $cache[$wig_cd][$mb['mb_id']] = $mb;
        }
    }
    
    if( $find_mb_id && isset($cache[$wig_cd][$find_mb_id]) ){
        return $cache[$wig_cd][$find_mb_id];
    }
}
}

//svg 파일을 인쿠르드와 색상설정하는 함수(컬러설정값은 기본적으로 단색 아이콘에만 반영이됨)
if(!function_exists('wgf_icon')){
function wgf_icon($svg_name='',$svg_color='#efefef',$width_data=30,$height_data=30,$fill=false,$svg_color2='#efefef'){
	//인수설명 : $svg_name=cart,대표컬러'#ffffff',서브컬러'red',채우기여부(false),폭숫자(px),높이숫자(px)
	global $g5;
	//svg_name값이 없으면 실행하지 않는다.
	if(!$svg_name) return;
	
	$icid = 'icon_'.$svg_name.'_'.wgf_uniqid();
	$width = ($width_data == 'auto') ? $width_data : $width_data.'px';
	$height = ($height_data == 'auto') ? $height_data : $height_data.'px';
	//svg_name값은 존재하는데, 배열 키값으로 존재하지 않으면 실행하지 않는다.
	if(!array_key_exists($svg_name,$g5['wgf_svg_php_name'])) return;
	
	if(is_file($g5['wgf_svg_php_name'][$svg_name]['path']))
		include($g5['wgf_svg_php_name'][$svg_name]['path']);
}
}

//svg 파일을 인쿠르드와 색상설정하는 함수(컬러설정값은 기본적으로 단색 아이콘에만 반영이됨)
if(!function_exists('wgf_get_icon')){
function wgf_get_icon($svg_name,$svg_color='#efefef',$width_data=30,$height_data=30,$fill=false,$svg_color2='#efefef'){
	//인수설명 : $svg_name=cart,대표컬러'#ffffff',서브컬러'red',채우기여부(false),폭숫자(px),높이숫자(px)
	global $g5;
	//svg_name값이 없으면 실행하지 않는다.
	if(!$svg_name) return false;
	
	$icid = 'icon_'.$svg_name.'_'.wgf_uniqid();
	$width = ($width_data == 'auto') ? $width_data : $width_data.'px';
	$height = ($height_data == 'auto') ? $height_data : $height_data.'px';
	//svg_name값은 존재하는데, 배열 키값으로 존재하지 않으면 실행하지 않는다.
	if(!array_key_exists($svg_name,$g5['bwg_svg_php_name'])) return;

	if(is_file($g5['wgf_svg_php_name'][$svg_name]['path'])){
		ob_start();
		include($g5['wgf_svg_php_name'][$svg_name]['path']);
		$bwgs_icon_tag = ob_get_contents();
		ob_end_clean();
		return $bwgs_icon_tag;
	}	
}
}

//체크박스 checkbox 관련 함수
if(!function_exists('wgf_check')){
function wgf_check($name,$chk_flag=false,$txt='',$width_data=26,$height_data=26,$color='#efef',$color2='#efef'){
	//인수:name,체크여부,라벨텍스트,가로폭,세로폭,테두리색상,체크색상
	global $g5;
	//svg_name값이 없으면 실행하지 않는다.
	if(!$name) return;
	
	$ipid = 'ip_'.$name.'_'.wgf_uniqid();
	$width = ($width_data == 'auto') ? $width_data : $width_data.'px';
	$height = ($height_data == 'auto') ? $height_data : $height_data.'px';
	//svg_name값은 존재하는데, 배열 키값으로 존재하지 않으면 실행하지 않는다.
	if(!array_key_exists('check',$g5['wgf_check_radio_svg'])) return;
	
	if(is_file($g5['wgf_check_radio_svg']['check']['path']))
		include($g5['wgf_check_radio_svg']['check']['path']);
}	
}
//랜덤문자열 생성하는 함수
/*
기본---------------------------get_random_string() . '
숫자만-------------------------get_random_string('09') . '
숫자만 30글자------------------get_random_string('09', 30) . '
소문자만-----------------------get_random_string('az') . '
대문자만-----------------------get_random_string('AZ') . '
소문자+대문자------------------get_random_string('azAZ') . '
소문자+숫자--------------------get_random_string('az09') . '
대문자+숫자--------------------get_random_string('AZ09') . '
소문자+대문자+숫자-------------get_random_string('azAZ09') . '
특수문자만---------------------get_random_string('$') . '
숫자+특수문자------------------get_random_string('09$') . '
소문자+특수문자----------------get_random_string('az$') . '
대문자+특수문자----------------get_random_string('AZ$') . '
소문자+대문자+특수문자---------get_random_string('azAZ$') . '
소문자+대문자+숫자+특수문자----get_random_string('azAZ09$') . '
*/
if(!function_exists('wgf_get_random_string')){
function wgf_get_random_string($type = '', $len = 10) {
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numeric = '0123456789'; 
    $special = '`~!@#$%^&*()-_=+\\|[{]};:\'",<.>/?';
    $key = '';
    $token = '';
    if ($type == '') {
        $key = $lowercase.$uppercase.$numeric;
    } else {
        if (strpos($type,'09') > -1) $key .= $numeric;
        if (strpos($type,'az') > -1) $key .= $lowercase; 
        if (strpos($type,'AZ') > -1) $key .= $uppercase;
        if (strpos($type,'$') > -1) $key .= $special;
    }
 
    for ($i = 0; $i < $len; $i++) {
        $token .= $key[mt_rand(0, strlen($key) - 1)];
    }
    return $token;
}
}

//유니크값을 반환하는 함수
if(!function_exists('wgf_uniqid')){
function wgf_uniqid(){
	$start_ran = mt_rand(0,38);
	$cnt_ran = mt_rand(4,7);
	$uniq = substr(uniqid(md5(rand())),$start_ran,$cnt_ran);
	$uniq2 = substr(uniqid(md5(rand())),$start_ran,$cnt_ran);
	//$uniq3 = substr(uniqid(md5(rand())),$start_ran,$cnt_ran);
	//return bpwg_get_random_string('az',3).$uniq.$uniq2.$uniq3;
	return wgf_get_random_string('az',3).$uniq.$uniq2;
}	
}

//접속한 디바이스 타입
if (!function_exists('wgf_deviceCheck')){
function wgf_deviceCheck(){
	if( stristr($_SERVER['HTTP_USER_AGENT'],'ipad') ) {
		$device = "ipad";
	} else if( stristr($_SERVER['HTTP_USER_AGENT'],'iphone') ||
		strstr($_SERVER['HTTP_USER_AGENT'],'iphone') ) {
		$device = "iphone";
	} else if( stristr($_SERVER['HTTP_USER_AGENT'],'ipod') ||
		strstr($_SERVER['HTTP_USER_AGENT'],'ipod') ) {
		$device = "ipod";
	} else if( stristr($_SERVER['HTTP_USER_AGENT'],'blackberry') ) {
		$device = "blackberry";
	} else if( stristr($_SERVER['HTTP_USER_AGENT'],'android') ) {
		$device = "android";
	} else {
		$device = "etc";
	}
	return $device;
}
}

//접속한 브라우저의 이름/버전을 반환해 주는 함수
if (!function_exists('wgf_browserCheck')){
function wgf_browserCheck(){
	/*
	크롬 : Chrome/Safari
	파폭 : Firefox
	익11 : Trident
	익10 : MSIE
	훼일 : Chrome/Whale/Safari
	엣지 : Chrome/Safari/Edge
	*/
	$userAgent = $_SERVER["HTTP_USER_AGENT"];
	//echo $userAgent;
	if ( preg_match("/MSIE*/", $userAgent) ) {
		// 익스플로러
		if ( preg_match("/MSIE 6.0[0-9]*/", $userAgent) ) {
			$browser = "ie6"; //"explorer6";
		}else if ( preg_match("/MSIE 7.0*/", $userAgent) ) {
			$browser = "ie7"; //"explorer7";
		}else if ( preg_match("/MSIE 8.0*/", $userAgent) ) {
			$browser = "ie8"; //"explorer8";
		}else if ( preg_match("/MSIE 9.0*/", $userAgent) ) {
			$browser = "ie9"; //"explorer9";
		}else if ( preg_match("/MSIE 10.0*/", $userAgent) ) {
			$browser = "ie10"; //"explorer10";
		}else{
			// 익스플로러 기타
			$browser = "ie100"; //"explorerETC";
		}
	}
	else if(preg_match("/Trident*/", $userAgent) && preg_match("/rv:11.0*/", $userAgent) && preg_match("/Gecko*/", $userAgent)){
		$browser = "ie11"; //"explorer11";
	}

	else if ( preg_match("/Edge*/", $userAgent) ) {
		// 엣지
		$browser = "edge";
	}
	else if ( preg_match("/Firefox*/", $userAgent) ) {
		// 모질라 (파이어폭스)
		$browser = "firefox";
	}
	//else if ( preg_match("/(Mozilla)*/", $userAgent) ) {
	// // 모질라 (파이어폭스)
	// $browser = "mozilla";
	//}
	//else if ( preg_match("/(Nav|Gold|X11|Mozilla|Nav|Netscape)*/", $userAgent) ) {
	// // 네스케이프, 모질라(파이어폭스)
	// $browser = "Netscape/mozilla";
	//}
	else if ( preg_match("/Safari*/", $userAgent) && preg_match("/WOW/", $userAgent) ) {
		// 사파리
		$browser = "safari";
	}
	else if ( preg_match("/OPR*/", $userAgent) ) {
		// 오페라
		$browser = "opera";
	}
	else if ( preg_match("/DaumApps*/", $userAgent) ) {
		// daum
		$browser = "daum";
	}
	else if ( preg_match("/KAKAOTALK*/", $userAgent) ) {
		// kakaotalk
		$browser = "kakaotalk";
	}
	else if ( preg_match("/NAVER*/", $userAgent) ) {
		// kakaotalk
		$browser = "naver";
	}
	else if ( preg_match("/Whale*/", $userAgent) ) {
		// 크롬
		$browser = "whale";
	}
	else if ( preg_match("/Chrome/", $userAgent) 
		&& !preg_match("/Whale/", $userAgent) 
		&& !preg_match("/WOW/", $userAgent) 
		&& !preg_match("/OPR/", $userAgent) 
		&& !preg_match("/DaumApps/", $userAgent) 
		&& !preg_match("/KAKAOTALK/", $userAgent) 
		&& !preg_match("/NAVER/", $userAgent) 
		&& !preg_match("/Edge/", $userAgent) ) {
		// 크롬
		$browser = "chrome";
	}
	
	else{
		$browser = "other";
	}
	return $browser; //$userAgent;//$browser;
}
}


//ie브라우저인지 확인해 주는 함수 위 browserCheck()함수 사용함
if (!function_exists('wgf_is_explorer')){
function wgf_is_explorer(){
	/*
	크롬 : Chrome/Safari
	파폭 : Firefox
	익11 : Trident
	익10 : MSIE
	훼일 : Chrome/Whale/Safari
	엣지 : Chrome/Safari/Edge
	*/
	$browser_name = wgf_browserCheck();
	$ie_flag = false;
	if(preg_match("/ie/", $browser_name)){
		$ie_flag = true;
	}

	return $ie_flag;
}
}

//전체 uri의 get변수 중 특정 영역의 변수값을 반환
if(!function_exists('wgf_uriReturnGetValue')){
function wgf_uriReturnGetValue($getArea,$ky){
	$uri_arr = explode('&',$getArea);
	foreach($uri_arr as $uri_get){
		list($key,$value) = explode('=',trim($uri_get));
		$uriArr[$key] = $value;
	}
	return $uriArr[$ky];
}
}

//전체 url에서 get변수 영역만 추출하는 함수, 전체 uri에서 get변수만 추출하는 함수
if(!function_exists('wgf_uriReturnGetArea')){
function wgf_uriReturnGetArea($uri,$ky){
	$pos = stripos($uri,$ky);
	if(is_int($pos)){
		return substr($uri,$pos+strlen($ky));
	}
	return false;
}
}
//색상/투명도 설정 input form 생성 함수
if(!function_exists('wgf_input_color')){
function wgf_input_color($name='',$value='#333333',$w='',$alpha_flag=0){
	global $g5,$config,$default,$member,$is_admin;
	
	//if($name == '') return '컬러픽커 name값이 없습니다.';
	
	$aid = wgf_get_random_string('az',4).'_'.wgf_uniqid();
	$bid = wgf_get_random_string('az',4).'_'.wgf_uniqid();
	$cid = wgf_get_random_string('az',4).'_'.wgf_uniqid();
	//그외 랜덤id값
	$did = wgf_get_random_string('az',4).'_'.wgf_uniqid();
	$eid = wgf_get_random_string('az',4).'_'.wgf_uniqid();
	
	
	if($alpha_flag){
		if(substr($value,0,1) == '#') $value = 'rgba('.wgf_rgb2hex2rgb($value).',1)';
		$input_color = (isset($value)) ? $value : 'rgba(51, 51, 51, 1)';
		//echo $value;
		$bgrgba = substr(substr($input_color,5),0,-1);//처음에 'rgba('를 잘라낸뒤 반환하고, 그다음 끝에 ')'를 잘라내고 '255, 0, 0, 0'를 반환
		$rgba_arr = explode(',',$bgrgba);
		$bgrgb = trim($rgba_arr[0]).','.trim($rgba_arr[1]).','.trim($rgba_arr[2]);
		$bga = trim($rgba_arr[3]);
		//echo $bga;
		$bg16 = ($w == 'u') ? wgf_rgb2hex2rgb($bgrgb) : '#333333';//#FF0000
	}
	else{
		if(substr($value,0,4) == 'rgba'){
			$rgb_str_arr = explode(',',substr(substr($value,5),0,-1));
			$rgb_str = $rgb_str_arr[0].','.$rgb_str_arr[1].','.$rgb_str_arr[2];
			$value = wgf_rgb2hex2rgb($rgb_str);
		}
		$input_color = ($value) ? $value : '#333333';
	}
	
	ob_start();
    include G5_USER_ADMIN_FORM_PATH.'/input_color.skin.php';
    $input_content = ob_get_contents();
    ob_end_clean();

    return $input_content;
}
}
//범위(range) input form 생성 함수
if(!function_exists('wgf_input_range')){
function wgf_input_range($rname='',$val='1',$w='',$min='0',$max='1',$step='0.1',$width='100',$padding_right=29,$unit=''){
	global $g5,$config,$default,$member,$is_admin;
	
	if(preg_match("/%/", $width)){
		$width = substr($width,0,-1);
		$wd_class = ' wg_wdp'.$width;
	}else{
		$wd_class = ' wg_wdx'.$width;
	}
	
	$output_show = '';
	if(!$padding_right || $padding_right == '0'){
		$output_show = 'display:none;';
		$padding_right_style='';
		$wd_class = '';
	}else{
		$padding_right_style = 'padding-right:'.$padding_right.'px;';
	}
	
	$rid = 'r_'.wgf_uniqid();
	$rinid = 'rin_'.wgf_uniqid();
	$rotid = 'rot_'.wgf_uniqid();
	
	ob_start();
    include G5_USER_ADMIN_FORM_PATH.'/input_range.skin.php';
    $input_content = ob_get_contents();
    ob_end_clean();

    return $input_content;
}	
}

// url에 http:// 를 붙인다
if(!function_exists('wgf_set_http')){
function wgf_set_http($url){
    if (!trim($url)) return;
	
	$htp_s = (G5_HTTPS_DOMAIN == '') ? 'http://' : 'https://';
    if (!preg_match("/^(http|https|ftp|telnet|news|mms)\:\/\//i", $url) && substr($url,0,1)!='#')
        $url = $htp_s.$url;

    return $url;
}
}

//위젯 해당스킨안의 특정폴더안에 목록을 선택박스로 추출
if(!function_exists('wgf_get_file_select')){
function wgf_get_file_select($dir_parent_path, $dir_name, $id, $name, $selected='', $event=''){
	global $config;

    $files = array();
	
	$dirs = wgf_get_file_dir($dir_name, $dir_parent_path);
	if(!empty($dirs)) {
		foreach($dirs as $dir) {
			if(preg_match('/\.skin\.php$/',$dir))
				$files[] = $dir;
		}
	}
	
	$files = array_merge($files, wgf_get_file_dir($dir_name));
	
	$str = '';
	if(count($files)){
		$str .= "<select id=\"$id\" name=\"$name\" $event class=\"$event\">\n";
		for ($i=0; $i<count($files); $i++) {
			//if ($i == 0) $str .= "<option value=\"\">선택</option>";
			$txt_arr = explode('.',$files[$i]);
			$text = $txt_arr[0];

			$str .= option_selected($files[$i], $selected, $text);
		}
		$str .= "</select>";
	}
    return $str;
}
}

//위젯 해당스킨안의 특정폴더안에 특정 단어와 매칭되는 목록을 선택박스로 추출
if(!function_exists('wgf_get_file_match_select')){
function wgf_get_file_match_select($dir_parent_path, $dir_name, $id, $name, $selected='', $event='', $mtch=''){
	global $config,$match;

    $files = array();
	
	$dirs = wgf_get_file_dir($dir_name, $dir_parent_path);
	if(!empty($dirs)) {
		foreach($dirs as $dir) {
			if(preg_match('/^'.$mtch.'([a-zA-Z0-9].*)\.skin\.php/',$dir)){
				$files[] = $dir;
			}
		}
	}
	
	$files = array_merge($files, wgf_get_file_dir($dir_name));
	
	$str = '';
	if(count($files)){
		$str .= "<select match=\"$match\" id=\"$id\" name=\"$name\" $event class=\"$event\">";
		for ($i=0; $i<count($files); $i++) {
			//if ($i == 0) $str .= "<option value=\"\">선택</option>";
			$txt_arr = explode('.',$files[$i]);
			$text = $txt_arr[0];

			$str .= wgf_option_selected($files[$i], $selected, $text);
		}
		$str .= "</select>";
	}
	
    return $str;
}	
}

//위젯 해당스킨안의 특정폴더의 경로를 얻는다.
if(!function_exists('wgf_get_file_dir')){
function wgf_get_file_dir($dir_name, $dir_path=G5_WIDGET_PATH){
	global $g5;

    $result_array = array();
	
	
    $dirname = $dir_path.'/'.$dir_name.'/';
    if(!is_dir($dirname))
        return;
	
    $handle = opendir($dirname);
    while ($file = readdir($handle)) {
        if($file == '.'||$file == '..') continue;

        if (!is_dir($dirname.$file)) $result_array[] = $file;
    }
    closedir($handle);
    sort($result_array);

    return $result_array;
	
}
}

//위젯의 선택박스의 option요소를 구성해 주는 함수
if(!function_exists('wgf_option_selected')){
function wgf_option_selected($value, $selected, $text=''){
    if (!$text) $text = $value;
    if ($value == $selected)
        return "<option value=\"$value\" selected=\"selected\">$text</option>";
    else
        return "<option value=\"$value\">$text</option>";
}
}

//위젯 상품이미지에 유형 아이콘 출력
if(!function_exists('wgf_item_icon')){
function wgf_item_icon($it){
    global $g5;

    $icon = '<span class="sit_icon">';

    if ($it['it_type1'])
        $icon .= '<span class="shop_icon shop_icon_1">#히트</span>';

    if ($it['it_type2'])
        $icon .= '<span class="shop_icon shop_icon_2">#추천</span>';

    if ($it['it_type3'])
        $icon .= '<span class="shop_icon shop_icon_3">#최신</span>';

    if ($it['it_type4'])
        $icon .= '<span class="shop_icon shop_icon_4">#인기</span>';

    if ($it['it_type5'])
        $icon .= '<span class="shop_icon shop_icon_5">#할인</span>';


    // 쿠폰상품
    $sql = " select count(*) as cnt
                from {$g5['g5_shop_coupon_table']}
                where cp_start <= '".G5_TIME_YMD."'
                  and cp_end >= '".G5_TIME_YMD."'
                  and (
                        ( cp_method = '0' and cp_target = '{$it['it_id']}' )
                        OR
                        ( cp_method = '1' and ( cp_target IN ( '{$it['ca_id']}', '{$it['ca_id2']}', '{$it['ca_id3']}' ) ) )
                      ) ";
    $row = sql_fetch($sql);
    if($row['cnt'])
        $icon .= '<span class="shop_icon shop_icon_coupon">#쿠폰</span>';

    $icon .= '</span>';

    return $icon;
}
}

//BP위젯 sns 공유하기
if(!function_exists('wgf_get_sns_share_link')){
function wgf_get_sns_share_link($sns, $url, $title, $img){
    global $config;

    if(!$sns)
        return '';

    switch($sns) {
        case 'facebook':
            $str = '<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($url).'&amp;p='.urlencode($title).'" class="share-facebook" target="_blank"><img src="'.$img.'" alt="페이스북에 공유"></a>';
            break;
        case 'twitter':
            $str = '<a href="https://twitter.com/share?url='.urlencode($url).'&amp;text='.urlencode($title).'" class="share-twitter" target="_blank"><img src="'.$img.'" alt="트위터에 공유"></a>';
            break;
        case 'googleplus':
            $str = '<a href="https://plus.google.com/share?url='.urlencode($url).'" class="share-googleplus" target="_blank"><img src="'.$img.'" alt="구글플러스에 공유"></a>';
            break;
        case 'kakaotalk':
            if($config['cf_kakao_js_apikey'])
                $str = '<a href="javascript:kakaolink_send(\''.str_replace('+', ' ', urlencode($title)).'\', \''.urlencode($url).'\');" class="share-kakaotalk"><img src="'.$img.'" alt="카카오톡 링크보내기"></a>';
            break;
    }

    return $str;
}
}

//BP위젯 상품 이미지를 얻는다
if(!function_exists('wgf_get_it_image')){
function wgf_get_it_image($it_id, $width, $height=0, $anchor=false, $img_id='', $img_alt='', $is_crop=false, $is_url=false){
    global $g5;

    if(!$it_id || !$width)
        return '';

    $row = get_shop_item($it_id, true);

    //if(!$row['it_id'])
    //    return '';

    $filename = $thumb = $img = '';

    for($i=1;$i<=10; $i++) {
        $file = G5_DATA_PATH.'/item/'.$row['it_img'.$i];
        if(is_file($file) && $row['it_img'.$i]) {
            $size = @getimagesize($file);
            if($size[2] < 1 || $size[2] > 3)
                continue;

            $filename = basename($file);
            $filepath = dirname($file);
            $img_width = $size[0];
            $img_height = $size[1];

            break;
        }
    }

    if($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    if($filename) {
        //thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create, $is_crop=false, $crop_mode='center', $is_sharpen=true, $um_value='80/0.5/3')
        $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, $is_crop, 'center', true, $um_value='85/3.4/15');
    }

    if($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath.'/'.$thumb);
        $img = '<img src="'.$file_url.'" width="'.$width.'" height="'.$height.'" alt="'.$img_alt.'"';
    } else {
        $file_url = G5_SHOP_URL.'/img/no_image.gif';
		$img = '<img class="no_img" src="'.$file_url.'" width="'.$width.'" height="'.$height.'"';
        //if($height)
        //    $img .= ' height="'.$height.'"';
        $img .= ' alt="'.$img_alt.'"';
    }

    if($img_id)
        $img .= ' id="'.$img_id.'"';
    $img .= '>';

    if($anchor)
        $img = $img = '<a href="'.shop_item_url($it_id).'">'.$img.'</a>';

    return ($is_url) ? $file_url : run_replace('get_it_image_tag', $img, $thumb, $it_id, $width, $height, $anchor, $img_id, $img_alt, $is_crop);
}
}

//위젯 상품이미지 썸네일 생성
if(!function_exists('wgf_get_it_thumbnail')){
function wgf_get_it_thumbnail($img, $width, $height=0, $id='', $is_crop=false){
    $str = '';

    if ( $replace_tag = run_replace('get_it_thumbnail_tag', $str, $img, $width, $height, $id, $is_crop) ){
        return $replace_tag;
    }

    $file = G5_DATA_PATH.'/item/'.$img;
    if(is_file($file))
        $size = @getimagesize($file);

    if($size[2] < 1 || $size[2] > 3)
        return '';

    $img_width = $size[0];
    $img_height = $size[1];
    $filename = basename($file);
    $filepath = dirname($file);

    if($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, $is_crop, 'center', true, $um_value='85/3.4/15');

    if($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath.'/'.$thumb);
        $str = '<img src="'.$file_url.'" width="'.$width.'" height="'.$height.'"';
        if($id)
            $str .= ' id="'.$id.'"';
        $str .= ' alt="">';
    }

    return $str;
}
}

//위젯 상품이미지 썸네일 생성한 후 썸네일의 URL을 반환하는 함수
if(!function_exists('wgf_get_it_thumbnail_url')){
function wgf_get_it_thumbnail_url($img, $width, $height=0, $id='', $is_crop=false){
    $file_url = '';

    if ( $replace_tag = run_replace('get_it_thumbnail_tag', $str, $img, $width, $height, $id, $is_crop) ){
        return $replace_tag;
    }

    $file = G5_DATA_PATH.'/item/'.$img;
    if(is_file($file))
        $size = @getimagesize($file);

    if($size[2] < 1 || $size[2] > 3)
        return '';

    $img_width = $size[0];
    $img_height = $size[1];
    $filename = basename($file);
    $filepath = dirname($file);

    if($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, $is_crop, 'center', true, $um_value='85/3.4/15');
	
    if($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath.'/'.$thumb);
    }

    return $file_url;
}
}

//게시물 wr_content 데이터 유형별 사이즈 재설정 함수
if(!function_exists('wgf_get_wr_content_size')){
function wgf_get_wr_content_size(){
    $sql = "SELECT COLUMN_TYPE
                  FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_NAME = '%s'
                      AND COLUMN_NAME = '%s'
                      AND TABLE_SCHEMA = '%s'";

    $sql = sprintf($sql, $GLOBALS['write_table'], 'wr_content', G5_MYSQL_DB);

    $query = sql_query($sql);
    $row = sql_fetch_array($query, sql_num_rows($query));
 

    switch($row['COLUMN_TYPE']) {
        case 'tinytext': return 256;
        case 'text': return 65535;
        case 'mediumtext': return 16777215;
        case 'longtext': return 4294967295;
    }
}
}

//현재 사이트가 http://인지 https://인지를 확인
if(!function_exists('wgf_is_https')){
function wgf_is_https(){	
	if ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')  || $_SERVER['SERVER_PORT'] == 443) {		
		return true; 
	}
	return false;
}
}
//투명이미지를 만드는 함수
if(!function_exists('wgf_transparent_image')){
function wgf_transparent_image($width=10, $height=10, $test=''){
	return "<img class='trans_image' src='".G5_USER_ADMIN_IMG_URL."/text2img.php?width=".$width."&height=".$height."&color=black&left=center&font_size=16&test=".$test."&txt=' border='0'>";
}
}
//노이미지(no image, no img, noimage, noimg)를 만드는 함수
if(!function_exists('wgf_no_image')){
function wgf_no_image($width=10, $height=10, $icon_size=3, $icon_color='gray'){
	$no_img_tag = "<img class='trans_image' style='position:relative;display:block;width:100% !important;height:auto !important;' src='".G5_USER_ADMIN_IMG_URL."/text2img.php?width=".$width."&height=".$height."&color=black&left=center&font_size=16&txt=' border='0'>".PHP_EOL;
	$no_img_tag .= "<div style='position:absolute !important;z-index:0 !important;top:0px !important;left:0px !important;width:100% !important;height:100% !important;'>".PHP_EOL;
	$no_img_tag .= "<div style='display:table;width:100% !important;height:100% !important;'>".PHP_EOL;
	$no_img_tag .= "<div style='display:table-cell;text-align:center !important;vertical-align:middle !important;height:100% !important;'>".PHP_EOL;
	$no_img_tag .= "<i class='fa fa-picture-o' style='font-size:".$icon_size."em;color:".$icon_color.";'></i>".PHP_EOL;
	$no_img_tag .= "</div></div></div>".PHP_EOL;
	
	return $no_img_tag;
}
}

// 한페이지에 보여줄 행, 현재페이지, 총페이지수, URL
if(!function_exists('wgf_get_paging')){
function wgf_get_paging($write_pages, $cur_page, $total_page, $url, $add="")
{
    //$url = preg_replace('#&amp;page=[0-9]*(&amp;page=)$#', '$1', $url);
    $url = preg_replace('#(&amp;)?page=[0-9]*#', '', $url);
	$url .= substr($url, -1) === '?' ? 'page=' : '&amp;page=';

    $str = '';
    if ($cur_page > 1) {
        $str .= '<a href="'.$url.'1'.$add.'" class="pg_page pg_start"><i class="fa fa-angle-double-left" aria-hidden="true"></i></a>'.PHP_EOL;
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= '<a href="'.$url.($start_page-1).$add.'" class="pg_page pg_prev"><i class="fa fa-angle-left" aria-hidden="true"></i></a>'.PHP_EOL;

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= '<a href="'.$url.$k.$add.'" class="pg_page">'.$k.'<span class="sound_only">페이지</span></a>'.PHP_EOL;
            else
                $str .= '<span class="sound_only">열린</span><strong class="pg_current">'.$k.'</strong><span class="sound_only">페이지</span>'.PHP_EOL;
        }
    }

    if ($total_page > $end_page) $str .= '<a href="'.$url.($end_page+1).$add.'" class="pg_page pg_next"><i class="fa fa-angle-right" aria-hidden="true"></i></a>'.PHP_EOL;

    if ($cur_page < $total_page) {
        $str .= '<a href="'.$url.$total_page.$add.'" class="pg_page pg_end"><i class="fa fa-angle-double-right" aria-hidden="true"></i></a>'.PHP_EOL;
    }

    if ($str)
        return "<nav class=\"pg_wrap\"><span class=\"pg\">{$str}</span></nav>";
    else
        return "";
}
}
?>