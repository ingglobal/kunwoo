if(!g5_is_mobile){ //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ PC 버전일때 @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//######## 쓰기페이지 관련 ##############
if($('.btn_confirm').length > 0) $('.btn_confirm').addClass('btn_fixed_top').removeClass('write_div').removeClass('btn_confirm');
$('.btn_fixed_top > .btn_cancel').addClass('btn_02');


} // PC 버전일때
else{ //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ MOBILE 버전일때 @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if($('.btn_confirm').length > 0) $('.btn_confirm').addClass('btn_fixed_top').removeClass('btn_confirm');
$('.btn_fixed_top > .btn_cancel').addClass('btn').text('').html('<i class="fa fa-times" aria-hidden="true"></i>');
} // MOBILE 버전일때