<?php
include_once('./_common.php');

$g5['title'] = $board['bo_subject'].' 환경설정';
include_once('./_head.php');

if(!$is_admin)
    alert('관리자만 접속할 수 있습니다.');

// bo_7=>longtext 환경설정 확장변수로 사용하기 위함
$q = sql_query( 'DESCRIBE '.$g5['board_table'] );
while($row = sql_fetch_array($q)) {
    if($row['Field']=='bo_7' && $row['Type']=='varchar(255)') {
        //echo $row['Field'].' - '.$row['Type'].'<br>';
        sql_query(" ALTER TABLE `{$g5['board_table']}` CHANGE `bo_7` `bo_7` longtext ", true);
    }
}


// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>

<section id="bo_w">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

	<div class="config_caution">
		<ol>
			<li>스케줄과 관련된 추가적인 환경설정을 하는 페이지입니다.</li>
            <li>공휴일 설정은 하단 달력에서 별도로 설정해 주시기 바랍니다.</li>
            <li>게시판 관련한 기본적인 설정들은 관리자단 게시판관리 페이지에서 설정해 주세요.</li>
		</ol>
	</div>
    
    <form name="form01" class="config_form" action="./config_form_update.php" onsubmit="return form_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    
    <div class="tbl_frm01 tbl_wrap">
        <h2>기본 환경설정</h2>
        <ul>
            <li>
                <label class="frm_label">일정상태값</label>
                <input type="text" name="bo_9" id="bo_9" value="<?php echo $board['bo_9'] ?>" style="width:85%;" required class="frm_input required" placeholder="예약상태값">
                <span class="frm_info">
                    pending=대기,ok=정상,hide=숨김,trash=삭제
                </span>                
            </li>
            <li>
                <label class="frm_label">등록 초기상태값</label>
                <input type="text" name="set_default_status" id="set_default_status" value="<?php echo $board['set_default_status'] ?>" style="width:100px;" required class="frm_input required" placeholder="예약초기상태값">
                <span class="frm_info">
                    상태값 항목값을 참고하셔서 값을 정확히 입력해 주세요. pending(대기)상태로 설정하면 완료로 바뀌기 전까지는 노출되지 않습니다.
                </span>                
            </li>
            <li>
                <label class="frm_label">제외 상태값</label>
                <input type="text" name="set_notin_status" id="set_notin_status" value="<?php echo $board['set_notin_status'] ?>" style="width:400px;" class="frm_input" placeholder="제외 상태값">
                <span class="frm_info">
                    기본적으로 모든 일정이 다 나타납니다. 제외할 상태값을 입력하면 나타나지 않습니다. 영문으로 입력하세요. ex.hide,trash,...
                </span>                
            </li>
            <li>
                <label class="frm_label">구분항목</label>
                <input type="text" name="bo_8" id="bo_8" value="<?php echo $board['bo_8'] ?>" style="width:85%;" required class="frm_input required" placeholder="예약항목 설정">
                <span class="frm_info">
                    ex) ssak=싹산악회,seokuk=서국회,ansi=안시회,jaein=재인회,jaeseo=재서골,..... 게시판의 아이디와 맞추어 주세요.
                </span>                
            </li>
            <li>
                <label class="frm_label">단위시간 설정</label>
                <input type="text" name="set_time_unit" id="set_time_unit" value="<?php echo $board['set_time_unit'] ?>" style="width:50px;" required class="frm_input required" placeholder=""> 분
                <span class="frm_info">
                    분 단위로 숫자로만 입력하세요. 30분이면 30, 1시간인 경우는 60, 2시간 단위인 경우는 120 등과 같이 입력하세요.
                </span>
            </li>
        </ul>
        
        <div class="btn_confirm write_div">
            <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?=$bo_table?>" class="btn_cancel btn">취소</a>
            <input type="submit" value="작성완료" id="btn_submit" accesskey="s" class="btn_submit btn">
        </div>
        
    </div>

    <div class="tbl_frm01">
        <h2>공휴일 설정</h2>
        <!-- 달력 시작 { -->
        <?php
        // 디폴트값 설정
        $month = ($month)? $month:date('Ym', G5_SERVER_TIME);
        $_month = substr($month,0,4).'-'.substr($month,-2);
        ?>
        <div class="calendar">
            <div class="calendar_title">
                <a href="javascript:" class="prev_month" cal_val="-1" title="이전달"><img src="https://icongr.am/feather/arrow-left-circle.svg?size=30&color=4e4e4e"></a>
                <span class="this_month"><?=$_month?></span>
                <a href="javascript:" class="next_month" cal_val="+1" title="다음달"><img src="https://icongr.am/feather/arrow-right-circle.svg?size=30&color=4e4e4e"></a>
            </div>
            <div class="div_calendar">
                <table class="table_calendar">
                <thead>
                <tr>
                    <th class="th_sunday">일</th>
                    <th>월</th>
                    <th>화</th>
                    <th>수</th>
                    <th>목</th>
                    <th>금</th>
                    <th class="th_saturday">토</th>
                </tr>
                </thead>
                <tbody><!-- 달력 리스트 --></tbody>
                </table>
            </div>
        </div>
        <!-- } 달력 종료 -->
    </div>

    </form>

</section>
<div class="div_backdrop"></div>
<div class="div_modal">
    <div class="modal_header">
        <span>2019-03-18</span> 설정
    </div>
    <div class="modal_body">
        <div class="setting_time">
            <form name="form02" id="form02" class="config_form" autocomplete="off">
            <input type="hidden" name="ymd_date" value="">
            <input type="hidden" name="type" value="time">
            <div class="tbl_frm01 tbl_wrap">
                <h2>시간 설정</h2>
                <ul>
                    <li>
                        <label class="frm_label">예약가능시간 설정</label>
                        <input type="text" name="start_time" id="start_time" value="<?php echo $board['start_time'] ?>" style="width:100px;" class="frm_input" placeholder="시작시간">
                        부터
                        <input type="text" name="end_time" id="end_time" value="<?php echo $board['end_time'] ?>" style="width:100px;" class="frm_input" placeholder="종료시간">
                        까지
                        <span class="frm_info">
                            ex) 09:00 ~ 18:00 등과 같은 범위로 설정할 수 있습니다.
                        </span>
                    </li>
                    <li>
                        <label class="frm_label">브레이크타임 설정</label>
                        <input type="text" name="break_start_time" id="break_start_time" value="<?php echo $board['break_start_time'] ?>" style="width:100px;" class="frm_input" placeholder="시작시간">
                        부터
                        <input type="text" name="break_end_time" id="break_end_time" value="<?php echo $board['break_end_time'] ?>" style="width:100px;" class="frm_input" placeholder="종료시간">
                        까지
                        <span class="frm_info">
                            점심시간과 같은 휴식시간(브레이크타임)을 설정하세요. 브레이크타임이 없으면 공란으로 그냥 두시면 됩니다.
                        </span>
                    </li>
                    <li>
                        <label for="day_apply_yn" class="frm_label">예약가능</label>
                        <label class="day_apply_yn" style="margin-bottom:0;"><input type="checkbox" name="day_apply_yn" value="1" id="day_apply_yn" <?php if($board['day_apply_yn']) echo 'checked';?>>예약을 받습니다.</label>
                        <span class="frm_info">
                            해당 날짜에만 설정되는 예약가능 설정입니다.<br>개별 설정하지 않으면 전체 설정을 따라갑니다.
                        </span>
                    </li>
                    <li>
                        <label for="setting_reset" class="frm_label">설정초기화</label>
                        <label class="setting_reset" style="margin-bottom:0;"><input type="checkbox" name="setting_reset" value="1" class="setting_reset">해당 날짜 설정을 초기화합니다.</label>
                    </li>
                </ul>
                <div class="btn_confirm write_div">
                    <a href="javascript:" class="btn_cancel btn modal_cancel">취소</a>
                    <input type="submit" value="작성완료" class="btn_submit" accesskey="s" class="btn_submit btn">
                </div>
                
            </div>
            </form>
        
        </div>
        <div class="setting_holiday">

            <form name="form03" id="form03" class="config_form" autocomplete="off">
            <input type="hidden" name="ymd_date" value="">
            <input type="hidden" name="type" value="holiday">
            <div class="tbl_frm01 tbl_wrap">
                <h2>공휴일 설정</h2>
                <ul>
                    <li>
                        <label class="frm_label">공휴일 명칭</label>
                        <input type="text" name="holiday_name" id="holiday_name" value="<?php echo $board['holiday_name'] ?>" style="width:100px;" required class="frm_input required" placeholder="공휴일 명칭">
                        <span class="frm_info">
                            
                        </span>                
                    </li>
                    <li>
                        <label class="frm_label">간단 설명</label>
                        <input type="text" name="holiday_description" id="holiday_description" value="<?php echo $board['holiday_description'] ?>" style="width:70%;" required class="frm_input required" placeholder="간단 설명">
                        <span class="frm_info">
                            마우스 오버 시 나타나는 설명입니다. (없으면 공란)
                        </span>                
                    </li>
                    <li>
                        <label for="setting_reset" class="frm_label">설정초기화</label>
                        <label class="setting_reset" style="margin-bottom:0;"><input type="checkbox" name="setting_reset" value="1" class="setting_reset">해당 날짜 설정을 초기화합니다.</label>
                    </li>
                </ul>
                <div class="btn_confirm write_div">
                    <a href="javascript:" class="btn_cancel btn modal_cancel">취소</a>
                    <input type="submit" value="작성완료" class="btn_submit" accesskey="s" class="btn_submit btn">
                </div>
                
            </div>
            </form>
    
        </div>
    </div>
    <div class="modal_footer"></div>
</div>


<script>
var g5_board_skin_url = '<?php echo $board_skin_url ?>';
var g5_board_config = 1;

$(function(e){
    // 시간설정, 공휴일설정 클릭
    $(document).on('click','.btn_set_time, .btn_set_holiday',function(e){
        //alert( $(e.target).closest('td').find('.day_no').text() );
        //alert( $(this).attr('class').replace("btn_set_","") );
        var this_date = $(e.target).closest('td').attr('td_date');
        $('.modal_header span').text( this_date );
        $('.modal_body').find('input[name=ymd_date]').val( this_date );
        var objname = $(this).attr('class').replace("btn_set_","");
        $('.modal_body > div').hide();
        $('.modal_body .setting_'+objname).show();
        
        if(objname == 'time') {
            var this_start_time = $(e.target).closest('td').attr('start_time');
            var this_end_time = $(e.target).closest('td').attr('end_time');
            var this_break_start_time = $(e.target).closest('td').attr('break_start_time');
            var this_break_end_time = $(e.target).closest('td').attr('break_end_time');
//            var this_holiday_yn = ($(e.target).closest('td').hasClass('day_disable')) ? 0:1;
            var this_holiday_checked = ($(e.target).closest('td').hasClass('day_disable')) ? false : true;
            $('#form02').find('input[name=start_time]').val( this_start_time );
            $('#form02').find('input[name=end_time]').val( this_end_time );
            $('#form02').find('input[name=break_start_time]').val( this_break_start_time );
            $('#form02').find('input[name=break_end_time]').val( this_break_end_time );
//            $('#form02').find('input[name=day_apply_yn]').val( this_holiday_yn );
//            $('#form02').find('input[name=day_apply_yn]').attr('checked',this_holiday_checked);
            document.getElementById('day_apply_yn').checked = this_holiday_checked; // checkbox native javascript
        }
        else if(objname == 'holiday') {
            var this_holiday_text = $(e.target).closest('td').find('.day_holiday_text').text();
            var this_holiday_description = $(e.target).closest('td').find('.day_holiday_text').attr('title');
            $('#form03').find('input[name=holiday_name]').val( this_holiday_text );
            $('#form03').find('input[name=holiday_description]').val( this_holiday_description );
        }
        modal_open(objname);
    });
    
    // backdrop 클릭(or ESC 버튼)시 닫힘
    $(document).on('click','.div_backdrop',function(e) { 
        modal_close();
    });    
    $(document).keyup(function(e) { 
        if(e.keyCode == 27) {
            modal_close();
        }
    });
    // backdrop을 클릭했을 때만 닫히게..
    $(document).on('click','.div_modal',function(e) { 
        e.stopPropagation();
    });    
    // 모달 취소버튼 - 창닫기
    $(document).on('click','.modal_cancel',function(e) { 
        modal_close();
    });
    

    // 작성완료 버튼 클릭
    $(document).on('click','.modal_body .btn_submit',function(e) {
        e.preventDefault();
        var target_form = $(this).closest('form');
        
        // form 설정값 serialize 
        data_serialized = target_form.serialize();
        
        //-- 디버깅 Ajax --//
        $.ajax({
            url:g5_board_skin_url+'/ajax.calendar_update.php', type:'post', data:{"w":"u1","data_serialized":data_serialized}, dataType:'json', timeout:10000,  beforeSend:function(){},
            success:function(res){
        //-- 디버깅 Ajax --//
        
        //$.getJSON(g5_board_skin_url+'/ajax.calendar_update.php',{"w":"u1","data_serialized":data_serialized},function(res){
            //alert(res.sql);
            //var items;
            //for(items in res) { alert(items +': '+ res[items]); }
            if(res.result == true) {

                get_calendar( $('.prev_month') );
                modal_close();

                // 해당 날짜 정보 변경
                //self.location.reload();
            }
            else {
                alert(res.msg);
            }
        
        //-- 디버깅 Ajax --//
        },
        error:function(xmlRequest) {
            alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
                + ' \n\rresponseText: ' + xmlRequest.responseText);	
        }		
        //-- 디버깅 Ajax --//
        
        });
    });
    
});


if(typeof(modal_open)!='function') {
function modal_open(objname) {
    $('.div_backdrop').insertAfter('#ft').css('display','block');
    $('.div_backdrop').append( $('.div_modal') );
    $('.div_modal').css('display','block');
    $('.div_modal .setting_reset').attr('checked',false);
    //document.getElementByClassName('setting_reset')[0].checked = false;
}
}
if(typeof(modal_close)!='function') {
function modal_close() {
    $('.div_backdrop').css('display','none');
    $('.div_modal').css('display','none');
}
}
</script>
<script src="<?=$board_skin_url?>/calendar.js" type="text/javascript" charset="utf-8"></script>

<script>
function form_submit(f)
{

	document.getElementById("btn_submit").disabled = "disabled";

	return true;
}
</script>


<?php
include_once('./_tail.php');
?>