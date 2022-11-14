<?php
// 실행주소: http://bogwang.epcs.co.kr/adm/v10/convert/data_ymdhis.php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1 (실행모드 = 0)

$g5['title'] = '데이타 입력';
include_once(G5_PATH.'/head.sub.php');
?>
<div class="" style="padding:10px;">
	<span style=''>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');

//-- 설정값
$start_time = strtotime('2020-01-01 00:00:00'); //<<<<<<<<<<<<<<==================================
$end_time = strtotime('2020-12-31 23:59:59');   //<<<<<<<<<<<<<<==================================

flush();
ob_flush();

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 40; // 몇건씩 화면에 보여줄건지?

$cnt=0; // 카운터를 세는 이유가 있네 (이거 안 하니까 자꾸 두번째부터 보임!)
// 디비 생성
for($i=$start_time;$i<=$end_time;$i+=5) {
	$cnt++;

    // 데모 테스트용은 6개만 보여주세요.
//	if($cnt>5)
//		break;

    $dta_dt[$cnt] = date("Y-m-d H:i:s",$i);

    $sql1 = " INSERT INTO g5_5_ymdhis SET						".
			"	ymd_his	= '".date("YmdHis",$i)."'				".
			"	, ymd_datetime	= '".date("Y-m-d H:i:s",$i)."'  ".
			"	, ymd_timestamp	= '".$i."'						";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	
    echo "<script> document.all.cont.innerHTML += '".$cnt.". ".date("Y-m-d H:i:s",$i)." 완료!<br>'; </script>".PHP_EOL;

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);
    if ($cnt % $countgap == 0)
        echo "<script> document.all.cont.innerHTML += '<br>'; document.body.scrollTop += 1000; </script>\n";

    // 화면을 지운다... 부하를 줄임
    if ($cnt % $maxscreen == 0)
        echo "<script> document.all.cont.innerHTML = ''; document.body.scrollTop += 1000; </script>\n";

}
?>

<script> document.all.cont.innerHTML += "<br><br><br>총 <?php echo number_format($cnt) ?>건 작업 완료<br><br><font color=crimson><b>[끝]</b></font>"; document.body.scrollTop += 1000; </script>
</body>
</html>