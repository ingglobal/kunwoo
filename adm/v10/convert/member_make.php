<?
// 공통파일 추가
include_once("./_common.php");

//-- 화면 표시 
$countgap = 10; // 몇건씩 보낼지 설정
$maxscreen = 40; // 몇건씩 화면에 보여줄건지?
$sleepsec = 200;  // 천분의 몇초간 쉴지 설정


//-- 설정값
$pg_array = array('offline','kcp','lgu','paypal','ksnet','etc');
$status_array = array('pending','ok','ok','ok','cancel');
$ampm_array = array('am','pm');
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>자료 입력</title>
</head>
<body>
<span style='font-size:9pt;'>
	<p>입력시작 ...<p><font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전에는 중간에 중지하지 마세요.<p>
</span>
<span id="cont"></span>


<?
// 회원 100명 생성
for($i=120012;$i<130000;$i++) {
	// 데모 테스트용은 6개만 보여주세요.
//	if($i>120011)
//		break;
//	echo "<br><br>";

	//-- 회원아이디 설정
	$mb_id[$i] = 'test'.$i;
	$mb_name[$i] = '테스트'.$i;
	$mb_nick[$i] = '닉네임'.$i;
	$mb_email[$i] = 'test'.$i.'@test.com';
	$mb_hp[$i] = '010-'.rand(1111,9999).'-'.rand(1111,9999);
	
	if($i % 10 == 0){
		if($i > 50){
			$mb_level[$i] = '4';
		}
		else{
			$mb_level[$i] = '6';
		}
	}
	else{
		$mb_level[$i] = '2';
	}
	$mb_level[$i] = '6';
	if($mb_level[$i] == '6') {
		// 조직 랜덤 추출 (2차 카테고리만)
		$trm1 = sql_fetch(" SELECT * FROM jt_term WHERE trm_taxonomy = 'department' AND trm_idx_parent <> 0 ORDER BY RAND() LIMIT 1 ");

		// term_relation 입력
		$sql2 = " INSERT INTO jt_term_relation SET	".
			"	trm_idx	= '".$trm1['trm_idx']."'			".
			"	, tmr_db_table = 'member'			".
			"	, tmr_db_key = 'department'			".
			"	, tmr_db_id = '".$mb_id[$i]."'		".
			"	, tmr_sort = '1'							".
			"	, tmr_reg_dt = now()					";
		sql_query($sql2,1);	//<===============================
		//echo $sql1.'<br><br>';
		
	}
	
	$mb_zip1[$i] = '122';
	$mb_zip2[$i] = '35';
	$mb_addr1[$i] = '경기 남양주시 경춘로 883-36';
	$mb_addr2[$i] = '3번지';
	$mb_addr3[$i] = '(금곡동, 마을공동회관)';
	$mb_addr_jibeon[$i] = 'R';
	$mb_login_ip[$i] = '106.245.225.250';
	$mb_ip[$i] = '106.245.225.250';
	$mb_email_certify[$i] = date("Y-m-d H:i:s");
	$mb_mailling[$i] = '1';
	$mb_sms[$i] = '1';
	$mb_open[$i] = '1';

	//-- 회원정보 입력 --//
	$sql1 = " INSERT INTO g5_member SET											".
			"	mb_id	= '".$mb_id[$i]."'										".
			"	, mb_password = '*00A51F3F48415C7D4E8908980D443C29C69B60C9'		".
			"	, mb_name = '".$mb_name[$i]."'									".
			"	, mb_nick = '".$mb_nick[$i]."'									".
			"	, mb_nick_date = now()											".
			"	, mb_email = '".$mb_email[$i]."'								".
			"	, mb_hp = '".$mb_hp[$i]."'										".
			"	, mb_level = '".$mb_level[$i]."'								".
			"	, mb_zip1 = '".$mb_zip1[$i]."'									".
			"	, mb_zip2 = '".$mb_zip2[$i]."'									".
			"	, mb_addr1 = '".$mb_addr1[$i]."'								".
			"	, mb_addr2 = '".$mb_addr2[$i]."'								".
			"	, mb_addr3 = '".$mb_addr3[$i]."'								".
			"	, mb_addr_jibeon = '".$mb_addr_jibeon[$i]."'					".
			"	, mb_login_ip = '".$mb_login_ip[$i]."'							".
			"	, mb_datetime = now()											".
			"	, mb_ip = '".$mb_ip[$i]."'										".
			"	, mb_email_certify = '".$mb_email_certify[$i]."'				".
			"	, mb_mailling = '".$mb_mailling[$i]."'							".
			"	, mb_sms = '".$mb_sms[$i]."'									".
			"	, mb_open = '".$mb_open[$i]."'									".
			"	, mb_open_date = now()											";
	sql_query($sql1,1);	//<===============================
	//echo $sql1.'<br><br>';

	

	//-- DOM 보도 자바스크립트가 좀 빨라서 0,1번이 안 보이고 2번부터 보이는 표현상 에러가 있음, 동작에는 무리가 없습니다.
	echo "<script> document.all.cont.innerHTML += '".($i+1).". ".addslashes($sql1)."<br>'; </script>\n";
	//echo "+";
    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);
    if ($i % $countgap == 0)
    {
        echo "<script> document.all.cont.innerHTML += '<br>'; document.body.scrollTop += 1000; </script>\n";
    }

    // 화면을 지운다... 부하를 줄임
    if ($i % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML = ''; document.body.scrollTop += 1000; </script>\n";


}
?>

<script> document.all.cont.innerHTML += "<br><br><br>총 <?php echo number_format($i) ?>건 작업 완료<br><br><font color=crimson><b>[끝]</b></font>"; document.body.scrollTop += 1000; </script>
</body>
</html>
