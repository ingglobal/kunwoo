<?php
// 실행주소: http://local.ingsystem.com/adm/v10/convert/data_insert2.php
include_once('./_common.php');

$demo = 0;  // 데모모드 = 1 (실행모드 = 0)

//$range1_array = array(-20,1500,10,100); // min, max, rangemin, rangemax
//$value1 = rand($range1_array[0],$range1_array[1]); // 초기화
//$plus1 = 1; // 초기값
//for($i=0;$i<100;$i++) {
//    $byunit1 = $plus1 * rand($range1_array[2],$range1_array[3]);
//    $value1 += $byunit1;
//    if($value1 < $range1_array[0] || $value1 > $range1_array[1]) {
//        $plus1 = ($value1 < $range1_array[0]) ? 1 : $plus1;
//        $plus1 = ($value1 > $range1_array[1]) ? -1 : $plus1;
//        $value1 = rand($range1_array[0],$range1_array[1]); // 초기화 (초가화를 안 하면 맨위, 또는 맨 아래에서 연속함)
//    }
//    echo $value1.' + '.$plus1.'<br>';
//}
//exit;


$g5['title'] = '데이타 입력';
include_once(G5_PATH.'/head.sub.php');
?>
<style>
    #hd_login_msg {display:none;}
</style>
<div class="" style="padding:10px;">
	<span>
		작업 시작~~ <font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
	</span><br><br>
	<span id="cont"></span>
</div>
<?php
include_once(G5_PATH.'/tail.sub.php');


//-- 설정값
$group_array = array('mea','mea','mea');
$no_array = array(0,0,0,0,0,0,0,1,2,3,4);   // 측정번호
$item_array = array(0,0,0,0,0,1,1,1,2);   // 기종번호

$arr1 = array(-20,1500,10,100); // min, max, rangemin, rangemax
$arr2 = array(-300,300,5,30); // min, max, rangemin, rangemax
$arr3 = array(0,1000,7,20); // min, max, rangemin, rangemax
$arr4 = array(0,1000,7,20); // min, max, rangemin, rangemax
$arr5 = array(20,2000,10,50); // min, max, rangemin, rangemax
$arr6 = array(0,150,5,15); // min, max, rangemin, rangemax
$arr7 = array(0,100,5,15); // min, max, rangemin, rangemax
$arr8 = array(0,100,5,15); // min, max, rangemin, rangemax
$arr9 = array(0,3000,10,100); // min, max, rangemin, rangemax
$plus1 = 1; // 처음 곡선 방향: 위쪽
$plus2 = -1;// 처음 곡선 방향: 아래
$plus3 = 1;
$plus4 = -1;
$plus5 = 1;
$plus6 = -1;
$plus7 = 1;
$plus8 = -1;
$plus9 = 1;
$nonperiodic_array = array(2,3,4,5,6,8,9);

$start_time = time()-86400*1; //<<<<<<<<<<<<<<==================================
$end_time = time();   //<<<<<<<<<<<<<<==================================

//-- 필드명 추출 mb_ 와 같은 앞자리 4자 추출 --//
$r = sql_query(" desc g5_1_data_measure ");
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

	$dta_shf_max[$cnt] = rand(1,3);
	$dta_shf_no[$cnt] = rand(1,$dta_shf_max[$cnt]);

    // 온도, 습도는 항상 입력
	// 나머지는 7개중에 4개만 입력 2. 토크(%) 3. 전류(A) 4. 전압(V) 5. 진동(Hz) 6. 소리(dB) 8. 압력(psi) 9.속도(r/min)
    
	// 온도는 항상 입력
    $dta_dt[$cnt] = $i;
	$dta_type[$cnt] = 1;
//	$dta_value[$cnt] = ${'range'.$dta_type[$cnt].'_array'}[rand(0,sizeof(${'range'.$dta_type[$cnt].'_array'})-1)];
    ${'byunit'.$dta_type[$cnt]} = ${'plus'.$dta_type[$cnt]} * rand(${'arr'.$dta_type[$cnt]}[2],${'arr'.$dta_type[$cnt]}[3]);
    ${'val'.$dta_type[$cnt]} += ${'byunit'.$dta_type[$cnt]};
    if(${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0] || ${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) {
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0]) ? 1 : ${'plus'.$dta_type[$cnt]};
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) ? -1 : ${'plus'.$dta_type[$cnt]};
        ${'val'.$dta_type[$cnt]} = rand(${'arr'.$dta_type[$cnt]}[0],${'arr'.$dta_type[$cnt]}[1]); // 초기화 (초가화를 안 하면 맨위, 또는 맨 아래에서 연속함)
    }
	$sql1 = " INSERT INTO g5_1_data_measure SET									  ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, mms_idx	= '".rand(1,4)."'							          ".
			"	, shf_idx	= '".rand(1,4)."'							          ".
			"	, dta_shf_no = '".$dta_shf_no[$cnt]."'						      ".
			"	, dta_shf_max = '".$dta_shf_max[$cnt]."'					      ".
			"	, dta_mmi_no = '".$item_array[rand(0,sizeof($item_array)-1)]."'	  ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".${'val'.$dta_type[$cnt]}."'					  ".
			"	, dta_status = '0'								                  ".
            "	, dta_reg_dt = '".G5_SERVER_TIME."'							      ".
            "	, dta_update_dt = '".G5_SERVER_TIME."'						      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
    $dta_display[$cnt] = $g5['set_data_type_value'][$dta_type[$cnt]].'('.${'val'.$dta_type[$cnt]}.'), ';
	

	// 습도도 항상 입력
    $dta_dt[$cnt] = $i;
	$dta_type[$cnt] = 7;
    ${'byunit'.$dta_type[$cnt]} = ${'plus'.$dta_type[$cnt]} * rand(${'arr'.$dta_type[$cnt]}[2],${'arr'.$dta_type[$cnt]}[3]);
    ${'val'.$dta_type[$cnt]} += ${'byunit'.$dta_type[$cnt]};
    if(${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0] || ${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) {
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0]) ? 1 : ${'plus'.$dta_type[$cnt]};
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) ? -1 : ${'plus'.$dta_type[$cnt]};
        ${'val'.$dta_type[$cnt]} = rand(${'arr'.$dta_type[$cnt]}[0],${'arr'.$dta_type[$cnt]}[1]); // 초기화 (초가화를 안 하면 맨위, 또는 맨 아래에서 연속함)
    }
	$sql1 = " INSERT INTO g5_1_data_measure SET									  ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, mms_idx	= '".rand(1,4)."'							          ".
			"	, shf_idx	= '".rand(1,4)."'							          ".
			"	, dta_shf_no = '".$dta_shf_no[$cnt]."'						      ".
			"	, dta_shf_max = '".$dta_shf_max[$cnt]."'					      ".
			"	, dta_mmi_no = '".$item_array[rand(0,sizeof($item_array)-1)]."'	  ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".${'val'.$dta_type[$cnt]}."'					  ".
			"	, dta_status = '0'								                  ".
            "	, dta_reg_dt = '".G5_SERVER_TIME."'							      ".
            "	, dta_update_dt = '".G5_SERVER_TIME."'						      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
    $dta_display[$cnt] .= $g5['set_data_type_value'][$dta_type[$cnt]].'('.${'val'.$dta_type[$cnt]}.'), ';
	
	// 7개 중에서 4개
    $rand_keys = array_rand($nonperiodic_array, 4);
    
	// 4개중에 첫번째
    $dta_dt[$cnt] = $i+rand(0,9);
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[0]];
    ${'byunit'.$dta_type[$cnt]} = ${'plus'.$dta_type[$cnt]} * rand(${'arr'.$dta_type[$cnt]}[2],${'arr'.$dta_type[$cnt]}[3]);
    ${'val'.$dta_type[$cnt]} += ${'byunit'.$dta_type[$cnt]};
    if(${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0] || ${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) {
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0]) ? 1 : ${'plus'.$dta_type[$cnt]};
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) ? -1 : ${'plus'.$dta_type[$cnt]};
        ${'val'.$dta_type[$cnt]} = rand(${'arr'.$dta_type[$cnt]}[0],${'arr'.$dta_type[$cnt]}[1]); // 초기화 (초가화를 안 하면 맨위, 또는 맨 아래에서 연속함)
    }
    
	$sql1 = " INSERT INTO g5_1_data_measure SET								      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, mms_idx	= '".rand(1,4)."'							          ".
			"	, shf_idx	= '".rand(1,4)."'							          ".
			"	, dta_shf_no = '".$dta_shf_no[$cnt]."'						      ".
			"	, dta_shf_max = '".$dta_shf_max[$cnt]."'					      ".
			"	, dta_mmi_no = '".$item_array[rand(0,sizeof($item_array)-1)]."'	  ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".${'val'.$dta_type[$cnt]}."'					  ".
			"	, dta_status = '0'								                  ".
            "	, dta_reg_dt = '".G5_SERVER_TIME."'							      ".
            "	, dta_update_dt = '".G5_SERVER_TIME."'						      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
    $dta_display[$cnt] .= $g5['set_data_type_value'][$dta_type[$cnt]].'('.${'val'.$dta_type[$cnt]}.'), ';

    // 4개중에 두번째
    $dta_dt[$cnt] = $i+rand(0,9);
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[1]];
    ${'byunit'.$dta_type[$cnt]} = ${'plus'.$dta_type[$cnt]} * rand(${'arr'.$dta_type[$cnt]}[2],${'arr'.$dta_type[$cnt]}[3]);
    ${'val'.$dta_type[$cnt]} += ${'byunit'.$dta_type[$cnt]};
    if(${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0] || ${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) {
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0]) ? 1 : ${'plus'.$dta_type[$cnt]};
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) ? -1 : ${'plus'.$dta_type[$cnt]};
        ${'val'.$dta_type[$cnt]} = rand(${'arr'.$dta_type[$cnt]}[0],${'arr'.$dta_type[$cnt]}[1]); // 초기화 (초가화를 안 하면 맨위, 또는 맨 아래에서 연속함)
    }
	
	$sql1 = " INSERT INTO g5_1_data_measure SET							          ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, mms_idx	= '".rand(1,4)."'							          ".
			"	, shf_idx	= '".rand(1,4)."'							          ".
			"	, dta_shf_no = '".$dta_shf_no[$cnt]."'						      ".
			"	, dta_shf_max = '".$dta_shf_max[$cnt]."'					      ".
			"	, dta_mmi_no = '".$item_array[rand(0,sizeof($item_array)-1)]."'	  ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".${'val'.$dta_type[$cnt]}."'					  ".
			"	, dta_status = '0'								                  ".
            "	, dta_reg_dt = '".G5_SERVER_TIME."'							      ".
            "	, dta_update_dt = '".G5_SERVER_TIME."'						      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
    $dta_display[$cnt] .= $g5['set_data_type_value'][$dta_type[$cnt]].'('.${'val'.$dta_type[$cnt]}.'), ';
	
	// 4개중에 세번째
    $dta_dt[$cnt] = $i+rand(0,9);
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[2]];
    ${'byunit'.$dta_type[$cnt]} = ${'plus'.$dta_type[$cnt]} * rand(${'arr'.$dta_type[$cnt]}[2],${'arr'.$dta_type[$cnt]}[3]);
    ${'val'.$dta_type[$cnt]} += ${'byunit'.$dta_type[$cnt]};
    if(${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0] || ${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) {
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0]) ? 1 : ${'plus'.$dta_type[$cnt]};
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) ? -1 : ${'plus'.$dta_type[$cnt]};
        ${'val'.$dta_type[$cnt]} = rand(${'arr'.$dta_type[$cnt]}[0],${'arr'.$dta_type[$cnt]}[1]); // 초기화 (초가화를 안 하면 맨위, 또는 맨 아래에서 연속함)
    }
	
	$sql1 = " INSERT INTO g5_1_data_measure SET								      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, mms_idx	= '".rand(1,4)."'							          ".
			"	, shf_idx	= '".rand(1,4)."'							          ".
			"	, dta_shf_no = '".$dta_shf_no[$cnt]."'						      ".
			"	, dta_shf_max = '".$dta_shf_max[$cnt]."'					      ".
			"	, dta_mmi_no = '".$item_array[rand(0,sizeof($item_array)-1)]."'	  ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".${'val'.$dta_type[$cnt]}."'					  ".
			"	, dta_status = '0'								                  ".
            "	, dta_reg_dt = '".G5_SERVER_TIME."'							      ".
            "	, dta_update_dt = '".G5_SERVER_TIME."'						      ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
    $dta_display[$cnt] .= $g5['set_data_type_value'][$dta_type[$cnt]].'('.${'val'.$dta_type[$cnt]}.'), ';
	
	// 4개중에 네번째
    $dta_dt[$cnt] = $i+rand(0,9);
	$dta_type[$cnt] = $nonperiodic_array[$rand_keys[3]];
    ${'byunit'.$dta_type[$cnt]} = ${'plus'.$dta_type[$cnt]} * rand(${'arr'.$dta_type[$cnt]}[2],${'arr'.$dta_type[$cnt]}[3]);
    ${'val'.$dta_type[$cnt]} += ${'byunit'.$dta_type[$cnt]};
    if(${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0] || ${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) {
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} < ${'arr'.$dta_type[$cnt]}[0]) ? 1 : ${'plus'.$dta_type[$cnt]};
        ${'plus'.$dta_type[$cnt]} = (${'val'.$dta_type[$cnt]} > ${'arr'.$dta_type[$cnt]}[1]) ? -1 : ${'plus'.$dta_type[$cnt]};
        ${'val'.$dta_type[$cnt]} = rand(${'arr'.$dta_type[$cnt]}[0],${'arr'.$dta_type[$cnt]}[1]); // 초기화 (초가화를 안 하면 맨위, 또는 맨 아래에서 연속함)
    }
	
	$sql1 = " INSERT INTO g5_1_data_measure SET								      ".
			"	com_idx	= '".rand(1,10)."'							              ".
			"	, imp_idx	= '".rand(1,16)."'							          ".
			"	, mms_idx	= '".rand(1,4)."'							          ".
			"	, shf_idx	= '".rand(1,4)."'							          ".
			"	, dta_shf_no = '".$dta_shf_no[$cnt]."'						      ".
			"	, dta_shf_max = '".$dta_shf_max[$cnt]."'					      ".
			"	, dta_mmi_no = '".$item_array[rand(0,sizeof($item_array)-1)]."'	  ".
			"	, dta_group = '".$group_array[rand(0,sizeof($group_array)-1)]."'  ".
			"	, dta_type = '".$dta_type[$cnt]."'		     				      ".
			"	, dta_no = '".$no_array[rand(0,sizeof($no_array)-1)]."'			  ".
			"	, dta_dt = '".$dta_dt[$cnt]."'								      ".
			"	, dta_value = '".${'val'.$dta_type[$cnt]}."'					  ".
			"	, dta_status = '0'								                  ".
            "	, dta_reg_dt = '".G5_SERVER_TIME."'							      ".
            "	, dta_update_dt = '".G5_SERVER_TIME."'						      ";
    if($demo) {echo $sql1.'<br>=========================<br>';}
    else {sql_query($sql1,1);}
    $dta_display[$cnt] .= $g5['set_data_type_value'][$dta_type[$cnt]].'('.${'val'.$dta_type[$cnt]}.'), ';
    	

    

    echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$dta_display[$cnt]." 완료 <br>'; </script>".PHP_EOL;

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
sql_query("TRUNCATE g5_1_data_measure_sum",1);
$sql = "INSERT INTO g5_1_data_measure_sum (com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_type, dta_no, dta_date, dta_sum, dta_max, dta_min, dta_avg)
		SELECT com_idx, imp_idx, mms_idx, shf_idx, dta_shf_no, dta_mmi_no, dta_group, dta_type, dta_no
		, FROM_UNIXTIME(dta_dt,'%Y-%m-%d') AS dta_date
		, SUM(dta_value) AS dta_value_sum
		, MAX(dta_value) AS dta_value_max
		, MIN(dta_value) AS dta_value_min
		, ROUND(AVG(dta_value),2) AS dta_value_avg
		FROM g5_1_data_measure 
		WHERE dta_status = 0
		GROUP BY mms_idx, dta_shf_no, dta_mmi_no, dta_type, dta_no, dta_date
		ORDER BY dta_date ASC, mms_idx, dta_shf_no, dta_mmi_no, dta_type, dta_no
";
sql_query($sql,1);

?>

<script> document.all.cont.innerHTML += "<br><br><br>총 <?php echo number_format($cnt) ?>건 작업 완료<br><br><font color=crimson><b>[끝]</b></font>"; document.body.scrollTop += 1000; </script>
</body>
</html>