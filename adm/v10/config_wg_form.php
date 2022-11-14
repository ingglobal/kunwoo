<?php
$sub_menu = "990100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

if(!$config['cf_faq_skin']) $config['cf_faq_skin'] = "basic";
if(!$config['cf_mobile_faq_skin']) $config['cf_mobile_faq_skin'] = "basic";

$g5['title'] = '위젯환경(대시보드)설정';
include_once('./_top_menu_setting.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_wg_basic">위젯(대시보드)기본 설정</a></li>
</ul>';

if (!$config['cf_icode_server_ip'])   $config['cf_icode_server_ip'] = '211.172.232.124';
if (!$config['cf_icode_server_port']) $config['cf_icode_server_port'] = '7295';

if ($config['cf_sms_use'] && $config['cf_icode_id'] && $config['cf_icode_pw']) {
    $userinfo = get_icode_userinfo($config['cf_icode_id'], $config['cf_icode_pw']);
}
?>

<form name="fconfigwgform" id="fconfigwgform" method="post" onsubmit="return fconfigwgform_submit(this);">
<input type="hidden" name="token" value="" id="token">
<section id="anc_wg_basic">
	<h2 class="h2_frm">위젯 환경설정</h2>
    <?php echo $pg_anchor; ?>
	<table class="tbl_frm">
		<colgroup>
			<col span="1" width="110">
			<col span="1" width="250">
			<col span="1" width="110">
			<col span="1" width="250">
			<col span="1" width="110">
			<col span="1" width="250">
		</colgroup>
		<tbody>
			<tr>
				<th>BP위젯목록<br>메뉴번호</th>
				<td class="wgf_help">
					<?php echo wgf_help("여섯자리 숫자 입력. 값이 없으면 기본값은 '910100'으로 입력됩니다.",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_sub_menu"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_sub_menu?>">
				</td>
				<th>BP위젯환경설정<br>메뉴번호</th>
				<td class="wgf_help">
					<?php echo wgf_help("여섯자리 숫자 입력. 값이 없으면 기본값 '910200'으로 입력됩니다.",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_sub_menu2"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_sub_menu2?>">
				</td>
				<th>BP위젯캐시<br>저장시간</th>
				<td class="wgf_help">
					<!--
					$cache_time은 시간단위 
					1시간=1, 5초=0.00139, 10초=0.0028, 20초=0.0056, 30초=0.0084, 40초=0.012, 50초=0.0139, 60초=0.0167, 3600초=1시간
					-->
					<?php echo wgf_help("캐시 저장시간의 값이 작을수록 위젯 수정후 반영되는 시간이 짧아집니다.",1,'#f9fac6','#333333'); ?>
					<?php echo wgf_select_selected($set_cachetime, 'set_cache_time', $set_cache_time, 0,0,0);//인수('pending=대기,ok=정상,hide=숨김,trash=삭제','set_status(name속성)','ok(값)',0(값없음활성화),1(필수여부)) ?>
				</td>
			</tr>
			<tr>
				<th>개별업로드<br>파일용량</th>
				<td class="wgf_help">
					<?php echo wgf_help("(예 : 300)개별업로드 용량이 크면 페이지로딩에 영향을 줍니다.",1,'#f9fac6','#333333'); ?>
					최대 <input type="text" name="set_filesize" class="wg_wdp40" value="<?=$set_filesize?>" style="text-align:right;">&nbsp;KB 까지
				</td>
				<th>업로드하는<br>멀티파일 총용량</th>
				<td class="wgf_help">
					<?php echo wgf_help("(예 : 3000)멀티파일 총용량이 크면 페이지로딩에 영향을 줍니다.",1,'#f9fac6','#333333'); ?>
					최대 <input type="text" name="set_total_filesize" class="wg_wdp40" value="<?=$set_total_filesize?>" style="text-align:right;">&nbsp;KB 까지
				</td>
				<th>PC기본색상</th>
				<td class="wgf_help">
					<?php echo wgf_help("PC버전에서 사이트 전체 기본 배경/폰트 색상을 설정하세요.",1,'#f9fac6','#333333'); ?>
					<ul class="ul_pc_basic_color">
						<li>
						배경<br>
						<?php echo wgf_input_color('set_default_bg',$set_default_bg,$w); ?>
						</li>
						<li>
						폰트<br>
						<?php echo wgf_input_color('set_default_font',$set_default_font,$w); ?>
						</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th>모바일 기본색상</th>
				<td class="wgf_help">
					<?php echo wgf_help("모바일 버전에서 사이트 전체 기본 배경/폰트 색상을 설정하세요.",1,'#f9fac6','#333333'); ?>
					<ul class="ul_pc_basic_color">
						<li>
						배경<br>
						<?php echo wgf_input_color('set_mo_default_bg',$set_mo_default_bg,$w); ?>
						</li>
						<li>
						폰트<br>
						<?php echo wgf_input_color('set_mo_default_font',$set_mo_default_font,$w); ?>
						</li>
					</ul>
				</td>
				<th>기본선형<br>그라데이션색상</th>
				<td colspan="<?=$colspan3?>" class="wgf_help">
					<?php echo wgf_help("사이트 전체 기본 선형 그라데이션 색상을 설정하세요.(주로 로그인,비번확인,비번찾기 페이지에서 사용됨.)",1,'#f9fac6','#333333'); ?>
					<ul class="ul_pc_basic_color">
						<li>
						From 색상<br>
						<?php echo wgf_input_color('set_gradient_from',$set_gradient_from,$w); ?>
						</li>
						<li>
						To 색상<br>
						<?php echo wgf_input_color('set_gradient_to',$set_gradient_to,$w); ?>
						</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th>디바이스 종류</th>
				<td colspan="<?=$colspan3?>" class="wgf_help">
					<?php echo wgf_help("예제 : pc=PC,mobile=MOBILE",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_device"<?=$readonly?> class="wg_wdx243<?=$readonly?>" value="<?=$set_device?>">
				</td>
			</tr>
			<tr>
				<th>캐시시간</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : 0=0초,0.00139=5초,0.0028=10초,0.0056=20초,0.0084=30초,0.012=40초,0.0139=50초,0.0167=60초,1=1시간",1,'#f9fac6','#333333'); ?>
					<!--
					$cache_time은 시간단위 
					1시간=1, 5초=0.00139, 10초=0.0028, 20초=0.0056, 30초=0.0084, 40초=0.012, 50초=0.0139, 60초=0.0167, 3600초=1시간
					-->
					<input type="text" name="set_cachetime"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_cachetime?>">
				</td>
				<th>공통 상태</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : pending=대기,ok=정상,hide=숨김,trash=삭제",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_common_status"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_common_status?>">
				</td>
			</tr>
			<tr>
				<th>콘텐츠페이지 탭목록</th>
				<td colspan="<?=$colspan5?>" class="wgf_help">
					<?php echo wgf_help("예제 : 회사소개,인사말,개인정보처리방침(내용관리에서 관리하는 내용입니다.)",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_content_tab_list" readonly class="wg_wdp100 readonly" value="<?=$set_content_tab_list?>">
				</td>
			</tr>
			<tr>
				<th>링크타겟</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : _self=현재창,_blank=새창",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_target"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_target?>">
				</td>
				<th>여부(예/아니오)</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : yes=예,no=아니오",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_yes_no"<?=$readonly?> class="wg_wdx240<?=$readonly?>" value="<?=$set_yes_no?>">
				</td>
				<th>너비/높이</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : width=너비,height=높이",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_width_height"<?=$readonly?> class="wg_wdx240<?=$readonly?>" value="<?=$set_width_height?>">
				</td>
			</tr>
			<tr>
				<th>표시여부확인</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : show=표시,hide=비표시",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_show_hide"<?=$readonly?> class="wg_wdx242<?=$readonly?>" value="<?=$set_show_hide?>">
				</td>
				<th>사용여부확인</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : use=사용,nouse=사용안함",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_use_nouse"<?=$readonly?> class="wg_wdx242<?=$readonly?>" value="<?=$set_use_nouse?>">
				</td>
				<th>자동여부확인</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : auto=자동,manual=수동",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_auto_manual"<?=$readonly?> class="wg_wdx242<?=$readonly?>" value="<?=$set_auto_manual?>">
				</td>
			</tr>
			<tr>
				<th>사이트하단<br>기본간격</th>
				<td colspan="<?=$colspan3?>" class="wgf_help">
					<?php echo wgf_help("사이트 하단(푸터바로 위)의 기본간격을 설정해 주세요.(미세조정은 키보드의 방향키로 하세요.)",1,'#f9fac6','#333333'); ?>
					<?php
					$set_container_bottom_interval = (isset($set_container_bottom_interval)) ? $set_container_bottom_interval : 100;
					echo wgf_input_range('set_container_bottom_interval',$set_container_bottom_interval,$w,20,200,10,'147',48,'px');
					?>
				</td>
				<th>PC최근자료그룹<br>상단간격</th>
				<td class="wgf_help">
					<?php echo wgf_help("PC메인에서 최근자료(레이티스트)그룹의 상단간격을 설정해 주세요.(미세조정은 키보드의 방향키로 하세요.)",1,'#f9fac6','#333333'); ?>
					<?php
					$set_latest_top_interval = (isset($set_latest_top_interval)) ? $set_latest_top_interval : 100;
					echo wgf_input_range('set_latest_top_interval',$set_latest_top_interval,$w,20,200,10,'147',48,'px');
					?>
				</td>
			</tr>
			<tr>
				<th>위젯 사용범주</th>
				<td colspan="<?=$colspan3?>" class="wgf_help">
					<?php echo wgf_help("예제 : banner=배너,content=콘텐츠,board=게시판,shop=쇼핑몰,item=상품,turn=360회전이미지,section=섹션스킨,etc=기타",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_purpose"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_purpose?>">
				</td>
				<th>최근자료간<br>사이드간격</th>
				<td class="wgf_help">
					<?php echo wgf_help("최근자료간 사이드간격을 설정해 주세요.(미세조정은 키보드의 방향키로 하세요.)",1,'#f9fac6','#333333'); ?>
					<?php
					$set_latest_side_interval = (isset($set_latest_side_interval)) ? $set_latest_side_interval : 20;
					echo wgf_input_range('set_latest_side_interval',$set_latest_side_interval,$w,0,80,1,'147',48,'px');
					?>
				</td>
			</tr>
			<tr>
				<th>가로정렬위치</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : left=왼쪽,center=가운데,right=오른쪽",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_horizontal_align"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_horizontal_align?>">
				</td>
				<th>가로정렬위치2</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : left=왼쪽,right=오른쪽",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_horizontal_align2"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_horizontal_align2?>">
				</td>
				<th>가로정렬위치3</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : left=왼쪽,center=가운데",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_horizontal_align3"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_horizontal_align3?>">
				</td>
			</tr>
			<tr>
				<th>단위</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : px=PX,%=%",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_unit"<?=$readonly?> class="wg_wdx240<?=$readonly?>" value="<?=$set_unit?>">
				</td>
				<th>단위2</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : px=PX,%=%,pt=PT,em=EM",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_unit2"<?=$readonly?> class="wg_wdx240<?=$readonly?>" value="<?=$set_unit2?>">
				</td>
				<th>검색유형</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : yes=상품검색,no=일반검색",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_sch_shop"<?=$readonly?> class="wg_wdx240<?=$readonly?>" value="<?=$set_sch_shop?>">
				</td>
			</tr>
			<tr>
				<th>모바일 최대너비</th>
				<td class="wgf_help">
					<?php echo wgf_help("PC화면에서 모바일화면 최대너비(폭)을 설정해 주세요.(미세조정은 키보드의 방향키로 하세요.)",1,'#f9fac6','#333333'); ?>
					<?php
					$set_mobile_max_width = (isset($set_mobile_max_width)) ? $set_mobile_max_width : 800;
					echo wgf_input_range('set_mobile_max_width',$set_mobile_max_width,$w,640,900,10,'147',48,'px');
					?>
				</td>
				<th>세로정렬위치</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : top=상단,middle=가운데,bottom=하단",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_vertical_align"<?=$readonly?> class="wg_wdp100<?=$readonly?>" value="<?=$set_vertical_align?>">
				</td>
				<th>세로정렬위치2</th>
				<td class="wgf_help">
					<?php echo wgf_help("예제 : top=상단,bottom=하단",1,'#f9fac6','#333333'); ?>
					<input type="text" name="set_vertical_align2"<?=$readonly?> class="wg_wdx240<?=$readonly?>" value="<?=$set_vertical_align2?>">
				</td>
			</tr>
			<tr>
				<th>사이트기본<br>버튼색상</th>
				<td class="wgf_help">
					<?php echo wgf_help("사이트에서 사용하는 기본 버튼색상을 설정하세요.<span style='color:#0000ff;'>(위의 첫번째 색상그룹은 버튼의 기본색상, 아래 두번째 색상그룹은 마우스오버시 색상, 아래 세번째 색상 그룹은 버튼폰트색상.)</span>",1,'#f9fac6','#333333'); ?>
					<ul class="adm_color_ul">
						<li class="adm_color_li"><p class="adm_color_p basic_color1"><?=$set_basic_color1?></p><?php echo wgf_input_color('set_basic_color1',$set_basic_color1,$w); ?><p>#1</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_color2"><?=$set_basic_color2?></p><?php echo wgf_input_color('set_basic_color2',$set_basic_color2,$w); ?><p>#2</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_color3"><?=$set_basic_color3?></p><?php echo wgf_input_color('set_basic_color3',$set_basic_color3,$w); ?><p>#3</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_color4"><?=$set_basic_color4?></p><?php echo wgf_input_color('set_basic_color4',$set_basic_color4,$w); ?><p>#4</p></li>
					</ul>
					<ul class="adm_color_ul" style="border-top:1px solid #ddd;">
						<li class="adm_color_li"><p class="adm_color_p basic_hover_color1"><?=$set_basic_hover_color1?></p><?php echo wgf_input_color('set_basic_hover_color1',$set_basic_hover_color1,$w); ?><p>#1_hover</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_hover_color2"><?=$set_basic_hover_color2?></p><?php echo wgf_input_color('set_basic_hover_color2',$set_basic_hover_color2,$w); ?><p>#2_hover</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_hover_color3"><?=$set_basic_hover_color3?></p><?php echo wgf_input_color('set_basic_hover_color3',$set_basic_hover_color3,$w); ?><p>#3_hover</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_hover_color4"><?=$set_basic_hover_color4?></p><?php echo wgf_input_color('set_basic_hover_color4',$set_basic_hover_color4,$w); ?><p>#4_hover</p></li>
					</ul>
					<ul class="adm_color_ul" style="border-top:1px solid #ddd;">
						<li class="adm_color_li"><p class="adm_color_p basic_font_color1"><?=$set_basic_font_color1?></p><?php echo wgf_input_color('set_basic_font_color1',$set_basic_font_color1,$w); ?><p>#1_font</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_font_color2"><?=$set_basic_font_color2?></p><?php echo wgf_input_color('set_basic_font_color2',$set_basic_font_color2,$w); ?><p>#2_font</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_font_color3"><?=$set_basic_font_color3?></p><?php echo wgf_input_color('set_basic_font_color3',$set_basic_font_color3,$w); ?><p>#3_font</p></li>
						<li class="adm_color_li"><p class="adm_color_p basic_font_color4"><?=$set_basic_font_color4?></p><?php echo wgf_input_color('set_basic_font_color4',$set_basic_font_color4,$w); ?><p>#4_font</p></li>
					</ul>
				</td>
				<td colspan="<?=$colspan4?>" class="wgf_help">
					<?php echo wgf_help("해당 색상을 사용한 버튼의 클래스명<strong style='color:blue;'>(예: .btn_submit,.btn_cart)</strong>각 클래스는 쉼표(,)로 구분하세요.",1,'#f9fac6','#333333'); ?>
					<p class="adm_basic_color_p first_p">#1_basic&nbsp; : <input type="text" name="set_basic_color1_class" class="wg_wdp90" value="<?=$set_basic_color1_class?>"></p>
					<p class="adm_basic_color_p">#1_hover : <input type="text" name="set_basic_color1_hover_class" class="wg_wdp90" value="<?=$set_basic_color1_hover_class?>"></p>
					<p class="adm_basic_color_p chief_p">#2_basic&nbsp; : <input type="text" name="set_basic_color2_class" class="wg_wdp90" value="<?=$set_basic_color2_class?>"></p>
					<p class="adm_basic_color_p">#2_hover : <input type="text" name="set_basic_color2_hover_class" class="wg_wdp90" value="<?=$set_basic_color2_hover_class?>"></p>
					<p class="adm_basic_color_p chief_p">#3_basic&nbsp; : <input type="text" name="set_basic_color3_class" class="wg_wdp90" value="<?=$set_basic_color3_class?>"></p>
					<p class="adm_basic_color_p">#3_hover : <input type="text" name="set_basic_color3_hover_class" class="wg_wdp90" value="<?=$set_basic_color3_hover_class?>"></p>
					<p class="adm_basic_color_p chief_p">#4_basic&nbsp; : <input type="text" name="set_basic_color4_class" class="wg_wdp90" value="<?=$set_basic_color4_class?>"></p>
					<p class="adm_basic_color_p">#4_hover : <input type="text" name="set_basic_color4_hover_class" class="wg_wdp90" value="<?=$set_basic_color4_hover_class?>"></p>
				</td>
			</tr>
			<tr>
				<th>미디어쿼리<br>사이즈구분<br>너비(폭)</th>
				<td colspan="<?=$colspan5?>" class="wgf_help">
					<style>
					.ul_mda{}
					.ul_mda:after{display:block;visibility:hidden;clear:both;content:"";}
					.ul_mda li{float:left;width:25%;}
					.ul_mda li input{text-align:right;padding-right:5px;}
					</style>
					<?php
					$set_media_wd_xl = ($set_media_wd_xl) ? $set_media_wd_xl : 1200;
					$set_media_wd_lg = ($set_media_wd_lg) ? $set_media_wd_lg : 992;
					$set_media_wd_md = ($set_media_wd_md) ? $set_media_wd_md : 768;
					$set_media_wd_sm = ($set_media_wd_sm) ? $set_media_wd_sm : 576;
					?>
					<ul class="ul_mda">
						<li>
							<span>extrem large</span><br>
							<input type="text" name="set_media_wd_xl"<?=$readonly?> class="wa_wdx100<?=$readonly?>" value="<?=$set_media_wd_xl?>">&nbsp;px
						</li>
						<li>
							<span>large</span><br>
							<input type="text" name="set_media_wd_lg"<?=$readonly?> class="wg_wdx100<?=$readonly?>" value="<?=$set_media_wd_lg?>">&nbsp;px
						</li>
						<li>
							<span>medium</span><br>
							<input type="text" name="set_media_wd_md"<?=$readonly?> class="wg_wdx100<?=$readonly?>" value="<?=$set_media_wd_md?>">&nbsp;px
						</li>
						<li>
							<span>small</span><br>
							<input type="text" name="set_media_wd_sm"<?=$readonly?> class="wg_wdx100<?=$readonly?>" value="<?=$set_media_wd_sm?>">&nbsp;px
						</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
</section><!--#anc_wg_basic-->
<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
$(function(){

});

function fconfigwgform_submit(f) {


    f.action = "./config_wg_form_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
