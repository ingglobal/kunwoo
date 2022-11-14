<?php
// cart 디비에 ct_more, ct_keys 정보 추가하고 내용 정리
// 실행주소: http://woogle.kr/adm/v10/convert/cart_change2.php
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


// 확장 메타값 배열로 반환하는 함수
if(!function_exists('get_meta2')){
function get_meta2($db_table,$db_id)
{
    global $g5;
	
	if(!$db_table||!$db_id)
		return false;

	// 메타 확장값(들) 추출
	$sql = "	SELECT GROUP_CONCAT(CONCAT(mta_key, '=', COALESCE(mta_value, 'NULL'))) AS metas
				FROM {$g5['meta_table']} 
				WHERE mta_db_table = '".$db_table."' AND mta_db_id = '".$db_id."'
    ";
    //echo $sql.'<br>';
	$mta1 = sql_fetch($sql);
	$pieces = explode(',', $mta1['metas']);
	foreach ($pieces as $piece) {
		if($piece) {
			list($key, $value) = explode('=', $piece);
            //echo $key.'>'.$value.'<br>';
			$mta2[$key] = $value;
            if(is_serialized($value)) {
                unset($mta2[$key]); // serialized된 변수는 제거
                $unser = unserialize($value);
                if( is_array($unser) ) {
                    foreach ($unser as $k1=>$v1) {
                        //echo $k1.'>'.$v1.'<br>';
                        $mta2[$k1] = $v1;
                    }    
                }
            }
		}
	}
	unset($pieces);unset($piece);

    return $mta2;
}
}
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
    $row['ct_keys2'] = get_keys($row['ct_keys']);
    $row['ct_more2'] = get_meta2('shop_cart',$row['ct_id']);
//    print_r2($row['ct_more2']).'<br>';

	// ct_more 정보 설정
    $row['ct_more3'] = '';
    $row['ct_more3'] = serialized_update('mb_name_saler',$row['ct_more2']['mb_name_saler'],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('trm_name_department',$g5['department_name'][$row['trm_idx_department']],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('mb_name_worker',$row['ct_more2']['mb_name_worker'],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('com_idx',$row['ct_more2']['com_idx'],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('com_name',$row['ct_more2']['com_name'],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('ct_refund_price',$row['ct_more2']['ct_refund_price'],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('ct_price_penalty',$row['ct_more2']['ct_price_penalty'],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('it_service_days',$row['ct_more2']['it_service_days'],$row['ct_more3']);
    $row['ct_more3'] = serialized_update('ct_refund_use_yn',$row['ct_more2']['ct_refund_use_yn'],$row['ct_more3']);
//    print_r2(get_serialized($row['ct_more3']));
//    echo $row['ct_more3'].'<br>';

    // }변수 재설정 =====================

	
    //-- 정보 입력 --//
    if($row['ct_id']) {
        $sql1 = "   UPDATE {$table2} SET
                        ct_keys = '".$row['ct_keys']."'
                    WHERE ct_id = '".$row['ct_id']."'
        ";
//        sql_query($sql1,1);	//<===============================
//        echo br2nl($sql1).'<br><br>';
    }
    
    $ar['mta_db_table'] = 'shop_cart';
    $ar['mta_db_id'] = $row['ct_id'];
    $ar['mta_key'] = 'ct_more';
    $ar['mta_value'] = $row['ct_more3'];
    meta_update($ar);
    unset($ar);


	
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
