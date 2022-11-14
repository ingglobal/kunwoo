<?php
// 실행주소: http://http://bogwang.epcs.co.kr/adm/v10/convert/data_item1.php
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
$defect_array = array(0,0,0,1,0,0,0,0,0,0,0,0,0,1);
$defect_type_array = array('error_stitch'
                            ,'error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle'
                            ,'error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle','error_wrinkle'
                            ,'error_fabric','error_push','error_pollution','error_bottom','error_etc');

$total = 10000;
$start_time = time()-86400*10; //<<<<<<<<<<<<<<==================================
$end_time = time();   //<<<<<<<<<<<<<<==================================
// $start_time = time()-86400*200; //<<<<<<<<<<<<<<==================================
// $end_time = time()-86400*190;   //<<<<<<<<<<<<<<==================================


//-- 필드명 추출 mb_ 와 같은 앞자리 4자 추출 --//
$r = sql_query(" desc g5_1_data_output ");
while ( $d = sql_fetch_array($r) ) {$db_fields[] = $d['Field'];}
$db_prefix = substr($db_fields[0],0,4);

flush();
ob_flush();

$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 20;  // 백만분의 몇초간 쉴지 설정
$maxscreen = 40; // 몇건씩 화면에 보여줄건지?

$cnt=0; // 카운터를 세는 이유가 있네 (이거 안 하니까 자꾸 두번째부터 보임!)
// 디비 생성
for($i=0;$i<$total;$i++) {
	$cnt++;

    // 데모 테스트용은 3개만 입력
	if($demo && $cnt>3)
		break;

    // 완성품 하나 RANDOM 추출
    $sql = "SELECT bom_idx,com_idx,bct_id,bom_name,bom_part_no FROM {$g5['bom_table']}
            WHERE com_idx = '{$_SESSION['ss_com_idx']}' AND bom_status NOT IN ('delete','trash')
                AND bom_type = 'product'
                AND bom_status = 'ok'
                AND bom_idx >= 3200
                ORDER BY RAND() LIMIT 1
    ";
    $bom = sql_fetch($sql,1);

    // 관련 자재
    $sql = "SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status, bom_min_cnt
                , bit1.bit_idx, bit1.bom_idx_child, bit1.bit_reply, bit1.bit_count 
                , COUNT(bit2.bit_idx) AS group_count
            FROM g5_1_bom_item AS bit1 
            JOIN g5_1_bom_item AS bit2
            LEFT JOIN g5_1_bom AS bom ON bom.bom_idx = bit2.bom_idx_child
            WHERE bit1.bom_idx = '".$bom['bom_idx']."' AND bit2.bom_idx = '".$bom['bom_idx']."'
            AND bit1.bit_num = bit2.bit_num AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
            GROUP BY bit1.bit_num, bit1.bit_reply
            ORDER BY bit1.bit_num DESC, bit1.bit_reply
    ";
    $rs = sql_query($sql,1);
    for($j=0;$row=sql_fetch_array($rs);$j++) {
        // print_r2($row);
    }

    // 출하-실행계획 RANDOM 추출
    $sql = " SELECT oop_idx FROM {$g5['order_out_practice_table']}
        WHERE oop_status NOT IN ('trash','delete')
            ORDER BY RAND() LIMIT 1
    ";
    $oop = sql_fetch($sql,1);


    $itm_dt[$i] = date("Y-m-d H:i:s",rand($start_time,$end_time));

    $itm_defect[$i] = $defect_array[rand(0,sizeof($defect_array)-1)];
    if($itm_defect[$i]) {
        $itm_status[$i] = $defect_type_array[rand(0,sizeof($defect_type_array)-1)];
    }
    else {
        $itm_status[$i] = 'finish';
    }
	
	$sql1 = "INSERT INTO {$g5['item_table']} SET
                com_idx	= '11'
                , ori_idx	= '".rand(1,40)."'
                , bom_idx	= '".$bom['bom_idx']."'
                , oop_idx	= '".$oop['oop_idx']."'
                , shf_idx	= '0'
                , bom_part_no	= '".$bom['bom_part_no']."'
                , itm_name = '".addslashes($bom['bom_name'])."'
                , itm_barcode = ''
                , itm_com_barcode = ''
                , plt_idx = ''
                , itm_lot = ''
                , itm_price = '".$bom['bom_price']."'
                , itm_defect = ''
                , itm_defect_type = ''
                , trm_idx_location = '53'
                , itm_shift = '".rand(1,10)."'
                , itm_history = '".$itm_status[$i]."|".$itm_dt[$i]."'
                , itm_status = '".$itm_status[$i]."'
                , itm_date = '".substr($itm_dt[$i],0,10)."'
                , itm_reg_dt = '".$itm_dt[$i]."'
                , itm_update_dt = '".$itm_dt[$i]."'
    ";
    if($demo) {echo $sql1.'<br>';}
    else {sql_query($sql1,1);}
	

    echo "<script> document.all.cont.innerHTML += '".$cnt.". ".$bom['bom_name']." ".$itm_status[$i]." <br>'; </script>".PHP_EOL;

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
sql_query("TRUNCATE g5_1_item_sum",1);
$sql = "INSERT INTO g5_1_item_sum (com_idx, itm_date, itm_shift, trm_idx_line, bom_idx, bom_part_no, itm_price, itm_status, itm_count)
        SELECT itm.com_idx, itm_date, itm_shift, trm_idx_line, oop.bom_idx, bom_part_no, itm_price, itm_status
        , COUNT(itm_idx) AS itm_count
        FROM g5_1_item AS itm
            LEFT JOIN g5_1_order_out_practice AS oop ON oop.oop_idx = itm.oop_idx
            LEFT JOIN g5_1_order_practice AS orp ON orp.orp_idx = oop.orp_idx
        WHERE itm_status NOT IN ('trash','delete')
            AND itm_date != '0000-00-00'
        GROUP BY itm_date, trm_idx_line, itm_shift, bom_idx, itm_status
        ORDER BY itm_date ASC, trm_idx_line, itm_shift, bom_idx, itm_status
";
sql_query($sql,1);

?>
<script>
    document.all.cont.innerHTML += "<br><br>총 <?=number_format($cnt)?>건 작업 완료<br><br><font color=crimson><b>[끝]</b></font>";
    document.body.scrollTop += 1000;
</script>
</body>
</html>