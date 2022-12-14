<?php
$sub_menu = "930100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');
//erp - personal_carexp_month_list파일 참고
$g5['title'] = '생산실행계획(날짜/설비별)';
// include_once('./_top_menu_orp.php');
include_once('./_head.php');
include_once('./_top_menu_practice.php');
$yoil = array('일','월','화','수','목','금','토');
// $forge_arr = $g5['trms']['forge_arr'];
$forge_arr = array();
$date_range = array();
// $show_days = 7;//기본 표시 기간(일수)
$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">기본목록</a>';


// $start_date = '2022-11-15';
// $end_date = '2022-11-20';

if($start_date && $end_date){//검색 시작일과 종료일이 있을때 -> 검색범위만큼 표시
    //검색 기간이 일주일 즉 7일 이상인지 확인
    $diff_day_count = date_diff(new DateTime($start_date),new DateTime($end_date))->d;
    $chk_days_flag = ($diff_day_count >= 6) ? 1 : 0;
    //검색 기간이 7일 미만이면 검색취소됨
    // if(!$chk_days_flag) $end_date = get_dayAddDate($start_date,6);
    $end_date = get_dayAddDate($start_date,6);

    $first_date = $start_date;
    $last_date = $end_date;  
}
else if($start_date && !$end_date){//검색 시작일만 있을때 -> 시작일로부터 일주일(7일)간 표시
    $first_date = $start_date;
    $last_date = get_dayAddDate($start_date,6);
}
else if(!$start_date && $end_date){//검색 종료일만 있을때 -> 종료일까지로 포함해서 이전 일주일(7일)간 표시
    $first_date = get_dayAddDate($end_date,-6);
    $last_date = $end_date;
}
else{//검색일이 아무것도 없을때 -> 오늘날짜가 속한 주의 일요일부터 토요일까지 표시
    $today_date = G5_TIME_YMD;
    //오늘날짜 주간의 일요일(첫째날)을 찾자
    $minus_days = ' -'.date('w',strtotime($today_date)).'days';
    $first_date = date('Y-m-d', strtotime($today_date.$minus_days));
    //오늘날짜 주간의 토요일(마지막날)을 찾자
    $plus_days = ' +'.(6-date('w',strtotime($today_date))).'days';
    $last_date = date('Y-m-d', strtotime($today_date.$plus_days));
}


$diff_days = date_diff(new DateTime($first_date),new DateTime($last_date))->d;
for($i=0;$i<=$diff_days;$i++){
    $date_str = get_dayAddDate($first_date,$i);
    $yoil_str = $yoil[date('w',strtotime($date_str))];
    $dy_arr = array(
        'date' => $date_str
        ,'yoil' => $yoil_str
        ,'day' => $date_str.'('.$yoil_str.')'
    );
    array_push($date_range,$dy_arr);
}

if($date_range[0]['yoil'] == '일'){ //기존 첫번째 날짜의 요일이 일요일이면 
    $prev_week_date = get_dayAddDate($date_range[0]['date'],-7);
    $next_week_date = get_dayAddDate($date_range[0]['date'],7);
}
else{ //기존 첫번째 날짜의 요일이 일요일 아니라면
    //첫째날짜 주간의 일요일(첫째날)을 찾자
    $minus_day_num = ' -'.date('w',strtotime($date_range[0]['date'])).'days';
    $prev_week_date = date('Y-m-d', strtotime($date_range[0]['date'].$minus_day_num));
    $next_week_date = get_dayAddDate($prev_week_date,7);
}
// print_r2($date_range);
//mmg_idx = 2; //절단동 그룹 번호는 2번
//mmg_idx = 3; //단조동 그룹 번호는 3번
$mms_sql = " SELECT mms_idx,mmg_idx,mms_idx2,mms_name,mms_model,mms_sort FROM {$g5['mms_table']} WHERE mmg_idx = '3' AND mms_status = 'ok' AND com_idx = '{$g5['setting']['set_com_idx']}' ORDER BY mms_sort,mms_idx2 ";
$mms_res = sql_query($mms_sql,1);
for($mms_row=0;$mms_row=sql_fetch_array($mms_res);$mms_row++){
    foreach($date_range as $drv){
        $mms_row['orp_arr'][$drv['date']] = array();
    }
    $forge_arr[$mms_row['mms_idx']] = $mms_row;
}

// print_r3(end($forge_arr)['mms_sort']);
//임의의 외주단조를 $forge_arr 마지막단에 추가하자
$add_ex_forge = array(
    'mms_idx' => 0
    ,'mmg_idx' => 0
    ,'mms_idx2' => 0
    ,'mms_name' => '단조외주'
    ,'mms_model' => 'out_forge'
    ,'mms_sort' => end($forge_arr)['mms_sort']+1
    ,'orp_arr' => array()
);
//임의의 외주절단과 외주단조 $forge_arr 제일 마지막단에 추가하자
$add_ex_all = array(
    'mms_idx' => 0
    ,'mmg_idx' => 0
    ,'mms_idx2' => 0
    ,'mms_name' => 'ALL외주'
    ,'mms_model' => 'out_all'
    ,'mms_sort' => end($forge_arr)['mms_sort']+2
    ,'orp_arr' => array()
);
foreach($date_range as $drv){
    $add_ex_forge['orp_arr'][$drv['date']] = array();
    $add_ex_all['orp_arr'][$drv['date']] = array();
}
$forge_arr['0'] = $add_ex_forge;
$forge_arr['-1'] = $add_ex_all;
// print_r3($forge_arr);

$sql_common = " AND orp_start_date >= '{$first_date}' 
                AND orp_start_date <= '{$last_date}'
";
$sql_common_child = " AND ooc_date >= '{$first_date}' 
                AND ooc_date <= '{$last_date}'
";
$sql = " SELECT oop.oop_idx
                , oop.ori_idx
                , oop.bom_idx
                , oop.orp_idx
                , oop.mtr_bom_idx
                , oop.oop_count
                , oop.oop_memo
                , oop.oop_onlythis_yn
                , oop.oop_1
                , oop.oop_2
                , oop.oop_status
                , orp.cut_mms_idx
                , orp.cut_mb_id
                , orp.forge_mms_idx
                , orp.forge_mb_id
                , orp.trm_idx_line
                , ooc.ooc_date AS orp_start_date
                , orp.orp_done_date
                , bom.bom_name
                , bom.bom_part_no
                , bom.bom_std
                , bom.bom_press_type
                , 'child' AS orp_relation
                , ooc_day_night
                , ( SELECT bom_name FROM {$g5['bom_table']} WHERE bom_idx = mtr_bom_idx ) AS mtr_bom_name
                , ( SELECT bom_part_no FROM {$g5['bom_table']} WHERE bom_idx = mtr_bom_idx ) AS mtr_bom_part_no
                , ( SELECT bom_std FROM {$g5['bom_table']} WHERE bom_idx = mtr_bom_idx ) AS mtr_bom_std
                , cut.mms_name AS cut_mms_name
                , cut.mms_model AS cut_mms_model
                , forge.mms_name AS forge_mms_name
                , forge.mms_model AS forge_mms_model
                FROM {$g5['order_oop_child_table']} ooc
                LEFT JOIN {$g5['order_out_practice_table']} oop ON ooc.oop_idx = oop.oop_idx
                LEFT JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
                LEFT JOIN {$g5['mms_table']} cut ON orp.cut_mms_idx = cut.mms_idx
                LEFT JOIN {$g5['mms_table']} forge ON orp.forge_mms_idx = forge.mms_idx
                LEFT JOIN {$g5['bom_table']} bom ON oop.bom_idx = bom.bom_idx
                WHERE oop_status NOT IN('del','delete','trash','cancel')
                {$sql_common_child} 

         UNION

         SELECT oop.oop_idx
                , oop.ori_idx
                , oop.bom_idx
                , oop.orp_idx
                , oop.mtr_bom_idx
                , oop.oop_count
                , oop.oop_memo
                , oop.oop_onlythis_yn
                , oop.oop_1
                , oop.oop_2
                , oop.oop_status
                , orp.cut_mms_idx
                , orp.cut_mb_id
                , orp.forge_mms_idx
                , orp.forge_mb_id
                , orp.trm_idx_line
                , orp.orp_start_date
                , orp.orp_done_date
                , bom.bom_name
                , bom.bom_part_no
                , bom.bom_std
                , bom.bom_press_type
                , 'parent' AS orp_relation
                , '' AS ooc_day_night
                , ( SELECT bom_name FROM {$g5['bom_table']} WHERE bom_idx = mtr_bom_idx ) AS mtr_bom_name
                , ( SELECT bom_part_no FROM {$g5['bom_table']} WHERE bom_idx = mtr_bom_idx ) AS mtr_bom_part_no
                , ( SELECT bom_std FROM {$g5['bom_table']} WHERE bom_idx = mtr_bom_idx ) AS mtr_bom_std
                , cut.mms_name AS cut_mms_name
                , cut.mms_model AS cut_mms_model
                , forge.mms_name AS forge_mms_name
                , forge.mms_model AS forge_mms_model
            FROM {$g5['order_out_practice_table']} oop
            LEFT JOIN {$g5['order_practice_table']} orp ON oop.orp_idx = orp.orp_idx
            LEFT JOIN {$g5['mms_table']} cut ON orp.cut_mms_idx = cut.mms_idx
            LEFT JOIN {$g5['mms_table']} forge ON orp.forge_mms_idx = forge.mms_idx
            LEFT JOIN {$g5['bom_table']} bom ON oop.bom_idx = bom.bom_idx
        WHERE oop_status NOT IN('del','delete','trash','cancel')
            {$sql_common}
            ORDER BY orp_start_date,orp_idx
";
// echo $sql;
$result = sql_query($sql,1);
$total_count = $result->num_rows;
// print_r2($result);
for($row=0;$row=sql_fetch_array($result);$row++){
    if($row['orp_relation'] == 'child'){
        $row['oop_1'] = '';
        $row['oop_2'] = '';
        $row['oop_1'] = ($row['ooc_day_night'] == 'D' || $crow['ooc_day_night'] == 'A')?1:0;
        $row['oop_2'] = ($row['ooc_day_night'] == 'N' || $crow['ooc_day_night'] == 'A')?1:0;
    }
    if($row['forge_mms_idx'] && $row['oop_count']) //각 단조설비별로 분류
        array_push($forge_arr[$row['forge_mms_idx']]['orp_arr'][$row['orp_start_date']],$row);
    else if(!$row['forge_mms_idx'] && $row['cut_mms_idx'] && $row['oop_count']) //절단내부, 단조외주
        array_push($forge_arr['0']['orp_arr'][$row['orp_start_date']],$row);
    else if(!$row['forge_mms_idx'] && !$row['cut_mms_idx'] && $row['oop_count']) //절단외주, 단조외주
        array_push($forge_arr['-1']['orp_arr'][$row['orp_start_date']],$row);
}
// echo $qstr;
$qstr .= '&calendar=1&start_date='.$first_date.'&end_date='.$last_date; // 추가로 확장해서 넘겨야 할 변수들
// echo $qstr;
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/function.date.js"></script>', 0);
// print_r2($forge_arr);
?>
<style>
.local_sch{position:relative;}
.sch_dv{position:relative;padding-bottom:20px;}
.sch_label{position:relative;}
.sch_label span{position:absolute;top:-23px;left:5px;z-index:2;}
.sch_label .date_blank{position:absolute;top:-21px;right:0px;z-index:2;font-size:1.1em;cursor:pointer;}
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:5px;z-index:2;}

#dv_title{margin-top:20px;position:relative;}
#dv_title h1{font-size:1.5em;}
#dv_title h1 span{font-size:0.7em;}
#dv_title .ul_btn{position:absolute;bottom:10px;right:10px;}
#dv_title .ul_btn:after{display:block;visibility:hidden;clear:both;content:'';}
#dv_title .ul_btn li{float:left;}
#dv_title .ul_btn li a{display:block;width:40px;height:40px;line-height:45px;text-align:center;border:1px solid #888;}
#dv_title .ul_btn li a i{font-size:2em;}
#dv_title .ul_btn li:first-child{margin-right:10px;}
.tbl_head01 thead th{position:sticky;top:127px;z-index:100;font-size:1.1em;}
.tbl_head01 thead th.th_today{background:#B33771 !important;}
.tbl_head01 tbody td.td_mms{width:110px !important;}
.tbl_head01 tbody td.td_dnn{width:45px !important;}
.tbl_head01 tbody td.td_cell{padding:10px;width:12% !important;position:relative;}
.tbl_head01 tbody td.td_cell .orp_add{position:absolute;bottom:5px;right:5px;display:block;width:24px;height:24px;line-height:20px;text-align:center;border-radius:50%;background:rgba(0,0,0,0.6);}
.tbl_head01 tbody td.td_cell .orp_add img{}
.tbl_head01 tbody td.td_mms,
.tbl_head01 tbody td.td_n{border-bottom:2px solid #888;}
.tbl_head01 tbody td.td_d{border-bottom:1px solid #555;}
.tbl_head01 tbody td .dv_cell{min-height:100px;}
.tbl_head01 tbody td.td_today{background:#6D214F;}
.tbl_head01 tbody td.td_today_n{background:#4B112D;}
.dv_item{position:relative;text-align:left;padding:5px;border:1px solid #2980b9;background:#1e3799;border-radius:5px;margin-top:5px;}
.dv_item.dv_child{background:#1e2c1c;}
.dv_item.dv_stop{border:1px solid #4c4e56;background:#383a40;opacity:0.7;}
.dv_item:first-child{margin-top:0px;}
.dv_item p{text-overflow:ellipsis;overflow:hidden;white-space:nowrap;height:23px;line-height:23px;}
.dv_item .p_info{cursor:pointer;}
.dv_item p.p_name{color:orange;}
.dv_item p.p_no{color:yellow;font-size:0.9em;}
.dv_item p.p_std{color:pink;font-size:0.95em;}
.dv_item p.p_mtr{color:white;font-size:0.9em;}
.dv_item p.p_memo{color:yellow;font-weight:700;font-size:0.9em;}
.dv_item span{}
.dv_item span.s_cnt{position:absolute;display:block;top:5px;right:5px;border:0px solid #711320;background:rgba(77, 11, 59,0.7);padding:3px 5px;border-radius:4px;}
.dv_item .oop_son{position:absolute;bottom:40px;right:5px;display:block;width:30px;height:30px;line-height:36px;text-align:center;border-radius:50%;background:rgba(0,0,0,0.4);cursor:pointer;}
.dv_item .orp_mod{position:absolute;bottom:5px;right:5px;display:block;width:30px;height:30px;line-height:32px;text-align:center;border-radius:50%;background:rgba(0,0,0,0.4);}
</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>
<?php
echo $g5['container_sub_title'];
// print_r2($date_range);
?>
<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <div class="sch_dv">생산시작일의 검색범위는 무조건 일주일간(7일간)의 범위로 설정됩니다.</div>
    <label for="start_date" class="sch_label">
        <span>검색시작일</span>
        <i class="fa fa-times date_blank" aria-hidden="true"></i>
        <input type="text" name="start_date" value="<?php echo $first_date ?>" id="start_date" readonly class="frm_input readonly" placeholder="검색시작일" style="width:100px;" autocomplete="off">
    </label>
    ~
    <label for="end_date" class="sch_label">
        <span>검색최종일</span>
        <i class="fa fa-times date_blank" aria-hidden="true"></i>
        <input type="text" name="end_date" value="<?php echo $last_date ?>" id="end_date" readonly class="frm_input readonly" placeholder="검색최종일" style="width:100px;" autocomplete="off">
    </label>
    <input type="submit" class="btn_submit" value="검색">
</form>
<div id="pdf_box">
    <div id="dv_title">
        <h1><?=$g5['title']?> <span>( <?=G5_TIME_YMD?> )</span></h1>
        <ul class="ul_btn">
            <li><a href="./order_out_practice_calendar_list.php?start_date=<?=$prev_week_date?>" class="prev_week"><span class="sound_only">이전주</span><i class="fa fa-angle-left" aria-hidden="true"></i></a></li>
            <li><a href="./order_out_practice_calendar_list.php?start_date=<?=$next_week_date?>" class="next_week"><span class="sound_only">다음주</span><i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
        </ul>
    </div>
    <div class="tbl_head01 tbl_wrap">
        <table class="table table-bordered table-condensed">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
        <tr>
            <th scope="col">설비명</th>
            <th scope="col">주/야</th>
            <?php foreach($date_range as $dr_v){ ?>
            <th scope="col" class="<?=(($dr_v['date']==G5_TIME_YMD)?'th_today':'')?>"><?=$dr_v['day']?></th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($forge_arr as $forge){
            if($forge['mms_model'] == 'out_forge')
                $forge_title = '절단내부';
            else if($forge['mms_model'] == 'out_all')
                $forge_title = '절단외주<br>단조외주';
            else
                $forge_title = $forge['mms_sort'].'호기';
        ?>
        <tr>
            <td class="td_mms" rowspan="2" style="width:110px;"><?=$forge_title?><br>(<?=$forge['mms_name']?>)</td>
            <td class="td_dnn td_d">주간</td>
            <?php foreach($forge['orp_arr'] as $d => $forge_val){ ?>
            <td class="td_cell td_d td_day<?=(($d==G5_TIME_YMD)?' td_today':'')?>" date="<?=$d?>">
            <div class="dv_cell dv_d dv_day">
                <?php if(count($forge_val)){ ?>
                <?php for($i=0;$i<count($forge_val);$i++){ ?>
                    <?php if($forge_val[$i]['oop_1']){ 
                        $forge_val[$i]['mtr_bom_str'] = ($forge_val[$i]['mtr_bom_std'])?$forge_val[$i]['mtr_bom_name'].'('.$forge_val[$i]['mtr_bom_std'].')':$forge_val[$i]['mtr_bom_name'];
                        $forge_val[$i]['cut_mms_name_str'] = ($forge_val[$i]['cut_mms_idx']) ? $forge_val[$i]['cut_mms_name'].'('.$forge_val[$i]['cut_mms_model'].')' : '절단외주작업';
                        $forge_val[$i]['forge_mms_name_str'] = ($forge_val[$i]['forge_mms_idx']) ? $forge_val[$i]['forge_mms_name'].'('.$forge_val[$i]['forge_mms_model'].')' : '단조외주작업';
                    ?>
                        <div class="dv_item<?=(($forge_val[$i]['oop_status'] != 'confirm')?' dv_stop':'')?><?=(($forge_val[$i]['child'])?' dv_child':'')?>"
                        oop_idx="<?=$forge_val[$i]['oop_idx']?>"
                        itm="<?=cut_str($forge_val[$i]['bom_name'],20,'...')?>"
                        no="<?=$forge_val[$i]['bom_part_no']?>"
                        std="<?=$forge_val[$i]['bom_std']?>"
                        press_type="<?=$g5['set_bom_press_type_value'][$forge_val[$i]['bom_press_type']]?>"
                        mtr="<?=$forge_val[$i]['mtr_bom_str']?>"
                        cut="<?=$forge_val[$i]['cut_mms_name_str']?>"
                        forge="<?=$forge_val[$i]['forge_mms_name_str']?>"
                        cnt_d="<?=@number_format($forge_val[$i]['oop_1'])?>"
                        cnt_n="<?=@number_format($forge_val[$i]['oop_2'])?>"
                        cnt="<?=@number_format($forge_val[$i]['oop_count'])?>"
                        date="<?=$forge_val[$i]['orp_start_date']?>"
                        onlythis="<?=$g5['set_noyes_value'][$forge_val[$i]['oop_onlythis_yn']]?>"
                        memo="<?=cut_str(trim(strip_tags($forge_val[$i]['oop_memo'])),30,'...')?>"
                        status="<?=$g5['set_oop_status_value'][$forge_val[$i]['oop_status']]?>">
                            <p class="p_info p_name"><?=cut_str($forge_val[$i]['bom_name'],20,'...')?></p>
                            <p class="p_info p_no"><?=$forge_val[$i]['bom_part_no']?></p>
                            <p class="p_info p_std"><?=$forge_val[$i]['bom_std']?></p>
                            <p class="p_info p_press_type"><?=$g5['set_bom_press_type_value'][$forge_val[$i]['bom_press_type']]?></p>
                            <p class="p_info p_mtr">
                                <?=cut_str($forge_val[$i]['mtr_bom_str'],12,'...')?>
                            </p>
                            <p class="p_info p_onlythis">단일단조 : <?=$g5['set_noyes_value'][$forge_val[$i]['oop_onlythis_yn']]?></p>
                            <?php if(trim(strip_tags($forge_val[$i]['oop_memo']))){ ?>
                            <p class="p_info p_memo"><?=cut_str(trim(strip_tags($forge_val[$i]['oop_memo'])),12,'...')?></p>
                            <?php } ?>
                            <?php if(!$forge_val[$i]['child'] && $forge_val[$i]['oop_1']){ ?>
                            <span class="p_info s_cnt"><?=number_format($forge_val[$i]['oop_count'])?></span>
                            <?php } ?>
                            <?php if(true){ //if($is_admin){ //if(true){ ?>
                            <span class="p_clone oop_son"><?=svg_icon('clone','svg_edit',20,20,'#ffffff')?></span>
                            <?php } ?>
                            <a href="./order_out_practice_form.php?<?=$qstr?>&w=u&oop_idx=<?=$forge_val[$i]['oop_idx']?>" class="orp_mod" title="계획수정"><?=svg_icon('edit','svg_edit',20,20,'#ffffff')?></a>
                        </div>
                    <?php } //if($forge_val[$i]['oop_1']) ?>
                    <?php } //for($i=0;$i<count($forge_val);$i++) ?>
                <?php } //if(count($forge_val)) ?>
            </div>
            </td>
            <?php } ?>
        </tr>
        <tr>
            <td class="td_dnn td_n">야간</td>
            <?php foreach($forge['orp_arr'] as $d => $forge_val){ ?>
            <td class="td_cell td_n td_night<?=(($d==G5_TIME_YMD)?' td_today_n':'')?>" date="<?=$d?>">
            <div class="dv_cell dv_n dv_night">
                <?php if(count($forge_val)){ ?>
                <?php for($i=0;$i<count($forge_val);$i++){ ?>
                    <?php if($forge_val[$i]['oop_2']){ 
                        $forge_val[$i]['mtr_bom_str'] = ($forge_val[$i]['mtr_bom_std'])?$forge_val[$i]['mtr_bom_name'].'('.$forge_val[$i]['mtr_bom_std'].')':$forge_val[$i]['mtr_bom_name'];
                        $forge_val[$i]['cut_mms_name_str'] = ($forge_val[$i]['cut_mms_idx']) ? $forge_val[$i]['cut_mms_name'].'('.$forge_val[$i]['cut_mms_model'].')' : '절단외주작업';
                        $forge_val[$i]['forge_mms_name_str'] = ($forge_val[$i]['forge_mms_idx']) ? $forge_val[$i]['forge_mms_name'].'('.$forge_val[$i]['forge_mms_model'].')' : '단조외주작업';
                    ?>
                        <div class="dv_item<?=(($forge_val[$i]['oop_status'] != 'confirm')?' dv_stop':'')?><?=(($forge_val[$i]['child'])?' dv_child':'')?>"
                        oop_idx="<?=$forge_val[$i]['oop_idx']?>"
                        itm="<?=cut_str($forge_val[$i]['bom_name'],20,'...')?>"
                        no="<?=$forge_val[$i]['bom_part_no']?>"
                        std="<?=$forge_val[$i]['bom_std']?>"
                        press_type="<?=$g5['set_bom_press_type_value'][$forge_val[$i]['bom_press_type']]?>"
                        mtr="<?=$forge_val[$i]['mtr_bom_str']?>"
                        cut="<?=$forge_val[$i]['cut_mms_name_str']?>"
                        forge="<?=$forge_val[$i]['forge_mms_name_str']?>"
                        cnt_d="<?=@number_format($forge_val[$i]['oop_1'])?>"
                        cnt_n="<?=@number_format($forge_val[$i]['oop_2'])?>"
                        cnt="<?=@number_format($forge_val[$i]['oop_count'])?>"
                        date="<?=$forge_val[$i]['orp_start_date']?>"
                        onlythis="<?=$g5['set_noyes_value'][$forge_val[$i]['oop_onlythis_yn']]?>"
                        memo="<?=trim(strip_tags($forge_val[$i]['oop_memo']))?>"
                        status="<?=$g5['set_oop_status_value'][$forge_val[$i]['oop_status']]?>">
                            <p class="p_info p_name"><?=cut_str($forge_val[$i]['bom_name'],20,'...')?></p>
                            <p class="p_info p_no"><?=$forge_val[$i]['bom_part_no']?></p>
                            <p class="p_info p_std"><?=$forge_val[$i]['bom_std']?></p>
                            <p class="p_info p_press_type"><?=$g5['set_bom_press_type_value'][$forge_val[$i]['bom_press_type']]?></p>
                            <p class="p_info p_mtr">
                                <?=cut_str($forge_val[$i]['mtr_bom_str'],12,'...')?>
                            </p>
                            <p class="p_info p_onlythis">단일단조 : <?=$g5['set_noyes_value'][$forge_val[$i]['oop_onlythis_yn']]?></p>
                            <?php if(trim(strip_tags($forge_val[$i]['oop_memo']))){ ?>
                            <p class="p_info p_memo"><?=cut_str(trim(strip_tags($forge_val[$i]['oop_memo'])),12,'...')?></p>
                            <?php } ?>
                            <?php if(!$forge_val[$i]['child'] && !$forge_val[$i]['oop_1']){ ?>
                            <span class="p_info s_cnt"><?=number_format($forge_val[$i]['oop_count'])?></span>
                            <?php } ?>
                            <?php if(true){ //if($is_admin){ //if(true){ ?>
                            <span class="p_clone oop_son"><?=svg_icon('clone','svg_edit',20,20,'#ffffff')?></span>
                            <?php } ?>
                            <a href="./order_out_practice_form.php?<?=$qstr?>&w=u&oop_idx=<?=$forge_val[$i]['oop_idx']?>" class="orp_mod" title="계획수정"><?=svg_icon('edit','svg_edit',20,20,'#ffffff')?></a>
                        </div>
                    <?php } //if($forge_val[$i]['oop_2']) ?>
                <?php } //for($i=0;$i<count($forge_val);$i++) ?>
                <?php } //if(count($forge_val)) ?>
            </div>
            </td>
            <?php } ?>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</div>
<div class="btn_fixed_top">
    <?php if (!auth_check($auth[$sub_menu],'w')) { ?>
    <a href="./order_out_practice_form.php?calendar=1" id="member_add" class="btn btn_01">생산계획추가</a>
    <?php } ?>
</div>
<script>
$("input[name=start_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){
    $("input[name=end_date]").val(addDate(selectedDate,6));
}});

$("input[name=end_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect:function(selectedDate){
    $("input[name=start_date]").val(addDate(selectedDate,-6));
}});

$('.p_info').on('click',function(){
    var plan = $(this).parent();
    var oop_idx = plan.attr('oop_idx');
    var itm = plan.attr('itm');
    var no = plan.attr('no');
    var std = plan.attr('std');
    var press_type = plan.attr('press_type');
    var mtr = plan.attr('mtr');
    var cut = plan.attr('cut');
    var forge = plan.attr('forge');
    var cnt_d = plan.attr('cnt_d');
    var cnt_n = plan.attr('cnt_n');
    var cnt = plan.attr('cnt');
    var date = plan.attr('date');
    var onlythis = plan.attr('onlythis');
    var memo = plan.attr('memo');
    var status = plan.attr('status');
    $('#modal').removeClass('mdl_hide');
    $('.h1_item').text(itm);
    $('.sp_oop_idx').text(oop_idx);
    $('.sp_no').text(no);
    $('.sp_std').text(std);
    $('.sp_press_type').text(press_type);
    $('.sp_mtr').text(mtr);
    $('.sp_cut').text(cut);
    $('.sp_forge').text(forge);
    // $('.sp_cnt_d').text(cnt_d);
    // $('.sp_cnt_n').text(cnt_n);
    $('.sp_cnt').text(cnt);
    $('.sp_date').text(date);
    $('.sp_onlythis').text(onlythis);
    $('.sp_memo').text(memo);
    $('.sp_status').text(status);
    modal_event_on();
});

$('.p_clone').on('click',function(){
    var plan = $(this).parent();
    var parent_date = plan.attr('date');
    var oop_idx = plan.attr('oop_idx');
    var itm = plan.attr('itm');
    var no = plan.attr('no');
    var std = plan.attr('std');
    $('#modal_clone').removeClass('mdl_hide');
    $('.cp_parent_date').text(parent_date);
    $('.h1_item').text(itm+' [날짜추가복제]');
    $('.cp_oop_idx').text(oop_idx);
    $('.cp_no').text(no);
    $('.cp_std').text(std);
    $('input[name="oop_idx"]').val(oop_idx)
    modal_clone_event_on();
});

function modal_event_on(){
    $('.mdl_bg,.mdl_close').on('click',function(){
        $('.h1_item').text("");
        $('.sp_oop_idx').text("");
        $('.sp_no').text("");
        $('.sp_std').text("");
        $('.sp_press_type').text("");
        $('.sp_mtr').text("");
        $('.sp_cut').text("");
        $('.sp_forge').text("");
        // $('.sp_cnt_d').text("");
        // $('.sp_cnt_n').text("");
        $('.sp_cnt').text("");
        $('.sp_date').text("");
        $('.sp_onlythis').text("");
        $('.sp_memo').text("");
        $('.sp_status').text("");
        $('#modal').addClass('mdl_hide');
        modal_event_off();
    });
}

function modal_clone_event_on(){
    $('.mdl_bg,.mdl_close').on('click',function(){
        $('.cp_parent_date').text("");
        $('.h1_item').text("");
        $('.cp_oop_idx').text("");
        $('.cp_no').text("");
        $('.cp_std').text("");
        $('input[name="oop_idx"]').val("")
        $('input[name="ooc_date"]').val("")
        $('#modal_clone').addClass('mdl_hide');
        modal_clone_event_off();
    });
}

function modal_event_off(){
    $('.mdl_bg,.mdl_close').off('click');
}

function modal_clone_event_off(){
    $('.mdl_bg,.mdl_close').off('click');
}
</script>
<?php
include_once ('./_tail.php');
?>