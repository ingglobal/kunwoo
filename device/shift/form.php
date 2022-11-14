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
	<tr><td>1교대시작시간</td><td><input type="text" name="shf_range_1_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>1교대종료시간</td><td><input type="text" name="shf_range_1_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>1교대목표</td><td><input type="text" name="shf_target_1[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>2교대시작시간</td><td><input type="text" name="shf_range_2_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>2교대종료시간</td><td><input type="text" name="shf_range_2_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>2교대목표</td><td><input type="text" name="shf_target_2[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>3교대시작시간</td><td><input type="text" name="shf_range_3_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>3교대종료시간</td><td><input type="text" name="shf_range_3_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>3교대목표</td><td><input type="text" name="shf_target_3[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>시작일시</td><td><input type="text" name="shf_start_dt[]" value="<?=date("Y:m:d H:i:s",time())?>"></td></tr>
	<tr><td>종료일시</td><td><input type="text" name="shf_end_dt[]" value="<?=date("Y:m:d H:i:s",time()+86400*5)?>"></td></tr>
</table>

<hr>
2번
<table>
	<tr><td>업체 idx1</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>MMS idx1</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>1교대시작시간</td><td><input type="text" name="shf_range_1_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>1교대종료시간</td><td><input type="text" name="shf_range_1_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>1교대목표</td><td><input type="text" name="shf_target_1[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>2교대시작시간</td><td><input type="text" name="shf_range_2_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>2교대종료시간</td><td><input type="text" name="shf_range_2_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>2교대목표</td><td><input type="text" name="shf_target_2[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>3교대시작시간</td><td><input type="text" name="shf_range_3_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>3교대종료시간</td><td><input type="text" name="shf_range_3_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>3교대목표</td><td><input type="text" name="shf_target_3[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>시작일시</td><td><input type="text" name="shf_start_dt[]" value="<?=date("Y:m:d H:i:s",time())?>"></td></tr>
	<tr><td>종료일시</td><td><input type="text" name="shf_end_dt[]" value="<?=date("Y:m:d H:i:s",time()+86400*5)?>"></td></tr>
</table>

<hr>
3번
<table>
	<tr><td>업체 idx1</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>MMS idx1</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>1교대시작시간</td><td><input type="text" name="shf_range_1_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>1교대종료시간</td><td><input type="text" name="shf_range_1_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>1교대목표</td><td><input type="text" name="shf_target_1[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>2교대시작시간</td><td><input type="text" name="shf_range_2_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>2교대종료시간</td><td><input type="text" name="shf_range_2_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>2교대목표</td><td><input type="text" name="shf_target_2[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>3교대시작시간</td><td><input type="text" name="shf_range_3_start[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>3교대종료시간</td><td><input type="text" name="shf_range_3_end[]" value="<?=date("H:i:00",time()-rand(0,3600*8))?>"></td></tr>
	<tr><td>3교대목표</td><td><input type="text" name="shf_target_3[]" value="<?=rand(5000,10000)?>"></td></tr>
	<tr><td>시작일시</td><td><input type="text" name="shf_start_dt[]" value="<?=date("Y:m:d H:i:s",time())?>"></td></tr>
	<tr><td>종료일시</td><td><input type="text" name="shf_end_dt[]" value="<?=date("Y:m:d H:i:s",time()+86400*5)?>"></td></tr>
</table>


<hr>
<button type="submit">확인</button>
</form>


