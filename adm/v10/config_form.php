<?php
$sub_menu = "990100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

if(!$config['cf_faq_skin']) $config['cf_faq_skin'] = "basic";
if(!$config['cf_mobile_faq_skin']) $config['cf_mobile_faq_skin'] = "basic";

$g5['title'] = '솔루션설정';
include_once('./_top_menu_setting.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">기본설정</a></li>
    <li><a href="#anc_cf_message">메시지설정</a></li>
    <li><a href="#anc_cf_secure">관리설정</a></li>
</ul>';

if (!$config['cf_icode_server_ip'])   $config['cf_icode_server_ip'] = '211.172.232.124';
if (!$config['cf_icode_server_port']) $config['cf_icode_server_port'] = '7295';

if ($config['cf_sms_use'] && $config['cf_icode_id'] && $config['cf_icode_pw']) {
    $userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);
}
?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="" id="token">

<section id="anc_cf_default">
	<h2 class="h2_frm">기본설정</h2>
	<?php echo $pg_anchor ?>

	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>기본설정</caption>
		<colgroup>
			<col class="grid_4">
			<col>
			<col class="grid_4">
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th scope="row">스마트공장<br>사후관리로그 API Key값</th>
			<td colspan="3">
				<?php echo help('ex) $5$API$3Ue8EDeh9uh5SBT98qSOmiuz636aFqZyV5aMgFmL4rD') ?>
				<input type="text" name="set_userlog_crtfckey" value="<?php echo $g5['setting']['set_userlog_crtfckey'] ?>" id="set_userlog_crtfckey" class="frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">API통신토큰</th>
			<td colspan="3">
				<?php echo help('예)1099de5drf09') ?>
				<input type="text" name="set_api_token" value="<?php echo $g5['setting']['set_api_token'] ?>" id="set_api_token" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">디폴트상태값</th>
			<td colspan="3">
				<?php echo help('pending=대기,auto-draft=자동저장,ok=정상,hide=숨김,trash=삭제') ?>
				<input type="text" name="set_status" value="<?php echo $g5['setting']['set_status'] ?>" id="set_status" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">디폴트 예/아니오</th>
			<td colspan="3">
				<?php echo help('1=예,0=아니오') ?>
				<input type="text" name="set_yesno" value="<?php echo $g5['setting']['set_yesno'] ?>" id="set_yesno" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">디폴트 아니오/예</th>
			<td colspan="3">
				<?php echo help('0=아니오,1=예') ?>
				<input type="text" name="set_noyes" value="<?php echo $g5['setting']['set_noyes'] ?>" id="set_noyes" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">디폴트 기간타입</th>
			<td colspan="3">
				<?php echo help('0=특정기간,1=공통(전체기간)') ?>
				<input type="text" name="set_period_type" value="<?php echo $g5['setting']['set_period_type'] ?>" id="set_period_type" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">디폴트 출하예정까지 날수<br>(수주일로부터 몇일?)</th>
			<td colspan="3">
				<?php echo help('ex) 5') ?>
				<input type="text" name="set_oro_date_plan_cnt" value="<?php echo $g5['setting']['set_oro_date_plan_cnt'] ?>" id="set_oro_date_plan_cnt" required class="required frm_input" style="width:60px;">
			</td>
		</tr>
		<tr>
			<th scope="row">디폴트 생산종료일까지 날수<br>(생산시작일로부터 몇일?)</th>
			<td colspan="3">
				<?php echo help('ex) 5') ?>
				<input type="text" name="set_orp_done_date_cnt" value="<?php echo $g5['setting']['set_orp_done_date_cnt'] ?>" id="set_orp_done_date_cnt" required class="required frm_input" style="width:60px;">
			</td>
		</tr>
		<tr>
			<th scope="row">모니터별업로드이미지개수</th>
			<td colspan="3">
				<?php echo help('예) 3 : 최대 3장 업로드 가능') ?>
				<input type="text" name="set_monitor_cnt" value="<?php echo $g5['setting']['set_monitor_cnt'] ?>" id="set_monitor_cnt" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">모니터이미지로테이션시간</th>
			<td colspan="3">
				<?php echo help('예) 3000 : 3초') ?>
				<input type="text" name="set_monitor_time" value="<?php echo $g5['setting']['set_monitor_time'] ?>" id="set_monitor_time" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">모니터페이지리로딩간격시간</th>
			<td colspan="3">
				<?php echo help('예) 10000 : 10초') ?>
				<input type="text" name="set_monitor_reload" value="<?php echo $g5['setting']['set_monitor_reload'] ?>" id="set_monitor_reload" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">분류(카테고리) terms</th>
			<td colspan="3">
				<?php echo help('') ?>
				<input type="text" name="set_taxonomies" value="<?php echo $g5['setting']['set_taxonomies'] ?>" id="set_taxonomies" required class="required frm_input" style="width:80%;">
			</td>
		</tr>
		<tr>
			<th scope="row">회원레벨명 mb_level</th>
			<td colspan="3">
				<input type="text" name="set_mb_levels" value="<?php echo $g5['setting']['set_mb_levels'] ?>" id="set_mb_levels" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">직책(권한) mb_1</th>
			<td colspan="3">
				<?php echo help('1=지원팀,4=팀원,6=팀장,8=센터장,10=부서장,20=운영관리') ?>
				<input type="text" name="set_mb_positions" value="<?php echo $g5['setting']['set_mb_positions'] ?>" id="set_mb_positions" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">직급(직위) mb_3</th>
			<td colspan="3">
				<?php echo help('2=파트타임............50=팀장,60=과장,70=차장,80=부장,90=센터장,100=본부장,110=실장,120=이사,130=부사장,140=대표') ?>
				<input type="text" name="set_mb_ranks" value="<?php echo $g5['setting']['set_mb_ranks'] ?>" id="set_mb_ranks" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">부서 mb_7</th>
			<td colspan="3">
				<?php echo help('1=구매원가,2=생산관리,3=품질개발') ?>
				<input type="text" name="set_mb_parts" value="<?php echo $g5['setting']['set_mb_parts'] ?>" id="set_mb_parts" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">업체분류</th>
			<td colspan="3">
				<?php echo help('electricity=전기,electronic=전자,facility=설비,food=식품,parts=자재') ?>
				<input type="text" name="set_com_type" value="<?php echo $g5['setting']['set_com_type'] ?>" id="set_com_type" required class="required frm_input" style="width:90%;">
			</td>
		</tr>
		<tr>
			<th scope="row">업체상태값 설정</th>
			<td colspan="3">
				<?php echo help('ok=정상,pending=대기,trash=휴지통,delete=삭제,hide=숨김,prohibit=영업금지업체') ?>
				<input type="text" name="set_com_status" value="<?php echo $g5['setting']['set_com_status']; ?>" class="frm_input" style="width:60%;">
			</td>
		</tr>
        <tr>
            <th scope="row">업체-영업자 상태값 설정</th>
            <td colspan="3">
                <input type="text" name="set_cms_status" value="<?php echo $g5['setting']['set_cms_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
        <tr>
            <th scope="row">업체-회원 상태 설정</th>
            <td colspan="3">
                <input type="text" name="set_cmm_status" value="<?php echo $g5['setting']['set_cmm_status']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
			<th scope="row">정산 업데이트 기준일</th>
			<td colspan="3">
				<?php echo help('디폴트(한달): -1 MONTH (설정 기간 이후의 매출정산을 업데이트한다.) -1 DAY, -1 WEEK, -2 MONTH 등등'); ?>
				<input type="text" name="set_sales_update_interval" value="<?php echo $g5['setting']['set_sales_update_interval']; ?>" class="frm_input" style="width:80%;">
			</td>
		</tr>
        <tr>
            <th scope="row">IMP 묶음단위</th>
            <td>
                <input type="text" name="set_imp_count" value="<?php echo $g5['setting']['set_imp_count']; ?>" class="frm_input" style="width:30px;"> 개
            </td>
        </tr>
        <tr>
            <th scope="row">데이타 타입</th>
            <td>
				<?php echo help('1. 정.온도(도), temperature: 범위 -20~1500 / 5초
2. 비.토크(%), torque: -300~300 / 1초~30
3. 비.전류(A), current: 0~1000 / 1초~30
4. 비.전압(V), voltage: 0~1000 / 1초~30
5. 비.진동(Hz), vibration: 20~2000
6. 비.소리(dB), sound: 0~150
7. 정.습도(%), humidity: 0~100
8. 비.압력(psi), pressure: 0~100
9. 비.속도(r/min), rpm: 0~3000
...
...
태그값은 계속 추가될 수 있습니다. 100만개~'); ?>
                <input type="text" name="set_data_type" value="<?php echo $g5['setting']['set_data_type']; ?>" class="frm_input" style="width:80%;">
            </td>
        </tr>
		<tr>
			<th scope="row">데이타 그룹 설정</th>
			<td colspan="3">
				<?php echo help('err=에러,pre=예지,run=가동시간,product=생산,mea=측정...(err+pre=에러테이블, run+product=가동테이블, mea=측정테이블)'); ?>
				<input type="text" name="set_data_group" value="<?php echo $g5['setting']['set_data_group']; ?>" class="frm_input" style="width:50%;">
			</td>
		</tr>
		<tr>
			<th scope="row">데이타 그래프 값</th>
			<td colspan="3">
				<?php echo help('각 데이터 그룹별로 그래프 초기값을 설정하세요. 3개값을 쉽표로 구분하여 입력하세요.
형식: <span class="color_red">검색항목, 단위값, 갯수, 값타입</span> 형식으로 입력합니다. 아래 예제를 참고하세요.
minute,5,600 = 분단위,5분단위,5분*600개표시=50시간,avg(평균)
second,10,600 = 초단위,10초단위,10초*600개=100분,sum(합계)
monthly,1,12 = 월별,1개월단위,12개월치,sum(합계)
daily,1,30 = 일별,1일단위,30일치,sum(합계)
yearly,1,10 = 연도별,1년단위,10년치,sum(합계)'); ?>
                <?php
                $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_data_group']));
                foreach ($set_values as $set_value) {
                    list($key, $value) = explode('=', trim($set_value));
                    echo ' <input type="text" name="set_graph_'.$key.'" value="'.$g5['setting']['set_graph_'.$key].'" class="frm_input" style="width:150px;margin-bottom:5px;"> ('.$value.' <span class="color_gray">'.$key.'</span> 그래프 초기값)<br>'.PHP_EOL;
                }
                unset($set_values);unset($set_value);
                ?>
			</td>
		</tr>
		<tr>
			<th scope="row">그룹별 JSON 호출파일</th>
			<td colspan="3">
				<?php echo help('각 데이터 그룹별로 호출하는 JSON파일명을 입력하세요.'); ?>
                <?php
                $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_data_group']));
                foreach ($set_values as $set_value) {
                    list($key, $value) = explode('=', trim($set_value));
                    echo ' <input type="text" name="set_json_file_'.$key.'" value="'.$g5['setting']['set_json_file_'.$key].'" class="frm_input" style="width:150px;margin-bottom:5px;"> ('.$value.' <span class="color_gray">'.$key.'</span>)<br>'.PHP_EOL;
                }
                unset($set_values);unset($set_value);
                ?>
			</td>
		</tr>
		<tr>
			<th scope="row">데이타 수집 기준</th>
			<td colspan="3">
				<?php echo help('shift=교대기준,date=날짜기준 (기본 디폴트 = shift, 설정값이 없으면 교대기준이라고 봅니다.)'); ?>
				<input type="text" name="set_mms_set_data" value="<?php echo $g5['setting']['set_mms_set_data']; ?>" class="frm_input" style="width:50%;">
			</td>
		</tr>
        <tr>
            <th scope="row">그래프 시간단위</th>
            <td>
				<?php echo help('그래프에서 시간 검색 범위를 선택(timepicker)할 때의 분단위 간격을 숫자로 입력하세요.'); ?>
                <input type="text" name="set_time_step" value="<?php echo $g5['setting']['set_time_step']; ?>" class="frm_input" style="width:30px;"> 분
            </td>
        </tr>
        <tr>
            <th scope="row">그래프 좌표갯수 최대</th>
            <td>
				<?php echo help('좌표갯수 max값에 따라 그래프 로딩시간이 오래 걸릴 수 있습니다. 그래프의 로딩 시간을 봐 가면서 좌표갯수 max값을 조정해 주세요. '); ?>
                <input type="text" name="set_graph_max" value="<?php echo $g5['setting']['set_graph_max']; ?>" class="frm_input" style="width:40px;"> 개
            </td>
        </tr>
		<tr>
			<th scope="row">그래프 단위 설정</th>
			<td colspan="3">
				<?php echo help('daily=일별,weekly=주간별,monthly=월별,yearly=연도별,minute=분,second=초'); ?>
				<input type="text" name="set_graph_unit" value="<?php echo $g5['setting']['set_graph_unit']; ?>" class="frm_input" style="width:50%;">
			</td>
		</tr>
		<tr>
			<th scope="row">그래프 단위별 초기값</th>
			<td colspan="3">
				<?php echo help('각 그래프 단위별로 디폴트 단위 갯수값을 설정하세요. 2개값을 쉽표로 구분하여 입력하세요.
형식: <span class="color_red">단위값, 갯수</span> 형식으로 입력합니다. 아래 예제를 참고하세요.
5,600 = 분단위인 경우 5분단위 600개이므로 5분*600개표시=50시간이 그래프 초기 범위가 됩니다.
30,200 = 분단위인 경우 30분단위 200개이므로 30분*200개표시=100시간이 그래프 초기 범위가 됩니다.
1,12 = 월별인 경우 1개월 단위 12개=1년이 그래프 초기 범위가 됩니다.
1,31 = 일별인 경우 1일단위 31개=1달이 그래프 초기 범위가 됩니다.'); ?>
                <?php
                $set_values = explode(',', preg_replace("/\s+/", "", $g5['setting']['set_graph_unit']));
                foreach ($set_values as $set_value) {
                    list($key, $value) = explode('=', trim($set_value));
                    echo ' <input type="text" name="set_graph_'.$key.'" value="'.$g5['setting']['set_graph_'.$key].'" class="frm_input" style="width:60px;margin-bottom:5px;"> ('.$value.' <span class="color_gray">'.$key.'</span> 단위 선택 시 초기값)<br>'.PHP_EOL;
                }
                unset($set_values);unset($set_value);
                ?>
			</td>
		</tr>
        <tr>
            <th scope="row">디폴트업체번호</th>
            <td>
				<?php echo help('수퍼관리자가 로그인할 때 디폴트 업체 번호입니다. (com_idx)'); ?>
                <input type="text" name="set_com_idx" value="<?php echo $g5['setting']['set_com_idx']; ?>" class="frm_input" style="width:40px;">
            </td>
        </tr>
		<tr>
			<th scope="row">코드타입설정</th>
			<td colspan="3">
				<?php echo help('r=기록, a=알람, p=예지'); ?>
				<input type="text" name="set_cod_type" value="<?php echo $g5['setting']['set_cod_type']; ?>" class="frm_input" style="width:50%;">
			</td>
		</tr>
		<tr>
			<th scope="row">코드그룹명 설정</th>
			<td colspan="3">
				<?php echo help('err=일반알림, pre=PLC예지'); ?>
				<input type="text" name="set_cod_group" value="<?php echo $g5['setting']['set_cod_group']; ?>" class="frm_input" style="width:50%;">
			</td>
		</tr>
		<tr>
			<th scope="row">코드상태 설정</th>
			<td colspan="3">
				<?php echo help('stop=중지,ok=정상') ?>
				<input type="text" name="set_cod_status" value="<?php echo $g5['setting']['set_cod_status']; ?>" class="frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">예지주기 설정</th>
			<td colspan="3">
				<?php echo help('3600=1시간, 86400=1일, 604800=주간, 2592000=월간') ?>
				<input type="text" name="set_cod_interval" value="<?php echo $g5['setting']['set_cod_interval']; ?>" class="frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">메시지발송수단</th>
			<td colspan="3">
				<?php echo help('email=이메일, sms=문자, push=푸시...') ?>
				<input type="text" name="set_send_type" value="<?php echo $g5['setting']['set_send_type']; ?>" class="frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
			<th scope="row">가동상태</th>
			<td colspan="3">
				<?php echo help('0=POWER OFF, 1=수동, 2=자동, 3=이상') ?>
				<input type="text" name="set_run_status" value="<?php echo $g5['setting']['set_run_status']; ?>" class="frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
            <th scope="row">ONESIGNAL APP ID</th>
            <td colspan="3">
                <input type="text" name="set_onesignal_id" value="<?php echo $g5['setting']['set_onesignal_id']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">ONESIGNAL REST API KEY</th>
            <td colspan="3">
                <?php echo help('OneSignal > Settings > Keys & IDs : REST API KEY'); ?>
                <input type="text" name="set_onesignal_key" value="<?php echo $g5['setting']['set_onesignal_key']; ?>" class="frm_input" style="width:60%;">
            </td>
        </tr>
		<tr>
            <th scope="row">1차분류(카테고리1)</th>
            <td>
                <?php echo help('1차분류를 ex)cat1_name=카테고리1설명 이와같이 작성하고 구분은 줄바꿈으로 세로로 나열하세요.<br>(가능한 기존의 작성 순서를 지켜주시고 만약 순서를 변경 하였을시 각 제품(BOM)의 설정된 카테고리도 변경해야 할 수 있습니다.)') ?>
                <textarea name="set_cat_1" id="set_cat_1" style="width:50%;height:200px;"><?php echo get_text($g5['setting']['set_cat_1']); ?></textarea>
            </td>
        </tr>
		<tr>
            <th scope="row">2차분류(카테고리2)</th>
            <td>
                <?php echo help('2차분류를 ex)cat2_name=카테고리2설명 이와같이 작성하고 구분은 줄바꿈으로 세로로 나열하세요.<br>(가능한 기존의 작성 순서를 지켜주시고 만약 순서를 변경 하였을시 각 제품(BOM)의 설정된 카테고리도 변경해야 할 수 있습니다.)') ?>
                <textarea name="set_cat_2" id="set_cat_2" style="width:50%;height:200px;"><?php echo get_text($g5['setting']['set_cat_2']); ?></textarea>
            </td>
        </tr>
		<tr>
            <th scope="row">3차분류(카테고리3)</th>
            <td>
                <?php echo help('3차분류를 ex)cat3_name=카테고리3설명 이와같이 작성하고 구분은 줄바꿈으로 세로로 나열하세요.<br>(가능한 기존의 작성 순서를 지켜주시고 만약 순서를 변경 하였을시 각 제품(BOM)의 설정된 카테고리도 변경해야 할 수 있습니다.)') ?>
                <textarea name="set_cat_3" id="set_cat_3" style="width:50%;height:200px;"><?php echo get_text($g5['setting']['set_cat_3']); ?></textarea>
            </td>
        </tr>
		<tr>
            <th scope="row">4차분류(카테고리4)</th>
            <td>
                <?php echo help('4차분류를 ex)cat4_name=카테고리4설명 이와같이 작성하고 구분은 줄바꿈으로 세로로 나열하세요.<br>(가능한 기존의 작성 순서를 지켜주시고 만약 순서를 변경 하였을시 각 제품(BOM)의 설정된 카테고리도 변경해야 할 수 있습니다.)') ?>
                <textarea name="set_cat_4" id="set_cat_4" style="width:50%;height:200px;"><?php echo get_text($g5['setting']['set_cat_4']); ?></textarea>
            </td>
        </tr>
		<tr>
			<th scope="row">메뉴권한종류 mb_8</th>
			<td colspan="3">
				<?php echo help('adm=총괄관리권한,adm_production=생산관리권한,adm_quality=품질관리권한,normal=일반사원권한') ?>
				<input type="text" name="set_mb_auth" value="<?php echo $g5['setting']['set_mb_auth'] ?>" id="set_mb_auth" required class="required frm_input" style="width:60%;">
			</td>
		</tr>
		<tr>
            <th scope="row">총괄관리메뉴권한</th>
            <td>
                <?php echo help('총괄관리자의 메뉴 접근권한입니다.') ?>
                <textarea name="set_admin_auth" id="set_admin_auth" style="width:50%;"><?php echo get_text($g5['setting']['set_admin_auth']); ?></textarea>
            </td>
        </tr>
		<tr>
            <th scope="row">생산관리메뉴권한</th>
            <td>
                <?php echo help('생산관리자의 메뉴 접근권한입니다.') ?>
                <textarea name="set_admin_production_auth" id="set_admin_production_auth" style="width:50%;"><?php echo get_text($g5['setting']['set_admin_production_auth']); ?></textarea>
            </td>
        </tr>
		<tr>
            <th scope="row">품질관리메뉴권한</th>
            <td>
                <?php echo help('품질관리자의 메뉴 접근권한입니다.') ?>
                <textarea name="set_admin_quality_auth" id="set_admin_quality_auth" style="width:50%;"><?php echo get_text($g5['setting']['set_admin_quality_auth']); ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">사원 메뉴권한</th>
            <td>
                <?php echo help('사원이 등록될 때 디폴트 메뉴 접근권한입니다.') ?>
                <textarea name="set_employee_auth" id="set_employee_auth" style="width:50%;"><?php echo get_text($g5['setting']['set_employee_auth']); ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 메뉴권한</th>
            <td>
                <?php echo help('모바일 회원등록될 때 디폴트 메뉴 접근권한입니다.') ?>
                <textarea name="set_mobile_auth" id="set_mobile_auth" style="width:50%;"><?php echo get_text($g5['setting']['set_mobile_auth']); ?></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row">품질정보입력시차</th>
            <td>
				<?php echo help('교대 시간이 바뀌어도 시차 간격을 두고 품질 정보를 입력합니다.'); ?>
                <input type="text" name="set_quality_input_time" value="<?php echo $g5['setting']['set_quality_input_time']; ?>" class="frm_input" style="width:40px;"> 시간
            </td>
        </tr>
        <tr>
            <th scope="row">UPH기본설비</th>
            <td>
				<?php echo help('75=단조프레스1,65=단조프레스2,77=단조프레스3,78=단조프레스4,79=단조프레스5,80=프레스6 <br>의 값중에서 숫자데이터를 저장하세요.'); ?>
                <input type="text" name="set_uph_mms" value="<?php echo $g5['setting']['set_uph_mms']; ?>" class="frm_input" style="width:60px;">
            </td>
        </tr>
		<tr>
			<th scope="row">설비상태 설정</th>
			<td colspan="3">
				<?php echo help('quality=품질, offwork=비가동'); ?>
				<input type="text" name="set_mst_type" value="<?php echo $g5['setting']['set_mst_type']; ?>" class="frm_input" style="width:50%;">
			</td>
		</tr>
		<tr>
			<th scope="row">로그인 첫페이지</th>
			<td colspan="3">
				<?php echo help('index.php=대시보드, manual_quality_input.php=품질정보입력페이지'); ?>
				<input type="text" name="set_first_page" value="<?php echo $g5['setting']['set_first_page']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">비가동유형</th>
			<td colspan="3">
				<?php echo help('2010=금형교환,2020=트리밍교환,3010=금형수정,3020=트리밍수정,3030=금형준비,3040=금형대기,5010=기계(F/P),5020=기계(T/R),5030=기계(I/H),5040=코일교환,5050=콘베어/모터,5060=ITEM교환,6010=시타(T/O),7010=교육,9010=소재대기,9020=이형제,9030=정전,9040=기타'); ?>
				<input type="text" name="set_downtime_type" value="<?php echo $g5['setting']['set_downtime_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
        <tr>
            <th scope="row">비가동정보입력시차</th>
            <td>
				<?php echo help('설정 시간 이전의 비가동 정보는 입력할 수 없습니다.'); ?>
                <input type="text" name="set_downtime_input_time" value="<?php echo $g5['setting']['set_downtime_input_time']; ?>" class="frm_input" style="width:40px;"> 시간
            </td>
        </tr>
		<tr>
			<th scope="row">원가설정타입</th>
			<td colspan="3">
				<?php echo help('electricity=전기, consumable=소모품, oil=장비유류대, worker=현장작업자, engineer=장비기사'); ?>
				<input type="text" name="set_csc_type" value="<?php echo $g5['setting']['set_csc_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">제품 I/O 구분</th>
			<td colspan="3">
				<?php echo help('제품의 INNER / OUTER / SINGLE 을 구분한다. single=SG, inner=IR, outer=OU '); ?>
				<input type="text" name="set_bom_io_type" value="<?php echo $g5['setting']['set_bom_io_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">불량유형</th>
			<td colspan="3">
				<?php echo help('3010=접합,3020=결육,3030=치수,3040=정치,3050=단조품크랙,3060=형이탈,3070=타흔,3090=밴딩,3100=과열,3110=스케일,3120=덧살,3150=작업자과실,3170=트리밍,3180=시타,3190=기타,3200=금형,3210=후처리,3230=장비,4010=원소재,4020=절단,5010=외주,5020=크레임,5030=대체,5040=개발,5050=열처리,5060=로스'); ?>
				<input type="text" name="set_error_type" value="<?php echo $g5['setting']['set_error_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">제품유형</th>
			<td colspan="3">
				<?php echo help('3000=원자재,3010=SWCH10A,3020=SWCH18A,3030=SWCH25F,3050=SWCH45F,4000=제품,4010=제품OEM,4050=제품A/S,4100=일진,4200=BK'); ?>
				<input type="text" name="set_item_type" value="<?php echo $g5['setting']['set_item_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">BOM타입</th>
			<td colspan="3">
				<?php echo help('product=완성품,half=반제품,material=자재'); ?>
				<input type="text" name="set_bom_type" value="<?php echo $g5['setting']['set_bom_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">BOM구성 표시</th>
			<td colspan="3">
				<?php echo help('제품사양 정보 목록에서 BOM 구조를 표시할 BOM타입을 입력하세요. 쉼표로 구분하고 영문만 입력하세요. ex)product,half '); ?>
				<input type="text" name="set_bom_type_display" value="<?php echo $g5['setting']['set_bom_type_display']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">BOM상태</th>
			<td colspan="3">
				<input type="text" name="set_bom_status" value="<?php echo $g5['setting']['set_bom_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">BOM I/O 여부</th>
			<td colspan="3">
				<?php echo help('제품의 INNER/OUTER의 듀얼생성제품인지 아닌지를 구분한다. 0=실글생성, 1=듀얼생성  '); ?>
				<input type="text" name="set_bom_io_yn" value="<?php echo $g5['setting']['set_bom_io_yn']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">BOM 프레스카운팅 유형</th>
			<td colspan="3">
				<?php echo help('제품의 프레스카운팅 유형을 선택. 0_1=0타1개,1_1=1타1개,1_2=1타2개,2_1=2타1개,2_2=2타2개,1_3=1타3개,1_4=1타4개'); ?>
				<input type="text" name="set_bom_press_type" value="<?php echo $g5['setting']['set_bom_press_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">수주상태</th>
			<td colspan="3">
				<?php echo help('ok=완료,pendign=대기,cancel=취소,trash=삭제'); ?>
				<input type="text" name="set_ord_status" value="<?php echo $g5['setting']['set_ord_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">고객처재고상태</th>
			<td colspan="3">
				<?php echo help('pending=대기,ok=완료,cancel=취소,trash=삭제'); ?>
				<input type="text" name="set_gst_status" value="<?php echo $g5['setting']['set_gst_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">자재입고차수</th>
			<td colspan="3">
				<?php echo help('1=1차,2=2차,3=3차,4=4차,5=5차,6=6차,7=7차,8=8차,9=9차,10=10차'); ?>
				<input type="text" name="set_mtr_times" value="<?php echo $g5['setting']['set_mtr_times']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">자재상태</th>
			<td colspan="3">
				<?php echo help('stock=재고,repairing=수리중,repairstock=수리완료재고,scrap=폐기,pendign=불출대기,used=사용완료,trash=삭제'); ?>
				<input type="text" name="set_mtr_status" value="<?php echo $g5['setting']['set_mtr_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr><tr>
			<th scope="row">자재상태중 양품상태</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) finish'); ?>
				<input type="text" name="set_mtr_status_ok" value="<?php echo $g5['setting']['set_mtr_status_ok']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">자재상태중 불량품상태</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) error_inhomogeneity,error_foreign,error_etc'); ?>
				<input type="text" name="set_mtr_status_ng" value="<?php echo $g5['setting']['set_mtr_status_ng']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">반제품불량타입</th>
			<td colspan="3">
				<?php echo help('3030=error_size,3070=error_dent,3090=error_bend,3150=error_worker,4010=error_material,4020=error_cut,5010=error_subcontractor,3190=error_etc,3195=error_scrap'); ?>
				<input type="text" name="set_mtr_defect_type" value="<?php echo $g5['setting']['set_mtr_defect_type']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">반제품상태</th>
			<td colspan="3">
				<?php echo help('pending=대기,stock=절단완료,finish=사용완료,trash=삭제,error_size=치수불량,error_dent=타흔불량,error_bend=벤딩불량,error_worker=과실불량,error_material=원소재불량,error_cut=절단불량,error_subcontractor=외주불량,error_etc=기타불량,error_scrap=폐기불량'); ?>
				<input type="text" name="set_half_status" value="<?php echo $g5['setting']['set_half_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">반제품상태중 양품상태</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) stock,finish'); ?>
				<input type="text" name="set_half_status_ok" value="<?php echo $g5['setting']['set_half_status_ok']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">반제품상태중 불량품상태</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) error_size,error_dent,error_bend,error_worker,error_material,error_cut,error_subcontractor,error_etc,error_scrap'); ?>
				<input type="text" name="set_half_status_ng" value="<?php echo $g5['setting']['set_half_status_ng']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">반제품상태중 불량품상태2</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) 3030=error_size,3070=error_dent,3090=error_bend,3150=error_worker,4010=error_material,4020=error_cut,5010=error_subcontractor,3190=error_etc,3195=error_scrap'); ?>
				<input type="text" name="set_half_status_ng2" value="<?php echo $g5['setting']['set_half_status_ng2']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">완제품생산시간대</th>
			<td colspan="3">
				<?php echo help('1=시간1,2=시간2,3=시간3,4=시간4,5=시간5,6=시간6,7=시간7,8=시간8,9=시간9,10=시간10'); ?>
				<input type="text" name="set_itm_shift2" value="<?php echo $g5['setting']['set_itm_shift2']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">완제품생산시간대(한국수지)</th>
			<td colspan="3">
				<?php echo help('1=시간1,2=시간2,3=시간3,4=시간4'); ?>
				<input type="text" name="set_itm_shift4" value="<?php echo $g5['setting']['set_itm_shift4']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">완제품상태</th>
			<td colspan="3">
				<?php echo help('pending=대기,finish=생산완료,delivery=출하,trash=삭제,error_fold=접힘불량,error_unformed=결육불량,error_size=치수불량,error_position=정치불량,error_crack=크랙불량,error_dent=타흔불량,error_breakaway=형이탈불량,error_bend=벤딩불량,error_overheat=과열불량,error_scale=스케일불량,error_layer=덧살불량,error_worker=과실불량,error_trim=트리밍불량,error_sita=시타불량,error_material=원소재불량,error_cut=절단불량,error_mold=금형불량,error_equipment=장비불량,error_after=후처리불량,error_subcontractor=외주불량,error_replace=대체불량,error_dev=개발불량,error_heat=열처리불량,error_lose=로스불량,error_etc=기타불량,error_scrap=폐기불량'); ?>
				<input type="text" name="set_itm_status" value="<?php echo $g5['setting']['set_itm_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">완제품상태중 양품상태</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) finish'); ?>
				<input type="text" name="set_itm_status_ok" value="<?php echo $g5['setting']['set_itm_status_ok']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">완제품상태중 불량품상태</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) error_fold,error_unformed,error_size,error_position,error_crack,error_breakaway,error_dent,error_bend,error_overheat,error_scale,error_layer,error_worker,error_trim,error_sita,error_material,error_cut,error_mold,error_equipment,error_after,error_subcontractor,error_claim,error_replace,error_dev,error_heat,error_lose,error_etc,error_scrap'); ?>
				<input type="text" name="set_itm_status_ng" value="<?php echo $g5['setting']['set_itm_status_ng']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">완제품상태중 불량품상태2</th>
			<td colspan="3">
				<?php echo help('영문상태값만 쉽표로 구분해서 입력하세요. 예) 3010=error_fold,3020=error_unformed,3030=error_size,3040=error_position,3050=error_crack,3060=error_breakaway,3070=error_dent,3090=error_bend,3100=error_overheat,3110=error_scale,3120=error_layer,3150=error_worker,3170=error_trim,3180=error_sita,4010=error_material,4020=error_cut,3200=error_mold,3230=error_equipment,3210=error_after,5010=error_subcontractor,5020=error_claim,5030=error_replace,5040=error_dev,5050=error_heat,5060=error_lose,3190=error_etc,3195=error_scrap'); ?>
				<input type="text" name="set_itm_status_ng2" value="<?php echo $g5['setting']['set_itm_status_ng2']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">파렛트상태</th>
			<td colspan="3">
				<?php echo help('pending=적재완료,delivery=출하,trash=삭제'); ?>
				<input type="text" name="set_plt_status" value="<?php echo $g5['setting']['set_plt_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">출하상태</th>
			<td colspan="3">
				<?php echo help('pending=대기,ok=출하완료,trash=삭제'); ?>
				<input type="text" name="set_oro_status" value="<?php echo $g5['setting']['set_oro_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		<tr>
			<th scope="row">출하생산계획상태</th>
			<td colspan="3">
				<?php echo help('predict=예측,confirm=확정,done=완료,trash=삭제'); ?>
				<input type="text" name="set_oop_status" value="<?php echo $g5['setting']['set_oop_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
        <tr>
			<th scope="row">생산계획상태</th>
			<td colspan="3">
				<?php echo help('predict=예측,confirm=확정,done=완료,trash=삭제'); ?>
				<input type="text" name="set_orp_status" value="<?php echo $g5['setting']['set_orp_status']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		</tr>
            <tr>
			<th scope="row">생산시간구간</th>
			<td colspan="3">
				<?php echo help('01=05:10:00-10:09:59,02=10:10:00-12:59:59,03=13:00:00-15:09:59,04=15:10:00-17:09:59,05=17:10:00-19:09:59,06=19:10:00-21:09:59,07=21:10:00-23:09:59,081=23:10:00-23:59:59,082=00:00:00-01:09:59,09=01:10:00-03:09:59,10=03:10:00-05:09:59'); ?>
				<input type="text" name="set_itm_shift" value="<?php echo $g5['setting']['set_itm_shift']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
		</tr>
            <tr>
			<th scope="row">생산시간구간(한국수지)</th>
			<td colspan="3">
				<?php echo help('01=00:00:00-05:59:59,02=06:00:00-11:59:59,03=12:00:00-17:59:59,04=18:00:00-23:59:59'); ?>
				<input type="text" name="set_itm_shift3" value="<?php echo $g5['setting']['set_itm_shift3']; ?>" class="frm_input" style="width:70%;">
			</td>
		</tr>
        </tbody>
		</table>
	</div>
</section>



<section id="anc_cf_message">
    <h2 class="h2_frm">메시지설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>메시지설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">알람/예지 메일</th>
            <td colspan="3">
                <?php echo help('치환 변수: {제목} {업체명} {이름} {설비명} {코드} {만료일} {년월일} {남은기간} {HOME_URL}'); ?>
                <input type="text" name="set_error_subject" value="<?php echo $g5['setting']['set_error_subject']; ?>" class="frm_input" style="width:80%;" placeholder="메일제목">
                <?php echo editor_html("set_error_content", get_text($g5['setting']['set_error_content'], 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">계획정비 메일</th>
            <td colspan="3">
                <?php echo help('치환 변수: {제목} {업체명} {이름} {설비명} {만료일} {년월일} {남은기간} {HOME_URL}'); ?>
                <input type="text" name="set_maintain_plan_subject" value="<?php echo $g5['setting']['set_maintain_plan_subject']; ?>" class="frm_input" style="width:80%;" placeholder="메일제목">
                <?php echo editor_html("set_maintain_plan_content", get_text($g5['setting']['set_maintain_plan_content'], 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">게시판 new 아이콘</th>
            <td>
                <input type="text" name="set_new_icon_hour" value="<?php echo $g5['setting']['set_new_icon_hour']; ?>" class="frm_input" style="width:20px;"> 시간동안 new 아이콘 표시
            </td>
            <th scope="row">new 아이콘 주말포함</th>
            <td>
                <div style="visibility:hidden;">
                <label for="set_new_icon_holiday_yn_1">
                    <input type="radio" name="set_new_icon_holiday_yn" value="1" id="set_new_icon_holiday_yn_1" <?php echo ($g5['setting']['set_new_icon_holiday_yn']) ? 'checked':'' ?>> 영업일만 포함
                </label> &nbsp;&nbsp;
                <label for="set_new_icon_holiday_yn_0">
                    <input type="radio" name="set_new_icon_holiday_yn" value="0" id="set_new_icon_holiday_yn_0" <?php echo ($g5['setting']['set_new_icon_holiday_yn']) ? '':'checked' ?>> 주말까지 포함
                </label>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">만료공지 메일</th>
            <td colspan="3">
                <?php echo help('치환 변수: {법인명} {업체명} {담당자} {년월일} {승인명} {남은기간} {HOME_URL} {연락처} {이메일}'); ?>
                <input type="text" name="set_expire_email_subject" value="<?php echo $g5['setting']['set_expire_email_subject']; ?>" class="frm_input" style="width:80%;" placeholder="메일제목">
                <?php echo editor_html("set_expire_email_content", get_text($g5['setting']['set_expire_email_content'], 0)); ?>
            </td>
        </tr>
		</tbody>
		</table>
	</div>
</section>

<section id="anc_cf_secure">
    <h2 class="h2_frm">관리설정</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>관리자 설정입니다.</p>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>관리설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">관리자메모</th>
            <td>
                <?php echo help('관리자 메모입니다.') ?>
                <textarea name="set_memo_super" id="set_memo_super"><?php echo get_text($g5['setting']['set_memo_super']); ?></textarea>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
$(function(){

});

function fconfigform_submit(f) {

    <?php echo get_editor_js("set_expire_email_content"); ?>
    <?php echo chk_editor_js("set_expire_email_content"); ?>
    <?php echo get_editor_js("set_maintain_plan_content"); ?>
    <?php echo chk_editor_js("set_maintain_plan_content"); ?>
    <?php echo get_editor_js("set_error_content"); ?>
    <?php echo chk_editor_js("set_error_content"); ?>

    f.action = "./config_form_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
