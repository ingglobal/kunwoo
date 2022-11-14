$(function(e) {
    // 초기 default 달력 호출
    get_calendar( $('.prev_month') );
    
	// 이전달 다음달 이동
	$(document).on('click','.prev_month, .next_month',function(e){
		e.preventDefault();
		var this_month = $(this).closest('div.calendar_title').find('.this_month');
		this_month.text( get_month( this_month, $(this).attr('cal_val') ) ); // 현재달 Display 표시
		get_calendar( $(this) );
	});

});


// 달력 호출
if(typeof(get_calendar)!='function') {
function get_calendar(obj) {

	// 해당 DOM 의 현재달 추출
	var fn_month = obj.closest('.calendar_title').find('.this_month').text();
	fn_month = fn_month.replace(/-/g,"");	// - 기호 제거
	
	//-- 리스트 호출 (HTML) --//
	$.ajax({
		url:g5_user_admin_ajax_url+'/ajax.calendar.php', type:'get', data:{"month":fn_month,"bo_table":g5_bo_table,"bo_config":g5_board_config}, dataType:'html', timeout:10000,  beforeSend:function(){},
		success:function(res){
			//console.log(res);
			obj.closest('.calendar').find('.table_calendar tbody').empty().append(res);
		},
		error:function(xmlRequest){
			alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText
			+ ' \n\rresponseText: ' + xmlRequest.responseText);
			$('#bugReport').html(xmlRequest.responseText);
		}
	});	
}
}


// 월 구하는 함수(현재달 - get_month(0), 다음달 - get_month(1), 이전달 - get_month(-1) )
if(typeof(get_month)!='function') {
function get_month(obj, calVal) {
	if(typeof calVal != 'number')
		calVal = parseInt(calVal);
	stDt = obj.text()+'-01';	// 기준이 되는 월의 1일 추출
	var d = new Date( stDt.substr(0,4), parseInt(remove_zeros(stDt.substr(5,2)))-1, parseInt(remove_zeros(stDt.substr(8,2))) );
	d.setMonth(d.getMonth() + calVal);
	var s = leading_zeros(d.getFullYear(), 4) + '-' + leading_zeros(d.getMonth() + 1, 2);	// month(월)인 경우는 +1을 해야 정상적인 숫자가 나옴
	return s;
}
}
if(typeof(leading_zeros)!='function') {
function leading_zeros(n, digits) {
	var zero = '';
	n = n.toString();
	if (n.length < digits) {
		for (i = 0; i < digits - n.length; i++)
			zero += '0';
	}
	return zero + n;
}
}
if(typeof(remove_zeros)!='function') {
function remove_zeros(n) {
	return n.replace(/^0+/, '');
}
}


//금액 콤마찍기 천자리 쉼표 찍기
if(typeof(thousand_comma)!='function') {
function thousand_comma(n) {
    var reg = /(^[+-]?\d+)(\d{3})/;
    n +='';
    while(reg.test(n))
    n = n.replace(reg, '$1' + ',' + '$2');
    
    return n;
}
}
