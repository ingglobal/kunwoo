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
<script>
var seriesOptions = [],
    names = ['TEMPERATURE'];
    // names = ['MSFT', 'AAPL', 'TEMPERATURE'];

/**
 * Create the chart when all data is loaded
 * @returns {undefined}
 */
function createChart() {
    var chart = new Highcharts.stockChart({
        chart: {
            renderTo: 'chart1'
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
            // xDateFormat: '<b>{this.point.x:%b-%Y}</b>',
            // xDateFormat: '%Y-%m-%d %H:%M:%S', // 점이 몇 개 안 되면 잘 보고이고 좌표가 많으면 다시 원복?
            // xDateFormat: Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', new Date(this.x)), // 점이 몇 개 안 되면 잘 보고이고 좌표가 많으면 다시 원복?
            // pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br>',
            // valueDecimals: 2,
            split: false,
            shared: true
        },
        series: seriesOptions
        
    }, function(chart) {
        // chart.showLoading();
    });
}

// Set the datepicker's date format
$.datepicker.setDefaults({
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
    onSelect: function(dateText) {
        this.onchange();
        this.onblur();
    }
});

function success(data) {
    // url 주소에서 해당 텍스트 검사해서 배열 할당
    var name = this.url.match(/(temperature|msft|aapl)/)[0].toUpperCase();
//    var name = this.url.match(/(msft|aapl)/)[0].toUpperCase();
    var i = names.indexOf(name);
    seriesOptions[i] = {
        name: name,
        data: data
    };
    createChart();
}

//setTimeout(function(){
    Highcharts.getJSON(
        'http://bogwang.epcs.co.kr/device/json/temperature.php?token=1099de5drf09&mms_idx=1&dta_group=mea&dta_type=1&dta_no=0&start_date=2020-04-15&start_time=10:00:00&end_date=2020-04-24&end_time=23:59:59',
        success
    );
//},500);
// Highcharts.getJSON(
//     'http://bogwang.epcs.co.kr/device/json/msft-c1.json',
//     success
// );
// Highcharts.getJSON(
//     'http://bogwang.epcs.co.kr/device/json/aapl-c1.json',
//     success
// );
</script>


</div>

<script>
$(function(e) {

});
</script>

<?php
include_once ('./_tail.php');
?>
