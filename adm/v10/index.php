<?php
$sub_menu = "910100";
include_once('./_common.php');

// print_r3($_SESSION['ss_com_idx']);
if($_SESSION['ss_com_idx']&&$member['mb_level']>=8) {
    $com = get_table_meta('company','com_idx',$_SESSION['ss_com_idx']);
    // print_r2($com);
    $com_name = $com['com_name'] ? ' ('.$com['com_name'].')' : '';
}

$g5['title'] = '대시보드'.$com_name;
//include_once('./_top_menu_default.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
        return;
    }
}

// add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/index.css">', 0);
// add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/index.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_URL.'/js/slick-1.8.1/slick/slick-theme.css">', 0);
add_javascript('<script src="'.G5_USER_URL.'/js/slick-1.8.1/slick/slick.min.js"></script>', 10);
?>
<style>
</style>

<div class="mms_list_wrapper">
<!-- IMP List -->
<div class="mms_icons list_icons">
    <a href="javascript:" class="icon_x8">x8</a>
    <a href="javascript:" class="icon_x10">x10</a>
    <a href="javascript:" class="icon_x12">x12</a>
    <a href="javascript:" class="icon_x15">x15</a>
    <a href="./dashboard_setting.php?file_name=<?=$g5['file_name']?>" class="icon_setting"><span>setting</span></a>
    <a href="javascript:" class="icon_max"><span>max</span></a>
</div>
<ul class="list_wrapper">
    <?php
    // print_r2($member);
    $sql = "SELECT *
            FROM {$g5['member_dash_table']} AS mbd
                LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = mbd.mms_idx AND mms.com_idx = mbd.com_idx
            WHERE mb_id = '".$member['mb_id']."' AND mbd_type = 'list'
                AND mbd_status = 'show'
                AND mms.com_idx = '".$_SESSION['ss_com_idx']."'
            ORDER BY mbd_value
    ";
    // echo $sql;
    $result = sql_query($sql,1);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $img_container_style = ($i==0) ? ' on':'';
        // print_r2($row);
        $row['com'] = get_table_meta('company','com_idx',$row['com_idx']);
        $row['mmg'] = get_table_meta('mms_group','mmg_idx',$row['mmg_idx']);
        $row['img'] = get_mms_image(array("mms_idx"=>$row['mms_idx'],"img_width"=>227,"img_height"=>180));
        //print_r2($row['img']);
        // echo $row['img']['img'];
        // print_r2($row['mmg']);
        // 기종 추출 (해당 설비 맨 마지막 1개)
        $sql1 = "SELECT mmi_no FROM {$g5['mms_item_table']}
                WHERE mms_idx = '".$row['mms_idx']."'
                    AND mmi_status NOT IN ('trash','delete')
                ORDER BY mmi_idx DESC
                LIMIT 1
        ";
        // echo $sql1.'<br>';
        $row['item'] = sql_fetch($sql1,1);
    ?>
    <li class="ui-state-default mms_container <?=$img_container_style?>"
        mms_img_src="<?=$row['img']['src']?>"
        mms_name="<?=$row['mms_name']?>"
        mms_idx="<?=$row['mms_idx']?>">
        <table class="list_mms_table">
        <tr class="tr_title">
            <td colspan="2" class="td_center" title="<?=$row['mms_name']?>"><?=cut_str($row['mms_name'],7,'..')?></td>
        </tr>
        <tr style="display:none;">
            <td>생산기종</td>
            <td class="mms_mmi_no"><?=$row['item']['mmi_no']?></td>
        </tr>
        <tr>
            <td>일생산</td>
            <td><span class="daily_output"></span></td>
        </tr>
        <tr style="display:none;">
            <td>달성율</td>
            <td><span class="daily_output_rate"></span></td>
        </tr>
        <tr>
            <td>장비이상</td>
            <td><span class="daily_error_count"></span></td>
        </tr>
        <tr>
            <td>예지알람</td>
            <td><span class="daily_alarm_count"></span></td>
        </tr>
        <tr>
            <td colspan="2" class="td_dot"><span class="run_status"></span></td>
        </tr>
        </table>
    </li>
    <script>
    $(function(e) {
        var mms_status_colors = ['#eebb0c','#62ee0c','#ee0c0c'];    // 황색, 초록, 빨강
        // var mms_run_status = ['POWER OFF','수동','자동','이상'];
        var mms_run_status = ['<?=implode("','",$g5['set_run_status_value'])?>'];
        // Set loop interval for 1min (60second)
        var interval_seconds = 60;
        mms_interval_<?=$row['mms_idx']?> = setInterval(callMMS_<?=$row['mms_idx']?>, interval_seconds*1000);
        function callMMS_<?=$row['mms_idx']?>(fnRunCount) {

            var this_mms_idx = <?=$row['mms_idx']?>;
            // 5 dot should be fetched at first time.
            var run_count1 = fnRunCount || 0;
            // console.log(run_count1);

            // console.log( 'mms: ' + <?=$row['mms_idx']?> );
            var my_mms_li = $('li[mms_idx=<?=$row['mms_idx']?>]');
            // console.log(my_mms_li.attr('mms_idx'));

            // check how many dots & ajax for 5 or 1
            var run_count = (run_count1>0) ? run_count1 : 5 - my_mms_li.find('.td_dot i').length;
            // console.log(run_count);
            
            //-- 디버깅 Ajax --//
            $.ajax({
                url:'//<?=$row['mms_data_url']?>/mms.php',
                data:{"token":"0304de5drf07","mms_idx":"<?=$row['mms_idx']?>","run_count":run_count},
                dataType:'json', timeout:15000, beforeSend:function(){}, success:function(res){
                    // console.log(this_mms_idx);
                    // console.log(res);
                    //var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
                    if(res.result == true) {
                        // console.log('<?=$row['mms_idx']?> mms 호출 완료!');

                        // product item
                        var item_no = (res.item_no==undefined) ? '-' : res.item_no;
                        my_mms_li.find('.mms_mmi_no').text( item_no );

                        // daily output count
                        // my_mms_li.find('.daily_output').text( thousand_comma(res.output_count) ).hide().fadeIn('slow');
                        my_mms_li.find('.daily_output').text( thousand_comma(res.output_count) );

                        // output rate
                        if(res.output_rate) {
                            my_mms_li.find('.daily_output_rate').text( res.output_rate + '%' );
                        }
                        else {
                            my_mms_li.find('.daily_output_rate').text( '-' );
                        }

                        // error
                        my_mms_li.find('.daily_error_count').text( thousand_comma(res.error_count) );

                        // predict and alarm
                        my_mms_li.find('.daily_alarm_count').text( thousand_comma(res.alarm_count) );

                        // hidden data of 3, output_success, output_defect, run_time_hour
                        my_output_count_success = res.output_count_success || 0;
                        my_mms_li.find('table').data('daily_output_success', my_output_count_success );
                        my_output_count_defect = res.output_count_defect || 0;
                        my_mms_li.find('table').data('daily_output_defect', my_output_count_defect );
                        my_run_time_hour = res.run_time_hour || 0;
                        // console.log(my_run_time_hour);
                        my_mms_li.find('table').data('daily_run_time_hour', my_run_time_hour );

                        // run status related.
                        var run_status = (res.mms_status==null||res.mms_status==undefined) ? '-' : mms_run_status[res.mms_status];
                        my_mms_li.find('.run_status').text( run_status );


                        // data_left mms info, if exists.
                        // console.log( 'date_left mms(<?=$row['mms_idx']?>): ' + $('.data_left[mms_idx='+this_mms_idx+']').length );
                        if( $('.data_left[mms_idx='+this_mms_idx+']').length ) {
                            // console.log(this_mms_idx + ' exists.');
                            // console.log('my_output_count_success: ' + my_output_count_success);
                            // console.log('my_output_count_defect: ' + my_output_count_defect);
                            var my_mms_left = $('.data_left[mms_idx='+this_mms_idx+']');

                            var item_no = (res.item_no==undefined) ? '-' : res.item_no;
                            my_mms_left.find('.mms_mmi_no').text( item_no );
                            my_mms_left.find('.daily_output').text( thousand_comma(res.output_count) );
                            my_mms_left.find('.daily_output_success').text( thousand_comma(my_output_count_success) );
                            my_mms_left.find('.daily_output_defect').text( thousand_comma(my_output_count_defect) );

                            if(res.output_rate) {
                                my_mms_left.find('.daily_output_rate').text( res.output_rate + '%' );
                            }
                            else {
                                my_mms_left.find('.daily_output_rate').text( '-' );
                            }

                            my_mms_left.find('.daily_error_count').text( thousand_comma(res.error_count) );
                            my_mms_left.find('.daily_alarm_count').text( thousand_comma(res.alarm_count) );
                            my_mms_left.find('.daily_run_time_hour').text( my_run_time_hour );
                        }



                    }
                    else {
                        console.log('<?=$row['mms_name']?>(<?=$row['mms_idx']?>): ' + res.msg);
                    }
                },
                error:function(xmlRequest) {
                    console.log('<?=$row['mms_name']?>(<?=$row['mms_idx']?>): error');
                    console.log('Status: ' + xmlRequest.status);
                    console.log('statusText: ' + xmlRequest.statusText);
                    console.log('responseText: ' + xmlRequest.responseText);
                }
            });

        }
        // Initial call for the first time.
        callMMS_<?=$row['mms_idx']?>(5);
    });
    </script>
    <?php
    } // the end of for statement
    if($i<=0) {
        echo '<div class="no_mms_list">설비 정보가 없습니다. <a href="./dashboard_setting.php?file_name='.$g5['file_name'].'">세팅(Setting) 페이지 이동</a></div>';
    }
    ?>
</ul>
</div> <!-- the end of .mms_list_wrapper -->
<script>
<?php
// if not mms exists
if($i>0) {
?>
    $('.list_wrapper').slick({
        infinite: false,
        slidesToShow: 10,
        slidesToScroll: 10,
        prevArrow: '<i class="fa fa-angle-left slide_prev"></i>',
        nextArrow: '<i class="fa fa-angle-right slide_next"></i>'
    });
<?php
}
?>
</script>



<!-- Data Infomation -->
<div class="mms_icons graph_icons">
    <a href="javascript:" class="icon_x1"><span>x1</span></a>
    <a href="javascript:" class="icon_x2"><span>x2</span></a>
    <a href="javascript:" class="icon_x3"><span>x3</span></a>
    <a href="javascript:" class="icon_x4"><span>x4</span></a>
    <a href="javascript:" class="icon_x5"><span>x5</span></a>
    <a href="javascript:" class="icon_graph"><span>graph</span></a>
    <a href="javascript:" class="icon_max"><span>max</span></a>
</div>
<div class="mms_wrapper">
    <!-- MMS info Area -->
    <div class="data_left">
        <div class="mms_title">
            <span>Title</span>
            <a href="javascirtp:" class="icon_mms_setting"><i class="fa fa-gear"></i></a>
            <ul class="span_mms_setting">
                <li><a href="javascript:" class="set_mms_view">설비이력카드</a></li>
                <!-- <li><a href="javascript:" class="set_mms_parts">부속품관리</a></li> -->
                <li style="display:no ne;"><a href="javascript:" class="set_mms_maintain">정비관리</a></li>
                <!-- <li><a href="javascript:" class="set_mms_checks">점검기준관리</a></li> -->
                <li style="display:no ne;"><a href="javascript:" class="set_mms_item">기종설정</a></li>
                <!-- <li><a href="javascript:" class="set_mms_shift">교대및목표설정</a></li> -->
                <li><a href="javascript:" class="set_mms_graph_setting">그래프설정</a></li>
                <li><a href="javascript:" class="set_mms_setting">설비설정</a></li>
            </ul>
        </div>
        <div class="mms_image">No image</div>

        <div class="mms_tabs" style="margin-top:10px;">
            <div id="tabs1">
                <ul>
                    <li><a href="#tabs-1">설비정보</a></li>
                    <li><a href="#tabs-2">주요현황</a></li>
                </ul>
                <div id="tabs-1">
                    <table class="data_mms_table">
                    <tr>
                        <td>계획정비</td>
                        <td>
                            <?php
                            $sql = "SELECT COUNT(wr_id) AS cnt FROM g5_write_plan WHERE wr_is_comment = 0 AND wr_1 = '".$_SESSION['ss_com_idx']."' ";
                            // echo $sql.'<br>';
                            $board_plan = sql_fetch($sql,1);
                            echo '<a href="'.G5_BBS_URL.'/board.php?bo_table=plan">'.$board_plan['cnt'].'</a>';
                            ?> 건
                        </td>
                    </tr>
                    <tr>
                        <td>정비이력</td>
                        <td>
                            <?php
                            $sql = "SELECT COUNT(wr_id) AS cnt FROM g5_write_maintain WHERE wr_is_comment = 0 AND wr_1 = '".$_SESSION['ss_com_idx']."' ";
                            // echo $sql.'<br>';
                            $maintain_plan = sql_fetch($sql,1);
                            echo '<a href="'.G5_BBS_URL.'/board.php?bo_table=maintain">'.$maintain_plan['cnt'].'</a>';
                            ?> 건
                        </td>
                    </tr>
                    <tr>
                        <td>매뉴얼</td>
                        <td>
                            <?php
                            $sql = "SELECT COUNT(wr_id) AS cnt FROM g5_write_manual WHERE wr_is_comment = 0 AND wr_1 = '".$_SESSION['ss_com_idx']."' ";
                            // echo $sql.'<br>';
                            $manual_plan = sql_fetch($sql,1);
                            echo '<a href="'.G5_BBS_URL.'/board.php?bo_table=manual">'.$manual_plan['cnt'].'</a>';
                            ?> 건
                        </td>
                    </tr>
                    <tr>
                        <td>설비사양서</td>
                        <td>
                            <?php
                            $sql = "SELECT COUNT(wr_id) AS cnt FROM g5_write_drawing WHERE wr_is_comment = 0 AND wr_1 = '".$_SESSION['ss_com_idx']."' ";
                            // echo $sql.'<br>';
                            $drawing_plan = sql_fetch($sql,1);
                            echo '<a href="'.G5_BBS_URL.'/board.php?bo_table=drawing">'.$drawing_plan['cnt'].'</a>';
                            ?> 건
                        </td>
                    </tr>
                    <tr>
                        <td>A/S연락처</td>
                        <td>
                            <?php
                            $sql = "SELECT COUNT(wr_id) AS cnt FROM g5_write_contact WHERE wr_is_comment = 0 AND wr_1 = '".$_SESSION['ss_com_idx']."' ";
                            // echo $sql.'<br>';
                            $contact_plan = sql_fetch($sql,1);
                            echo '<a href="'.G5_BBS_URL.'/board.php?bo_table=contact">'.$contact_plan['cnt'].'</a>';
                            ?> 건
                        </td>
                    </tr>
                    </table>
                </div>
                <!-- 설비정보 -->
                <div id="tabs-2">
                    <table class="data_mms_table">
                    <tr style="display:none;">
                        <td>생산기종</td>
                        <td class="mms_mmi_no"><?=$row['item']['mmi_no']?></td>
                    </tr>
                    <tr>
                        <td>일생산</td>
                        <td class="daily_output">0</td>
                    </tr>
                    <tr style="display:none;">
                        <td>달성율</td>
                        <td class="daily_output_rate">0%</td>
                    </tr>
                    <tr>
                        <td>장비이상</td>
                        <td class="daily_error_count">0</td>
                    </tr>
                    <tr>
                        <td>예지알람</td>
                        <td class="daily_alarm_count">0</td>
                    </tr>
                    <tr>
                        <td>가동시간</td>
                        <td><span class="daily_run_time_hour">0</span> <span style="color:#9e9e9e;">hour</span></td>
                    </tr>
                    <tr>
                        <td>정상</td>
                        <td class="daily_output_success">0</td>
                    </tr>
                    <tr>
                        <td>불량</td>
                        <td class="daily_output_defect">0</td>
                    </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(function(e) {
        var $tabs = $("#tabs1");
        $( "#tabs1" ).tabs({
            create: function(event, ui) {
                // Adjust hashes to not affect URL when clicked.
                var widget = $tabs.data("uiTabs");
                widget.panels.each(function(i){
                this.id = "uiTab_" + this.id; // Prepend a custom string to tab id.
                widget.anchors[i].hash = "#" + this.id;
                $(widget.tabs[i]).attr("aria-controls", this.id);
                });
            },
            activate: function(event, ui) {
                // Add the original "clean" tab id to the URL hash.
                window.location.hash = ui.newPanel.attr("id").replace("uiTab_", "");
            },
        });

        var $tabs = $("#tabs2");
        $( "#tabs2" ).tabs({
            create: function(event, ui) {
                // Adjust hashes to not affect URL when clicked.
                var widget = $tabs.data("uiTabs");
                widget.panels.each(function(i){
                this.id = "uiTab_" + this.id; // Prepend a custom string to tab id.
                widget.anchors[i].hash = "#" + this.id;
                $(widget.tabs[i]).attr("aria-controls", this.id);
                });
            },
            activate: function(event, ui) {
                // Add the original "clean" tab id to the URL hash.
                window.location.hash = ui.newPanel.attr("id").replace("uiTab_", "");
            },
        });

    } );        
    </script>

    <!-- Right Graph Area -->
    <div class="data_right">
        <iframe id="data_right" src="./iframe.empty.php" frameborder="0" scrolling="no"></iframe>
    </div>
    <script>
    // iFrame높이 자동(자식창의 높이에 따라 맞춤)
    function receiveMessage(event) {
        // if localhost com.
        if( /localhost/.test(event.origin) ) {
            $("#data_right").height(event.data + 10); 
        }
        else {
            if (event.origin !== "<?=G5_URL?>")	// 자식창의 URL 주소
            return;
            $("#data_right").height(event.data + 10); 
        }
    }
    if ('addEventListener' in window){ 
        window.addEventListener('message', receiveMessage, false); 
    }
    else if ('attachEvent' in window) { //IE
        window.attachEvent('onmessage', receiveMessage); 
    } 
    </script>

</div>

<div class="btn_fixed_top">
    <a href="./dashboard_graph_multi.php" com_idx="<?=$_SESSION['ss_com_idx']?>" class="btn btn_02" style="display:none;"><i class="fa fa-bar-chart"></i> 겹쳐보기</a>
    <a href="./dashboard_mms_group.php" com_idx="<?=$_SESSION['ss_com_idx']?>" class="btn btn_02 btn_mms_group"><i class="fa fa-th"></i> 배치도</a>
    <a href="./dashboard_setting.php?file_name=<?=$g5['file_name']?>" id="btn_add" class="btn btn_02" style="display:none;"><i class="fa fa-gear"></i> 설정</a>
</div>

<script>
var my_mms_idx = 1, // 디폴트 mms_idx
    my_mms_name = ''
    file_name='<?php echo $g5['file_name']?>';

// 설비 클릭 ======================================================
$(document).on('click','.list_mms_table',function(e){
    e.preventDefault();
    var this_li = $(this).closest('li.mms_container');
    my_mms_idx = this_li.attr('mms_idx'); // my_mms_idx 변경 
    my_mms_name = this_li.attr('mms_name');
    // 일단 다른 설비 비활성
    $('.mms_container').each(function(e){
        $(this).removeClass('on');
    });
    // 나의 설비 활성
    this_li.addClass('on');

    // 타이블 변경, set mms_idx attritube
    $('.data_left').find('.mms_title span:first-child').text( cut_str(my_mms_name,17,'..') );
    $('.data_left').attr('mms_idx',my_mms_idx);

    // 이미지 변경
    
    this_img = $('<img src="'+this_li.attr('mms_img_src')+'">');
    $('.data_left').find('.mms_image').empty().append( this_img );
    
    // 생산기종
    $('.data_left').find('.mms_mmi_no').text( this_li.find('.mms_mmi_no').text() );

    // 일생산
    $('.data_left').find('.daily_output').text( this_li.find('.daily_output').text() );

    // 달성율
    $('.data_left').find('.daily_output_rate').text( this_li.find('.daily_output_rate').text() );

    // 장비이상
    $('.data_left').find('.daily_error_count').text( this_li.find('.daily_error_count').text() );

    // 예지알람
    $('.data_left').find('.daily_alarm_count').text( this_li.find('.daily_alarm_count').text() );

    // 가동시간
    $('.data_left').find('.daily_run_time_hour').text( this_li.find('table').data('daily_run_time_hour') );

    // 합격
    this_daily_output_success = this_li.find('table').data('daily_output_success') || 0;
    $('.data_left').find('.daily_output_success').text( thousand_comma(this_daily_output_success) );

    // 불량
    this_daily_output_defect = this_li.find('table').data('daily_output_defect') || 0;
    $('.data_left').find('.daily_output_defect').text( thousand_comma(this_daily_output_defect) );

    // console.log('---------------');
    // console.log(this_li.find('table').data());
    // console.log(this_li.find('table').data('daily_output_success'));

    // Popover Setting close.
    $('.span_mms_setting').hide()
    .prev().find('i').removeClass('fa-times').addClass('fa-gear');

    // 프레임 이동
    $('#data_right').attr('src', './iframe.<?=$g5['file_name']?>.php?mms_idx='+my_mms_idx);
    
    // $('#data_right').attr('src', './iframe.<?=preg_replace("/index/","graph",$g5['file_name'])?>.php?mms_idx='+my_mms_idx);

});

// 로딩시 맨 처음 설비 클릭 (디폴트)
$('.list_mms_table').eq(0).trigger('click');

// 그래프 아이콘 클릭 (2줄, 3줄, 4줄, 5줄)


// 그래프 클릭
$(document).on('click','.icon_graph',function(e){
    e.preventDefault();
    // console.log(my_mms_idx);
    $('#data_right').attr('src', './iframe.<?=preg_replace("/index/","graph",$g5['file_name'])?>.php?mms_idx='+my_mms_idx);
    // window.frames["#data_right"].location.href="./iframe.graph.php?mms_idx="+my_mms_idx;
});

// x2, x3, x4 click for graph
$(document).on('click','.icon_x1, .icon_x2, .icon_x3, .icon_x4, .icon_x5',function(e){
    e.preventDefault();
    var column = $(this).attr('class').replace('icon_x','');
    // console.log( column );
    $('#data_right').attr('src', './iframe.<?=$g5['file_name']?>.php?mms_idx='+my_mms_idx+'&column='+column);
});
// x8, x10, x15 click for mms
$(document).on('click','.icon_x8, .icon_x10, .icon_x12, .icon_x15, .icon_x20',function(e){
    e.preventDefault();
    var column = $(this).attr('class').replace('icon_x','');
    // remvoe class like x8, x10... 
    var column_old_arr = $('.list_wrapper').find('li').attr('class').split(' ');
    // console.log( column_old_arr.length );
    for(i=0;i<column_old_arr.length;i++) {
        // console.log(column_old_arr[i].substr(0,1));
        if( column_old_arr[i].substr(0,1) == 'x' ) {
            var column_old_class = column_old_arr[i];
        }
    }
    // console.log('old: '+column_old_class);
    $('.list_wrapper').find('li').removeClass(column_old_class).addClass('x'+column);
    // icon_x들 전부 비활성
    $('.list_icons a[class^=icon_x]').removeClass('on');
    // active on for my icon
    $('.list_icons').find('.icon_x'+column).addClass('on');

});


// maximize view for graph
$(document).on('click','.icon_max',function(e){
    e.preventDefault();
    var item = $(this).closest('div.mms_icons').hasClass('list_icons');
    // console.log(item);
    // mms_icon max click
    if(item) {
        // alert('크게보기 작업중');
        $('.max_wrapper').fadeIn('fast');
        $('.max_wrapper').prepend( $('.list_icons') ).find('.icon_max').text('min').attr('class','icon_min');
        $('.list_icons').find('a').slice(0,4).show();
        // icon_x들 전부 비활성
        $('.list_icons a[class^=icon_x]').removeClass('on');

        $('.max_container').append( $('.list_wrapper') );
        $('.list_wrapper').slick('unslick');

        // remvoe class like x8, x10... 
        var column_old_arr = $('.list_wrapper').find('li').attr('class').split(' ');
        for(i=0;i<column_old_arr.length;i++) {
            if( column_old_arr[i].substr(0,1) == 'x' ) {
                var column_old_class = column_old_arr[i];
            }
        }
        // console.log('old: '+column_old_class);
        var column_class = column_old_class || 'x10';
        $('.list_wrapper').find('li').removeClass(column_old_class).addClass(column_class);
        // active icon on class
        $('.list_icons').find('.icon_'+column_class).addClass('on');

    }
    // graph_icon max click
    else {
        $('.max_wrapper').fadeIn('fast');
        $('.max_wrapper').prepend( $('.graph_icons') ).find('.icon_max').text('min').attr('class','icon_min');
        $('.max_container').append( $('#data_right') );
    }
});
// close graph max
$(document).on('click','.graph_icons .icon_min',function(e){
    e.preventDefault();
    $('.graph_icons').insertBefore( $('.mms_wrapper') ).find('.icon_min').text('max').attr('class','icon_max');
    $('.max_wrapper').fadeOut('fast');
    $('.data_right').append( $('#data_right') );
});
// close mms max
$(document).on('click','.list_icons .icon_min',function(e){
    e.preventDefault();
    $('.list_icons').appendTo( $('.mms_list_wrapper') ).find('.icon_min').text('max').attr('class','icon_max');
    $('.list_icons').find('a').slice(0,4).hide();
    $('.max_wrapper').fadeOut('fast');
    $('.mms_list_wrapper').append( $('.list_wrapper') );
    $('.list_wrapper').slick({
        infinite: false,
        slidesToShow: 10,
        slidesToScroll: 10,
        prevArrow: '<i class="fa fa-angle-left slide_prev"></i>',
        nextArrow: '<i class="fa fa-angle-right slide_next"></i>'
    });
});

</script>

<div style="height:30px;border:solid 0px red;"></div>

<!-- max view for grapsh -->
<div class="max_wrapper">
<div class="max_container"></div>
</div>

<?php
include_once ('./_tail.php');
?>
