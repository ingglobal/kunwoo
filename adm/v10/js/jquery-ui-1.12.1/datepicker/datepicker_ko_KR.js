$(function(){
	$('.from_date').datepicker({
         closeText:'닫기',
         prevText:'이전달',
         nextText:'다음달',
         currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
         dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100',
		onClose:function(selectedDate){
			$('#to_date').datepicker('option','minDate',selectedDate);
		}
	});
	
	$('.to_date').datepicker({
		closeText:'닫기',
         prevText:'이전달',
         nextText:'다음달',
         currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
         dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '2017:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		minDate: '-140D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
		onClose:function(selectedDate){
			$('#from_date').datepicker('option','maxDate',selectedDate);
		}
	});
	
	$('.date').datepicker({
		closeText:'닫기',
         prevText:'이전달',
         nextText:'다음달',
         currentText:'오늘',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['일','월','화','수','목','금','토'],
		dayNames:['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
         dayNamesShort:['일','월','화','수','목','금','토'],
		monthNames:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		monthNamesShort:['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '2017:2100',
		//maxDate: '+1D', //오늘날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		minDate: '-140D', //오늘날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
		onClose:function(selectedDate){
			$('#from_date').datepicker('option','maxDate',selectedDate);
		}
	});
});