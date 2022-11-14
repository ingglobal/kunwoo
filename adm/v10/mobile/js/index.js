// 대시보드(index) 스크립트
$(function(e){

});


// data_right (그래프 영역) 폭 추출
window.onload = reSize;
window.onresize = reSize;
function reSize() {
    // data_right_width = $('.data_right').width();
    // console.log( data_right_width );

}


// mms 설정 클릭 시 팝오버
$(document).on('click','.icon_mms_setting',function(e){
    if( $('.span_mms_setting').is(':hidden') ) {
        $('.span_mms_setting').show();
        $(this).find('i').removeClass('fa-gear').addClass('fa-times');
    }
    else {
        $('.span_mms_setting').hide();
        $(this).find('i').removeClass('fa-times').addClass('fa-gear');
    }
});


// 개별보기 창을 클릭 시 각각 새창이 열림
$( ".graph_newwin" ).on( "click", function() {
    var icon = $( this );
    var imp_idx = icon.closest("li").attr('imp_idx');
    var dta_type = icon.closest("li").attr('dta_type');
    var href = './graph_each.php?imp_idx='+imp_idx+'&dta_type='+dta_type;
    var winName = "winGraphEach_"+imp_idx+"_"+dta_type;
    var win_left = 100 + dta_type*20;
    var win_top = 100 + dta_type*20;
    var winLocation = "left="+win_left+",top="+win_top;
    eval( winName+" = window.open('"+href+"', '"+dta_type+"', '"+winLocation+", width=460, height=400, scrollbars=0'); ");
    eval( winName+".focus(); ");
});


// 설비이력카드 팝업
$(document).on('click','.mms_image img, .set_mms_view',function(e){
    e.preventDefault();
    var href = './mms_view.popup.php?&mms_idx='+my_mms_idx;
    winMMSView = window.open(href, "winMMSView", "left=100,top=100,width=520,height=600,scrollbars=1");
    winMMSView.focus();
    if( $(this).prop('tagName')!='IMG' ) {
        $('.icon_mms_setting').trigger('click');
    }
    return false;
});

// 부속품 클릭
$(document).on('click','.set_mms_parts',function(e){
    e.preventDefault();
    var href = './mms_parts_list.php?&mms_idx='+my_mms_idx;
    winParts = window.open(href, "winParts", "left=100,top=100,width=520,height=600,scrollbars=1");
    winParts.focus();
    $('.icon_mms_setting').trigger('click');
    return false;
});

// 기종 클릭
$(document).on('click','.set_mms_item',function(e){
    e.preventDefault();
    var href = './mms_item_list.php?&mms_idx='+my_mms_idx;
    winItem = window.open(href, "winItem", "left=100,top=100,width=520,height=600,scrollbars=1");
    winItem.focus();
    $('.icon_mms_setting').trigger('click');
    return false;
});

// 정비 클릭
$(document).on('click','.set_mms_maintain',function(e){
    e.preventDefault();
    var href = './maintain_list.php?&mms_idx='+my_mms_idx;
    winMaintain = window.open(href, "winMaintain", "left=100,top=100,width=520,height=600,scrollbars=1");
    winMaintain.focus();
    $('.icon_mms_setting').trigger('click');
    return false;
});

// 점검기준 클릭
$(document).on('click','.set_mms_checks',function(e){
    e.preventDefault();
    var href = './mms_checks_list.php?&mms_idx='+my_mms_idx;
    winChecks = window.open(href, "winChecks", "left=100,top=100,width=520,height=600,scrollbars=1");
    winChecks.focus();
    $('.icon_mms_setting').trigger('click');
    return false;
});

// 교대및목표
$(document).on('click','.set_mms_shift',function(e){
    e.preventDefault();
    var href = './mms_shift_list.php?&mms_idx='+my_mms_idx;
    winShift = window.open(href, "winShift", "left=100,top=100,width=520,height=600,scrollbars=1");
    winShift.focus();
    $('.icon_mms_setting').trigger('click');
    return false;
});

// 그래프설정
$(document).on('click','.set_mms_graph_setting',function(e){
    e.preventDefault();
    var href = './mms_graph_setting.php?&mms_idx='+my_mms_idx;
    winGraphSetting = window.open(href, "winGraphSetting", "left=100,top=100,width=520,height=600,scrollbars=1");
    winGraphSetting.focus();
    $('.icon_mms_setting').trigger('click');
    return false;
});

// 설비설정
$(document).on('click','.set_mms_setting',function(e){
    e.preventDefault();
    var href = './mms_setting.php?&mms_idx='+my_mms_idx;
    winMMSSetting = window.open(href, "winMMSSetting", "left=100,top=100,width=520,height=600,scrollbars=1");
    winMMSSetting.focus();
    $('.icon_mms_setting').trigger('click');
    return false;
});


// 배치도 button, window popup
$(document).on('click','.btn_mms_group',function(e){
    e.preventDefault();
    var com_idx = $(this).attr('com_idx') || '';
    if(com_idx=='') {
        alert('소속 업체 정보가 존재하지 않습니다.');
    }    
    else {
        var href = $(this).attr('href');
        winAddChart = window.open(href+'?file_name='+file_name+'&com_idx='+com_idx,"winAddChart","left=100,top=100,width=520,height=600,scrollbars=1");
        winAddChart.focus();
    }    
});    
