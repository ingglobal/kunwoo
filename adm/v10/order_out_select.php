<?php
// 호출 페이지들
// /adm/v10/bom_form.php: 제품(BOM)수정: 거래처찾기
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

$where = array();




$g5['title'] = '생산계획 검색'.$total_count_display;
include_once('./_head.sub.php');

$qstr1 = 'frm='.$frm.'&d='.$d.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word).'&file_name='.$file_name;

add_stylesheet('<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/ui-darkness/jquery-ui.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.structure.min.css">', 1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/jquery-ui-1.12.1/jquery-ui.min.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker-ko.js"></script>',1);
add_javascript('<script src="'.G5_USER_ADMIN_JS_URL.'/bwg_datepicker.js"></script>',1);
?>
<style>

</style>



<script>

</script>
<?php
include_once('./_tail.sub.php');