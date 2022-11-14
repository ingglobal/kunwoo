$(function(){
	$('.from_date').datepicker({
         closeText:'Close',
         prevText:'Last Month',
         nextText:'Next Month',
         currentText:'Today',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['Sun','Mon','Tues','Wed','Thurs','Fri','Sat'],
		dayNames:['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
         dayNamesShort:['Sun','Mon','Tues','Wed','Thurs','Fri','Sat'],
		monthNames:['January','February','March','April','May','June','July','August','September','October','November','December'],
		monthNamesShort:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100',
		onClose:function(selectedDate){
			$('.to_date').datepicker('option','minDate',selectedDate);
		}
	});
	
	$('.to_date').datepicker({
		closeText:'Close',
         prevText:'Last Month',
         nextText:'Next Month',
         currentText:'Today',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['Sun','Mon','Tues','Wed','Thurs','Fri','Sat'],
		dayNames:['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
         dayNamesShort:['Sun','Mon','Tues','Wed','Thurs','Fri','Sat'],
		monthNames:['January','February','March','April','May','June','July','August','September','October','November','December'],
		monthNamesShort:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100',
		//maxDate: '+1D', //Today날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		//minDate: '-1D', //Today날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
		onClose:function(selectedDate){
			$('.from_date').datepicker('option','maxDate',selectedDate);
		}
	});
	
	$('.date').datepicker({
		closeText:'Close',
         prevText:'Last Month',
         nextText:'Next Month',
         currentText:'Today',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['Sun','Mon','Tues','Wed','Thurs','Fri','Sat'],
		dayNames:['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
         dayNamesShort:['Sun','Mon','Tues','Wed','Thurs','Fri','Sat'],
		monthNames:['January','February','March','April','May','June','July','August','September','October','November','December'],
		monthNamesShort:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sept','Oct','Nov','Dec'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100'
	});
});