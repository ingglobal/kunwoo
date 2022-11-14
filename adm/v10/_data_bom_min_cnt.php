<?php
/*
이 페이지는 해당업체의 완성품이 아닌 자재에 해당하는 BOM레코드에 알림최소재고수량을 설정하기 위한 페이지이다.
*/
$sub_menu = "985315";
include_once('./_common.php');

$g5['title'] = 'BOM재고최소수량데이터입력';
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

$countgap = 10; //몇건씩 보낼지 설정
$sleepsec = 10000; //백만분의 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
$maxscreen = 50; // 몇건씩 화면에 보여줄건지 설정

flush();
ob_flush();

//초기 데이터 설정 작업

$mat_cnt_arr = [500,600,700,1000]; //자재재고의 최소수량
$prd_cnt_arr = [50,60,70,100]; //완성품재고의 초소수량
//echo $mat_cnt_arr[rand(0,count($mat_cnt_arr)-1)]."<br>";
//echo $prd_cnt_arr[rand(0,count($prd_cnt_arr)-1)];


$sql = " SELECT bom_idx FROM {$g5['bom_table']}
            WHERE com_idx = '{$_SESSION['ss_com_idx']}'
                AND bom_status = 'ok'
                AND bom_type = 'material'
";

$result = sql_query($sql,1);
$cnt = 0;
//print_r2($result);
for($i=0;$row=sql_fetch_array($result);$i++){
    $cnt++;

    //루프 개별 데이터 설정 작업
    $bom_min_cnt = $mat_cnt_arr[rand(0,count($mat_cnt_arr)-1)];
    $rsql = " UPDATE {$g5['bom_table']} SET bom_min_cnt = {$bom_min_cnt} WHERE bom_idx = {$row['bom_idx']} ";
    sql_query($rsql,1);
    echo "<script>document.all.cont.innerHTML += '{$cnt} == {$rsql} - 처리됨<br>';</script>\n";

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
