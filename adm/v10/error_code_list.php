<?php
$sub_menu = "917230";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'code';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
$qstr .= '&ser_cod_group='.$ser_cod_group.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

$g5['title'] = '예지 설정';
//include_once('./_top_menu_data.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];


$sql_common = " FROM {$g5_table_name} AS ".$pre."
                LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = ".$pre.".com_idx
                LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = ".$pre.".mms_idx
"; 

$where = array();
$where[] = " ".$pre."_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// com_idx 조건
$where[] = " cod.com_idx IN (".$_SESSION['ss_com_idx'].") ";

// cod_group 조건
if($ser_cod_group)
    $where[] = " cod_group = '".$ser_cod_group."' ";

// cod_type 조건
if($ser_cod_type)
    $where[] = " cod_type = '".$ser_cod_type."' ";

if ($stx) {
    switch ($sfl) {
		case ( $sfl == $pre.'_id' || $sfl == $pre.'_idx' || $sfl == 'cod.trm_idx_category' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ( $sfl == 'cod.com_idx' || $sfl == 'cod.mms_idx' ) :
            $where[] = " {$sfl} = '{$stx}' ";
            break;
		case ($sfl == $pre.'_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
        default :
            $where[] = " ({$sfl} LIKE '%{$stx}%') ";
            break;
    }
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
// echo $sql;
$result = sql_query($sql,1);
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") ); 
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, colspan, rowspan, 정렬링크여부(타이틀클릭)
$items1 = array(
    "cod_idx"=>array("번호",0,0,0)
    ,"mms_idx"=>array("설비명",0,0,0)
    ,"cod_code"=>array("코드",0,0,0)
    ,"cod.trm_idx_category"=>array("분류",0,0,0)
    ,"cod_name"=>array("알람내용",0,0,0)
    ,"cod_offline_yn"=>array("비가동",0,0,1)
    ,"cod_quality_yn"=>array("품질",0,0,1)
    ,"cod_code_count"=>array("누적발생",0,0,0)
    ,"cod_group"=>array("GROUP",0,0,0)
    ,"cod_type"=>array("TYPE",0,0,0)
    ,"cod_setting"=>array("설정",0,0,0)
    ,"cod_reports_count"=>array("알림대상",0,0,0)
    ,"cod_memo_check"=>array("메모",0,0,0)
);
?>
<style>
.tr_stop, .tr_stop .cod_type_text_p, .tr_stop .td_cod_code_count a {color:#bbb;}
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
<select name="ser_cod_type" id="ser_cod_type">
    <option value="">코드타입</option>
    <?=$g5['set_cod_type_value_options']?>
</select>
<script>$('select[name="ser_cod_type"]').val('<?=$ser_cod_type?>');</script>
<select name="ser_cod_group" id="ser_cod_group">
    <option value="">GROUP</option>
    <?=$g5['set_data_group_options']?>
</select>
<script>
    $('select[name="ser_cod_group"] option:gt(2)').remove(); // err, pre만 남겨두고 제거
    $('select[name="ser_cod_group"]').val('<?=$ser_cod_group?>');
</script>
<select name="sfl" id="sfl">
    <?php
    $skips = array('cod_idx','cod_group','cod_type','cod_code_count','cod_setting','cod_reports_count','cod_memo_check','com_idx','mms_idx','cod_offline_yn','cod_quality_yn');
    if(is_array($items1)) {
        foreach($items1 as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
    <option value="mms_name" <?=get_selected($sfl, "mms_name")?>>설비명</option>
    <option value="cod.mms_idx" <?=get_selected($sfl, "cod.mms_idx")?>>설비번호</option>
    <?php if($member['mb_level']>=9) { ?>
    <option value="cod.com_idx" <?=get_selected($sfl, "cod.com_idx")?>>업체번호</option>
    <?php } ?>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>설비에서 자동으로 넘어온 알람, 예지 등과 관련된 코드를 확인하고 설정하는 페이지입니다.</p>
    <p>GROUP항목은 일반알람과 PLC예지를 구분합니다. TYPE 설정을 통해 예지 발생 조건을 설정해 주시면 됩니다.</p>
    <p>체크된 항목들은 비가동에 영향을 주는 요소 또는 품질에 영향을 주는 요소 항목들입니다. [수정]에 들어가서 체크를 해제 또는 재설정할 수 있습니다.</p>
</div>

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
        $skips = (!$member['mb_manager_yn']) ? array('com_idx') : array();
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
		<th scope="col" id="mb_list_mng">수정</th>
	</tr>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $row['com'] = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '".$row['com_idx']."' ");
        $row['mms'] = sql_fetch(" SELECT mms_name FROM {$g5['mms_table']} WHERE mms_idx = '".$row['mms_idx']."' ");
        // print_r2($row);

        // report people.
        $row['reports'] = json_decode($row['cod_reports'], true);
        if( is_array($row['reports']) ) {
            foreach($row['reports'] as $k1 => $v1) {
                for($j=0;$j<@sizeof($v1);$j++) {
                    $row['reports_array'][$j][$k1] = $v1[$j];
                }
            }
        }
        // print_r2($row['reports_array']);
        

		// 버튼
        $s_view = ($member['mb_manager_yn']&&preg_match("/p/",$row['cod_type'])) ? '<a href="./'.$fname.'_view.php?'.$qstr.'&w=u&'.$pre.'_idx='.$row[$pre.'_idx'].'" class="btn btn_02">보기</a>' : '';
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&w=u&'.$pre.'_idx='.$row[$pre.'_idx'].'" class="btn btn_03">수정</a>';
        
        $bg = 'bg'.($i%2);
        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row[$pre.'_status'].'" tr_id="'.$row[$pre.'_idx'].'">'.PHP_EOL;
        ?>
        <td class="td_chk">
            <input type="hidden" name="<?=$pre?>_idx[<?php echo $i ?>]" value="<?php echo $row[$pre.'_idx'] ?>" id="<?=$pre?>_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row[$pre.'name']); ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <?php
        // cod_type이 foreach내부에서 값이 바뀌므로 별도 설정해서 바뀌지 않도록 함
        $row['cod_type1'] = $row['cod_type'];
        
        $skips = (!$member['mb_manager_yn']) ? array('com_idx') : array();
        if(is_array($items1)) {
//            print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // echo $k1.' / '.$row[$k1].'<br>';

                $list[$k1] = $row[$k1];

                if(preg_match("/_price$/",$k1)) {
                    $list[$k1] = number_format($row[$k1]);
                }
                else if(preg_match("/_dt$/",$k1)) {
                    $list[$k1] = '<span class="font_size_8">'.substr($row[$k1],0,16).'</span>';
                }
                else if($k1=='mms_idx') {
                    $list[$k1] = $row[$k1].'  <span class="font_size_8">'.cut_str($row['mms']['mms_name'],8,'..').'</span>';
                }
                else if($k1=='com_idx') {
                    $list[$k1] = $row[$k1].'  <span class="font_size_8">'.cut_str($row['com']['com_name'],8,'..').'</span>';
                    // $list[$k1] = $row[$k1];
                }
                else if($k1=='cod.trm_idx_category') {
                    $list[$k1] = '<span class="font_size_8">'.$g5['category_up_names'][$row['trm_idx_category']].'</span>';
                }
                else if($k1=='cod_group') {
                    $list[$k1] = ($row[$k1]=='pre') ? '<span class="font_size_8">'.$g5['set_cod_group_value'][$row[$k1]].'</span>' : '-';
                }
                else if($k1=='cod_type') {
                    $list[$k1] = '<span class="cod_type_text_'.$row[$k1].'"><span class="font_size_8">'.$g5['set_cod_type_value'][$row[$k1]].'</span></span>';
                }
                else if($k1=='cod_setting') {
                    // r, a, p 구분해서 설정 내용을 보여줌
                    if($row['cod_type1']=='r') {
                        $list[$k1] = '-';
                    }
                    else if($row['cod_type1']=='a') {
                        $list[$k1] = '-';
                    }
                    else if($row['cod_type1']=='p') {
                        $list[$k1] = '<span class="cod_type_text_'.$row['cod_type1'].'">'.$g5['set_cod_interval_value'][$row['cod_interval']].' '.$row['cod_count'].'회</span>';
                    }
                    else if($row['cod_type1']=='p2') {
                        $list[$k1] = '<span class="cod_type_text_'.$row['cod_type1'].'">발생시 알림</span>';
                    }
                }
                else if($k1=='cod_reports_count') {
                    if($row['cod_type1']=='r') {
                        $list[$k1] = '-';
                    }
                    else {
                        $list[$k1] = @count($row['reports_array']).'명';
                    }
                }
                else if($k1=='cod_memo_check') {
                    // $list[$k1] = ($row['cod_memo']) ? '<span><i class="fa fa-check"></i></span>' : '';
                    $list[$k1] = ($row['cod_memo']) ? cut_str($row['cod_memo'],10) : '';
                }
                else if($k1=='cod_code_count') {
                    $list[$k1] = '<a href="./error_data_list.php?ser_mms_idx='.$row['mms_idx'].'&sfl=dta_code&stx='.$row['cod_code'].'">'.number_format($row[$k1]).'</a>';
                }
                else if($k1=='cod_status') {
                    $list[$k1] = '<span class="font_size_8">'.$g5['set_cod_status_value'][$row[$k1]].'</span>';
                }
                else if($k1=='cod_offline_yn') {
                    $list[$k1] = ($row[$k1]=='1') ? '<i class="fa fa-check"></i>' : '';
                }
                else if($k1=='cod_quality_yn') {
                    $list[$k1] = ($row[$k1]=='1') ? '<i class="fa fa-check"></i>' : '';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
            }
        }
        echo '<td class="td_admin">'.$s_view.' '.$s_mod.'</td>'.PHP_EOL;
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
    <a href="./<?=$fname?>_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a>
    <a href="javascript:" id="btn_excel_upload" class="btn btn_03" style="margin-right:20px;">엑셀등록</a>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">추가하기</a>
    <?php } ?>
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./error_code_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
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
	if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/code/form.php');
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
