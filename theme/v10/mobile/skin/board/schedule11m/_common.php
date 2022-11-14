<?php
if (!defined('G5_COMMUNITY_USE')) {
    define('_SHOP_', true); // 쇼핑몰
}

// 공통파일 삽입
$g5_path['path'] = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/theme/'));
if(!$g5_path['path'])
    $g5_path['path'] = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], '/bbs/'));
include_once($g5_path['path'].'/common.php');   // 설정 파일
unset($g5_path);

//-- REQUEST 변수 재정의 (변수명이 너무 길어~) --//
while( list($key, $val) = each($_REQUEST) ) {
	${$key} = $_REQUEST[$key];
}

// 디폴트 게시판
$bo_table = ($bo_table)? $bo_table : 'schedule';


// 게시판 환경설정값 추출
if ($bo_table) {
    $board = get_board($bo_table);
    
    // wr_id 가 있으면 $write 배열 확장(+serialized 변수들)
    if($wr_id && is_serialized($write['wr_9'])) {
        $write = array_merge($write, get_serialized($write['wr_9']));
    }
}
//print_r2($board);
//print_r2($write);

// 통계제외 상태값
$set_notin_status_array = ($board['set_notin_status']) ? explode(',', preg_replace("/\s+/", "", $board['set_notin_status'])) : '';
//print_r2($set_notin_status_array);

// 구분타입 및 타입별가격 설정
$set_values = explode(',', preg_replace("/\s+/", "", $board['bo_8']));
foreach ($set_values as $set_value) {
	list($key, $value) = explode('=', $set_value);
	$g5['set_schedule_type'][$key] = $value.' ('.$key.')';
	$g5['set_schedule_type_value'][$key] = $value;
	$g5['set_schedule_type_radios'] .= '<label for="set_schedule_type_'.$key.'" class="set_schedule_type"><input type="radio" id="set_schedule_type_'.$key.'" name="set_schedule_type" value="'.$key.'">'.$value.'('.$key.')</label>';
	$g5['set_schedule_type_checkboxs'] .= '<label for="set_schedule_type_'.$key.'" class="set_schedule_type"><input type="hidden" name="set_schedule_type_'.$key.'" value=""><input type="checkbox" id="set_schedule_type_'.$key.'">'.$value.'('.$key.')</label>';
	$g5['set_schedule_type_buttons'] .= '<a href="javascript:" class="set_schedule_type" cmm_status="'.$key.'">'.$value.'</a>';
	$g5['set_schedule_type_options'] .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
	$g5['set_schedule_type_options_value'] .= '<option value="'.trim($key).'">'.trim($value).'</option>';
}
unset($set_values);unset($set_value);
//print_r2($g5['set_schedule_type_value']);

// 상태값
$set_values = explode(',', preg_replace("/\s+/", "", $board['bo_9']));
foreach ($set_values as $set_value) {
	list($key, $value) = explode('=', $set_value);
	$g5['set_schedule_status'][$key] = $value.' ('.$key.')';
	$g5['set_schedule_status_value'][$key] = $value;
	$g5['set_schedule_status_radios'] .= '<label for="set_schedule_status_'.$key.'" class="set_schedule_status"><input type="radio" id="set_schedule_status_'.$key.'" name="set_schedule_status" value="'.$key.'">'.$value.'('.$key.')</label>';
	$g5['set_schedule_status_checkboxs'] .= '<label for="set_schedule_status_'.$key.'" class="set_schedule_status"><input type="hidden" name="set_schedule_status_'.$key.'" value=""><input type="checkbox" id="set_schedule_status_'.$key.'">'.$value.'('.$key.')</label>';
	$g5['set_schedule_status_buttons'] .= '<a href="javascript:" class="set_schedule_status" cmm_status="'.$key.'">'.$value.'</a>';
	$g5['set_schedule_status_options'] .= '<option value="'.trim($key).'">'.trim($value).' ('.$key.')</option>';
	$g5['set_schedule_status_options_value'] .= '<option value="'.trim($key).'">'.trim($value).'</option>';
}
unset($set_values);unset($set_value);

// 시간
$set_values = explode(',', preg_replace("/\s+/", "", $board['set_hours']));
foreach ($set_values as $set_value) {
	$g5['set_hours_options'] .= '<option value="'.trim($set_value).'">'.trim($set_value).'시간</option>';
}
unset($set_values);unset($set_value);

?>