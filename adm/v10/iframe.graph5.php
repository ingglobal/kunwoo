<?php
include_once('./_common.php');

// mms_idx 는 반드시 존재해야 한다.
if(!$mms_idx)
    goto_url('./iframe.empty.php');

// mms_idx 디폴트
$mms_idx = ($_REQUEST['mms_idx']) ?: 1;
$mms = get_table_meta('mms','mms_idx',$mms_idx);
$com = get_table_meta('company','com_idx',$mms['com_idx']);
$mms['mms_set_output'] = $mms['mms_set_output'] ?: 'shift';    // 값이 없으면 날짜 기준

$g5['title'] = '그래프';
include_once('./_head.sub.php');

// mbd_idx 가 존재하면 저장된 값에서 그래프 추출
if($mbd_idx) {
    $mbd = get_table_meta('member_dash','mbd_idx',$mbd_idx);
    // print_r2($mbd);
    $mbd['sried'] = get_serialized($mbd['mbd_setting']);
    $mbd['data'] = json_decode($mbd['sried']['data_series'],true);
    unset($mbd['mbd_setting']);
    unset($mbd['sried']['data_series']);
    // print_r2($mbd);
    // for($j=0;$j<sizeof($mbd['data']);$j++) {
    //     // print_r2($mbd['data'][$j]);
    // }
    // 디폴트 선택값 추출
    $st_date = $mbd['sried']['st_date'];
    $st_time = $mbd['sried']['st_time'];
    $en_date = $mbd['sried']['en_date'];
    $en_time = $mbd['sried']['en_time'];
    $dta_item = $mbd['sried']['dta_item'];
    $dta_unit = $mbd['sried']['dta_unit'];
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
    
}
// 디폴트인 경우
else {
    // 자바스크립트로 뒤쪽에서 ajax로 불러와서 디폴트 값을 넣어줘야 함
    // // 디폴트 선택
    // $dta_group = ($_REQUEST['dta_group']) ?: 'product'; // 디폴트 = 생산량(product)
    // $dta_type = ($_REQUEST['dta_type']) ?: 1;   // 온도
    // $dta_no = ($_REQUEST['dta_type']) ?: 0;   // 0번
    // $graph_type = ($_REQUEST['graph_type']) ?: 'spline'; // 디폴트 그래프 타입

    // // 그룹 선택에 따른 초기 dta_item, dta_unit 설정 (user.07.intra.default.php 참조)
    // $dta_item = ($dta_item) ?: $g5['set_graph_'.$dta_group]['default0'];
    // $dta_unit = ($dta_unit) ?: $g5['set_graph_'.$dta_group]['default1'];
    // $dta_unit = ($dta_unit) ?: 1;   // dta_unit가 환경설정값에도 없으면 1로 디폴트 설정
    // if($dta_item=='minute'||$dta_item=='second') {
    //     // 분,초인 경우
    //     $dta_minsec = 1;
    // }
    // else {
    //     // 분,초가 아니면 무조건 1
    //     $dta_unit = 1;
    //     $en_time = '23:59:59';
    // }
}
// echo $dta_item.'<br>';
// echo $dta_unit.'<br>';
?>
<style>
#graph_wrapper {padding:0 5px;}
/* .local_sch01 {margin:0 0 10px;} */
.xbuttons {float:right;margin-right:8px;margin-bottom:3px;}
.xbuttons a {margin-left:2px;border:solid 1px #ddd;padding:2px 6px;}
.graph_wrap {position:relative;}
#report {display:none;position:absolute;top:0;left:0;}
#report span {border:solid 1px #bbb;}
#fchart {margin:0 0;}
#fchart .chr_name {font-weight:bold;font-size:1.5em;margin-right:6px;}
.table01 {width:auto;margin:0 auto;}
.table01 td {padding:7px 9px;}
.table01 td input {width:84px;-moz-outline: none;outline: none;ie-dummy: expression(this.hideFocus=true);}
.ui-slider-handle {-moz-outline: none;outline: none;ie-dummy: expression(this.hideFocus=true);}
.chr_control {display:none;}
#chr_type, #chr_line {height:30px;-moz-outline: none;outline: none;ie-dummy: expression(this.hideFocus=true);}
.chart_empty {text-align:center;height:200px;line-height:200px;}
.local_sch div {margin: 0;}
</style>

<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/highstock.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/data.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/exporting.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<div id="graph_wrapper">
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

    <div class="div_btn_add" style="float:right;display:none;">
        <a href="./data_graph_add.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_03 btn_add_chart"><i class="fa fa-bar-chart"></i>불러오기</a>
        <a href="javascript:alert('설정된 그래프를 대시보드로 내보냅니다.');" class="btn btn_03 btn_add_dash"><i class="fa fa-line-chart"></i> 내보내기</a>
    </div>
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>좌표갯수 최대값(Max)은 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개</span>입니다. (로딩속도 최적화를 위한 설정입니다.) 시간 범위를 크게 잡더라도 좌표 갯수는 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개까지만</span> 표현됩니다. (시작시점 기준)</p>
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
        <input type="hidden" name="chr_idx" style="width:20px">
    
            <table class="table01" style="">
            <tbody>
                <tr>
                    <td class="td_" style="vertical-align:bottom;">
                        <span class="chr_name">항목명</span>
                    </td>
                    <td class="td_" style="display:no ne;">
                        <label for="chr_amp_value">증폭</label>
                        <input type="text" id="chr_amp_value" style="border:0; color:#f6931f; font-weight:bold;" autocomplete="off">
                        <div id="chr_amp" style="width:150px;"></div>
                    </td>
                    <td class="td_" style="display:no ne;">
                        <label for="chr_move_value">이동</label>
                        <input type="text" id="chr_move_value" style="border:0; color:#f6931f; font-weight:bold;" autocomplete="off">
                        <div id="chr_move" style="width:150px;"></div>
                    </td>
                    <td class="td_" style="padding-top:14px;">
                        <select name="chr_type" id="chr_type" autocomplete="off">
                            <option value="">그래프타입</option>
                            <option value="spline">꺽은선1</option>
                            <option value="line">꺽은선2</option>
                            <option value="column">막대</option>
                        </select>
                    </td>
                    <td class="td_" style="padding-top:14px;">
                        <select name="chr_line" id="chr_line" autocomplete="off">
                            <option value="">선종류</option>
                            <option value="solid">실선</option>
                            <option value="shortdot">점선</option>
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
    // 컨트롤 부분 변경
    $("#fchart").on('submit', function(e){
        e.preventDefault();
        var frm = $('#fchart');
        // console.log( frm.find('#chr_amp_value').val() );
        // console.log( frm.find('#chr_move_value').val() );

        var chr_idx_chg = parseInt(frm.find('input[name=chr_idx]').val());
        var chr_amp = parseFloat(frm.find('#chr_amp_value').val());
        var chr_move = parseInt(frm.find('#chr_move_value').val());
        var chr_type = frm.find('#chr_type').val();
        var chr_line = frm.find('#chr_line').val();
        
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
            // console.log(seriesOptions[chr_idx_chg]);
            old_type = seriesOptions[chr_idx_chg].type;
            old_line = seriesOptions[chr_idx_chg].dashStyle;
            old_yamp = seriesOptions[chr_idx_chg].data[0].yamp;
            old_ymove = seriesOptions[chr_idx_chg].data[0].ymove;
            // console.log( chr_idx_chg );
            // console.log( old_yamp +'/'+ chr_amp +'//'+ old_ymove +'/'+ chr_move +'//'+ old_type +'/'+ chr_type );
            // console.log( '----' );
            // 증폭, 이동값, 그래프 종류가 바뀐 경우만 수정 그래프 변형
            if(old_yamp!=chr_amp || old_ymove!=chr_move || old_type!=chr_type || old_line!=chr_line) {
                for(i=0;i<seriesOptions[chr_idx_chg].data.length;i++) {
                    // console.log(seriesOptions[chr_idx_chg].data[i]);
                    raw_y = seriesOptions[chr_idx_chg].data[i].yraw;    // original Y value
                    amp_y = raw_y*chr_amp;  // amplified value
                    new_y = amp_y+chr_move;  // amplified+moved value
                    // console.log( raw_y +'/'+ chr_amp );
                    seriesOptions[chr_idx_chg].type = chr_type;
                    seriesOptions[chr_idx_chg].dashStyle = chr_line;
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

</div><!-- .graph_wrap -->
</div><!-- #graph_wrapper -->

<script>
var graphs2 = [], seriesOptions = [], data_series = [], graph_type = 'spline', graph_line = 'solid',
    mms_set_output = '<?=$mms['mms_set_output']?>',
    seriesCounter = 0, chart, options;

// ======================================================================
// graphs attr in in chart div
// 변수가 바뀌면 graph_id를 바꿔줘야 합니다. 테스트하려면 주석 해제 후 [확인]만 하면 됩니다.
// graphs[0] = {
//     dta_data_url: "bogwang.epcs.co.kr/device/json",
//     dta_json_file: "measure",
//     dta_group: "mea",
//     mms_idx: 7,
//     dta_type: 1,
//     dta_no: 0,
//     shf_no: 0,
//     dta_mmi_no: 0,
//     dta_defect: "0,1",
//     dta_defect_type: 0, // 1,2,3,4...
//     dta_code: '',    // only if err, pre
//     graph_type: 'spline',
//     graph_line: 'solid',
//     graph_name: '측정1',
//     graph_id: 'bWVhc3VyZV9tZWFfN18xXzBfMF8wXzAsMV8wXw'
// };
// graphs[1] = {
//     dta_data_url: "bogwang.epcs.co.kr/device/json",
//     dta_json_file: "output",
//     dta_group: "product",
//     mms_idx: 7,
//     dta_type: 1,
//     dta_no: 0,
//     shf_no: 0,
//     dta_mmi_no: 0,
//     dta_defect: "0,1",
//     dta_defect_type: 0, // 1,2,3,4...
//     dta_code: '',    // only if err, pre
//     graph_type: 'spline',
//     graph_line: 'solid',
//     graph_name: '생산1',
//     graph_id: 'b3V0cHV0X3Byb2R1Y3RfN18xXzBfMF8wXzAsMV8wXw'
// };
// $("#chart1").attr("graphs",JSON.stringify(graphs));


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
    var para = urlParaToJSON2(this.url); // get values from Json Url
    // console.log(this.url);
    var graph_id = para.graph_id;
    // console.log('기준: '+graph_id);
    
    // idx 찾기 (비동기라 어떤 idx가 왔는지 모르기 때문!!)
    if( $("#chart1").attr("graphs") != undefined ) {
        graphs =  JSON.parse( $("#chart1").attr("graphs") );
        for(i=0;i<graphs.length;i++) {
            if( graph_id == graphs[i].graph_id ) {
                var chr_idx = i;
            }
        }
        // console.log(chr_idx + ' arrived.');
            
        // console.log(graphs[chr_idx]);
        // 해당하는 graphs 배열에서 값을 뽑아서 graph_id 를 생성
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
            graph_name: graphs[chr_idx].graph_name,
            graph_id: graph_id1
        };

        // data variable definition <<<<==============================================
        seriesOptions[chr_idx] = {
            name: decodeURIComponent(graphs[chr_idx].graph_name),
            id:chr_id,
            type: graphs[chr_idx].graph_type,
            dashStyle: graphs[chr_idx].graph_line,
            data: data
        };

        // Create chart when all data loaded.
        seriesCounter += 1;
        if (seriesCounter == graphs.length) {
            console.log('graph drawing .................................');
            console.log('seriesOptions length: ' + seriesOptions.length);
            createChart();
        }


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

    // get the graphs attribute form target object div
    var graphs =  JSON.parse( $("#chart1").attr("graphs") );
    // console.log(graphs);
    // console.log(graphs.length);

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
        var graph_name = graphs[i].graph_name;
        var graph_id1 = getGraphId(dta_json_file, dta_group, mms_idx, dta_type, dta_no, shf_no, dta_mmi_no, dta_defect, dta_defect_type, dta_code);
        // console.log(i+'. '+graph_id1);

        // 그래프 호출 URL
        var dta_url = '//'+dta_data_url+'/'+dta_json_file+dta_file+'.php?token=1099de5drf09'
                        +'&mms_idx='+mms_idx+'&dta_group='+dta_group+'&shf_no='+shf_no+'&dta_mmi_no='+dta_mmi_no
                        +'&dta_type='+dta_type+'&dta_no='+dta_no
                        +'&dta_defect='+dta_defect+'&dta_defect_type='+dta_defect_type
                        +'&dta_code='+dta_code
                        +'&dta_item='+dta_item+'&dta_unit='+dta_unit
                        +'&st_date='+st_date+'&st_time='+st_time+'&en_date='+en_date+'&en_time='+en_time
                        +'&graph_id='+graph_id1;
        console.log(dta_url);

        Highcharts.getJSON(
            dta_url,
            drawChart
        );

    }
    dta_loading('hide');
    $('.div_btn_add').show();

});

// ==========================================================================================
// 대시보드에서 불러온 그래프 추가 ==================================================================
// ==========================================================================================
<?php
// mbd_idx 가 존재하면 저장된 값에서 그래프 추출
if($mbd_idx) {
    for($j=0;$j<sizeof($mbd['data']);$j++) {
        // print_r2($mbd['data'][$j]);
        // $datas = explode("_",$mbd['data'][$j]['id']);
        // target should be from local
        if($mbd['data'][$j]['id']['dta_json_file']=='output.target') {
            $mbd['data'][$j]['id']['dta_data_url'] = strip_http(G5_ADMIN_URL).'/v10/ajax';
        }
        $mbd['dta_data_url'] = $mbd['data'][$j]['id']['dta_data_url'];
        $mbd['dta_json_file'] = $mbd['data'][$j]['id']['dta_json_file'];
        $mbd['dta_group'] = $mbd['data'][$j]['id']['dta_group'];
        ?>
            var graph_id1 = getGraphId('<?=$mbd['dta_json_file']?>','<?=$mbd['dta_group']?>','<?=$mbd['data'][$j]['id']['mms_idx']?>','<?=$mbd['data'][$j]['id']['dta_type']?>','<?=$mbd['data'][$j]['id']['dta_no']?>','<?=$mbd['data'][$j]['id']['shf_no']?>','<?=$mbd['data'][$j]['id']['dta_mmi_no']?>','<?=$mbd['data'][$j]['id']['dta_defect']?>','<?=$mbd['data'][$j]['id']['dta_defect_type']?>','<?=$mbd['data'][$j]['id']['dta_code']?>');
            // console.log(graph_id1);
            graphs2[<?=$j?>] = {
                dta_data_url: "<?=$mbd['dta_data_url']?>",
                dta_json_file: "<?=$mbd['dta_json_file']?>",
                dta_group: "<?=$mbd['dta_group']?>",
                mms_idx: <?=$mbd['data'][$j]['id']['mms_idx']?>,
                dta_type: "<?=$mbd['data'][$j]['id']['dta_type']?>",
                dta_no: "<?=$mbd['data'][$j]['id']['dta_no']?>",
                shf_no: "<?=$mbd['data'][$j]['id']['shf_no']?>",
                dta_mmi_no: "<?=$mbd['data'][$j]['id']['dta_mmi_no']?>",
                dta_defect: "<?=$mbd['data'][$j]['id']['dta_defect']?>",
                dta_defect_type: "<?=$mbd['data'][$j]['id']['dta_defect_type']?>",
                dta_code: "<?=$mbd['data'][$j]['id']['dta_code']?>",
                graph_type: "<?=$mbd['data'][$j]['type']?>",
                graph_line: "<?=$mbd['data'][$j]['dashStyle']?>",
                graph_name: "<?=$mbd['data'][$j]['id']['graph_name']?>",
                graph_id: graph_id1
            };
        <?php
    }
    echo '$("#chart1").attr("graphs",JSON.stringify(graphs2));';
    echo 'console.log( $("#chart1").attr("graphs") );';
    // [확인] 버튼 클릭
    echo "$('#fsearch button[type=submit]').trigger('click');";
}
// mbd_idx 가 없으면 기본 디폴트 그래프 한개
else {
?>
    // 이게 위치가 클릭 선언 뒤쪽으로 와야 하는구만!
    // $('#fsearch button[type=submit]').trigger('click');
<?php
}
?>

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

// 그래프 내보내기
$(document).on('click','.btn_add_dash',function(e){
    e.preventDefault();
    if(seriesOptions.length == 0) {
        alert('내보내기할 그래프가 없습니다.');
        return false;
    }
    if(confirm('설정된 그래프를 대시보드로 내보시겠습니까?')) {
        // 폼 설정값 
        var frm_serialized = $('#fsearch').serialize();

        // 그래프 설정값
        // console.log(seriesOptions);
        for(i=0;i<seriesOptions.length;i++) {
            // console.log(seriesOptions[i]);
            data_series[i] = {
                name: seriesOptions[i].name,
                id: seriesOptions[i].id,
                type:seriesOptions[i].type,
                dashStyle:seriesOptions[i].dashStyle
            };
        }
        // console.log(frm_serialized);
        // console.log(data_series);
        //-- 디버깅 Ajax --//
        $.ajax({
            url:g5_user_admin_ajax_url+'/dash.php',
            data:{"aj":"put","mms_idx":"<?=$mms_idx?>","mbd_idx":"<?=$mbd_idx?>","frm_data":frm_serialized,"data_series":data_series},
            dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
                // console.log(res);
                //var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
                if(res.result == true) {
                    self.location.href = './iframe.index.php?mms_idx=<?=$mms_idx?>';
                }
                else {
                    alert(res.msg);
                }
            },
            error:function(xmlRequest) {
                alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
                    + ' \n\rresponseText: ' + xmlRequest.responseText);
            } 
        });
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
    <?php
    if(!$mbd_idx) {
    ?>
        $.ajax({
            url:'//bogwang.epcs.co.kr/device/json/output.default.php',
            data:{"token":"1099de5drf09","mms_idx":"<?=$mms_idx?>"},
            dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
                console.log(res);
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
                $('.div_btn_add').show();

            },
            error:function(xmlRequest) {
                alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
                    + ' \n\rresponseText: ' + xmlRequest.responseText);
            } 
        });
    <?php
    }
    ?>

});

// 부모창에 나의 높이를 전달
parent.postMessage(document.body.scrollHeight+100,"<?=G5_URL?>"); // 부모창의 URL 주소

// 부모창 선택 영역 색상 변경
$('.mms_icons a', parent.document).removeClass('on');
$('.icon_graph', parent.document).addClass('on');
</script>

<?php
include_once('./_tail.sub.php');
?>
