<?php
$sub_menu = "955600";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$html_title = ($w=='')?'추가':'수정'; 

$g5['title'] = '설비(iCMMS) '.$html_title;
//include_once('./_top_menu_mms.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    $com['com_name'] = '아이앤지글로벌';
    $mms['com_idx'] = 7;
	$mms['imp_idx'] = 13;
	$imp['imp_name'] = 'IMP 3';
	$mms['mmg_idx'] = 12;
	$mmg['mmg_name'] = '공장';
    $mms['mms_sort'] = 1;
    $mms['mms_set_output'] = $mms['mms_set_error'] = 'shift';
    $mms['mms_data_url'] = 'bogwang.epcs.co.kr/device/json';
    $mms['mms_status'] = 'ok';
	$html_title = '추가';
    
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	$mms = get_table_meta('mms','mms_idx',$mms_idx);
	if (!$mms['mms_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',$mms['com_idx']);
	$imp = get_table_meta('imp','imp_idx',$mms['imp_idx']);
    $mmg = get_table_meta('mms_group','mmg_idx',$mms['mmg_idx']);
    // print_r2($mms);
	
	$html_title = '수정';
	
	$mms['mms_price'] = number_format($mms['mms_price']);
    $mms['mms_set_output'] = $mms['mms_set_output'] ?: 'shift';
    $mms['mms_set_error'] = $mms['mms_set_error'] ?: 'shift';

	// 관련 파일 추출
	$sql = "SELECT * FROM {$g5['file_table']} 
			WHERE fle_db_table = 'mms' AND fle_db_id = '".$mms['mms_idx']."' ORDER BY fle_sort, fle_reg_dt DESC ";
	$rs = sql_query($sql,1);
//	echo $sql;
	for($i=0;$row=sql_fetch_array($rs);$i++) {
		$mms[$row['fle_type']][$row['fle_sort']]['file'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							'&nbsp;&nbsp;'.$row['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$row['fle_path'].'/'.$row['fle_name']).'&file_name_orig='.$row['fle_name_orig'].'">파일다운로드</a>'
							.'&nbsp;&nbsp;<input type="checkbox" name="'.$row['fle_type'].'_del['.$row['fle_sort'].']" value="1"> 삭제'
							:'';
		$mms[$row['fle_type']][$row['fle_sort']]['fle_name'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_name'] : '' ;
		$mms[$row['fle_type']][$row['fle_sort']]['fle_path'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							$row['fle_path'] : '' ;
		$mms[$row['fle_type']][$row['fle_sort']]['exists'] = (is_file(G5_PATH.$row['fle_path'].'/'.$row['fle_name'])) ? 
							1 : 0 ;
	}
	
    
	// 대표이미지
	$fle_type3 = "mms_img";
	if($mms[$fle_type3][0]['fle_name']) {
		$mms[$fle_type3][0]['thumbnail'] = thumbnail($mms[$fle_type3][0]['fle_name'], 
						G5_PATH.$mms[$fle_type3][0]['fle_path'], G5_PATH.$mms[$fle_type3][0]['fle_path'],
						45, 45, 
						false, true, 'center', true, $um_value='80/0.5/3'
		);	// is_create, is_crop, crop_mode
	}
	else {
		$mms[$fle_type3][0]['thumbnail'] = 'default.png';
		$mms[$fle_type3][0]['fle_path'] = '/data/'.$fle_type3;
	}
	//$mms[$fle_type3][0]['thumbnail_img'] = '<img src="'.G5_URL.$mms[$fle_type3][0]['fle_path'].'/'.$mms[$fle_type3][0]['thumbnail'].'" width="45" height="45">';
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');


if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}
?>

