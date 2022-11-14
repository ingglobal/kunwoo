<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '측정(정주기) 그래프';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// mms_idx 디폴트
$mms_idx = ($_REQUEST['mms_idx']) ?: 1;
$mms = get_table_meta('mms','mms_idx',$mms_idx);
$com = get_table_meta('company','com_idx',$mms['com_idx']);

// 디폴트 선택
$dta_group = ($_REQUEST['dta_group']) ?: 'mea';
$dta_type = ($_REQUEST['dta_type']) ?: 1;   // 온도
$dta_no = ($_REQUEST['dta_type']) ?: 0;   // 0번
$graph_type = ($_REQUEST['graph_type']) ?: 'spline'; // 디폴트 그래프 타입

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
.chr_select {display:none;}
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
    <a href="./data_measure_add.php?file_name=<?php echo $g5['file_name']?>" class="btn btn_02 btn_add_chart">추가</a>

    <div style="float:right;">
        mms
        <input type="text" name="mms_idx" value="<?=$mms_idx?>" class="frm_input" style="width:40px;">
        &nbsp;&nbsp;
        type
        <input type="text" name="dta_type" value="<?=$dta_type?>" class="frm_input" style="width:40px;">
        &nbsp;&nbsp;
        no
        <input type="text" name="dta_no" value="<?=$dta_no?>" class="frm_input" style="width:40px;">
        &nbsp;&nbsp;
        <select name="graph_type" style="display:none;">
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
    </div>

    <!-- 컨트롤 -->
    <div class="chr_select" style="text-align:center;">
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
            old_yamp = seriesOptions[chr_idx_chg].data[0].yamp;
            old_ymove = seriesOptions[chr_idx_chg].data[0].ymove;
            // console.log( chr_idx_chg );
            // console.log( old_yamp +'/'+ chr_amp +'//'+ old_ymove +'/'+ chr_move );
            // console.log( '----' );
            // 증폭이나 이동값이 바뀐 경우만 수정 그래프 변형
            if(old_yamp!=chr_amp || old_ymove!=chr_move) {
                for(i=0;i<seriesOptions[chr_idx_chg].data.length;i++) {
                    // console.log(seriesOptions[chr_idx_chg].data[i]);
                    raw_y = seriesOptions[chr_idx_chg].data[i].yraw;    // original Y value
                    amp_y = raw_y*chr_amp;  // amplified value
                    new_y = amp_y+chr_move;  // amplified+moved value
                    // console.log( raw_y +'/'+ chr_amp );
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
var seriesOptions = [],
    dta_types = ['타입명','온도','토크','전류','전압','진동','소리','습도','압력','속도'],
    chart, options;

function createChart() {
    // var chart = new Highcharts.stockChart({
    options = {
        chart: {
            renderTo: 'chart1',
            type: 'spline',   // line, spline, area, areaspline, column, bar, pie, scatter, gauge, arearange, areasplinerange, columnrange
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
                        var chr_id = this.userOptions.id;
                        // var chr_idx = this.userOptions._symbolIndex;
                        var chr_idx = this.userOptions._colorIndex; // _symbolIndex가 undefined일 때가 있어서 _colorIndex로 대체함
                        var chr_name = this.userOptions.name;
                        var old_amp = this.userOptions.data[0].yamp;
                        var old_move = this.userOptions.data[0].ymove;
                        $('#fchart input[name=chr_id]').val(chr_id);
                        $('#fchart input[name=chr_idx]').val(chr_idx);
                        $('#fchart .chr_name').text(chr_name);

                        // reset value of amplification, move
                        chr_amp_slider.slider("option", "value", old_amp);
                        chr_move_slider.slider("option", "value", old_move);
                        $('#chr_amp_value').val(old_amp);
                        $('#chr_move_value').val(old_move);

                        $('.chr_select').show();
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
    dta_loading('hide');
    removeLogo();
    // console.log( chart.series );
    
}

// 그래프 호출 =============================================
$(document).on('click','#fsearch button[type=submit]',function(e){
    e.preventDefault();
    var frm = $('#fsearch');
    var dta_group = 'mea';
    var mms_idx = frm.find('input[name=mms_idx]').val();
    var dta_type = frm.find('input[name=dta_type]').val() || 1;
    var dta_no = frm.find('input[name=dta_no]').val() || 0;
    var shf_no = frm.find('input[name=shf_no]').val() || '';
    var dta_item = frm.find('select[name=dta_item]').val() || '';   // 일,주,월,년,분,초
    var dta_file = (dta_item=='minute'||dta_item=='second') ? '' : '.sum'; // measure.php(그룹핑), measure.sum.php(일자이상)
    var dta_unit = frm.find('input[name=dta_unit]:checked').val() || '';   // 10,20,30,60
    var st_date = frm.find('#st_date').val() || '';
    var st_time = frm.find('#st_time').val() || '';
    var en_date = frm.find('#en_date').val() || '';
    var en_time = frm.find('#en_time').val() || '';
    var chr_id = mms_idx+'_'+dta_type+'_'+dta_no;
    var chr_exists = null;
    for(i=0;i<seriesOptions.length;i++) {
        // console.log(seriesOptions[i]);
        // console.log(seriesOptions[i].id);
        if(seriesOptions[i].id==chr_id) {
            chr_exists = 1;
            chr_idx = i;
        }
    }
    if(chr_exists) {
        // alert('이미 적용된 그래프입니다.');
        // return false;
    }
    else {
        chr_idx = chart ? (chart.series.length/2) : 0;  // navigator series가 있으므로 2배 숫자가 나옴 (navigation series가 있어서 따로 +1을 하지 않아도 됨)
    }
    dta_loading('show');

    dta_name = dta_types[dta_type]+dta_no;
    var dta_url = '//bogwang.epcs.co.kr/device/json/measure'+dta_file+'.php?token=1099de5drf09&mms_idx='+mms_idx+'&shf_no='+shf_no+'&dta_type='+dta_type+'&dta_no='+dta_no+'&dta_item='+dta_item+'&dta_unit='+dta_unit+'&st_date='+st_date+'&st_time='+st_time+'&en_date='+en_date+'&en_time='+en_time;
    // console.log(dta_url);

    Highcharts.getJSON(
        dta_url,
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

            // 폼에 날짜값이 없으면 JSON에서 날짜 추출해서 폼값에 입력!
            if( $('#st_date').val() == '' )
                $('#st_date').val(st_date_new);
            if( $('#st_time').val() == '' )
                $('#st_time').val(st_time_new);
            if( $('#en_date').val() == '' )
                $('#en_date').val(en_date_new);
            if( $('#en_time').val() == '' )
                $('#en_time').val(en_time_new);

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
$('#fsearch button[type=submit]').trigger('click');

// 추가 button, window popup
$(document).on('click','.btn_add_chart',function(e){
    e.preventDefault();
    var frm = $('#fsearch');
    var com_idx = '<?=$com['com_idx']?>';
    var mms_idx = frm.find('input[name=mms_idx]').val();
    var dta_type = frm.find('input[name=dta_type]').val() || '';
    var st_date = frm.find('#st_date').val() || '';
    var st_time = frm.find('#st_time').val() || '';
    var en_date = frm.find('#en_date').val() || '';
    var en_time = frm.find('#en_time').val() || '';
    if(st_date==''||en_date=='') {
        alert('검색 날짜를 먼저 입력하세요.');
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
        $('.chr_select').hide();
    }

});

// 차트 설정 닫기
$(document).on('click','.btn_chr_close',function(e){
    e.preventDefault();
    $('.chr_select').hide();
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
        // var chr_idx_chg = $('#fchart').find('input[name=chr_idx]').val();
        // var chr_amp = ui.value;
        // if(chr_idx_chg!='') {
        //     // console.log( seriesOptions[chr_idx_chg].data );
        //     old_y1 = seriesOptions[chr_idx_chg].data[0].y;
        //     new_y1 = seriesOptions[chr_idx_chg].data[0].yraw*chr_amp;
        //     if(old_y1!=new_y1) {
        //         for(i=0;i<seriesOptions[chr_idx_chg].data.length;i++) {
        //             // console.log(seriesOptions[chr_idx_chg].data[i]);
        //             raw_y = seriesOptions[chr_idx_chg].data[i].yraw;    // original Y value
        //             // console.log( raw_y +'/'+ chr_amp );
        //             seriesOptions[chr_idx_chg].data[i].yamp = chr_amp;
        //             seriesOptions[chr_idx_chg].data[i].y = raw_y*chr_amp;
        //             // console.log(seriesOptions[chr_idx_chg].data[i].y);
        //         }
        //         createChart();
        //     }
        // }
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
        // var chr_idx_chg = $('#fchart').find('input[name=chr_idx]').val();
        // var chr_move = ui.value;
        // if(chr_idx_chg!='') {
        //     // console.log( seriesOptions[chr_idx_chg].data );
        //     old_y1 = seriesOptions[chr_idx_chg].data[0].y;
        //     new_y1 = seriesOptions[chr_idx_chg].data[0].yraw*chr_amp;
        //     if(old_y1!=new_y1) {
        //         for(i=0;i<seriesOptions[chr_idx_chg].data.length;i++) {
        //             // console.log(seriesOptions[chr_idx_chg].data[i]);
        //             cur_y = seriesOptions[chr_idx_chg].data[i].yraw * seriesOptions[chr_idx_chg].data[i].yamp;    // yraw*yamp value
        //             // console.log( raw_y +'/'+ chr_amp );
        //             seriesOptions[chr_idx_chg].data[i].ymove = chr_move;
        //             seriesOptions[chr_idx_chg].data[i].y = cur_y+chr_move;
        //             // console.log(seriesOptions[chr_idx_chg].data[i].y);
        //         }
        //         createChart();
        //     }
        // }
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

    // 일괄입력
    $(document).on('click','.btn_insert',function(e){
        e.preventDefault();
        if(confirm('하루치(1일) 데이타를 입력합니다. 창을 닫지 마세요. 입력을 시작합니다.')) {
            winDataInsert = window.open('<?=G5_USER_ADMIN_URL?>/convert/data_measure1.php', "winDataInsert", "left=100,top=100,width=520,height=600,scrollbars=1");
            winDataInsert.focus();
            return false;
        }
        return false;
    });

});
</script>

<div class="btn_fixed_top">
    <a href="./data_measure_chart.php" id="btn_add" class="btn btn_02">Chart(정주기)</a>
    <a href="./data_measure_real_chart.php" id="btn_add" class="btn btn_02">Chart(실측,비주기)</a>
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <button class="btn_02 btn btn_insert">일괄입력</button>
    <?php } ?>
    <a href="./data_measure_list.php" id="btn_add" class="btn btn_01">목록</a>
</div>


<?php
include_once ('./_tail.php');
?>
