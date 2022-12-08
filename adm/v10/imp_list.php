<?php
$sub_menu = "917180";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = 'iMP관리';
//include_once('./_top_menu_company.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];


$sql_common = " FROM {$g5['imp_table']} AS imp 
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = imp.com_idx
"; 

$where = array();
$where[] = " imp_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// com_idx 조건
$where[] = " imp.com_idx IN (".$_SESSION['ss_com_idx'].") ";

if (isset($stx)&&$stx!='') {
    switch ($sfl) {
		case ( $sfl == 'mb_id' || $sfl == 'imp_idx' ) :
            $where[] = " ({$sfl} = '{$stx}') ";
            break;
		case ($sfl == 'mb_hp') :
            $where[] = " REGEXP_REPLACE(mb_hp,'-','') LIKE '".preg_replace("/-/","",$stx)."' ";
            break;
		case ($sfl == 'mb_id_saler' || $sfl == 'mb_name_saler' ) :
            $where[] = " (mb_id_salers LIKE '%^{$stx}^%') ";
            break;
		case ($sfl == 'mb_name' || $sfl == 'mb_nick' ) :
            $where[] = " ({$sfl} LIKE '{$stx}%') ";
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
    $sst = "imp_idx";
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

// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
$items = array(
    "com_name"=>array("업체명",0,0,0)
    ,"imp_name"=>array("iMP명",0,0,1)
    ,"imp_idx2"=>array("관리번호",0,0,1)
    ,"mms_lists"=>array("설비(iMMS)",0,0,0)
    ,"imp_location"=>array("위치",0,0,0)
    ,"imp_install_date"=>array("도입날짜",0,0,1)
    ,"imp_reg_dt"=>array("등록일",0,0,1)
    ,"imp_idx"=>array("DB고유번호",0,0,1)
    ,"imp_status"=>array("상태",0,0,0)
);
?>
<style>
    .td_mms_lists {text-align:left !important;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <?php
    $skips = array('imp_idx','mms_count','imp_status','mms_lists');
    if(is_array($items)) {
        foreach($items as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
    <?php if($member['mb_manager_yn']) { ?>
	<option value="imp.com_idx"<?php echo get_selected($_GET['sfl'], "imp.com_idx"); ?>>업체고유번호</option>
    <?php } ?>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>iMP는 iMMS(설비,장비)를 최대 <?=$g5['setting']['set_imp_count']?>개 단위로 묶어주는 개념입니다. 장비와 1:1 관계가 아닙니다.</p>
</div>

<form name="form01" id="form01" action="./imp_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
		<th scope="col" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<label for="chkall" class="sound_only">항목 전체</label>
			<input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
		</th>
        <?php
        $skips = array();
        if(is_array($items)) {
            foreach($items as $k1 => $v1) {
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
        
		// 수정 및 발송 버튼
//		if($is_delete) {
			$s_mod = '<a href="./imp_form.php?'.$qstr.'&amp;w=u&amp;imp_idx='.$row['imp_idx'].'&amp;ser_imp_type='.$ser_imp_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'">수정</a>';
//		}
        //$s_del = '<a href="./imp_form_update.php?'.$qstr.'&amp;w=d&amp;imp_idx='.$row['imp_idx'].'&amp;ser_imp_type='.$ser_imp_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        // mms list & count
        $sql = "SELECT mms_idx2, mms_name, mms_status FROM {$g5['mms_table']}
                WHERE imp_idx = '".$row['imp_idx']."' AND mms_status NOT IN('delete','del','trash')
                ORDER BY mms_idx2
        ";
        $rs1 = sql_query($sql,1);
        for($j=0;$row1=sql_fetch_array($rs1);$j++) {
            // print_r2($row1);
            $row['mms']['list'] .= '<div>'.$row1['mms_idx2'].'. '.$row1['mms_name'].'</div>';
        }
        $row['mms']['mms_count'] = $j;
        // print_r2($row['mms']);
        
        $bg = 'bg'.($i%2);

        echo '<tr class="'.$bg.' tr_'.$row['imp_status'].'">'.PHP_EOL;
        ?>
		<td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="imp_idx[<?php echo $i ?>]" value="<?php echo $row['imp_idx'] ?>" id="imp_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['imp_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <?php
        if(is_array($items)) {
            foreach($items as $k1 => $v1) {
                
                if($k1=='imp_idx') {
                    echo '<td class="td_'.$k1.'"><a href="./imp_list.php?sfl=imp_idx&stx='.$row[$k1].'">'.$row[$k1].'</a></td>'.PHP_EOL;
                }
                else if($k1=='mms_idx') {
                    echo '<td class="td_'.$k1.'"><a href="./mms_list.php?sfl=imp_idx&stx='.$row[$k1].'">'.$row[$k1].'</a></td>'.PHP_EOL;
                }
                else if($k1=='mms_lists') {
                    echo '<td class="td_'.$k1.'">'.$row['mms']['list'].'</td>'.PHP_EOL;
                }
                else if($k1=='mms_count') {
                    echo '<td class="td_'.$k1.'"><a href="./mms_list.php?sfl=mms.imp_idx&stx='.$row['imp_idx'].'">'.$row['mms']['mms_count'].'</a></td>'.PHP_EOL;
                }
                else if($k1=='imp_status') {
                    echo '<td class="td_'.$k1.'">'.$g5['set_status_value'][$row[$k1]].'</td>'.PHP_EOL;
                }
                else {
                    echo '<td class="td_'.$k1.'">'.$row[$k1].'</td>'.PHP_EOL;
                }
            }
        }
        echo '<td class="td_mngsmall">'.$s_mod.'</td>'.PHP_EOL;
		//echo $td_items[$i];
        echo '</tr>'.PHP_EOL;	
	}
	if ($i == 0)
		echo '<tr><td colspan="11" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./imp_form.php" id="btn_add" class="btn_01 btn">추가하기</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_imp_type='.$ser_imp_type.'&amp;page='); ?>

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

    // 담당자 클릭
    $(".btn_manager").click(function(e) {
        var href = "./imp_member_list.php?imp_idx="+$(this).attr('imp_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=600,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_imp_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_imp_board = window.open(this_href,'win_imp_board','left=100,top=100,width=770,height=650');
        win_imp_board.focus();
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
</script>

<?php
include_once ('./_tail.php');
?>
