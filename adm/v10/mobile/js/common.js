// 추가 관리자단(v10) 설정을 위한 js입니다.
// /extend/user.003.default.php 에서 선언되었습니다.

$(function(e) {

    // 메뉴 업데이트
    $('#gnb .gnb_li').each(function(e){
        
        // 하단에 아무것도 없으면 숨김(관리자가 아닌 경우만 숨김)
        if( $(this).find('.gnb_oparea ul li').length == 0) {
            $(this).closest('.gnb_li').remove();
        }
        
        // 메뉴 삭제, 링크수정 등 탑메뉴 정리
        var menu1txt = $(this).find('.btn_op').text();   //1차 메뉴명

        // 하단 메뉴 아이콘이 숨김이면 상위 li 도 함께 숨김
        //console.log( menu1txt +' is hidden? / '+ $(this).find('.btn_op').css('display') +'/'+ $(this).find('.btn_op').is(':hidden') );
        if( $(this).find('.btn_op').css('display')=='none' ) {
            //$(this).closest('.gnb_li').css('display','none');
            $(this).closest('.gnb_li').remove();
        }
        
        // 1차 메뉴 통째 제거
        if(menu1txt=='환경설정') {
            $(this).remove();
        }
        if(menu1txt=='회원관리') {
            $(this).remove();
        }
        if(menu1txt=='게시판관리') {
            $(this).remove();
        }
        if(menu1txt=='쇼핑몰관리') {
            $(this).remove();
        }
        if(menu1txt=='쇼핑몰현황/기타') {
            $(this).remove();
        }
        if(menu1txt=='SMS 관리') {
            $(this).remove();
        }
        if(menu1txt=='설정/관리') {
            $(this).remove();
        }
        if(menu1txt=='iCMMS') {
            // 모바일 관리자단은 한개뿐이므로 iCMMS라는 이름을 보일 필요 없음
            $(this).find('button').hide();
        }
    

        $(this).find('.gnb_oparea li').each(function(e){
            var menu2txt = $(this).find('a').text();    //2차 메뉴명
            // console.log( menu1txt +' / '+ menu2txt );
            if(menu1txt=='환경설정') {
                if(menu2txt=='테마설정') { $(this).remove(); }
                if(menu2txt=='메뉴설정') { $(this).remove(); }
                if(menu2txt=='세션파일 일괄삭제') { $(this).remove(); }
                if(menu2txt=='캐시파일 일괄삭제') { $(this).remove(); }
                if(menu2txt=='캡챠파일 일괄삭제') { $(this).remove(); }
                if(menu2txt=='썸네일파일 일괄삭제') { $(this).remove(); }
                if(menu2txt=='Browscap 업데이트') { $(this).remove(); }
                if(menu2txt=='DB업그레이드') { $(this).remove(); }
                if(menu2txt=='접속로그 변환') { $(this).remove(); }
                if(menu2txt=='부가서비스') { $(this).remove(); }
            }
            if(menu1txt=='회원관리') {
                if(menu2txt=='회원메일발송') { $(this).remove(); }
                if(menu2txt=='접속자집계') { $(this).remove(); }
                if(menu2txt=='접속자검색') { $(this).remove(); }
                if(menu2txt=='포인트관리') { $(this).remove(); }
                if(menu2txt=='투표관리') { $(this).remove(); }
            }
            if(menu1txt=='게시판관리') {
                if(menu2txt=='1:1문의설정') { $(this).remove(); }
                if(menu2txt=='내용관리') { $(this).remove(); }
                if(menu2txt=='FAQ관리') { $(this).remove(); }
                if(menu2txt=='글,댓글 현황') { $(this).remove(); }
                if(menu2txt=='인기검색어관리') { $(this).remove(); }
                if(menu2txt=='인기검색어순위') { $(this).remove(); }
            }
            if(menu1txt=='인트라게시판') {
                if(menu2txt=='업체게시판') { $(this).remove(); }
            }
            if(menu1txt=='인트라게시판') {
                if(menu2txt=='환경설정') { $(this).remove(); }
            }
            if(menu1txt=='iCMMS') {
                // console.log( menu1txt +' / '+ menu2txt );
                if(menu2txt=='설비관리') { $(this).remove(); }
                if(menu2txt=='계획정비') { $(this).remove(); }
                if(menu2txt=='정비이력') { $(this).remove(); }
                if(menu2txt=='부품재고') { $(this).remove(); }
                if(menu2txt=='매뉴얼') { $(this).remove(); }
                if(menu2txt=='설비사양서') { $(this).remove(); }
                if(menu2txt=='A/S연락처') { $(this).remove(); }
                if(menu2txt=='알람/예지') { $(this).remove(); }
                if(menu2txt=='알람목록조회') { $(this).remove(); }
                if(menu2txt=='예지목록조회') { $(this).remove(); }
                if(mb_level>3) {
                    if(menu2txt=='설비(iMMS)관리') { $(this).remove(); }
                    if(menu2txt=='API연동가이드') { $(this).remove(); }
                }
            }
        });
    });

	if($('#ft p').length > 0){
		$('<a href="javascript:self.location.reload();" id="pc_change"><i class="fa fa-refresh" aria-hidden="true"></i></a>').appendTo('#ft p');
	}
    
});	// ehd of $(document).ready(function()	-------------

