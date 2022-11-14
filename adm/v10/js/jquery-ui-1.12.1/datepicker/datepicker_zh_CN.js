$(function(){
	$('.from_date').datepicker({
         closeText:'关闭',
         prevText:'上月',
         nextText:'下个月',
         currentText:'今天',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['日','一','二','三','四','五','六'],
		dayNames:['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
         dayNamesShort:['日','一','二','三','四','五','六'],
		monthNames:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
		monthNamesShort:['一','二','三','四','五','六','七','八','九','十','十一','十二'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100',
		onClose:function(selectedDate){
			$('.to_date').datepicker('option','minDate',selectedDate);
		}
	});
	
	$('.to_date').datepicker({
		closeText:'关闭',
         prevText:'上月',
         nextText:'下个月',
         currentText:'今天',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['日','一','二','三','四','五','六'],
		dayNames:['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
         dayNamesShort:['日','一','二','三','四','五','六'],
		monthNames:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
		monthNamesShort:['一','二','三','四','五','六','七','八','九','十','十一','十二'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100',
		//maxDate: '+1D', //今天날짜에서 다음날까지 선택가능?('+1D','+2M','+1Y')
		//minDate: '-1D', //今天날짜에서 다음날까지 선택가능?('-1D','-2M','-1Y')?
		onClose:function(selectedDate){
			$('.from_date').datepicker('option','maxDate',selectedDate);
		}
	});
	
	$('.date').datepicker({
		closeText:'关闭',
         prevText:'上月',
         nextText:'下个月',
         currentText:'今天',
		dateFormat:"yy-mm-dd",
		dayNamesMin:['日','一','二','三','四','五','六'],
		dayNames:['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],
         dayNamesShort:['日','一','二','三','四','五','六'],
		monthNames:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],
		monthNamesShort:['一','二','三','四','五','六','七','八','九','十','十一','十二'],
		changeMonth: true,//달을 선택할 수 있게한다.
     	changeYear: true,//년을 선택할 수 있게 한다.
     	yearRange: '1910:2100'
	});
});