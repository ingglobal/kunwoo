//-- 번역 문장 -> 리턴
// 활용 alert(_t('적용할 항목이 존재하지 않습니다.'));
if(typeof(byte_check)!='_t') {
function _t(string, domain) {
	if(domain == undefined) domain = "default";
	sp_string = "singular";
	try {
		string = (eval('lang_'+domain+'[string][sp_string]'))? eval('lang_'+domain+'[string][sp_string]'):string;
	} catch (e) {}
	return string;
}
}

//-- 번역 문장 -> 출력(echo)
if(typeof(byte_check)!='__t') {
function __t(string, domain) {
	if(domain == undefined) domain = "default";
	sp_string = "singular";
	try {
		string = (eval('lang_'+domain+'[string][sp_string]'))? eval('lang_'+domain+'[string][sp_string]'):string;
	} catch (e) {}
	alert(string);
}
}

//-- 복수 처리 번역 문장 -> 리턴
//-- 활용 alert(sprintf('%2$s %3$s a %1$s', 'cracker', 'Polly', 'wants'));
//alert( sprintf(_p('%d 개월','%d 개월들',3),3) );
//alert( _p('%d 개월','%d 개월들',3) );
if(typeof(byte_check)!='_p') {
function _p(string1, string2, number, domain) {
	var number = parseInt(number);
	if(domain == undefined) domain = "default";
	sp_string = (number > 1)? "plural":"singular";
	string = (number > 1)? string2:string1;
	try {
		if(eval('lang_'+domain+'[string][sp_string]'))
			string = (eval('lang_'+domain+'[string][sp_string]'))? eval('lang_'+domain+'[string][sp_string]'):string;
		else
			string = (number > 1)? string2:string1;
	} catch (e) {}
	return string;
}
}


// SMS 80바이트 체크
if(typeof(byte_check)!='function') {
function byte_check(el_cont, el_byte) {
    var cont = document.getElementById(el_cont);
    var bytes = document.getElementById(el_byte);
    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = '';

    for (i=0; i<cont.value.length; i++) {
        ch = cont.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

    //byte.value = cnt + ' / 80 bytes';
    bytes.innerHTML = cnt + ' / 80 bytes';

    if (cnt > 80) {
        exceed = cnt - 80;
        alert('메시지 내용은 80바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = cont.value;
        for (i=0; i<tmp.length; i++) {
            ch = tmp.charAt(i);
            if (escape(ch).length > 4) {
                tcnt += 2;
            } else {
                tcnt += 1;
            }

            if (tcnt > 80) {
                tmp = tmp.substring(0,i);
                break;
            } else {
                xcnt = tcnt;
            }
        }
        cont.value = tmp;
        //byte.value = xcnt + ' / 80 bytes';
        bytes.innerHTML = xcnt + ' / 80 bytes';
        return;
    }
}
}


//금액 콤마찍기 천자리 쉼표 찍기
if(typeof(thousand_comma)!='function') {
function thousand_comma(n) {
	var g5_decimals = 0;
	var g5_thousands_sep = ',';
	var g5_dec_point = ".";
	n = Number(n).toFixed(g5_decimals); //소수점 표현 추가, 반올림 처리
	nArray = n.split('.');	// 정수, 소수 분리 처리, nArray[0]->정수부, nArray[1]->소수부

	var reg = /(^[+-]?\d+)(\d{3})/;
	nArray[0] +='';
	while(reg.test(nArray[0]))
	nArray[0] = nArray[0].replace(reg, '$1' + g5_thousands_sep + '$2');
	
	n = (nArray[1] == undefined) ? nArray[0] : nArray[0] + g5_dec_point + nArray[1]; 
	return n;
}
}

// 날짜 관련 앞쪽을 0 으로 채우기
if(typeof(leadingZeros)!='function') {
function leadingZeros(n, digits) {
	var zero = '';
	n = n.toString();
	if (n.length < digits) {
		for (i = 0; i < digits - n.length; i++)
			zero += '0';
	}
	return zero + n;
}
}
if(typeof(removeZeros)!='function') {
function removeZeros(n) {
	return n.replace(/^0+/, '');
}
}


// 모달 리스트 정리 함수
if(typeof(ajtable_nothing_display)!='function') {
function ajtable_nothing_display(tdom) {
	// IE8 같은 느린 브라우저에서 에러가 있어서 0.1초 시간 차이를 둡니다.
	setTimeout(function() {
		if(tdom.find("tr").not('.tr_nothing, .tr_loading').length == 0) {
			tdom.parents('div.modal_body:first').find('span.count_total').text(0);
			tdom.find('.tr_nothing').show();
			tdom.closest('table').next('nav').find('ul.pg').hide();
		}
		else {
			tdom.find('.tr_nothing').hide();
			tdom.closest('table').next('nav').find('ul.pg').show();
		} 
	},100);
}
}


// jQuery 디버깅을 위한 공통 함수
if(typeof(this_ajax_error)!='function') {
function this_ajax_error(xmlRequest) {
	alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText
	+ ' \n\rresponseText: ' + xmlRequest.responseText);
	$('#bugReport').html(xmlRequest.responseText);
}}

// 문자열 자르기
if(typeof(cut_str)!='function') {
function cut_str(str, len, suffix) {
    suffix = suffix || '...';  
    var str_len = str.length;
    var str_suffix = (str_len > len) ? suffix : '';
    if (str_len >= len) {
        str = str.substring(0,len) + str_suffix;
    }
    return str;
}
}
    
//url에서 파라메타만 JSON에 담아 보내기
if(typeof(urlParaToJSON2)!='function') {
function urlParaToJSON2(furl) {
    url = furl || self.location.toString();
    var arr=[];
    var rtnArr={};
    var sharp = (url.indexOf('#')>-1) ? url.indexOf('#') : url.length;
    arr = url.substring(url.indexOf('?')+1,sharp).split('&');
    for(i=0;i<arr.length;i++){
        var arr2=[];
        arr2 = arr[i].split('=');
        rtnArr[arr2[0]] = arr2[1];
    }
    return rtnArr;
}
}

//url에서 파일명 구하기
if(typeof(urlParaToJSON)!='function') {
function urlParaToJSON(furl) {
    url = furl || self.location.toString();
    var tmp= url.substring(url.lastIndexOf('/')+1,url.lastIndexOf('.php'));
    return tmp;
}    
}

//url에서 파일명 구하기
if(typeof(getGraphId)!='function') {
function getGraphId(dta_json_file,dta_group,mms_idx,dta_type,dta_no,shf_no,dta_mmi_no,dta_defect,dta_defect_type,dta_code) {
    var graph_id1 = dta_json_file+'_'+dta_group+'_'+mms_idx+'_'+dta_type
                    +'_'+dta_no+'_'+shf_no+'_'+dta_mmi_no
                    +'_'+dta_defect+'_'+dta_defect_type
                    +'_'+dta_code;
    var graph_id2 = btoa(graph_id1).replace(/=/g,''); // encoded
    // console.log('f encoded > '+graph_id2);
    // graph_id3 = atob(graph_id2); // decode
    // console.log('f decoded > '+graph_id3);
    return graph_id2;
}    
}

//url에서 파일명 구하기
if(typeof(getGraphURL)!='function') {
function getGraphURL(url) {
    var url2 = btoa(url).replace(/=/g,''); // encoded
    // console.log('f encoded > '+url2);
    // url3 = atob(url2); // decode
    // console.log('f decoded > '+url3);
    return url2;
}    
}

