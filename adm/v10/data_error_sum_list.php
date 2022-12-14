<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'data_error_sum';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
$qstr .= '&ser_mms_idx='.$ser_mms_idx.'&st_date='.$st_date.'&en_date='.$en_date; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '에러데이터 일간합계';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_error = " ( SELECT dta_group,dta_code,dta_message FROM {$g5['data_error_table']}
                GROUP BY dta_code, dta_group
                HAVING dta_group = 'err' ) ";

$sql_common = " FROM {$g5_table_name} dta 
                    LEFT JOIN {$sql_error} err ON dta.dta_code = err.dta_code
"; 

$where = array();
$where[] = " (1) ";   // 디폴트 검색조건

if ($stx && $sfl) {
    switch ($sfl) {
		case ( $sfl == $pre.'_id' || $sfl == $pre.'_idx' || $sfl == 'mms_idx' ) :
            $where[] = " (dta.{$sfl} = '{$stx}') ";
            break;
		case ($sfl == $pre.'_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
        default :
            $where[] = " (dta.{$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

$where[] = " dta.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 기간 검색
if ($st_date) {
    $where[] = " dta.dta_date >= '".$st_date."' ";
}
if ($en_date) {
    $where[] = " dta.dta_date <= '".$en_date."' ";
}

// 설비번호 검색
if ($ser_mms_idx) {
    $where[] = " dta.mms_idx = '".$ser_mms_idx."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = 'dta.'.$pre."_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT dta.*
            ,err.dta_message
		{$sql_common}
		{$sql_search}
        {$sql_order}
		LIMIT {$from_record}, {$rows} 
";
//echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, colspan, rowspan, 정렬링크여부(타이틀클릭)
$items1 = array(
    "dta_idx"=>array("번호",0,0,1)
    ,"mms_idx"=>array("설비번호",0,0,0)
    ,"imp_idx"=>array("IMP",0,0,0)
    ,"dta_code"=>array("CODE",0,0,0)
    ,"dta_message"=>array("의미",0,0,0)
    ,"dta_value"=>array("발생수",0,0,0)
    ,"dta_date"=>array("날짜",0,0,1)
);
/*
$items1 = array(
    "dta_idx"=>array("번호",0,0,1)
    ,"mms_idx"=>array("설비번호",0,0,0)
    ,"imp_idx"=>array("IMP",0,0,0)
    ,"dta_shf_no"=>array("교대",0,0,0)
    ,"dta_code"=>array("CODE",0,0,0)
    ,"trm_idx_category"=>array("분류",0,0,0)
    ,"dta_value"=>array("값(db)",0,0,0)
    ,"dta_value_sum"=>array("합산",0,0,0)
    ,"dta_date"=>array("날짜",0,0,1)
);
*/
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" onsubmit="return sch_submit(this);" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_mms_idx" id="ser_mms_idx">
    <option value="">설비전체</option>
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

<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:95px;" placeholder="날짜검색시작">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:95px;" placeholder="날짜검색종료">

<select name="sfl" id="sfl">
    <option value="">검색항목</option>
    <?php
    $skips = array('com_idx','mmg_idx','mms_idx','dta_date','dta_value','dta_message');
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

<div class="float_right" style="display:inline-block;">
    <a href="./<?=preg_replace("/_sum/","",$fname)?>_list.php" class="btn btn_02">상세데이터목록</a>
</div>
</form>
<script>
function sch_submit(f){
    
    if(f.st_date.value && f.en_date.value){
        var st_d = new Date(f.st_date.value);
        var en_d = new Date(f.en_date.value);
        if(st_d.getTime() > en_d.getTime()){
            alert('검색날짜의 종료일를 시작일보다 과거일을 입력 할 수는 없습니다.');
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

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	<tr class="success">
		<th scope="col">
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
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $row['com'] = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '".$row['com_idx']."' ");
        $row['mms'] = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '".$row['mms_idx']."' ");
        $row['mmi'] = sql_fetch(" SELECT mmi_name FROM {$g5['mms_item_table']} WHERE mmi_idx = '".$row['dta_mmi_no']."' ");
        
        // 합산 추출
        $row['st_dt'] = strtotime($row['dta_date'].' 00:00:00');
        $row['en_dt'] = strtotime($row['dta_date'].' 23:59:59');
        $sql1 = "SELECT COUNT(dta_idx) AS dta_value_sum
                FROM ".$g5[preg_replace("/_sum/","",$table_name).'_table']."
                WHERE dta_status = 0
                    AND dta_dt >= '".$row['st_dt']."'
                    AND dta_dt <= '".$row['en_dt']."'
                    AND mms_idx = '".$row['mms_idx']."'
                    AND trm_idx_category = '".$row['trm_idx_category']."'
                    AND dta_shf_no = '".$row['dta_shf_no']."'
                    AND dta_group = '".$row['dta_group']."'
                    AND dta_code = '".$row['dta_code']."'
        ";
        $sum1 = sql_fetch($sql1,1);
        $row['dta_value_sum'] = $sum1['dta_value_sum'];
        
        // 조정 버튼 (합산값이랑 다를 때만 조정버튼)
        if((int)$sum1['dta_value_sum'] != $row['dta_value'])
            $row['s_mod'] = '<a href="javascript:" dta_idx="'.$row['dta_idx'].'" dta_group="'.$row['dta_group'].'" class="btn btn_03 btn_adjust">조정</a>';

        $bg = 'bg'.($i%2);
        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row[$pre.'status'].'" tr_id="'.$row[$pre.'_idx'].'">'.PHP_EOL;
        ?>
        <td class="td_chk">
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
                    $list[$k1] = '<span class="font_size_8">'.date("y-m-d H:i:s",$row[$k1]).'</span>';
//                    $list[$k1] = substr($row[$k1],0,10);
                }
                else if($k1=='mms_idx') {
                    $list[$k1] = '<a href="./mms_list.php?sfl=mms_idx&stx='.$row[$k1].'">'.$row[$k1].'  <span class="font_size_8">'.$row['mms']['mms_name'].'</span></a>';
                }
                else if($k1=='dta_mmi_no') {
                    $list[$k1] = $row[$k1].'  <span class="font_size_8">'.cut_str($row['mmi']['mmi_name'],12,'..').'</span>';
                }
                else if($k1=='com_idx') {
                    $list[$k1] = '<a href="./company_list.php?sfl=com.com_idx&stx='.$row[$k1].'">'.$row[$k1].'</a>';
                }
                else if($k1=='dta_value') {
                    $list[$k1] = $row[$k1];
                }
                else if($k1=='dta_value_sum') {
                    $row['dta_value_sum_color'] = ($row['dta_value']!=$row[$k1]) ? 'darkorange':'';
                    $list[$k1] = '<span style="color:'.$row['dta_value_sum_color'].'">'.$row[$k1].'</span>';
                }
                else if($k1=='dta_group') {
                    $list[$k1] = $row[$k1].' <span class="font_size_8">'.$g5['set_data_group_value'][$row[$k1]].'</span>';
                }
                else if($k1=='trm_idx_category') {
                    $list[$k1] = $row[$k1].' <span class="font_size_8">'.$g5['category_name'][$row[$k1]].'</span>';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
        if(false){ //($member['mb_manager_yn']) {
            echo '<td class="td_mngsmall">'.$row['s_mod'].'</td>'.PHP_EOL;
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
    <input type="submit" name="act_button" value="Chart" onclick="document.pressed=this.value" class="btn_02 btn">
    <input type="submit" name="act_button" value="일괄입력" onclick="document.pressed=this.value" class="btn_02 btn">
    <input type="submit" name="act_button" value="테스트입력" onclick="document.pressed=this.value" class="btn_03 btn">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <?php if(!auth_check($auth[$sub_menu],"w",1)) { ?>
    <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">추가하기</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_dta_type='.$ser_dta_type.'&amp;page='); ?>

<script>
$(function(e) {
    $("input[name=st_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=en_date]").datepicker('option','minDate',selectedDate);},closeText:'취소', onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');}} });

    $("input[name=en_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect:function(selectedDate){$("input[name=st_date]").datepicker('option','maxDate',selectedDate);},closeText:'취소', onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');}} });

    $(document).on('click','.btn_adjust',function(e){
        var dta_idx = $(this).attr('dta_idx');
        var dta_group = $(this).attr('dta_group');
        //-- 디버깅 Ajax --//
        $.ajax({
            url:g5_user_admin_ajax_url+'/data_adjust.php',
            data:{"aj":"set","dta_idx":dta_idx,"dta_group":dta_group},
            dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
                // console.log(res);
                //var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
                if(res.result == true) {
                    self.location.reload();
                }
                else {
                    alert(res.msg);
                }
            },
            error:function(xmlRequest) {
                alert('Status: ' + xmlRequest.status + ' \n\rstatusText: ' + xmlRequest.statusText 
                    + ' \n\rresponseText: ' + xmlRequest.responseText);
            } 
        });

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
        self.location = './data_run_chart.php';
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
