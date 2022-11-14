<?php
include_once('./_common.php');
// 인스타 로그인후 인스타쪽 피드 배열을 받아서 올스타에 업데이트하는 폼을 임시로 구현한 페이지입니다.
// 실제로는 member_log.php가 크롤링 서버에서 정보를 받아서 저장합니다.
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..

$group_array = array('run','run');
?>
<style>
    #hd_login_msg {display:none;}
    button {background:#37a7ff;padding:10px 20px;font-size:1.5em;border-radius:4px;}
</style>

<form id="form01" action="./form2.php">

<h1>가동데이터 dta_group=run</h1>
<h2>그룹(dta_group): run=가동시간</h2>
Token(암호코드)
<table>
	<tr><td>토큰값</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<hr>
1번
<table>
	<tr><td>업체 idx1</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>IMP idx1</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx1</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>교대번호1</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>총교대수1</td><td><input type="text" name="dta_shf_max[]" value="<?=rand(2,3)?>"></td></tr>
	<tr><td>기종1</td><td><input type="text" name="dta_mmi_no[]" value="<?=rand(0,1)?>"></td></tr>
	<tr><td>데이터그룹1</td><td><input type="text" name="dta_group[]" value="<?=$group_array[rand(0,sizeof($group_array)-1)]?>"></td></tr>
	<tr><td>날짜1</td><td><input type="text" name="dta_date[]" value="<?=date("y.m.d",time())?>"></td></tr>
	<tr><td>시간1</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값1</td><td><input type="text" name="dta_value[]" value="<?=rand(10,55)?>"> 초</td></tr>
	<tr><td>상태1</td><td><input type="text" name="mms_status[]" value="<?=rand(0,2)?>"> 0=알수없음,전원OFF / 1=수동 / 2=자동 / 3=이상</td></tr>
</table>

<hr>
2번
<table>
	<tr><td>업체 idx2</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>IMP idx2</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx2</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>교대번호2</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,3)?>"></td></tr>
	<tr><td>총교대수2</td><td><input type="text" name="dta_shf_max[]" value="3"></td></tr>
	<tr><td>기종2</td><td><input type="text" name="dta_mmi_no[]" value="<?=rand(0,1)?>"></td></tr>
	<tr><td>데이터그룹2</td><td><input type="text" name="dta_group[]" value="<?=$group_array[rand(0,sizeof($group_array)-1)]?>"></td></tr>
	<tr><td>날짜2</td><td><input type="text" name="dta_date[]" value="<?=date("y.m.d",time()-86400*0.1)?>"></td></tr>
	<tr><td>시간2</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값2</td><td><input type="text" name="dta_value[]" value="<?=rand(10,40)?>"> 초</td></tr>
	<tr><td>상태2</td><td><input type="text" name="mms_status[]" value="<?=rand(0,2)?>"></td></tr>
</table>

<hr>
3번
<table>
	<tr><td>업체 idx3</td><td><input type="text" name="com_idx[]" value="<?=rand(1,68)?>"></td></tr>
	<tr><td>IMP idx3</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx3</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>교대번호3</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>총교대수3</td><td><input type="text" name="dta_shf_max[]" value="<?=rand(2,3)?>"></td></tr>
	<tr><td>기종3</td><td><input type="text" name="dta_mmi_no[]" value="<?=rand(0,1)?>"></td></tr>
	<tr><td>데이터그룹3</td><td><input type="text" name="dta_group[]" value="<?=$group_array[rand(0,sizeof($group_array)-1)]?>"></td></tr>
	<tr><td>날짜3</td><td><input type="text" name="dta_date[]" value="<?=date("y.m.d",time()-86400*0.2)?>"></td></tr>
	<tr><td>시간3</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값3</td><td><input type="text" name="dta_value[]" value="<?=rand(10,60)?>"> 초</td></tr>
	<tr><td>상태3</td><td><input type="text" name="mms_status[]" value="<?=rand(0,2)?>"></td></tr>
</table>

<hr>
<button type="submit">확인</button>
</form>


