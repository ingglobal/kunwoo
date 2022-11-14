<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '가동데이터 그래프';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];
?>
<style>
.btn_bigger, .btn_smaller {position:absolute;top:3px;right:30px;z-index:10;}
.btn_bigger {right:60px;}
</style>

<!-- <script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/highcharts.src.js"></script> -->
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/highstock.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/data.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/exporting.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>그래프 좌표갯수 Max값은 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개</span>입니다. (로딩속도 최적화를 필요한 제약입니다.) 시간 범위를 크게 잡더라도 좌표에 표현하는 갯수는 자동으로 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개까지만</span> 표현됩니다. (시작시점 기준)</p>
</div>

<div class="graph_wrap" style="position:relative;">
<a href="javascript:" class="btn_bigger">크게</a>
<a href="javascript:" class="btn_smaller">작게</a>

<div id="chart1" style="width:100%; height:400px;text-align:center;">
    <i class="fa fa-spin fa-spinner" style="margin:40px auto;font-size:4em;"></i>
</div>
<div id="chart2" style="width:100%; height:400px;text-align:center;">
    <i class="fa fa-spin fa-spinner" style="margin:40px auto;font-size:4em;"></i>
</div>
<div id="chart3" style="width:100%; height:400px;text-align:center;">
    <i class="fa fa-spin fa-spinner" style="margin:40px auto;font-size:4em;"></i>
</div>

<script>
// var seriesOptions = [];
    // names = ['MSFT', 'AAPL', 'TEMPERATURE'];

/**
 * Create the chart when all data is loaded
 * @returns {undefined}
 */
function createChart(cid,seriesOptions) {
    var chart = new Highcharts.stockChart({
        chart: {
            renderTo: 'chart'+cid
        },

        animation: false,

        xAxis: {
            // format: '{value:%Y-%m-%e}'
            type: 'datetime',
            labels: {
                formatter: function() {
                    return moment(this.value).format("MM/DD HH:mm");
                }
            }            
        },

        yAxis: {
            // min: -100,  // 크게 확대해서 보려면 -10, 없애버리면 자동 스케일
            // max: 200,   // 크게 확대해서 보려면 20
            scrollbar: {
                enabled: true
            },
            opposite: false,
            tickInterval: 200,
            // minorTickInterval: 5,
            // minorTickLength: 0,
        },

        plotOptions: {
            series: {
                showInNavigator: true
            }
        },

        navigator: {
            enabled: false,
            // xAxis: {
            //     type: 'datetime',
            //     dateTimeLabelFormats: {
            //         second: '%H:%M:%S',
            //         minute: '%H:%M',
            //         hour: '%H:%M',
            //         day: '%m-%d',
            //         week: '%m-%d',
            //         month: '%Y-%m',
            //         year: '%Y-%m'
            //     }
            // },
        },

        rangeSelector: {
            enabled: false,
        },

        tooltip: {
            formatter: function(e) {
                var tooltip1 =  moment(this.x).format("YYYY-MM-DD HH:mm:ss");
                $.each(this.points, function () {
                    // console.log(this);
                    tooltip1 += '<br/><span style="color:' + this.color + '">\u25CF '+this.series.name+'</span>: <b>' + this.y + '</b><br/>';
                });
                return tooltip1;
            },
            split: false,
            shared: true
        },
        series: seriesOptions
        
    });
}

Highcharts.getJSON(
    'http://bogwang.epcs.co.kr/device/json/temperature.php?token=1099de5drf09&mms_idx=1&dta_group=mea&dta_type=1&dta_no=0&start_date=2020-04-23&start_time=20:30:00&end_date=2020-04-23&end_time=21:59:59',
    function(data) {
        var seriesOptions = [];
        seriesOptions[0] = {
            name: '온도0',
            data: data
        };
        createChart(1,seriesOptions);
    }
);

Highcharts.getJSON(
    'http://bogwang.epcs.co.kr/device/json/temperature.php?token=1099de5drf09&mms_idx=1&dta_group=mea&dta_type=1&dta_no=0&start_date=2020-04-23&start_time=20:30:00&end_date=2020-04-23&end_time=21:59:59',
    function(data) {
        var seriesOptions = [];
        // console.log(data);
        var data2 = [];
        var data3 = [];
        seriesOptions[0] = {
            name: '온도1',
            data: data
        };
        for(i=0;i<data.length;i++) {
            data2[i] = [];
            data2[i][0] = data[i][0];
            // // console.log(data[i][1]);
            data2[i][1] = data[i][1] + Math.ceil(Math.random() * 400);
            // console.log('new: '+data[i][1]);
        }
        seriesOptions[1] = {
            name: '온도2',
            data: data2
        };
        for(i=0;i<data.length;i++) {
            data3[i] = [];
            data3[i][0] = data[i][0];
            // // console.log(data[i][1]);
            data3[i][1] = data[i][1] + Math.ceil(Math.random() * 400);
            // console.log('new: '+data[i][1]);
        }
        seriesOptions[2] = {
            name: '온도3',
            data: data3
        };
        createChart(2,seriesOptions);
    }
);

Highcharts.getJSON(
    'http://bogwang.epcs.co.kr/device/json/temperature.php?token=1099de5drf09&mms_idx=1&dta_group=mea&dta_type=1&dta_no=0&start_date=2020-04-23&start_time=20:30:00&end_date=2020-04-23&end_time=21:59:59',
    function(data) {
        var seriesOptions = [];
        seriesOptions[0] = {
            name: '온도4',
            data: data
        };
        createChart(3,seriesOptions);
    }
);



</script>


</div>

<script>
$(function(e) {

});
</script>

<?php
include_once ('./_tail.php');
?>
