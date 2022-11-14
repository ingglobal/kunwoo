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
	<tr><td>IMP idx1</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx1</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,10)?>"></td></tr>
	<tr><td>코드값1</td><td><input type="text" name="dta_code[]" value="M0100"></td></tr>
	<tr><td>교대번호1</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>총교대수1</td><td><input type="text" name="dta_shf_max[]" value="<?=rand(2,3)?>"></td></tr>
	<tr><td>구분1</td><td><input type="text" name="dta_group[]" value="err"></td></tr>
	<tr><td>타입1</td><td><input type="text" name="dta_type[]" value="<?=rand(1,9)?>"></td></tr>
	<tr><td>측정번호1</td><td><input type="text" name="dta_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>장치명1</td><td><input type="text" name="dta_name[]" value="서보모터"></td></tr>
	<tr><td>날짜1</td><td><input type="text" name="dta_date[]" value="<?=date("Y.m.d",time())?>"></td></tr>
	<tr><td>시간1</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값1</td><td><input type="text" name="dta_value[]" value="<?=rand(1,50)?>"></td></tr>
	<tr><td>메시지1</td><td><input type="text" name="dta_message[]" value="에러코드입니다."></td></tr>
</table>

<hr>
2번
<table>
	<tr><td>업체 idx2</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>IMP idx2</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx2</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,10)?>"></td></tr>
	<tr><td>코드값2</td><td><input type="text" name="dta_code[]" value="M0101"></td></tr>
	<tr><td>교대번호2</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,3)?>"></td></tr>
	<tr><td>총교대수2</td><td><input type="text" name="dta_shf_max[]" value="3"></td></tr>
	<tr><td>구분2</td><td><input type="text" name="dta_group[]" value="product"></td></tr>
	<tr><td>타입2</td><td><input type="text" name="dta_type[]" value="<?=rand(1,9)?>"></td></tr>
	<tr><td>측정번호2</td><td><input type="text" name="dta_no[]" value="<?=rand(1,3)?>"></td></tr>
	<tr><td>장치명2</td><td><input type="text" name="dta_name[]" value="실린더"></td></tr>
	<tr><td>날짜2</td><td><input type="text" name="dta_date[]" value="<?=date("Y.m.d",time()-86400)?>"></td></tr>
	<tr><td>시간2</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값2</td><td><input type="text" name="dta_value[]" value="<?=rand(1,20)?>"></td></tr>
	<tr><td>메시지2</td><td><input type="text" name="dta_message[]" value="생산량입니다."></td></tr>
</table>

<hr>
3번
<table>
	<tr><td>업체 idx3</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>IMP idx3</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx3</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,10)?>"></td></tr>
	<tr><td>코드값3</td><td><input type="text" name="dta_code[]" value="M0102"></td></tr>
	<tr><td>교대번호3</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>총교대수3</td><td><input type="text" name="dta_shf_max[]" value="<?=rand(2,3)?>"></td></tr>
	<tr><td>구분3</td><td><input type="text" name="dta_group[]" value="pre"></td></tr>
	<tr><td>타입3</td><td><input type="text" name="dta_type[]" value="<?=rand(1,9)?>"></td></tr>
	<tr><td>측정번호3</td><td><input type="text" name="dta_no[]" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>장치명3</td><td><input type="text" name="dta_name[]" value="모터"></td></tr>
	<tr><td>날짜3</td><td><input type="text" name="dta_date[]" value="<?=date("Y.m.d",time()-86400*2)?>"></td></tr>
	<tr><td>시간3</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값3</td><td><input type="text" name="dta_value[]" value="<?=rand(1,600)?>"></td></tr>
	<tr><td>메시지3</td><td><input type="text" name="dta_message[]" value="예시코드입니다."></td></tr>
</table>

<hr>
<button type="submit">확인</button>
</form>


