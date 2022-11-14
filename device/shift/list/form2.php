<?php
include_once('./_common.php');
// 인스타 로그인후 인스타쪽 피드 배열을 받아서 올스타에 업데이트하는 폼을 임시로 구현한 페이지입니다.
// 실제로는 member_log.php가 크롤링 서버에서 정보를 받아서 저장합니다.
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..

include(G5_PATH.'/head.sub.php');

$arr['token'] = $_REQUEST['token'];
$arr['mms_idx'] = $_REQUEST['mms_idx'];
//print_r2($arr);
//exit;
?>
<style>
    #hd_login_msg {display:none;}
    table tr td {border:solid 1px #ddd;padding:10px;}
    button {background:#ff8b37;padding:10px 20px;font-size:1.5em;border-radius:4px;}
</style>

<form id="form02" action="./index.php">

<table>
	<tr><td style="background:#aaa;">JSON BODY (실제로 넘어가는 JSON object)</td></tr>
	<tr>
        <td>
            <?=json_encode($arr);?>
        </td>
    </tr>
	<tr><td style="background:#aaa;">배열값으로 보면 이렇습니다.</td></tr>
	<tr>
        <td style="background:#f3f3f3;">
            <?=print_r2($arr);?>
        </td>
    </tr>
</table>
    
<hr>
<button type="submit" id="btn_submit">결과확인</button>
</form>
<div id="result" style="margin-top:20px;">
</div>

<script>
$(document).on('click','#btn_submit',function(e) {
    e.preventDefault();
    $.ajax({
        url:'./index.php',
        type:'post',
        data : "<?=addslashes(json_encode($arr));?>",
        dataType:'json',
        timeout:10000, 
        beforeSend:function(){},
        success:function(res){
//            var items;
//            for(items in res) { alert(items +': '+ res[items]); }
            if(res.meta.code>200) {
                alert(res.meta.message);
            }
            else {
                alert('교대 및 목표수량 정보 추출 성공, 하단에 표시됩니다.\n(요소검사쪽에서 결과를 확인하실 수도 있습니다.)');
//                var arr = json2array(res.meta);
//                var arr = JSONtoString(res.meta);
//                $('#result').text(arr);
                $('#result').text(JSON.stringify(res.meta));
                //var arr = jQuery.parseJSON(res.meta);
                // console.log(arr);
            }
            console.log(res);
        },
        error:function(xmlRequest) {
            alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
            + ' \n\rresponseText: ' + xmlRequest.responseText);
        }
    });
});
    
function json2array(json){
    var result = [];
    var keys = Object.keys(json);
    keys.forEach(function(key){
        result.push(key+"="+json[key]);
    });
    return result;
}
function JSONtoString(object) {
    var results = [];
    for (var property in object) {
        var value = object[property];
        if (value)
            results.push(property.toString() + ': ' + value);
        }
                
        return '{' + results.join(', ') + '}';
}
</script>


<?php
include(G5_PATH.'/tail.sub.php');
?>