$(document).ready(function() {

	// 회원 검색 관련
	aj01_dom=$('#aj01');
	aj01_copy_tr=aj01_dom.find('table tbody tr').eq(0).clone();	// 초기 dom 복사본
	aj01_copy_td=aj01_copy_tr.find('td').eq(0).clone();
	aj01_dom.find('table tbody tr').not('.tr_loading').hide();	// 로딩만 남기고 tr 전체 숨김
	
	// 엔터키 또는 검색버튼 클릭 시 폼 전송(name이 _form으로 끝나는 태그를 선택)
	aj01_dom.find('form[name$=_form]').submit(function(e){
		e.preventDefault(e);
		aj01_list(aj01_dom,1,modal_mb_lv);
	});
	// 전체 목록 버튼 클릭 시
	aj01_dom.find('form[name$=_form] .btn_all').click(function(e){
		e.preventDefault();
		aj01_dom.find('input[name$=_stx]').val('');
		aj01_list(aj01_dom,1,modal_mb_lv);
	});
	//--==== //AjaxList01 관련 --//

	// 회원검색 클릭 (일반회원, 업체회원 둘다 검색)
	$(document).on('click','#btn_mb_id',function(e) {
//	$('#btn_mb_id').click( function(e) {
		// 아이디 초기화
		$('input[name=mb_id]').val('');
		$('input[name=mb_id2]').val('');
		$('input[name=mb_nick]').val('');
	
		e.preventDefault();
		modal_mb_lv = '2,4'; // company_form.php 파일 script 영역에 정의
		$('#modal01').find('.modal_header .modal_title').text('회원 검색');
		$(this).blur();	// IE8 에서 포커스가 남아 있어서 추가함
		$('.tr_new_reg').hide();
		if( $('input[name=w]').val() == 'n' )
			$('input[name=w]').val('');	// 신규 등록 w 값 변경

		// 모달 창 오픈
		$('#modal01').off().on('show.bs.modal', function (e) {
			aj01_dom.find('input[name$=_stx]').val('');	// 모달 검색 키워드 초기화
			aj01_list(aj01_dom,1,modal_mb_lv);
		});
		$('#modal01').modal('show');
	});

	//  영업자검색 클릭
	$(document).on('click','#btn_mb_id_saler',function(e) {
//	$('#btn_mb_id_saler').click( function(e) {
		e.preventDefault();
		modal_mb_lv = 6; // company_form.php 파일 script 영역에 정의
		$('#modal01').find('.modal_header .modal_title').text('영업자 검색');
		$(this).blur();	// IE8 에서 포커스가 남아 있어서 추가함
		// 모달 창 오픈
		$('#modal01').off().on('show.bs.modal', function (e) {
			aj01_dom.find('input[name$=_stx]').val('');	// 모달 검색 키워드 초기화
			aj01_list(aj01_dom,1,modal_mb_lv);
		});
		$('#modal01').modal('show');
	});
	
	//  선택 버튼 클릭 시 (영업자 & 일반회원)
	$(document).on('click','#modal01 .td_select a',function(e) { // 업체 선택
//	$('#modal01 .td_select a').click( function(e) {
		e.preventDefault();
		// 영업자 회원 입력 (여러 영업자 연결 가능)
		if(modal_mb_lv == 6) {
			$('.td_sales').append('<div class="div_salesman" style="margin-bottom:5px;">'
								+ '<input readonly type="hidden" name="cmm_idx[]" />'
								+ '<input readonly type="hidden" name="cmm_status[]" value="ok" />'
								+ '<input readonly type="text" placeholder="영업자ID" name="mb_id_saler[]" value="'+$(this).attr('mb_id')+'"'
									+ 'required class="frm_input required" readonly size="15" minlength="3" maxlength="30">'
								+ '<input readonly type="text" placeholder="영업자명" name="mb_name_saler[]" value="'+$(this).attr('mb_name')+'"'
									+ ' class="frm_input" style="background-color:#dadada;margin-left:4px;margin-right:4px;" size="15" minlength="3" maxlength="30">'
								+ '[<span class="span_saler_delete" style="cursor:pointer;">삭제</span>]'
								+ '</div>'
			);
			//$('input[name=mb_id_saler]').val( $(this).attr('mb_id') );
			//$('input[name=mb_name_saler]').val( $(this).attr('mb_name') );
		}
		// 일반회원 입력
		else {
			$('input[name=mb_id]').val( $(this).attr('mb_id') );
			$('input[name=mb_name]').val( $(this).attr('mb_name') );
			$('input[name=com_president]').val( $(this).attr('mb_name') );
			$('input[name=com_tel]').val( $(this).attr('mb_hp') );
			$('input[name=com_email]').val( $(this).attr('mb_email') );
			$('input[name=com_manager]').val( $(this).attr('mb_name') );
			$('input[name=com_manager_hp]').val( $(this).attr('mb_hp') );
			$('input[name=com_zip]').val( $(this).attr('mb_zip1')+$(this).attr('mb_zip2') );
			$('input[name=com_addr1]').val( $(this).attr('mb_addr1') );
			$('input[name=com_addr2]').val( $(this).attr('mb_addr2') );
			$('input[name=com_addr3]').val( $(this).attr('mb_addr3') );
			$('input[name=com_addr_jibeon]').val( $(this).attr('mb_addr_jibeon') );
		}
		
		// 모달 창 닫기
		$(this).closest('div[id^=modal]').modal('hide');
	});
	// //회원 검색 관련

});	// ehd of $(document).ready(function()	-------------


// 회원 리스트 함수
if(typeof(aj01_list)!='function') {
function aj01_list(tdom,pagenum,mb_lv) {
	
	//-- 파라메타 설정 --//
	aj01_sfl = tdom.find('[name$=_sfl]').val();
	aj01_stx = encodeURIComponent(tdom.find('input[name$=_stx]').val());	//url 인코딩
	aj01_where = encodeURIComponent(tdom.find('input[name$=_where]').val());	//url 인코딩
	if(pagenum==undefined) pagenum=1;	//현재 페이지

	var list_dom=tdom.find('table tbody');
	list_dom.find('tr').not('.tr_nothing, .tr_loading').remove();
	
	// 로딩중 표시
	tdom.find('.tr_loading').show();
	
	//-- 리스트 호출 --//
	//-- 디버깅 Ajax --//
	//$.ajax({
	//	url:g5_user_url+'/ajax/member.json.php',
	//	type:'get', data:{"aj":"list","lv":"4","aj_sfl":aj01_sfl,"aj_stx":aj01_stx,"pagenum":pagenum,"aj_where":aj01_where},
	//	dataType:'json', timeout:3000, beforeSend:function(){}, success:function(res) {
	$.getJSON(g5_user_url+'/ajax/member.json.php',{"aj":"list","lv":mb_lv,"aj_sfl":aj01_sfl,"aj_stx":aj01_stx,"pagenum":pagenum,"aj_where":aj01_where},function(res) {
			//alert(res.sql);
			//console.log(res.sql +'<br>'+ mb_lv);
			
			// 로딩중 표시 숨김
			tdom.find('.tr_loading').hide();
			
			//-- 리스트가 없는 경우 --//
			if(res.total == 0) {
				// 등록 자료가 없다(TR) 보임
				ajtable_nothing_display(list_dom);
			}
			//-- 리스트가 있을 경우 --//
			else {
				// 등록 자료가 없다(TR) 숨김
				ajtable_nothing_display(list_dom);

				//기존 리스트 삭제
				list_dom.find("tr").not('.tr_nothing, .tr_loading').remove();

				//전체 카운터 표시
				//alert(res.total_page +'page / total:'+ res.total);
				tdom.find('span.count_total').text(res.total);

				try{
					$.each(res.rows,function(i,v){
						sdomtr = aj01_copy_tr.clone();
						//sdomtd = aj01_copy_td.clone();

						// 값 입력
						sdomtr.find('td.td_mb_name').text( v['mb_name'] );
						sdomtr.find('td.td_mb_id').text( v['mb_id'] );
						sdomtr.find('td.td_mb_hp').text( v['mb_hp2'] );
						sdomtr.find('td.td_mb_email').text( v['mb_email2'] );
						sdomtr.find('td.td_select a').attr({'mb_id':v['mb_id']
														,'mb_name':v['mb_name']
														,'mb_email':v['mb_email']
														,'mb_hp':v['mb_hp']
														,'mb_zip1':v['mb_zip1']
														,'mb_zip2':v['mb_zip2']
														,'mb_addr1':v['mb_addr1']
														,'mb_addr2':v['mb_addr2']
														,'mb_addr3':v['mb_addr3']
														,'mb_jibeon':v['mb_jibeon']
						});

						// tr에 td추가
						sdomtr.appendTo(list_dom).show();
						list_dom.show();
					});

					//-- Paging 표현 --//
					if(res.total_page > 1) {
						tdom.find('ul.pg').paging({
							current:pagenum
							,format:'<a class="pg_page" data-page="{0}">{0}</a>'
							,item:"li"	// IE8 에서는 그냥 a 테그는 안 되는군! 보통은 li
							,itemCurrent:"pg_current"
							,next:'<a class="pg_page pg_next" data-page="{5}">다음</i></a>'
							,prev:'<a class="pg_page pg_perv" data-page="{4}">이전</a>'
							,first:'<a class="pg_page pg_start" data-page="1">처음</i></a>'
							,last:'<a class="pg_page pg_end" data-page="{6}">맨끝</i></a>'
							,length:5
							,href:'#{0}'
							,max:res.total_page
							,onclick: function(e) {
								page = $(e.target).attr('data-page');
								if(page != pagenum) {
									//alert($(e.target).attr('data-page'))
									aj01_list(tdom,page,mb_lv);
									pagenum = page;
								}
							}
						});
						tdom.find('ul.pg').parent().show();
					}
					else {
						tdom.find('ul.pg').parent().hide();
					}
						
					// 리스트 존재 여부 업데이트(혹시 모르니까~)
					ajtable_nothing_display(list_dom);

				} catch(e){}

			}

		//}, error:this_ajax_error ////-- 디버깅 Ajax --//
	});	
}}
// //회원 리스트 함수
