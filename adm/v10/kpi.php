<?php
$sub_menu = "955400";
include_once('./_common.php');

include_once(G5_USER_ADMIN_PATH.'/lib/latest10.lib.php');
$page_key = ($sub_page) ? 'kpi_'.$sub_page : 'kpi';
foreach($menu as $mk => $mv){
    foreach($mv as $mv_k => $mv_v){
        if(in_array($page_key,$mv_v)) {
            $sub_menu = $mv_v[0];
            break;
        }
    }
}

auth_check($auth[$sub_menu],"r");

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
$en_date2 = ($st_date==$en_date) ? '' : ' ~ '.$en_date; // wave(~) mark before en_date.

$member['com_idx'] = $_SESSION['ss_com_idx'] ?: $member['com_idx'];
$com = get_table_meta('company','com_idx',$member['com_idx']);
$com_idx = $com['com_idx'];

// print_r3($_SESSION['ss_com_idx']);
if($_SESSION['ss_com_idx']&&$member['mb_level']>=8) {
    $com = get_table_meta('company','com_idx',$_SESSION['ss_com_idx']);
    // print_r2($com);
    $com_name = $com['com_name'] ? ' ('.$com['com_name'].')' : '';
}

$head_page_path = (G5_IS_MOBILE) ?  G5_USER_ADMIN_MOBILE_PATH.'/kpi.head.php' : G5_USER_ADMIN_PATH.'/kpi.head.php';


//$sub_page변수가 잘못 넘어왔을때
if($sub_page && !is_file(G5_USER_ADMIN_PATH.'/kpi.'.$sub_page.'.php')) {
    alert('존재하지 않는 페이지 입니다.');
} else if($sub_page && is_file(G5_USER_ADMIN_PATH.'/kpi.'.$sub_page.'.php')) {
    ;
} else {
    $sub_page = 'main';
}

$ttl_arr = array('main'=>'종합','output'=>'생산','alarm'=>'알람','offwork'=>'비가동','predict'=>'예지','quality'=>'품질','maintain'=>'정비및재고');
$g5['title'] = 'KPI '.$ttl_arr[$sub_page].' 보고서';
//include_once('./_top_menu_default.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

// mmg_idx, if duplicated, mmg_idx is the last one.
while( list($key, $val) = each($_REQUEST) ) {
    if( preg_match("/mmg/",$key) && $_REQUEST[$key]!='' ) {
        // echo $_REQUEST[$key].'<br>';
        $mmg_idx = $_REQUEST[$key];
    }
}
// echo $mmg_idx.'<br>';

// In case of mms_idx. ex. 6-3, 4-5
// You should devide one eath other.
if( preg_match("/-/",$mmg_idx) ) {
    $mmg_arr = explode("-",$mmg_idx);
    $mmg_idx = $mmg_arr[0];
    $mms_idx = $mmg_arr[1];
    // echo $mmg_idx.'<br>';
    // echo $mms_idx.'<br>';
}
// exit;

// down_idxs를 뽑아두자. 라인별 합계를 위해서 미리 추출
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
}
// 선택라인이 없으면 전체에서 추출한다.
else {

}



// 교대별 기종별 목표 먼저 추출 (아래 부분 목표 추출하는 부분에서 활용합니다.)
$sql = "SELECT shf_idx, sig_shf_no, mmi_no, sig_item_target
        FROM {$g5['shift_item_goal_table']} AS sig 
            LEFT JOIN {$g5['mms_item_table']} AS mmi ON mmi.mmi_idx = sig.mmi_idx
        WHERE (1)
            {$sql_mmses}
        ORDER BY shf_idx, sig_shf_no 
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($j=0;$row=sql_fetch_array($rs);$j++){
    // print_r2($row1);
    $target['shift_no_mmi'][$row['shf_idx']][$row['sig_shf_no']][$row['mmi_no']] += $row['sig_item_target'];    // 교대별 기종별 목표
}
// print_r2($target['shift_mmi']);
// echo '----------<br>';


// 목표추출 get target fetch
// 전체기간 설정이 있는 경우는 마지막 부분에서 돌면서 없는 날짜 목표를 채워줍니다.
$sql = "SELECT mms_idx, shf_idx, shf_period_type
        , (shf_target_1+shf_target_2+shf_target_3) AS shf_target_sum
        , shf_target_1
        , shf_target_2
        , shf_target_3
        , shf_start_dt AS db_shf_start_dt
        , shf_end_dt AS db_shf_end_dt
        , GREATEST('".$st_date." 00:00:00', shf_start_dt ) AS shf_start_dt
        , LEAST('".$en_date." 23:59:59', shf_end_dt ) AS shf_end_dt
        FROM {$g5['shift_table']}
        WHERE com_idx = '".$com['com_idx']."'
            AND shf_status NOT IN ('trash','delete')
            AND shf_end_dt >= '".$st_date."'
            AND shf_start_dt <= '".$en_date."'
            {$sql_mmses}
        ORDER BY mms_idx, shf_period_type, shf_start_dt
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
$byunit = 86400;
for($i=0;$row=sql_fetch_array($rs);$i++){
    $row['mmg_idx'] = $g5['mms'][$row['mms_idx']]['mmg_idx'];
    // print_r2($row);

    // 날짜범위를 for 돌면서 배열변수 생성
    $ts1 = strtotime(substr($row['shf_start_dt'],0,10));    // 시작 timestamp
    $ts2 = strtotime(substr($row['shf_end_dt'],0,10));    // 종료 timestamp
    // 종료일시가 오전인 경우는 전날로 바꾸어서 중복이 생기지 않도록 처리한다.
    if(substr($row['shf_end_dt'],11,2) < 12) {
        $ts2 = strtotime(substr($row['shf_end_dt'],0,10))-86400;
    }
    // echo date("Y-m-d",$ts1).'~'.date("Y-m-d",$ts2).'<br>';
    for($k=$ts1;$k<=$ts2;$k+=$byunit) {
        $date1 = preg_replace("/[ :-]/","",date("Y-m-d",$k));   // 날짜중에서 일자 추출하여 배열키값으로!

        // 전체기간 설정일 때는 동일설비, 같은 날짜값이 있으면 통과, 중복 계산하지 않도록 한다.
        if( $row['shf_period_type'] && $mms_date[$row['mms_idx']][$date1] ) {
            continue;
        }

        $date2 = preg_replace("/[ :-]/","",date("Y-m",$k));     // 날짜중에서 월 추출하여 배열키값으로!
        $date3 = preg_replace("/[ :-]/","",date("Y",$k));       // 년도만
        $week1 = date("w",$k); // 0 (for Sunday) through 6 (for Saturday)
        // 주차값 (1년 중 몇 주, date('w')랑 기준이 달라서 일요일인 경우 다음차수로 넘김)
        $week2 = (!$week1) ? date("W",$k)+1 : date("W",$k);
        // echo $week1.'(0=sunsay..) : '.$week2.'주차 : ';
        // echo date("Y-m-d",$k).'(오늘날짜) : ';
        // echo date('Y-m-d', strtotime(date("Y-m-d",$k)." -".$week1."days")).'(주첫날)<br>';
        $target['week_day'][$week2] = date('Y-m-d', strtotime(date("Y-m-d",$k)." -".$week1."days"));  // 주차의 시작 일요일

        $target['date'][$date1] += $row['shf_target_sum'];  // 날짜별 목표
        $target['week'][$week2] += $row['shf_target_sum'];  // 주차별 목표
        $target['month'][$date2] += $row['shf_target_sum'];  // 월별 목표
        $target['year'][$date3] += $row['shf_target_sum'];  // 연도별 목표
        $target['mms'][$row['mms_idx']] += $row['shf_target_sum'];  // 설비별 목표
        $target['mmg'][$row['mmg_idx']] += $row['shf_target_sum'];  // 그룹별 목표
        $target['total'] += $row['shf_target_sum'];  // 전체 목표
        // 날짜별 교대별 목표
        for($j=1;$j<4;$j++) {
            // echo $row['shf_target_'.$j].'<br>';
            $target['date_shift'][$date1][$j] += $row['shf_target_'.$j];    // 날짜별 교대별 목표
            $target['shift'][$j] += $row['shf_target_'.$j];    // 교대별 목표만
            $target['mms_shift'][$row['mms_idx']][$j] += $row['shf_target_'.$j];    // 설비별 교대별 목표만
            // 날짜별 교대별 목표. $target['shift_no_mmi'][shf_idx][shf_no][mmi_no] = 200 과 같은 구조로 되어 있음
            if( is_array($target['shift_no_mmi'][$row['shf_idx']]) ) {
                if(is_array($target['shift_no_mmi'][$row['shf_idx']][$j])) {
                    // $j=교대번호
                    foreach($target['shift_no_mmi'][$row['shf_idx']][$j] as $k1=>$v1) {
                        // k1=기종번호, $v1=목표값
                        $target['mms_mmi'][$row['mms_idx']][$k1] += $v1;    // 설비별 기종목표
                    }
                }
            }
        }
        $mms_date[$row['mms_idx']][$date1] = 1; // 중복 체크를 위해서 배열 생성해 둠
        // echo '------<br>';
    }
    // echo '<br>--------------<br>';
}
// print_r2($mms_date);
// print_r2($target);


// 설비가동율 추출
$sql = "SELECT (CASE WHEN n='1' THEN CONCAT(mms_idx) ELSE 'total' END) AS item_name
            , mms_idx
            , SUM(dta_value_sum) AS dta_value_sum
            , COUNT(mms_idx) AS mms_count
        FROM
        (
            SELECT
                mms_idx
                , SUM(dta_value) AS dta_value_sum
            FROM g5_1_data_run_sum
            WHERE dta_date >= '".$st_date."' AND dta_date <= '".$en_date."'
                AND com_idx='".$com_idx."'
                {$sql_mmses}
            GROUP BY mms_idx
            ORDER BY mms_idx

        ) AS db1, g5_5_tally AS db_no
        WHERE n <= 2
        GROUP BY item_name
        ORDER BY n DESC, item_name
";
// echo $sql.'<br>';
$result = sql_query($sql,1);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    // print_r2($row);
    // total 부분만 가지고 와서 사용
    if($row['item_name'] == 'total') {
        $runtime_avg = $row['dta_value_sum']/$row['mms_count'];
    }
}
// echo $runtime_avg.'<br>';

// 날짜 차이 (+1을 해 줘야 함)
$sql = " SELECT TIMESTAMPDIFF(day,'".$st_date."','".$en_date."')+1 AS days ";
$days = sql_fetch($sql,1);
// echo $days['days'].'<br>';
// echo $days['days'].'<br>';
$run_rate = ($days['days']*86400 != 0) ? $runtime_avg / ($days['days']*86400) * 100 : 0;
// echo $run_rate.'<br>';

add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/css/nice-select.css">', 1);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/multipurpose_tabcontent/css/style.css">', 0);
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/multipurpose_tabcontent/js/jquery.multipurpose_tabcontent.js"></script>', 1);
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/function.date.js"></script>', 0);

add_stylesheet('<link rel="stylesheet" href="'.((G5_IS_MOBILE) ? G5_USER_ADMIN_MOBILE_URL : G5_USER_ADMIN_URL).'/css/kpi1.css">', 0);
echo '<div id="report_wrapper">'.PHP_EOL;
?>
<style>
.td_graph {line-height:14px;}
</style>
<?php
if(G5_IS_MOBILE){
    $mo_file_path = G5_USER_ADMIN_MOBILE_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($mo_file_path)){
        @include_once($mo_file_path);
    }
} else {
    $pc_file_path = G5_USER_ADMIN_PATH.'/'.$g5['file_name'].'.php';
    if(is_file($pc_file_path)){
        @include_once($pc_file_path);
    }
}
echo '</div>'.PHP_EOL;//--//report_wrapper
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_URL.'/js/slick-1.8.1/slick/slick-theme.css">', 0);
add_javascript('<script src="'.G5_USER_URL.'/js/slick-1.8.1/slick/slick.min.js"></script>', 10);
?>
<script>
$(function(e){
    $('select[name^=mmg]').niceSelect();
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
				//$('#mmg'+mmg_depth).after(create_dom);
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
	$('.tab_list li').eq(<?=$tab_idx?>).trigger('click');

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
	console.log(data_serialized);
	// iframe loading.
	display_loading('show');
	frame_file = (tab_idx==0) ? 'report' : 'merp';
	$('#frame_'+frame_file).attr('src', './kpi.'+frame_file+'.php?'+data_serialized);
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
