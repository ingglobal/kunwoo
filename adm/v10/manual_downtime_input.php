<?php
$sub_menu = "960130";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// Get the current shift info.
$sql = "SELECT shf_idx, mms_idx, shf_period_type
			, shf_range_1, shf_range_2, shf_range_3
            , shf_target_1, shf_target_2, shf_target_3
            ,shf_start_dt
            ,shf_end_dt
        FROM {$g5['shift_table']} 
        WHERE shf_status IN ('ok')
			AND com_idx = '".$_SESSION['ss_com_idx']."'
            AND shf_end_dt >= '".G5_TIME_YMDHIS."'
        ORDER BY mms_idx, shf_period_type, shf_start_dt
";
// print_r3($sql);
$rs = sql_query($sql,1);
$idx = 0;
$mmses = array();
for($i=0;$row=sql_fetch_array($rs);$i++) {
    //    print_r2($row);
    // print_r3('설비: '.$row['mms_idx'].'--------------------------');

	// 전체기간 설정일 때는 동일설비값이 있으면 통과, 중복 계산하지 않도록 한다.
	if( $row['shf_period_type'] && in_array($row['mms_idx'],$mmses) ) {
		continue;
	}

	$shift[$idx]['mms_idx'] = $row['mms_idx'];
    for($j=1;$j<=4;$j++) {
        $row['range'][$j] = $row['shf_range_'.$j];
        $row['target'][$j] = $row['shf_target_'.$j];
        
        // 교대 시작~종료 시간 분리 배열
        $row['shift'][$j] = explode("~",$row['range'][$j]);
        // print_r3($j.'교대: '.$row['shift'][$j][0].' ~ '.$row['shift'][$j][1]);                       // ------------------
		// 교대시간 값이 있는 경우만 추출
		if($row['shift'][$j][0] && $row['shift'][$j][1]) {
			$shift[$idx]['shift'][$j]['shf_start_dt'] = $row['shift'][$j][0];
			$shift[$idx]['shift'][$j]['shf_end_dt'] = $row['shift'][$j][1];
		}
    }

	// 설비 중복 체크를 위해서 저장해둔다.
	$mmses[] = $row['mms_idx'];
	$idx++;
}
// print_r3($mmses);
// print_r3($shift);
if(!$mmses[0]) {
	$mmses[] = 0;
}

// 설비별 비가동항목 추출
$sql = "SELECT *
		FROM {$g5['mms_status_table']}
		WHERE mst_status IN ('ok')
			AND mms_idx IN (".implode(',',$mmses).")
			AND mst_type = 'offwork'
";
// print_r3($sql);
$rs = sql_query($sql,1);
for($j=0;$row=sql_fetch_array($rs);$j++) {
	// print_r3($row);
	// 하단에 사용할 변수를 미리 생성
	$mms_status[$row['mms_idx']][$row['mst_idx']] = $row['mst_name'];
	$mms_options[$row['mms_idx']] .= '<option value="'.$row['mst_idx'].'">'.$row['mst_name'].'</option>';
}
// print_r3($mms_status);



// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$dta[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'입력':'수정'; 
$g5['title'] = '비가동정보 '.$html_title;
// include_once('./_top_menu_data.php');
include_once ('./_head.php');
// echo $g5['container_sub_title'];
?>
<style>
.div_shift_info {margin:40px 0 15px;padding:0 5px;position:relative;}
.div_shift_info .datetime {font-weight:bold;font-size:2em;color:#0099d6;}
.div_shift_info .btns {position:absolute;top:10px;right:10px;}
.div_shift_info .btns a {font-size:1.2em;}
.th_mms_name {font-size:1.3em;}
.th_shift {margin-top:4px;font-weight:normal;}
.th_shift .shift {color:fff;}
.th_shift .time {color:#818181;}
.td_mms, .td_input {vertical-align:top;}
.td_mms, .td_input .td_shift_text {margin-bottom:2px;}
.td_inputbox {margin-bottom:4px;margin-top:15px;}
.td_inputbox .item_input{width:100%;}
.item_each{margin-top:5px;}
.item_each_add{display:inline-block;cursor:pointer;}
.item_each_add:hover{color:#ff9595;}
.select_item {height:25px;margin-right:5px;}
.td_inputbox .item_output{margin-right:10px;}
.item_output b {color:#04b92c;}
.input_date {width:130px !important;height:25px;line-height:25px;background:#2a3241 !important;color:#bbb;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="com_idx" value="<?=$_SESSION['ss_com_idx']?>">
<input type="hidden" name="token" value="<?=$_SESSION['ss_com_idx']?>">

<div class="local_desc01 local_desc" style="display:no ne;">
	<?php
	$input_start_time = G5_SERVER_TIME-$g5['setting']['set_downtime_input_time']*3600;
	$input_start_dt = date("Y-m-d H:i:s",$input_start_time);
	$input_end_time = G5_SERVER_TIME;
	$input_end_dt = date("Y-m-d H:i:s",$input_end_time);

	$st_date = datetime_split('date',$input_start_dt);
	$st_hour = datetime_split('hour',$input_start_dt);
	$st_minute = datetime_split('minute',$input_start_dt);
	$st_second = datetime_split('second',$input_start_dt);

	$en_date = datetime_split('date',$input_start_dt);
	$en_hour = datetime_split('hour',$input_start_dt);
	$en_minute = datetime_split('minute',$input_start_dt);
	$en_second = datetime_split('second',$input_start_dt);

	
	$hor_option = '';
	$min_option = '';
	$sec_option = '';
	for($k=0;$k<60;$k++){
		$kstr = (strlen($k) == 1) ? "0".$k : (String)$k;
		$hor_selected = ($kstr == $st_hour) ? ' selected="selected"' : '';
		$min_selected = ($kstr == $st_minute) ? ' selected="selected"' : '';
		$sec_selected = ($kstr == $st_second) ? ' selected="selected"' : '';
		if($k < 24){
			$hor_option .= '<option value="'.$kstr.'"'.$hor_selected.'>'.$kstr.'</option>';
		}
		$min_option .= '<option value="'.$kstr.'"'.$min_selected.'>'.$kstr.'</option>';
		$sec_option .= '<option value="'.$kstr.'"'.$sec_selected.'>'.$kstr.'</option>';
	}

	function datetime_split($d='date',$datetime='0000-00-00 00:00:00'){
		$full_arr = explode(' ',$datetime);
		$date = $full_arr[0];
		$time = explode(':',$full_arr[1]);
		$hour = $time[0];
		$minute = $time[1];
		$second = $time[2];
		if($d == 'date') return $date;
		else if($d == 'hour') return $hour;
		else if($d == 'minute') return $minute;
		else if($d == 'second') return $second;
		else return $date;
	}
	?>
    <p>현 시점 기준 <?=$g5['setting']['set_downtime_input_time']?>시간 안에 발생한 비가동 정보를 입력합니다.
       입력 범위: <b><?=$input_start_dt?></b> ~ <b><?=$input_end_dt?></b>
	</p>
    <p>과거 정보를 수정하려면 관리자에게 문의해 주시기 바랍니다.</p>
</div>

<div class="div_shift_info">
    <div class="datetime"></div>
</div>

<script>
//1초마다 함수 갱신
function realtimeClock() {
  $('.datetime').text( getTimeStamp() );
  setTimeout("realtimeClock()", 1000);
}
realtimeClock(); 
 
function getTimeStamp() { // 24시간제
	var date = new Date();
 
    //년-월-일 시:분:초
	var f_date =
		leadingZeros(date.getFullYear(), 4) + '-' +
		leadingZeros(date.getMonth() + 1, 2) + '-' +
		leadingZeros(date.getDate(), 2) + ' ' +
		leadingZeros(date.getHours(), 2) + ':' +
		leadingZeros(date.getMinutes(), 2) + ':' +
		leadingZeros(date.getSeconds(), 2);

	return f_date;
}
 
//숫자 두자리 ex) 1이면 01 앞에 0을 붙임
function leadingZeros(date, digits) {
  var zero = '';
  date = date.toString();
 
  if (date.length < digits) {
    for (i = 0; i < digits - date.length; i++)
      zero += '0';
  }
  return zero + date;
}
</script>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:25%;">
		<col style="width:85%;">
	</colgroup>
	<tbody>
    <?php
	// 기준시간 = 현재시간 - 2시간(환경설정시간) / 기준시간을 비교하여 입력해야 할 교대시간을 찾는다.
	$t0 = G5_SERVER_TIME - 3600*$g5['setting']['set_downtime_input_time'];
	$current_dt = date("His",$t0);	// (시간)숫자로만
	$current_dt = sprintf("%06d",preg_replace("/:/","",G5_TIME_HIS));	// (시간)숫자로만
	// echo $mms_name[$i].'현재시각: '. G5_TIME_YMDHIS.'<br>';
	// echo '기준시간: '.date("Y-m-d H:i:s",$t0).'<br>';
	// echo 'Curent dt: '.$current_dt.'<br>+++++++++++++++++++++++++++<br>';

	for($i=0;$i<sizeof($shift);$i++) {
        // echo $shift[$i]['mms_idx'].' ========= <br>';
		// 설비명
		$mms_name[$i] = '<div class="th_mms_name">'.$g5['mms'][$shift[$i]['mms_idx']]['mms_name'].'</div>';
        // 1교대 ~ 4교대
        for($j=1;$j<=sizeof($shift[$i]['shift']);$j++) {
			
			// 시간 범위 디폴트 추출해 두고
			$start_dt[$j] = $shift[$i]['shift'][$j]['shf_start_dt'];
			$end_dt[$j] = $shift[$i]['shift'][$j]['shf_end_dt'];
			$shift_dt[$j] = $start_dt[$j].'~'.$end_dt[$j];
			// 좌변에 교대 표현할 텍스트 뽑아두고..
			$mms_shift[$i] .= '<div><span class="shift">'.$j.'교대</span> <span class="time">'.$shift_dt[$j].'</span></div>';

        }
		// tr starts here =============================================

		echo '
		<tr>
		<th class="td_mms" scope="row">'.$mms_name[$i].'<div class="th_shift">'.$mms_shift[$i].'</div></th>
		<td class="td_input">
		';
			// print_r2($shift_time[$shift_target[$i]]);
			// echo $shift_target[$i].'교대 범위: '.date("Y-m-d H:i:s",$shift_time[$shift_target[$i]]['start']).'~'.date("Y-m-d H:i:s",$shift_time[$shift_target[$i]]['end']).'<br>';
			echo '<div class="td_shift_text">입력범위: <span><b>'.$input_start_dt.'</b> ~ <b>'.$input_end_dt.'</b></div>';
			// echo $shift[$i]['mms_idx'].'<br>';

			echo '<div class="td_inputbox" item="'.$shift[$i]['mms_idx'].'">'
					.'<input type="hidden" name="chk[]" value="'.$i.'">'
					.'<input type="hidden" name="mms_idx['.$i.']" value="'.$shift[$i]['mms_idx'].'">';

				// 비가동 항목명 표기 ------------------
				$sql = "SELECT dta_idx, mms_idx, mst_idx, dta_start_dt, dta_end_dt
							, FROM_UNIXTIME(dta_start_dt, '%Y-%m-%d %H:%i:%s') AS dta_start_ymdhis
							, FROM_UNIXTIME(dta_end_dt, '%Y-%m-%d %H:%i:%s') AS dta_end_ymdhis
						FROM {$g5['data_downtime_table']}
						WHERE mms_idx = '".$shift[$i]['mms_idx']."'
							AND dta_start_dt >= ".(G5_SERVER_TIME-$g5['setting']['set_downtime_input_time']*3600)."
				";
				// echo $sql.'<br>';
				$rs = sql_query($sql,1);
				for($j=0;$row=sql_fetch_array($rs);$j++) {
					// print_r2($row);
					echo '<div class="item_each">'.$mms_status[$shift[$i]['mms_idx']][$row['mst_idx']].':'
							.' <input type="hidden" name="dta_idx['.$i.'][]" class="frm_input input_date" value="'.$row['dta_idx'].'">'
							.' <input type="hidden" name="mst_idx['.$i.'][]" class="frm_input input_date" value="'.$row['mst_idx'].'">'
							.' <input name="dta_start_ymdhis['.$i.'][]" class="frm_input input_date" value="'.$row['dta_start_ymdhis'].'">'
							.'~ <input name="dta_end_ymdhis['.$i.'][]" class="frm_input input_date" value="'.$row['dta_end_ymdhis'].'"></div>';
				}
				// //비가동 항목명 표기 ------------------

			// 추가하기 버튼
			echo '<div class="item_each_add">추가...</div>';
			// select box
			$mms_select[$i][$j] = '<select name="mst_idx['.$i.'][]" class="select_item"><option value="">비가동 항목 선택</option>'.$mms_options[$shift[$i]['mms_idx']].'</select>';
			echo '<div class="item_each" style="display:none;">'.$mms_select[$i][$j]
					.' <input type="hidden" name="dta_idx['.$i.'][]" class="frm_input input_date" value="">'
					.' <input name="dta_start_ymd['.$i.'][]" class="frm_input input_date st_date" value="'.$st_date.'" style="width:80px !important;">'
					.' <select name="dta_start_h['.$i.'][]" class="select_item">'.$hor_option.'</select>'
					.' <select name="dta_start_m['.$i.'][]" class="select_item">'.$min_option.'</select>'
					.' <select name="dta_start_s['.$i.'][]" class="select_item">'.$sec_option.'</select>'
					.'&nbsp;&nbsp;~&nbsp;&nbsp;&nbsp;<input name="dta_end_ymd['.$i.'][]" class="frm_input input_date en_date" value="'.$st_date.'" style="width:80px !important;">'
					.' <select name="dta_end_h['.$i.'][]" class="select_item">'.$hor_option.'</select>'
					.' <select name="dta_end_m['.$i.'][]" class="select_item">'.$min_option.'</select>'
					.' <select name="dta_end_s['.$i.'][]" class="select_item">'.$sec_option.'</select></div>';

			echo '</div>';

		echo '
		</td>
		</tr>
		';
		// tr ends here ===============================================
    }
    ?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {

	// 추가버튼 클릭
	$(document).on('click','.item_each_add',function(e){
		e.preventDefault();
		date_event_off();
		//$(".input_date").datepicker("option","disabled",true);
		// 설정값 번호를 추출
		// console.log( $(this).closest('.td_inputbox').find('.item_each').length );
		var idx_last = $(this).closest('.td_inputbox').find('.item_each').length;

		var item_dom = $(this).next('div').clone();
		// console.log(item_dom.html());
		item_dom.insertBefore( $(this) ).removeAttr('style');
		
		date_event_on();
	});

	

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

function date_event_off(){
	$(".st_date").each(function(){
		//console.log($(this).attr('name'));
		$('input[name="'+$(this).attr('name')+'"]').datepicker("destroy");
	});
	$(".en_date").each(function(){
		//console.log($(this).attr('name'));
		$('input[name="'+$(this).attr('name')+'"]').datepicker("destroy");
	});
}

function date_event_on(){
	$(".st_date").each(function(){
		//console.log($(this).attr('name'));
		$('input[name="'+$(this).attr('name')+'"]').datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
	});
	$(".en_date").each(function(){
		//console.log($(this).attr('name'));
		$('input[name="'+$(this).attr('name')+'"]').datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
	});
}

function form01_submit(f) {
	$('.item_each[style="display:none;"]').remove();
    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
