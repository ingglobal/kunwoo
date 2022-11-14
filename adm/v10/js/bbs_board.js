if(!g5_is_mobile){ //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ PC 버전일때 @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//######## 목록페이지 관련 ##############
// 하단의 버튼그룹을 상단탑에 고정
if($('.bo_fx').length > 0) $('.bo_fx').addClass('btn_fixed_top');
if($('.bo_fx .btn_admin').length > 0){
    $('.bo_fx .btn_admin').empty().addClass('btn_01').text($('.bo_fx .btn_admin').attr('title')).unwrap('li').removeClass('btn_admin');
}
if($('.bo_fx a[href*="bbs_write"]').length > 0){
    $('.bo_fx a[href*="bbs_write"]').empty().addClass('btn_03').text($('.bo_fx a[href*="bbs_write"]').attr('title')).unwrap('li').removeClass('btn_01');
}


//######## 보기페이지 관련 ##############


} //PC 버전일때
else{ //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ MOBILE 버전일때 @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
if($('.btn_bo_user').length > 0) $('.btn_bo_user').addClass('btn_fixed_top');  
if($('.btn_bo_user .write_btn').length > 0){
    $('.btn_bo_user .write_btn').addClass('btn');
}
} //MOBILE 버전일때