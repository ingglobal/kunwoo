<?php
include_once('./_common.php');
// 인스타 로그인후 인스타쪽 피드 배열을 받아서 올스타에 업데이트하는 폼을 임시로 구현한 페이지입니다.
// 실제로는 member_log.php가 크롤링 서버에서 정보를 받아서 저장합니다.
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..

$group_array = array('mea','mea');
?>
<style>
    #hd_login_msg {display:none;}
    button {background:#37a7ff;padding:10px 20px;font-size:1.5em;border-radius:4px;}
</style>

<form id="form01" action="./form2.php">

<h1>교대 및 목표수량 동기화</h1>
<h2>실제 URL: <a href="http://bogwang.epcs.co.kr/device/shift/list/" target="_blank">http://bogwang.epcs.co.kr/device/shift/list/</a></h2>
Token(암호코드)
<table>
	<tr><td>토큰값</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<hr>
<table>
	<tr><td>MMS idx</td><td><input type="text" name="mms_idx" value="<?=rand(1,3)?>"></td></tr>
</table>

<hr>
<button type="submit">확인</button>
</form>


