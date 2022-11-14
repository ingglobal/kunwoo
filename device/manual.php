<?php
include_once('../common.php');
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="imagetoolbar" content="no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=1,user-scalable=yes">
<title>올스타그램 변수 래퍼런스</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.theme.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<style>
/*====================================== 초기화============================================*/
html {overflow-y:scroll;padding:0;margin:0;}
body{padding:0;margin:0;}
pre{padding:0px;margin:0;height:auto;}
/*========================================head영역==========================================*/
#ml_head{}
#ml_head h5{text-align:center;padding:20px 0px;}
/*========================================content영역===========================================*/
#ml_content{padding:0 20px;}
section{margin-top:80px;}
section#sec_member{margin-top:0px;}
section h6{padding:10px 0px;font-size:2em;}
section table th{text-align:center;}
section table td.no{width:70px;text-align:center;}
section table td.nm{width:200px;}
section table td.tx{}
/*========================================footer영역=============================================*/
#ml_footer{}
.cancel td{text-decoration:line-through;color:#ffa8a8;}
.cancel {text-decoration:line-through;color:#ffa8a8;}
.added td{color:#ff22a5;}
.added{color:#ff22a5;}
</style>
</head>
<body>
<div id="ml_head">
	<h5>올스타그램 변수 래퍼런스</h5>
</div>
<div id="ml_content">
	<!--==========================================================  회원 관련  ============================================================-->
	<section id="sec_member">
		<h6>인스타회원 목록 [서버별] (http://allstagram.kr/i/member.php?no=서버번호)</h6>
		<p></p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">member_idx</td>
				<td class="tx">회원의 고유번호</td>
			</tr>
			<tr>
				<td class="no">1</td><td class="nm">member_type</td>
				<td class="tx">회원구분 (allsta=정회원,intra=직원,like=유령라이크계정,buy=구매계정,foreigner=외국인)</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">insta_id</td>
				<td class="tx">인스타그램 계정</td>
			</tr>
			<tr>
				<td class="no">3</td><td class="nm">insta_password</td>
				<td class="tx">인스타그램 계정의 비밀번호</td>
			</tr>
			<tr>
				<td class="no">4</td><td class="nm">insta_hp</td>
				<td class="tx">인스타그램 계정의 휴대폰번호</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">insta_email</td>
				<td class="tx">인스타그램 계정의 이메일</td>
			</tr>
			<tr>
				<td class="no">6</td><td class="nm">insta_footer_content</td>
				<td class="tx">인트타그램계정페이지의 푸터정보</td>
			</tr>
			<tr>
				<td class="no">7</td><td class="nm">server_no</td>
				<td class="tx">서비스를 실행할 서버번호</td>
			</tr>
			<tr>
				<td class="no">8</td><td class="nm">member_status</td>
				<td class="tx">회원의 상태값(pendding=대기, ok=승인)</td>
			</tr>
			<tr>
				<td class="no">9</td><td class="nm">member_reg_dt</td>
				<td class="tx">회원등록일</td>
			</tr>
			<tr>
				<td class="no">10</td><td class="nm">member_update_dt</td>
				<td class="tx">회원정보수정일</td>
			</tr>
			<tr>
				<td class="no">11</td><td class="nm">member_log.php</td>
				<td class="tx">회원정보 로그 파일 (피드정보 + 좋아요 + 댓글 카운트 등..)</td>
			</tr>
			</tbody>
		</table>

		<pre style="font-size:0.8em;border:solid 1px #dadada;margin-left:40px;margin-bottom:20px;padding-top:20px;">
		회원 로그인 후 
		그 회원  피드 리스트 정보 아래와 같이 배열로 보낼꼐요 

		requestData.put("mem_idx[]", mem_idx);               // 올스타그램 회원 시퀀스
		requestData.put("feed_pk[]", feed_pk);               // 인스타 피드 pk (실제 PK로 사용)
		requestData.put("feed_id[]", feed_id);               // 인스타 피드 id (pk_업로드id로 추청됨, 참고용 필드)
		requestData.put("like_count[]", like_count);         // 좋아요 갯수
		requestData.put("comment_count[]", comment_count);   // 댓글 갯수
		requestData.put("caption[]", feed.getCaption());     // 내용 (태그 #insta_15245 형태에서 뒷부분 숫자 = 올스타 ifd_idx | 씽크 맞춤)

		</pre>
		
	</section>

	<!--==========================================================  인스타 회원 정보 업데이트 관련  ================================================-->
	<section id="sec_member">
		<h6>인스타 로그인 후 회원 정보 업데이트 (http://allstagram.kr/i/member_update.php)</h6>
		<!-- 간단설명 -->
		<ul style="font-size:0.8em;border:solid 1px #dadada;margin:30px;padding-top:10px;padding-bottom:10px;">
			<li>크롤링 서버가 인스타 로그인을 하고 나면 정보를 가지고 와서 올스타쪽으로 넣어줍니다.</li>
			<li>시간차가 다소 있겠지만 인스타 회원정보를 올스타쪽이랑 계속 동기화시켜 줍니다.</li>
		</ul>
		<!-- 변수 설명 -->
		<p>
			<ol>
				<li>no<br>
					정수형<br>
					서버번호 (크롤링 서버가 여러대일 수 있으므로 no로 크롤링 서버를 구분합니다.)
				</li>
				<li>mem_idx<br>
					올스타 회원 pk<br>
					올스타그램쪽 PK (mem_idx = imb_idx)
				</li>
				<li>insta_pk<br>
					bigint형예요. 전부 숫자로 되어 있네요.<br>
					인스타그램쪽 고유 PK입니다. (인스타에서 넘겨받은 값을 올스타로 넘겨줍니다.)
				</li>
				<li>follower_count: 팔로워카운트</li>
				<li>following_count: 팔로잉카운트</li>
				<li>profile_pic_url: 프로필 사진 URL</li>
			</ol>
		</p>
		<hr>
		<!-- 장과장이 준 메시지 -->
		<p style="font-size:0.8em;border:solid 1px #dadada;margin:30px;padding:20px;">
		실장님 인스타에서 받은 회원정보 조회 응답값입니다. <br>
		{"user": {"pk": 3050955354, "username": "jisiksohn", "full_name": "Ji-SikSohn", "is_private": false, "profile_pic_url": "https://scontent-icn1-1.cdninstagram.com/vp/e790fbe1df163d4eba51039f9f2d2f96/5B604AAD/t51.2885-19/s150x150/26221651_174625889964092_2104397060072538112_n.jpg", "profile_pic_id": "1694938728117751195_3050955354", "is_verified": false, "has_anonymous_profile_picture": false, "media_count": 51, "geo_media_count": 0, "follower_count": 55, "following_count": 17, "biography": "", "external_url": "", "usertags_count": 0, "hd_profile_pic_versions": [{"width": 320, "height": 320, "url": "https://scontent-icn1-1.cdninstagram.com/vp/b149aa5d0ce0df0302e5d161ecf3d9e4/5B77315D/t51.2885-19/s320x320/26221651_174625889964092_2104397060072538112_n.jpg"}, {"width": 640, "height": 640, "url": "https://scontent-icn1-1.cdninstagram.com/vp/52bce35c4bc9e51869cace3f4770dccf/5B794E32/t51.2885-19/s640x640/26221651_174625889964092_2104397060072538112_n.jpg"}], "hd_profile_pic_url_info": {"url": "https://scontent-icn1-1.cdninstagram.com/vp/508b7e3f54b0a0c7b414b5687513f4bf/5B632857/t51.2885-19/26221651_174625889964092_2104397060072538112_n.jpg", "width": 720, "height": 720}, "has_highlight_reels": false, "auto_expand_chaining": false}, "status": "ok"}
		</p>
	</section>

	<!--==========================================================  인스타 팔로잉 정보 업데이트 관련  ================================================-->
	<section id="sec_member">
		<h6>인스타 로그인 후 팔로잉 정보 입력 & 업데이트 (http://allstagram.kr/i/member_follow_update.php)</h6>
		<!-- 간단설명 -->
		<ul style="font-size:0.8em;border:solid 1px #dadada;margin:30px;padding-top:10px;padding-bottom:10px;">
			<li>크롤링 서버가 인스타 로그인을 하고 나면 팔로잉한 인스타 회원 정보를 올스타쪽으로 넣어줍니다. 로그인 후 피드 정보를 배열로 던져주는 방식과 같습니다.</li>
			<li>누구한테 팔로잉을 하고 다녔는지 올스타쪽에서 알 수 있습니다.</li>
		</ul>
		<!-- 변수 설명 -->
		<p>
			<ol>
				<li>mem_idx[]<br>
					올스타 회원 pk<br>
					올스타그램쪽 PK (mem_idx = imb_idx)
				</li>
				<li>insta_pk[]<br>
					인스타 회원 PK bigint형예요. 전부 숫자로 되어 있네요.<br>
					인스타그램쪽 고유 PK입니다. (인스타에서 넘겨받은 값을 올스타로 넘겨줍니다.)
				</li>
				<li>insta_name[]</li>
				<li>insta_full_name[]</li>
				<li>insta_profile_pic_url[]</li>
			</ol>
		</p>
		<hr>
		<!-- 장과장이 준 메시지 -->
		<p style="font-size:0.8em;border:solid 1px #dadada;margin:30px;padding:20px;">
		
		</p>
	</section>


	

	<!--==========================================================  피드관련  ============================================================-->
	<section id="sec_feed">
		<h6>콘텐츠 Feed작업 (http://allstagram.kr/i/feed.php?id=인스타계정아이디)</h6>
		<p>
			<ul style="font-size:0.8em;">
				<li>서비스구분은 전부 service_type = feed 값으로 동일하다.</li>
				<li>회원별로 오늘 올려야 할 피드 정보를 추출해서 인스타 계정에 업로드한다. (이미지, 슬라이드, 동영상)</li>
				<li>피드 등록 가능한 조건들을 아래와 같다.
					<div>
						계약기간이 아니면 등록 안 한다. service_start_date ~ service_expire_date
						<br>
						서비스 상태가 ok 인 것만 등록가능 service_status
						<br>
						피드등록 불가능 일자 feed_nowork_day
						<br>
						오늘 요일 체크 feed_week (오늘 요일이 아니면 등록 안 하면 된다.)
					</div>
				</li>
				<li>해당 시간에 등록한다. 그 시간대면 된다. 0~24시간 feed_hour</li>
				<li>삽일할 콘텐츠 내용: 내용(feed_content) + 고정태그(feed_fix_tag) + 랜덤태그(feed_random_tag 들 중에서 랜덤으로 feed_random_cnt 개) + 피드푸터(feed_footer)</li>
				<li></li>
				<li>결과 전달: http://allstagram.kr/i/feed_log.php?id=인스타계정아이디</li>
			</ul>
		</p>
		<pre style="font-size:0.8em;border:solid 1px #dadada;margin-left:40px;margin-bottom:20px;padding-top:20px;">
		1. feed 작업 후 결과
		  > 결과 전달: http://allstagram.kr/i/feed_log.php?id=인스타계정아이디
			- 파라미터
			   - resultCode : 코드 ( ok / error )
			   - resultMessage : 메시지
			   - id : 인스타 계정
  			   - feed_idx : 피드 고유번호 (올스타 PK)
  			   - insta_feed_idx : 피드 고유번호 (인스타 PK)

		</pre>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
				<tr>
					<td class="no">1</td><td class="nm">service_idx</td>
					<td class="tx">서비스고유번호</td>
				</tr>
				<tr>
					<td class="no">2</td><td class="nm">service_type</td>
					<td class="tx">서비스구분(bot=봇, like=좋아요., feed=콘텐츠업로드, comment=댓글)</td>
				</tr>
				<tr>
					<td class="no">3</td><td class="nm">service_start_date</td>
					<td class="tx">서비스 시작일(ex : 2018-03-03)</td>
				</tr>
				<tr>
					<td class="no">4</td><td class="nm">service_expire_date</td>
					<td class="tx">서비스 종료일(ex : 2018-03-03)</td>
				</tr>
				<tr class="added">
					<td class="no">5</td><td class="nm">service_status</td>
					<td class="tx">서비스상태<span style="text-decoration:line-through">(on=진행중, off=중지)</span> 변경되었어요.(ok=진행중, stop=중지)</td>
				</tr>
				<tr>
					<td class="no">6</td><td class="nm">feed_idx</td>
					<td class="tx">피드 고유번호 (올스타그램 피드 PK)</td>
				</tr>
				<tr>
					<td class="no">7</td><td class="nm">insta_id</td>
					<td class="tx">인스타그램 계정</td>
				</tr>
				<tr>
					<td class="no">8</td><td class="nm">insta_password</td>
					<td class="tx">인스타그램 계정의 비밀번호</td>
				</tr>
				<tr class="cancel">
					<td class="no">9</td><td class="nm">feed_nowork_day</td>
					<td class="tx">피드등록을 실행하면 안되는 날짜(배열형태 : 2018-01-01, 2018-03-01, 2018-12-25)</td>
				</tr>
				<tr>
					<td class="no">10</td><td class="nm">feed_loop_yn</td>
					<td class="tx">피드등록 반복실행 여부</td>
				</tr>
				<tr>
					<td class="no">11</td><td class="nm">feed_week</td>
					<td class="tx">한 주에 피드를 실행할 요일[번호로 등록됨] (배열 : -1(매일),0(일),1(월),2(화),3(수),4(목),5(금),6(토))</td>
				</tr>
				<tr class="cancel">
					<td class="no">12</td><td class="nm">feed_hour</td>
					<td class="tx">하루에 피드를 실행할 시간</td>
				</tr>
				<tr class="cancel">
					<td class="no">13</td><td class="nm">feed_status</td>
					<td class="tx">피드실행상태(ifd_status | pending=등록대기,fail=실패,giveup=포기,ok=성공,trash=삭제)</td>
				</tr>
				<tr>
					<td class="no">14</td><td class="nm">feed_content</td>
					<td class="tx">피드의 내용</td>
				</tr>
				<tr>
					<td class="no">15</td><td class="nm">feed_footer</td>
					<td class="tx">피드의 푸터정보</td>
				</tr>
				<tr>
					<td class="no">16</td><td class="nm">feed_file_type</td>
					<td class="tx">피드에 첨부할 파일의 종류(image=이미지, slide=여러장, video=동영상, regram=리그램)</td>
				</tr>
				<tr class="added">
					<td class="no">17</td><td class="nm">feed_file</td>
					<td class="tx">파일경로(배열) / 경로 변경 temp->i (<span style="text-decoration:line-through">http://allstagram/data/temp/파일명</span> > http://allstagram/data/i/파일명) </td>
				</tr>
				<tr>
					<td class="no">18</td><td class="nm">feed_fix_tag</td>
					<td class="tx">피드에 기재할 고정 해시태그</td>
				</tr>
				<tr>
					<td class="no">19</td><td class="nm">feed_random_tag</td>
					<td class="tx">피드에 기재할 랜덤 해시태그</td>
				</tr>
				<tr>
					<td class="no">20</td><td class="nm">feed_random_cnt</td>
					<td class="tx">피드에 기재할 랜덤 해시태그를 설정한 숫자만큼 랜덤으로 추출</td>
				</tr>
				<tr>
					<td class="no">21</td><td class="nm">feed_reg_dt</td>
					<td class="tx">피드작성일</td>
				</tr>
				<tr class="added">
					<td class="no">22</td><td class="nm">feed_reserve_dt</td>
					<td class="tx">피드등록예약일 (등록 실패하면 3회까지 재시도후 다음 피드로 넘어감)</td>
				</tr>
				<tr>
					<td class="no">22</td><td class="nm">참고</td>
					<td class="tx">피드 등록시 마지막 해시태그 #allsta_1526 (뒷부분 숫자 = 올스타 ifd_idx | 인스타 PK 씽크 맞추기 위해서 필요함)</td>
				</tr>
			</tbody>
		</table>
	</section>
	<!--==========================================================  봇 토탈 관련  ============================================================-->
	<section id="sec_bot_total">
		<h6>Bot작업 (http://allstagram.kr/i/bot.php?id=인스타계정아이디)</h6>
		<p>
			<ul style="font-size:0.8em;">
				<li>Bot 작업은 실제 4가지 작업이 포함된다. 
					<div>
						1. 팔로우(bot-follow)+좋아요(bot-like): bot_tags의 태그(들)로 검색된 계정에 팔로우 신청하고 최근 몇 개 게시물을 좋아요해 주고 나온다.
						<br>
						2. 팬좋아요(bot-fanlike): 팔로잉된 내 친구들 최근 게시물 몇 개에 좋아요를 해 주고 나온다.
						<br>
						3. 언팔로우(bot-unfollow): 나는 팔로우 신청했는데 상대편이 신청을 안 받아주면 적당한 주기로 언팔한다. (팔로워 갯수 5000으로 제한되어 있어서..)
					</div>
				</li>
				<li>bot.php 에서 일괄 작업으로 가지고 가도 되고 개별 bot 작업별로 bot_follow, bot_unfollow 처럼 개별로 가지고 가도 된다.</li>
				<li></li>
				<li>결과 전달: http://allstagram.kr/i/bot_log.php?id=인스타계정아이디</li>
			</ul>
		</p>
		<pre style="font-size:0.8em;border:solid 1px #dadada;margin-left:40px;margin-bottom:20px;padding-top:20px;">
		2. 봇 작업 후 결과
		  > 결과 전달: http://allstagram.kr/i/bot_log.php?id=인스타계정아이디
			- 파라미터
			   - resultCode : 코드 ( ok / error )
			   - resultMessage : 메시지
			   - insta_feed_pk : 좋아요한 피드 번호
			   - insta_account_pk : 좋아요한 회원 번호
			   - bot_idx : 봇 고유번호

		</pre>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">service_idx</td>
				<td class="tx">bot_reg_dt</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">service_type</td>
				<td class="tx">서비스구분(bot=봇, like=좋아요., feed=콘텐츠업로드, comment=댓글)</td>
			</tr>
			<tr>
				<td class="no">3</td><td class="nm">service_start_date</td>
				<td class="tx">서비스 시작일(ex : 2018-03-03)</td>
			</tr>
			<tr>
				<td class="no">4</td><td class="nm">service_expire_date</td>
				<td class="tx">서비스 종료일(ex : 2018-03-03)</td>
			</tr>
			<tr class="added">
				<td class="no">5</td><td class="nm">service_status</td>
				<td class="tx">서비스상태<span style="text-decoration:line-through">(on=진행중, off=중지)</span> 변경되었어요.(ok=진행중, stop=중지)</td>
			</tr>
			<tr>
				<td class="no">6</td><td class="nm">insta_id</td>
				<td class="tx">인스타그램 계정</td>
			</tr>
			<tr>
				<td class="no">7</td><td class="nm">insta_password</td>
				<td class="tx">인스타그램 계정의 비밀번호</td>
			</tr>
			<tr>
				<td class="no">8</td><td class="nm">bot_idx</td>
				<td class="tx">Bot서비스 고유번호</td>
			</tr>
			<tr>
				<td class="no">9</td><td class="nm">bot_status</td>
				<td class="tx">Bot서비스 실행상태(ok=성공, fail=실패)</td>
			</tr>
			<tr>
				<td class="no">10</td><td class="nm">bot_nowork_day</td>
				<td class="tx">Bot서비스를 실행하면 안되는 날짜(배열형태 : 2018-01-01, 2018-03-01, 2018-12-25)</td>
			</tr>
			<tr>
				<td class="no">11</td><td class="nm">bot_week</td>
				<td class="tx">한 주에 Bot서비스를 실행할 요일[번호로 등록됨] (배열 : -1(매일),0(일),1(월),2(화),3(수),4(목),5(금),6(토))</td>
			</tr>
			<tr>
				<td class="no">12</td><td class="nm">bot_hour</td>
				<td class="tx">하루에 Bot서비스를 실행할 시간</td>
			</tr>
			<tr>
				<td class="no">13</td><td class="nm">bot_tags</td>
				<td class="tx">계정을 검색하기 위한 키워드 태그(배열형태)</td>
			</tr>
			<tr>
				<td class="no">14</td><td class="nm">bot_tag_cnt</td>
				<td class="tx">태그 배열에서 설정된 숫자만큼 랜덤 추출 (아직은 미확정)</td>
			</tr>
			<tr>
				<td class="no">15</td><td class="nm">bot_reg_dt</td>
				<td class="tx">Bot서비스의 등록일</td>
			</tr>
			<tr>
				<td class="no">16</td><td class="nm">bot_like</td>
				<td class="tx">Bot 좋아요의 정보를 담은 배열 (하단 상세 내용 참조)</td>
			</tr>
			<tr>
				<td class="no">17</td><td class="nm">bot_follow</td>
				<td class="tx">Bot 선팔+좋아요의 정보를 담은 배열 (하단 상세 내용 참조)</td>
			</tr>
			<tr>
				<td class="no">18</td><td class="nm">bot_fanlike</td>
				<td class="tx">Bot 팬좋아요의 정보를 담은 배열 (하단 상세 내용 참조)</td>
			</tr>
			<tr>
				<td class="no">19</td><td class="nm">bot_unfollow</td>
				<td class="tx">Bot 언팔의 정보를 담은 배열 (하단 상세 내용 참조)</td>
			</tr>
			</tbody>
		</table>
	</section>
	
	<!--==========================================================  봇 좋아요 관련  ============================================================-->
	<section id="sec_bot_like">
		<h6>
			Bot작업 상세 (Bot 작업별 세부 기능 분리)<br>
			(Bot-like, Bot-follow, Bot-fanlilke, Bot-unfollow) <br>
			http://allstagram.kr/i/bot_like.php?id=인스타계정아이디<br>
			http://allstagram.kr/i/bot_follow.php?id=인스타계정아이디<br>
			http://allstagram.kr/i/bot_fanlike.php?id=인스타계정아이디<br>
			http://allstagram.kr/i/bot_unfollow.php?id=인스타계정아이디
		</h6>
		<p>
			<ul style="font-size:0.8em;">
				<li>Bot 작업은 실제 4가지 작업이 포함된다. (상기 내용 참조)</li>
				<li>Bot 개별 작업 결과 전달: http://allstagram.kr/i/bot_서비스명_log.php?id=인스타계정아이디</li>
			</ul>
		</p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">service_idx</td>
				<td class="tx">bot_reg_dt</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">service_type</td>
				<td class="tx">서비스구분(bot=봇, like=좋아요., feed=콘텐츠업로드, comment=댓글)</td>
			</tr>
			<tr>
				<td class="no">3</td><td class="nm">service_start_date</td>
				<td class="tx">서비스 시작일(ex : 2018-03-03)</td>
			</tr>
			<tr>
				<td class="no">4</td><td class="nm">service_expire_date</td>
				<td class="tx">서비스 종료일(ex : 2018-03-03)</td>
			</tr>
			<tr class="added">
				<td class="no">5</td><td class="nm">service_status</td>
				<td class="tx">서비스상태<span style="text-decoration:line-through">(on=진행중, off=중지)</span> 변경되었어요.(ok=진행중, stop=중지)</td>
			</tr>
			<tr>
				<td class="no">6</td><td class="nm">insta_id</td>
				<td class="tx">인스타그램 계정</td>
			</tr>
			<tr>
				<td class="no">7</td><td class="nm">insta_password</td>
				<td class="tx">인스타그램 계정의 비밀번호</td>
			</tr>
			<tr>
				<td class="no">8</td><td class="nm">bot_idx</td>
				<td class="tx">Bot서비스 고유번호</td>
			</tr>
			<tr>
				<td class="no">9</td><td class="nm">bot_status</td>
				<td class="tx">Bot서비스 실행상태(ok=성공, fail=실패)</td>
			</tr>
			<tr>
				<td class="no">10</td><td class="nm">bot_nowork_day</td>
				<td class="tx">Bot서비스를 실행하면 안되는 날짜(배열형태 : 2018-01-01, 2018-03-01, 2018-12-25)</td>
			</tr>
			<tr>
				<td class="no">11</td><td class="nm">bot_week</td>
				<td class="tx">한 주에 Bot서비스를 실행할 요일[번호로 등록됨] (배열 : -1(매일),0(일),1(월),2(화),3(수),4(목),5(금),6(토))</td>
			</tr>
			<tr>
				<td class="no">12</td><td class="nm">bot_hour</td>
				<td class="tx">하루에 Bot서비스를 실행할 시간</td>
			</tr>
			<tr>
				<td class="no">13</td><td class="nm">bot_tags</td>
				<td class="tx">계정을 검색하기 위한 키워드 태그(배열형태)</td>
			</tr>
			<tr>
				<td class="no">14</td><td class="nm">bot_tag_cnt</td>
				<td class="tx">태그 배열에서 설정된 숫자만큼 태그를 랜덤으로 추출하기 위함</td>
			</tr>
			<tr>
				<td class="no">15</td><td class="nm">bot_reg_dt</td>
				<td class="tx">Bot서비스의 등록일</td>
			</tr>
			</tbody>
		</table>
	</section>
	<!--==========================================================  좋아요받기관련  ============================================================-->
	<section id="sec_like">
		<h6>좋아요받기 작업 (http://allstagram.kr/i/like.php?id=인스타계정아이디)</h6>
		<p>
			<ul style="font-size:0.8em;">
				<li>좋아요 받기 작업은 해당 계정의 피드들에 약속한 만큼의 좋아요를 단시간에 구현하는 기능이다.</li>
				<li>좋아요 참여대상 계정 리스트: http://allstagram.kr/i/member_like.php</li>
				<li>좋아요 참여대상 구분: allstagram=올스타그램정식회원,intra=엔씨티사원,foreigner=외국인유령계정,buy=구매한계정 등등..</li>
				<li>계정들끼리 서로서로 좋아요를 해 주면서 다녀야 할 것으로 판단됨</li>
				<li></li>
				<li>결과 전달: http://allstagram.kr/i/like_log.php?id=인스타계정아이디</li>
			</ul>
		</p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
				<tr>
					<td class="no">1</td><td class="nm">service_idx</td>
					<td class="tx">서비스고유번호</td>
				</tr>
				<tr>
					<td class="no">2</td><td class="nm">service_type</td>
					<td class="tx">서비스구분(bot=봇, like=좋아요., feed=콘텐츠업로드, comment=댓글)</td>
				</tr>
				<tr>
					<td class="no">3</td><td class="nm">service_start_date</td>
					<td class="tx">서비스 시작일(ex : 2018-03-03)</td>
				</tr>
				<tr>
					<td class="no">4</td><td class="nm">service_expire_date</td>
					<td class="tx">서비스 종료일(ex : 2018-03-03)</td>
				</tr>
				<tr class="added">
					<td class="no">5</td><td class="nm">service_status</td>
					<td class="tx">서비스상태<span style="text-decoration:line-through">(on=진행중, off=중지)</span> 변경되었어요.(ok=진행중, stop=중지)</td>
				</tr>
				<tr>
					<td class="no">6</td><td class="nm">insta_id</td>
					<td class="tx">인스타그램 계정</td>
				</tr>
				<tr>
					<td class="no">7</td><td class="nm">insta_password</td>
					<td class="tx">인스타그램 계정의 비밀번호</td>
				</tr>
				<tr>
					<td class="no">8</td><td class="nm">like_idx</td>
					<td class="tx">좋아요받기서비스 고유번호</td>
				</tr>
				<tr>
					<td class="no">8</td><td class="nm">like_target_cnt</td>
					<td class="tx">좋아요받기 목표수 (500 or 1000)</td>
				</tr>
				<tr>
					<td class="no">9</td><td class="nm">like_status</td>
					<td class="tx">좋아요받기실행상태(ok=성공, fail=실패)</td>
				</tr>
				<tr>
					<td class="no">10</td><td class="nm">like_nowork_day</td>
					<td class="tx">좋아요받기를 실행하면 안되는 날짜(배열형태 : 2018-01-01, 2018-03-01, 2018-12-25)</td>
				</tr>
				<tr>
					<td class="no">11</td><td class="nm">like_week</td>
					<td class="tx">한 주에 좋아요받기를 실행할 요일[번호로 등록됨] (배열 : -1(매일),0(일),1(월),2(화),3(수),4(목),5(금),6(토))</td>
				</tr>
				<tr>
					<td class="no">12</td><td class="nm">like_hour</td>
					<td class="tx">하루에 좋아요받기를 실행할 시간</td>
				</tr>
				<tr>
					<td class="no">13</td><td class="nm">like_reg_dt</td>
					<td class="tx">좋아요받기서비스를 등록한날짜</td>
				</tr>
			</tbody>
		</table>
	</section>
	
	<!--==========================================================  인스타 계정 차단  ============================================================-->
	<section id="sec_setting">
		<h6 class="cancel">인스타 차단 및 해제 (http://allstagram.kr/i/auth_block.php?id=tomasjoa@nate.com)</h6>
		<p>
			<ul style="font-size:0.8em;">
				<li>해당 계정이 차단되면 auth_block.php?id=tomasjoa@nate.com 에 차단되었다고 통보한다.</li>
				<li>차단된 계정이 있는 지 관리자가 확인하고 관리자가 차단해제를 시작하겠다고 상태값을 변경한다. (auth_code.php로 상태값 확인)</li>
				<li>크롤러 서버가 고객 계정에 등록된 메일로 인증요청 메일을 발송한다. </li>
				<li>관리자가 메일을 확인하고 인증코드를 넣어준다.  (auth_code.php로 상태값 확인, insta_auth_code=124658 와 같은 값이 들어간다.)</li>
				<li>크롤러 서버가 인증코드를 받아서 인증을 해제하고 성공하면 auth_reset.php 에 결과값을 보낸다.</li>
				<li>안 되면 계속 반복</li>
				<li>auth_code 값의 상태값이 중요하여 아래 정리한다.</li>
			</ul>
		</p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명(auth_code 관련)</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">insta_status</td>
				<td class="tx">
					block: 차단상태 (진행하다가 차단되었어요.)
					<br>email: 이메일 발송해 주세요.
					<br>ok: 정상진행상태
				</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">insta_auth_code</td>
				<td class="tx">이메일로 받은 인증코드</td>
			</tr>
			</tbody>
		</table>
	</section>
	

	<!--==========================================================  인스타 계정 차단 및 해제  ============================================================-->
	<section id="sec_setting">
		<h6>인스타 차단 및 해제 v2 (http://allstagram.kr/i/auth_result.php?id=tomasjoa@n...)</h6>
		<p>
			<ul style="font-size:0.8em;">
				<li>파일명이 바뀌었어요. auto_block.php => auth_result.php</li>
				<li>해당 계정이 차단되면 auth_result.php?id=tomasjoa@nate.com&status=block 상태값과 함께 차단되었다고 통보한다.</li>
				<li>차단된 계정이 있는 지 관리자가 확인하고 관리자가 차단해제를 시작하겠다고 상태값을 변경한다. (auth_code.php로 상태값 확인)</li>
				<li>크롤러 서버가 고객 계정에 등록된 메일로 인증요청 메일을 발송한다. </li>
				<li>관리자가 메일을 확인하고 인증코드를 넣어준다.  (auth_code.php로 상태값 확인, insta_auth_code=124658 와 같은 값이 들어간다.)</li>
				<li>크롤러 서버가 인증코드를 받아서 인증을 해제하고 성공하면 auth_result.php?id=tomasjoa@nate.com&status=ok 결과값을 보낸다.</li>
				<li>안 되면 계속 반복</li>
				<li>auth_result 상태값이 중요하여 아래 정리한다.</li>
			</ul>
		</p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명(auth_code 관련)</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">status 값들</td>
				<td class="tx">
					block: 차단상태 (진행하다가 차단되었어요.)
					<br>ok: 정상
					<br>wait: 재로그인 대기
					<br>sentry: 완전블락(로그인불가상태)
					<br>email: 이메일 발송해 주세요~ 상태
					<br>sms: 문자로 인증해라 (이메일 인증은 더 이상 안 되는 계정)
					<br>pwd_wrong: 비밀번호 에러
				</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">insta_auth_code</td>
				<td class="tx">이메일로 받은 인증코드</td>
			</tr>
			</tbody>
		</table>
	</section>
	

	<!--==========================================================  유령 계정 차단  ============================================================-->
	<section id="sec_setting">
		<h6>좋아요 진행 계정 차단 및 해제 (http://allstagram.kr/i/like_auth_block.php?id=tomasjoa@nate.com)</h6>
		<p>
			<ul style="font-size:0.8em;">
				<li>해당 계정이 차단되면 like_auth_block.php?id=tomasjoa@nate.com 에 차단되었다고 통보한다.</li>
				<li>차단된 계정이 있는 지 관리자가 확인하고 관리자가 차단해제를 시작하겠다고 상태값을 변경한다. (like_auth_code.php로 상태값 확인)</li>
				<li>크롤러 서버가 고객 계정에 등록된 메일로 인증요청 메일을 발송한다. </li>
				<li>관리자가 메일을 확인하고 인증코드를 넣어준다.  (like_auth_code.php로 상태값 확인, insta_auth_code=124658 와 같은 값이 들어간다.)</li>
				<li>크롤러 서버가 인증코드를 받아서 인증을 해제하고 성공하면 like_auth_reset.php 에 결과값을 보낸다.</li>
				<li>안 되면 계속 반복</li>
				<li>auth_code 값의 상태값은 상기 인스타 차단 및 해제와 같다.</li>
			</ul>
		</p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명(auth_code 관련)</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">insta_status</td>
				<td class="tx">
					block: 차단상태 (진행하다가 차단되었어요.)
					<br>email: 이메일 발송해 주세요.
					<br>ok: 정상진행상태
				</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">insta_auth_code</td>
				<td class="tx">이메일로 받은 인증코드</td>
			</tr>
			</tbody>
		</table>
	</section>
	

	<!--==========================================================  공통설정 관련  ============================================================-->
	<section id="sec_setting">
		<h6>환경변수설정(글로벌) (http://allstagram.kr/i/setting.php)</h6>
		<p></p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">follow_cnt</td>
				<td class="tx">시간당 선팔 횟수, 한 시간에 선팔을 몇 번 까지</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">like_cnt</td>
				<td class="tx">시간당 라이크 횟수, 한 시간에 좋아요를 몇 번 까지</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">fanlike_cnt</td>
				<td class="tx">팬좋아요 시간당 처리횟수 (디폴트=20)</td>
			</tr>
			<tr>
				<td class="no">3</td><td class="nm">execute_feed_proccess</td>
				<td class="tx">피드 처리 실행 여부  = true</td>
			</tr>
			<tr>
				<td class="no">4</td><td class="nm">execute_bot_proccess</td>
				<td class="tx"># 봇 작업 처리 실행 여부 EXECUTE_BOT_PROCCESS = false</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">download_path</td>
				<td class="tx"># 올스타에서 이미지 다운 받는 경로 DOWNLOAD_PATH = </td>
			</tr>
			<tr>
				<td class="no">6</td><td class="nm">worker_loop_delay_time</td>
				<td class="tx"># 워커 주기 (마이크로) WORKER_LOOP_DELAY_TIME = 3600000</td>
			</tr>
			<tr>
				<td class="no">7</td><td class="nm">auth_loop_delay</td>
				<td class="tx"># 인증시 반복 주기 (마이크로) AUTH_LOOP_DELAY = 30000</td>
			</tr>
			<tr>
				<td class="no">8</td><td class="nm">auth_code_input_timeout</td>
				<td class="tx"># 인증 타임아웃 시간 (초) AUTH_CODE_INPUT_TIMEOUT = 180</td>
			</tr>
			<tr>
				<td class="no">9</td><td class="nm">bot_process_type</td>
				<td class="tx">봇 처리 타입 ( 1 : 전체 태그 검색한 결과 합쳐서 순서대로 처리, 2 : 태그별로 검색해서 나온 결과대로 처리 ) BOT_PROCESS_TYPE = 2</td>
			</tr>
			<tr>
				<td class="no">10</td><td class="nm">unfollowing_day</td>
				<td class="tx">언팔로우 대기 시간 - 팔로우 후에 설정된 일 이후에 언팔로우 진행 UNFOLLOWING_DAY = 7</td>
			</tr>
			<tr>
				<td class="no">11</td><td class="nm">auth_after_login_day</td>
				<td class="tx">인증대기일 - 인증해제 후 대기 후에 처리 AUTH_AFTER_LOGIN_DAY = 2</td>
			</tr>
			<tr>
				<td class="no">12</td><td class="nm">feed_upload_assets</td>
				<td class="tx"># 피드 업로드시 서버와 이미지 갯수 체크 후 올릴것인지 ( true : 체크 해서 안맞으면 작업 안함, false : 실패한 이미지가 있더라도 업로드 시도 함 ) FEED_UPLOAD_ASSETS = true</td>
			</tr>
			<tr>
				<td class="no">13</td><td class="nm">unfollowing_day</td>
				<td class="tx">선팔 신청 후 +day 까지 맛팔이 없으면 언팔 (디플트=7)</td>
			</tr>
			<tr>
				<td class="no">14</td><td class="nm">unfollowing_reset_day</td>
				<td class="tx">한번 언팔한 사람들에게 다시 선팔 신청하는 기간 (디폴트=30), 한번 언팔하면 영원히 언팔은 아님</td>
			</tr>
			<tr>
				<td class="no">15</td><td class="nm">follow_block_delay</td>
				<td class="tx">팔로우작업 에러메시지 발생시 휴식시간(단위:분)</td>
			</tr>
			<tr>
				<td class="no">16</td><td class="nm">like_block_delay</td>
				<td class="tx">좋아요작업 에러메시지 발생시 휴식시간(단위:분)</td>
			</tr>
			</tbody>
		</table>
	</section>
	
	<!--==========================================================  공통설정 관련  ============================================================-->
	<section id="sec_setting">
		<h6>회원별 환경변수설정 (http://allstagram.kr/i/setting.php?id=websiteman@naver.com)</h6>
		<p></p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">1</td><td class="nm">execute_feed_proccess</td>
				<td class="tx">피드 처리 실행 여부 = true</td>
			</tr>
			<tr>
				<td class="no">2</td><td class="nm">execute_bot_proccess</td>
				<td class="tx">봇 작업 처리 실행 여부</td>
			</tr>
			<tr>
				<td class="no">3</td><td class="nm">follow_cnt</td>
				<td class="tx">시간당 선팔 횟수, 한 시간에 선팔을 몇 번 까지</td>
			</tr>
			<tr>
				<td class="no">4</td><td class="nm">fanlike_cnt</td>
				<td class="tx">팬좋아요 시간당 처리횟수 (디폴트=20)</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">like_cnt</td>
				<td class="tx">시간당 라이크 횟수, 한 시간에 좋아요를 몇 번 까지</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">auth_after_login_day</td>
				<td class="tx">인증대기일</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">execute_like_proccess</td>
				<td class="tx">검색 좋아요 작업 여부 = true</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">execute_fanlike_proccess</td>
				<td class="tx">팬좋아요 작업 여부 true|false</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">fanlike_random_cnt</td>
				<td class="tx">팬좋아요(팔로워) 리스트중에서 랜덤으로 설정 갯수 만큼만 추출해서 최근 피드에 좋아요 한다. ex.3000명 중에서 20명!</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">execute_ghost_like_proccess</td>
				<td class="tx">유령(상부상조) 좋아요 작업 여부 = true</td>
			</tr>
			<tr>
				<td class="no">5</td><td class="nm">execute_ghost_fanlike_proccess</td>
				<td class="tx">유령(상부상조) 팔로우 작업 여부 = true</td>
			</tr>
			</tbody>
		</table>
	</section>
	


	<!--==========================================================  여분 관련  ============================================================-->
	<section id="sec_etc">
		<h6>etc 페이지 (http://allstagram.kr/i/etc.php)</h6>
		<p></p>
		<table class="table table-hover table-striped table-sm">
			<thead><tr><th>번호</th><th>변수명</th><th>설명</th></tr></thead>
			<tbody>
			<tr>
				<td class="no">번호</td><td class="nm">변수명</td>
				<td class="tx">설명</td>
			</tr>
			</tbody>
		</table>
	</section>
	

	<br><br><br>
	<!--==========================================================  체크포인트 몇 가지  ================================================-->
	<section id="sec_member">
		<h6>체크 포인트 몇 가지를 정리합니다.</h6>
		<!-- 간단설명 -->
		<ul style="font-size:0.8em;border:solid 1px #dadada;margin:30px;padding-top:10px;padding-bottom:10px;">
			<li>잊어버릴까봐.. 하하!!</li>
		</ul>
		<!-- 변수 설명 -->
		<p>
			<ol>
				<li>봇 작업시 신고 회원에 대한 중복 작업 중지 <br>
					자꾸 나한테 친구신청하지 마.. 죽는다~~ 이랬는데.. 자동작업하다 보니까 오늘도, 내일도 죽도록 팬신청.. 이거 안 되요.
				</li>
				<li>작업 전 초기 팬은 그대로 유지<br>
					원래 팬이 100명 있었는데 작업하고 나서 보니 실제 내 친구들이 싹 다 사라졌다?? 복구해 조라. 이 문제에 대한 대책
				</li>
				<li>좋아요 작업을 위한...<br>
					유령 계정을 무작위 생성할 수 있을까?
				</li>
			</ol>
		</p>
		<hr>
		<!-- 장과장이 준 메시지 -->
		<p style="font-size:0.8em;border:solid 1px #dadada;margin:30px;padding:20px;">
		장 과장님, 마련해 주셔요.
		</p>
	</section>

	<br><br><br>
	<!--=======  장과장 메시지  ================================================-->
	<section id="sec_member">
		<h6>장과장이 준 정보들</h6>
		<!-- 간단설명 -->
		<!-- 장과장이 준 메시지 -->
		클로링 서버 컨피그 정보 -  <br>
		<ul style="font-size:0.8em;margin:10px;">
			<li># 개발모드 여부
			isDev = false
            </li>
			<li># 단일 실행 체크 여부
			SINGLE_MODE = false
            </li>
			<li># 서버번호
			SERVER_NUMBER = 1
            </li>

		</ul>
	</section>


<br><br><br><br><br><br>	
</div>
<div id="ml_footer">

</div>
</body>
</html>