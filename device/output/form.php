<?php
include_once('./_common.php');
// 파일명이 실제로는 member_feed_log.php가 더 적합할 수도..

$group_array = array('product','product');
$defect_array = array(0,0,0,0,0,0,0,1,0,0,0,1);
$defect_type_array = array(1,1,1,2,2,3,3,3,4,4,4,5);
$message_array = array('스크레치불량','형상불량','치수불량','스크레치불량','형상불량','치수불량');
?>
<style>
    #hd_login_msg {display:none;}
    button {background:#37a7ff;padding:10px 20px;font-size:1.5em;border-radius:4px;}
</style>

<form id="form01" action="./form2.php">

<h1>생산데이터 dta_group=product</h1>
<h2>그룹(dta_group): product=생산</h2>
Token(암호코드)
<table>
	<tr><td>토큰값</td><td><input type="text" name="token" value="1099de5drf09"></td></tr>
</table>

<hr>
1번
<table>
	<tr><td>업체 idx1</td><td><input type="text" name="com_idx[]" value="<?=rand(1,8)?>"></td></tr>
	<tr><td>IMP idx1</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx1</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>교대번호1</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>총교대수1</td><td><input type="text" name="dta_shf_max[]" value="<?=rand(2,3)?>"></td></tr>
	<tr><td>기종1</td><td><input type="text" name="dta_mmi_no[]" value="<?=rand(0,9)?>"></td></tr>
 	<tr><td>데이터그룹1</td><td><input type="text" name="dta_group[]" value="<?=$group_array[rand(0,sizeof($group_array)-1)]?>"> (product=IMP자동입력, manual=현장입력)</td></tr>
	<tr><td>불량여부1</td><td><input type="text" name="dta_defect[]" value="<?=$defect_array[rand(0,sizeof($defect_array)-1)]?>"> (1 이면 불량)</td></tr>
	<tr><td>불량타입1</td><td><input type="text" name="dta_defect_type[]" value="<?=$defect_type_array[rand(0,sizeof($defect_type_array)-1)]?>"> (불량타입, 내부적으로 정해야 함)</td></tr>
	<tr><td>날짜1</td><td><input type="text" name="dta_date[]" value="<?=date("y.m.d",time())?>"></td></tr>
	<tr><td>시간1</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값1</td><td><input type="text" name="dta_value[]" value="<?=rand(1,3)?>"></td></tr>
	<tr><td>메시지1</td><td><input type="text" name="dta_message[]" value="<?=$message_array[rand(0,sizeof($message_array)-1)]?>"></td></tr>
	<tr><td style="color:red;">통계날짜1</td><td><input type="text" name="dta_date2[]" value="<?=date("y.m.d",time()-86400)?>"> (어제 날짜가 될 수 있음)</td></tr>
</table>

<hr>
2번
<table>
	<tr><td>업체 idx2</td><td><input type="text" name="com_idx[]" value="<?=rand(1,8)?>"></td></tr>
	<tr><td>IMP idx2</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx2</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>교대번호2</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,3)?>"></td></tr>
	<tr><td>총교대수2</td><td><input type="text" name="dta_shf_max[]" value="3"></td></tr>
	<tr><td>기종2</td><td><input type="text" name="dta_mmi_no[]" value="<?=rand(1,5)?>"></td></tr>
	<tr><td>데이터그룹2</td><td><input type="text" name="dta_group[]" value="<?=$group_array[rand(0,sizeof($group_array)-1)]?>"></td></tr>
	<tr><td>불량여부2</td><td><input type="text" name="dta_defect[]" value="<?=$defect_array[rand(0,sizeof($defect_array)-1)]?>"></td></tr>
	<tr><td>불량타입2</td><td><input type="text" name="dta_defect_type[]" value="<?=$defect_type_array[rand(0,sizeof($defect_type_array)-1)]?>"></td></tr>
	<tr><td>날짜2</td><td><input type="text" name="dta_date[]" value="<?=date("y.m.d",time()-86400)?>"></td></tr>
	<tr><td>시간2</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값2</td><td><input type="text" name="dta_value[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>메시지2</td><td><input type="text" name="dta_message[]" value="<?=$message_array[rand(0,sizeof($message_array)-1)]?>"></td></tr>
	<tr><td style="color:red;">통계날짜2</td><td><input type="text" name="dta_date2[]" value="<?=date("y.m.d",time()-86400*1.8)?>"> (어제 날짜가 될 수 있음)</td></tr>
</table>

<hr>
3번
<table>
	<tr><td>업체 idx3</td><td><input type="text" name="com_idx[]" value="<?=rand(1,8)?>"></td></tr>
	<tr><td>IMP idx3</td><td><input type="text" name="imp_idx[]" value="<?=rand(1,16)?>"></td></tr>
	<tr><td>MMS idx3</td><td><input type="text" name="mms_idx[]" value="<?=rand(1,4)?>"></td></tr>
	<tr><td>교대번호3</td><td><input type="text" name="dta_shf_no[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>총교대수3</td><td><input type="text" name="dta_shf_max[]" value="<?=rand(2,3)?>"></td></tr>
	<tr><td>기종3</td><td><input type="text" name="dta_mmi_no[]" value="<?=rand(1,5)?>"></td></tr>
	<tr><td>불량여부3</td><td><input type="text" name="dta_defect[]" value="<?=$defect_array[rand(0,sizeof($defect_array)-1)]?>"></td></tr>
	<tr><td>불량타입3</td><td><input type="text" name="dta_defect_type[]" value="<?=$defect_type_array[rand(0,sizeof($defect_type_array)-1)]?>"></td></tr>
	<tr><td>데이터그룹3</td><td><input type="text" name="dta_group[]" value="<?=$group_array[rand(0,sizeof($group_array)-1)]?>"></td></tr>
	<tr><td>날짜3</td><td><input type="text" name="dta_date[]" value="<?=date("y.m.d",time()-86400*2)?>"></td></tr>
	<tr><td>시간3</td><td><input type="text" name="dta_time[]" value="<?=date("H:i:s",time()-rand(0,86400))?>"></td></tr>
	<tr><td>값3</td><td><input type="text" name="dta_value[]" value="<?=rand(1,2)?>"></td></tr>
	<tr><td>메시지3</td><td><input type="text" name="dta_message[]" value="<?=$message_array[rand(0,sizeof($message_array)-1)]?>"></td></tr>
	<tr><td style="color:red;">통계날짜3</td><td><input type="text" name="dta_date2[]" value="<?=date("y.m.d",time()-86400*2.8)?>"> (어제 날짜가 될 수 있음)</td></tr>
</table>

<hr>
<button type="submit">확인</button>
</form>


