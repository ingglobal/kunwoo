<?php
if (!defined('_GNUBOARD_')) exit;
// 카테고리 관리 상단 공통 탭 링크들입니다.

// print_r2($_REQUEST);
// exit;

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
$en_date2 = ($st_date==$en_date) ? '' : ' ~ '.$en_date; // wave(~) mark before en_date.

$member['com_idx'] = $_SESSION['ss_com_idx'] ?: $member['com_idx'];
$com = get_table_meta('company','com_idx',$member['com_idx']);
$com_idx = $com['com_idx'];
// print_r2($com);



$st_timestamp = strtotime($st_date.' 00:00:00');
$en_timestamp = strtotime($en_date.' 23:59:59');

// mmg_idx, if duplicated, mmg_idx is the last one.
foreach( $_REQUEST as $key=>$val ) {
    if( preg_match("/mmg/",$key) && $_REQUEST[$key]!='' ) {
        // echo $_REQUEST[$key].'<br>';
        $mmg_idx = $_REQUEST[$key];
    }
}
// echo $mmg_idx.' ---- mmg_idx <br>';

// In case of mms_idx. ex. 6-3, 4-5
// You should devide one eath other.
if( preg_match("/-/",$mmg_idx) ) {
    $mmg_arr = explode("-",$mmg_idx);
    $mmg_idx = $mmg_arr[0];
    $mms_idx = $mmg_arr[1];
    // echo $mmg_idx.'<br>';
    // echo $mms_idx.'<br>';
}
// echo $mms_idx.' ---- mms_idx <br>';
// exit;

// Get a mmsGroup variables : down_idxs를 뽑아두자. 라인별 합계를 위해서 미리 추출
$sql = "SELECT parent.mmg_idx
            , GROUP_CONCAT(cast(mmg.mmg_idx as char) ORDER BY mmg.mmg_left) AS down_idxs
        FROM {$g5['mms_group_table']} AS mmg, 
            {$g5['mms_group_table']} AS parent
        WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
            AND mmg.com_idx='".$com_idx."'
            AND parent.com_idx='".$com_idx."'
            AND mmg.mmg_status NOT IN ('trash','delete')
            AND parent.mmg_status NOT IN ('trash','delete')
        GROUP BY parent.mmg_idx
        ORDER BY parent.mmg_left
";
$result = sql_query($sql,1);
// echo $sql.'<br>';
for ($i=0; $row=sql_fetch_array($result); $i++) {
    // print_r2($row);
    $mmg_down_idxs[$row['mmg_idx']] = explode(",",$row['down_idxs']);
}
// print_r2($mmg_down_idxs);
// print_r2($mmg_down_idxs[$mmg_idx]); //mmg_idxs (그룹 번호들)


// mms_idxes 를 뽑아두어야 함 (이후 계산에서 해당 mms 관련 데이터들만 뽑아와야 함)
// 선택 라인이 있는 경우
if( is_array($mmg_down_idxs[$mmg_idx]) ) {
    // mmg_idxes 먼저 설정
    $mmg_array = $mmg_down_idxs[$mmg_idx];
    // print_r2($mmg_array);
    $sql_mmgs = " AND mmg_idx IN (".implode(',',$mmg_array).") ";
    // echo $sql_mmgs.'<br>';
    // mmg & parent 구조안에 있을 때 sql
    $sql_mmg_parent = " AND mmg.mmg_idx IN (".implode(',',$mmg_array).")
                        AND parent.mmg_idx IN (".implode(',',$mmg_array).")
    ";
    // echo $sql_mmg_parent;

    // mms_idxes 설정
    $sql = "SELECT GROUP_CONCAT(mms_idx) AS mmses
            FROM {$g5['mms_table']} AS mms
            WHERE mms_status NOT IN ('trash','delete') 
                AND mms.com_idx = '".$com_idx."'
                -- AND mms.mms_output_yn = 'Y'
                AND mmg_idx IN (".implode(',',$mmg_down_idxs[$mmg_idx]).")
            ORDER BY mms_idx
    ";
    // echo $sql.'<br>';
    // in case of mms_idx(설비)
    if($mms_idx) {
        $mms1['mmses'] = $mms_idx;
    }
    else {
        $mms1 = sql_fetch($sql,1);
    }
    // echo $mms1['mmses'];
    $mms_array = explode(",",$mms1['mmses']);
    // print_r2($mms_array);
    $sql_mmses = " AND mms_idx IN (".$mms1['mmses'].") ";
    // echo $sql_mmses.'<br>';

    // arm join인 경우는 mms_idx가 명확하지 않아서 재정의 필요 
    $sql_mmses1 = " AND arm.mms_idx IN (".implode(",",$mms_array).") ";
    // 게시판용 mms_idx 조건절
    $sql_mmses2 = " AND wr_2 IN (".implode(",",$mms_array).") ";
    // 지시수량용 mms_idx 조건절
    $sql_mmses3 = " AND trm_idx_line IN (".implode(",",$mms_array).") ";
}
// 선택라인이 없으면 전체에서 추출한다.
else {

}


// 지시수량 목표 먼저 추출 (아래 부분 목표 추출하는 부분에서 활용합니다.)
$sql = "SELECT bom_idx, trm_idx_line, orp_done_date, oop_count, oop_1, oop_2, oop_3, oop_4, oop_5, oop_6, oop_7, oop_8
        FROM {$g5['order_out_practice_table']} AS oop
            LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
        WHERE oop_status IN ('confirm','done')
            AND orp_start_date >= '".$st_date."'
            AND orp_done_date <= '".$en_date."'
            AND orp_done_date != '0000-00-00'
            {$sql_mmses3}
        GROUP BY bom_idx, trm_idx_line, orp_done_date, oop_count, oop_1, oop_2, oop_3, oop_4, oop_5, oop_6, oop_7, oop_8
        ORDER BY bom_idx, trm_idx_line, orp_done_date
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($j=0;$row=sql_fetch_array($rs);$j++){
    // print_r2($row);
    $date1 = preg_replace("/[ :-]/","",substr($row['orp_done_date'],0,10));   // 날짜중에서 일자 추출하여 배열키값으로!
    $date2 = preg_replace("/[ :-]/","",date("Y-m",strtotime($date1)));     // 날짜중에서 월 추출하여 배열키값으로!
    $date3 = preg_replace("/[ :-]/","",date("Y",strtotime($date1)));       // 년도만
    $week1 = date("w",strtotime($date1)); // 0 (for Sunday) through 6 (for Saturday)
    // 주차값 (1년 중 몇 주, date('w')랑 기준이 달라서 일요일인 경우 다음차수로 넘김)
    $week2 = (!$week1) ? date("W",strtotime($date1))+1 : date("W",strtotime($date1));
    // echo $week1.'(0=sunsay..) : '.$week2.'주차 : ';
    // echo date("Y-m-d",$k).'(오늘날짜) : ';
    // echo date('Y-m-d', strtotime(date("Y-m-d",$k)." -".$week1."days")).'(주첫날)<br>';
    $target['week_day'][$week2] = date('Y-m-d', strtotime(date("Y-m-d",strtotime($date1))." -".$week1."days"));  // 주차의 시작 일요일

    $target['bom'][$row['bom_idx']] += (int)$row['oop_count'];  // 제품별 목표
    $target['line'][$row['trm_idx_line']] += (int)$row['oop_count'];  // 제품별 목표
    $target['date_shift'][$date1]['1'] += (int)$row['oop_1'];  // 날짜별-1구간 목표
    $target['date_shift'][$date1]['2'] += (int)$row['oop_2'];  // 날짜별-2구간 목표
    $target['date_shift'][$date1]['3'] += (int)$row['oop_3'];  // 날짜별-3구간 목표
    $target['date_shift'][$date1]['4'] += (int)$row['oop_4'];  // 날짜별-4구간 목표
    $target['date_shift'][$date1]['5'] += (int)$row['oop_5'];  // 날짜별-5구간 목표
    $target['date_shift'][$date1]['6'] += (int)$row['oop_6'];  // 날짜별-6구간 목표
    $target['date_shift'][$date1]['7'] += (int)$row['oop_7'];  // 날짜별-7구간 목표
    $target['date_shift'][$date1]['8'] += (int)$row['oop_8'];  // 날짜별-8구간 목표
    $target['shift']['1'] += (int)$row['oop_1'];  // 1구간 목표
    $target['shift']['2'] += (int)$row['oop_2'];  // 2구간 목표
    $target['shift']['3'] += (int)$row['oop_3'];  // 3구간 목표
    $target['shift']['4'] += (int)$row['oop_4'];  // 4구간 목표
    $target['shift']['5'] += (int)$row['oop_5'];  // 5구간 목표
    $target['shift']['6'] += (int)$row['oop_6'];  // 6구간 목표
    $target['shift']['7'] += (int)$row['oop_7'];  // 7구간 목표
    $target['shift']['8'] += (int)$row['oop_8'];  // 8구간 목표
    $target['date'][$date1] += (int)$row['oop_count'];  // 날짜별 목표
    $target['week'][$week2] += (int)$row['oop_count'];  // 주차별 목표
    $target['month'][$date2] += (int)$row['oop_count'];  // 월별 목표
    $target['year'][$date3] += (int)$row['oop_count'];  // 연도별 목표
    $target['total'] += (int)$row['oop_count'];  // 전체 목표
}
// print_r2($target);
// echo '----------<br>';


// 비가동추출 get offwork time
// 전체기간 설정이 있는 경우는 마지막 부분에서 돌면서 없는 날짜 목표를 채워줍니다.
// 설비별 가동율도 계산해야 하지만 여기에서는 제외하는 걸로 합니다. (나중에 추가하자.)
$sql = "SELECT mms_idx, off_idx, off_period_type
        , off_start_time AS db_off_start_time
        , off_end_time AS db_off_end_time
        , FROM_UNIXTIME(off_start_time,'%Y-%m-%d %H:%i:%s') AS db_off_start_ymdhis
        , FROM_UNIXTIME(off_end_time,'%Y-%m-%d %H:%i:%s') AS db_off_end_ymdhis
        , GREATEST('".$st_timestamp."', off_start_time ) AS off_start_time
        , LEAST('".$en_timestamp."', off_end_time ) AS off_end_time
        , FROM_UNIXTIME( GREATEST('".$st_timestamp."', off_start_time ) ,'%Y-%m-%d %H:%i:%s') AS off_start_ymdhis
        , FROM_UNIXTIME( LEAST('".$en_timestamp."', off_end_time ) ,'%Y-%m-%d %H:%i:%s') AS off_end_ymdhis
        FROM {$g5['offwork_table']}
        WHERE com_idx = '".$_SESSION['ss_com_idx']."'
            AND off_status IN ('ok')
            AND off_end_time >= '".$st_timestamp."'
            AND off_start_time <= '".$en_timestamp."'
            AND mms_idx IN (0)
        ORDER BY mms_idx DESC, off_period_type, off_start_time
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
$byunit = 86400;
for($i=0;$row=sql_fetch_array($rs);$i++){
    // print_r2($row);
    $offwork[$i]['mms_idx'] = $row['mms_idx'];
    $offwork[$i]['start'] = date("His",$row['db_off_start_time']);
    $offwork[$i]['end'] = date("His",$row['db_off_end_time']);
    // print_r2($offwork[$i]);
    // echo '<br>----<br>';
    // echo $i.'번째  <br>';
    // 앞에서 정의한 겹치는 시간이 있으면 빼야 함, 중복 계산하지 않도록 한다.
    if( is_array($offwork) ) {
        $offworkold = $offwork;
        for($j=0;$j<sizeof($offworkold);$j++){
            // print_r2($offworkold[$j]);
            // 완전 내부 포함인 경우는 중복 제외
            if( $offwork[$i]['start'] > $offworkold[$j]['start'] && $offwork[$i]['end'] < $offworkold[$j]['end'] ) {
                unset($offwork[$i]);
            }
            // 걸쳐 있는 경우
            else if( $offwork[$i]['start'] < $offworkold[$j]['end'] && $offwork[$i]['end'] > $offworkold[$j]['start'] ) {
                if( $offwork[$i]['start'] < $offworkold[$j]['start'] ) {
                    $offwork[$i]['end'] = $offworkold[$j]['start'];
                }
                if( $offwork[$i]['end'] > $offworkold[$j]['end'] ) {
                    $offwork[$i]['start'] = $offworkold[$j]['end'];
                }
            }
        }
    }
    // echo '<br>정리<br>';
    // print_r2($offwork[$i]);
    // echo '<br>----------------------------------------<br>';
}
// print_r2($mms_date);
// print_r2($offwork);

// 하루의 전체 비가동 시간 계산
$off_total = 0;
for($j=0;$j<@sizeof($offwork);$j++){
    // print_r2($offwork[$j]);
    $off_total += strtotime($offwork[$j]['end']) - strtotime($offwork[$j]['start']);
}
// echo $off_total.'<br>';





add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/css/nice-select.css">', 0);
?>

<div class="kpi_wrapper">

<div class="title01">
	<?=$com['com_name']?>
	<span class="title_breadcrumb"></span><!-- > 제1공장 > 1라인 -->
	<span class="text01 title_date"><?=$st_date?><?=$en_date2?></span>
</div>

<!-- selections -->
<form id="form01" name="form01" class="form01" onsubmit="return sch_submit(this);" method="get">
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

function sch_submit(f){
    
    if(f.st_date.value && f.en_date.value){
        var st_d = new Date(f.st_date.value);
        var en_d = new Date(f.en_date.value);
        if(st_d.getTime() > en_d.getTime()){
            alert('검색날짜의 최종날짜를 시작날짜보다 과거날짜를 입력 할 수는 없습니다.');
            return false;
        }
    }

    return true;
}
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

});

function group_loading(com_idx, up_idx) {

	// console.log(up_idx);
	//-- 디버깅 Ajax --//
	$.ajax({
		url:g5_user_admin_ajax_url+'/mms.group.php',
		data:{"aj":"grp","com_idx":com_idx,"up_idx":up_idx},
		dataType:'json', timeout:15000, beforeSend:function(){}, success:function(res){
			//console.log(res);
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
</script>
