<?php
// 모바일용입니다. 피씨는 없습니다.
$sub_menu = "955610";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = 'API 연동 가이드';
include_once('./_head.php');
?>
<style>
.div_session .btn_token {
    font-size: 1.1em;
    font-weight: 500;
    margin: 0 auto;
    margin-top: 15px;
    padding: 10px 15px;
    width: 95%;
    border: solid 1px black;
    border-radius: 5px;
    text-align:center;
    cursor:pointer;
    color:white;
    background:#611f69;
}
.div_session .text_token {
    font-size: 1.1em;
    font-weight: 500;
    margin: 0 auto;
    margin-top: 15px;
    padding: 10px 15px;
    width: 95%;
    border: solid 1px black;
    border-radius: 5px;
    text-align:center;
}
</style>

<div class="div_session">
    <div class="div_title_h1">토큰발급</div>
    <div class="div_icon"><i class="fa  fa-check-square-o"></i></div>
    <div class="div_content">
        API를 사용하기 위해서는 토큰(Token)을 발급받아야 합니다. 
        <br>
        토큰은 6개월동안만 유효합니다. (보안 및 트래픽 이슈)
        <br>
        계속 사용하시려면 6개월 단위로 재발급 받으시면 됩니다.
    </div>
    <div class="div_content">
        <div class="btn_token">토큰 발급 신청</div>
        <div class="text_token"><?=($member['mb_api_token'])?$member['mb_api_token']:'Token not exists.'?></div>
    </div>
</div>

<div class="div_session">
    <div class="div_title_h1">생산데이터 입력</div>
    <div class="div_title_h2">
        설비의 생산 정보를 API를 통해서 입력합니다.
        <br>
        전달값은 JSON BODY로 만들어서 POST로 입력하세요.
    </div>
    <div class="div_icon"><i class="fa fa-bar-chart"></i></div>
    <div class="div_content">
        <b>접속 URL</b> http://bogwang.epcs.co.kr/device/output/
        <ul>
        <li><b>token</b> Token값 (부여받은 토큰값)</li>
        <li><b>list</b> 생산데이터 배열 (여러개 입력가능, 배열 항목은 아래 참조)</li>
        <li><b>list[0][com_idx]</b> 7</li>
        <li><b>list[0][imp_idx]</b> 13</li>
        <li><b>list[0][mms_idx]</b> 설비의 mms_idx (설비관리에서 번호 확인하세요.)</li>
        <li><b>list[0][dta_group]</b> product</li>
        <li><b>list[0][dta_date]</b> <?=date("y.m.d")?> (YY.mm.dd와 같은 형식입니다.)</li>
        <li><b>list[0][dta_time]</b> <?=date("H:i:s")?> (24시간제로 입력)</li>
        <li><b>list[0][dta_value]</b> 생산량 (숫자로 입력)</li>
        <li><b>list[0][dta_date2]</b> <?=date("y.m.d")?> (통계 적용일, 다음날로 넘어가는 경우만 활용)</li>
        </ul>
    </div>
    <br>
    <div class="div_title_h2">
        JSON BODY 예제 (JSON object)
    </div>
    <div class="div_content_code">
        {"token":"1099de5drf09","list":[{"com_idx":"32","imp_idx":"6","mms_idx":"3","dta_shf_no":"2","dta_shf_max":"3","dta_mmi_no":"9","dta_group":"product","dta_defect":"0","dta_defect_type":"1","dta_date":"21.02.15","dta_time":"03:47:06","dta_value":"3","dta_message":"\uce58\uc218\ubd88\ub7c9","dta_date2":"21.02.14"},{"com_idx":"59","imp_idx":"10","mms_idx":"1","dta_shf_no":"3","dta_shf_max":"3","dta_mmi_no":"1","dta_group":"product","dta_defect":"0","dta_defect_type":"3","dta_date":"21.02.14","dta_time":"17:02:05","dta_value":"1","dta_message":"\uce58\uc218\ubd88\ub7c9","dta_date2":"21.02.13"},{"com_idx":"28","imp_idx":"16","mms_idx":"1","dta_shf_no":"1","dta_shf_max":"2","dta_mmi_no":"5","dta_group":"product","dta_defect":"1","dta_defect_type":"1","dta_date":"21.02.13","dta_time":"10:58:52","dta_value":"1","dta_message":"\ud615\uc0c1\ubd88\ub7c9\u001d","dta_date2":"21.02.12"}]}
    </div>
</div>

<div class="div_session">
    <div class="div_title_h1">측정데이터 입력</div>
    <div class="div_title_h2">
        설비 측정 정보를 API를 통해서 입력합니다.
    </div>
    <div class="div_icon"><i class="fa fa-line-chart"></i></div>
    <div class="div_content">
        <b>접속 URL</b> http://bogwang.epcs.co.kr/device/measure/
        <ul>
        <li><b>token</b> Token값 (부여받은 토큰값)</li>
        <li><b>list</b> 측정데이터 배열 (여러개 입력가능)</li>
        <li><b>list[0][com_idx]</b> 7</li>
        <li><b>list[0][imp_idx]</b> 13</li>
        <li><b>list[0][mms_idx]</b> 설비의 mms_idx (설비관리에서 번호 확인하세요.)</li>
        <li><b>list[0][dta_group]</b> mea</li>
        <li><b>list[0][dta_type]</b> 측정값 종류 (1.온도, 2.토크, 3.전류, 4.전압, 5.진동, 6.소리, 7.습도, 8.압력, 9.속도)</li>
        <li><b>list[0][dta_no]</b> 측정번호 (1~10, 측정값을 구분하기 위해서 번호 사용)</li>
        <li><b>list[0][dta_date]</b> <?=date("y.m.d")?> (YY.mm.dd와 같은 형식입니다.)</li>
        <li><b>list[0][dta_time]</b> <?=date("H:i:s")?> (24시간제로 입력)</li>
        <li><b>list[0][dta_value]</b> 측정값 (정수, 소수 가능)</li>
        </ul>
    </div>
    <br>
    <div class="div_title_h2">
        JSON BODY 예제 (JSON object)
    </div>
    <div class="div_content_code">
    {"token":"1099de5drf09","list":[{"com_idx":"45","imp_idx":"12","mms_idx":"2","dta_shf_no":"1","dta_shf_max":"3","dta_mmi_no":"1","dta_group":"mea","dta_type":"7","dta_no":"2","dta_date":"21.02.15","dta_time":"06:20:15","dta_value":"50"},{"com_idx":"37","imp_idx":"10","mms_idx":"1","dta_shf_no":"2","dta_shf_max":"3","dta_mmi_no":"1","dta_group":"mea","dta_type":"6","dta_no":"1","dta_date":"21.02.14","dta_time":"11:58:19","dta_value":"20"},{"com_idx":"31","imp_idx":"4","mms_idx":"1","dta_shf_no":"2","dta_shf_max":"3","dta_mmi_no":"1","dta_group":"mea","dta_type":"9","dta_no":"2","dta_date":"21.02.13","dta_time":"02:51:14","dta_value":"12"}]}
    </div>
</div>

<div class="div_session" style="display:none;">
    <div class="div_title_h1">통합</div>
    <div class="div_title_h2">소주제</div>
    <div class="div_icon"><i class="fa fa-edit"></i></div>
    <div class="div_content">문서를 드래그 앤 드롭하는 작업부터 Google 드라이브와 같은 서비스에서 빠르게 추가하는 작업에 이르기까지, 파일 공유는 메시지를 입력하고 전송하는 것만큼이나 Slack에서 많은 부분을 차지하고 있습니다.</div>
    <div class="div_link"><a href="javascript:">Slack에 파일을 추가하는 방법</a></div>
</div>

<script>
$(document).on('click','.btn_token',function(e){
    if(confirm('토큰을 새로 발급하시겠습니까?')) {
        self.location.href = "./api_token_update.php";
    }
    return false;
})
</script>

<?php
include_once ('./_tail.php');
?>
