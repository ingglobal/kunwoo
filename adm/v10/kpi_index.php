<?php
$sub_menu = "955500";
include_once('./_common.php');

$g5['title'] = 'KPI 보고서';
// include_once('./_top_kpi_monthly.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];

// tab_idx
$tab_idx = $tab_idx ?: 0;

// 당월, 당일
$st_ymd = date("Y-m-01",G5_SERVER_TIME);
$ym_days = date("t",G5_SERVER_TIME);
$en_ymd = date("Y-m-".$ym_days,G5_SERVER_TIME);
$today = date("Y-m-d",G5_SERVER_TIME);
$yesterday = date("Y-m-d",G5_SERVER_TIME-86400);
$tomorrow = date("Y-m-d",G5_SERVER_TIME+86400);
//echo $today.'<br>';

// st_date, en_date
$st_date = $st_date ?: date($st_ymd);
$en_date = $en_date ?: date("Y-m-d");
$en_date = date("Y-m-t", strtotime($en_date)); //매월말일 날짜로 재정의
$en_date2 = ($st_date==$en_date) ? '' : ' ~ '.$en_date; // wave(~) mark before en_date.

$member['com_idx'] = $_SESSION['ss_com_idx'] ?: $member['com_idx'];
$com = get_table_meta('company','com_idx',$member['com_idx']);
$com_idx = $com['com_idx'];
// print_r2($com);


add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/css/nice-select.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/multipurpose_tabcontent/css/style.css">', 0);
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/multipurpose_tabcontent/js/jquery.multipurpose_tabcontent.js"></script>', 0);
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/function.date.js"></script>', 0);
?>                 
<style>
</style>

<div class="kpi_wrapper">

<div class="title01">
	<?=$com['com_name']?>
	<span class="title_breadcrumb"></span><!-- > 제1공장 > 1라인 -->
	<span class="text01 title_date"><?=$st_date?><?=$en_date2?></span>
</div>

<!-- selections -->
<form id="form01" name="form01" class="form01" method="get">
	<input type="hidden" name="com_idx" value="<?=$com['com_idx']?>" class="frm_input">
	<input type="text" name="st_date" id="st_date" value="<?=$st_date?>" class="frm_input">
	<span class="text01">~</span>
	<input type="text" name="en_date" id="en_date" value="<?=$en_date?>" class="frm_input">
	<div class="text02 prev_month"><i class="fa fa-chevron-left"></i></div>
	<div class="text02 this_month" s_ymd="<?=$st_ymd?>" e_ymd="<?=$en_ymd?>">이번달</div>
	<div class="text02 next_month"><i class="fa fa-chevron-right"></i></div>
	<div class="text02 prev_day"><i class="fa fa-chevron-left"></i></div>
	<div class="text02 this_day" s_ymd="<?=$today?>" e_ymd="<?=$today?>">오늘</div>
	<div class="text02 next_day"><i class="fa fa-chevron-right"></i></div>
	<div>
		<select name="mmg0" id="mmg0">
			<option value="">전체</option>
		</select>
	</div>
	<input type="submit" class="btn_submit" value="확인">
</form>
<script>
$(function(e){
	$('select[name^=mmg]').niceSelect();
});
</script>

<div class="tab_wrapper reports">
	<ul class="tab_list">
	  	<li>M-ERP 보고서</li>
	</ul>
	<div class="content_wrapper">
	  <div class="tab_content"><!--kpi.merp.php-->
		  <iframe id="frame_merp" src="kpi.merp.php?<?=$qstr?>" frameborder="0" scrolling="no"></iframe>
	  </div>
	</div>
</div>
<script>
	$(".reports").champ();
</script>


</div> <!-- .kpi_wrapper -->


<script>
$(function(e){
	// group select change
	$(document).on('change','select[name^=mmg]',function(e) {
		// console.log( $(this).attr('id') );
		var mmg_depth = $(this).attr('id').replace('mmg','');
		var mmg_idx = $(this).val();
		// console.log( 'select tag count: '+$('select[name^=mmg]').length );
		var mmg_select_count = $('select[name^=mmg]').length;

		// 선택 select 그 다음에 있는 select는 일단 전부 제거, 앞쪽에 있는 Select를 선택할 수도 있으므로 뒤쪽 Select all remove.
		$('select[name^=mmg]').each(function(i,v){
			var this_depth = $(this).attr('id').replace('mmg','');
			if(this_depth > mmg_depth ) {
				$(this).closest('div').remove();
			}
		});

		// 선택항목이 있는 경우
		if(mmg_idx) {

			// In case of mms(설비), do not load select
			if( /-/.test( mmg_idx ) ) {
				// alert( 'mms related.' );
			}
			// Only if it is mmg group.
			else {

				group_loading(<?=$com_idx?>, mmg_idx);

				// 이제 바로 하위 div select 초기화 
				var create_depth = parseInt(mmg_depth)+1;
				var create_dom = '<div>'
									+'<select name="mmg'+create_depth+'" id="mmg'+create_depth+'">'
									+	'<option value="">전체</option>'
									+'</select>'
								+'</div>';
				$('#mmg'+mmg_depth).closest('div').after(create_dom);
				$('select[name=mmg'+create_depth+']').niceSelect();

			}

		}

	});
	// default group loading.
	group_loading(<?=$com_idx?>, 0);

	// prev Month
	$(document).on('click','.prev_month',function(e) {
		// console.log( $('#st_date').val() );
		this_day = $('#st_date').val();
		$('#st_date').val( getPrevMonthFirst( this_day ) );
		$('#en_date').val( getPrevMonthLast( this_day ) );
	});
	// next Month
	$(document).on('click','.next_month',function(e) {
		this_day = $('#st_date').val();
		$('#st_date').val( getNextMonthFirst( this_day ) );
		$('#en_date').val( getNextMonthLast( this_day ) );
	});
	// prev Day
	$(document).on('click','.prev_day',function(e) {
		this_day = $('#st_date').val();
		$('#st_date').val( getNthPrevDay( this_day, 1 ) );
		$('#en_date').val( getNthPrevDay( this_day, 1 ) );
	});
	// prev Day
	$(document).on('click','.next_day',function(e) {
		this_day = $('#st_date').val();
		$('#st_date').val( getNthNextDay( this_day, 1 ) );
		$('#en_date').val( getNthNextDay( this_day, 1 ) );
	});

	// this month, this day click
	$(document).on('click','div[s_ymd]',function(e) {
		$('#st_date').val( $(this).attr('s_ymd') );
		$('#en_date').val( $(this).attr('e_ymd') );
	});

	// form submit click
	$(document).on('click','.btn_submit',function(e) {
		e.preventDefault();

		// title breadcrumb change.
		var title_breadcrumb = '';
		$('select[name^=mmg]').each(function(i,v){
			// console.log( $(this).attr('name') );
			// console.log( $(this).find('option:selected').val() );
			// console.log( $(this).find('option:selected').text() );
			if( $(this).find('option:selected').val() != '' ) {
				// console.log( $(this).find('option:selected').text() );
				// $('.title_breadcrumb').append('<span> > '+$(this).find('option:selected').text()+'</span>');
				title_breadcrumb += ' > '+$(this).find('option:selected').text();
			}
		});
		$('.title_breadcrumb').text(title_breadcrumb);

		// date text update
		if( $('#st_date').val()==$('#en_date').val() ) {
			$('.title_date').text( $('#st_date').val() );
		}
		else {
			var st_d = new Date($('#st_date').val());
			var en_d = new Date($('#en_date').val());
			if(st_d.getTime() > en_d.getTime()){
				alert('검색날짜의 최종날짜를 시작날짜보다 과거날짜를 입력 할 수는 없습니다.');
				return false;
			}
			$('.title_date').text( $('#st_date').val() +' ~ '+ $('#en_date').val() );
		}

		// console.log( $('.tab_list li').index( $('.tab_list li.active') ) );
		idx = $('.tab_list li').index( $('.tab_list li.active') );
		frame_loading(idx);
		console.log('submit');
	});

	// tab click
	$(document).on('click','.tab_list li',function(e){
		// console.log( $('.tab_list li').index( $(this) ) );
		idx = $('.tab_list li').index( $(this) );

		frame_loading(idx);
		console.log('tab');

		// tab selection
		// frm_idx = idx + 1;
		// $(".reports").champ({active_tab :frm_idx});
	});
	// default first tap, 
	// $('.tab_list li').eq(<?=$tab_idx?>).trigger('click');
	frame_loading(0);

    $("input[name=st_date]").datepicker({
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
        onSelect: function(selectedDate){$("input[name=en_date]").datepicker('option','minDate',selectedDate);} 
    });

	$("input[name=en_date]").datepicker({
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
        onSelect:function(selectedDate){$("input[name=st_date]").datepicker('option','maxDate',selectedDate); }
    });

	// iFrame높이 자동(자식창의 높이에 따라 맞춤)
    function receiveMessage(event) {
		// if localhost com.
        if( /localhost/.test(event.origin) ) {
            $(".tab_content iframe").height(event.data + 10); 
        }
        else {
            if (event.origin !== "<?=G5_URL?>")	// 자식창의 URL 주소
            return;
            $(".tab_content iframe").height(event.data + 10); 
        }
    }
    if ('addEventListener' in window){ 
        window.addEventListener('message', receiveMessage, false); 
    }
    else if ('attachEvent' in window) { //IE
        window.attachEvent('onmessage', receiveMessage); 
    } 


});

function group_loading(com_idx, up_idx) {

	// console.log(up_idx);
	//-- 디버깅 Ajax --//
	$.ajax({
		url:g5_user_admin_ajax_url+'/mms.group.php',
		data:{"aj":"grp","com_idx":com_idx,"up_idx":up_idx},
		dataType:'json', timeout:15000, beforeSend:function(){}, success:function(res){
			// console.log(res);
			//var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
			if(res.result == true) {
				if(res.list.length) {
					$.each(res.list, function(i,v){
						// console.log('mmg_idx: '+v['mmg_idx']+', mmg_name: '+v['mmg_name']);
						// 0단계, 1단계, 2단계... 해당 단계 select 박스에 option 삽입한다.
						// console.log('selected depth: '+v['depth']);
						this_depth = v['depth'];
						$('<option value="'+ v['mmg_idx'] +'">' + v['mmg_name'] + '</option>').appendTo('#mmg'+v['depth']);
					});
					$('select[name=mmg'+this_depth+']').niceSelect('update');
				}
			}
			else {
				console.log(res.msg);
			}
		},
		error:function(xmlRequest) {
			// console.log('<?=$row['mms_name']?>(<?=$row['mms_idx']?>): error');
			console.log('Status: ' + xmlRequest.status);
			console.log('statusText: ' + xmlRequest.statusText);
			console.log('responseText: ' + xmlRequest.responseText);
		}
	});

}

function frame_loading(flag) {
	// iframe reset.
	// 기존에 있던 정보가 잠깐 비치는 게 신경쓰여서 empty.php 페이지 일단 보여주고 나서 로딩하는 걸로 합니다.
	$('.tab_content').find('iframe').attr('src', './kpi.empty.php');

	// tab_idx starts from 0, frm_idx starts from 1
	// Those two indexes are confusing.
	tab_idx = flag;
	// console.log(tab_idx);

	// form serialize
	data_serialized = $('#form01').serialize();

	// iframe loading.
	display_loading('show');
	frame_file = (tab_idx==0) ? 'merp' : 'merp';
	$('#frame_'+frame_file).attr('src', './kpi.'+frame_file+'.php?'+data_serialized);
	//console.log('./kpi.'+frame_file+'.php?'+data_serialized);
}

// 로딩 spinner 이미지 표시/비표시
function display_loading(flag) {
    var img_loading = $('<i class="fa fa-spin fa-spinner" style="position:absolute;top:80px;left:48%;font-size:4em;"></i>');
    if(flag=='show') {
        // console.log('show');
        $('.content_wrapper').append(img_loading);
    }
    else if(flag=='hide') {
        // console.log('hide');
        $('.fa-spinner').remove();
    }
}
</script>

<?php
include_once ('./_tail.php');
?>

