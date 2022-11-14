$(document).ready(function() {

	// 상품 모달  관련 ===============
	aj05_dom=$('#aj05');
	aj05_copy_tr=aj05_dom.find('table tbody tr').eq(0).clone();	// 초기 dom 복사본
	aj05_copy_td=aj05_copy_tr.find('td').eq(0).clone();
	aj05_dom.find('table tbody tr').not('.tr_loading, .tr_nothing').hide();	// 로딩만 남기고 tr 전체 숨김
	
	// 엔터키 또는 검색버튼 클릭 시 폼 전송(name이 _form으로 끝나는 태그를 선택)
	aj05_dom.find('form[name$=_form]').submit(function(e){
		e.preventDefault(e);
		aj05_list(aj05_dom,1);
	});
	// 전체 목록 버튼 클릭 시
	aj05_dom.find('form[name$=_form] .btn_all').click(function(e){
		e.preventDefault();
		aj05_dom.find('input[name$=_stx]').val('');
		aj05_list(aj05_dom,1);
	});

	// 상품검색 클릭
	$(document).on('click','#btn_add_cart',function(e) {
		e.preventDefault();
		$(this).blur();	// IE8 에서 포커스가 남아 있어서 추가함
		
		// 모달 창 오픈
		$('#modal05').off().on('show.bs.modal', function (e) {
			aj05_dom.find('input[name$=_stx]').val('');	// 모달 검색 키워드 초기화
			aj05_list(aj05_dom,1);	// 함수 호출(디폴트 1페이지 호출),level
		});
		$('#modal05').modal('show');
	});

	// 상품 선택 버튼 클릭 시
	$(document).on('click','#modal05 .td_select a',function(e) { // 상품 선택
		e.preventDefault();

		// 견적문의 상품인 경우
		var it_price = $(this).closest('tr').find('input[name^=it_price]').val();
		
		if( $(this).attr('it_price') == '견적문의' && it_price == '') {
			alert('견적가격을 입력하세요.');
		}
		else if(confirm('완료된 신청건에 대한 상품변경은 정산 혼란을 초래할 수 있습니다. \n추가한 후 받드시 매출정산 등을 확인하세요.\n\n상품을 추가하시겠습니까?')) {
			
			var od_id = $('input[name=od_id]').val();
			var it_id = $(this).attr('it_id');
			
			// 선택한 상품을 장바구니에 추가한다. ajax처리 (주문 정보도 함께 처리해야 함)
			//-- 리스트 호출 --//
			//-- 디버깅 Ajax --//
			//$.ajax({
			//	url:g5_user_url+'/ajax/cart.json.php',
			//	type:'get', data:{"aj":"put","od_id":od_id,"it_id":it_id,"it_price":it_price},
			//	dataType:'json', timeout:3000, beforeSend:function(){}, success:function(res) {
			$.getJSON(g5_user_url+'/ajax/cart.json.php',{"aj":"put","od_id":od_id,"it_id":it_id,"it_price":it_price},function(res) {
					//alert(res.sql);
					if(res.result == true) {
						alert("'주문' 상태로 상품을 추가하였습니다. 결제금액을 확인하시고 매출 반영해 주세요.");
						// 현재창 새로고침
						self.location.reload();
					}
					else {
						alert(res.msg);
					}				

			//}, error:this_ajax_error	////-- 디버깅 Ajax --//
			});
			
		}
	});
	// //상품 모달  관련 ===============

	
});	// ehd of $(document).ready(function()	-------------


// 상품 호출 함수 ==============
if(typeof(aj05_list)!='function') {
function aj05_list(tdom,pagenum) {
	
	//-- 파라메타 설정 --//
	aj05_sfl = tdom.find('[name$=_sfl]').val();
	aj05_stx = encodeURIComponent(tdom.find('input[name$=_stx]').val());	//url 인코딩
	aj05_where = encodeURIComponent(tdom.find('input[name$=_where]').val());	//url 인코딩
	if(pagenum==undefined) pagenum=1;	//현재 페이지
	
	var list_dom=tdom.find('table tbody');
	list_dom.find('tr').not('.tr_nothing, .tr_loading').remove();
	
	// 로딩중 표시
	tdom.find('.tr_loading').show();
	
	//-- 리스트 호출 --//
	//-- 디버깅 Ajax --//
	//$.ajax({
	//	url:g5_user_url+'/ajax/item.json.php',
	//	type:'get', data:{"aj":"list","aj_sfl":aj05_sfl,"aj_stx":aj05_stx,"pagenum":pagenum,"aj_where":aj05_where},
	//	dataType:'json', timeout:3000, beforeSend:function(){}, success:function(res) {
	$.getJSON(g5_user_url+'/ajax/item.json.php',{"aj":"list","aj_sfl":aj05_sfl,"aj_stx":aj05_stx,"pagenum":pagenum,"aj_where":aj05_where},function(res) {
			//alert(res.sql);
			
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
						sdomtr = aj05_copy_tr.clone();
						//sdomtd = aj05_copy_td.clone();
						
						if(v['it_price'] == '견적문의')
							var it_price_text = '<input name="it_price[]" class="frm_input" style="width:70px;padding:4px 6px;">';
						else 
							var it_price_text = v['it_price'];

						// 값 입력
						sdomtr.find('.div_it_name').text( v['it_name'] );
						sdomtr.find('.div_it_basic').text( v['it_basic'] );
						sdomtr.find('td.td_it_price').html( it_price_text );
						sdomtr.find('td.td_select a').attr({'it_id':v['it_id']
														,'it_name':v['it_name']
														,'it_price':v['it_price']
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
									aj05_list(tdom,page);
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

	//}, error:this_ajax_error	////-- 디버깅 Ajax --//
	});	
}}
// //상품 호출 함수 ==============

