<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");


// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'item_sum';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
$qstr .= '&ser_trm_line='.$ser_trm_line.'&st_date='.$st_date.'&en_date='.$en_date; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '일별생산합계';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// update_item_sum_by_status(160379);

$sql_common = " FROM {$g5_table_name} AS ".$pre."
                    LEFT JOIN {$g5['bom_table']} AS bom ON itm.bom_idx = bom.bom_idx
";

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

$where[] = " itm.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 기간 검색
if ($st_date) {
    $where[] = " itm_date >= '".$st_date."' ";
}
if ($en_date) {
    $where[] = " itm_date <= '".$en_date."' ";
}

// 라인번호 검색
if ($ser_trm_line) {
    $where[] = " itm.trm_idx_line = '".$ser_trm_line."' ";
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' HAVING '.implode(' AND ', $where);

$sql_groupby = ' GROUP BY itm.itm_date, itm.bom_part_no, itm.itm_status ';

if (!$sst) {
    $sst = $pre."_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS 
                    DISTINCT ".$pre.".*
                    , bom.bom_name
                    , SUM(itm.itm_count) AS itm_cnt
                    , SUM(itm.itm_weight) AS itm_wt
		{$sql_common}
		{$sql_groupby}
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
    "itm_idx"=>array("번호",0,0,1)
    ,"bom_name"=>array("품명",0,0,0)
    ,"bom_part_no"=>array("파트번호",0,0,0)
    ,"mms_idx"=>array("MMS_idx",0,0,0)
    ,"trm_idx_line"=>array("라인",0,0,0)
    ,"itm_status"=>array("상태",0,0,0)
    ,"itm_cnt"=>array("톤백수량",0,0,0)
    ,"itm_wt"=>array("무게(kg)",0,0,0)
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
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_trm_line" id="ser_trm_line">
    <option value="">설비라인</option>
    <?php
    // 설비라인
    $sql2 = "SELECT trm_idx, trm_name
            FROM {$g5['term_table']}
            WHERE trm_status = 'ok'
                AND com_idx = '".$_SESSION['ss_com_idx']."'
                AND trm_taxonomy = 'line'
            ORDER BY trm_left
    ";
    // echo $sql2.'<br>';
    $result2 = sql_query($sql2,1);
    for ($i=0; $row2=sql_fetch_array($result2); $i++) {
        // print_r2($row2);
        echo '<option value="'.$row2['trm_idx'].'" '.get_selected($ser_trm_line, $row2['trm_idx']).'>'.$row2['trm_name'].'</option>';
        $line_name[$row2['trm_idx']] = $row2['trm_name'];    // 아래쪽에서 사용하기 위해서 변수 설정
    }
    ?>
</select>
<script>$('select[name=ser_trm_line]').val("<?=$ser_trm_line?>").attr('selected','selected');</script>

<input type="text" name="st_date" value="<?=$st_date?>" id="st_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="검색시작일">
~
<input type="text" name="en_date" value="<?=$en_date?>" id="en_date" class="frm_input" autocomplete="off" style="width:80px;" placeholder="종료일">

<select name="sfl" id="sfl">
    <option value="">검색항목</option>
    <?php
    $skips = array('com_idx','mms_idx','bom_part_no','trm_idx_line');
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
		<th scope="col" id="mb_list_mng" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">수정</th>
	</tr>
	</thead>
	<tbody>
    <?php
    $g5['set_itm_mtr_status'] = array_merge($g5['set_itm_status'],$g5['set_half_status']);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
        
        // 합산 추출
        $sql1 = "SELECT COUNT(itm_idx) AS itm_count_sum
                FROM {$g5['item_table']} AS itm
                    LEFT JOIN g5_1_order_out_practice AS oop ON oop.oop_idx = itm.oop_idx
                    LEFT JOIN g5_1_order_practice AS orp ON orp.orp_idx = oop.orp_idx
                WHERE itm_date = '".$row['itm_date']."'
                    AND trm_idx_line = '".$row['trm_idx_line']."'
                    AND itm_shift = '".$row['itm_shift']."'
                    AND oop.bom_idx = '".$row['bom_idx']."'
                    AND itm_status = '".$row['itm_status']."'
        ";
        // echo $sql1.'<br>';
        $sum1 = sql_fetch($sql1,1);
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
                else if($k1=='trm_idx_line') {
                    $list[$k1] = $line_name[$row['trm_idx_line']];
                }
                else if($k1=='itm_status') {
                    $list[$k1] = $g5['set_itm_mtr_status'][$row['itm_status']];
                }
                else if($k1=='itm_count') {
                    $list[$k1] = number_format($row[$k1]);
                }
                else if($k1=='itm_count_sum') {
                    $row['itm_count_sum_color'] = ($row['itm_count']!=$row[$k1]) ? 'darkorange':'';
                    $list[$k1] = '<span style="color:'.$row['itm_count_sum_color'].'">'.number_format($row[$k1]).'</span>';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
        if($member['mb_manager_yn']) {
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
