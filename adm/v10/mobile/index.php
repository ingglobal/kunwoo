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
<span class="btn_imp_list_all" style="display:none;" onClick="javascript:alert('IMP 전체 목록을 보면서 설정하는 페이지입니다.');"><i class="fa fa-arrows-alt"></i></span>
<div class="swiper-container">
    <ul class="swiper-wrapper">
    <?php
    // print_r2($member);
    $sql = "SELECT *
            FROM {$g5['member_dash_table']} AS mbd
                LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = mbd.mms_idx
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
        // print_r2($row['img']);
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
        <li class="swiper-slide mms_container <?=$img_container_style?>"
            mms_img_src="<?=$row['img']['src']?>"
            mms_name="<?=$row['mms_name']?>"
            mms_idx="<?=$row['mms_idx']?>">
            <table class="list_mms_table">
            <tr class="tr_title">
                <td colspan="2" class="td_center" title="<?=$row['mms_name']?>"><?=cut_str($row['mms_name'],18,'..')?></td>
            </tr>
            <tr>
                <td>생산기종</td>
                <td class="mms_mmi_no"><?=$row['item']['mmi_no']?></td>
            </tr>
            <tr>
                <td>일생산</td>
                <td><span class="daily_output"></span></td>
            </tr>
            <tr>
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


                            // data_mms_table mms info, if exists.
                            if( $('.data_mms_table[mms_idx='+this_mms_idx+']').length ) {
                                // console.log(this_mms_idx + ' exists.');
                                // console.log('my_output_count_success: ' + my_output_count_success);
                                // console.log('my_output_count_defect: ' + my_output_count_defect);
                                var my_mms_left = $('.data_mms_table[mms_idx='+this_mms_idx+']');

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
          <li><a href="#tabs-1">그래프</a></li>
          <li><a href="#tabs-2">주요현황</a></li>
          <li><a href="#tabs-3">설비정보</a></li>
        </ul>
        <!-- 개별보기 -->
        <div id="tabs-1">

            <iframe id="data_right" style="width:100%;" src="./iframe.empty.php" frameborder="0" scrolling="no"></iframe>
            <script>
            // iFrame높이 자동(자식창의 높이에 따라 맞춤)
            function receiveMessage(event) {
                // alert(event.data);
                // if localhost com.
                if( /localhost/.test(event.origin) || /10.150.189.151/.test(event.origin) ) {
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
        <!-- 주요현황 -->
        <div id="tabs-2">

            <table class="data_mms_table">
            <tr>
                <td>생산기종</td>
                <td class="mms_mmi_no"><?=$row['item']['mmi_no']?></td>
            </tr>
            <tr>
                <td>일생산</td>
                <td class="daily_output">0</td>
            </tr>
            <tr>
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
        <!-- 설비정보 -->
        <div id="tabs-3">

            <div class="mms_image">No image</div>

            <table class="data_mms_table">
            <tr>
                <td>예방정비</td>
                <td>0</td>
            </tr>
            <tr>
                <td>정비이력</td>
                <td>0</td>
            </tr>
            <tr>
                <td>부품재고</td>
                <td>0</td>
            </tr>
            <tr>
                <td>매뉴얼</td>
                <td>0</td>
            </tr>
            <tr>
                <td>설비도면</td>
                <td>0</td>
            </tr>
            <tr>
                <td>A/S연락처</td>
                <td>0</td>
            </tr>
            </table>
        
        </div>
    </div>
</div>


<script>
    // 상단 탭
    var tabs = $( "#tabs" ).tabs();
    // tabs.find( ".ui-tabs-nav" ).sortable({
    //     axis: "x",
    //     stop: function() {
    //         tabs.tabs( "refresh" );
    //     },
    //     show: function(e, ui) {
    //         console.log('showed');
    //     }
    // });
    $( "#tabs" ).on( "tabsactivate", function( event, ui ) {
        event.preventDefault();
        // console.log('tabsactivate.');
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

<script>
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

    // 탭은 맨 처음으로
    $( "#tabs" ).tabs( "option", "active", 0 );

    // set mms_idx attritube
    $('.data_mms_table').attr('mms_idx',my_mms_idx);

    // 이미지 변경
    var img_format = "\.(gif|jpg|jpeg|png)$";
    if(!(new RegExp(img_format, "i")).test( this_li.attr('mms_img_src') )) {
        // alert("이미지 파일이 없네.");
        this_img = $('<img src="'+g5_admin_url+'/v10/img/no_mms_image.png">');
    }
    else {
        this_img = $('<img src="'+this_li.attr('mms_img_src')+'">');
    }
    $('.mms_image').empty().append( this_img );
    
    // 생산기종
    $('.data_mms_table').find('.mms_mmi_no').text( this_li.find('.mms_mmi_no').text() );

    // 일생산
    $('.data_mms_table').find('.daily_output').text( this_li.find('.daily_output').text() );

    // 달성율
    $('.data_mms_table').find('.daily_output_rate').text( this_li.find('.daily_output_rate').text() );

    // 장비이상
    $('.data_mms_table').find('.daily_error_count').text( this_li.find('.daily_error_count').text() );

    // 예지알람
    $('.data_mms_table').find('.daily_alarm_count').text( this_li.find('.daily_alarm_count').text() );

    // 가동시간
    $('.data_mms_table').find('.daily_run_time_hour').text( this_li.find('table').data('daily_run_time_hour') );

    // 합격
    this_daily_output_success = this_li.find('table').data('daily_output_success') || 0;
    $('.data_mms_table').find('.daily_output_success').text( thousand_comma(this_daily_output_success) );

    // 불량
    this_daily_output_defect = this_li.find('table').data('daily_output_defect') || 0;
    $('.data_mms_table').find('.daily_output_defect').text( thousand_comma(this_daily_output_defect) );

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

</script>


<?php
include_once ('./_tail.php');
?>