<?php
// item 디비에 it_more, it_keys 정보 추가하고 내용 정리
// 실행주소: http://woogle.kr/adm/v10/convert/item_change1.php
include_once('./_common.php');

$g5['title'] = '주문 정보 변경';
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
?>


<?php
//-- 설정값
$pg_array = array('offline','kcp','lgu','paypal','ksnet','etc');
$status_array = array('pending','ok','ok','ok','cancel');
$ampm_array = array('am','pm');

// 변수 설정
$table1 = 'g5_shop_item';
$table1_id = 'item';
$table2 = 'g5_shop_item';
$table2_id = 'item';

//-- 필드명 추출 mb_ 와 같은 앞자리 4자 추출 --//
$r = sql_query(" desc g5_1_data ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,4);


flush();
ob_flush();

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20000;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

// 대상 디비 전체 추출
$sql = "	SELECT * FROM {$table1} ";
//echo $sql;
$result = sql_query($sql,1);
$cnt=0; // 카운터를 세는 이유가 있네 (이거 안 하니까 자꾸 두번째부터 보임!)
for($i=0;$row=sql_fetch_array($result);$i++) {
	$cnt++;
//	if($i > 5)
//		break;
	

	//변수 추출
	for ($j=0; $j<sizeof($db_fields); $j++) {
		$row[$db_fields[$j]] = $row[$db_fields[$j]];
		$row['esc'][$db_fields[$j]] = addslashes($row[$db_fields[$j]]);
	}
    // 메타 정보 추출
    $row['mta'] = get_meta1('shop_item',$row['it_id']);
    
	//print_r2($row);

	// 변수 재설정{ =====================
    //$row['mb_password'] = get_encrypt_string($row['M_PWD']);
    //$row['mb_mailling'] = ($row['M_MAILING_U']=='Y') ? 1 : 0 ;

    // it_key 정보 생성
	$row['it_keys'] = ':it_price_type='.$row['mta']['it_price_type'].':,';
	$row['it_keys'] .= ':it_sls_type1='.$row['mta']['it_sls_type1'].':,';
	$row['it_keys'] .= ':it_sls_type2='.$row['mta']['it_sls_type2'].':,';
	$row['it_keys'] .= ':it_ad_type='.$row['it_1'].':,';

	// it_more 정보 설정
    $a['it_price_type'] = $row['mta']['it_price_type'];
    $a['it_sls_type1'] = $row['mta']['it_sls_type1'];
    $a['it_sls_type2'] = $row['mta']['it_sls_type2'];
    $a['it_ad_type'] = $row['it_1'];
    $a['it_service_days'] = $row['it_service_days'];
    $a['it_sales_zero'] = $row['it_2'];
    $a['it_price_zero_yn'] = $row['it_3'];
    $a['it_theme_name'] = $row['it_10'];
    //$a['set_policy_content'] = base64_encode($_REQUEST['set_policy_content']);
    $row['it_more'] = addslashes(serialize($a));
    unset($a);

    // }변수 재설정 =====================

	
    //-- 정보 입력 --//
    if($row['it_id']) {
        $sql1 = "   UPDATE {$table2} SET
                        it_keys = '".$row['it_keys']."'
                    --    ,it_more	= '".$row['it_more']."'
                    WHERE it_id = '".$row['it_id']."'
        ";
        sql_query($sql1,1);	//<===============================
//        echo br2nl($sql1).'<br><br>';
    }
    
    $ar['mta_db_table'] = 'shop_item';
    $ar['mta_db_id'] = $row['it_id'];
    $ar['mta_key'] = 'it_more';
    $ar['mta_value'] = $row['it_more'];
    meta_update($ar);
//    print_r2($ar);
    unset($ar);


	
	// 메시지 보임
	echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$row['it_id']." 처리됨<br>'; </script>".PHP_EOL;
	
	flush();
	ob_flush();
	ob_end_flush();
	usleep($sleepsec);
	
	// 보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
	if ($cnt % $countgap == 0)
		echo "<script> document.all.cont.innerHTML += '<br>'; </script>\n";
	
	// 화면 정리! 부하를 줄임 (화면 싹 지움)
	if ($cnt % $maxscreen == 0)
		echo "<script> document.all.cont.innerHTML = ''; </script>\n";
	
}
?>
<script>
	document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i) ?>건 완료<br><br><font color=crimson><b>[끝]</b></font>";
</script>
