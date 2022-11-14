<?php
$sub_menu = "985320";
include_once('./_common.php');

$g5['title'] = '완제품 재고 생성';
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

//초기 데이터 설정 작업


$cnt = 0;

$result = 1000;
for($i=0;$i<$result;$i++){
    $cnt++;
    
    //부모 루프 개별 데이터 설정 작업

    $sub_cnt = 0;
    $sub_result = 100;

    for($j=0;$j<$sub_result;$j++){
        $sub_cnt++;
        //자식 루프 개별 데이터 설정 작업
        
    }


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