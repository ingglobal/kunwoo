<?php
include_once('./_common.php');
// 인스타 로그인후 인스타쪽 피드 배열을 받아서 올스타에 업데이트하는 폼을 임시로 구현한 페이지입니다.
// 실제로는 member_log.php가 크롤링 서버에서 정보를 받아서 저장합니다.
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..

include(G5_PATH.'/head.sub.php');

$arr['token'] = $_REQUEST['token'];
if(is_array($_REQUEST)) {
    foreach($_REQUEST as $k1 => $v1) {
        if($k1=='token')
            continue;
//        echo $k1.$v1.'<br>';
//        $arr[$k1][$i] = $v1;
//        print_r2($v1);

        foreach($v1 as $k2 => $v2) {
            $arr['list'][$k2][$k1] = $v2;
//            echo $k1.'/'.$k2.'/'.$v2.'<br>';
        }
        $i++;
    }
}
//print_r2($arr);
//exit;
?>
<style>
    #hd_login_msg {display:none;}
    table tr td {border:solid 1px #ddd;padding:10px;}
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
<button type="submit" id="btn_submit">확인</button>
</form>


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
                alert('데이터 입력 성공, 입력값은 관리자단에서 확인해 주세요.\n(요소검사: 결과값을 확인하세요.)');
            }
            console.log(res);
        },
        error:function(xmlRequest) {
            alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
            + ' \n\rresponseText: ' + xmlRequest.responseText);
        }
    });
});
</script>


<?php
include(G5_PATH.'/tail.sub.php');
?>