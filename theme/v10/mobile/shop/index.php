<?php
include_once('./_common.php');
define("_INDEX_", TRUE);

// echo 'HTTP_HOST:'.$_SERVER['HTTP_HOST'].'<br>';
// echo 'QUERY_STRING:'.$_SERVER['QUERY_STRING'].'<br>';
// print_r2($_REQUEST);
// print_r2($_SERVER);
// print_r2($_SESSION);


include_once(G5_THEME_MSHOP_PATH.'/shop.head.php');

add_stylesheet('<link type="text/css" href="'.G5_JS_URL.'/swiper/swiper.min.css" rel="stylesheet" />', 0);
?>
<style>
</style>

<?php if($member['mb_id']) { ?>
<div class="div_login">
    <div><?=$member['mb_name']?>님, 안녕하세요. 아래 버튼을 클릭하세요.</div>
    <div>관리콘솔에서 운영중인 설비상태를 실시간으로 확인합니다.</div>
    <a href="<?=G5_USER_ADMIN_URL?>/"><div class="btn_admin" style="">관리콘솔 바로가기 <i class="fa fa-arrow-right"></i></div></a>
</div>
<?php } ?>


<div class="div_01">
    <div class="div_title">설비통합관리플랫폼 iCMMS에 오신 것을 환영합니다.</div>
    <div class="div_content">
        설비관리를 위해서 Data Getting(데이터 게더링)이 중요합니다.
        복잡한 공장 상황을 정확히 분석해서 데이터를 수집해야 합니다.
        수집된 빅데이터는 다양한 방법으로 실시간 조회 및 분석이 가능해야 합니다. 이를 위해서 iCMMS가 탄생했습니다.
    </div>
</div>
<br>
<div class="div_image">
    <img src="<?=G5_THEME_IMG_URL?>/mobile/main_01.png">
</div>
<br>

<div class="div_box">
    <div class="div_icon"><i class="fa fa-industry"></i></div>
    <div class="div_title">생산(UPH) 최적화</div>
    <div class="div_content">
        생산에 저해되는 요소를 분석하여 원인을 제거하여 설비 UPH(Unit Per Hour)를 향상시킵니다.
        최종적인 목적은 생산 매출을 올려 고객의 이익을 극대화하는 것입니다.
    </div>
</div>

<div class="div_box">
    <div class="div_icon"><i class="fa fa-bar-chart"></i></div>
    <div class="div_title">데이터 분석 최적화</div>
    <div class="div_content">
        설비 상태를 정확히 진단하려면 엄청난 데이터의 수집이 필요합니다.
        iCMMS는 전압, 장치부하, 진동, 열, 습도, 에러 발생주기, 가동주기, 생산주기 등의 설비 데이터를 실시간으로 수집합니다.
        수집된 빅데이터(Big Data) 분석을 통해 심각한 장애가 발생하기 전에 설비의 고장을 감지하여 예지, 예방 정보를 작업자 및 관리자에게 제공합니다.
        최고 경영자에게는 설비 통합 플랫폼의 핵심 지표(KPI)를 주기적으로 제공합니다.
    </div>
</div>

<div class="div_box">
    <div class="div_icon"><i class="fa fa-bomb"></i></div>
    <div class="div_title">이벤트 대응 최적화</div>
    <div class="div_content">
        설비 장애는 매출 감소의 가장 큰 원인입니다.
        설비 고장이 발행할 경우 양방향 통신 IoT인 iMP(ING Manager Pannel)를 통해 무선으로 원격 접속하여 
        고장 원인을 찾아 신속히 해결함으로서 설비 가동률을 높일 수 있습니다.
        iMP는 0.1sec 이하로 Data를 수집하고 빠르게 분석하여 장애를 신속히 처리하는 데 최적화되어 있습니다.
    </div>
</div>

<div class="div_box">
    <div class="div_icon"><i class="fa fa-database"></i></div>
    <div class="div_title">매카니즘(Mechanism) 분석 최적화</div>
    <div class="div_content">
        iCMMS의 빅데이터(Big Data)를 활용하여 설비의 메카니즘(Mechanism)을 계속 고도화시켜 나가야 합니다.
        이를 통해 설비 구조를 지속적으로 개선하여 설비의 수명 및 생산량(UPH)을 계속 향상시켜 나갈 수 있습니다.
    </div>
</div>

<div class="div_ingglobal">
    <div class="div_title">
        고객의 가치에 집중합니다.
        <br>
        고객의 더 낳은 가치를 위해 발전합니다.
        <br>
        ING GLOBAL (주)아이엔지글로벌
        <br>
    </div>
    <div class="div_home">홈페이지 바로가기</div>
    <div class="div_tel">TEL 054-742-0661</div>
</div>
<div class="div_ingglobal_bottom">
    <img src="<?=G5_THEME_IMG_URL?>/mobile/main_ingglobal_bottom_round.png">
</div>

<div class="div_footer">
    <div class="div_item">
        <span class="item_title">회사명</span>
        <span class="item_content">ING GLOBAL 아이엔지글로벌(주)</span>
    </div>
    <div class="div_item">
        <span class="item_title">대표</span>
        <span class="item_content">이병구</span>
    </div>
    <div class="div_item">
        <span class="item_title">주소</span>
        <span class="item_content">경상북도 경주시 외동읍 문산2산단4로19</span>
    </div>
    <div class="div_item">
        <span class="item_title">사업자등록번호</span>
        <span class="item_content">505-81-60327</span>
    </div>
    <div class="div_item">
        <span class="item_title">전화번호</span>
        <span class="item_content">054-742-0661</span>
    </div>
    <div class="div_item">
        <span class="item_title">Email</span>
        <span class="item_content">ing@ingglobal.net</span>
    </div>
    <div class="div_item">
        <span class="item_title">개인정보보호책임자</span>
        <span class="item_content">최은희</span>
    </div>
</div>




<script>
// 앱인 경우 실행
$(window).load(function(){
	document.location.href="icmms://update-push-key?id=<?=$member['mb_id']?>";
});

// idx-container 클래스 추가
$("#container").addClass("idx-container");

$(function(e){
    $(document).on('click','.div_home',function(e){
        window.open('http://www.ingglobal.net');
    });
});
</script>

<?php
include_once(G5_THEME_MSHOP_PATH.'/shop.tail.php');
?>