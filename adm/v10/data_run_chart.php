<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'data_run';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_chart/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '가동데이터 그래프';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// mms_idx 디폴트
$mms_idx = ($_REQUEST['mms_idx']) ?: 1;
$mms = get_table_meta('mms','mms_idx',$mms_idx);
$com = get_table_meta('company','com_idx',$mms['com_idx']);

$dta_group = ($_REQUEST['dta_group']) ?: 'product';
$dta_value_type = ($_REQUEST['dta_value_type']) ?: 'sum';
$graph_type = ($_REQUEST['graph_type']) ?: 'line';

// 그룹 선택에 따른 초기 ser1, ser2 설정 (user.07.intra.default.php 참조)
$ser1 = ($ser1) ?: $g5['set_graph_'.$dta_group]['default0'];
$ser2 = ($ser2) ?: $g5['set_graph_'.$dta_group]['default1'];
$ser2 = ($ser2) ?: 1;   // ser2가 환경설정값에도 없으면 1로 디폴트 설정
if($ser1!='minute'&&$ser1!='second') // 분,초가 아니면 무조건 1
    $ser2 = 1;
//echo $ser1.'<br>';
//echo $ser2.'<br>';
// 분초로 바뀌면 기본적으로 5가 선택!!


$where = array();
$where[] = " mms_idx = '".$mms_idx."' AND dta_group = '".$dta_group."' AND ".$pre."_status NOT IN (1) ";   // 디폴트 검색조건

// dta_type 조건
if($_REQUEST['dta_type']) 
    $where[] = " dat_type = '".$_REQUEST['dta_type']."' ";
    
// dta_no 조건
if($_REQUEST['dta_no']) 
    $where[] = " dat_no = '".$_REQUEST['dta_no']."' ";
    
// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


//1. 처음로딩(종료일이 없는 경우) 해당 조건에 맞는 값이 존재하는 제일 마지막 날짜를 추출해서 종료일자로 설정해 둔다.
if(!$en_date) {
    $en1 = sql_fetch("SELECT * FROM {$g5_table_name} {$sql_search}
            ORDER BY dta_dt DESC LIMIT 1 
    ");
//    print_r2($en1);
    $en_date = date("Y-m-d",$en1['dta_dt']);
    $en_time = date("H:i:s",$en1['dta_dt']);
}

//2. 시작일, 종료일 둘 다 있으면
//        시작일부터 600개가 종료 설정값을 넘어가면 자동으로 줄여버림
//        안 넘어가면 설정한 값이 종료일자.
if($st_date && $en_date) {
    $st_timestamp = strtotime($st_date.' '.$st_time);
    $en_timestamp = strtotime($en_date.' '.$en_time);
    $seconds[$ser1][1] = ($seconds[$ser1][1]) ?: $ser2;// 단위 선택값이 없으면 폼에서 선택된 값을 참조
    // 분(60초)*단위(60개)*600개(환경설정값) [분선택, 60개 선택인 경우]
    // 초(1초)*단위(10개)*600개 [초선택,10개 선택인 경우]
    // 달(2592000초)*단위(무조건 1개)*600개 [초선택,10개 선택인 경우]
    $en_timestamp_max = $st_timestamp + ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['setting']['set_graph_max']);
    if($en_timestamp > $en_timestamp_max) {
        $en_date = date("Y-m-d",$en_timestamp_max);
        $en_time = date("H:i:s",$en_timestamp_max);
    }
}

//3. 종료일만 있으면
//        종료일에서부터 검색항목별 설정값(daily,1,30 = 일별,1일단위,30일치 등..)을 계산한 후
//        시작일자로 설정을 해 준다.
if(!$st_date && $en_date) {
    $en_timestamp = strtotime($en_date.' '.$en_time);
    $seconds[$ser1][1] = ($seconds[$ser1][1]) ?: $ser2;// 단위 선택값이 없으면 폼에서 선택된 값을 참조
//    $st_timestamp = $en_timestamp - ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['setting']['set_graph_max']); // 일별인 경우 -600일이 너무 커서 변경
//    echo $g5['set_graph_'.$dta_group]['default2'].'<br>';
    $st_timestamp = $en_timestamp - ($seconds[$ser1][0]*$seconds[$ser1][1]*$g5['set_graph_'.$dta_group]['default2']);
    $st_date = date("Y-m-d",$st_timestamp);
    $st_time = date("H:i:s",$st_timestamp);
}


// 최종 날짜 조건
$start = strtotime($st_date.' '.$st_time);
$end = strtotime($en_date.' '.$en_time);
//echo $st_date.' '.$st_time.'~'.$en_date.' '.$en_time.'<br>';
//echo $start.'~'.$end.'<br>';

// 끝자리 단위값 조정
$byunit = $seconds[$ser1][0]*$ser2;
//echo $byunit.'초 단위<br>';
$ix1 = floor($start/$byunit);   // 시작값은 내림으로 (애매한 소수점 처리를 위해)
$ix2 = ceil($end/$byunit);  // 종료값을 올림으로
$idx1 = $ix1*$byunit; // 다시 단위값을 곱해서 timestamp로 변환
$idx2 = $ix2*$byunit;
$dt1 = date("Y-m-d H:i:s",$idx1); // 끝자리 처리한 후 시작일시
$dt2 = date("Y-m-d H:i:s",$idx2); // 종료일시
//echo '시작: '.$idx1.' / '.date("Y-m-d H:i:s",$idx1).'<br>';   //-------------------------------------
//echo '끝: '.$idx2.' / '.date("Y-m-d H:i:s",$idx2).'<br>';    //-------------------------------------


// 생산량 추출
$sql = "SELECT dta_dt, FROM_UNIXTIME(dta_dt,'%Y-%m-%d %H:%i:%s') AS dta_date
            , SUM(dta_value) AS dta_value_sum
            , (dta_dt DIV ".$byunit.") AS dta_divided
            , (dta_dt DIV ".$byunit.")*".$byunit." AS dta_made_timestamp
            , FROM_UNIXTIME((dta_dt DIV ".$byunit.")*".$byunit.",'%Y-%m-%d %H:%i:%s') AS dta_made_dt
        FROM {$g5_table_name} {$sql_search}
            AND dta_dt >= '".$idx1."'
            AND dta_dt <= '".$idx2."'
        GROUP BY dta_dt DIV ".$byunit."
        ORDER BY dta_dt ASC
";
//echo $sql.'<br>';
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
//    print_r2($row);
//    echo $row['dta_made_dt'].'<br>';
    $val1 = preg_replace("/[ :-]/","",$row['dta_made_dt']); // 날짜중에서 숫자만 추출
//    echo $val1.'<br>';
    $dta1[$val1] = $row['dta_value_sum'];
}
//print_r2($dta1);

// 교대 및 생산목표 추출
$sql = "SELECT shf_idx, shf_range_1, shf_range_2, shf_range_3
            , shf_target_1, shf_target_2, shf_target_3
          ,shf_start_dt
            , GREATEST('".$dt1."', shf_start_dt ) AS shf_start_dt
          ,shf_end_dt
            , LEAST('".$dt2."', shf_end_dt ) AS shf_end_dt
        FROM {$g5['shift_table']} 
        WHERE mms_idx = '".$mms_idx."'
            AND shf_status = 'ok'
            AND shf_end_dt >= '".$dt1."'
            AND shf_start_dt <= '".$dt2."'
        ORDER BY shf_start_dt
";
//echo $sql.'<br>';
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
//    print_r2($row);
    for($j=1;$j<=4;$j++) {
        $row['range'][$j] = $row['shf_range_'.$j];
        $row['target'][$j] = $row['shf_target_'.$j];
        
        // 교대 시작~종료 시간 분리 배열
        $row['shift'][$j] = explode("~",$row['range'][$j]);
//        echo $j.'교대: '.$row['shift'][$j][0].' ~ ';             // ------------------
//        echo $row['shift'][$j][1].'<br>';                       // ------------------
    }
//    echo '---- 교대시간 추출해 둔 후 하단에서 배열생성 ----------<br>';  // ------------------
//    print_r2($row);
//    print_r2($row['shift']);
//    echo $row['shf_idx'].'. '.$row['shf_start_dt'].' ~ '.$row['shf_end_dt'].'<br>'; // 교대 시간 범위
    $ts1 = strtotime($row['shf_start_dt']);    // 시작 timestamp
    // 시작지점 재설정 (단위 간격이 있으므로 아무데서나 시작하면 해당배열이 존재하지 않아서 표현이 안 되요.)
    $ts1 = ceil($ts1/$byunit);   // 시작값은 내림으로 (애매한 소수점 처리를 위해)
    $ts1 = $ts1*$byunit; // 다시 단위값을 곱해서 timestamp로 변환
    $ts2 = strtotime($row['shf_end_dt']);    // 종료 timestamp
    // 날짜범위를 for 돌면서 해당시간을 찾아서 배열변수 생성
    $cnt2 = 0;
    for($k=$ts1;$k<=$ts2;$k+=$byunit) {
        $cnt2++;
//        echo $cnt2.'. '.date("Y-m-d H:i:s",$k).'<br>';                              // ------------------
//        echo $cnt2.'. '.preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$k)).'<br>';   // ------------------
        $val1 = preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$k)); // 날짜중에서 숫자만 추출하여 배열키값으로!
        $time1 = date("H:i:s",$k); // 날짜중에서 시간만 추출하여 교대값 찾기
        // $k값의 시간값 비교 (몇 교대인지 파악해서 목표값 할당)
        // 단, 교대종료시간이 24시가 넘는 익일을 포함한 교대수는 별도 계산해야 함
        for($x=1;$x<5;$x++) {
            if(!$row['shift'][$x][0])
                break;
            if($time1 >= $row['shift'][$x][0] && $time1 <= $row['shift'][$x][1]) { // 해당 교대 범위 안에 있으면
//                echo $x.'교대'.$row['shift'][$x][1].'<br>';
                $dta2[$val1] = $row['shf_target_'.$x];  // 해당 교대목표값 할당
                $shift2[$val1] = $row['shf_idx'].'_'.$x;  // 교대값 할당
            }
            // 1교대 시작 시간 이전이면서 24시간이 넘는 익일 종료시간인 경우
            if($time1<$row['shift'][1][0] && $row['shift'][$x][1]>'24:00:00') {
                $t1 = sprintf("%02d",substr($row['shift'][$x][1],0,2)-24); // 24시간을 뺀 시간
                $t2 = $t1.substr($row['shift'][$x][1],2);// 종료시간 재설정
                if($time1 >= '00:00:00' && $time1 <= $t2) { // 해당 교대 범위 안에 있으면
//                    echo $x.'교대'.$row['shift'][$x][1].'<br>';
                    $dta2[$val1] = $row['shf_target_'.$x];  // 해당 교대목표값 할당
                    $shift2[$val1] = $row['shf_idx'].'_'.$x;  // 교대값 할당
                }
//                echo $x.'넘는다............'.$t2.'....<br>';
            }
        }
    }
//    echo '<br>--------------<br>';
}
//print_r2($dta2);


// 좌표값(생산&목표)) 생성
$cnt=0;
for($i=$idx1;$i<=$idx2;$i+=$byunit) {
    $cnt++;
//    echo $cnt.'. '.date("Y-m-d H:i:s",$i).'<br>';
//    echo $cnt.'. '.preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$i)).'<br>';
    $val1 = preg_replace("/[ :-]/","",date("Y-m-d H:i:s",$i)); // 날짜중에서 숫자만 추출
    // 교대값이 바뀌면 누적값 초기화
    if($shift2[$val1] != $shift_old) {
        $acc = 0;
    }
    // 자바스크립트 내부에서 사용할 배열변수(숫자부분만) 생성: data: [1, 100, 220, 777, 800, 2, 440, 500, 1, 50, 888, 899], 
    // 생산
    $data1[] = ($dta1[$val1]) ?: 0;

    // 누적배열
    $acc += $dta1[$val1]; // 누적합계
    $acc1[] = $acc;

    // 목표
    $data2[] = ($dta2[$val1]) ?: 'null';
    
    // 이전 교대값 저장
    $shift_old = $shift2[$val1];
}
//print_r2($data1);
//print_r2($data2);

// 목표값은 null값이 있으므로 제외하고 max 추출해야 함
$max2 = array_diff($data2,array('null')); // max값을 추출하기 위해서 null값 전부 삭제
$max2 = array_values($max2);    //빈 index 다시 채우기
$targetmax = ($max2) ? max($max2):0;
$datamax = ($data1) ? max($data1):0;
$accmax = ($acc1) ? max($acc1):0;
$maxarr = array($targetmax,$datamax,$accmax);
//echo max($maxarr).'<br>';


// data 호출 URL 
$json_url = 'http://bogwang.epcs.co.kr/device/json/data.php?token=1099de5drf09&mms_idx='.$mms_idx
                .'&dta_group='.$dta_group.'&dta_type='.$dta_type.'&dta_no='.$dta_no.'&dta_value_type='.$dta_value_type
                .'&start_date='.$st_date.'&start_time='.$st_time.'&end_date='.$en_date.'&end_time='.$en_time;
//echo $json_url;

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_JS_URL.'/timepicker/jquery.timepicker.css">', 0);
?>
<script type="text/javascript" src="<?=G5_USER_JS_URL?>/timepicker/jquery.timepicker.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/highcharts.src.js"></script>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" style="width:80px;">
    <input type="text" name="st_time" value="<?=$st_time?>" id="st_time" class="frm_input" style="width:65px;">
    ~
    <input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" style="width:80px;">
    <input type="text" name="en_time" value="<?=$en_time?>" id="en_time" class="frm_input" style="width:65px;">

    <label for="ser1" class="sound_only">검색대상</label>
    <select name="ser1" id="ser1">
        <option value="daily"<?php echo get_selected($_GET['ser1'], "daily"); ?>>일별</option>
        <option value="weekly"<?php echo get_selected($_GET['ser1'], "weekly"); ?>>주간별</option>
        <option value="monthly"<?php echo get_selected($_GET['ser1'], "monthly"); ?>>월별</option>
        <option value="yearly"<?php echo get_selected($_GET['ser1'], "yearly"); ?>>연도별</option>
        <option value="minute"<?php echo get_selected($_GET['ser1'], "minute"); ?>>분</option>
        <option value="second"<?php echo get_selected($_GET['ser1'], "second"); ?>>초</option>
    </select>
    <script>$('select[name=ser1]').val("<?=$ser1?>").attr("selected","selected")</script>
    <div class="div_ser2" style="display:<?=($ser1=='minute'||$ser1=='second')?'inline-block':'none'?>;">
        <label class="ser2_radio"><input type="radio" name="ser2" value="5" id="ser2_5"<?=get_checked($ser2, "5")?>>5</label>
        <label class="ser2_radio"><input type="radio" name="ser2" value="10" id="ser2_10"<?=get_checked($ser2, "10")?>>10</label>
        <label class="ser2_radio"><input type="radio" name="ser2" value="20" id="ser2_20"<?=get_checked($ser2, "20")?>>20</label>
        <label class="ser2_radio"><input type="radio" name="ser2" value="30" id="ser2_30"<?=get_checked($ser2, "30")?>>30</label>
        <label class="ser2_radio"><input type="radio" name="ser2" value="60" id="ser2_60"<?=get_checked($ser2, "60")?>>60</label>
    </div>
    <script>
        // 분, 초 선택시 시간선택 보여줌
        $(document).on('change','select[name=ser1]',function(e){
            if( $(this).val()=='minute' || $(this).val()=='second' ) {
                $('.div_ser2').css('display','inline-block');
            }
            else {
                $('.div_ser2').hide();
                $('input[name=ser2]').closest('label').removeClass('active');
                $('input[name=ser2]:checked').attr('checked',false);
            }
        });
        $('input[name=ser2]:checked').closest('label').addClass('active');
        $(document).on('click','input[name=ser2]',function(e){
            $('input[name=ser2]').closest('label').removeClass('active');
            $('input[name=ser2]:checked').closest('label').addClass('active');
        });
    </script>

    <button type="submit" class="btn btn_01">검색</button>

    <div style="float:right;">
        mms
        <input type="text" name="mms_idx" value="<?=$mms_idx?>" class="frm_input" style="width:40px;">
        &nbsp;&nbsp;
        <select name="dta_group">
            <option value="">데이타그룹</option>
            <option value="run"<?php echo get_selected($_GET['dta_group'], "run"); ?>>가동시간</option>
            <option value="product"<?php echo get_selected($_GET['dta_group'], "product"); ?>>생산량</option>
        </select>
        <script>$('select[name=dta_group]').val("<?=$dta_group?>").attr("selected","selected")</script>
        <select name="dta_value_type">
            <option value="">값표현</option>
            <option value="sum"<?php echo get_selected($_GET['dta_value_type'], "sum"); ?>>합계</option>
            <option value="max"<?php echo get_selected($_GET['dta_value_type'], "max"); ?>>최고치</option>
            <option value="min"<?php echo get_selected($_GET['dta_value_type'], "min"); ?>>최저치</option>
            <option value="average"<?php echo get_selected($_GET['dta_value_type'], "average"); ?>>평균</option>
        </select>
        <script>$('select[name=dta_value_type]').val("<?=$dta_value_type?>").attr("selected","selected")</script>
        <select name="graph_type">
            <option value="">그래프종류</option>
            <option value="line"<?php echo get_selected($_GET['graph_type'], "line"); ?>>꺽은선</option>
            <option value="column"<?php echo get_selected($_GET['graph_type'], "column"); ?>>막대</option>
            <option value="pie"<?php echo get_selected($_GET['graph_type'], "pie"); ?>>파이차트</option>
        </select>
        <script>$('select[name=graph_type]').val("<?=$graph_type?>").attr("selected","selected")</script>
    </div>
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>그래프 좌표갯수 Max값은 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개</span>입니다. (로딩속도 최적화를 필요한 제약입니다.) 시간 범위를 크게 잡더라도 좌표에 표현하는 갯수는 자동으로 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개까지만</span> 표현됩니다. (시작시점 기준)</p>
</div>

<div class="graph_wrap">

    <div id="chart1" style="width:100%; height:400px;"></div>

    <script>
    Highcharts.chart('chart1', {
        chart: {
            zoomType: 'xy'
        },
        title: {
            text: '<?=$com['com_name']?> <?=$mms['mms_name']?> <?=$g5['set_data_group_value'][$dta_group]?>'
        },
        subtitle: {
            text: '<?=$st_date?>~<?=$en_date?>'
        },
        
        xAxis: {
            type: 'datetime',
            tickPixelInterval: 100,  // 이게 좀 애매하다. 뭔지 모르겠음!
        },
        plotOptions: {
            series: {
                // 시작점 (자바스크립트 날자에서 월은 -1을 해야 함)
                pointStart: Date.UTC(<?=date("Y",$idx1)?>, <?=(date("m",$idx1)-1)?>, <?=date("j",$idx1)?>, <?=date("H",$idx1)?>, <?=date("i",$idx1)?>, <?=date("s",$idx1)?>),
                pointInterval: <?=$byunit?> * 1000 // 5초 간격 (초단위*1000)
            }
        },        
        yAxis: [{ // Primary yAxis
            title: {
                text: '생산',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            labels: {
                format: '',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            max: <?=(max($maxarr)+1)?>   // y축 최대값
        }, { // Secondary yAxis
            title: {
                text: '목표',
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            labels: {
                format: '',
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            linkedTo: 0,    // 오른편에도 y값이 나타나게 설정
            opposite: true
        }],
        tooltip: {
            xDateFormat: '%Y-%m-%d %H:%M:%S',
            shared: true
        },
        series: [{
            name: '목표',
            type: 'spline',
            yAxis: 1,
//            data: [1000, 1111, null, 1000, 1000, 700, 700, 1100, 900, 900, 900, 900],
            data: [<?=implode(",",$data2)?>],
            color:'#434348', 
            dashStyle: 'shortdot',
            zIndex: 1,
            tooltip: {
                valueSuffix: ''
            }
        }, {
            name: '생산',
            type: 'spline', // column
//            data: [1, 100, 220, 777, 800, 2, 440, 500, 1, 50, 888, 899],
//            data: [<?=implode(",",$data1)?>], // 개별값
            data: [<?=implode(",",$acc1)?>],   // 누적값
            color:'#80b6ec', 
            tooltip: {
                valueSuffix: ''
            }
        }]
    });
    </script>
    
</div>



<div class="btn_fixed_top">
    <a href="./<?=$fname?>_chart.php" id="btn_add" class="btn btn_02">Chart</a>
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <button class="btn_02 btn btn_insert">일괄입력</button>
    <?php } ?>
    <a href="./<?=$fname?>_list.php" id="btn_add" class="btn btn_01">목록</a>
</div>

<script>
$(function(e) {
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

    // timepicker 설정
    $('input[name$=_time]').timepicker({
        'timeFormat': 'H:i',
        'step': <?=$g5['setting']['set_time_step']?>,
    });

    // 일괄입력
    $(document).on('click','.btn_insert',function(e){
        e.preventDefault();
        if(confirm('하루치(1일) 데이타를 입력합니다. 창을 닫지 마세요. 입력을 시작합니다.')) {
            winDataInsert = window.open('<?=G5_USER_ADMIN_URL?>/convert/data_run1.php', "winDataInsert", "left=100,top=100,width=520,height=600,scrollbars=1");
            winDataInsert.focus();
            return false;
        }
        return false;
    });
    
    //Highcharts.com 로고 제거
    setTimeout(function(e){
        $('.highcharts-credits').remove();
    },10);

    
});
</script>

<?php
include_once ('./_tail.php');
?>
