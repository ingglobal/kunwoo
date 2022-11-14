<?php
// cart 디비에 ct_history에 들어간 이상한 정보를 제거하고 내용 정리
// 실행주소: http://woogle.kr/adm/v10/convert/cart_change3.php
include_once('./_common.php');

$g5['title'] = '주문상품 정보 변경';
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
$table1 = 'g5_shop_cart';
$table1_id = 'cart';
$table2 = 'g5_shop_cart';
$table2_id = 'cart';


//-- 필드명 추출 mb_ 와 같은 앞자리 4자 추출 --//
$r = sql_query(" desc {$table1} ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,4);


$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 200;  // 백만분의 몇초간 쉴지 설정 (20000하면 좀 느림)
$maxscreen = 50; // 몇건씩 화면에 보여줄건지?

flush();
ob_flush();

// 대상 디비 전체 추출
$sql = "	SELECT * FROM {$table1} ";
//$sql = "	SELECT * FROM {$table1} WHERE od_id = '2018021209460323' ";
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
	// 배열 변수 할당
    $row['ct_history3'] = '';
    $row['ct_history2'] = get_ct_history($row['ct_history']);
    for($j=0;$j<sizeof($row['ct_history2']);$j++) {
        list($ct_status,$mb_id,$ct_date,$ct_ip) = explode('|', trim($row['ct_history2'][$j]));
        //echo $ct_status.' | '.$mb_id.' | '.$ct_date.' | '.$ct_ip.'<br>';
        // 상태값이 한글이 아니면 무시
        if(preg_match("/^[가-힝]/",$ct_status)) {
            $row['ct_history3'] .= trim($row['ct_history2'][$j]).'\n';
        }
    }

    // }변수 재설정 =====================

	
    //-- 정보 입력 --//
    if($row['ct_id']) {
        $sql1 = "   UPDATE {$table2} SET
                        ct_history = '".substr($row['ct_history3'],0,-2)."'
                    WHERE ct_id = '".$row['ct_id']."'
        ";
        sql_query($sql1,1);	//<===============================
//        echo $sql1.'<br>---------<br>';
//        echo br2nl($sql1).'<br><br>';
    }
    
	
	// 메시지 보임
	echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$row['ct_id']." 처리됨<br>'; </script>".PHP_EOL;
	
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
