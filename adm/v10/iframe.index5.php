<?php
// mms_idx=1, list_column=2(2줄)
include_once('./_common.php');

if(!$mms_idx)
    goto_url('./iframe.empty.php');

// column definition. if not posted, find it from stored DB.
if(!$column) {
    // 대시보드 정보 추출
    $sql = "SELECT mbd_value
            FROM {$g5['member_dash_table']}
            WHERE mms_idx = '".$mms_idx."' AND mb_id = '".$member['mb_id']."' AND mbd_status IN ('ok') AND mbd_type = 'column'
            ORDER BY mbd_idx DESC
            LIMIT 1
    ";
    $mbd1 = sql_fetch($sql,1);
    // echo $sql;
    $column = $mbd1['mbd_value'];
}
$column = $column ?: 2; // if not exists anyway.
$strlen = 47 - 7*$column; // Title string length
// echo $column;


$g5['title'] = '대시보드 그래프';
include_once('./_head.sub.php');

// mms_idx * column 조합을 저장 (다음 클릭 시 저장된 값을 불러오게 하기 위함)
dashboard_column_update(array("mms_idx"=>$mms_idx,"column"=>$column));

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/iframe.index.css">', 0);
// add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/iframe.index.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_URL.'/js/slick-1.8.1/slick/slick-theme.css">', 0);
?>
<style>
    .graph_empty {width:100%;height:100px;line-height:200px;text-align:center;}
</style>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/highstock.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/data.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/Highstock/code/modules/exporting.js"></script>
<script src="<?php echo G5_URL?>/lib/highcharts/moment.js"></script><!-- 다양한 시간 표현을 위한 플러그인 -->

<script>
// 부모창 선택 영역 색상 변경
$('.graph_icons a', parent.document).removeClass('on');
$('.icon_x<?=$column?>', parent.document).addClass('on');

// 로딩 spinner 이미지 표시/비표시
function dta_loading(flag,mbd_idx) {
    var img_loading = $('<i class="fa fa-spin fa-spinner" id="spinner'+mbd_idx+'" style="position:absolute;top:80px;left:46%;font-size:4em;"></i>');
    if(flag=='show') {
        // console.log('show');
        $('#chart'+mbd_idx).append(img_loading);
    }
    else if(flag=='hide') {
        // console.log('hide');
        $('#spinner'+mbd_idx).remove();
    }
}

// highchart.com이라는 로고 제거
function removeLogo() {
    //Highcharts.com 로고 제거
    setTimeout(function(e){
        $('.highcharts-credits').remove();
    },10);
}

function createChart(mbd_idx,seriesOptions,dta_item) {
    var chart = new Highcharts.stockChart({
        chart: {
            renderTo: 'chart_'+mbd_idx
        },
        scrollbar: {
            enabled: false
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
        },

        yAxis: {
            // max: 1800,   // 크게 확대해서 보려면 20
            // min: -100,  // 크게 확대해서 보려면 -10, 없애버리면 자동 스케일
            showLastLabel: true,    // 위 아래 마지막 label 보임 (이게 없으면 끝label이 안 보임)
            opposite: false,
            tickInterval: null,
            // minorTickInterval: 5,
            // minorTickLength: 0,
        },

        plotOptions: {
            series: {
                showInNavigator: true,
                dataGrouping: {
                    enabled: false, // dataGrouping 안 함 (range가 변경되면 평균으로 바뀌어서 헷갈림)
                },
                marker: {
                    enabled: true   // point dot display
                }
            }
        },

        navigator: {
            enabled: false
        },

        navigation: {
            buttonOptions: {
                enabled: false, // contextButton (인쇄, 다운로드..) 설정 (기본옵션 사용자들에게는 안 보이게!!)
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
                if(dta_item=='daily'||dta_item=='weekly') {
                    var tooltip1 =  moment(this.x).format("MM/DD");
                }
                else if(dta_item=='monthly') {
                    var tooltip1 =  moment(this.x).format("YYYY-MM");
                }
                else if(dta_item=='yearly') {
                    var tooltip1 =  moment(this.x).format("YYYY");
                }
                else {
                    var tooltip1 =  moment(this.x).format("MM/DD HH:mm:ss");
                }
                // console.log(this);
                var tooltip2 = [];
                $.each(this.points, function () {
                    // console.log(this.point);
                    var this_name = this.series.name;
                    // if 기종 exists
                    if(this.point.dta_mmi_no) {
                        console.log(this.point.dta_mmi_no);
                        this_name += ' (기종:'+this.point.dta_mmi_no+')';
                    }
                    tooltip1 += '<br/><span style="color:' + this.color + '">\u25CF '+this_name+'</span>: <b>' + this.point.yraw + '</b>';
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
    });

    dta_loading('hide',mbd_idx);
    removeLogo();
    
}

// As we're loading the data asynchronously, we don't know what order it will arrive.
// So we keep a counter and create the chart when all the data is loaded.
function drawChart(data) {
    // find which graph
    var para = urlParaToJSON2(this.url); // get values from Json Url
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
    var dta_item = para.dta_item;
    var graph_name = para.graph_name;
    var graph_id = para.graph_id;
    var mbd_idx = para.mbd_idx;

    // 그래프 배열을 할당 (여러개라서 해당 그래프에 개발 할당을 해야 함) 
    var graphs = eval('graphs'+mbd_idx);
    var seriesOptions = eval('seriesOptions'+mbd_idx);
    // console.log('기준: '+graph_id);
    // chr_idx = graphs.length-1;
    // console.log( mbd_idx );
    // console.log( graphs );
    
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
        graph_name: graphs[chr_idx].graph_name,
        graph_id: graph_id1
    };

    // data variable definition <<<<==============================================
    seriesOptions[chr_idx] = {
        name: decodeURIComponent(graph_name),
        id:chr_id,
        type: graphs[chr_idx].graph_type,
        dashStyle: graphs[chr_idx].graph_line,
        data: data
    };

    // Create chart when all data loaded.
    eval( 'seriesCounter'+mbd_idx+' += 1;' );
    // console.log( mbd_idx +'> '+ eval('seriesCounter'+mbd_idx) );
    // seriesCounter += 1;// This is not ok for many graphs.
    if (eval('seriesCounter'+mbd_idx) == graphs.length) {
        console.log('graph drawing .................................');
        console.log('seriesOptions length: ' + seriesOptions.length);
        eval( 'seriesCounter'+mbd_idx+' = 0;' );
        createChart(mbd_idx,seriesOptions,dta_item);
    }

}
</script>



<!-- Right Graph Area -->
<ul id="sortables">
<?php
$sql = "SELECT * FROM {$g5['member_dash_table']}
        WHERE mb_id = '".$member['mb_id']."'
            AND mms_idx = '".$mms_idx."'
            AND mbd_type = 'graph'
            AND mbd_status = 'show'
        ORDER BY mbd_value
";
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    $row['sried'] = get_serialized($row['mbd_setting']);
    $row['data'] = json_decode($row['sried']['data_series'],true);
    unset($row['mbd_setting']);
    unset($row['sried']['data_series']);
    // print_r2($row); // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    for($j=0;$j<sizeof($row['data']);$j++) {
        // print_r2($row['data'][$j]);
        $row['chr_names'][] = $row['data'][$j]['name'];
        $row['chr_mms_idxs'][] = $row['data'][$j]['id']['mms_idx'];
        // target should be from local
        if($row['data'][$j]['id']['dta_json_file']=='output.target') {
            $row['data'][$j]['id']['dta_data_url'] = strip_http(G5_ADMIN_URL).'/v10/ajax';
        }
    }
    // mms 다중 아이콘 표현 (mms_idx 중복 제거)
    $row['chr_mms_idxs'] = array_unique($row['chr_mms_idxs']);
    for($j=0;$j<sizeof($row['chr_mms_idxs']);$j++) {
        $row['chr_mms_idx_icons'] .= '<i class="fa fa-circle"></i>';
    }
    // 그래프 이름 (tag name array if not desiganated name.)
    $row['mbd_graph_name'] = $row['sried']['graph_name'] ?: implode(", ",$row['chr_names']);
    // print_r2($row['chr_mms_idxs']);
    // print_r2($row['data']); // script내부에 표현이 안 되므로 여기 찍어요.
    ?>
    <li class="ui-state-default x<?=$column?>" mbd_idx="<?=$row['mbd_idx']?>">
        <span class='graph_icons'>
            <a href="javascript:" class="chart_view" style="display:none;"><i class='fa fa-bar-chart'></i></a>
            <a href="javascript:" class="chart_setting"><i class='fa fa-gear'></i></a>
        </span>
        <ul class="graph_setting">
            <li><a href="javascript:" class="graph_view">상세보기</a></li>
            <li><a href="javascript:" class="graph_name_change">이름변경</a></li>
            <li><a href="javascript:" class="graph_delete">삭제</a></li>
        </ul>
        <div class="graph_header">
            <span class="graph_title" title="<?=$row['mbd_graph_name']?>">
                <?=$row['mbd_graph_name']?>
            </span>
            <span class="mms_multi_marks"><!-- 여러 설비인 경우 아이콘 표현 -->
                <?=(sizeof($row['chr_mms_idxs'])>1)?$row['chr_mms_idx_icons']:''?>
            </span>
        </div>
        <div id="chart_<?=$row['mbd_idx']?>">
            <i class="fa fa-spin fa-circle-o-notch" id="spinner" style="position:absolute;top:80px;left:46%;font-size:4em;color:#ddd;"></i>
        </div>

        <script>
            // initialize script values
            var seriesOptions<?=$row['mbd_idx']?> = [], graphs<?=$row['mbd_idx']?> = []
                , seriesCounter<?=$row['mbd_idx']?> = 0;
            <?php
            // for loop as many times as graph count.
            for($j=0;$j<sizeof($row['data']);$j++) {
                // echo 'console.log("'.$row['data'][$j]['id']['dta_data_url'].'");';
                // in case for http://demo.bogwang.epcs.co.kr
                if( preg_match("/demo\./",G5_URL) ) {
                    $row['data'][$j]['id']['dta_data_url'] = (!preg_match("/demo\./",$row['data'][$j]['id']['dta_data_url'])) ? 'demo.'.$row['data'][$j]['id']['dta_data_url']
                                                                : $row['data'][$j]['id']['dta_data_url'];
                    $row['data'][$j]['id']['dta_data_url'] = (preg_match("/demo\.test\./",$row['data'][$j]['id']['dta_data_url'])) ? preg_replace("/test\./","",$row['data'][$j]['id']['dta_data_url'])
                                                                : $row['data'][$j]['id']['dta_data_url'];
                }
                ?>
                var dta_data_url = '<?=$row['data'][$j]['id']['dta_data_url']?>';
                var dta_json_file = '<?=$row['data'][$j]['id']['dta_json_file']?>';
                var dta_group = '<?=$row['data'][$j]['id']['dta_group']?>';
                var mms_idx = <?=$row['data'][$j]['id']['mms_idx']?>;
                var dta_type = '<?=$row['data'][$j]['id']['dta_type']?>';
                var dta_no = '<?=$row['data'][$j]['id']['dta_no']?>';
                var shf_no = '<?=$row['data'][$j]['id']['shf_no']?>';
                var dta_mmi_no = '<?=$row['data'][$j]['id']['dta_mmi_no']?>';
                var dta_defect = '<?=$row['data'][$j]['id']['dta_defect']?>';
                var dta_defect_type = '<?=$row['data'][$j]['id']['dta_defect_type']?>'; // 1,2,3,4...
                var dta_code = '<?=$row['data'][$j]['id']['dta_code']?>';    // only if err, pre
                var graph_type = '<?=$row['data'][$j]['type']?>';
                var graph_line = '<?=$row['data'][$j]['dashStyle']?>';
                var mbd_idx = <?=$row['mbd_idx']?>;
                var graph_name = '<?=$row['data'][$j]['id']['graph_name']?>';
                var graph_id1 = getGraphId(dta_json_file, dta_group, mms_idx, dta_type, dta_no, shf_no, dta_mmi_no, dta_defect, dta_defect_type, dta_code);
                // console.log(i+' 호출 시:'+graph_id1);

                // 그래프 배열 선언
                graphs<?=$row['mbd_idx']?>[<?=$j?>] = {
                    dta_data_url: dta_data_url,
                    dta_json_file: dta_json_file,
                    dta_group: dta_group,
                    mms_idx: mms_idx,
                    dta_type: dta_type,
                    dta_no: dta_no,
                    shf_no: shf_no,
                    dta_mmi_no: dta_mmi_no,
                    dta_defect: dta_defect,
                    dta_defect_type: dta_defect_type,
                    dta_code: dta_code,
                    graph_type: graph_type,
                    graph_line: graph_line,
                    graph_name: graph_name,
                    graph_id: graph_id1
                };

                var dta_item = '<?=$row['sried']['dta_item']?>';   // 일,주,월,년,분,초
                var dta_file = (dta_item=='minute'||dta_item=='second') ? '' : '.sum'; // measure.php(그룹핑), measure.sum.php(일자이상)
                var dta_unit = <?=$row['sried']['dta_unit']?>;   // 10,20,30,60
                dta_loading('show',mbd_idx);

                <?php
                // the latest time range from now exact!
                $en_timestamp = strtotime($row['sried']['en_date'].' '.$row['sried']['en_time']);
                $st_timestamp = strtotime($row['sried']['st_date'].' '.$row['sried']['st_time']);
                $diff_timestamp = $en_timestamp - $st_timestamp;
                $row['df_seconds'][$row['mbd_idx']][$j] = $diff_timestamp;
                $row['df_unit'][$row['mbd_idx']][$j] = $seconds[$row['sried']['dta_item']][0]*$row['sried']['dta_unit'];    // seconds per unit * item_count = interval seconds
                
                $row['en_date'] = date("Y-m-d",G5_SERVER_TIME);
                $row['en_time'] = date("H:i:s",G5_SERVER_TIME);
                $row['st_date'] = date("Y-m-d",G5_SERVER_TIME-$diff_timestamp);
                $row['st_time'] = date("H:i:s",G5_SERVER_TIME-$diff_timestamp);
                ?>
                var en_date = '<?=$row['en_date']?>';
                var en_time = '<?=$row['en_time']?>';
                var st_date = '<?=$row['st_date']?>';
                var st_time = '<?=$row['st_time']?>';
                // time range from db stored data. uncomment for testing below.
                // var en_date = '<?=$row['sried']['en_date']?>';
                // var en_time = '<?=$row['sried']['en_time']?>';
                // var st_date = '<?=$row['sried']['st_date']?>';
                // var st_time = '<?=$row['sried']['st_time']?>';

                var dta_url = '//'+dta_data_url+'/'+dta_json_file+dta_file+'.php?token=1099de5drf09'
                                +'&mms_idx='+mms_idx+'&dta_group='+dta_group+'&shf_no='+shf_no+'&dta_mmi_no='+dta_mmi_no
                                +'&dta_type='+dta_type+'&dta_no='+dta_no
                                +'&dta_defect='+dta_defect+'&dta_defect_type='+dta_defect_type
                                +'&dta_code='+dta_code
                                +'&dta_item='+dta_item+'&dta_unit='+dta_unit
                                +'&st_date='+st_date+'&st_time='+st_time+'&en_date='+en_date+'&en_time='+en_time
                                +'&graph_name='+graph_name+'&graph_id='+graph_id1+'&mbd_idx='+mbd_idx;
                // console.log(dta_url);
                // console.log(decodeURIComponent(graph_name));

                Highcharts.getJSON(
                    dta_url,
                    drawChart
                );
            <?php
            }
            // for loop as many times as graph count.
            ?>

            <?php
            // -----------------------------------------------------------------------
            // refresh periodically (10 seconds, 60 seconds,...)
            // for loop as many times as graph count.
            for($j=0;$j<sizeof($row['data']);$j++) {
                // echo 'console.log("'.$row['data'][$j]['id']['dta_data_url'].'");';
            ?>
                // Set loop interval 
                interval_<?=$row['mbd_idx']?>_<?=$j?> = setInterval(callChart_<?=$row['mbd_idx']?>_<?=$j?>, <?=$row['df_unit'][$row['mbd_idx']][$j]*1000?>);
                function callChart_<?=$row['mbd_idx']?>_<?=$j?>() {
                    // console.log( <?=$row['mbd_idx']?>+'-'+<?=$j?> );
                    // console.log(decodeURIComponent('<?=$row['data'][$j]['id']['graph_name']?>'));

                    var df_seconds = <?=$row['df_seconds'][$row['mbd_idx']][$j]?>;
                    var df_unit = <?=$row['df_unit'][$row['mbd_idx']][$j]?>;
                    // console.log('interval seconds: ' + df_unit);

                    var dta_data_url = '<?=$row['data'][$j]['id']['dta_data_url']?>';
                    var dta_json_file = '<?=$row['data'][$j]['id']['dta_json_file']?>';
                    var dta_group = '<?=$row['data'][$j]['id']['dta_group']?>';
                    var mms_idx = <?=$row['data'][$j]['id']['mms_idx']?>;
                    var dta_type = '<?=$row['data'][$j]['id']['dta_type']?>';
                    var dta_no = '<?=$row['data'][$j]['id']['dta_no']?>';
                    var shf_no = '<?=$row['data'][$j]['id']['shf_no']?>';
                    var dta_mmi_no = '<?=$row['data'][$j]['id']['dta_mmi_no']?>';
                    var dta_defect = '<?=$row['data'][$j]['id']['dta_defect']?>';
                    var dta_defect_type = '<?=$row['data'][$j]['id']['dta_defect_type']?>'; // 1,2,3,4...
                    var dta_code = '<?=$row['data'][$j]['id']['dta_code']?>';    // only if err, pre
                    var graph_type = '<?=$row['data'][$j]['type']?>';
                    var graph_line = '<?=$row['data'][$j]['dashStyle']?>';
                    var mbd_idx = <?=$row['mbd_idx']?>;
                    var graph_name = '<?=$row['data'][$j]['id']['graph_name']?>';
                    var graph_id1 = getGraphId(dta_json_file, dta_group, mms_idx, dta_type, dta_no, shf_no, dta_mmi_no, dta_defect, dta_defect_type, dta_code);

                    // Set en_date and st_date for loop call ------
                    var date1 = new Date(); // current date
                    // console.log( date1.getFullYear().toString()+"-"
                    //                 +((date1.getMonth()+1).toString().length==2?(date1.getMonth()+1).toString():"0"+(date1.getMonth()+1).toString())+"-"
                    //                 +(date1.getDate().toString().length==2?date1.getDate().toString():"0"+date1.getDate().toString())+" "
                    //                 +(date1.getHours().toString().length==2?date1.getHours().toString():"0"+date1.getHours().toString())+":"
                    //                 +(date1.getMinutes().toString().length==2?date1.getMinutes().toString():"0"+date1.getMinutes().toString())+":"
                    //                 +(date1.getSeconds().toString().length==2?date1.getSeconds().toString():"0"+date1.getSeconds().toString()) );
                    var en_date = date1.getFullYear().toString()+"-"
                                    +((date1.getMonth()+1).toString().length==2?(date1.getMonth()+1).toString():"0"+(date1.getMonth()+1).toString())+"-"
                                    +(date1.getDate().toString().length==2?date1.getDate().toString():"0"+date1.getDate().toString());
                    var en_time = (date1.getHours().toString().length==2?date1.getHours().toString():"0"+date1.getHours().toString())+":"
                                    +(date1.getMinutes().toString().length==2?date1.getMinutes().toString():"0"+date1.getMinutes().toString())+":"
                                    +(date1.getSeconds().toString().length==2?date1.getSeconds().toString():"0"+date1.getSeconds().toString());
                    // console.log('cur dt: ' + en_date + ' ' + en_time);
                    // console.log(date1 + '에서 ' + df_seconds+'초 전은... ');
                    date1.setSeconds(date1.getSeconds() - df_seconds); // some amount of seconds prior..
                    // console.log( date1.getFullYear().toString()+"-"
                    //                 +((date1.getMonth()+1).toString().length==2?(date1.getMonth()+1).toString():"0"+(date1.getMonth()+1).toString())+"-"
                    //                 +(date1.getDate().toString().length==2?date1.getDate().toString():"0"+date1.getDate().toString())+" "
                    //                 +(date1.getHours().toString().length==2?date1.getHours().toString():"0"+date1.getHours().toString())+":"
                    //                 +(date1.getMinutes().toString().length==2?date1.getMinutes().toString():"0"+date1.getMinutes().toString())+":"
                    //                 +(date1.getSeconds().toString().length==2?date1.getSeconds().toString():"0"+date1.getSeconds().toString()) );
                    var st_date = date1.getFullYear().toString()+"-"
                                    +((date1.getMonth()+1).toString().length==2?(date1.getMonth()+1).toString():"0"+(date1.getMonth()+1).toString())+"-"
                                    +(date1.getDate().toString().length==2?date1.getDate().toString():"0"+date1.getDate().toString());
                    var st_time = (date1.getHours().toString().length==2?date1.getHours().toString():"0"+date1.getHours().toString())+":"
                                    +(date1.getMinutes().toString().length==2?date1.getMinutes().toString():"0"+date1.getMinutes().toString())+":"
                                    +(date1.getSeconds().toString().length==2?date1.getSeconds().toString():"0"+date1.getSeconds().toString());
                    // console.log('start dt: ' + st_date + ' ' + st_time);
                    // console.log( '---------' );

                    var dta_item = '<?=$row['sried']['dta_item']?>';   // 일,주,월,년,분,초
                    var dta_file = (dta_item=='minute'||dta_item=='second') ? '' : '.sum'; // measure.php(그룹핑), measure.sum.php(일자이상)
                    var dta_unit = <?=$row['sried']['dta_unit']?>;   // 10,20,30,60
                    dta_loading('show',mbd_idx);

                    var dta_url = '//'+dta_data_url+'/'+dta_json_file+dta_file+'.php?token=1099de5drf09'
                                    +'&mms_idx='+mms_idx+'&dta_group='+dta_group+'&shf_no='+shf_no+'&dta_mmi_no='+dta_mmi_no
                                    +'&dta_type='+dta_type+'&dta_no='+dta_no
                                    +'&dta_defect='+dta_defect+'&dta_defect_type='+dta_defect_type
                                    +'&dta_code='+dta_code
                                    +'&dta_item='+dta_item+'&dta_unit='+dta_unit
                                    +'&st_date='+st_date+'&st_time='+st_time+'&en_date='+en_date+'&en_time='+en_time
                                    +'&graph_name='+graph_name+'&graph_id='+graph_id1+'&mbd_idx='+mbd_idx;
                    // console.log(dta_url);
                    // console.log(decodeURIComponent(graph_name));

                    Highcharts.getJSON(
                        dta_url,
                        drawChart
                    );

                }

            <?php
            }
            // for loop as many times as graph count.
            ?>
        </script>


    </li>
    <?php
}
if($i<=0) {
    echo '<li class="graph_empty">그래프가 없습니다. 그래프를 먼저 등록하세요. <a href="./iframe.graph.php?mms_idx='.$mms_idx.'" class="">[바로가기]</a></li>';
}
?>
</ul>


<script>
// Sortable portlets 
$( "#sortables" ).sortable({
    handle:'.graph_header'
    // , sort: function(e, ui) {
    //     $(".ui-sortable-handle.ui-sortable-helper").css({'top':e.pageY-350});
    // }
    , stop: function( event, ui ) {
        // console.log('stop');
        var mbd_idxs = [];
        $('#sortables li[mbd_idx]').each(function(i) {
            // console.log(i+'-> '+$(this).attr('mbd_idx'));
            mbd_idxs[i] = $(this).attr('mbd_idx');
        });
        //-- 디버깅 Ajax --//
        $.ajax({
            url:g5_user_admin_ajax_url+'/dash.php',
            data:{"aj":"srt","mbd_idxs":mbd_idxs},
            dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
                // console.log(res);
                //var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
                if(res.result == true) {
                    console.log('그래프 재정렬 완료!');
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
$( "#sortables" ).disableSelection();

// 그래프 개별보기로 이동
$(document).on('click','.graph_view, .chart_view',function(e){
    e.preventDefault();
    var my_mbd_idx = $(this).closest('li[mbd_idx]').attr('mbd_idx');
    // alert('./iframe.graph.php?mms_idx=<?=$mms_idx?>&mbd_idx='+my_mbd_idx);
    self.location.href = './<?=preg_replace("/index/","graph",$g5['file_name'])?>.php?mms_idx=<?=$mms_idx?>&mbd_idx='+my_mbd_idx;
});

// 이름변경
$(document).on('click','.graph_name_change',function(e){
    e.preventDefault();
    var my_mbd_idx = $(this).closest('li[mbd_idx]').attr('mbd_idx');
    var my_mbd_title = $(this).closest('li[mbd_idx]').find('.graph_title').attr('title');
    var my_mbd_title_input = '<input style="width:89%;height:19px;" value="'+my_mbd_title+'">';
    $(this).closest('li[mbd_idx]').find('.graph_title').empty().append(my_mbd_title_input).find('input').focus();
    // $(this).closest('li[mbd_idx]').find('.graph_title').replaceWith('<input style="width:88%;">'+''+'</input>');
    $('.graph_setting').hide(); // 다른 모든 설정 팝오버 숨김
    $('.graph_setting').closest('li').find('.chart_setting i').removeClass('fa-times').addClass('fa-gear');

});
// 이름변경 blur - no action
// $(document).on('blur','.graph_title input',function(e){
//     e.preventDefault();
//     var my_mbd_title = $(this).closest('li[mbd_idx]').find('.graph_title').attr('title');
//     $(this).closest('li[mbd_idx]').find('.graph_title').text(my_mbd_title);
// });

// 이름변경 enterkey - action for change by ajax
$(document).on('keyup focusout','.graph_title input',function(e){
    e.preventDefault();
    var my_title_span = $(this).closest('li[mbd_idx]').find('.graph_title');
    var my_mbd_idx = $(this).closest('li[mbd_idx]').attr('mbd_idx');
    var my_mbd_title = $(this).closest('li[mbd_idx]').find('.graph_title').attr('title');
    if (e.type == 'focusout') {
        my_title_span.text(my_mbd_title);
    }
    else if(e.keyCode == 13) {
        // alert('Enter key was pressed.');
        var chg_mbd_title = $(this).val();  // due to blur event bubbling, unique name required. 
        var blur = false;
        //-- 디버깅 Ajax --//
        $.ajax({
            url:g5_user_admin_ajax_url+'/dash.php',
            data:{"aj":"tit","mbd_idx":my_mbd_idx,"mbd_title":chg_mbd_title},
            dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
                // console.log(res);
                //var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
                if(res.result == true) {
                    alert(res.msg);
                    // time delay is needed.
                    setTimeout(function(){
                        if (!blur) {
                            blur = true;
                            my_title_span.text(chg_mbd_title);
                            my_title_span.attr('title',chg_mbd_title);
                            blur = false;
                        }
                    }, 150);
                }
                else {
                    alert(res.msg);
                    my_title_span.text(my_mbd_title);
                }
            },
            error:function(xmlRequest) {
                alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
                    + ' \n\rresponseText: ' + xmlRequest.responseText);
            } 
        });
    }
});

// 그래프 설정
$(document).on('click','.chart_setting',function(e){
    e.preventDefault();
    var my_graph_setting = $(this).closest('li').find('.graph_setting');
    if( my_graph_setting.is(':hidden') ) {
        $('.graph_setting').hide(); // 다른 모든 설정 팝오버 숨김
        $('.graph_setting').closest('li').find('.chart_setting i').removeClass('fa-times').addClass('fa-gear');
        my_graph_setting.show();
        $(this).find('i').removeClass('fa-gear').addClass('fa-times');
    }
    else {
        my_graph_setting.hide();
        $(this).find('i').removeClass('fa-times').addClass('fa-gear');
    }
});

// 그래프 제거하기
$(document).on('click','.graph_delete',function(e){
    e.preventDefault();
    my_mbd_idx = $(this).closest('li[mbd_idx]').attr('mbd_idx');
    $(this).closest('li[mbd_idx]').find('.chart_setting').trigger('click');
    if(confirm('선택한 그래프를 삭제하시겠습니까?')) {

        //-- 디버깅 Ajax --//
        $.ajax({
            url:g5_user_admin_ajax_url+'/dash.php',
            data:{"aj":"del","mbd_idx":my_mbd_idx},
            dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
                // console.log(res);
                //var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
                if(res.result == true) {
                    $('li[mbd_idx='+my_mbd_idx+']').toggle('scale'); // drop, fold, highlight, scale, slide
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

// 부모창에 나의 높이를 전달
parent.postMessage(document.body.scrollHeight,"<?=G5_URL?>"); // 부모창의 URL 주소
</script>

<?php
include_once('./_tail.sub.php');
?>
