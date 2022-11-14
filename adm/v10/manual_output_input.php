<?php
$sub_menu = "960120";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

if ($w == '') {
    $sound_only = '<strong class="sound_only">필수</strong>';
    $w_display_none = ';display:none';  // 쓰기에서 숨김
    
    $dta['com_idx'] = $_SESSION['ss_com_idx'];
    $dta['dta_group'] = 'manual';
    $dta['dta_type'] = rand(1,9);
}
else if ($w == 'u') {
    $u_display_none = ';display:none;';  // 수정에서 숨김

	$dta = get_table_meta($table_name, 'dta_idx', $dta_idx);
    if (!$dta['dta_idx'])
		alert('존재하지 않는 자료입니다.');
	$com = get_table_meta('company','com_idx',$dta['com_idx']);
	$imp = get_table_meta('imp','imp_idx',$dta['imp_idx']);
    $mms = get_table_meta('mms','mms_idx',$dta['mms_idx']);
	
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

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

// 설비별 품질항목 추출
$sql = "SELECT *
		FROM {$g5['mms_status_table']}
		WHERE mst_status IN ('ok')
			AND mms_idx IN (".implode(',',$mmses).")
			AND mst_type = 'quality'
";
// print_r3($sql);
$rs = sql_query($sql,1);
for($j=0;$row=sql_fetch_array($rs);$j++) {
	// print_r3($row);
	// 하단에 사용할 변수를 미리 생성
	$mms_status[$row['mms_idx']][$row['mst_idx']] = $row['mst_name'];
}
// print_r3($mms_status);



// Get the mmi_nos for each mms
$sql = "SELECT mms_idx, mmi_no, mmi_name
		FROM g5_1_mms_item
		WHERE mmi_status = 'ok'
		GROUP BY mms_idx, mmi_no
		ORDER BY mms_idx, mmi_no
";
// print_r3($sql);
$rs = sql_query($sql,1);
$idx = 0;
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // print_r3('설비: '.$row['mms_idx'].' - '.$row['mmi_no'].'--------------------------');
    $mms_mmi[$row['mms_idx']][] = $row['mmi_no'];
    $mmi_name[$row['mms_idx']][$row['mmi_no']] = $row['mmi_name'];
}
// print_r3($mms_mmi);
// print_r3($mmi_name);



// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_gender');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.$dta[$check_array[$i]]} = ' checked';
}

$html_title = ($w=='')?'입력':'수정'; 
$g5['title'] = '품질정보 '.$html_title;
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
.th_shift .shift {color:black;}
.th_shift .time {color:#818181;}
.td_mms, .td_input {vertical-align:top;}
.td_mms, .td_input .td_shift_text {margin-bottom:2px;}
.td_inputbox {margin-bottom:4px;margin-top:15px;}
.td_inputbox .item_input{width:100%;}
.item_each{display:inline-block;margin-right:10px;}
.item_each_add{display:inline-block;cursor:pointer;}
.item_each_add:hover{color:#ff9595;}
.td_inputbox .item_no{display:inline-block;margin-right:10px;}
.td_inputbox .item_name{margin-right:10px;}
.td_inputbox .item_output{margin-right:10px;}
.item_no b
, .item_name b {color:black;}
.item_output b {color:#04b92c;}
.input_defect {width:40px !important;height:25px;line-height:25px;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="com_idx" value="">
<input type="hidden" name="token" value="<?=$_SESSION['ss_com_idx']?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <?php
    $ex1[0] = 20;
    $ex1[1] = $ex1[0]+$g5['setting']['set_quality_input_time'];
    $ex2[0] = 7;
    $ex2[1] = $ex2[0]+$g5['setting']['set_quality_input_time'];
    ?>
    <p><?=$g5['setting']['set_quality_input_time']?>시간 시차를 가지고 교대별 품질 정보를 입력합니다.</p>
    <p>ex1) <b><?=sprintf("%02d",$ex1[0])?>시 1교대가 종료</b>된 경우 <b><?=sprintf("%02d",$ex1[1])?>시까지 1교대에 대한 품질 정보 입력이 가능</b>하며 <?=sprintf("%02d",$ex1[1])?>시 이후는 1교대의 품질 정보를 입력하는 것이 아니고 그 다음 교대에 대한 품질 정보를 입력합니다.</p>
    <p>ex2) <b><?=sprintf("%02d",$ex2[0])?>시 2교대가 종료</b>된 경우 <b><?=sprintf("%02d",$ex2[1])?>시까지 2교대에 대한 품질 정보 입력이 가능</b>하며 <?=sprintf("%02d",$ex2[1])?>시 이후는 2교대의 품질 정보를 입력하는 것이 아니고 그 다음 교대에 대한 품질 정보를 입력합니다.</p>
    <p>입력이 끝난 과거 정보는 수정할 수 없습니다. 과거 정보를 수정하려면 관리자에게 문의해 주시기 바랍니다.</p>
</div>

<div class="div_shift_info">
    <div class="datetime"></div>
    <div class="btns">
		<a href="javascript:" class="btn_open_close" flag="down">전체열기 <i class="fa fa-chevron-down"></i></a>
	</div>
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
	$t0 = G5_SERVER_TIME - 3600*$g5['setting']['set_quality_input_time'];
	$current_dt = date("His",$t0);	// (시간)숫자로만
	$current_dt = sprintf("%06d",preg_replace("/:/","",G5_TIME_HIS));	// (시간)숫자로만
	// echo $mms_name[$i].'현재시각: '. G5_TIME_YMDHIS.'<br>';
	// echo '기준시간: '.date("Y-m-d H:i:s",$t0).'<br>';
	// echo 'Curent dt: '.$current_dt.'<br>+++++++++++++++++++++++++++<br>';

	$idx = 0;	// i,k 이중 루프라서 차례대로 증가하는 idx 필요
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

			// 기준 시간이 1교대 시작 전이면 어제 날짜를 기준날짜로 변경
			if( $current_dt < sprintf("%06d",preg_replace("/:/","",$shift[$i]['shift'][1]['shf_start_dt'])) )
			{
				$date_target[$i] = date("Y-m-d",$t0-86400); // 어제 날짜로 변경
			}
			// 기준 날자는 $t0 기준
			else {
				$date_target[$i] = date("Y-m-d",$t0);
			}

			$start_dt1[$j] = sprintf("%06d",preg_replace("/:/","",$start_dt[$j])); 	// 숫자로만
			$end_dt1[$j] = sprintf("%06d",preg_replace("/:/","",$end_dt[$j]));		// 숫자로만
			// 기준 시간 범위
			$start_time[$j] = strtotime($date_target[$i].' '.$start_dt[$j]);
			$end_time[$j] = strtotime($date_target[$i].' '.$end_dt[$j]);

			// 종료일자가 24시를 넘는 경우 추가사항
			if( sprintf("%06d",preg_replace("/:/","",$end_dt[$j])) >= 240000 )
			{
				$t1[$j] = sprintf("%02d",substr($end_dt[$j],0,2)-24); // 24시간을 뺀 시간
				$t2[$j] = $t1[$j].substr($end_dt[$j],2);// 종료시간 재설정
				$end_time[$j] = strtotime($date_target[$i].' '.$t2[$j]);

				// 나의 교대시작 시간 이전이면(2교대나 3교대 같은 경우) 기준 날짜를 어제 날짜로 변경
				if( $current_dt < sprintf("%06d",preg_replace("/:/","",$shift[$i]['shift'][1]['shf_end_dt'])) )
				{
					// echo 'Yesterday.............<br>';
					// 시작 시간은 어제 날짜로 변경
					$date_target[$i] = date("Y-m-d",$t0-86400); // 어제 날짜로 변경
					$start_time[$j] = strtotime($date_target[$i].' '.$start_dt[$j]);
				}
				else {
					// echo 'Next day.............<br>';
					// 종료 시간을 내일 날짜로 변경
					$date_target[$i] = date("Y-m-d",$t0+86400); // 내일 날짜로 변경
					$end_time[$j] = strtotime($date_target[$i].' '.$t2[$j]);
				}

			}
			// echo $j.'교대 범위: '.date("Y-m-d H:i:s",$start_time[$j]).'~'.date("Y-m-d H:i:s",$end_time[$j]).'<br>';
			$shift_time[$j]['start'] = $start_time[$j];	// 교대 시작 timestamp
			$shift_time[$j]['end'] = $end_time[$j];	// 교대 종료 timestamp

			// 이제 교대값과 시간범위를 추출
			if($t0 >= $start_time[$j] && $t0 <= $end_time[$j] ) {
				// echo $j.'교대 '.$shift_dt[$j].'<br>';
				$shift_target[$i] = $j;
			}

        }
		// echo 'shift_target: '.$shift_target[$i].' <<<<============= Found... <br>';
		// echo '---------<br>';
		// tr starts here =============================================

		// 기종별 생산량 추출
		$sql = "SELECT dta_mmi_no, dta_defect_type, SUM(dta_value) AS dta_sum
				FROM g5_1_data_output_".$shift[$i]['mms_idx']."
				WHERE dta_dt >= '".$shift_time[$shift_target[$i]]['start']."'
					AND dta_dt <= '".$shift_time[$shift_target[$i]]['end']."'
				GROUP BY dta_mmi_no, dta_defect_type
		";
		// echo $sql.'<br>';
		$rs = sql_query($sql,1);
		for($j=0;$row=sql_fetch_array($rs);$j++) {
			// print_r2($row);
			// 하단에 사용할 변수를 미리 생성
			$mms_mmi_count[$shift[$i]['mms_idx']][$row['dta_mmi_no']][$row['dta_defect_type']] = $row['dta_sum'];
			$mms_mmi_count[$shift[$i]['mms_idx']][$row['dta_mmi_no']]['total'] += $row['dta_sum'];
		}
		if($j==0) {
			// 생산량이 없으면 안 보임
			$tr_display[$i] = 'none';
			$tr_class[$i] = 'tr_nothing';
		}
		// print_r2($mms_mmi_count[$shift[$i]['mms_idx']]);


		echo '
		<tr class="'.$tr_class[$i].'" style="display:'.$tr_display[$i].';">
		<th class="td_mms" scope="row">'.$mms_name[$i].'<div class="th_shift">'.$mms_shift[$i].'</div></th>
		<td class="td_input">
		';
			// print_r2($shift_time[$shift_target[$i]]);
			// echo $shift_target[$i].'교대 범위: '.date("Y-m-d H:i:s",$shift_time[$shift_target[$i]]['start']).'~'.date("Y-m-d H:i:s",$shift_time[$shift_target[$i]]['end']).'<br>';
			echo '<div class="td_shift_text"><span>'.$shift_target[$i].'교대</span> 품질 정보를 입력합니다.</div>';
			// echo $shift[$i]['mms_idx'].'<br>';

			// print_r2($mms_mmi[$shift[$i]['mms_idx']]);
			// 입력박스 생성
			for($j=0;$j<sizeof($mms_mmi[$shift[$i]['mms_idx']]);$j++) {
				// print_r2($row);

				// 전체 생산량
				$output[$i][$j] = $mms_mmi_count[$shift[$i]['mms_idx']][$mms_mmi[$shift[$i]['mms_idx']][$j]]['total'];

				// 생산량이 있으면 표기
				if($output[$i][$j]) {
					$output_count[$j] = ' <span class="item_output">생산량: <b>'.number_format($output[$i][$j]).'</b></span>';
					$item_display[$j] = 'block';
					$item_class[$j] = 'item_show';
				}
				// 생산량이 없으면 일단 숨김
				else {
					$output_count[$j] = '';
					$item_display[$j] = 'none';
					$item_class[$j] = 'item_nothing';
					// echo $mms_mmi[$shift[$i]['mms_idx']][$j].'번 기종 숨김-------------<br>';
				}

				// 항목명 표시
				$item_name[$j] = $mmi_name[$shift[$i]['mms_idx']][$mms_mmi[$shift[$i]['mms_idx']][$j]];

				$item_no = $shift[$i]['mms_idx'].'^'.$shift_target[$i].'^'.$mms_mmi[$shift[$i]['mms_idx']][$j];
				echo '<div class="td_inputbox '.$item_class[$j].'" style="display:'.$item_display[$j].'" item="'.$item_no.'">'
						.'<input type="hidden" name="chk[]" value="'.$idx.'">'
						.'<input type="hidden" name="mms_idx['.$idx.']" value="'.$shift[$i]['mms_idx'].'">'
						.'<input type="hidden" name="shift_no['.$idx.']" value="'.$shift_target[$i].'">'
						.'<input type="hidden" name="item_no['.$idx.']" value="'.$mms_mmi[$shift[$i]['mms_idx']][$j].'">'
						.'<input type="hidden" name="dta_time['.$idx.']" value="'.$shift_time[$shift_target[$i]]['end'].'">'
						.'<span class="item_no">기종번호: <b>'.$mms_mmi[$shift[$i]['mms_idx']][$j].'</b></span>'
						.'<span class="item_name">기종명: <b>'.$item_name[$j].'</b></span>'
						.$output_count[$j];

					// 품질 항목명 표기
					echo '<div class="item_input">';
					if(is_array($mms_status[$shift[$i]['mms_idx']])) {
						foreach($mms_status[$shift[$i]['mms_idx']] as $k1=>$v1) {
							$input_name = $shift[$i]['mms_idx'].'^'.$shift_target[$i].'^'.$mms_mmi[$shift[$i]['mms_idx']][$j].'^'.$k1;
							// echo $k1.' / '.$v1.'<br>';
							// 생산량
							$output2[$i][$j] = $mms_mmi_count[$shift[$i]['mms_idx']][$mms_mmi[$shift[$i]['mms_idx']][$j]][$k1];
							// echo $shift[$i]['mms_idx'].'<br>';
							// echo $mms_mmi[$shift[$i]['mms_idx']][$j].'<br>';
							// echo $k1.'<br>';
							// print_r2($mms_mmi_count);
							echo '<div class="item_each">'.$v1.': <input name="dta_item['.$idx.']['.$k1.']" class="frm_input input_defect" value="'.$output2[$i][$j].'"></div>';
							// echo '<div class="item_each">'.$v1.': <input name="'.$input_name.'" class="frm_input input_defect" value="'.$output2[$i][$j].'"></div>';
						}
					}
					// 추가하기 버튼
					echo '<div class="item_each_add"
							item="'.$item_no.'"
							output="'.$output[$i][$j].'"
							idx="'.$idx.'"
							time="'.$shift_time[$shift_target[$i]]['end'].'">추가...</div>';
					echo '</div>';


				echo '</div>';
				$idx++;
			}

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
// 추가입력 팝업
$(document).on('click','.item_each_add',function(e){
	e.preventDefault();
	var href = './manual_output_add_form.php?item='+$(this).attr('item')+'&output='+$(this).attr('output')+'&time='+$(this).attr('time')+'&idx='+$(this).attr('idx');
	winItemAdd = window.open(href,"winItemAdd","left=100,top=100,width=520,height=680");
	winItemAdd.focus();
});

// 전체열기 닫기
$(document).on('click','.btn_open_close',function(e){
	e.preventDefault();
	var this_flag = $(this).attr('flag');
	// 숨김을 표시
	if(this_flag=='down') {
		$(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
		$(this).attr('flag','up');
		$('.tr_nothing').show();
		$('.item_nothing').show();

	}
	// 다시 숨기기
	else {
		$(this).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
		$(this).attr('flag','down');
		$('.tr_nothing').hide();
		$('.item_nothing').hide();
	}

});

$(function() {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});

});

function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
