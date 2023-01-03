<?php
$sub_menu = "955400";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

if($sum_reload){
    update_item_sum2();
    unset($sum_reload);
    unset($_GET['sum_reload']);
    Header("Location:./kpi_uph.php"); 
}
// 변수 설정, 필드 구조 및 prefix 추출
$qstr .= '&ser_mms_idx='.$ser_mms_idx.'&st_date='.$st_date.'&en_date='.$en_date.'&st_time='.$st_time.'&en_time='.$en_time; // 추가로 확장해서 넘겨야 할 변수들

// st_date, en_date
$st_date = $st_date ?: date("Y-m-01",G5_SERVER_TIME);
$st_date = date("Y-m-d H:i:s",strtotime("-1month",strtotime($st_date)));//작업후에 반드시 주석처리해라
$en_date = $en_date ?: date("Y-m-d");
$st_time = $st_time ?: '00:00:00';
$en_time = $en_time ?: '23:59:59';
$st_timestamp = strtotime($st_date.' '.$st_time);
$en_timestamp = strtotime($en_date.' '.$en_time);


$g5['title'] = 'UPH(시간당생산) 보고서';
include_once('./_top_menu_kpi.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// Get all the mms_idx values to make them options for selection.
$sql2 = "   SELECT mms_idx, mms_name
            FROM {$g5['mms_table']}
            WHERE com_idx = '".$_SESSION['ss_com_idx']."'
            ORDER BY mms_idx
";
// echo $sql2.'<br>';
$result2 = sql_query($sql2,1);
for ($i=0; $row2=sql_fetch_array($result2); $i++) {
    // print_r2($row2);
    $mms[$row2['mms_idx']] = $row2['mms_name'];
}

//g5_1_data_measure_디비 시리즈에서 테이블명 끝에 54번으로 시작하는 것만 추출
$sql = "SELECT table_name, table_rows, auto_increment
            , SUBSTRING_INDEX (SUBSTRING_INDEX(table_name,'_',-3), '_', 1) AS mms_idx
            , SUBSTRING_INDEX (SUBSTRING_INDEX(table_name,'_',-2), '_', 1) AS dta_type
            , SUBSTRING_INDEX (SUBSTRING_INDEX(table_name,'_',-1), '_', 1) AS dta_no
        FROM Information_schema.tables
        WHERE TABLE_SCHEMA = '".G5_MYSQL_DB."'
            AND TABLE_NAME LIKE 'g5_1_data_measure_".$g5['setting']['set_uph_mms']."%'
        ORDER BY convert(mms_idx, decimal), convert(dta_type, decimal), convert(dta_no, decimal)
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r2($row);
    // echo ($i+1).'<br>';
    $row['ar'] = explode("_",$row['table_name']);
    // print_r2($row);
    // print_r2($row['ar']);
    // 해당 업체 것만 추출, 아니면 통과
    if($mms[$row['ar'][4]]) {
        $ser_mms_idx = ($ser_mms_idx) ?: $row['ar'][4];
    }
}
// echo $ser_mms_idx.'<br>';
// exit;
if(!$ser_mms_idx)
    alert('설비정보가 존재하지 않습니다.');


// Get the mmi_nos for each mms
$sql = "SELECT mms_idx, mmi_no, mmi_name
        FROM {$g5['mms_item_table']}
        WHERE mmi_status = 'ok'
        GROUP BY mms_idx, mmi_no
        ORDER BY mms_idx, mmi_no
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r3('설비: '.$row['mms_idx'].' - '.$row['mmi_no'].'--------------------------');
    $mms_mmi[$row['mms_idx']][] = $row['mmi_no'];
    $mmi_name[$row['mms_idx']][$row['mmi_no']] = $row['mmi_name'];
}
// print_r3($mms_mmi);
// print_r2($mmi_name);



// 공제 get offwork time
// 전체기간 설정이 있는 경우는 마지막 부분에서 돌면서 없는 날짜 목표를 채워줍니다.
$sql = "SELECT mms_idx
        , off_idx
        , off_period_type
        , off_start_time AS db_off_start_time
        , off_end_time AS db_off_end_time
        , FROM_UNIXTIME(off_start_time,'%Y-%m-%d %H:%i:%s') AS db_off_start_ymdhis
        , FROM_UNIXTIME(off_end_time,'%Y-%m-%d %H:%i:%s') AS db_off_end_ymdhis
        , GREATEST('".$st_timestamp."', off_start_time ) AS off_start_time
        , LEAST('".$en_timestamp."', off_end_time ) AS off_end_time
        , FROM_UNIXTIME( GREATEST('".$st_timestamp."', off_start_time ) ,'%Y-%m-%d %H:%i:%s') AS off_start_ymdhis
        , FROM_UNIXTIME( LEAST('".$en_timestamp."', off_end_time ) ,'%Y-%m-%d %H:%i:%s') AS off_end_ymdhis
        FROM {$g5['offwork_table']}
        WHERE com_idx = '".$_SESSION['ss_com_idx']."'
            AND off_status IN ('ok')
            AND off_end_time >= '".$st_timestamp."'
            AND off_start_time <= '".$en_timestamp."'
            AND mms_idx IN (".$ser_mms_idx.",0)
        ORDER BY mms_idx DESC, off_period_type, off_start_time
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
$byunit = 86400;
$offdaily = array();
$offsomed = array();
for($i=0;$row=sql_fetch_array($rs);$i++){
    // print_r2($row);
    $offwork[$i]['mms_idx'] = $row['mms_idx'];
    $offwork[$i]['off_period_type'] = $row['off_period_type'];
    $offwork[$i]['start'] = date("H:i:s",$row['db_off_start_time']);
    $offwork[$i]['end'] = date("H:i:s",$row['db_off_end_time']);
    //특정날짜의 비가동
    if(!$row['off_period_type']){
        $offwork[$i]['start_day'] = date("Y-m-d",$row['db_off_start_time']); 
        $offwork[$i]['end_day'] = date("Y-m-d",$row['db_off_end_time']); 
        // $offwork[$i]['days'] = date_gapdays_times($offwork[$i]['start_day'],$offwork[$i]['end_day'],$offwork[$i]['start'],$offwork[$i]['end']);       
        $offsomed = date_gapdays_times($offwork[$i]['start_day'],$offwork[$i]['end_day'],$offwork[$i]['start'],$offwork[$i]['end']);       
    }
    //매일비가동
    else{
        array_push($offdaily,array('start'=>$offwork[$i]['start'],'end'=>$offwork[$i]['end']));
    }
}
// print_r2($offsomed);
// print_r2($offdaily);

$sql_common = " FROM {$g5['item_table']} ";

$where = array();
$where[] = " mms_idx = '".$ser_mms_idx."' AND itm_status NOT IN ('delete','del','trash') ";

// 기간 검색
if ($st_date) {
    $where[] = " itm_date >= '".$st_date."' ";
}
if ($en_date) {
    $where[] = " itm_date <= '".$en_date."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

$sql = " SELECT SQL_CALC_FOUND_ROWS mms_idx, bom_part_no, itm_date, oop_idx
    , COUNT(itm_idx) AS output_sum
    , MIN(itm_reg_dt) AS itm_ymdhis_min
    , MAX(itm_reg_dt) AS itm_ymdhis_max
    {$sql_common}
    {$sql_search}
GROUP BY itm_date
ORDER BY itm_date DESC
";

// echo $sql;
$result = sql_query($sql,1);

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi1.css">', 1);
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/function.date.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/timepicker/jquery.timepicker.css">', 0);
?>
<script type="text/javascript" src="<?=G5_USER_ADMIN_URL?>/js/timepicker/jquery.timepicker.js"></script>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_mms_idx" id="ser_mms_idx">
    <?php
    // 해당 범위 안의 모든 설비를 select option으로 만들어서 선택할 수 있도록 한다.
    // Get all the mms_idx values to make them optionf for selection.
    $sql2 = "SELECT mms_idx, mms_name
            FROM {$g5['mms_table']}
            WHERE com_idx = '".$_SESSION['ss_com_idx']."' AND mms_status = 'ok' AND mmg_idx = '{$g5['setting']['set_uph_mmg']}'
            ORDER BY mms_sort
    ";
    // echo $sql2.'<br>';
    $result2 = sql_query($sql2,1);
    for ($i=0; $row2=sql_fetch_array($result2); $i++) {
        // print_r2($row2);
        echo '<option value="'.$row2['mms_idx'].'" '.get_selected($ser_mms_idx, $row2['mms_idx']).'>'.$row2['mms_name'].'</option>';
    }
    ?>
</select>
<script>$('select[name=ser_mms_idx]').val("<?=$ser_mms_idx?>").attr('selected','selected');</script>

<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:95px;">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:95px;">
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="submit" class="btn_submit" value="검색">
</form>


<div class="local_desc01 local_desc" style="display:no ne;">
    <p>설비를 선택하시고 파트번호 또는 구간번호를 입력하시고 검색하세요.</p>
    <p>공제시간 및 비가동 시간은 해당 페이지에서 설정해 주시기 바랍니다.</p>
</div>
<div class="tbl_head01 tbl_wrap">
    <table id="sodr_list">
    <caption>목록</caption>
    <thead>
    <tr>
        <th scope="col">날짜</th>
        <th scope="col">생산량</th>
        <th scope="col">시작시간</th>
        <th scope="col">종료시간</th>
        <th scope="col">작업시간(분)</th>
        <th scope="col">공제시간(분)</th>
        <th scope="col">실작업시간(시)</th>
        <th scope="col" style="display:none;">비가동시간(시)</th>
        <th scope="col" style="display:none;">SPH(비가동포함)</th>
        <th scope="col">UPH</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $row['itm_start_his'] = preg_replace("/:/","",substr($row['itm_ymdhis_min'],11));
        $row['itm_end_his'] = preg_replace("/:/","",substr($row['itm_ymdhis_max'],11));
        $row['itm_ymdhis_max_display'] = $row['itm_ymdhis_max'];
        // 작업시간 합계 (초)
        $row['worktime'] = strtotime($row['itm_ymdhis_max']) - strtotime($row['itm_ymdhis_min']);
        //작업시간 합계 (분)
        $row['workmin'] = round($row['worktime'] / 60);
        
        // 공제시간 합계 (초)
        $offtime_seconds = offtime_result($row['itm_ymdhis_min'],$row['itm_ymdhis_max'],$offdaily,($offsomed[$row['itm_date']])?$offsomed[$row['itm_date']]:array());
        //공제시간 합계 (분)
        $row['offworkmin'] = round($offtime_seconds / 60);
        //실제작업시간 합계(분)
        $row['workrealmin'] = $row['workmin'] - $row['offworkmin'];
        //실제작업시간 합계(시간)
        $row['workhour'] = round($row['workrealmin'] / 60);
        
        // 링크
        $row['ahref'] = '<a href="?'.$qstr.'&sfl=dta_mmi_no&stx='.$row['dta_mmi_no'].'">';

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?> tr_<?=$row['dmn_status']?>">
        <td><?=$row['itm_date']?></td><!-- 날짜 -->
        <td class="td_right pr_10"><?=number_format($row['output_sum'])?></td><!-- 생산수량(타) -->
        <td><?=substr($row['itm_ymdhis_min'],5)?></td><!-- 시작시간 -->
        <td><?=substr($row['itm_ymdhis_max_display'],5)?></td><!-- 종료시간 -->
        <td><?=$row['workmin']?></td><!-- 작업시간(분) -->
        <td><?=$row['offworkmin']?></td><!-- 공제(분) -->
        <td><?=$row['workrealmin']?> (<?=$row['workhour']?>)</td><!-- 실작업시간(시) -->
        <td style="display:none;"><?=$row['downtimemin']?> (<?=$row['downtimehour']?>)</td><!-- 비가동시간(시) -->
        <td style="display:none;">
            <?php
                $workdata = ($row['workhour'] <= 0) ? 0 : round($row['output_sum']/$row['workhour'],2);
            ?>
            <?=$workdata?>
        </td><!-- SPH(비가동포함) -->
        <td>
            <?php
                $realdata = ($row['workhour']-$row['downtimehour'] <= 0) ? 0 : round($row['output_sum']/($row['workhour']-$row['downtimehour']),2);
            ?>
            <?=number_format($realdata)?>
        </td><!-- SPH(비가동제외) -->
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="12" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>
<div class="btn_fixed_top" style="display:none;">
    <a href="./<?=$g5['file_name']?>.php?sum_reload=1" class="btn btn_02">리로드</a>
</div>
<script>
$(function(e) {
    // timepicker 설정
    $("input[name$=_time]").timepicker({
        'timeFormat': 'H:i:s',
        'step': 10
    });

    // st_date chage
    $(document).on('focusin', 'input[name=st_date]', function(){
        // console.log("Saving value: " + $(this).val());
        $(this).data('val', $(this).val());
    }).on('change','input[name=st_date]', function(){
        var prev = $(this).data('val');
        var current = $(this).val();
        // console.log("Prev value: " + prev);
        // console.log("New value: " + current);
        if(prev=='') {
            $('input[name=st_time]').val('00:00:00');
        }
    });
    // en_date chage
    $(document).on('focusin', 'input[name=en_date]', function(){
        $(this).data('val', $(this).val());
    }).on('change','input[name=en_date]', function(){
        var prev = $(this).data('val');
        if(prev=='') {
            $('input[name=en_time]').val('23:59:59');
        }
    });

    $("input[name$=_date]").datepicker({
        closeText: "닫기",
        currentText: "오늘",
        monthNames: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
        monthNamesShort: ["1월","2월","3월","4월","5월","6월", "7월","8월","9월","10월","11월","12월"],
        dayNamesMin:['일','월','화','수','목','금','토'],
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        //maxDate: "+0d"
    });

    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
        },
        mouseleave: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }
    });

});
</script>

<?php
include_once ('./_tail.php');
?>
