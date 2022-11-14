<?php
$sub_menu = "955100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '겹쳐보기';
// include_once('./_top_menu_data.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];

// mms_idx 디폴트
$mms_idx = ($_REQUEST['mms_idx']) ?: 1;
$mms = get_table_meta('mms','mms_idx',$mms_idx);
$com = get_table_meta('company','com_idx',$mms['com_idx']);

?>
<style>
.xbuttons {float:right;margin-right:8px;margin-bottom:3px;}
.xbuttons a {margin-left:2px;border:solid 1px #ddd;padding:2px 6px;}
.graph_wrap {position:relative;}
#report {display:none;position:absolute;top:0;left:0;}
#report span {border:solid 1px #bbb;}
#fchart {margin:0 0;}
#fchart .chr_name {font-weight:bold;font-size:1.5em;margin-right:6px;}
.table01 {width:auto;margin:0 auto;}
.table01 td {padding:7px 9px;}
.table01 td input {-moz-outline: none;outline: none;ie-dummy: expression(this.hideFocus=true);}
.ui-slider-handle {-moz-outline: none;outline: none;ie-dummy: expression(this.hideFocus=true);}
.chr_control {display:none;}
#chr_type, #chr_line {height:30px;-moz-outline: none;outline: none;ie-dummy: expression(this.hideFocus=true);}
.chart_empty {text-align:center;height:200px;line-height:200px;}
</style>

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

    <button type="submit" class="btn btn_01">확인</button>

    <div style="float:right;">
        <a href="./data_measure_add.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_03 btn_add_chart"><i class="fa fa-bar-chart"></i> 불러오기</a>
        <a href="javascript:alert('설정된 그래프를 대시보드로 내보냅니다.');" class="btn btn_03 btn_add_dash" style="display:none;"><i class="fa fa-line-chart"></i> 기</a>
    </div>
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>그래프 좌표갯수 Max값은 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개</span>입니다. (로딩속도 최적화를 필요한 제약입니다.) 시간 범위를 크게 잡더라도 좌표에 표현하는 갯수는 자동으로 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개까지만</span> 표현됩니다. (시작시점 기준)</p>
</div>

<div class="graph_wrap" style="">
    <span id="report">
        <input type="text" id="xmin" placeholder="xmin">
        <input type="text" id="xmax" placeholder="xmax">
        <input type="text" id="ymin" placeholder="ymin">
        <input type="text" id="ymax" placeholder="ymax">
    </span>
    <div class="xbuttons">
        <a href="javascript:" class="btn_orig" title="초기화"><i class="fa fa-bars"></i></a>
        <a href="javascript:" class="btn_smaller" title="작게"><i class="fa fa-compress"></i></a>
        <a href="javascript:" class="btn_bigger" title="크게"><i class="fa fa-expand"></i></a>
    </div>

    <!-- 차트 -->
    <div id="chart1" style="position:relative;width:100%; height:400px;">
        <div class="chart_empty">그래프가 존재하지 않습니다.</div>
    </div>

    <!-- 컨트롤 -->
    <div class="chr_control" style="text-align:center;">
        <form name="fchart" id="fchart" method="post">
        <input type="hidden" name="chr_id" style="width:70px">
        <input type="hidden" name="chr_idx" style="width:20px">
    
            <table class="table01" style="">
            <tbody>
                <tr>
                    <td class="td_" style="vertical-align:bottom;">
                        <span class="chr_name">항목명</span>
                    </td>
                    <td class="td_">
                        <label for="chr_amp_value">증폭</label>
                        <input type="text" id="chr_amp_value" style="border:0; color:#f6931f; font-weight:bold;" autocomplete="off">
                        <div id="chr_amp" style="width:200px;"></div>
                    </td>
                    <td class="td_">
                        <label for="chr_move_value">이동</label>
                        <input type="text" id="chr_move_value" style="border:0; color:#f6931f; font-weight:bold;" autocomplete="off">
                        <div id="chr_move" style="width:200px;"></div>
                    </td>
                    <td class="td_" style="padding-top:14px;">
                        <select name="chr_type" id="chr_type" autocomplete="off">
                            <option value="spline">꺽은선1</option>
                            <option value="line">꺽은선2</option>
                            <option value="column">막대</option>
                        </select>
                    </td>
                    <td class="td_" style="vertical-align:bottom;">
                        <button type="submit" class="btn btn_03"><i class="fa fa-check"></i> 적용</button>
                        <a href="javascript:" class="btn btn_02 btn_chr_del" title="제거">제거 <i class="fa fa-trash-o"></i></a>
                        <a href="javascript:" class="btn btn_02 btn_chr_close" title="닫기">닫기 <i class="fa fa-times"></i></a>
                    </td>
                </tr>
            </tbody>
            </table>
        </form>
    </div>
    <script>
    $("#fchart").on('submit', function(e){
        e.preventDefault();
        var frm = $('#fchart');
        // console.log( frm.find('#chr_amp_value').val() );
        // console.log( frm.find('#chr_move_value').val() );

        var chr_idx_chg = parseInt(frm.find('input[name=chr_idx]').val());
        var chr_amp = parseFloat(frm.find('#chr_amp_value').val());
        var chr_move = parseInt(frm.find('#chr_move_value').val());
        var chr_type = frm.find('#chr_type').val();
        
        // console.log( $('#chr_amp').slider('option','min') );
        // console.log( chr_amp_slider.slider("option","max") );
        // 증폭값 설정
        if(chr_amp<chr_amp_slider.slider("option","min")) {
           alert('증폭 최소값은 '+chr_amp_slider.slider("option","min")+'입니다.');
           return false;
        }
        if(chr_amp>chr_amp_slider.slider("option","max")) {
           chr_amp_slider.slider("option", "max", chr_amp);
           chr_amp_slider.slider("option", "value", chr_amp);
        }
        // 이동값 설정
        if(chr_move<chr_move_slider.slider("option","min")) {
           alert('이동 최소값은 '+chr_move_slider.slider("option","min")+'입니다.');
           return false;
        }
        if(chr_move>chr_move_slider.slider("option","max")) {
            alert('이동 최대값은 '+chr_move_slider.slider("option","max")+'입니다.');
           return false;
        }

        // chr_move_slider.slider("option", "value", 0);
        if(isNaN(chr_idx_chg) == false) {
            old_type = seriesOptions[chr_idx_chg].type;
            old_yamp = seriesOptions[chr_idx_chg].data[0].yamp;
            old_ymove = seriesOptions[chr_idx_chg].data[0].ymove;
            // console.log( chr_idx_chg );
            // console.log( old_yamp +'/'+ chr_amp +'//'+ old_ymove +'/'+ chr_move );
            // console.log( '----' );
            // 증폭, 이동값, 그래프 종류가 바뀐 경우만 수정 그래프 변형
            if(old_yamp!=chr_amp || old_ymove!=chr_move || old_type!=chr_type) {
                for(i=0;i<seriesOptions[chr_idx_chg].data.length;i++) {
                    // console.log(seriesOptions[chr_idx_chg].data[i]);
                    raw_y = seriesOptions[chr_idx_chg].data[i].yraw;    // original Y value
                    amp_y = raw_y*chr_amp;  // amplified value
                    new_y = amp_y+chr_move;  // amplified+moved value
                    // console.log( raw_y +'/'+ chr_amp );
                    seriesOptions[chr_idx_chg].type = chr_type;
                    seriesOptions[chr_idx_chg].data[i].yamp = chr_amp;
                    seriesOptions[chr_idx_chg].data[i].ymove = chr_move;
                    seriesOptions[chr_idx_chg].data[i].y = new_y;
                    // console.log(seriesOptions[chr_idx_chg].data[i].y);
                }
                createChart();
            }
        }
    });
    </script>

</div>

<script>
var graphs = [], seriesOptions = [], data_series = [], graph_type = 'spline', graph_line = 'solid',
    seriesCounter = 0, chart, options;

    function createChart() {
    // var chart = new Highcharts.stockChart({
    options = {
        chart: {
            renderTo: 'chart1',
            // type: 'spline',   // line, spline, area, areaspline, column, bar, pie, scatter, gauge, arearange, areasplinerange, columnrange
            events: {
                redraw: function() {
                    $('#xmin').val(this.xAxis[0].min);
                    $('#xmax').val(this.xAxis[0].max);
                    $('#ymin').val(this.yAxis[0].min);
                    $('#ymax').val(this.yAxis[0].max);
                    // console.log(this.yAxis[0].max);
                    // console.log(this.yAxis[0].min);
                    // bottom slider min, max change
                    chr_move_slider.slider("option", "min", parseInt(this.yAxis[0].min));
                    chr_move_slider.slider("option", "max", parseInt(this.yAxis[0].max));
                },
                load: function() {
                    $('#xmin').val(this.xAxis[0].min);
                    $('#xmax').val(this.xAxis[0].max);
                    $('#ymin').val(this.yAxis[0].min);
                    $('#ymax').val(this.yAxis[0].max);
                    // console.log(this.yAxis[0].max);
                    // console.log(this.yAxis[0].min);
                    // bottom slider min, max change
                    chr_move_slider.slider("option", "min", parseInt(this.yAxis[0].min));
                    chr_move_slider.slider("option", "max", parseInt(this.yAxis[0].max));
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
                    $('#xmin').val(e.min);
                    $('#xmax').val(e.max);
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
                        // console.log(this.userOptions.data[0]);
                        var chr_idx = this.userOptions._colorIndex; // _symbolIndex가 undefined일 때가 있어서 _colorIndex로 대체함
                        var chr_name = this.userOptions.name;
                        var old_type = this.userOptions.type;
                        var old_dashStyle = this.userOptions.dashStyle;
                        var old_amp = this.userOptions.data[0].yamp;
                        var old_move = this.userOptions.data[0].ymove;
                        $('#fchart input[name=chr_idx]').val(chr_idx);
                        $('#fchart .chr_name').text(chr_name);

                        // reset value of amplification, move
                        chr_amp_slider.slider("option", "value", old_amp);
                        chr_move_slider.slider("option", "value", old_move);
                        $('#chr_amp_value').val(old_amp);
                        $('#chr_move_value').val(old_move);
                        $('#chr_type option[value="'+old_type+'"]').attr('checked','checked');
                        $('#chr_type').val(old_type);
                        $('#chr_line').val(old_dashStyle);

                        $('.chr_control').show();
                    }
                },
                dataGrouping: {
                    enabled: false, // dataGrouping 안 함 (range가 변경되면 평균으로 바뀌어서 헷갈림)
                },
                marker: {
                    enabled: true   // point dot display
                }
            }
        },

        navigator: {
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
                enabled: true, // contextButton (인쇄, 다운로드..) 설정 (기본옵션 사용자들에게는 안 보이게!!)
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
                var tooltip2 = [];
                $.each(this.points, function () {
                    // console.log(this);
                    tooltip1 += '<br/><span style="color:' + this.color + '">\u25CF '+this.series.name+'</span>: <b>' + this.point.yraw + '</b>';
                    if(this.point.y!=this.point.yraw) {
                        if(this.point.yamp!=1)
                            tooltip2[0] = '×' + this.point.yamp;
                        if(this.point.ymove!=0) {
                            var tooltip2_unit = (this.point.ymove>0) ? '+':'';  // -기호는 자동으로 붙음
                            tooltip2[1] = tooltip2_unit + this.point.ymove;
                        }
                        // console.log(tooltip2);
          
                        if(tooltip2.length>=1) {
                            tooltip1 += '<span style="font-size:0.8em;"> (' + tooltip2.join(" ") + ')</span>';
                        }
                    }
                });
                return tooltip1;
            },
            split: false,
            shared: true
        },
        series: seriesOptions
        
    };

    chart = new Highcharts.stockChart(options);
    removeLogo();
    // console.log( chart.series );
    
}

// As we're loading the data asynchronously, we don't know what order it will arrive.
// So we keep a counter and create the chart when all the data is loaded.
function drawChart(data) {
    // find which graph
    var para = urlParaToJSON(this.url); // get values from Json Url
    // console.log(this.url);
    var dta_group = para.dta_group;
    var mms_idx = para.mms_idx;
    var dta_type = para.dta_type;
    var dta_no = para.dta_no;
    var shf_no = para.shf_no;
    var dta_mmi_no = para.dta_mmi_no;
    var dta_defect = para.dta_defect;
    var dta_defect_type = para.dta_defect_type;
    var dta_code = para.dta_code;
    var graph_id = para.graph_id;
    // console.log('기준: '+graph_id);
    // chr_idx = graphs.length-1;
    
    // idx 찾기 (비동기라 어떤 idx가 왔는지 모르기 때문!!)
    for(i=0;i<graphs.length;i++) {
        // console.log(i+'번: '+graphs[i].graph_id);
        if( graph_id == graphs[i].graph_id ) {
            var chr_idx = i;
        }
    }
    // console.log(chr_idx + ' arrived.');
 
    var graph_id1 = getGraphId(graphs[chr_idx].dta_json_file, graphs[chr_idx].dta_group, graphs[chr_idx].mms_idx, graphs[chr_idx].dta_type, graphs[chr_idx].dta_no, graphs[chr_idx].shf_no, graphs[chr_idx].dta_mmi_no, graphs[chr_idx].dta_defect, graphs[chr_idx].dta_defect_type, graphs[chr_idx].dta_code);
    var chr_id = {
        dta_data_url: graphs[chr_idx].dta_data_url,
        dta_json_file: graphs[chr_idx].dta_json_file,
        dta_group: graphs[chr_idx].dta_group,
        mms_idx: graphs[chr_idx].mms_idx,
        dta_type: graphs[chr_idx].dta_type,
        dta_no: graphs[chr_idx].dta_no,
        shf_no: graphs[chr_idx].shf_no,
        dta_mmi_no: graphs[chr_idx].dta_mmi_no,
        dta_defect: graphs[chr_idx].dta_defect,
        dta_defect_type: graphs[chr_idx].dta_defect_type,
        dta_code: graphs[chr_idx].dta_code,
        graph_id: graph_id1
    };

    // 그래프명
    if(graphs[chr_idx].dta_group == 'product') {
        if(graphs[chr_idx].dta_defect=='1')
            dta_name = '불량';
        else
            dta_name = '생산';

        if(graphs[chr_idx].dta_json_file=='output.target')
            dta_name = '목표';
    }
    else if(graphs[chr_idx].dta_group == 'mea') {
        dta_name = dta_types[dta_type]+dta_no;
    }
    else if(graphs[chr_idx].dta_group == 'run') {
        dta_name = '가동시간';
    }
    else if(graphs[chr_idx].dta_group == 'err') {
        dta_name = '에러('+dta_code+')';
    }
    else if(graphs[chr_idx].dta_group == 'pre') {
        dta_name = '예지('+dta_code+')';
    }

    // data variable definition <<<<==============================================
    seriesOptions[chr_idx] = {
        name: dta_name,
        id:chr_id,
        type: graphs[chr_idx].graph_type,
        dashStyle: graphs[chr_idx].graph_line,
        data: data
    };


    // Create chart when all data loaded.
    seriesCounter += 1;
    if (seriesCounter === graphs.length) {
        console.log('graph drawing .................................');
        console.log('seriesOptions length: ' + seriesOptions.length);
        createChart();
    }
}

// ==========================================================================================
// 그래프 호출 =================================================================================
// ==========================================================================================
$(document).on('click','#fsearch button[type=submit]',function(e){
    e.preventDefault();
    var frm = $('#fsearch');
    var st_date = frm.find('#st_date').val() || '';
    var st_time = frm.find('#st_time').val() || '';
    var en_date = frm.find('#en_date').val() || '';
    var en_time = frm.find('#en_time').val() || '';
    var dta_item = frm.find('select[name=dta_item]').val() || '';   // 일,주,월,년,분,초
    var dta_unit = frm.find('input[name=dta_unit]:checked').val() || '';   // 10,20,30,60
    var dta_file = (dta_item=='minute'||dta_item=='second') ? '' : '.sum'; // measure.php(그룹핑), measure.sum.php(일자이상)
    if(st_date==''||en_date=='') {
        alert('검색 날짜를 입력하세요.');
        return false;
    }

    dta_loading('show');
    seriesCounter = 0;
    // console.log(graphs);
    // 다중 그래프 표현
    for(i=0;i<graphs.length;i++) {
        // console.log(i+' --------------- ');
        var dta_data_url = graphs[i].dta_data_url;
        var dta_json_file = graphs[i].dta_json_file;
        var dta_group = graphs[i].dta_group;
        var mms_idx = graphs[i].mms_idx;
        var dta_type = graphs[i].dta_type;
        var dta_no = graphs[i].dta_no;
        var shf_no = graphs[i].shf_no;
        var dta_mmi_no = graphs[i].dta_mmi_no;
        var dta_defect = graphs[i].dta_defect;
        var dta_defect_type = graphs[i].dta_defect_type;
        var dta_code = graphs[i].dta_code;
        var graph_id1 = getGraphId(dta_json_file, dta_group, mms_idx, dta_type, dta_no, shf_no, dta_mmi_no, dta_defect, dta_defect_type, dta_code);
        // console.log(i+' 호출 시:'+graph_id1);

        // 그래프 호출 URL
        var dta_url = '//'+dta_data_url+'/'+dta_json_file+dta_file+'.php?token=1099de5drf09'
                        +'&mms_idx='+mms_idx+'&dta_group='+dta_group+'&shf_no='+shf_no+'&dta_mmi_no='+dta_mmi_no
                        +'&dta_type='+dta_type+'&dta_no='+dta_no
                        +'&dta_defect='+dta_defect+'&dta_defect_type='+dta_defect_type
                        +'&dta_code='+dta_code
                        +'&dta_item='+dta_item+'&dta_unit='+dta_unit
                        +'&st_date='+st_date+'&st_time='+st_time+'&en_date='+en_date+'&en_time='+en_time
                        +'&graph_id='+graph_id1;
        // console.log(dta_url);

        Highcharts.getJSON(
            dta_url,
            drawChart
        );

    }
    dta_loading('hide');

});

// 그래프 불러오기
$(document).on('click','.btn_add_chart',function(e){
    e.preventDefault();
    var frm = $('#fsearch');
    var com_idx = '<?=$com['com_idx']?>';
    var mms_idx = '<?=$mms['mms_idx']?>';
    var st_date = frm.find('#st_date').val() || '';
    var st_time = frm.find('#st_time').val() || '';
    var en_date = frm.find('#en_date').val() || '';
    var en_time = frm.find('#en_time').val() || '';
    if(st_date==''||en_date=='') {
        alert('검색 날짜를 입력하세요.');
    }
    else {
        var href = $(this).attr('href');
        winAddChart = window.open(href+'&com_idx='+com_idx+'&sch_field=mms_idx&sch_word='+mms_idx+'&st_date='+st_date+'&st_time='+st_time+'&en_date='+en_date+'&en_time='+en_time,"winAddChart","left=100,top=100,width=520,height=600,scrollbars=1");
        winAddChart.focus();
    }
});

// Y축 스케일 조정 (크게, 작게, 제쟈리로)
$('.btn_bigger, .btn_orig, .btn_smaller').click(function(e) {
    var act = $(this).attr('class');    // btn_bigger, btn_orig, btn_smaller
    // $("#chart1").empty();
    y1 = parseInt($('#ymin').val());
    y2 = parseInt($('#ymax').val());
    ydiff = parseInt($('#ymax').val()) - parseInt($('#ymin').val());
    yhalf = ydiff/2;    // 작게 할 때는 1/2 단위 기준으로 양쪽 한 단위값 추가해서 작게 보이게..
    yquar = ydiff/4;    // 크게 할 때 1/4 단위 기준으로 양쪽 한 단위값 제거해서 크게 보이게
    xmin = parseInt($('#xmin').val());   // 크게 작게 하더라도 x좌표 현재값은 유지되어야 함
    xmax = parseInt($('#xmax').val());   // 크게 작게 하더라도 x좌표 현재값은 유지되어야 함
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
                $('#xmin').val(e.min);
                $('#xmax').val(e.max);
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
    removeLogo();

});

// 차트 제거하기
$(document).on('click','.btn_chr_del',function(e){
    e.preventDefault();
    // 차트가 한개뿐이면 제거 안 함
    if(seriesOptions.length<=1) {
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
        $('.chr_control').hide();
    }

});

// 차트 설정 닫기
$(document).on('click','.btn_chr_close',function(e){
    e.preventDefault();
    $('.chr_control').hide();
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

// highchart.com이라는 로고 제거
function removeLogo() {
    //Highcharts.com 로고 제거
    setTimeout(function(e){
        $('.highcharts-credits').remove();
    },10);
}

// amplification setting
var chr_amp_slider = $( "#chr_amp" ).slider({
    range: "max",
    step: 0.5,
    min: 0.5,
    max: 5,
    value: 1, // Default
    slide: function( event, ui ) {
        $( "#chr_amp_value" ).val( ui.value );
    },
    stop: function( event, ui ) {
        // 증폭 값이 바뀌면 적용
        // console.log(ui.value);
        $('#fchart button[type=submit]').trigger('click');
    }
});
$( "#chr_amp_value" ).val( $( "#chr_amp" ).slider( "value" ) );   // default value display

// value move setting
var chr_move_slider = $( "#chr_move" ).slider({
    range: "max",
    min: -200,
    max: 200,
    value: 0,   // Default
    slide: function( event, ui ) {
        $( "#chr_move_value" ).val( ui.value );
    },
    stop: function( event, ui ) {
        // 이동 값이 바뀌면 적용
        // console.log(ui.value);
        $('#fchart button[type=submit]').trigger('click');
    }
});
$( "#chr_move_value" ).val( $( "#chr_move" ).slider( "value" ) );   // default value display
// setTimeout(function(e){
//     chr_move_slider.slider("option", "min", -88);
//     chr_move_slider.slider("option", "max", 1500);
// },4000);

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

    // 날짜 디폴트 입력
    $.ajax({
        url:'//bogwang.epcs.co.kr/device/json/output.default.php',
        data:{"token":"1099de5drf09","mms_idx":"<?=$mms_idx?>"},
        dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
            // console.log(res);
            //var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }

            // 폼에 날짜값이 없으면 JSON에서 날짜 추출해서 폼값에 입력!
            $('#st_date').val(res.st_date);
            $('#st_time').val(res.st_time);
            $('#en_date').val(res.en_date);
            $('#en_time').val(res.en_time);
            $('select[name=dta_item]').val(res.dta_item).attr("selected","selected");
            $('input[name=dta_unit]').val(res.dta_unit).attr("checked","checked");
            $('input[name=dta_unit]:checked').closest('label').addClass('active');
            // 분, 초 선택시 시간선택 보여줌
            if( res.dta_item=='minute' || res.dta_item=='second' ) {
                $('.div_dta_unit').css('display','inline-block');
                $('#st_time').show();
                $('#en_time').show();
                $('input[name=dta_minsec]').val(1);
            }
            // 일,주,월,년 선택시
            else {
                $('.div_dta_unit').hide();
                $('input[name=dta_minsec]').val('');
            }

        },
        error:function(xmlRequest) {
            alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
                + ' \n\rresponseText: ' + xmlRequest.responseText);
        } 
    });

});
</script>

<div class="btn_fixed_top" style="display:none;">
    <a href="./dashboard_graph_multi.php" id="btn_add" class="btn btn_02">겹쳐보기(정주기)</a>
    <a href="./dashboard_graph_multi_real.php" id="btn_add" class="btn btn_02">겹쳐보기(비주기측정)</a>
</div>


<?php
include_once ('./_tail.php');
?>
