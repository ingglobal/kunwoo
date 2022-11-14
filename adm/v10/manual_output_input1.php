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
// 생산목표 추출
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
.div_shift_info {margin:40px 0 15px;padding:0 5px;}
.div_shift_info .datetime {font-weight:bold;font-size:2em;color:#0099d6;}
.th_mms_name {font-size:1.3em;}
.th_shift {margin-top:4px;font-weight:normal;}
.th_shift .shift {color:black;}
.th_shift .time {color:#818181;}
.td_mms, .td_input {vertical-align:top;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

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
    for($i=0;$i<sizeof($shift);$i++) {
        // echo $shift[$i]['mms_idx'].' ========= <br>';
		// 설비명
		$mms_name[$i] = '<div class="th_mms_name">'.$g5['mms'][$shift[$i]['mms_idx']]['mms_name'].'</div>';
        // 1교대 ~ 4교대
        for($j=1;$j<=sizeof($shift[$i]['shift']);$j++) {
			$start_dt[$j] = $shift[$i]['shift'][$j]['shf_start_dt'];
			$end_dt[$j] = $shift[$i]['shift'][$j]['shf_end_dt'];
			$shift_dt[$j] = $start_dt[$j].'~'.$end_dt[$j];
			$start_dt1[$j] = sprintf("%06d",preg_replace("/:/","",$start_dt[$j])); 	// 숫자로만
			$end_dt1[$j] = sprintf("%06d",preg_replace("/:/","",$end_dt[$j]));		// 숫자로만

			// 좌변에 교대 표현할 텍스트 뽑아두고..
			$mms_shift[$i] .= '<div><span class="shift">'.$j.'교대</span> <span class="time">'.$shift_dt[$j].'</span></div>';
			echo $mms_name[$i].'현재시각: '. G5_TIME_YMDHIS.'<br>';
			// print_r2($shift[$i]['shift'][$j]);

			// 기준시간 = 현재시간 - 2시간(환경설정시간) / 기준시간을 비교하여 입력해야 할 교대시간을 찾는다.
			// 설비별로 교대시간이 다르기 때문에 여기에서 계산을 해야 됩니다.
			$t1 = G5_SERVER_TIME - 3600*$g5['setting']['set_quality_input_time'];
			$t2 = sprintf("%06d",preg_replace("/:/","",$shift[$i]['shift'][1]['shf_start_dt']) + $g5['setting']['set_quality_input_time']*10000 );	// 현재시간과 비교를 위해서
			// 01. 일단 기본 criteria를 만들어 두고.. (시간값을 숫자로만)
			$criteria = date("His",$t1);
			// echo date("His",$t1).'<br>';
			// echo $t2.'<br>';
			// 대상 일자는 기본적으로 오늘
			$date_target[$i] = G5_TIME_YMD;
			
			// 02. 1교대 시작 시간 이전이면서 24시간이 넘는 익일 종료시간인 경우 criterai 재정의합니다.
			// echo G5_TIME_HIS.'<'.$shift[$i]['shift'][1]['shf_start_dt'].'<br>';
			// echo $end_dt[$j].'<br>';
			if(sprintf("%06d",G5_TIME_HIS)<$t2 && $end_dt[$j]>='24:00:00') {
				echo ' the next day here ====================== <br>';
				// echo $end_dt[$j].'<br>';
				$criteria += 240000;
			}
			echo 'criteria: '.$criteria.'<br>';

			echo $j.'교대 '.$start_dt1[$j].' ~ '.$end_dt1[$j].'<br>';
			// 이제 비교후 입력할 교대값이 무엇인지 찾아냅니다.
			if($criteria >= $start_dt1[$j] && $criteria <= $end_dt1[$j] ) {
				echo $j.'교대 '.$shift_dt[$j].'<br>';
				$shift_target[$i] = $j;
				$date_target[$i] = date("Y-m-d", G5_SERVER_TIME-86400);
			}

        }
		echo 'date_target: '.$date_target[$i].'<br>';
		echo '---------<br>';
		// tr starts here =============================================
		echo '
		<tr> 
		<th class="td_mms" scope="row">'.$mms_name[$i].'<div class="th_shift">'.$mms_shift[$i].'</div></th>
		<td class="td_input">
		';
		// Find out the possible inputable shift.
		// shift for the current time -2 hour, If it is exists, that is the shift you can enter.
		// if not display the message that enter a quality value is not possible.

        // 1교대 ~ 4교대
        for($j=1;$j<=sizeof($shift[$i]['shift']);$j++) {
			$start_dt[$j] = sprintf("%06d",preg_replace("/:/","",$shift[$i]['shift'][$j]['shf_start_dt']));
			$end_dt[$j] = sprintf("%06d",preg_replace("/:/","",$shift[$i]['shift'][$j]['shf_end_dt']));
			$shift_dt[$j] = $start_dt[$j].'~'.$end_dt[$j];
			echo $j.'교대 '.$shift_dt[$j].' & '.$criteria.'<br>';

			// 기준값 비교, 해당 교대값을 추출해야 합니다.
			if($start_dt[$j] >= $criteria && $end_dt[$j] >= $criteria) {
				echo $j.'교대 '.$shift_dt[$j].'<br>';
			}
    
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

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
		<col class="grid_4" style="width:15%;">
		<col style="width:35%;">
	</colgroup>
	<tbody>
	<tr>
		<th scope="row">희망도메인</th>
		<td colspan="3">
			<?php echo help("한글 도메인은 DNS 연동 시 문제 발생 가능성이 존재합니다. (해외접속도 불가함) 가능하면 <span style='color:red;'>영문 도메인</span>으로 신청하세요."); ?>
			<input type="text" name="dmn_domain1" value="<?php echo $dmn['dmn_domain1'] ?>" class="frm_input">
			<input type="text" name="dmn_domain2" value="<?php echo $dmn['dmn_domain2'] ?>" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">이름</th>
		<td>
			<input type="text" name="dmn_name" value="<?=$dmn['dmn_name']?>" class="frm_input">
		</td>
		<th scope="row">영문이름</th>
		<td>
			<input type="text" name="dmn_name_eng" value="<?=$dmn['dmn_name_eng']?>" class="frm_input">
		</td>
	</tr>
	<tr>
		<th scope="row">주소</th>
		<td colspan="3" class="td_addr_line" style="line-height:220%;">
			<label for="dmn_zip" class="sound_only">우편번호</label>
			<input type="text" name="dmn_zip" value="<?php echo $dmn['dmn_zip1'].$dmn['dmn_zip2']; ?>" id="dmn_zip" class="frm_input readonly" maxlength="6" style="width:50px;">
			<button type="button" class="btn_frmline" onclick="win_zip('form01', 'dmn_zip', 'dmn_addr1', 'dmn_addr2', 'dmn_addr3', 'dmn_addr_jibeon');">주소 검색</button><br>
			<input type="text" name="dmn_addr1" value="<?php echo $dmn['dmn_addr1'] ?>" id="dmn_addr1" class="frm_input readonly" size="40">
			<label for="dmn_addr1">기본주소</label><br>
			<input type="text" name="dmn_addr2" value="<?php echo $dmn['dmn_addr2'] ?>" id="dmn_addr2" class="frm_input" size="40">
			<label for="dmn_addr2">상세주소</label>
			<br>
			<input type="text" name="dmn_addr3" value="<?php echo $dmn['dmn_addr3'] ?>" id="dmn_addr3" class="frm_input" size="40">
			<label for="dmn_addr3">참고항목</label>
			<input type="hidden" name="dmn_addr_jibeon" value="<?php echo $dmn['dmn_addr_jibeon']; ?>"><br>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
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
