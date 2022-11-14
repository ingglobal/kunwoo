<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<script>
//######################## 구분타입별 비가동(#chart_type) [Column with rotated labels]#############################
Highcharts.chart('chart_type', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        type: 'category',
        labels: {
            rotation: -45,
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    },
    yAxis: {
        min: 0,
        title: {
            text: '비가동횟수(Alarm count)'
        }
    },
    legend: {
        enabled: false
    },
    tooltip: {
        pointFormat: '비가동: <b>{point.y:.1f} 회</b>'
    },
    series: [{
        name: 'Population',
        data: [
            <?php
            for($i=0;$i<count($err_type_cat);$i++){
                if($i > 0) echo ',';
                echo '[
                   "'.$err_type_cat[$i][0].'",
                   '.(int)$err_type_cat[$i][1].' 
                ]';
            }
            ?>
        ],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#FFFFFF',
            align: 'right',
            format: '{point.y:.1f}', // one decimal
            y: 10, // 10 pixels down from the top
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    }],
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});
//######################## 설비별 비가동(#chart_facility) [Pie with gradient fill]#############################
// Radialize the colors
Highcharts.setOptions({
    colors: Highcharts.map(Highcharts.getOptions().colors, function (color) {
        return {
            radialGradient: {
                cx: 0.5,
                cy: 0.3,
                r: 0.7
            },
            stops: [
                [0, color],
                [1, Highcharts.color(color).brighten(-0.3).get('rgb')] // darken
            ]
        };
    })
});

// Build the chart
Highcharts.chart('chart_facility', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: '전체 발생횟수(<?=$err_faci_tot?>회)'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                connectorColor: 'silver'
            }
        }
    },
    series: [{
        name: '비율',
        data: [
            <?php
            for($i=0;$i<count($err_faci_cat);$i++){
                if($i > 0) echo ',';
                echo "
                    { 'name':'".$err_faci_cat[$i][0]."',
                      'y':".(float)$err_faci_cat[$i][1]."
                    }
                ";
            }
            ?>
        ]
    }],
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});
//######################## 비가동 발생횟수(#chart_occur) [Column with rotated labels]#############################
Highcharts.chart('chart_occur', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        type: 'category',
        labels: {
            rotation: -55,
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    },
    yAxis: {
        min: 0,
        title: {
            text: '비가동횟수(Alarm count)'
        }
    },
    legend: {
        enabled: false
    },
    tooltip: {
        pointFormat: '비가동: <b>{point.y:.1f} 회</b>'
    },
    series: [{
        name: 'Population',
        data: [
            <?php
            for($i=0;$i<count($err_occur_cat);$i++){
                if($i > 0) echo ',';
                echo '[
                   "'.$err_occur_cat[$i][0].'",
                   '.(int)$err_occur_cat[$i][1].' 
                ]';
            }
            ?>
        ],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#FFFFFF',
            align: 'right',
            format: '{point.y:.1f}', // one decimal
            y: 10, // 10 pixels down from the top
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    }],
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});
//######################## 일자별 비가동(#chart_day) [Column with rotated labels]#############################
Highcharts.chart('chart_day', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        type: 'category',
        labels: {
            rotation: -55,
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    },
    yAxis: {
        min: 0,
        title: {
            text: '비가동횟수(Alarm count)'
        }
    },
    legend: {
        enabled: false
    },
    tooltip: {
        pointFormat: '비가동: <b>{point.y:.1f} 회</b>'
    },
    series: [{
        name: 'Population',
        data: [
            <?php
            for($i=0;$i<count($err_day_cat);$i++){
                if($i > 0) echo ',';
                echo '[
                   "'.$err_day_cat[$i][0].'",
                   '.(int)$err_day_cat[$i][1].' 
                ]';
            }
            ?>
        ],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#FFFFFF',
            align: 'right',
            format: '{point.y:.1f}', // one decimal
            y: 10, // 10 pixels down from the top
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    }],
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});
//######################## 주간별 비가동(#chart_weekly) [Column with rotated labels]#############################
Highcharts.chart('chart_weekly', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        type: 'category',
        labels: {
            rotation: -55,
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    },
    yAxis: {
        min: 0,
        title: {
            text: '비가동횟수(Alarm count)'
        }
    },
    legend: {
        enabled: false
    },
    tooltip: {
        pointFormat: '비가동: <b>{point.y:.1f} 회</b>'
    },
    series: [{
        name: 'Population',
        data: [
            <?php
            for($i=0;$i<count($err_weekly_cat);$i++){
                if($i > 0) echo ',';
                echo '[
                   "'.$err_weekly_cat[$i][0].'",
                   '.(int)$err_weekly_cat[$i][1].' 
                ]';
            }
            ?>
        ],
        dataLabels: {
            enabled: true,
            rotation: -90,
            color: '#FFFFFF',
            align: 'right',
            format: '{point.y:.1f}', // one decimal
            y: 10, // 10 pixels down from the top
            style: {
                fontSize: '13px',
                fontFamily: 'Verdana, sans-serif'
            }
        }
    }],
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});
</script>