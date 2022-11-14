<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '가동데이터 그래프';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];
?>
<!-- <script src="<?php echo G5_URL?>/lib/highcharts/Highcharts/code/highcharts.src.js"></script> -->
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/highstock.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/data.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/exporting.js"></script>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>그래프 좌표갯수 Max값은 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개</span>입니다. (로딩속도 최적화를 필요한 제약입니다.) 시간 범위를 크게 잡더라도 좌표에 표현하는 갯수는 자동으로 <span class="color_red"><?=$g5['setting']['set_graph_max']?>개까지만</span> 표현됩니다. (시작시점 기준)</p>
</div>

<div class="graph_wrap">

<div id="container" style="width:100%; height:400px;"></div>
<script>
Highcharts.getJSON('https://cdn.jsdelivr.net/gh/highcharts/highcharts@v7.0.0/samples/data/large-dataset.json',
  function (data) {
    Highcharts.stockChart('container', {
        chart: {
            zoomType: 'xy'
        },
        title: {
            text: 'Scrollbar on Y axis'
        },
        subtitle: {
            text: 'Zoom in to see the scrollbar'
        },
        yAxis: {
            scrollbar: {
                enabled: true,
                showFull: false
            }
        },
        series: [{
            data: data.data,
            pointStart: data.pointStart,
            pointInterval: data.pointInterval
        }]
    });
});
</script>

</div>

<script>
$(function(e) {

});
</script>

<?php
include_once ('./_tail.php');
?>
