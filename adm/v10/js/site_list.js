$(document).ready(function() {

	// 원고접수처 수정 =======================================================
	$(document).on('click','.icon_wongo_type',function(e){
		e.preventDefault();
		
		// 정보 표시
		var sit_idx = $(this).closest('tr').attr('sit_idx');
		var sit_wongo_type = ($(this).attr('sit_wongo_type')) ?  $(this).attr('sit_wongo_type') : 'none' ;
		$('#modal100').find('input[name=sit_idx]').val( sit_idx );
		$('#modal100').find('input[id=sit_wongo_type_'+sit_wongo_type+']').prop('checked','checked');
		//alert(com_idx);
		$('#modal100').on('show.bs.modal', function (e) {})
		$('#modal100').modal('show');
	});
	
	// 원고접수처 수정 - [확인]
	$(document).on('click','#sit_wongo_type_submit',function(e){
		e.preventDefault();
		var target_form = $(this).closest('form');
		data_serialized = target_form.serialize();
		target_form.find('.btn_spinner').show();	// 진행중아이콘 표시
		target_form.find('input[type=submit]').hide();	// 확인버튼 숨김

		//-- 디버깅 Ajax --//
		//$.ajax({
		//	url:g5_user_url+'/ajax/site.json.php',
		//	data:{"aj":"wongo_type","data_serialized":data_serialized},
		//	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
		$.getJSON(g5_user_url+'/ajax/site.json.php',{"aj":"wongo_type","data_serialized":data_serialized},function(res) {
			//alert(res.sql);
			console.log(res);
			var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
			
			if(res.result == true) {
				//alert(res.msg);
				//alert(res.sit_wongo_type);
				$('#span_sit_wongo_type_'+res.sit_idx).html( res.sit_wongo_type_value );
				$('#modal100').modal('hide');
			}
			else {
				alert(res.msg);
			}
			
			target_form.find('.btn_spinner').hide();	// 진행중아이콘 숨김
			target_form.find('input[type=submit]').show();	// 확인버튼 표시
			target_form.find(':radio').prop("checked", false);	// 초기화
			
			//}, error:this_ajax_error	//<-- 디버깅 Ajax --//
		});
		
	});
	// //원고접수처 수정 =======================================================
	
	
	// 제작상태 수정 =======================================================
	$(document).on('click','.icon_sit_work_status',function(e){
		e.preventDefault();
		
		// 정보 표시
		var sit_idx = $(this).closest('tr').attr('sit_idx');
		var sit_work_status = $(this).attr('sit_work_status');
		$('#modal101').find('input[name=sit_idx]').val( sit_idx );
		$('#modal101').find('input[id=aj_sit_work_status_'+sit_work_status+']').prop('checked','checked');
		//alert(com_idx);
		$('#modal101').on('show.bs.modal', function (e) {})
		$('#modal101').modal('show');
	});
	
	// 제작상태 수정 - [확인]
	$(document).on('click','#sit_work_status_submit',function(e){
		e.preventDefault();
		var target_form = $(this).closest('form');
		data_serialized = target_form.serialize();
		target_form.find('.btn_spinner').show();	// 진행중아이콘 표시
		target_form.find('input[type=submit]').hide();	// 확인버튼 숨김

		//-- 디버깅 Ajax --//
		//$.ajax({
		//	url:g5_user_url+'/ajax/site.json.php',
		//	data:{"aj":"work_status","data_serialized":data_serialized},
		//	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
		$.getJSON(g5_user_url+'/ajax/site.json.php',{"aj":"work_status","data_serialized":data_serialized},function(res) {
			//alert(res.sql);
			console.log(res);
			var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
			
			if(res.result == true) {
				//alert(res.msg);
				//alert(res.sit_work_status);
				$('#span_sit_work_status_'+res.sit_idx).html( res.sit_work_status_value );
				$('#modal101').modal('hide');
			}
			else {
				alert(res.msg);
			}
			
			target_form.find('.btn_spinner').hide();	// 진행중아이콘 숨김
			target_form.find('input[type=submit]').show();	// 확인버튼 표시
			target_form.find(':radio').prop("checked", false);	// 초기화
			
			//}, error:this_ajax_error	//<-- 디버깅 Ajax --//
		});
		
	});
	// //제작상태 수정 =======================================================
	
	// 사이트상태 수정 =======================================================
	$(document).on('click','.icon_sit_status',function(e){
		e.preventDefault();
		
		// 정보 표시
		var sit_idx = $(this).closest('tr').attr('sit_idx');
		var sit_status = $(this).attr('sit_status');
		$('#modal102').find('input[name=sit_idx]').val( sit_idx );
		$('#modal102').find('input[id=aj_sit_status_'+sit_status+']').prop('checked','checked');
		//alert(com_idx);
		$('#modal102').on('show.bs.modal', function (e) {})
		$('#modal102').modal('show');
	});
	
	// 사이트상태 수정 - [확인]
	$(document).on('click','#sit_status_submit',function(e){
		e.preventDefault();
		var target_form = $(this).closest('form');
		data_serialized = target_form.serialize();
		target_form.find('.btn_spinner').show();	// 진행중아이콘 표시
		target_form.find('input[type=submit]').hide();	// 확인버튼 숨김

		//-- 디버깅 Ajax --//
		//$.ajax({
		//	url:g5_user_url+'/ajax/site.json.php',
		//	data:{"aj":"status","data_serialized":data_serialized},
		//	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
		$.getJSON(g5_user_url+'/ajax/site.json.php',{"aj":"status","data_serialized":data_serialized},function(res) {
			//alert(res.sql);
			console.log(res);
			var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
			
			if(res.result == true) {
				//alert(res.msg);
				//alert(res.sit_status);
				$('#span_sit_status_'+res.sit_idx).html( res.sit_status_value );
				$('#modal102').modal('hide');
			}
			else {
				alert(res.msg);
			}
			
			target_form.find('.btn_spinner').hide();	// 진행중아이콘 숨김
			target_form.find('input[type=submit]').show();	// 확인버튼 표시
			target_form.find(':radio').prop("checked", false);	// 초기화
			
			//}, error:this_ajax_error	//<-- 디버깅 Ajax --//
		});
		
	});
	// //사이트상태 수정 =======================================================
	

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

	
	// 회원검색 클릭
	$(document).on('click','.icon_mb_id_wongo',function(e) {
		e.preventDefault();
		modal_mb_lv = '6,8'; // site_list.php 파일 script 영역에 디폴트 정의
		var mb_name_target = $(this).attr('mb_name_target');	// 타겟을 계속 바꾸어 줌 (site_list.php 파일에 정의되어 있음)
		var sit_idx = $(this).closest('tr').attr('sit_idx');
		$(this).blur();	// IE8 에서 포커스가 남아 있어서 추가함
		// 모달 창 오픈
		$('#modal01').off().on('show.bs.modal', function (e) {
			// 기존 항목 제거 후
			aj01_dom.find('input[name=mb_name_target]').remove();	// 타겟돔 제거
			aj01_dom.find('input[name=sit_idx]').remove();	// sit_idx 설정

			// 설정값 추가
			aj01_dom.find('form[name$=_form]').append('<input type="hidden" name="mb_name_target" value="'+mb_name_target+'">');	// 타겟돔 설정
			aj01_dom.find('form[name$=_form]').append('<input type="hidden" name="sit_idx" value="'+sit_idx+'">');	// sit_idx 설정
			
			aj01_dom.find('input[name$=_stx]').val('');	// 모달 검색 키워드 초기화
			aj01_list(aj01_dom,1,modal_mb_lv);
		});
		$('#modal01').modal('show');
	});
	
	//  선택 버튼 클릭 시
	$(document).on('click','#modal01 .td_select a',function(e) { // 업체 선택
		e.preventDefault();
		var mb_name_target = $(this).closest('div[id^=modal]').find('input[name=mb_name_target]').val();
		var sit_idx = $(this).closest('div[id^=modal]').find('input[name=sit_idx]').val();
		var this_mb_id = $(this).attr('mb_id');
		var this_mb_name = $(this).attr('mb_name');
		var this_modal = $(this).closest('div[id^=modal]');

		// 실제 디비에 정보 수정 후 dom 내용 변경
		//-- 디버깅 Ajax --//
		$.ajax({
			url:g5_user_url+'/ajax/site.json.php',
			data:{"aj":"mbchg","mb_id":this_mb_id,"mb_target":mb_name_target,"sit_idx":sit_idx},
			dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
		//$.getJSON(g5_user_url+'/ajax/site.json.php',{"aj":"mbchg","mb_id":this_mb_id,"mb_target":mb_name_target,"sit_idx":sit_idx},function(res) {
			//alert(res.sql);
			console.log(res);
			var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
			
			if(res.result == true) {
				//alert(res.msg);
				// 해당 위치에 담당자 이름 변경
				$('#mb_name_'+mb_name_target+'_'+sit_idx).text( this_mb_name );

				// 모달 창 닫기
				this_modal.modal('hide');
			}
			else {
				alert(res.msg);
			}
			
			}, error:this_ajax_error	//<-- 디버깅 Ajax --//
		});
		
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


