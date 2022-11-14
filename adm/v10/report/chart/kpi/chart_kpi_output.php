<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<script>
//######################## 설비별 생산(#chart_facility) [Stacked column chart]#############################
var prd_fac_name = <?=json_encode($prd_fac_name)?>;
var prd_fac_cat = <?=json_encode($prd_fac_cat)?>;
//var prd_fac_cnt = prd_fac_name.length;
//console.log(prd_fac_name);
Highcharts.chart('chart_facility', {
    chart: {
        type: 'column'
    },
    title: '',
    xAxis: {
        categories: prd_fac_name
    },
    yAxis: {
        min: 0,
        title: {
            text: '생산수량'
        },
        stackLabels: {
            enabled: true,
            style: {
                fontWeight: 'bold',
                color: ( // theme
                    Highcharts.defaultOptions.title.style &&
                    Highcharts.defaultOptions.title.style.color
                ) || 'gray'
            }
        }
    },
    legend: {
        align: 'right',
        x: -0,
        verticalAlign: 'top',
        y: -10,
        floating: false,
        backgroundColor:
            Highcharts.defaultOptions.legend.backgroundColor || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
    },
    tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
    },
    plotOptions: {
        column: {
            stacking: 'normal',
            dataLabels: {
                enabled: true
            }
        }
    },
    series: prd_fac_cat,
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});

//######################## 교대별 생산(#chart_alternating) [Monthly Average Rainfall]#############################
var prd_alt_name = <?=json_encode($prd_alt_name)?>;
var prd_alt_cat = <?=json_encode($prd_alt_cat)?>;
//var prd_alt_cnt = prd_alt_name.length;
//console.log(prd_alt_name);
Highcharts.chart('chart_alternating', {
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
        categories: prd_alt_name,
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: '생산수량 (E/A)'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} E/A</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: prd_alt_cat,
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});

//######################## 기종별 생산(#chart_mode) [Stacked column chart]#############################
var prd_mod_name = <?=json_encode($prd_mod_name)?>;
var prd_mod_cat = <?=json_encode($prd_mod_cat)?>;
//var prd_fac_cnt = prd_fac_name.length;
//console.log(prd_fac_name);
Highcharts.chart('chart_mode', {
    chart: {
        type: 'column'
    },
    title: '',
    xAxis: {
        categories: prd_mod_name
    },
    yAxis: {
        min: 0,
        title: {
            text: '생산수량'
        },
        stackLabels: {
            enabled: true,
            style: {
                fontWeight: 'bold',
                color: ( // theme
                    Highcharts.defaultOptions.title.style &&
                    Highcharts.defaultOptions.title.style.color
                ) || 'gray'
            }
        }
    },
    legend: {
        align: 'right',
        x: -0,
        verticalAlign: 'top',
        y: -10,
        floating: false,
        backgroundColor:
            Highcharts.defaultOptions.legend.backgroundColor || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
    },
    tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
    },
    plotOptions: {
        column: {
            stacking: 'normal',
            dataLabels: {
                enabled: true
            }
        }
    },
    series: prd_mod_cat,
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});

//######################## 일자별 생산(#chart_day) [Stacked column chart]#############################
var prd_day_name = <?=json_encode($prd_day_name)?>;
var prd_day_cat = <?=json_encode($prd_day_cat)?>;
//var prd_fac_cnt = prd_fac_name.length;
//console.log(prd_fac_name);
Highcharts.chart('chart_day', {
    chart: {
        type: 'column'
    },
    title: '',
    xAxis: {
        categories: prd_day_name
    },
    yAxis: {
        min: 0,
        title: {
            text: '생산수량'
        },
        stackLabels: {
            enabled: true,
            style: {
                fontWeight: 'bold',
                color: ( // theme
                    Highcharts.defaultOptions.title.style &&
                    Highcharts.defaultOptions.title.style.color
                ) || 'gray'
            }
        }
    },
    legend: {
        align: 'right',
        x: -0,
        verticalAlign: 'top',
        y: -10,
        floating: false,
        backgroundColor:
            Highcharts.defaultOptions.legend.backgroundColor || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
    },
    tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
    },
    plotOptions: {
        column: {
            stacking: 'normal',
            dataLabels: {
                enabled: true
            }
        }
    },
    series: prd_day_cat,
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});
//######################## 주간별 생산(#chart_weekly) [Stacked bar chart]#############################
var prd_weekly_name = <?=json_encode($prd_weekly_name)?>;
var prd_weekly_cat = <?=json_encode($prd_weekly_cat)?>;
Highcharts.chart('chart_weekly', {
    chart: {
        type: 'column'
    },
    title: '',
    xAxis: {
        categories: prd_weekly_name
    },
    yAxis: {
        min: 0,
        title: {
            text: '생산수량'
        },
        stackLabels: {
            enabled: true,
            style: {
                fontWeight: 'bold',
                color: ( // theme
                    Highcharts.defaultOptions.title.style &&
                    Highcharts.defaultOptions.title.style.color
                ) || 'gray'
            }
        }
    },
    legend: {
        align: 'right',
        x: -0,
        verticalAlign: 'top',
        y: -10,
        floating: false,
        backgroundColor:
            Highcharts.defaultOptions.legend.backgroundColor || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
    },
    tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
    },
    plotOptions: {
        column: {
            stacking: 'normal',
            dataLabels: {
                enabled: true
            }
        }
    },
    series: prd_weekly_cat,
    exporting: {
        enabled:false
    },
    credits:{
        enabled:false
    }
});
//######################## 월별 생산(#chart_monthly) [Pie with gradient fill]#############################
/*
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
Highcharts.chart('chart_monthly', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: ''
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
        name: 'Share',
        data: [
            <?php
            //for($i=0;$i<count($prd_monthly_cat);$i++){
            //    if($i > 0) echo ',';
            //    echo "
            //        { 'name':'".$prd_monthly_cat[$i]['name']."',
            //          'y':".(float)$prd_monthly_cat[$i]['y']."
            //        }
            //    ";
            //}
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
*/
//######################## 년도별 생산(#chart_annual) [Pie with gradient fill]#############################
/*
// Build the chart
Highcharts.chart('chart_annual', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: ''
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
        name: 'Share',
        data: [
            <?php
            //for($i=0;$i<count($prd_annual_cat);$i++){
            //    if($i == 0) echo ',';
            //    echo "
            //        { 'name':'".$prd_annual_cat[$i]['name']."',
            //          'y':".(float)$prd_annual_cat[$i]['y']."
            //        }
            //    ";
            //}
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
*/
</script>