<style>
#fsearch{position:relative;}
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Gantt/code/highcharts-gantt.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<div style="padding-bottom:5px;">
<input type="text" name="st_date" value="<?=$st_date?>" placeholder="부터" id="st_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="시작일">
~
<input type="text" name="en_date" value="<?=$en_date?>" placeholder="까지" id="en_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="종료일">
</div>
<select name="sfl" id="sfl">
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
	<option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자</option>
	<option value="prs_role"<?php echo get_selected($_GET['sfl'], "prs_role"); ?>>역할</option>
	<option value="prj.prj_idx"<?php echo get_selected($_GET['sfl'], "prj.prj_idx"); ?>>프로젝트번호</option>
    <!--
	<option value="prs_task"<?php //echo get_selected($_GET['sfl'], "prs_task"); ?>>업무내용</option>
	<option value="prs_content"<?php //echo get_selected($_GET['sfl'], "prs_content"); ?>>상세설명</option>
	<option value="prj_name"<?php //echo get_selected($_GET['sfl'], "prj_name"); ?>>프로젝트명</option>
    -->
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
<div class="btn_fixed_top">
    <a href="./project_schedule_form.php?gant=1" class="btn btn_01"><i class="fa fa-plus" aria-hidden="true"></i><span class="sound_only">일정등록</span></a>
</div>
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
</div>

<div style="overflow:auto;">
    <div style="width:1000px;">
        <div id="chart1"></div>
    </div>
</div>

<script>
// var data_arr = <?=json_encode($list)?>;
// console.log(JSON.stringify(data_arr));
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

});


Highcharts.ganttChart('chart1', {
chart: {
    height: 165+35*<?=($gantt_y+1)?>
},
xAxis: [
    { // day display, first x-axis from bottom
        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=$st_date1['day']?>, 0),
        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=$en_date1['day']?>, 23, 59, 59),
        tickInterval: 1000 * 60 * 60 * 24 // 1 day
        ,labels: {
            format: '{value:%d}' // day of the week
        },
        grid: {
            cellHeight: 30
        }
    }
    ,{ // month display, 2nd x-axis from bottom
        min: Date.UTC(<?=$st_date1['year']?>, <?=($st_date1['month']-1)?>, <?=$st_date1['day']?>, 0),
        max: Date.UTC(<?=$en_date1['year']?>, <?=($en_date1['month']-1)?>, <?=$en_date1['day']?>, 23, 59, 59),
        tickInterval: 1000 * 60 * 60 * 24 * 30 // 1 month
        ,labels: {
            format: '{value:%Y-%m}'
        },
        grid: {
            cellHeight: 30
        },
        id: 'bottom-datetime-axis',
        currentDateIndicator: {
            width: 2,
            dashStyle: 'solid',
            color: 'blue',
            label: {
                format: '%Y-%m-%d'
            }
        }
    },
],
yAxis: {
    uniqueNames: true,
    staticScale: 20
},
navigator: {
    enabled: true,
    liveRedraw: true,
    series: {
        lineColor: '#f2f2f2',
        type: 'spline', // 'gantt' - bar is showing outside of the navigator, not good, 
        // ^ but 'spline', it showw warning #15, but neglect it, no problem for running.
        pointPlacement: 0.5,
        pointPadding: 0.25
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
    yAxis: {
        min: 0,
        max: 3,
        reversed: true,
        categories: []
    }
},
scrollbar: {
    enabled: true
},
rangeSelector: {
    enabled: false,
    selected: 0
},
tooltip: {
    useHTML: true,
    formatter: function(tooltip) {
        // console.log(this);
        // console.log(this.point.options);
        // console.log(this.point.options.content);

        // tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">4.12~5.12</span>';
        tooltip1 = '<b>'+this.point.options.assignee+'</b>: <span style="font-size:0.7em;">'+this.point.options.start_dt+'~'+this.point.options.end_dt+'</span>';
        if(this.point.options.content) {
            tooltip1 += '<br/><span style="font-size:0.9em;">'+this.point.options.content+'</span>';
        }
        return tooltip1;
        // // If not null, use the default formatter
        // return tooltip.defaultFormatter.call(this, tooltip);
    }
},
yAxis: {
    type: 'category',
    grid: {
        borderColor: '#dddddd',
        columns: [
        {
            title: {
            text: '프로젝트',
            rotation: 0,
                y: -5,
                x: -15
            },
            labels: {
                format: '{point.name}',
                align:'left',
                style: {
                    fontSize:'1em'
                }
            }
        },
        {
            title: {
            text: '담당자', // 역할
            rotation: 0,
                y: -5,
                x: 0
            },
            labels: {
                format: '{point.role}'
            }
        },
        {
            title: {
            text: ' ',  // 담당자이름
            rotation: 0,
                y: -5,
                x: -15
            },
            labels: {
                format: '{point.assignee}'
            }
        }
        ]
    }
},
plotOptions: {
    series: {
        // opacity:0.8,
        cursor: 'pointer',
        point: {
            events: {
                click: function () {
                    // console.log(this.options);
                    location.href = './project_schedule_form.php?gant=1&w=u&prs_idx=' +
                        this.options.prs_idx + '&sst=<?=$sst?>&sod=<?=$sod?>&sfl=<?=$sfl?>&stx=<?=$stx?>&page=<?=$page?>';
                }
            }
        }
    }
},
series: [
{
    name: 'Projects',
    opacity:0.85,
    data: [
        <?php
        for($i=0;$i<sizeof($list);$i++) {
            echo "
            {   name: '".$list[$i]['name']."',
                role: '".$list[$i]['role']."',
                assignee: '".$list[$i]['assignee']."',
                content: '".$list[$i]['content']."',
                start: Date.UTC(".$list[$i]['start_year'].", ".($list[$i]['start_month']-1).", ".$list[$i]['start_day']."),
                start_dt: '".$list[$i]['prs_start_month'].".".$list[$i]['prs_start_day']."',
                end: Date.UTC(".$list[$i]['end_year'].", ".($list[$i]['end_month']-1).", ".$list[$i]['end_day'].", 23, 59, 59),
                end_dt: '".$list[$i]['prs_end_month'].".".$list[$i]['prs_end_day']."',
                completed: ".$list[$i]['completed'].",
                pointWidth: ".$list[$i]['pointWidth'].",
                color: '".$list[$i]['color']."',
                prs_idx: ".$list[$i]['prs_idx'].",
                y: ".$list[$i]['y']."
            },
            ";
        }
        ?>
    ]
    // data: [
    // {
    //     name: '아진산업',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#7882a6',
    //     prs_idx: 4,
    //     y: 0
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2017, 12, 20),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0,
    //     pointWidth: 8,
    //     color: '#cccccc',
    //     y: 1
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0,
    //     pointWidth: 2,
    //     color: '#ff6361',
    //     y: 1
    // },
    // {
    //     name: '세림산업',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#525b7e',
    //     prs_idx: 4,
    //     y: 2
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2018, 1, 2),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     prs_idx: 4,
    //     y: 3
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 4
    // },
    // {
    //     name: '아강테크',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#525b7e',
    //     y: 5
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2018, 1, 2),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 6
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 6
    // },
    // {
    //     name: '세림산업',
    //     role: 'PM',
    //     assignee: '김청탁',
    //     content: '로봇 티칭 완료',
    //     start: Date.UTC(2017, 11, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.55,
    //     color: '#525b7e',
    //     y: 7
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     content: '현대 로봇 셋업 함',
    //     start: Date.UTC(2018, 1, 2),
    //     end: Date.UTC(2018, 1, 5),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 8
    // },
    // {
    //     name: '',
    //     role: 'SUB1',
    //     assignee: '최치환',
    //     start: Date.UTC(2017, 12, 1),
    //     end: Date.UTC(2018, 1, 2),
    //     completed: 0.5,
    //     color: '#525b7e',
    //     y: 9
    // },
    // ]
}
]
});
removeLogo();
// highchart.com이라는 로고 제거
function removeLogo() {
    //Highcharts.com 로고 제거
    setTimeout(function(e){
        $('.highcharts-credits').remove();
    },10);
}
</script>

<?php
include_once ('./_tail.php');
?>