<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'data_output';
$g5_table_name = $g5[$table_name.'_table'];
$pre = 'dta';
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명

$g5['title'] = '생산데이터';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// Get all the mms_idx values to make them optionf for selection.
$sql2 = "   SELECT mms_idx, mms_name
            FROM {$g5['mms_table']}
            WHERE com_idx = '".$_SESSION['ss_com_idx']."'
            ORDER BY mms_idx       
";
// echo $sql2.'<br>';
$result2 = sql_query($sql2,1);
for ($i=0; $row2=sql_fetch_array($result2); $i++) {
    // print_r2($row2);
    $mms[$row2['mms_idx']] = $row2['mms_name'];
	$mmses[] = $row2['mms_idx'];
}


// 측정이 너무 커서 쪼개서 검색
$sql = "SELECT table_name, table_rows, auto_increment
            , SUBSTRING_INDEX (SUBSTRING_INDEX(table_name,'_',-1), '_', 1) AS mms_idx
        FROM Information_schema.tables
        WHERE TABLE_SCHEMA = '".G5_MYSQL_DB."'
            AND TABLE_NAME REGEXP 'g5_1_data_output_[0-9]+$'
        ORDER BY convert(mms_idx, decimal)
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // echo ($i+1).'<br>';
    $row['ar'] = explode("_",$row['table_name']);
    // print_r2($row);
    // print_r2($row['ar']);
    // 해당 업체 것만 추출, 아니면 통과
    if($mms[$row['ar'][4]]) {
        // echo $mms[$row['ar'][4]].' / ';
        $row_com_total += $row['table_rows'];
    }
    $row_total += $row['table_rows'];
}
// echo $row_total.'<br>';
// echo $row_com_total.'<br>';


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


// 쪼개서 검색
$sql = "SELECT table_name, table_rows, auto_increment
            , SUBSTRING_INDEX (SUBSTRING_INDEX(table_name,'_',-3), '_', 1) AS mms_idx
            , SUBSTRING_INDEX (SUBSTRING_INDEX(table_name,'_',-2), '_', 1) AS dta_type
            , SUBSTRING_INDEX (SUBSTRING_INDEX(table_name,'_',-1), '_', 1) AS dta_no
        FROM Information_schema.tables
        WHERE TABLE_SCHEMA = '".G5_MYSQL_DB."'
            AND TABLE_NAME REGEXP 'g5_1_data_output_[0-9]{1,4}$'
        ORDER BY convert(mms_idx, decimal), convert(dta_type, decimal), convert(dta_no, decimal)
";
// echo $sql.'<br>';
$rs = sql_query($sql,1);
for($i=0;$row=sql_fetch_array($rs);$i++) {
    // echo ($i+1).'<br>';
    $row['ar'] = explode("_",$row['table_name']);
    // print_r2($row);
    // print_r2($row['ar']);
    // 해당 업체 것만 추출, 아니면 통과
    if($mms[$row['ar'][4]]) {
        // echo $mms[$row['ar'][4]].' / ';
        // echo $g5['set_data_type'][$row['ar'][5]].' / ';
        // echo $row['ar'][4].'_'.$row['ar'][5].'_'.$row['ar'][6].' (mms_idx='.$row['ar'][4].'/.dat_type='.$row['ar'][5].'/dat_no='.$row['ar'][6].')<br>';
        $ser_mms_idx = ($ser_mms_idx) ?: $row['ar'][4];
    }
}
// echo $ser_mms_idx.'<br>';

if(!$ser_mms_idx)
    alert('설비정보가 존재하지 않습니다.');

// ser_mms_idx 를 만든 다음에 qstr 선언이 필요합니다.
$qstr .= '&ser_mms_idx='.$ser_mms_idx.'&st_date='.$st_date.'&en_date='.$en_date.'&st_time='.$st_time.'&en_time='.$en_time; // 추가로 확장해서 넘겨야 할 변수들

$sql_common = " FROM g5_1_data_output_".$ser_mms_idx;

$where = array();
$where[] = " (1) ";   // 디폴트 검색조건

if ($stx && $sfl) {
    switch ($sfl) {
		case ( $sfl == $pre.'_id' || $sfl == $pre.'_idx' || $sfl == 'mms_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == $pre.'_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'dta_more') :
            $where[] = " dta_value >= '".$stx."' ";
            break;
		case ($sfl == 'dta_less') :
            $where[] = " dta_value <= '".$stx."' ";
            break;
		case ($sfl == 'dta_range') :
            $stxs = explode("-",$stx);
            $where[] = " dta_value >= '".$stxs[0]."' AND dta_value <= '".$stxs[1]."' ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}


// 기간 검색
if ($st_date) {
    if ($st_time) {
        $where[] = " dta_dt >= '".strtotime($st_date.' '.$st_time)."' ";
    }
    else {
        $where[] = " dta_dt >= '".strtotime($st_date.' 00:00:00')."' ";
    }
}
if ($en_date) {
    if ($en_time) {
        $where[] = " dta_dt <= '".strtotime($en_date.' '.$en_time)."' ";
    }
    else {
        $where[] = " dta_dt <= '".strtotime($en_date.' 23:59:59')."' ";
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = $pre."_dt";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT *
		{$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
// echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, colspan, rowspan, 정렬링크여부(타이틀클릭)
$items1 = array(
    "dta_idx"=>array("번호",0,0,1)
    ,"mms_idx"=>array("설비",0,0,0)
    ,"dta_shf_no"=>array("교대",0,0,0)
    ,"dta_mmi_no"=>array("기종",0,0,0)
    ,"dta_defect"=>array("불량여부",0,0,0)
    ,"dta_defect_type"=>array("불량타입",0,0,0)
    ,"dta_value"=>array("값",0,0,0)
    ,"dta_group"=>array("입력타입",0,0,1)
    ,"dta_dt"=>array("일시",0,0,1)
    ,"dta_date"=>array("통계일시",0,0,1)
    ,"dta_reg_dt"=>array("등록일시",0,0,0)
);

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/timepicker/jquery.timepicker.css">', 0);
?>
<script type="text/javascript" src="<?=G5_USER_ADMIN_URL?>/js/timepicker/jquery.timepicker.js"></script>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">업체생산데이터</span><span class="ov_num"> <?php echo number_format($row_com_total) ?></span></span>
    <span class="btn_ov01" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>"><span class="ov_txt">전체생산데이터</span><span class="ov_num"> <?php echo number_format($row_total) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" onsubmit="return sch_submit(this);" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_mms_idx" id="ser_mms_idx">
    <?php
    // 해당 범위 안의 모든 설비를 select option으로 만들어서 선택할 수 있도록 한다.
    // Get all the mms_idx values to make them optionf for selection.
    $sql2 = "SELECT mms_idx, mms_name
            FROM {$g5['mms_table']}
            WHERE com_idx = '".$_SESSION['ss_com_idx']."'
            ORDER BY mms_idx
    ";
    // echo $sql2.'<br>';
    $result2 = sql_query($sql2,1);
    for ($i=0; $row2=sql_fetch_array($result2); $i++) {
        // print_r2($row2);
        echo '<option value="'.$row2['mms_idx'].'" '.get_selected($ser_mms_idx, $row2['mms_idx']).'>'.$row2['mms_name'].'</option>';
    }
    ?>
</select>
<script>$('select[name=ser_mms_idx]').val("<?=$ser_mms_idx?>").attr('selected','selected');</script>

<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:80px;">
<input type="text" name="st_time" value="<?=$st_time?>" id="st_time" class="frm_input" autocomplete="off" style="width:65px;" placeholder="00:00:00">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:80px;">
<input type="text" name="en_time" value="<?=$en_time?>" id="en_time" class="frm_input" autocomplete="off" style="width:65px;" placeholder="00:00:00">

<select name="sfl" id="sfl">
    <option value="">검색항목</option>
    <?php
    $skips = array('dta_idx','mms_idx','com_idx','dta_dt','dta_reg_dt');
    if(is_array($items1)) {
        foreach($items1 as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
	<option value="dta_more"<?php echo get_selected($_GET['sfl'], "dta_more"); ?>>값이상</option>
	<option value="dta_less"<?php echo get_selected($_GET['sfl'], "dta_less"); ?>>값이하</option>
	<option value="dta_range"<?php echo get_selected($_GET['sfl'], "dta_range"); ?>>범위</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">

<div class="float_right" style="display:inline-block;">
    <a href="./<?=$fname?>_sum_list.php" class="btn btn_02">일간합계목록</a>
</div>
</form>
<script>
function sch_submit(f){
    
    if(f.st_date.value && f.en_date.value){
        var st_d = new Date(f.st_date.value+' '+f.st_time.value);
        var en_d = new Date(f.en_date.value+' '+f.en_time.value);
        if(st_d.getTime() > en_d.getTime()){
            alert('검색날짜의 최종일시를 시작일시보다 과거일시를 입력 할 수는 없습니다.');
            return false;
        }
    }

    return true;
}
</script>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="ser_mms_idx" value="<?php echo $ser_mms_idx ?>">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <?php
        $skips = array();
        if(is_array($items1)) {
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                // 정렬 링크
                if($v1[3])
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
            }
        }
        ?>
		<th scope="col" id="mb_list_mng" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">수정</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $row['com'] = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '".$row['com_idx']."' ");
        $row['mms'] = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '".$row['mms_idx']."' ");
        $row['mmi'] = sql_fetch(" SELECT mmi_name FROM {$g5['mms_item_table']} WHERE mmi_idx = '".$row['dta_mmi_no']."' ");
        
		// 수정 및 발송 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.$row[$pre.'_idx'].'" class="btn btn_03">수정</a>';

        $bg = 'bg'.($i%2);
        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row[$pre.'status'].'" tr_id="'.$row[$pre.'_idx'].'">'.PHP_EOL;
        ?>
        <td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
            <input type="hidden" name="<?=$pre?>_idx[<?php echo $i ?>]" value="<?php echo $row[$pre.'_idx'] ?>" id="<?=$pre?>_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row[$pre.'name']); ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <?php
        $skips = array();
        if(is_array($items1)) {
//            print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}

                $list[$k1] = $row[$k1];

                if(preg_match("/_price$/",$k1)) {
                    $list[$k1] = number_format($row[$k1]);
                }
                else if(preg_match("/_dt$/",$k1)) {
                    $list[$k1] = '<span class="font_size_8">'.date("Y-m-d H:i:s",$row[$k1]).'</span>';
//                    $list[$k1] = substr($row[$k1],0,10);
                }
                else if($k1=='mms_idx') {
                    // $list[$k1] = '<a href="?ser_mms_idx='.$row[$k1].'">'.$row[$k1].' <span class="font_size_8">'.$row['mms']['mms_name'].'</span></a>';
                    $list[$k1] = '<span class="font_size_8">'.$g5['mms'][$ser_mms_idx]['mms_name'].'</span>';
                }
                else if($k1=='dta_mmi_no') {
                    $list[$k1] = $row[$k1].'  <span class="font_size_8">'.cut_str($row['mmi']['mmi_name'],12,'..').'</span>';
                }
                else if($k1=='dta_defect_type') {
                    $list[$k1] = ($row['dta_defect']) ? $row[$k1].' <span class="font_size_8">'.$mms_status[$ser_mms_idx][$row[$k1]].'</span>' : '';
                }
                else if($k1=='dta_group') {
                    $list[$k1] = ($row['dta_group']!='product') ? '키인 <span class="font_size_8">'.$row[$k1].'</span>' : '';
                }
                else if($k1=='dta_date') {
                    $list[$k1] = '<span class="font_size_8">'.$row[$k1].'</span>';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
        if($member['mb_manager_yn']) {
            echo '<td class="td_mngsmall">'.$s_mod.'</td>'.PHP_EOL;
        }
        echo '</tr>'.PHP_EOL;	
	}
	if ($i == 0)
		echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="Chart" onclick="document.pressed=this.value" class="btn_02 btn" style="display:no ne;">
    <input type="submit" name="act_button" value="일괄입력" onclick="document.pressed=this.value" class="btn_02 btn">
    <input type="submit" name="act_button" value="일괄수정" onclick="document.pressed=this.value" class="btn_02 btn">
    <input type="submit" name="act_button" value="테스트입력" onclick="document.pressed=this.value" class="btn_03 btn">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <?php if(!auth_check($auth[$sub_menu],"w",1)) { ?>
    <a href="./<?=$fname?>_form.php?ser_mms_idx=<?=$ser_mms_idx?>" id="btn_add" class="btn btn_01">추가하기</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_dta_type='.$ser_dta_type.'&amp;page='); ?>

<script>
$(function(e) {
    // timepicker 설정
    $("input[name$=_time]").timepicker({
        'timeFormat': 'H:i:s',
        'step': 10
    });

    // st_date chage
    $(document).on('focusin', 'input[name=st_date]', function(){
        // console.log("Saving value: " + $(this).val());
        $(this).data('val', $(this).val());
    }).on('change','input[name=st_date]', function(){
        var prev = $(this).data('val');
        var current = $(this).val();
        // console.log("Prev value: " + prev);
        // console.log("New value: " + current);
        if(prev=='') {
            $('input[name=st_time]').val('00:00:00');
        }
    });
    // en_date chage
    $(document).on('focusin', 'input[name=en_date]', function(){
        $(this).data('val', $(this).val());
    }).on('change','input[name=en_date]', function(){
        var prev = $(this).data('val');
        if(prev=='') {
            $('input[name=en_time]').val('23:59:59');
        }
    });

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

    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            //stuff to do on mouse enter
            //console.log($(this).attr('od_id')+' mouseenter');
            //$(this).find('td').css('background','red');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
            
        },
        mouseleave: function () {
            //stuff to do on mouse leave
            //console.log($(this).attr('od_id')+' mouseleave');
            //$(this).find('td').css('background','unset');
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });

});


function form01_submit(f)
{
	if(document.pressed == "Chart") {
        self.location = './data_output_chart.php';
        return false;
	}

	if(document.pressed == "일괄입력") {
        if(confirm('하루치(1일) 데이타를 입력합니다. 창을 닫지 마세요. 입력을 시작합니다.')) {
            winDataInsert = window.open('<?=G5_USER_ADMIN_URL?>/convert/data_output1.php', "winDataInsert", "left=100,top=100,width=520,height=600,scrollbars=1");
            winDataInsert.focus();
            return false;
        }
        return false;
	}

    if(document.pressed == "일괄수정") {
		window.open('./data_output_batch.php?ser_mms_idx=<?=$ser_mms_idx?>&sfl=<?=$sfl?>&stx=<?=$stx?>');
        return false;
	}

    if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/output/form.php');
        return false;
	}

    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}
	if(document.pressed == "선택삭제") {
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		} 
	}
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
