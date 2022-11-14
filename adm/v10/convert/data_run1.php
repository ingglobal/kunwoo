<?php
// 실행주소: http://local.ingsystem.com/adm/v10/convert/data_insert2.php
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
$group_array = array('run','run','product');
$item_array = array(0,0,0,0,0,1,1,1,2);   // 기종번호

$start_time = time()-86400*1; //<<<<<<<<<<<<<<==================================
$end_time = time();   //<<<<<<<<<<<<<<==================================
// $start_time = time()-86400*120; //<<<<<<<<<<<<<<==================================
// $end_time = time()-86400*40;   //<<<<<<<<<<<<<<==================================

//-- 필드명 추출 mb_ 와 같은 앞자리 4자 추출 --//
$r = sql_query(" desc g5_1_data_run ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,4);

flush();
ob_flush();

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 40; // 몇건씩 화면에 보여줄건지?

$cnt=0; // 카운터를 세는 이유가 있네 (이거 안 하니까 자꾸 두번째부터 보임!)
// 디비 생성
for($i=$start_time;$i<=$end_time;$i+=10) {
	$cnt++;

    // 데모 테스트용은 6개만 보여주세요.
//	if($cnt>5)
//		break;

    $dta_dt[$cnt] = $i+rand(0,9);
	$dta_type[$cnt] = 1;
	$dta_unit[$cnt] = $unit_array[$dta_type[$cnt]-1];
	$dta_message[$cnt] = '메시지 '.$i;
	$dta_shf_max[$cnt] = rand(1,3);
	$dta_shf_no[$cnt] = rand(1,$dta_shf_max[$cnt]);
	$dta_group[$cnt] = $group_array[rand(0,sizeof($group_array)-1)];
	$dta_value[$cnt] = ($dta_group[$cnt]=='run') ? rand(0,59) : 1;
	
	$sql1 = " INSERT INTO g5_1_data_run SET								      ".
			"	com_idx	= '".rand(1,67)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, mms_idx	= '".rand(1,4)."'							          ".
			"	, shf_idx	= '".rand(1,4)."'							          ".
			"	, dta_shf_no = '".$dta_shf_no[$cnt]."'						      ".
			"	, dta_shf_max = '".$dta_shf_max[$cnt]."'					      ".
			"	, dta_mmi_no = '".$item_array[rand(0,sizeof($item_array)-1)]."'	  ".
			"	, dta_group = '".$dta_group[$cnt]."'                              ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".$dta_value[$cnt]."'							  ".
			"	, dta_status = '0'								                  ".
            "	, dta_reg_dt = '".G5_SERVER_TIME."'							      ".
            "	, dta_update_dt = '".G5_SERVER_TIME."'						      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	

    echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$dta_group[$cnt]." = ".$dta_value[$cnt]." 입력 <br>'; </script>".PHP_EOL;

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

// 합계 데이터 입력
sql_query("TRUNCATE g5_1_data_run_sum",1);
$sql = "INSERT INTO g5_1_data_run_sum (com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_date, dta_value)
		SELECT com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group
		, FROM_UNIXTIME(dta_dt,'%Y-%m-%d') AS dta_date
		, SUM(dta_value) AS dta_value_sum
		FROM g5_1_data_run 
		WHERE dta_status = 0
		GROUP BY mms_idx, dta_shf_no, dta_mmi_no, dta_group, dta_date
		ORDER BY dta_date ASC
";
sql_query($sql,1);

?>

<script> document.all.cont.innerHTML += "<br><br><br>총 <?php echo number_format($cnt) ?>건 작업 완료<br><br><font color=crimson><b>[끝]</b></font>"; document.body.scrollTop += 1000; </script>
</body>
</html>