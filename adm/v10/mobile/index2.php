<?php
add_stylesheet('<link type="text/css" href="'.G5_JS_URL.'/swiper/swiper.min.css" rel="stylesheet" />', 0);
?>

<style>
</style>
<script src="<?php echo G5_JS_URL; ?>/swipe.js"></script>
<script src="<?php echo G5_JS_URL; ?>/swiper/swiper.min.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/highcharts/code/highcharts.src.js"></script>

<!-- IMP List -->
<div class="imp_list">
<span class="btn_imp_list_all" onClick="javascript:alert('IMP 전체 목록을 보면서 설정하는 페이지입니다.');"><i class="fa fa-arrows-alt"></i></span>
<div class="swiper-container">
    <ul class="swiper-wrapper">
        <?php
        for($i=0;$i<4;$i++) {
            $img_container_style = ($i==0) ? ' on':' off';
        ?>
        <li class="swiper-slide imp_container<?=$img_container_style?>">
            <table class="imp_table">
            <tr class="tr_title">
                <td colspan="2" class="td_center">300톤 프레스 <?=($i+1)?>호기</td>
            </tr>
            <tr>
                <td>생산기종</td>
                <td>1</td>
            </tr>
            <tr>
                <td>일생산</td>
                <td>123</td>
            </tr>
            <tr>
                <td>목표율</td>
                <td>100%</td>
            </tr>
            <tr>
                <td>장비이상</td>
                <td>1</td>
            </tr>
            <tr>
                <td>예지알람</td>
                <td>1</td>
            </tr>
            </table>
        </li>
        <?php
        }
        ?>
        <li class="swiper-slide imp_container plus">
            +
        </li>
    </ul>
</div>
<!-- Add Pagination -->
<div class="swiper-pagination"></div>
</div>

<script>
    var swiper = new Swiper('.swiper-container', {
        slidesPerView: 2,
        spaceBetween: 5,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
    });
    $(document).on('click','.imp_container.plus',function(e){
        e.preventDefault();
        alert('IMP를 추가하시려면 관리자 승인이 필요합니다.');
    });
</script>


<!-- Data Infomation -->
<div class="data_wrapper">
    <div id="tabs">
        <ul>
          <li><a href="#tabs-1">개별보기</a></li>
          <li><a href="#tabs-2">겹쳐보기</a></li>
          <li><a href="#tabs-3">설비정보</a></li>
          <li><a href="#tabs-4">설비상태</a></li>
        </ul>
        <!-- 개별보기 -->
        <div id="tabs-1">

            <div class="column">
                
                <div class="portlet" imp_idx="3" dta_type="3">
                    <div class="portlet-header">토크</div>
                    <div class="portlet-content">
                    
                        <div id="chart3" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart3', {
                            chart: {
                                type: 'spline',
                                animation: Highcharts.svg, // don't animate in old IE
                                marginRight: 10,
                                events: {
                                    load: function () {

                                        // set up the updating of the chart each 5 second
                                        var series = this.series[0];
                                        setInterval(function () {
                                            var x = (new Date()).getTime(), // current time
                                                y = Math.random();
                                            series.addPoint([x, y], true, true);
                                        }, 5000);
                                    }
                                }
                            },

                            time: {
                                useUTC: false
                            },

                            title: {
                                text: 'Live random data'
                            },

                            accessibility: {
                                announceNewData: {
                                    enabled: true,
                                    minAnnounceInterval: 15000,
                                    announcementFormatter: function (allSeries, newSeries, newPoint) {
                                        if (newPoint) {
                                            return 'New point added. Value: ' + newPoint.y;
                                        }
                                        return false;
                                    }
                                }
                            },

                            xAxis: {
                                type: 'datetime',
                                tickPixelInterval: 150
                            },

                            yAxis: {
                                title: {
                                    text: 'Value'
                                },
                                plotLines: [{
                                    value: 0,
                                    width: 1,
                                    color: '#808080'
                                }]
                            },

                            tooltip: {
                                headerFormat: '<b>{series.name}</b><br/>',
                                pointFormat: '{point.x:%Y-%m-%d %H:%M:%S}<br/>{point.y:.2f}'
                            },

                            legend: {
                                enabled: false
                            },

                            exporting: {
                                enabled: false
                            },

                            series: [{
                                name: 'Random data',
                                data: (function () {
                                    // generate an array of random data
                                    var data = [],
                                        time = (new Date()).getTime(),
                                        i;

                                    for (i = -19; i <= 0; i += 1) {
                                        data.push({
                                            x: time + i * 5000,
                                            y: Math.random()
                                        });
                                    }
                                    return data;
                                }())
                            }]
                        });
                        </script>
                    
                    </div>
                </div>
                
                <div class="portlet" imp_idx="3" dta_type="1">
                    <div class="portlet-header">온도</div>
                    <div class="portlet-content">

                        <div id="chart1" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart1', {
                            chart: {
                                zoomType: 'x'
                            },
                            title: {
                                text: '',
                                align: 'left'
                            },
                            subtitle: {
                                text: 'Source: ingglobal.net',
                                align: 'left'
                            },
                            xAxis: [{
                                categories: ['01', '02', '03', 'Apr', 'May', 'Jun',
                                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                crosshair: true
                            }],
                            yAxis: [{ // Primary yAxis
                                labels: {
                                    format: '{value}°C',
                                    style: {
                                        color: Highcharts.getOptions().colors[2]
                                    }
                                },
                                title: {
                                    text: 'Temperature',
                                    style: {
                                        color: Highcharts.getOptions().colors[2]
                                    }
                                },
                                opposite: true

                            }, { // Secondary yAxis
                                gridLineWidth: 0,
                                title: {
                                    text: 'Rainfall',
                                    style: {
                                        color: Highcharts.getOptions().colors[0]
                                    }
                                },
                                labels: {
                                    format: '{value} mm',
                                    style: {
                                        color: Highcharts.getOptions().colors[0]
                                    }
                                }

                            }, { // Tertiary yAxis
                                gridLineWidth: 0,
                                title: {
                                    text: 'Sea-Level Pressure',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                labels: {
                                    format: '{value} mb',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                opposite: true
                            }],
                            tooltip: {
                                shared: true
                            },
                            legend: {
                                layout: 'vertical',
                                align: 'left',
                                x: 80,
                                verticalAlign: 'top',
                                y: 55,
                                floating: true,
                                backgroundColor:
                                    Highcharts.defaultOptions.legend.backgroundColor || // theme
                                    'rgba(255,255,255,0.25)'
                            },
                            series: [{
                                name: 'Rainfall',
                                type: 'column',
                                yAxis: 1,
                                data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
                                tooltip: {
                                    valueSuffix: ' mm'
                                }

                            }, {
                                name: 'Sea-Level Pressure',
                                type: 'spline',
                                yAxis: 2,
                                data: [1016, 1016, 1015.9, 1015.5, 1012.3, 1009.5, 1009.6, 1010.2, 1013.1, 1016.9, 1018.2, 1016.7],
                                marker: {
                                    enabled: false
                                },
                                dashStyle: 'shortdot',
                                tooltip: {
                                    valueSuffix: ' mb'
                                }

                            }, {
                                name: 'Temperature',
                                type: 'spline',
                                data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                                tooltip: {
                                    valueSuffix: ' °C'
                                }
                            }],
                            responsive: {
                                rules: [{
                                    condition: {
                                        maxWidth: 500
                                    },
                                    chartOptions: {
                                        legend: {
                                            floating: false,
                                            layout: 'horizontal',
                                            align: 'center',
                                            verticalAlign: 'bottom',
                                            x: 0,
                                            y: 0
                                        },
                                        yAxis: [{
                                            labels: {
                                                align: 'right',
                                                x: 0,
                                                y: -6
                                            },
                                            showLastLabel: false
                                        }, {
                                            labels: {
                                                align: 'left',
                                                x: 0,
                                                y: -6
                                            },
                                            showLastLabel: false
                                        }, {
                                            visible: false
                                        }]
                                    }
                                }]
                            }
                        });
                        </script>
                        
                    </div>
                </div>
                <div class="portlet" imp_idx="3" dta_type="3">
                    <div class="portlet-header">습도</div>
                    <div class="portlet-content">
                    
                        <div id="chart4" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart4', {
                            chart: {
                                type: 'line'
                            },
                            title: {
                                text: 'Monthly Average Temperature'
                            },
                            subtitle: {
                                text: 'Source: WorldClimate.com'
                            },
                            xAxis: {
                                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                            },
                            yAxis: {
                                title: {
                                    text: 'Temperature (°C)'
                                }
                            },
                            plotOptions: {
                                line: {
                                    dataLabels: {
                                        enabled: true
                                    },
                                    enableMouseTracking: false
                                }
                            },
                            series: [{
                                name: 'Tokyo',
                                data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
                            }, {
                                name: 'London',
                                data: [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
                            }]
                        });
                        </script>
                    
                    </div>
                </div>
                <div class="portlet" imp_idx="3" dta_type="3">
                    <div class="portlet-header">속도</div>
                    <div class="portlet-content">
                    
                        <div id="chart5" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart5', {
                            chart: {
                                type: 'column'
                            },
                            title: {
                                text: 'Monthly Average Rainfall'
                            },
                            subtitle: {
                                text: 'Source: WorldClimate.com'
                            },
                            xAxis: {
                                categories: [
                                    'Jan',
                                    'Feb',
                                    'Mar',
                                    'Apr',
                                    'May',
                                    'Jun',
                                    'Jul',
                                    'Aug',
                                    'Sep',
                                    'Oct',
                                    'Nov',
                                    'Dec'
                                ],
                                crosshair: true
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Rainfall (mm)'
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                    '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
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
                            series: [{
                                name: 'Tokyo',
                                data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4]

                            }, {
                                name: 'New York',
                                data: [83.6, 78.8, 98.5, 93.4, 106.0, 84.5, 105.0, 104.3, 91.2, 83.5, 106.6, 92.3]

                            }, {
                                name: 'London',
                                data: [48.9, 38.8, 39.3, 41.4, 47.0, 48.3, 59.0, 59.6, 52.4, 65.2, 59.3, 51.2]

                            }, {
                                name: 'Berlin',
                                data: [42.4, 33.2, 34.5, 39.7, 52.6, 75.5, 57.4, 60.4, 47.6, 39.1, 46.8, 51.1]

                            }]
                        });
                        </script>
                    
                    </div>
                </div>

                
                <div class="portlet" imp_idx="3" dta_type="2">
                    <div class="portlet-header">진동</div>
                    <div class="portlet-content">
                    
                        <div id="chart2" style="width:100%; height:300px;"></div>
                        <script>
                        var colors = Highcharts.getOptions().colors;

                        Highcharts.chart('chart2', {
                            chart: {
                                type: 'spline'
                            },

                            legend: {
                                symbolWidth: 40
                            },

                            title: {
                                text: 'Most common desktop screen readers'
                            },

                            subtitle: {
                                text: 'Source: WebAIM. Click on points to visit official screen reader website'
                            },

                            yAxis: {
                                title: {
                                    text: 'Percentage usage'
                                }
                            },

                            xAxis: {
                                title: {
                                    text: 'Time'
                                },
                                accessibility: {
                                    description: 'Time from December 2010 to September 2019'
                                },
                                categories: ['December 2010', 'May 2012', 'January 2014', 'July 2015', 'October 2017', 'September 2019']
                            },

                            tooltip: {
                                valueSuffix: '%'
                            },

                            plotOptions: {
                                series: {
                                    point: {
                                        events: {
                                            click: function () {
                                                window.location.href = this.series.options.website;
                                            }
                                        }
                                    },
                                    cursor: 'pointer'
                                }
                            },

                            series: [
                                {
                                    name: 'NVDA',
                                    data: [34.8, 43.0, 51.2, 41.4, 64.9, 72.4],
                                    website: 'https://www.nvaccess.org',
                                    color: colors[2],
                                    accessibility: {
                                        description: 'This is the most used screen reader in 2019'
                                    }
                                }, {
                                    name: 'JAWS',
                                    data: [69.6, 63.7, 63.9, 43.7, 66.0, 61.7],
                                    website: 'https://www.freedomscientific.com/Products/Blindness/JAWS',
                                    dashStyle: 'ShortDashDot',
                                    color: colors[0]
                                }, {
                                    name: 'VoiceOver',
                                    data: [20.2, 30.7, 36.8, 30.9, 39.6, 47.1],
                                    website: 'http://www.apple.com/accessibility/osx/voiceover',
                                    dashStyle: 'ShortDot',
                                    color: colors[1]
                                }, {
                                    name: 'Narrator',
                                    data: [null, null, null, null, 21.4, 30.3],
                                    website: 'https://support.microsoft.com/en-us/help/22798/windows-10-complete-guide-to-narrator',
                                    dashStyle: 'Dash',
                                    color: colors[9]
                                }, {
                                    name: 'ZoomText/Fusion',
                                    data: [6.1, 6.8, 5.3, 27.5, 6.0, 5.5],
                                    website: 'http://www.zoomtext.com/products/zoomtext-magnifierreader',
                                    dashStyle: 'ShortDot',
                                    color: colors[5]
                                }, {
                                    name: 'Other',
                                    data: [42.6, 51.5, 54.2, 45.8, 20.2, 15.4],
                                    website: 'http://www.disabled-world.com/assistivedevices/computer/screen-readers.php',
                                    dashStyle: 'ShortDash',
                                    color: colors[3]
                                }
                            ],

                            responsive: {
                                rules: [{
                                    condition: {
                                        maxWidth: 550
                                    },
                                    chartOptions: {
                                        legend: {
                                            itemWidth: 150
                                        },
                                        xAxis: {
                                            categories: ['Dec. 2010', 'May 2012', 'Jan. 2014', 'July 2015', 'Oct. 2017', 'Sep. 2019']
                                        },
                                        yAxis: {
                                            title: {
                                                enabled: false
                                            },
                                            labels: {
                                                format: '{value}%'
                                            }
                                        }
                                    }
                                }]
                            }
                        });
                        </script>
                    
                    </div>
                </div>
                <div class="portlet" imp_idx="3" dta_type="3">
                    <div class="portlet-header">압력</div>
                    <div class="portlet-content">
                    
                        <div id="chart7" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart7', {
                            chart: {
                                zoomType: 'xy'
                            },
                            title: {
                                text: 'Average Monthly Temperature and Rainfall in Tokyo'
                            },
                            subtitle: {
                                text: 'Source: WorldClimate.com'
                            },
                            xAxis: [{
                                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                crosshair: true
                            }],
                            yAxis: [{ // Primary yAxis
                                labels: {
                                    format: '{value}°C',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                title: {
                                    text: 'Temperature',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                }
                            }, { // Secondary yAxis
                                title: {
                                    text: 'Rainfall',
                                    style: {
                                        color: Highcharts.getOptions().colors[0]
                                    }
                                },
                                labels: {
                                    format: '{value} mm',
                                    style: {
                                        color: Highcharts.getOptions().colors[0]
                                    }
                                },
                                opposite: true
                            }],
                            tooltip: {
                                shared: true
                            },
                            legend: {
                                layout: 'vertical',
                                align: 'left',
                                x: 120,
                                verticalAlign: 'top',
                                y: 100,
                                floating: true,
                                backgroundColor:
                                    Highcharts.defaultOptions.legend.backgroundColor || // theme
                                    'rgba(255,255,255,0.25)'
                            },
                            series: [{
                                name: 'Rainfall',
                                type: 'column',
                                yAxis: 1,
                                data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
                                tooltip: {
                                    valueSuffix: ' mm'
                                }

                            }, {
                                name: 'Temperature',
                                type: 'spline',
                                data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                                tooltip: {
                                    valueSuffix: '°C'
                                }
                            }]
                        });                        
                        </script>
                    
                    </div>
                </div>

                <div class="portlet" imp_idx="3" dta_type="3">
                    <div class="portlet-header">생산</div>
                    <div class="portlet-content">

                        <div id="chart8" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart8', {
                            chart: {
                                type: 'column',
                                options3d: {
                                    enabled: true,
                                    alpha: 15,
                                    beta: 15,
                                    viewDistance: 25,
                                    depth: 40
                                }
                            },

                            title: {
                                text: 'Total fruit consumption, grouped by gender'
                            },

                            xAxis: {
                                categories: ['Apples', 'Oranges', 'Pears', 'Grapes', 'Bananas'],
                                labels: {
                                    skew3d: true,
                                    style: {
                                        fontSize: '16px'
                                    }
                                }
                            },

                            yAxis: {
                                allowDecimals: false,
                                min: 0,
                                title: {
                                    text: 'Number of fruits',
                                    skew3d: true
                                }
                            },

                            tooltip: {
                                headerFormat: '<b>{point.key}</b><br>',
                                pointFormat: '<span style="color:{series.color}">\u25CF</span> {series.name}: {point.y} / {point.stackTotal}'
                            },

                            plotOptions: {
                                column: {
                                    stacking: 'normal',
                                    depth: 40
                                }
                            },

                            series: [{
                                name: 'John',
                                data: [5, 3, 4, 7, 2],
                                stack: 'male'
                            }, {
                                name: 'Joe',
                                data: [3, 4, 4, 2, 5],
                                stack: 'male'
                            }, {
                                name: 'Jane',
                                data: [2, 5, 6, 2, 1],
                                stack: 'female'
                            }, {
                                name: 'Janet',
                                data: [3, 0, 4, 4, 3],
                                stack: 'female'
                            }]
                        });                        
                        </script>
                    
                    </div>
                </div>
                <div class="portlet" imp_idx="3" dta_type="3">
                    <div class="portlet-header">불량률</div>
                    <div class="portlet-content">
                    
                        <div id="chart9" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart9', {
                            chart: {
                                type: 'pie',
                                options3d: {
                                    enabled: true,
                                    alpha: 45,
                                    beta: 0
                                }
                            },
                            title: {
                                text: 'Browser market shares at a specific website, 2014'
                            },
                            accessibility: {
                                point: {
                                    valueSuffix: '%'
                                }
                            },
                            tooltip: {
                                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                            },
                            plotOptions: {
                                pie: {
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    depth: 35,
                                    dataLabels: {
                                        enabled: true,
                                        format: '{point.name}'
                                    }
                                }
                            },
                            series: [{
                                type: 'pie',
                                name: 'Browser share',
                                data: [
                                    ['Firefox', 45.0],
                                    ['IE', 26.8],
                                    {
                                        name: 'Chrome',
                                        y: 12.8,
                                        sliced: true,
                                        selected: true
                                    },
                                    ['Safari', 8.5],
                                    ['Opera', 6.2],
                                    ['Others', 0.7]
                                ]
                            }]
                        });
                        
                        
                        </script>
                    
                    
                    </div>
                </div>
            </div>            

        </div>
        <!-- 겹쳐보기 -->
        <div id="tabs-2">
            
            
                        <div id="chart10" style="width:100%; height:300px;"></div>
                        <script>
                        Highcharts.chart('chart10', {
                            chart: {
                                zoomType: 'x'
                            },
                            title: {
                                text: '',
                                align: 'left'
                            },
                            subtitle: {
                                text: 'Source: ingglobal.net',
                                align: 'left'
                            },
                            xAxis: [{
                                categories: ['01', '02', '03', 'Apr', 'May', 'Jun',
                                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                crosshair: true
                            }],
                            yAxis: [{ // Primary yAxis
                                labels: {
                                    format: '{value}°C',
                                    style: {
                                        color: Highcharts.getOptions().colors[2]
                                    }
                                },
                                title: {
                                    text: 'Temperature',
                                    style: {
                                        color: Highcharts.getOptions().colors[2]
                                    }
                                },
                                opposite: true

                            }, { // Secondary yAxis
                                gridLineWidth: 0,
                                title: {
                                    text: 'Rainfall',
                                    style: {
                                        color: Highcharts.getOptions().colors[0]
                                    }
                                },
                                labels: {
                                    format: '{value} mm',
                                    style: {
                                        color: Highcharts.getOptions().colors[0]
                                    }
                                }

                            }, { // Tertiary yAxis
                                gridLineWidth: 0,
                                title: {
                                    text: 'Sea-Level Pressure',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                labels: {
                                    format: '{value} mb',
                                    style: {
                                        color: Highcharts.getOptions().colors[1]
                                    }
                                },
                                opposite: true
                            }],
                            tooltip: {
                                shared: true
                            },
                            legend: {
                                layout: 'vertical',
                                align: 'left',
                                x: 80,
                                verticalAlign: 'top',
                                y: 55,
                                floating: true,
                                backgroundColor:
                                    Highcharts.defaultOptions.legend.backgroundColor || // theme
                                    'rgba(255,255,255,0.25)'
                            },
                            series: [{
                                name: 'Rainfall',
                                type: 'column',
                                yAxis: 1,
                                data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
                                tooltip: {
                                    valueSuffix: ' mm'
                                }

                            }, {
                                name: 'Sea-Level Pressure',
                                type: 'spline',
                                yAxis: 2,
                                data: [1016, 1016, 1015.9, 1015.5, 1012.3, 1009.5, 1009.6, 1010.2, 1013.1, 1016.9, 1018.2, 1016.7],
                                marker: {
                                    enabled: false
                                },
                                dashStyle: 'shortdot',
                                tooltip: {
                                    valueSuffix: ' mb'
                                }

                            }, {
                                name: 'Temperature',
                                type: 'spline',
                                data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                                tooltip: {
                                    valueSuffix: ' °C'
                                }
                            }],
                            responsive: {
                                rules: [{
                                    condition: {
                                        maxWidth: 500
                                    },
                                    chartOptions: {
                                        legend: {
                                            floating: false,
                                            layout: 'horizontal',
                                            align: 'center',
                                            verticalAlign: 'bottom',
                                            x: 0,
                                            y: 0
                                        },
                                        yAxis: [{
                                            labels: {
                                                align: 'right',
                                                x: 0,
                                                y: -6
                                            },
                                            showLastLabel: false
                                        }, {
                                            labels: {
                                                align: 'left',
                                                x: 0,
                                                y: -6
                                            },
                                            showLastLabel: false
                                        }, {
                                            visible: false
                                        }]
                                    }
                                }]
                            }
                        });
                        </script>
            
            
            
        </div>
        <!-- 설비정보 -->
        <div id="tabs-3">
            <img src="<?=G5_THEME_IMG_URL?>/machine/9.jpeg" style="width:100%;">
        </div>
        <!-- 설비상태 -->
        <div id="tabs-4">

            <table class="imp_table">
            <tr class="tr_title">
                <td colspan="2" class="td_center">주요현황</td>
            </tr>
            <tr>
                <td>설비상태</td>
                <td>00</td>
            </tr>
            <tr>
                <td>가동시간</td>
                <td>00:00:00</td>
            </tr>
            <tr>
                <td>생산카운터</td>
                <td>100%</td>
            </tr>
            <tr>
                <td>장비이상</td>
                <td>00</td>
            </tr>
            <tr>
                <td>부품재고</td>
                <td>1000</td>
            </tr>
            </table>
        
        </div>
    </div>
</div>


<script>
    // 상단 탭
    var tabs = $( "#tabs" ).tabs();
    tabs.find( ".ui-tabs-nav" ).sortable({
        axis: "x",
        stop: function() {
            tabs.tabs( "refresh" );
        },
        show: function(e, ui) {
            console.log('showed');
        }
    });
    $( "#tabs" ).on( "tabsactivate", function( event, ui ) {
        event.preventDefault();
        console.log('tabsactivate.');
        // 탭 위치로 애매하게 이동하는 이슈가 있음 (최상단으로 이동, setTimeout함수를 꼭 감싸야 되네!)
        setTimeout(function(){
            window.scrollTo(0,0);
        },1);
    } );
    
    
    $( ".portlet" )
      .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
      .find( ".portlet-header" )
        .addClass( "ui-widget-header ui-corner-all" )
        .prepend( "<span class='portlet-newwin'><i class='fa fa-window-restore'></i></span>");
 
    // 개별보기 창을 열면 각각 새창이 열림
    $( ".portlet-newwin" ).on( "click", function() {
        var icon = $( this );
        var imp_idx = icon.closest(".portlet").attr('imp_idx');
        var dta_type = icon.closest(".portlet").attr('dta_type');
        var href = './graph_each.php?imp_idx='+imp_idx+'&dta_type='+dta_type;
        var winName = "winGraphEach_"+imp_idx+"_"+dta_type;
        var win_left = 100 + dta_type*20;
        var win_top = 100 + dta_type*20;
        var winLocation = "left="+win_left+",top="+win_top;
        eval( winName+" = window.open('"+href+"', '"+dta_type+"', '"+winLocation+", width=460, height=400, scrollbars=0'); ");
        eval( winName+".focus(); ");
//        winGraphEach = window.open(href, "winGraphEach", "left=100, top=100, width=460, height=400, scrollbars=0");
//        winGraphEach.focus();
    });
    
</script>

<script>
    $("#container").addClass("idx-container");
</script>

<?php
include_once ('./_tail.php');
?>