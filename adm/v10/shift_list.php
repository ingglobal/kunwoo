<?php
$sub_menu = "915160";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'shift';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
$qstr .= '&ser_mms_idx='.$ser_mms_idx; // 추가로 확장해서 넘겨야 할 변수들


$g5['title'] = '작업구간 설정';
//include_once('./_top_menu_shift.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


$sql_common = " FROM {$g5_table_name} AS ".$pre." 
                LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = ".$pre.".com_idx
                LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = ".$pre.".mms_idx
"; 

$where = array();
$where[] = " ".$pre."_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// com_idx 조건
$where[] = " shf.com_idx IN (".$_SESSION['ss_com_idx'].") ";   // 디폴트 검색조건


if ($stx) {
    switch ($sfl) {
		case ( $sfl == $pre.'_id' || $sfl == $pre.'_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ( $sfl == 'shf.com_idx' || $sfl == 'shf.mms_idx' ) :
            $where[] = " {$sfl} = '{$stx}' ";
            break;
		case ($sfl == $pre.'_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == $pre.'_start_dt_more') :
            $where[] = " (shf_start_dt >= '{$stx}') ";
            break;
		case ($sfl == $pre.'_end_dt_more') :
            $where[] = " (shf_end_dt >= '{$stx}') ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
}

// 설비번호 검색
if ($ser_mms_idx) {
    $where[] = " shf.mms_idx = '".$ser_mms_idx."' ";
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

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT ".$pre.".*
            , com.com_name AS com_name
            , mms.mms_name AS mms_name
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
// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
$items1 = array(
    "mms_idx"=>array("설비",0,0,1)
    ,"shf_name"=>array("작업구간명",0,0,0)
    ,"shf_start_time"=>array("시작시간",0,0,0)
    ,"shf_end_time"=>array("종료시간",0,0,0)
    ,"shf_end_nextday"=>array("종료시간익일여부",0,0,0)
    ,"shf_period_type"=>array("기간타입",0,0,0)
    ,"shf_start_dt"=>array("적용시작일시",0,0,1)
    ,"shf_end_dt"=>array("적용종료일시",0,0,0)
    ,"shf_memo"=>array("메모",0,0,0)
    ,"shf_status"=>array("상태",0,0,0)
    ,"shf_reg_dt"=>array("등록일시",0,0,0)
);
?>
<style>
.td_mms_idx {text-align:left !important;}
.ui-dialog .ui-dialog-titlebar-close span {
    display: unset;
    margin: -8px 0 0 -8px;
}
#modal01 table ol {padding-right: 20px;text-indent: -12px;padding-left: 12px;}
#modal01 form {overflow:hidden;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<input type="text" name="ser_mms_idx" value="<?=$ser_mms_idx?>" id="ser_mms_idx" class="frm_input" autocomplete="off" style="width:65px;" placeholder="설비번호">
<select name="sfl" id="sfl">
    <option value="">검색항목</option>
    <?php
    $skips = array('com_idx','mms_idx','mmg_idx','shf_period_type');
    if(is_array($items1)) {
        foreach($items1 as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
    <option value="shf_start_dt_more" <?=get_selected($sfl, 'shf_start_dt_more')?>>적용시작일시이상</option>
    <option value="shf_end_dt_more" <?=get_selected($sfl, 'shf_end_dt_more')?>>적용종료일시이상</option>
    <option value="shf_idx" <?=get_selected($sfl, 'shf_idx')?>>번호</option>
    <option value="shf.com_idx" <?=get_selected($sfl, 'shf.com_idx')?> style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">업체번호</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p class="txt_redblink">설비idx=0 인 경우는 전체설비<br>설비idx 가 있으면 특정설비의 작업구간</p>
</div>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="ser_mms_idx" value="<?=$ser_mms_idx?>">

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
        $skips = array('shf_start_dt','shf_end_dt','shf_memo');
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
		<th scope="col" id="mb_list_mng">관리</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $row['com'] = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '".$row['com_idx']."' ");
        $row['mms'] = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '".$row['mms_idx']."' ");
        
		// 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.$row[$pre.'_idx'].'" class="btn btn_03">수정</a>';
        $s_copy = '<a href="./'.$fname.'_form.php?'.$qstr.'&w=c&'.$pre.'_idx='.$row[$pre.'_idx'].'" class="btn btn_03" style="margin-right:5px;">복제</a>';
        
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
        $skips = array('shf_start_dt','shf_end_dt','shf_memo');
        if(is_array($items1)) {
//            print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}

                $list[$k1] = $row[$k1];
                if($k1 == 'mms_idx'){
                    $mms_sql = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '{$row[$k1]}' ");
                    $list[$k1] = ($mms_sql['mms_name']) ? $mms_sql['mms_name'] : '전체설비';
                }
                else if($k1 == 'shf_start_time' || $k1 == 'shf_end_time'){
                    $list[$k1] = substr($row[$k1],0,5);
                }
                else if($k1 == 'shf_end_nextday'){
                    $list[$k1] = $g5['set_noyes_value'][$row[$k1]];
                }
                else if($k1 == 'shf_period_type'){
                    $list[$k1] = $g5['set_period_type_value'][$row[$k1]];
                }
                else if($k1 == 'shf_status'){
                    $list[$k1] = $g5['set_status_value'][$row[$k1]];
                }
                else if($k1 == 'shf_reg_dt'){
                    $list[$k1] = substr($row[$k1],0,10);
                }
                else{
                    $list[$k1] = $row[$k1];
                }
                
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
        echo '<td class="td_mngsmall" style="white-space:nowrap;">'.$s_copy.''.$s_mod.'</td>'.PHP_EOL;
        echo '</tr>'.PHP_EOL;	
	}
	if ($i == 0)
		echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"w",1)) { ?>
    <?php if($member['mb_manager_yn']) { ?>
    <input type="submit" name="act_button" value="테스트동기화" onclick="document.pressed=this.value" class="btn_03 btn">
    <input type="submit" name="act_button" value="테스트입력" onclick="document.pressed=this.value" class="btn_03 btn">
    <?php } ?>
    <?php if($_SESSION['ss_com_idx']==1) { ?>
    <a href="javascript:" id="btn_excel_upload" class="btn btn_03" style="margin-right:20px;">엑셀등록</a>
    <?php } ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">추가하기</a>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_shf_type='.$ser_shf_type.'&amp;page='); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./shift_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>

<script>
$(function(e) {
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

    // 엑셀등록 버튼
    $( "#modal01" ).dialog({
        autoOpen: false
        , position: { my: "right-40 top-10", of: "#btn_excel_upload"}
    });
    $( "#btn_excel_upload" ).on( "click", function() {
        $( "#modal01" ).dialog( "open" );
    });

});

function form01_submit(f)
{
	if(document.pressed == "테스트동기화") {
		window.open('<?=G5_URL?>/device/shift/list/form.php');
        return false;
	}

	if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/shift/form.php');
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

function form02_submit(f) {
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
