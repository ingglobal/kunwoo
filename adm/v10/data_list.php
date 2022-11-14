<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '데이터관리';
include_once('./_top_menu_data.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


$sql_common = " FROM {$g5['data_table']} AS dta 
                LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = dta.com_idx
                LEFT JOIN {$g5['imp_table']} AS imp ON imp.imp_idx = dta.imp_idx
                LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = dta.mms_idx
"; 

$where = array();
$where[] = " dta_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

if ($stx) {
    switch ($sfl) {
		case 'dta_name' :
            $where[] = " ( dta_name LIKE '%{$stx}%' OR dta_names LIKE '%{$stx}%' ) ";
            break;
		case ( $sfl == 'mb_id' || $sfl == 'dta_idx' ) :
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
    $sst = "dta_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT dta.*
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

$items = array(
    "dta_idx"=>"번호"
    ,"imp_idx"=>"IMP"
    ,"mms_idx"=>"MMS"
    ,"dta_shf_no"=>"교대"
    ,"dta_code"=>"코드"
    ,"dta_group"=>"GROUP"
    ,"dta_type"=>"TYPE"
    ,"dta_no"=>"측정번호"
    ,"dta_dt"=>"측정일시"
    ,"dta_value"=>"값"
    ,"dta_message"=>"메시지"
    ,"com_idx"=>"업체번호"
    ,"com_name"=>"업체명"
    ,"dta_reg_dt"=>"등록일"
);

?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <?php
    if(is_array($items)) {
        foreach($items as $k1 => $v1) {
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1.'</option>';
        }
    }
    ?>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<form name="form01" id="form01" action="./data_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
        if(is_array($items)) {
            foreach($items as $k1 => $v1) {
//                if($k1=='com_idx') {continue;}
                echo '<th scope="col">'.subject_sort_link($k1).$v1.'</a></th>';
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
			$s_mod = '<a href="./data_form.php?'.$qstr.'&amp;w=u&amp;dta_idx='.$row['dta_idx'].'&amp;ser_dta_type='.$ser_dta_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'">수정</a>';
//		}
		//$s_del = '<a href="./data_form_update.php?'.$qstr.'&amp;w=d&amp;dta_idx='.$row['dta_idx'].'&amp;ser_dta_type='.$ser_dta_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        $bg = 'bg'.($i%2);

        echo '<tr class="'.$bg.' tr_'.$row['dta_status'].'">'.PHP_EOL;
        ?>
		<td class="td_chk">
			<input type="hidden" name="dta_idx[<?php echo $i ?>]" value="<?php echo $row['dta_idx'] ?>" id="dta_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['dta_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <?php
        if(is_array($items)) {
            foreach($items as $k1 => $v1) {
                
                if($k1=='imp_idx') {
                    echo '<td class="td_'.$k1.'"><a href="./imp_list.php?sfl=dta_idx&stx='.$row[$k1].'">'.$row[$k1].'</a></td>'.PHP_EOL;
                }
                else if($k1=='mms_idx') {
                    echo '<td class="td_'.$k1.'"><a href="./mms_list.php?sfl=dta_idx&stx='.$row[$k1].'">'.$row[$k1].'</a></td>'.PHP_EOL;
                }
                else if($k1=='dta_name') {
                    echo '<td class="td_'.$k1.' td_left"><a href="./mms_list.php?sfl=dta_idx&stx='.$row[$k1].'">'.$row[$k1].'</a></td>'.PHP_EOL;
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
		echo '<tr><td colspan="15" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="테스트입력" onclick="document.pressed=this.value" class="btn_03 btn">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
    <?php } ?>
    <a href="./data_form.php" id="btn_add" class="btn_01 btn">항목추가</a>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_dta_type='.$ser_dta_type.'&amp;page='); ?>

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
        var href = "./data_member_list.php?dta_idx="+$(this).attr('dta_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=600,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_data_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_data_board = window.open(this_href,'win_data_board','left=100,top=100,width=770,height=650');
        win_data_board.focus();
	});
	
});

function form01_submit(f)
{
	if(document.pressed == "테스트입력") {
		window.open('<?=G5_URL?>/device/data/form.php');
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
