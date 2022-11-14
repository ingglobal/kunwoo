<?php
// order 디비에 od_more, od_keys 정보 추가하고 내용 정리
// 실행주소: https://ycart.co.kr/adm/v10/convert/order_change5.php
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
// 변수 설정
$table1 = 'g5_shop_order';
$table1_id = 'order';
$table2 = 'g5_shop_order';
$table2_id = 'order';


//-- 필드명 추출 mb_ 와 같은 앞자리 4자 추출 --//
$r = sql_query(" desc {$table1} ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,4);


$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 200;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();

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
	//print_r2($row);

	// 변수 재설정{ =====================
    //$row['mb_password'] = get_encrypt_string($row['M_PWD']);
    //$row['mb_mailling'] = ($row['M_MAILING_U']=='Y') ? 1 : 0 ;

	// 영업자 아이디
	$mb2 = sql_fetch(" SELECT mb_name FROM g5_member WHERE mb_id = '".$row['mb_id_saler']."' ");

    // od_key 정보 생성
	$row['od_keys'] = ':mb_name_saler='.$row['mb_name_saler'].':,';
	$row['od_keys'] .= ':trm_name_department='.$g5['department_name'][$row['trm_idx_department']].':,';
	$row['od_keys'] .= ':mb_name_worker='.$row['mb_name_worker'].':,';
	$row['od_keys'] .= ':com_idx='.$row['com_idx'].':,';
	$row['od_keys'] .= ':com_name='.addslashes($row['com_name']).':,';


	// od_more 정보 설정
    $a['mb_name_saler'] = $row['mb_name_saler'];
    $a['trm_name_department'] = $g5['department_name'][$row['trm_idx_department']];
    $a['mb_name_worker'] = $row['mb_name_worker'];
    $a['com_idx'] = $row['com_idx'];
    $a['com_name'] = addslashes($row['com_name']);
    $a['od_order_type'] = $row['od_order_type'];
    $a['od_contract_type'] = $row['od_contract_type'];
    $a['od_baebon_type'] = $row['od_baebon_type'];
    //$a['set_policy_content'] = base64_encode($_REQUEST['set_policy_content']);
    $row['od_more'] = addslashes(serialize($a));
    unset($a);
    
    // }변수 재설정 =====================

	
    //-- 정보 입력 --//
    if($row['od_id']) {
        $sql1 = "   UPDATE {$table2} SET
                        od_keys = '".$row['od_keys']."'
                    WHERE od_id = '".$row['od_id']."'
        ";
        sql_query($sql1,1);	//<===============================
        //echo br2nl($sql1).'<br><br>';
    }

    $ar['mta_db_table'] = 'shop_order';
    $ar['mta_db_id'] = $row['od_id'];
    $ar['mta_key'] = 'od_more';
    $ar['mta_value'] = $row['od_more'];
    meta_update($ar);
    unset($ar);


	
	// 메시지 보임
	echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$row['od_id']." 처리됨<br>'; </script>".PHP_EOL;
	
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
