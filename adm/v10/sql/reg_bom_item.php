<?php
include_once('./_head.sub.php');

$r_tblname = 'g5_0_item30';
$b_tblname = $g5['bom_table'];
$t_tblname = $g5['bom_item_table'];

//item정보추출
$rec_sql = " SELECT * FROM {$r_tblname} ";
$rec_res = sql_query($rec_sql);
$con_arr = array();
$allData = array();
$a3_arr = array();
$a2_arr = array();
$a1_arr = array();
$a0_arr = array();
$a0_cd = '';
$b0_idx = '';
$bom_sql = " SELECT bom_idx FROM {$b_tblname} WHERE bom_part_no = ";
for($i=0; $rrow=sql_fetch_array($rec_res); $i++){
    // if($i < 464) continue;
    // if($i == 475) break;

    //완제품일때
    if($rrow['itm_cd']){
        $a0_arr = array();
        $a1_arr = array();
        $a2_arr = array();
        $a3_arr = array();
        $a0_cd = '';
        $b0_idx = '';
        $bom0_res = sql_fetch(" {$bom_sql} '{$rrow['itm_cd']}' ");
        $p_arr = array(
            'bom_idx' => $bom0_res['bom_idx']
            ,'itm_cd' => $rrow['itm_cd']
            ,'itm_name' => $rrow['itm_name_std']
            ,'itm_level' => 0
            ,'itm_kg' => $rrow['itm_kg']
            ,'itm_parent' => ''
            ,'itm_bom_parent' => ''
            ,'itm_count' => 1
            ,'itm_num' => -1
            ,'itm_son' => array()
        );
        $a0_arr = $p_arr;
        $a0_cd = $rrow['itm_cd'];
        $b0_idx = $bom0_res['bom_idx'];
        // $wt0_sql = " UPDATE {$b_tblname} SET bom_weight = '{$rrow['itm_kg']}' WHERE bom_idx = '{$bom0_res['bom_idx']}' ";
        // sql_query($wt0_sql,1);
    }
    //원자제일때
    if($rrow['itm_level'] == '3'){
        $bom3_res = sql_fetch(" {$bom_sql} '{$rrow['itm_low_cd']}' ");
        $arr3 = array(
            'bom_idx' => $bom3_res['bom_idx']
            ,'itm_cd' => $rrow['itm_low_cd']
            ,'itm_name' => $rrow['itm_low_name']
            ,'itm_level' => 3
            ,'itm_kg' => 0.0
            ,'itm_parent' => ''
            ,'itm_bom_parent' => ''
            ,'itm_count' => 1
            ,'itm_num' => 0
        );
        array_push($a3_arr,$arr3);
    }
    //원자재 또는 중간반제품일때
    if($rrow['itm_level'] == '2'){
        $bom2_res = sql_fetch(" {$bom_sql} '{$rrow['itm_low_cd']}' ");
        $arr2 = array(
            'bom_idx' => $bom2_res['bom_idx']
            ,'itm_cd' => $rrow['itm_low_cd']
            ,'itm_name' => $rrow['itm_low_name']
            ,'itm_level' => 2
            ,'itm_kg' => count($a3_arr)?$rrow['itm_kg']:0
            ,'itm_parent' => ''
            ,'itm_bom_parent' => ''
            ,'itm_count' => 1
            ,'itm_num' => 0
            ,'itm_son' => array()
        );
        if(count($a3_arr)){
            for($j=0;$j<count($a3_arr);$j++){
                $a3_arr[$j]['itm_parent'] = $rrow['itm_low_cd'];
                $a3_arr[$j]['itm_bom_parent'] = $bom2_res['bom_idx'];
                $a3_arr[$j]['itm_num'] = -($j+1);
            }
            $arr2['itm_son'] = $a3_arr;
        }
        array_push($a2_arr,$arr2);
        // $wt2_sql = " UPDATE {$b_tblname} SET bom_weight = '{$arr2['itm_kg']}' WHERE bom_idx = '{$bom2_res['bom_idx']}' ";
        // sql_query($wt2_sql,1);
    }
    //직전반제품일때
    if($rrow['itm_level'] == '1'){
        $bom1_res = sql_fetch(" {$bom_sql} '{$rrow['itm_low_cd']}' ");
        $arr1 = array(
            'bom_idx' => $bom1_res['bom_idx']
            ,'itm_cd' => $rrow['itm_low_cd']
            ,'itm_name' => $rrow['itm_low_name']
            ,'itm_level' => 1
            ,'itm_kg' => $rrow['itm_kg']
            ,'itm_parent' => $a0_cd
            ,'itm_bom_parent' => $b0_idx
            ,'itm_count' => 1
            ,'itm_num' => -(count($a0_arr['itm_son'])+1)
            ,'itm_son' => array()
        );
        if(count($a2_arr)){
            for($j=0;$j<count($a2_arr);$j++){
                $a2_arr[$j]['itm_parent'] = $rrow['itm_low_cd'];
                $a2_arr[$j]['itm_bom_parent'] = $bom1_res['bom_idx'];
                $a2_arr[$j]['itm_num'] = -($j+1);
            }
            $arr1['itm_son'] = $a2_arr;
        }
        $a1_arr = $arr1;
        array_push($a0_arr['itm_son'],$a1_arr);
        array_push($allData,$a0_arr);
        // $wt1_sql = " UPDATE {$b_tblname} SET bom_weight = '{$arr1['itm_kg']}' WHERE bom_idx = '{$bom1_res['bom_idx']}' ";
        // sql_query($wt1_sql,1);
    }
}
// print_r2($allData);
?>
<div class="btn_box">
    <a href="<?=G5_USER_ADMIN_SQL_URL?>" class="btn btn_04 btn_start">SQL홈</a><br><br>
    <a href="<?=G5_USER_ADMIN_SQL_URL?>/reg_bom_item.php?start=1" class="btn btn_02 btn_start">[시작]</a>
</div>
<div id="cont"></div>
<?php
// if(false){
if($start == 1 && count($allData)){
    $countgap = 10; //몇건씩 보낼지 설정
    $sleepsec = 10000; //백만분에 몇초간 쉴지 설정(20000/1000000=0.02)(10000/1000000=0.01)(5000/1000000=0.005)
    $maxscreen = 30; // 몇건씩 화면에 보여줄건지 설정

    //bom_item 테이블을 텅비우고 초기화 한다.
    $truncate_sql = " TRUNCATE {$t_tblname} ";
    sql_query($truncate_sql,1);

    flush();
    ob_flush();

    $tcnt = 0;
    for($i=0;$i<count($allData);$i++){
        $son0 = $allData[$i]['itm_son'];
        for($j=0;$j<count($son0);$j++){ //1루프
            $son1 = $son0[$j];
            $sql1 = " INSERT INTO {$t_tblname} SET
                bom_idx = '{$son1['itm_bom_parent']}'
                ,bom_idx_child = '{$son1['bom_idx']}'
                ,bit_count = '{$son1['itm_count']}'
                ,bit_num = '{$son1['itm_num']}'
                ,bit_reg_dt = '".G5_TIME_YMDHIS."'
                ,bit_update_dt = '".G5_TIME_YMDHIS."'
            ";
            sql_query($sql1,1);
            if(count($son1['itm_son'])){//2차자식 있니?
                for($k=0;$k<count($son1['itm_son']);$k++){//2루프
                    $son2 = $son1['itm_son'][$k];
                    $sql2 = " INSERT INTO {$t_tblname} SET
                        bom_idx = '{$son2['itm_bom_parent']}'
                        ,bom_idx_child = '{$son2['bom_idx']}'
                        ,bit_count = '{$son2['itm_count']}'
                        ,bit_num = '{$son2['itm_num']}'
                        ,bit_reg_dt = '".G5_TIME_YMDHIS."'
                        ,bit_update_dt = '".G5_TIME_YMDHIS."'
                    ";
                    sql_query($sql2,1);
                    if(count($son2['itm_son'])){//3차자식 있니?
                        for($l=0;$l<count($son2['itm_son']);$l++){//3루프
                            $son3 = $son2['itm_son'][$l];
                            $sql3 = " INSERT INTO {$t_tblname} SET
                                bom_idx = '{$son3['itm_bom_parent']}'
                                ,bom_idx_child = '{$son3['bom_idx']}'
                                ,bit_count = '{$son3['itm_count']}'
                                ,bit_num = '{$son3['itm_num']}'
                                ,bit_reg_dt = '".G5_TIME_YMDHIS."'
                                ,bit_update_dt = '".G5_TIME_YMDHIS."'
                            ";
                            sql_query($sql3,1);
                        }//#3루프(종료)
                    }//#3차자식 있니?(종료)
                }//#2루프(종료)
            }//#2차자식 있니?(종료)
        }//#1루프(종료)


        $tcnt++;
        echo "<script>document.getElementById('cont').innerHTML += '".$tcnt."개 - ".$con_arr[$i]['cos_name']." - 처리됨<br>';</script>\n";

        flush();
        ob_flush();
        ob_end_flush();
        usleep($sleepsec);
    
        //보기 쉽게 묶음 단위로 구분 (단락으로 구분해서 보임)
        if($tcnt % $countgap == 0){
            echo "<script>document.getElementById('cont').innerHTML += '<br>';</script>\n";
        }
    
        //화면 정리! 부하를 줄임 (화면을 싹 지움)
        if($tcnt % $maxscreen == 0){
            echo "<script>document.getElementById('cont').innerHTML = '';</script>\n";
        }
    }
//if($start == 1 && count($allData)) //############################ 작업종료 ##############
?>
<script>
document.getElementById('cont').innerHTML += "<br><br>총 <?php echo number_format($tcnt); ?>건 완료<br><br><font color='crimson'><b>[끝]</b></font>";
</script>
<?php
}
else{
    echo '<div class="display_empty">[시작]버튼을 누르면 작업이 실행됩니다.</div>';
}

include_once('./_tail.sub.php');