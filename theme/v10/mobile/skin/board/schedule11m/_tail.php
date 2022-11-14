<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
?>

<!-- 체크박스 공통 라이브러리 -->
<script src="<?=G5_USER_JS_URL?>/Custom-jQuery-Form-Elements/jquery-form-elements.js"></script>
<script>
$(function() {
    $('input[type=checkbox]').customCheckbox();
    $('input[type=radio]').customRadio();
});

// 검색아이콘 클릭 시 열림 닫힘
$(document).on('click','.top_btn_search',function(e){
    if( $('#search_wrapper').is(':hidden') )
        $('#search_wrapper').show();
    else
        $('#search_wrapper').hide()
});
$(document).on('click','.sch_btn_close',function(e){
    e.preventDefault();
    $('#search_wrapper').hide()
});
</script>


<?php
include_once(G5_PATH.'/_tail.php');
?>