var bwg_timeArr = [
	['00:00','00:00','오전']
	,['00:30','00:30','오전']
	,['01:00','01:00','오전']
	,['01:30','01:30','오전']
	,['02:00','02:00','오전']
	,['02:30','02:30','오전']
	,['03:00','03:00','오전']
	,['03:30','03:30','오전']
	,['04:00','04:00','오전']
	,['04:30','04:30','오전']
	,['05:00','05:00','오전']
	,['05:30','05:30','오전']
	,['06:00','06:00','오전']
	,['06:30','06:30','오전']
	,['07:00','07:00','오전']
	,['07:30','07:30','오전']
	,['08:00','08:00','오전']
	,['08:30','08:30','오전']
	,['09:00','09:00','오전']
	,['09:30','09:30','오전']
	,['10:00','10:00','오전']
	,['10:30','10:30','오전']
	,['11:00','11:00','오전']
	,['11:30','11:30','오전']
	,['12:00','12:00','오후']
	,['12:30','12:30','오후']
	,['13:00','01:00','오후']
	,['13:30','01:30','오후']
	,['14:00','02:00','오후']
	,['14:30','02:30','오후']
	,['15:00','03:00','오후']
	,['15:30','03:30','오후']
	,['16:00','04:00','오후']
	,['16:30','04:30','오후']
	,['17:00','05:00','오후']
	,['17:30','05:30','오후']
	,['18:00','06:00','오후']
	,['18:30','06:30','오후']
	,['19:00','07:00','오후']
	,['19:30','07:30','오후']
	,['20:00','08:00','오후']
	,['20:30','08:30','오후']
	,['21:00','09:00','오후']
	,['21:30','09:30','오후']
	,['22:00','10:00','오후']
	,['22:30','10:30','오후']
	,['23:00','11:00','오후']
	,['23:30','11:30','오후']
];
/*
인수설명
1번 인수 : 타겟 select객체
2번 인수 : 24시간 타입인가? 12시간 타입인가?[기본 24]
3번 인수 : 시간범위에서 시작시간 (0~23) [기본 0]
4번 인수 : 시간범위에서 종료시간 (0~23) [기본 23]
*/
function timePicker(object,v_type,f_time,t_time){
	var obj = object;
	var val = obj.attr('val');
	var tp = 0;
	var apm = 2;
	var opts = '';
	var relArr = new Array();
	var indexFrom = 0;
	var indexTo = 0;
	var view_type = (v_type == 12) ? v_type : 24;
	var from_time = (f_time > 0) ? f_time : 0;
	var to_time = (t_time < 23) ? t_time : 23;
	
	if(from_time > to_time){
		alert('시작시간이 종료시간보다 클수는 없습니다.');
		return false;
	}
	
	if(view_type == '12'){
		tp = 1;
	}else{
		tp = 0;
	}
	
	indexFrom = fromTimeIndex(from_time);
	indexTo = toTimeIndex(to_time);
	for(var j = indexFrom; j<=indexTo; j++){
		//relArr.push(bwg_timeArr[j]);
		//console.log(bwg_timeArr[j]);
		opts += "<option value='"+bwg_timeArr[j][0]+"'"+((val == bwg_timeArr[j][0])?' selected="selected"':'')+">"+bwg_timeArr[j][tp]+((tp) ? "("+bwg_timeArr[j][2]+")" : "")+"</option>";
	}
	
	obj.html(opts).addClass('bwg_time');
}

function fromTimeIndex(f){
	var idx = 0;
	for(var i in bwg_timeArr){
		if(Number(bwg_timeArr[i][0].substr(0,2)) == f){
			idx = i;
			break;
		}
	}
	
	return idx;
}

function toTimeIndex(t){
	var idx = bwg_timeArr.length - 1;
	for(var i=idx; i>=0; i--){
		if(Number(bwg_timeArr[i][0].substr(0,2)) == t){
			idx = i;
			break;
		}
	}
	
	return idx;
}