<?php
$sub_menu = "985350";
include_once('./_common.php');

$g5['title'] = '고객처 재고 생성';
include(G5_PATH.'/head.sub.php');
?>
<div class="" style="padding:10px;">
    <span>
        작업시작~~ <font color="crimson"><b>[끝]</b></font> 이라는 단어가 나오기 전 중간에 중지하지 마세요.
    </span><br><br>
    <span id="cont"></span>
</div>
<?php
include(G5_PATH.'/tail.sub.php');

//필요한 함수 정의


$countgap = 10; // 몇건씩 보낼지 설정
$sleepsec = 10000; //백만분에 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
$maxscreen = 50; // 몇건씩 화면에 보여줄건지 설정

flush();
ob_flush();

//테이블 초기화
$truncate_sql = " TRUNCATE {$g5['guest_stock_table']} ";
sql_query($truncate_sql,1);

// 수주상품 목록을 추출
$sql = " SELECT ori.ori_idx, ori.bom_idx, ord.com_idx, ori.com_idx_customer, ord.ord_date FROM {$g5['order_item_table']} AS ori
                    LEFT JOIN {$g5['order_table']} AS ord ON ori.ord_idx = ord.ord_idx
                WHERE ori.ori_status NOT IN ('cancel','trash','del','delete')
                    AND ori.com_idx = '{$_SESSION['ss_com_idx']}'
";

$result = sql_query($sql,1);

$cnt = 0;

for($i=0;$row=sql_fetch_array($result);$i++){
    $cnt++;

    $stock_cnt = rand(20,300);
    $sql_common = " INSERT {$g5['guest_stock_table']} SET
        com_idx = '{$_SESSION['ss_com_idx']}'
        ,com_idx_customer = '{$row['com_idx_customer']}'
        ,bom_idx = '{$row['bom_idx']}'
        ,gst_count = '{$stock_cnt}'
        ,gst_date = '{$row['ord_date']}'
        ,gst_status = 'ok'
        ,gst_reg_dt = '".G5_TIME_YMDHIS."'
        ,gst_update_dt = '".G5_TIME_YMDHIS."'
    ";
    sql_query($sql_common,1);

    echo "<script>document.all.cont.innerHTML += '".$cnt." - 처리됨<br>';</script>\n";

    flush();
    ob_flush();
    ob_end_flush();
    usleep($sleepsec);

    //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
    if($cnt % $countgap == 0){
        echo "<script>document.all.cont.innerHTML += '<br>';</script>\n";
    }

    //화면 정리! 부하를 줄임 (화면을 싹 지움)
    if($cnt % $maxscreen == 0){
        echo "<script>document.all.cont.innerHTML = '';</script>\n";
    }
}
?>
<script>
    document.all.cont.innerHTML += "<br><br>총 <?php echo number_format($i); ?>건 완료<br><br><font color='crimson'><b>[끝]</b></font>";
</script>