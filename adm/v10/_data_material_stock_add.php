<?php
$sub_menu = "985340";
include_once('./_common.php');

$g5['title'] = '자재품 재고 추가';
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


//재고 데이터 생성
/*
1) 우선 보광 com_idx(8)의 BOM 자재품 목록수 만큼 루프를 돌린다
2) 해당 BOM(P/NO) 목록상에서 자식 루프갯수를 랜덤으로 설정(자식루프 수만큼 재고품목을 생성해야 한다.)
3) 자식 루프 돌면서 부모(P/NO)의 루프수만큼 자재품 재고데이터를 생성한다.
*/

//보광 com_idx(8)의 BOM 완성품 목록을 추출
$sql = " SELECT bom_idx,com_idx,bct_id,bom_name,bom_part_no FROM {$g5['bom_table']}
            WHERE com_idx = '{$_SESSION['ss_com_idx']}'
                AND bom_type = 'material'
                AND bom_status = 'ok'
";
$result = sql_query($sql,1);
//print_r2($result);exit;

$cnt = 0;
//echo rand(30,120);exit;
for($i=0;$row=sql_fetch_array($result);$i++){
    $cnt++;
    
    //부모 루프 개별 데이터 설정 작업
    $material_cnt = rand(30,90);

    
    for($j=0;$j<$material_cnt;$j++){
        //자식 루프 개별 데이터 설정 작업
        $sql_common = " INSERT {$g5['material_table']} SET
            com_idx = '{$_SESSION['ss_com_idx']}'
            ,bom_idx = '{$row['bom_idx']}'
            ,moi_idx = ''
            ,oop_idx = ''
            ,itm_idx = ''
            ,bom_part_no = '{$row['bom_part_no']}'
            ,mtr_name = '{$row['bom_name']}'
            ,mtr_barcode = ''
            ,mtr_lot = ''
            ,mtr_price = ''
            ,mtr_defect = ''
            ,mtr_defect_type = ''
            ,trm_idx_location = '52'
            ,mtr_history = ''
            ,mtr_status = 'stock'
            ,mtr_reg_dt = '".G5_TIME_YMDHIS."'
            ,mtr_update_dt = '".G5_TIME_YMDHIS."'
        ";
        sql_query($sql_common,1);
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