<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '에러(예지) 그래프';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


// mms_idx 디폴트
$mms_idx = ($_REQUEST['mms_idx']) ?: 1;
$mms = get_table_meta('mms','mms_idx',$mms_idx);
$com = get_table_meta('company','com_idx',$mms['com_idx']);

// 디폴트 선택
$dta_group = ($_REQUEST['dta_group']) ?: 'err';
$graph_type = ($_REQUEST['graph_type']) ?: 'column'; // 디폴트 그래프 타입

// 그룹 선택에 따른 초기 dta_item, dta_unit 설정 (user.07.intra.default.php 참조)
$dta_item = ($dta_item) ?: $g5['set_graph_'.$dta_group]['default0'];
$dta_unit = ($dta_unit) ?: $g5['set_graph_'.$dta_group]['default1'];
$dta_unit = ($dta_unit) ?: 1;   // dta_unit가 환경설정값에도 없으면 1로 디폴트 설정
if($dta_item=='minute'||$dta_item=='second') {
    // 분,초인 경우
    $dta_minsec = 1;
}
else {
    // 분,초가 아니면 무조건 1
    $dta_unit = 1;
    $en_time = '23:59:59';
}
//echo $dta_item.'<br>';
//echo $dta_unit.'<br>';

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_JS_URL.'/timepicker/jquery.timepicker.css">', 0);
?>
<style>
.xbuttons {float:right;margin-right:8px;margin-bottom:3px;}
.xbuttons a {margin-left:2px;border:solid 1px #ddd;padding:2px 6px;}
#report {display:none;position:absolute;top:0;left:0;}
#report span {border:solid 1px #bbb;}
#fchart {margin:0 0;}
/* #fchart .dta_value_type {display:inline-block;} */
#fchart .chr_name {font-weight:bold;font-size:1.1em;margin-right:6px;}
#div_shift {margin-bottom:10px;}
a[shf_no] {margin-right:15px;}
#ul_code {}
#ul_code:after {display:block;visibility:hidden;clear:both;content:'';}
#ul_code li {float:left;width:25%;text-align:left;}
</style>

<script type="text/javascript" src="<?=G5_USER_JS_URL?>/timepicker/jquery.timepicker.js"></script>
<!-- <script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/highcharts.src.js"></script> -->
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/highstock.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/data.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/exporting.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="hidden" name="dta_minsec" value="<?=$dta_minsec?>" id="dta_minsec" class="frm_input" style="width:20px;">
    <input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:80px;" >
    <input type="text" name="st_time" value="<?=$st_time?>" id="st_time" class="frm_input" autocomplete="off" style="width:65px;display:<?=($dta_minsec)?'inline-block':'none'?>;">
    ~
    <input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:80px;">
    <input type="text" name="en_time" value="<?=$en_time?>" id="en_time" class="frm_input" autocomplete="off" style="width:65px;display:<?=($dta_minsec)?'inline-block':'none'?>;">

    <label for="dta_item" class="sound_only">검색대상</label>
    <select name="dta_item" id="dta_item">
        <option value="daily"<?php echo get_selected($_GET['dta_item'], "daily"); ?>>일별</option>
        <option value="weekly"<?php echo get_selected($_GET['dta_item'], "weekly"); ?>>주간별</option>
        <option value="monthly"<?php echo get_selected($_GET['dta_item'], "monthly"); ?>>월별</option>
        <option value="yearly"<?php echo get_selected($_GET['dta_item'], "yearly"); ?>>연도별</option>
        <option value="minute"<?php echo get_selected($_GET['dta_item'], "minute"); ?>>분</option>
        <option value="second"<?php echo get_selected($_GET['dta_item'], "second"); ?>>초</option>
    </select>
    <script>$('select[name=dta_item]').val("<?=$dta_item?>").attr("selected","selected")</script>
    <div class="div_dta_unit" style="display:<?=($dta_minsec)?'inline-block':'none'?>;">
        <label class="dta_unit_radio"><input type="radio" name="dta_unit" value="10" id="dta_unit_10"<?=get_checked($dta_unit, "10")?>>10</label>
        <label class="dta_unit_radio"><input type="radio" name="dta_unit" value="20" id="dta_unit_20"<?=get_checked($dta_unit, "20")?>>20</label>
        <label class="dta_unit_radio"><input type="radio" name="dta_unit" value="30" id="dta_unit_30"<?=get_checked($dta_unit, "30")?>>30</label>
        <label class="dta_unit_radio"><input type="radio" name="dta_unit" value="60" id="dta_unit_60"<?=get_checked($dta_unit, "60")?>>60</label>
    </div>
    <script>
        $(document).on('change','select[name=dta_item]',function(e){
            // 분, 초 선택시 시간선택 보여줌
            if( $(this).val()=='minute' || $(this).val()=='second' ) {
                $('.div_dta_unit').css('display','inline-block');
                $('#st_time').show();
                $('#en_time').show();
                $('input[name=dta_minsec]').val(1);
                $('input[name=dta_unit]').closest('label').removeClass('active');
                $('input[name=dta_unit]').eq(0).attr('checked','checked').closest('label').addClass('active');
            }
            // 일,주,월,년 선택시
            else {
                $('.div_dta_unit').hide();
                $('#st_time').val('00:00:00').hide();
                $('#en_time').val('23:59:59').hide();
                $('input[name=dta_unit]').closest('label').removeClass('active');
                $('input[name=dta_unit]:checked').attr('checked',false);
                $('input[name=dta_minsec]').val('');
            }
        });
        // 초기 로딩시 선택된 거 테두리 표시
        $('input[name=dta_unit]:checked').closest('label').addClass('active');
        // 시간단위 선택하면 테두리 표시
        $(document).on('click','input[name=dta_unit]',function(e){
            $('input[name=dta_unit]').closest('label').removeClass('active');
            $('input[name=dta_unit]:checked').closest('label').addClass('active');
        });
    </script>

    <button type="submit" class="btn btn_01">검색</button>

    <div style="float:right;">
        mms
        <input type="text" name="mms_idx" value="<?=$mms_idx?>" class="frm_input" style="width:40px;">
        &nbsp;&nbsp;
        <select name="dta_group">
            <option value="">데이타그룹</option>
            <option value="err"<?php echo get_selected($_GET['dta_group'], "err"); ?>>에러</option>
            <option value="pre"<?php echo get_selected($_GET['dta_group'], "pre"); ?>>예지</option>
        </select>
        <script>$('select[name=dta_group]').val("<?=$dta_group?>").attr("selected","selected")</script>
        <select name="graph_type" style="display:no ne;">
            <option value="">그래프종류</option>
            <option value="line"<?php echo get_selected($_GET['graph_type'], "line"); ?>>꺽은선1</option>
            <option value="spline"<?php echo get_selected($_GET['graph_type'], "spline"); ?>>꺽은선2</option>
            <option value="column"<?php echo get_selected($_GET['graph_type'], "column"); ?>>막대</option>
            <!-- <option value="pie"<?php echo get_selected($_GET['graph_type'], "pie"); ?>>파이차트</option> -->
        </select>
        <script>$('select[name=graph_type]').val("<?=$graph_type?>").attr("selected","selected")</script>
    </div>
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>그래프 좌표갯수 Max값은 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개</span>입니다. (로딩속도 최적화를 필요한 제약입니다.) 시간 범위를 크게 잡더라도 좌표에 표현하는 갯수는 자동으로 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개까지만</span> 표현됩니다. (시작시점 기준)</p>
</div>

<div class="graph_wrap" style="">
    <span id="report">
        <span id="xmin">xmin</span>
        <span id="xmax">xmax</span>
        <span id="ymin">ymin</span>
        <span id="ymax">ymax</span>
    </span>
    <div class="xbuttons">
        <a href="javascript:" class="btn_orig" title="초기화"><i class="fa fa-bars"></i></a>
        <a href="javascript:" class="btn_smaller" title="작게"><i class="fa fa-compress"></i></a>
        <a href="javascript:" class="btn_bigger" title="크게"><i class="fa fa-expand"></i></a>
    </div>

    <!-- 차트 -->
    <div id="chart1" style="position:relative;width:100%; height:400px;">
    </div>

    <!-- 교대 및 코드 -->
    <div id="div_shift_code" style="text-align:center;">
        <div id="div_shift" style="display:none;">
            교대(2개 이상인 경우만)
        </div>
        <div id="div_code" style="display:none;">
            <ul id="ul_code"></ul>
        </div>
    </div>


</div>

<script>
var seriesOptions = [],
    chart, options;

function createChart() {
    var graph_type = $('select[name=graph_type]').val() || 'spline'; // 그래프 종류
    // var chart = new Highcharts.stockChart({
    options = {
        chart: {
            renderTo: 'chart1',
            type: graph_type,   // line, spline, area, areaspline, column, bar, pie, scatter, gauge, arearange, areasplinerange, columnrange
            events: {
                redraw: function() {
                    $('#xmin').text(this.xAxis[0].min);
                    $('#xmax').text(this.xAxis[0].max);
                    $('#ymin').text(this.yAxis[0].min);
                    $('#ymax').text(this.yAxis[0].max);
                    // console.log(this.yAxis[0].max);
                    // console.log(this.yAxis[0].min);
                },
                load: function() {
                    $('#xmin').text(this.xAxis[0].min);
                    $('#xmax').text(this.xAxis[0].max);
                    $('#ymin').text(this.yAxis[0].min);
                    $('#ymax').text(this.yAxis[0].max);
                    // console.log(this.yAxis[0].max);
                    // console.log(this.yAxis[0].min);
                }
            },
        },
        
        animation: false,

        xAxis: {
            // min: 1587635789000,
            // max: 1587643939000,
            type: 'datetime',
            labels: {
                formatter: function() {
                    return moment(this.value).format("MM/DD HH:mm");
                }
            },
            events: {
                setExtremes: function (e) {
                    $('#xmin').text(e.min);
                    $('#xmax').text(e.max);
                }
            }
        },

        yAxis: {
            // max: 1800,   // 크게 확대해서 보려면 20
            // min: -100,  // 크게 확대해서 보려면 -10, 없애버리면 자동 스케일
            showLastLabel: true,    // 위 아래 마지막 label 보임 (이게 없으면 끝label이 안 보임)
            scrollbar: {
                enabled: true
            },
            opposite: false,
            tickInterval: null,
            // minorTickInterval: 5,
            // minorTickLength: 0,
        },

        plotOptions: {
            series: {
                showInNavigator: true,
                events: {
                    legendItemClick: function (e) {
                        e.preventDefault();
                        // console.log(this);
                        // console.log(this.userOptions);
                        var chr_id = this.userOptions.id;
                        var chr_idx = this.userOptions._symbolIndex;
                        var chr_name = this.userOptions.name;
                        $('#fchart input[name=chr_id]').val(chr_id);
                        $('#fchart input[name=chr_idx]').val(chr_idx);
                        $('#fchart .chr_name').text(chr_name);
                    }
                },
                dataGrouping: {
                    enabled: false, // dataGrouping 안 함 (range가 변경되면 평균으로 바뀌어서 헷갈림)
                }
                // states: {
                //     inactive: {
                //         opacity: 0
                //     }
                // },
            }
        },

        navigator: {
            series: {
                type: graph_type,
            },
            xAxis: {
                type: 'datetime',
                dateTimeLabelFormats: {
                    second: '%H:%M:%S',
                    minute: '%H:%M',
                    hour: '%H:%M',
                    day: '%m-%d',
                    week: '%m-%d',
                    month: '%Y-%m',
                    year: '%Y-%m'
                }
            },
        },

        navigation: {
            buttonOptions: {
                enabled: false, // contextButton (인쇄, 다운로드..) 설정 (기본옵션 사용자들에게는 안 보이게!!)
                align: 'right',
                x: -20,
                y: 15
            }
        },

        legend: {
            enabled: true,
        },

        rangeSelector: {
            enabled: false,
        },

        tooltip: {
            formatter: function(e) {
                // var tooltip1 =  moment(this.x).format("YYYY-MM-DD HH:mm:ss");
                if($('#dta_item').val()=='daily'||$('#dta_item').val()=='weekly') {
                    var tooltip1 =  moment(this.x).format("MM/DD");
                }
                else if($('#dta_item').val()=='monthly') {
                    var tooltip1 =  moment(this.x).format("YYYY-MM");
                }
                else if($('#dta_item').val()=='yearly') {
                    var tooltip1 =  moment(this.x).format("YYYY");
                }
                else {
                    var tooltip1 =  moment(this.x).format("MM/DD HH:mm:ss");
                }
                // console.log(this);
                $.each(this.points, function () {
                    // console.log(this);
                    tooltip1 += '<br/><span style="color:' + this.color + '">\u25CF '+this.series.name+'</span>: <b>' + this.y + '</b>';
                });
                return tooltip1;
            },
            split: false,
            shared: true
        },
        series: seriesOptions
        
    };

    chart = new Highcharts.stockChart(options);
    dta_loading('hide');

}

// 그래프 호출 =========================================================================
// $(document).on('click','button[type=submit]',function(e){
$(document).on('click','button[type=submit], #div_shift a, #div_code a',function(e){
    e.preventDefault();
    var frm = $('#fsearch');
    var mms_idx = frm.find('input[name=mms_idx]').val();
    var dta_group = frm.find('select[name=dta_group]').val() || '';
    var shf_no = $(this).attr('shf_no') || '';  // 하단 교대 클릭
    var dta_code = $(this).attr('dta_code') || '';  // 하단 코드 클릭
    var dta_item = frm.find('select[name=dta_item]').val() || '';   // 일,주,월,년,분,초
    var dta_file = (dta_item=='minute'||dta_item=='second') ? '' : '.sum'; // error.php(그룹핑), error.sum.php(일자이상)
    var dta_unit = frm.find('input[name=dta_unit]:checked').val() || '';   // 10,20,30,60
    var st_date = frm.find('#st_date').val() || '';
    var st_time = frm.find('#st_time').val() || '';
    var en_date = frm.find('#en_date').val() || '';
    var en_time = frm.find('#en_time').val() || '';
    var tag_name = $(this).prop('tagName');
    // 검색 버튼 클릭하면 그래프 초기화
    if( tag_name == 'BUTTON' ) {
        var dta_name = frm.find('select[name=dta_group] option:selected').text() || '그래프';
        seriesOptions = [];
        chr_idx = 0;
        // 그래프 타입 = 막대그래프
        $('select[name=graph_type]').val("column").attr("selected","selected");
    }
    // 검색이 아니면(교대, 코드) 그래프 추가(삭제)
    else {
        var dta_name = $(this).find('b').text() || '항목';
        chr_idx = chart ? (chart.series.length/2) : 0;  // navigator series가 있으므로 2배 숫자가 나옴 (navigation series가 있어서 따로 +1을 하지 않아도 됨)
        // 그래프 타입 = spline
        $('select[name=graph_type]').val("spline").attr("selected","selected");
    }
    chr_id = mms_idx+'_'+dta_group+'_'+shf_no+'_'+dta_code;
    var dta_url = '//bogwang.epcs.co.kr/device/json/error'+dta_file+'.php?token=1099de5drf09&mms_idx='+mms_idx+'&dta_group='+dta_group+'&shf_no='+shf_no+'&dta_code='+dta_code+'&dta_item='+dta_item+'&dta_unit='+dta_unit+'&st_date='+st_date+'&st_time='+st_time+'&en_date='+en_date+'&en_time='+en_time;
    dta_loading('show');

    // console.log(dta_url);

    Highcharts.getJSON( dta_url,
        function(data) {
            // console.log(data);

            // 시간 범위값을 가지온 값에서 추출해서 재수정
            var st_date_new = moment(data[0].x).format("YYYY-MM-DD");
            var st_time_new = moment(data[0].x).format("HH:mm:ss");
            var en_date_new = moment(data[data.length-1].x).format("YYYY-MM-DD");
            // 분초가 아닌 경우 마지막 시간값은 23:59:59로 입력
            if( $('#dta_minsec').val()!=1 ) {
                var en_time_new = moment(data[data.length-1].x).format("23:59:59");
            }
            else {
                // console.log(data[data.length-1].x);
                var en_time_new = moment(data[data.length-1].x).format("HH:mm:ss");
            }

            $('#st_date').val(st_date_new);
            $('#st_time').val(st_time_new);
            $('#en_date').val(en_date_new);
            $('#en_time').val(en_time_new);


            // 검색 버튼 클릭할 때만 교대 및 코드를 하단에 뿌려줌
            if( tag_name == 'BUTTON' ) {
                // 교대수를 불러와서 표시
                var dta_shift_url = '//bogwang.epcs.co.kr/device/json/error.code.php?token=1099de5drf09&mms_idx='+mms_idx+'&dta_group='+dta_group+'&dta_groupby=shf_no&st_date='+st_date_new+'&st_time='+st_time_new+'&en_date='+en_date_new+'&en_time='+en_time_new;
                // console.log(dta_shift_url);
                var dta_shift_dom = '';
                $.getJSON(dta_shift_url,{"aj":"list"},function(res) {
                    // console.log(res);
                    try{
                        $.each(res,function(i,v){
                            // console.log(i + ' / ' + v['shf_no']);
                            // dta_shift_dom += v['shf_no']+'교대 ';
                            dta_shift_dom += '<a href="javascript:" shf_no="'+v['shf_no']+'"><b>'+v['shf_no']+'교대</b>: <span class="dta_count">'+v['dta_count']+'</span>회</a> ';
                        });
                        // 교대수가 한개만 있으면 숨김
                        if(res.length<=1)
                            $('#div_shift').hide();
                        else {
                            $('#div_shift').empty().append(dta_shift_dom);
                            $('#div_shift').show();
                        }

                    } catch(e){}
                });

                // 코드를 불러와서 표시
                var dta_code_url = '//bogwang.epcs.co.kr/device/json/error.code.php?token=1099de5drf09&mms_idx='+mms_idx+'&dta_group='+dta_group+'&dta_groupby=dta_code&st_date='+st_date_new+'&st_time='+st_time_new+'&en_date='+en_date_new+'&en_time='+en_time_new;
                // console.log(dta_code_url);
                var dta_code_dom = '';
                $.getJSON(dta_code_url,{"aj":"list"},function(res) {
                    // console.log(res);
                    try{
                        $.each(res,function(i,v){
                            // dta_code_dom += v['dta_code']+' ';
                            dta_code_dom += '<li><a href="javascript:" dta_code="'+v['dta_code']+'"><b>'+v['dta_code']+'</b>: <span class="dta_count">'+v['dta_count']+'</span>회</a> (<span>'+cut_str(v['dta_message'],18)+'</span>)</li>';
                        });
                        // 없으면 숨김
                        if(res.length<=0)
                            $('#div_code').hide();
                        else {
                            $('#ul_code').empty().append(dta_code_dom);
                            $('#div_code').show();
                        }
                    } catch(e){}
                });
            }
            // 하단 항목을 클릭했을 때
            else {
            }

            // 그래프 series 추출 & 그래프 그리기 
            seriesOptions[chr_idx] = {
                name: dta_name,
                id:chr_id,
                data: data
            };
            createChart();

        }
    );

});

// 이게 위치가 클릭 선언 뒤쪽으로 와야 하는구만!
$('button[type=submit]').trigger('click');


// 그래프 호출
$(document).on('click','.div_dta_type a',function(e){
    e.preventDefault();
    // console.log ( $(this).attr('id') );
    var chr_exists = null;
    for(i=0;i<seriesOptions.length;i++) {
        // console.log(seriesOptions[i]);
        // console.log(seriesOptions[i].id);
        if(seriesOptions[i].id==$(this).attr('id'))
            chr_exists = 1;
    }
    if(chr_exists) {
        alert('이미 적용된 그래프입니다.');
        return false;
    }
    dta_loading('show');
    chr_idx = chart ? (chart.series.length/2) : 0;  // navigator series가 있으므로 2배 숫자가 나옴 (navigation series가 있어서 따로 +1을 하지 않아도 됨)
    dta_name = $(this).text();
    mms_idx = $(this).attr('mms_idx');
    dta_type = $(this).attr('dta_type');
    dta_no = $(this).attr('dta_no');
    chr_id = mms_idx+'_'+dta_type+'_'+dta_no;
    $(this).attr('chr_id',chr_id);

    Highcharts.getJSON(
        '//bogwang.epcs.co.kr/device/json/error.php?token=1099de5drf09&mms_idx='+mms_idx+'&dta_type='+dta_type+'&dta_no='+dta_no+'&st_date=2020-04-23&st_time=19:00:00&en_date=2020-04-23&en_time=23:59:59',
        function(data) {
            seriesOptions[chr_idx] = {
                name: dta_name,
                id:chr_id,
                data: data
            };
            createChart();
        }
    );
});

// 이게 위치가 클릭 선언 뒤쪽으로 와야 하는구만!
// $('.div_dta_type a').eq(0).trigger('click');


$('.btn_bigger, .btn_orig, .btn_smaller').click(function(e) {
    var act = $(this).attr('class');    // btn_bigger, btn_orig, btn_smaller
    // $("#chart1").empty();
    y1 = parseInt($('#ymin').text());
    y2 = parseInt($('#ymax').text());
    ydiff = parseInt($('#ymax').text()) - parseInt($('#ymin').text());
    yhalf = ydiff/2;    // 작게 할 때는 1/2 단위 기준으로 양쪽 한 단위값 추가해서 작게 보이게..
    yquar = ydiff/4;    // 크게 할 때 1/4 단위 기준으로 양쪽 한 단위값 제거해서 크게 보이게
    xmin = parseInt($('#xmin').text());   // 크게 작게 하더라도 x좌표 현재값은 유지되어야 함
    xmax = parseInt($('#xmax').text());   // 크게 작게 하더라도 x좌표 현재값은 유지되어야 함
    if(act=='btn_bigger') {
        ymin = y1 + yquar;
        ymax = y2 - yquar;
        ytick = parseInt((ymax-ymin)/8);     // tickInterval
    }
    else if(act=='btn_smaller') {
        ymin = y1 - yhalf;
        ymax = y2 + yhalf;
        ytick = parseInt((ymax-ymin)/8);     // tickInterval
    }
    else {
        // xmin = null,   // 초기화 x좌표 초기화
        // xmax = null,   // 초기화 x좌표 초기화
        ymin = null;
        ymax = null;
        ytick = null;     // tickInterval
    }

    options.xAxis = {
        min: xmin,
        max: xmax,
        labels: {
            formatter: function() {
                return moment(this.value).format("MM/DD HH:mm");
            }
        },
        events: {
            setExtremes: function (e) {
                $('#xmin').text(e.min);
                $('#xmax').text(e.max);
            }
        }
    };
    options.yAxis = {
        min: ymin,
        max: ymax,
        showLastLabel: true,    // 위 아래 마지막 label 보임 (이게 없으면 끝label이 안 보임)
        scrollbar: {
            enabled: true
        },
        opposite: false,
        tickInterval: ytick,    // 눈금 크기(로그, 대수 형태로 계산한다는 데.. 모르겠다.)
    };
    chart = new Highcharts.stockChart(options);

});

// 차트 제거하기
$(document).on('click','.btn_chr_del',function(e){
    e.preventDefault();
    // 차트가 한개뿐이면 제거 안 함
    if($('.div_dta_type a[chr_id]').length==1) {
        alert('차트가 한개뿐이잖아요. 제거 안 할래요!');
        return false;
    }
    var frm = $('#fchart');
    var chr_id_del = frm.find('input[name=chr_id]').val();
    var chr_idx_del = frm.find('input[name=chr_idx]').val();
    if(chr_id_del=='') {
        alert('제거할 차트를 선택하세요.');
        return false;
    }
    else {
        seriesOptions.splice(chr_idx_del,1);
        $('.div_dta_type a[id='+chr_id_del+']').removeAttr('chr_id');
        frm.find('input[name=chr_id]').val('');    // 폼초기화
        createChart();
    }

});

// 로딩 spinner 이미지 표시/비표시
function dta_loading(flag) {
    var img_loading = $('<i class="fa fa-spin fa-spinner" id="spinner" style="position:absolute;top:80px;left:46%;font-size:4em;"></i>');
    if(flag=='show') {
        // console.log('show');
        $('#chart1').append(img_loading);
    }
    else if(flag=='hide') {
        // console.log('hide');
        $('#spinner').remove();
    }
}
</script>

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

    //Highcharts.com 로고 제거
    setTimeout(function(e){
        $('.highcharts-credits').remove();
    },500);

});
</script>

<?php
include_once ('./_tail.php');
?>
