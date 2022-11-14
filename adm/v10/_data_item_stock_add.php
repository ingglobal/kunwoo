<?php
$sub_menu = "985325";
include_once('./_common.php');

$g5['title'] = '완제품 재고 추가';
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
1) 우선 보광 com_idx(11)의 BOM 완성품 목록수 만큼 루프를 돌린다
2) 해당 BOM(P/NO) 목록상에서 자식 루프갯수를 랜덤으로 설정(자식루프 수만큼 재고품목을 생성해야 한다.)
3) 자식 루프 돌면서 부모(P/NO)의 루프수만큼 완성품 재고데이터를 생성한다.
*/

//보광 com_idx(8)의 BOM 완성품 목록을 추출
$sql = " SELECT bom_idx,com_idx,bct_id,bom_name,bom_part_no FROM {$g5['bom_table']}
            WHERE com_idx = '{$_SESSION['ss_com_idx']}'
                AND bom_type = 'product'
                AND bom_status = 'ok'
";
$result = sql_query($sql,1);
//print_r2($result);exit;

$cnt = 0;
//echo rand(30,120);exit;
for($i=0;$row=sql_fetch_array($result);$i++){
    $cnt++;
    
    //부모 루프 개별 데이터 설정 작업
    $item_cnt = rand(20,70);

    
    for($j=0;$j<$item_cnt;$j++){
        //자식 루프 개별 데이터 설정 작업
        $shf_rand_sql = " SELECT shf_idx FROM {$g5['shift_table']} WHERE com_idx = '{$_SESSION['ss_com_idx']}'
            AND shf_status = 'ok' ORDER BY RAND() LIMIT 1
        ";
        $shf_idxs = sql_fetch($shf_rand_sql);
        $sql_common = " INSERT {$g5['item_table']} SET
            com_idx = '{$_SESSION['ss_com_idx']}'
            ,ori_idx = ''
            ,bom_idx = '{$row['bom_idx']}'
            ,oop_idx = ''
            ,shf_idx = '{$shf_idxs['shf_idx']}'
            ,mb_id = '{$member['mb_id']}'
            ,bom_part_no = '{$row['bom_part_no']}'
            ,itm_name = '{$row['bom_name']}'
            ,itm_barcode = ''
            ,itm_com_barcode = ''
            ,itm_plt = ''
            ,itm_lot = ''
            ,itm_defect = ''
            ,itm_defect_type = ''
            ,trm_idx_location = '53'
            ,itm_history = ''
            ,itm_status = 'finish'
            ,itm_reg_dt = '".G5_TIME_YMDHIS."'
            ,itm_update_dt = '".G5_TIME_YMDHIS."'
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