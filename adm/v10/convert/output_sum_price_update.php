<?php
// 실행주소: http://localhost/icmms/adm/v10/convert/output_sum_price_update.php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1 (실행모드 = 0)

$g5['title'] = '데이타 입력';
include_once(G5_PATH.'/head.sub.php');
?>
<style>
    #hd_login_msg {display:none;}
</style>
<div class="" style="padding:10px;">
	<span style=''>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');


//-- 설정값
flush();
ob_flush();

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 40; // 몇건씩 화면에 보여줄건지?


// 대상 디비 전체 추출
$sql = "SELECT mms_idx, mmi_no, mip_price, mip_start_date
		    , lead(mip_start_date,1) over(partition by mms_idx, mmi_no order by mip_start_date) AS mip_end_date
		FROM g5_1_mms_item_price AS mip
			LEFT JOIN g5_1_mms_item AS mmi ON mmi.mmi_idx = mip.mmi_idx
		WHERE mmi_status NOT IN ('trash','delete')
";
//echo $sql;
$result = sql_query($sql,1);
$cnt=0; // 카운터를 세는 이유가 있네 (이거 안 하니까 자꾸 두번째부터 보임!)
for($i=0;$row=sql_fetch_array($result);$i++) {
	$cnt++;
    // 데모 테스트용은 6개만 보여주세요.
//	if($cnt>5)
//		break;

	$row['mip_end_date'] = $row['mip_end_date'] ?: '9999-12-31';

	$sql1 = " UPDATE g5_1_data_output_sum SET					".
			"	dta_mmi_no_price = '".$row['mip_price']."'		".
            " WHERE mms_idx = '".$row['mms_idx']."'				".
            " 	AND dta_mmi_no = '".$row['mmi_no']."'			".
            " 	AND dta_date >= '".$row['mip_start_date']."'	".
            " 	AND dta_date < '".$row['mip_end_date']."'		";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	

    echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$row['mms_idx'].", ".$row['mmi_no']." 입력 <br>'; </script>".PHP_EOL;

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