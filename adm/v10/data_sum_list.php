<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");


// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'item';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
$qstr .= '&ser_mms_idx='.$ser_mms_idx.'&ser_itm_status='.$ser_itm_status.'&st_date='.$st_date.'&en_date='.$en_date; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '검색별생산합계';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5_table_name} ".$pre."
                    LEFT JOIN {$g5['bom_table']} bom ON itm.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['company_table']} com ON bom.com_idx_customer = com.com_idx
";

$where = array();
$where[] = " itm.com_idx = '".$_SESSION['ss_com_idx']."' ";

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
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// $where[] = " itm.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 기간 검색
if ($st_date) {
    $where[] = " itm_date >= '".$st_date."' ";
}
if ($en_date) {
    $where[] = " itm_date <= '".$en_date."' ";
}

// 설비번호 검색
if ($ser_mms_idx != '-1' && $ser_mms_idx) {
    $where[] = " itm.mms_idx = '".$ser_mms_idx."' ";
}
// 상태값 검색
if ($ser_itm_status) {
    if($ser_itm_status == 'error')
        $where[] = " itm.itm_status LIKE '".$ser_itm_status."_%' ";
    else
        $where[] = " itm.itm_status = '".$ser_itm_status."' ";
}

// 이전 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

$sql_groupby = ' GROUP BY itm.bom_part_no, itm.itm_date ';

if (!$sst) {
    $sst = "itm.oop_idx";
    $sod = "DESC";
}

if (!$sst2) {
    $sst2 = ", itm.itm_date";
    $sod2 = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = " SELECT ROW_NUMBER() OVER (ORDER BY itm.oop_idx, itm.itm_date) AS num
                , itm.oop_idx
                , itm.bom_part_no
                , itm.mms_idx
                , bom.bom_idx
                , com.com_idx
                , com.com_name
                , bom.bom_name
                , bom.bom_std
                , COUNT(itm.itm_idx) AS itm_cnt
                , itm.itm_date
		{$sql_common}
		{$sql_search}
		{$sql_groupby}
        {$sql_order}
";
$sql1 = $sql." LIMIT {$from_record}, {$rows} ";
$result = sql_query($sql1,1);
//총갯수를 구하는 쿼리
$sql2 = " SELECT COUNT(ctbl.num) AS total
                , SUM(ctbl.itm_cnt) AS lst_total
        FROM (
            {$sql}      
        ) ctbl
";
// print_r2($result);
// $count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
// echo $sql2;
$count = sql_fetch($sql2); 
$total_count = $count['total'];
$lst_total = $count['lst_total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, colspan, rowspan, 정렬링크여부(타이틀클릭)
$items1 = array(
    "num"=>array("번호",0,0,1)
    ,"oop_idx"=>array("생산계획ID",0,0,0)
    ,"com_name"=>array("업체명",0,0,0)
    ,"bom_part_no"=>array("품목코드",0,0,0)
    ,"bom_name"=>array("품명",0,0,0)
    ,"bom_std"=>array("규격",0,0,0)
    ,"mms_idx"=>array("설비",0,0,0) 
    ,"itm_cnt"=>array("합계",0,0,0)
    ,"itm_date"=>array("통계일",0,0,1)
);
/*
$items1 = array(
    "itm_idx"=>array("번호",0,0,1)
    ,"bom_idx"=>array("품명",0,0,0)
    ,"bom_part_no"=>array("파트번호",0,0,0)
    ,"mms_idx"=>array("MMS_idx",0,0,0)
    ,"trm_idx_line"=>array("라인",0,0,0)
    ,"itm_shift"=>array("구간",0,0,0)
    ,"itm_price"=>array("단가",0,0,0)
    ,"itm_status"=>array("상태",0,0,0)
    ,"itm_count"=>array("생산량",0,0,0)
    ,"itm_count_sum"=>array("비교",0,0,0)
    ,"itm_date"=>array("날짜",0,0,1)
);
*/
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" onsubmit="return sch_submit(this);" method="get">
<input type="hidden" name="com_idx_customer" id="com_idx_customer" value="<?=$com_idx_customer?>">
<input type="text" name="com_idx_name" value="<?=$com_idx_name?>" id="btn_customer" readonly class="frm_input readonly" autocomplete="off" style="width:95px;" placeholder="업체선택" link="./customer_select.php?file_name=<?php echo $g5['file_name']?>">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_mms_idx" id="ser_mms_idx">
    <option value="-1">::단조설비선택::</option>
    <?=$g5['forge_options']?>
</select>
<script>$('select[name=ser_mms_idx]').val("<?=(($ser_mms_idx)?$ser_mms_idx:'-1')?>").attr('selected','selected');</script>

<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:95px;" placeholder="통계시작일">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:95px;" placeholder="통계종료일">
<select name="ser_itm_status" id="ser_itm_status">
    <option value="">::상태선택::</option>
    <?=$g5['set_itm_status_value_options']?>
    <option value="error">불량전체</option>
</select>
<script>$('select[name=ser_itm_status]').val("<?=(($ser_itm_status)?$ser_itm_status:'')?>").attr('selected','selected');</script>
<select name="sfl" id="sfl">
    <option value="">::검색항목::</option>
    <?php
    $skips = array('itm_idx','com_idx','mms_idx','itm_cnt','itm_status','itm_date');
    if(is_array($items1)) {
        foreach($items1 as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
    <option value="itm.bom_part_no" <?=get_selected($sfl, 'itm.bom_part_no')?>>파트번호</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">

<div class="float_right" style="display:inline-block;">
    <strong>총합산</strong>:<span style="margin-left:10px;margin-right:20px;"><?=number_format($lst_total)?></span>
    <a href="./<?=preg_replace("/_sum/","",$fname)?>_list.php" class="btn btn_02">상세목록</a>
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
		<!-- <th scope="col" id="mb_list_mng" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">수정</th> -->
	</tr>
	</thead>
	<tbody>
    <?php
    $g5['set_itm_mtr_status'] = array_merge($g5['set_itm_status'],$g5['set_half_status']);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
       
        $row['itm_count_sum'] = $sum1['itm_count_sum'];
        
        // 조정 버튼 (합산값이랑 다를 때만 조정버튼)
        if((int)$sum1['itm_count_sum'] != $row['itm_count'])
            $row['s_mod'] = '<a href="javascript:" itm_idx="'.$row['itm_idx'].'" itm_group="item" class="btn btn_03 btn_adjust">조정</a>';

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
                    $list[$k1] = $g5['trms']['forge_idx_arr'][$row['mms_idx']];
                }
                else if($k1=='itm_status') {
                    $list[$k1] = $g5['set_itm_mtr_status'][$row['itm_status']];
                }
                else if($k1=='itm_count') {
                    $list[$k1] = number_format($row[$k1]);
                }
                else if($k1=='itm_count_sum') {
                    $list[$k1] = number_format($row[$k1]);
                    // $row['itm_count_sum_color'] = ($row['itm_count']!=$row[$k1]) ? 'darkorange':'';
                    // $list[$k1] = '<span style="color:'.$row['itm_count_sum_color'].'">'.number_format($row[$k1]).'</span>';
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
    <?php if($is_admin){ //(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <!--
    <input type="submit" name="act_button" value="전체보정" onclick="document.pressed=this.value" class="btn_02 btn">
    -->
    <input type="submit" name="act_button" value="일괄입력" onclick="document.pressed=this.value" class="btn_02 btn">
    <input type="submit" name="act_button" value="테스트입력" onclick="document.pressed=this.value" class="btn_03 btn">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <?php if($is_admin){ //(!auth_check($auth[$sub_menu],"w",1)) { ?>
    <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">추가하기</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_itm_type='.$ser_itm_type.'&amp;page='); ?>

<script>
$(function(e) {
    $("input[name=st_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=en_date]").datepicker('option','minDate',selectedDate);},closeText:'취소', onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');}} });

    $("input[name=en_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect:function(selectedDate){$("input[name=st_date]").datepicker('option','maxDate',selectedDate);},closeText:'취소', onClose: function(){ if($(window.event.srcElement).hasClass('ui-datepicker-close')){ $(this).val('');  }  } });

    $(document).on('click','.btn_adjust',function(e){
        if(confirm('합계값을 조정하시겠습니까?')) {
            var itm_idx = $(this).attr('itm_idx');
            var itm_group = $(this).attr('itm_group');
            //-- 디버깅 Ajax --//
            $.ajax({
                url:g5_user_admin_ajax_url+'/data_adjust.php',
                data:{"aj":"itm","itm_idx":itm_idx,"itm_group":itm_group},
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
        }

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

    // 거래처찾기 버튼 클릭
	$("#btn_customer").click(function(e) {
		e.preventDefault();
        var href = $(this).attr('link');
		winCustomerSelect = window.open(href, "winCustomerSelect", "left=300,top=150,width=550,height=600,scrollbars=1");
        winCustomerSelect.focus();
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
            winDataInsert = window.open('<?=G5_USER_ADMIN_URL?>/convert/data_item1.php', "winDataInsert", "left=100,top=100,width=520,height=600,scrollbars=1");
            winDataInsert.focus();
            return false;
        }
        return false;
	}

    if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/itm_ing/form.php');
        return false;
	}

    if (!is_checked("chk[]") && document.pressed != "전체보정") {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요2.");
        return false;
    }

	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}

    if(document.pressed == "전체보정") {
		$('input[name="w"]').val('m');
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
