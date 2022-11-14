<?php
// 실행주소: http://local.ingsystem.com/adm/v10/convert/data_insert1.php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1 (실행모드 = 0)

$g5['title'] = '데이타 입력';
include_once(G5_PATH.'/head.sub.php');
?>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');


//-- 설정값
$group_array = array('err','run','product','pre','err','err','pre','pre','err','err');
$no_array = array(0,0,0,0,0,0,0,1,2,3,4);
$name_array = array('모터','서보모터','실린더','프레스');
$range1_array = array(-5,0,10,11,12,13,14,15,6,7,8,2,3,4,5,6,7,20,100,200,250,40,30,20,23,45,45,1000,1200,300);
$range2_array = array(50,55,23,90,-100,-30,34,58,57,35,134,167,250);
$range3_array = array(2,20,20,30,40,50,344,345,35,67,456,23,25,46,67,69,100);
$range4_array = array(2,20,20,30,40,50,344,345,35,67,456,23,25,46,67,69,100);
$range5_array = array(20,45,689,345,345,356,567,34,345,578,468,578,1000,3456,3456,13000,3450,1360,12000,14000,20000);
$range6_array = array(0,34,57,120,89,87,90,99,89,90,230,45,67,89,87,65,88);
$range7_array = array(0,34,57,56,89,87,90,99,89,90,23,45,67,89,87,65,88);
$range8_array = array(10,34,57,56,89,22,45,99,89,90,23,45,67,89,87,65,88);
$range9_array = array(2000,1200,689,345,345,356,567,34,345,578,468,578,1000,3456,3456,1300,1450,1360,1000,2000,2222);
$unit_array = array('°C','%','Am','V','Hz','dB','%','psi','rpm');
$nonperiodic_array = array(2,3,4,5,6,8,9);
$start_time = strtotime('2020-01-06 00:00:00'); //<<<<<<<<<<<<<<==================================
$end_time = strtotime('2020-01-31 23:59:59');   //<<<<<<<<<<<<<<==================================

//-- 필드명 추출 mb_ 와 같은 앞자리 4자 추출 --//
$r = sql_query(" desc g5_1_data ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,4);

flush();
ob_flush();

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 200;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 40; // 몇건씩 화면에 보여줄건지?

$cnt=0; // 카운터를 세는 이유가 있네 (이거 안 하니까 자꾸 두번째부터 보임!)
// 디비 생성
for($i=$start_time;$i<=$end_time;$i+=10) {
	$cnt++;

    // 데모 테스트용은 6개만 보여주세요.
//	if($cnt>5)
//		break;

	// 온도, 습도는 항상 입력
	// 나머지는 7개중에 4개만 입력 2. 토크(%) 3. 전류(A) 4. 전압(V) 5. 진동(Hz) 6. 소리(dB) 8. 압력(psi) 9.속도(r/min)
    
	// 온도는 항상 입력
    $dta_dt[$cnt] = date("Y-m-d H:i:s",$i);
	$dta_type[$cnt] = 1;
	$dta_value[$cnt] = ${'range'.$dta_type[$cnt].'_array'}[rand(0,sizeof(${'range'.$dta_type[$cnt].'_array'})-1)];
	$dta_unit[$cnt] = $unit_array[$dta_type[$cnt]-1];
	$dta_message[$cnt] = '메시지 '.$i;
	
	$sql1 = " INSERT INTO g5_1_data SET										      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, cod_idx	= '".rand(1,12)."'							          ".
			"	, dta_code = '".rand(111,9999)."'							      ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_name = '".$name_array[rand(0,sizeof($name_array)-1)]."'	  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".$dta_value[$cnt]."'							  ".
			"	, dta_unit = '".$dta_unit[$cnt]."'							      ".
			"	, dta_message = '".$dta_message[$cnt]."'						  ".
			"	, dta_status = 'ok'								                  ".
            "	, dta_reg_dt = now()										      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	

	// 습도도 항상 입력
    $dta_dt[$cnt] = date("Y-m-d H:i:s",$i);
	$dta_type[$cnt] = 7;
	$dta_value[$cnt] = ${'range'.$dta_type[$cnt].'_array'}[rand(0,sizeof(${'range'.$dta_type[$cnt].'_array'})-1)];
	$dta_unit[$cnt] = $unit_array[$dta_type[$cnt]-1];
	$dta_message[$cnt] = '메시지 '.$i;
	
	$sql1 = " INSERT INTO g5_1_data SET										      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, cod_idx	= '".rand(1,12)."'							          ".
			"	, dta_code = '".rand(111,9999)."'							      ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_name = '".$name_array[rand(0,sizeof($name_array)-1)]."'	  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".$dta_value[$cnt]."'							  ".
			"	, dta_unit = '".$dta_unit[$cnt]."'							      ".
			"	, dta_message = '".$dta_message[$cnt]."'						  ".
			"	, dta_status = 'ok'								                  ".
            "	, dta_reg_dt = now()										      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	
	// 7개 중에서 4개
    $rand_keys = array_rand($nonperiodic_array, 4);
    
	// 4개중에 첫번째
    $dta_dt[$cnt] = date("Y-m-d H:i:s",$i+rand(0,9));
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[0]];
	$dta_value[$cnt] = ${'range'.$dta_type[$cnt].'_array'}[rand(0,sizeof(${'range'.$dta_type[$cnt].'_array'})-1)];
	$dta_unit[$cnt] = $unit_array[$dta_type[$cnt]-1];
	$dta_message[$cnt] = '메시지 '.$i;
	
	$sql1 = " INSERT INTO g5_1_data SET										      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, cod_idx	= '".rand(1,12)."'							          ".
			"	, dta_code = '".rand(111,9999)."'							      ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_name = '".$name_array[rand(0,sizeof($name_array)-1)]."'	  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".$dta_value[$cnt]."'							  ".
			"	, dta_unit = '".$dta_unit[$cnt]."'							      ".
			"	, dta_message = '".$dta_message[$cnt]."'						  ".
			"	, dta_status = 'ok'								                  ".
            "	, dta_reg_dt = now()										      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}

    // 4개중에 두번째
    $dta_dt[$cnt] = date("Y-m-d H:i:s",$i+rand(0,9));
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[1]];
	$dta_value[$cnt] = ${'range'.$dta_type[$cnt].'_array'}[rand(0,sizeof(${'range'.$dta_type[$cnt].'_array'})-1)];
	$dta_unit[$cnt] = $unit_array[$dta_type[$cnt]-1];
	$dta_message[$cnt] = '메시지 '.$i;
	
	$sql1 = " INSERT INTO g5_1_data SET										      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, cod_idx	= '".rand(1,12)."'							          ".
			"	, dta_code = '".rand(111,9999)."'							      ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_name = '".$name_array[rand(0,sizeof($name_array)-1)]."'	  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".$dta_value[$cnt]."'							  ".
			"	, dta_unit = '".$dta_unit[$cnt]."'							      ".
			"	, dta_message = '".$dta_message[$cnt]."'						  ".
			"	, dta_status = 'ok'								                  ".
            "	, dta_reg_dt = now()										      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	
	// 4개중에 세번째
    $dta_dt[$cnt] = date("Y-m-d H:i:s",$i+rand(0,9));
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[2]];
	$dta_value[$cnt] = ${'range'.$dta_type[$cnt].'_array'}[rand(0,sizeof(${'range'.$dta_type[$cnt].'_array'})-1)];
	$dta_unit[$cnt] = $unit_array[$dta_type[$cnt]-1];
	$dta_message[$cnt] = '메시지 '.$i;
	
	$sql1 = " INSERT INTO g5_1_data SET										      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, cod_idx	= '".rand(1,12)."'							          ".
			"	, dta_code = '".rand(111,9999)."'							      ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_name = '".$name_array[rand(0,sizeof($name_array)-1)]."'	  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".$dta_value[$cnt]."'							  ".
			"	, dta_unit = '".$dta_unit[$cnt]."'							      ".
			"	, dta_message = '".$dta_message[$cnt]."'						  ".
			"	, dta_status = 'ok'								                  ".
            "	, dta_reg_dt = now()										      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	
	// 4개중에 네번째
    $dta_dt[$cnt] = date("Y-m-d H:i:s",$i+rand(0,9));
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[3]];
	$dta_value[$cnt] = ${'range'.$dta_type[$cnt].'_array'}[rand(0,sizeof(${'range'.$dta_type[$cnt].'_array'})-1)];
	$dta_unit[$cnt] = $unit_array[$dta_type[$cnt]-1];
	$dta_message[$cnt] = '메시지 '.$i;
	
	$sql1 = " INSERT INTO g5_1_data SET										      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, cod_idx	= '".rand(1,12)."'							          ".
			"	, dta_code = '".rand(111,9999)."'							      ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_name = '".$name_array[rand(0,sizeof($name_array)-1)]."'	  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".$dta_value[$cnt]."'							  ".
			"	, dta_unit = '".$dta_unit[$cnt]."'							      ".
			"	, dta_message = '".$dta_message[$cnt]."'						  ".
			"	, dta_status = 'ok'								                  ".
            "	, dta_reg_dt = now()										      ";
    if($demo) {echo $sql1.'<br>=========================<br>';}
    else {sql_query($sql1,1);}
    	
    
    
    
    echo "<script> document.all.cont.innerHTML += '".$cnt.". ".addslashes($sql1)."<br>'; </script>".PHP_EOL;

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