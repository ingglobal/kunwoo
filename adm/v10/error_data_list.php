<?php
$sub_menu = "919100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'data_error';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
//$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들
$manager_display = (!$member['mb_manager_yn']) ? 'none' : '';   // manager가 아니면 display:none;


$g5['title'] = '알람목록 전체조회';
// include_once('./_top_menu_data.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];


$sql_common = " FROM {$g5_table_name} AS ".$pre." "; 

$where = array();
$where[] = " ".$pre."_status NOT IN (1) ";   // 디폴트 검색조건

// com_idx 조건
if(!$member['mb_manager_yn'])
    $where[] = " com_idx IN (".$_SESSION['ss_com_idx'].") ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case ( $sfl == $pre.'_id' || $sfl == $pre.'_idx' || $sfl == 'trm_idx_category' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == $pre.'_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
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

// 설비번호 검색
if ($ser_mms_idx) {
    $where[] = " mms_idx = '".$ser_mms_idx."' ";
}


// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = $pre."_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS ".$pre.".*
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

// 넘겨줄 변수가 추가로 있어서 qstr 추가 (한글이 있으면 encoding)
$qstr1 = "&st_date=$st_date&st_time=$st_time&en_date=$en_date&en_time=$en_time&ser_mms_idx=$ser_mms_idx";
$qstr .= $qstr1;

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, colspan, rowspan, 정렬링크여부(타이틀클릭)
// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
$items1 = array(
    "dta_idx"=>array("번호",0,0,0)
    ,"mms_idx"=>array("설비명",0,0,0)
    ,"dta_code"=>array("코드",0,0,0)
    ,"trm_idx_category"=>array("분류",0,0,0)
    ,"dta_message"=>array("메시지",0,0,0)
    ,"dta_shf_no"=>array("교대",0,0,0)
    ,"dta_group"=>array("GROUP",0,0,0)
    ,"imp_idx"=>array("IMP",0,0,0)
    ,"dta_dt"=>array("일시",0,0,0)
    ,"com_idx"=>array("업체번호",0,0,0)
    ,"dta_reg_dt"=>array("등록일시",0,0,0)
);

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/timepicker/jquery.timepicker.css">', 0);
?>
<script type="text/javascript" src="<?=G5_USER_ADMIN_URL?>/js/timepicker/jquery.timepicker.js"></script>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" onsubmit="return sch_submit(this);" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:80px;">
<input type="text" name="st_time" value="<?=$st_time?>" id="st_time" class="frm_input" autocomplete="off" style="width:65px;" placeholder="00:00:00">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:80px;">
<input type="text" name="en_time" value="<?=$en_time?>" id="en_time" class="frm_input" autocomplete="off" style="width:65px;" placeholder="00:00:00">

<input type="text" name="ser_mms_idx" value="<?=$ser_mms_idx?>" id="ser_mms_idx" class="frm_input" autocomplete="off" style="width:65px;" placeholder="설비번호">
<select name="sfl" id="sfl">
    <?php
    $skips = array('dta_idx','mms_idx','com_idx','mmg_idx');
    if(is_array($items1)) {
        foreach($items1 as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
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
<input type="hidden" name="st_date" value="<?php echo $st_date ?>">
<input type="hidden" name="st_time" value="<?php echo $st_time ?>">
<input type="hidden" name="en_date" value="<?php echo $en_date ?>">
<input type="hidden" name="en_time" value="<?php echo $en_time ?>">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col" style="display:<?=$manager_display?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <?php
        $skips = (!$member['mb_manager_yn']) ? array('com_idx','dta_group','dta_reg_dt') : array();
        if(is_array($items1)) {
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                // 정렬 링크
                if($v1[3]>0)
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
            }
        }
        ?>
		<th scope="col" id="mb_list_mng" style="display:<?=$manager_display?>;">수정</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $row['com'] = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '".$row['com_idx']."' ");
        $row['mms'] = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '".$row['mms_idx']."' ");
        $row['cod'] = sql_fetch(" SELECT cod_name FROM {$g5['code_table']} WHERE mms_idx = '".$row['mms_idx']."' AND cod_group = '".$row['dta_group']."' AND cod_code = '".$row['dta_code']."' ");
        
		// 수정 및 발송 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.$row[$pre.'_idx'].'" class="btn btn_03">수정</a>';
        
        $bg = 'bg'.($i%2);
        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row[$pre.'status'].'" tr_id="'.$row[$pre.'_idx'].'">'.PHP_EOL;
        ?>
        <td class="td_chk" style="display:<?=$manager_display?>;">
            <input type="hidden" name="<?=$pre?>_idx[<?php echo $i ?>]" value="<?php echo $row[$pre.'_idx'] ?>" id="<?=$pre?>_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row[$pre.'name']); ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <?php
        $skips = (!$member['mb_manager_yn']) ? array('com_idx','dta_group','dta_reg_dt') : array();
        if(is_array($items1)) {
//            print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}

                if(preg_match("/_price$/",$k1)) {
                    $row[$k1] = number_format($row[$k1]);
                }
                else if(preg_match("/_dt$/",$k1)) {
                    $row[$k1] = '<span class="font_size_8">'.date("y-m-d H:i:s",$row[$k1]).'</span>';
//                    $row[$k1] = substr($row[$k1],0,10);
                }
                else if($k1=='mms_idx') {
                    $row[$k1] = '<a href="?ser_mms_idx='.$row[$k1].'">'.$row['mms']['mms_name'].'</a>';
                }
                else if($k1=='dta_code') {
                    $row[$k1] = '<a href="?sfl=dta_code&stx='.$row[$k1].$qstr1.'">'.$row[$k1].'</a>';
                }
                else if($k1=='com_idx') {
                    $row[$k1] = $row[$k1].'  <span class="font_size_8">'.cut_str($row['com']['com_name'],8,'..').'</span>';
                }
                else if($k1=='dta_memo') {
                    $row[$k1] = cut_str($row[$k1],10,'..');
                }
                else if($k1=='dta_group') {
                    $row[$k1] = $row[$k1].' <span class="font_size_8">'.$g5['set_data_group_value'][$row[$k1]].'</span>';
                }
                else if($k1=='dta_type') {
                    $row[$k1] = $row[$k1].' <span class="font_size_8">'.$g5['set_data_type_value'][$row[$k1]].'</span>';
                }
                else if($k1=='trm_idx_category') {
                    $row[$k1] = '<a href="?sfl=trm_idx_category&stx='.$row[$k1].$qstr1.'">'.$g5['category_name'][$row[$k1]].'</a>';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$row[$k1].'</td>';
            }
        }
        echo '<td class="td_mngsmall" style="display:'.$manager_display.'">'.$s_mod.'</td>'.PHP_EOL;
        echo '</tr>'.PHP_EOL;	
	}
	if ($i == 0)
		echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<?php if($member['mb_manager_yn']) { ?>
<div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">추가하기</a>
</div>
<?php } ?>

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
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css({'background':'#333'});
            
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
	if(document.pressed == "Chart(에러)") {
        self.location = './data_error_chart2.php';
        return false;
	}
	if(document.pressed == "Chart(예지)") {
        self.location = './data_error_chart2.php?dta_group=pre';
        return false;
	}

	if(document.pressed == "일괄입력") {
        if(confirm('하루치(1일) 데이타를 입력합니다. 창을 닫지 마세요. 입력을 시작합니다.')) {
            winDataInsert = window.open('<?=G5_USER_ADMIN_URL?>/convert/data_error1.php', "winDataInsert", "left=100,top=100,width=520,height=600,scrollbars=1");
            winDataInsert.focus();
            return false;
        }
        return false;
	}

	if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/error/form.php');
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
