<?php
include_once('./_common.php');
// 인스타 로그인후 인스타쪽 피드 배열을 받아서 올스타에 업데이트하는 폼을 임시로 구현한 페이지입니다.
// 실제로는 member_log.php가 크롤링 서버에서 정보를 받아서 저장합니다.
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..
?>
<style>
    #hd_login_msg {display:none;}
</style>

<form id="form01" action="./form2.php">

Token(암호코드)
<table>
	<tr><td>토큰값</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<hr>
1번
<table>
	<tr><td>업체 idx1</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>MMS idx1</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>교대명1</td><td><input type="text" name="shf_name[]" value="주간1팀"></td></tr>
	<tr><td>교대번호1</td><td><input type="text" name="shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>교대시작시간1</td><td><input type="text" name="shf_start_time[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>교대종료시간1</td><td><input type="text" name="shf_end_time[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>목표수량1</td><td><input type="text" name="shf_target[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>시작일시1</td><td><input type="text" name="shf_start_dt[]" value="<?=date("Y:m:d H:i:s",time())?>"></td></tr>
</table>

<hr>
2번
<table>
	<tr><td>업체 idx2</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>MMS idx2</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>교대명2</td><td><input type="text" name="shf_name[]" value="주간2팀"></td></tr>
	<tr><td>교대번호2</td><td><input type="text" name="shf_no[]" value="<?=rand(1,3)?>"></td></tr>
	<tr><td>교대시작시간2</td><td><input type="text" name="shf_start_time[]" value="<?=date("H:i:00",time()-rand(3600*8,3600*16))?>"></td></tr>
	<tr><td>교대종료시간2</td><td><input type="text" name="shf_end_time[]" value="<?=date("H:i:00",time()-rand(3600*8,3600*16))?>"></td></tr>
	<tr><td>목표수량2</td><td><input type="text" name="shf_target[]" value="<?=rand(7000,9000)?>"></td></tr>
	<tr><td>시작일시2</td><td><input type="text" name="shf_start_dt[]" value="<?=date("Y:m:d H:i:s",time()-rand(1000,86400*2))?>"></td></tr>
</table>

<hr>
3번
<table>
	<tr><td>업체 idx3</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>MMS idx3</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>교대명3</td><td><input type="text" name="shf_name[]" value="야간3팀"></td></tr>
	<tr><td>교대번호3</td><td><input type="text" name="shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>교대시작시간3</td><td><input type="text" name="shf_start_time[]" value="<?=date("H:i:00",time()-rand(3600*16,3600*23))?>"></td></tr>
	<tr><td>교대종료시간3</td><td><input type="text" name="shf_end_time[]" value="<?=date("H:i:00",time()-rand(3600*16,3600*23))?>"></td></tr>
	<tr><td>목표수량3</td><td><input type="text" name="shf_target[]" value="<?=rand(500,700)?>"></td></tr>
	<tr><td>시작일시3</td><td><input type="text" name="shf_start_dt[]" value="<?=date("Y:m:d H:i:s",time()+rand(2000,86400*3))?>"></td></tr>
</table>


<hr>
<button type="submit">확인</button>
</form>


