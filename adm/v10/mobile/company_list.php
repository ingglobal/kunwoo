<?php
$colspan = 11;

// 검색어 확장
$qstr .= $qstr.'&ser_trm_idxs='.$ser_trm_idxs.'&ser_com_type='.$ser_com_type.'&ser_trm_idx_salesarea='.$ser_trm_idx_salesarea;
?>
<style>

</style>
<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
    <span class="btn_ov01"><span class="ov_txt">승인대기</span><span class="ov_num"> <?php echo number_format($pending_count) ?></span></span>
	<div class="allchk_box">
		<input type="checkbox" value="1" id="chkall">
		<label for="chkall">전체선택</label>
	</div>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="ser_com_type" class="cp_field" title="업종선택" style="margin-top:5px;">
	<option value="">전체업종</option>
	<?=$g5['set_com_type_options_value']?>
</select>
<script>$('select[name=ser_com_type]').val('<?=$_GET[ser_com_type]?>').attr('selected','selected');</script>
<select name="sfl" id="sfl" style="margin-top:5px;">
	<option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
    <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자</option>
    <option value="mb_hp"<?php echo get_selected($_GET['sfl'], "mb_hp"); ?>>담당자휴대폰</option>
    <option value="com_president"<?php echo get_selected($_GET['sfl'], "com_president"); ?>>대표자</option>
	<option value="com.com_idx"<?php echo get_selected($_GET['sfl'], "com.com_idx"); ?>>업체고유번호</option>
	<option value="cmm.mb_id"<?php echo get_selected($_GET['sfl'], "cmm.mb_is"); ?>>담당자아이디</option>
    <option value="com_status"<?php echo get_selected($_GET['sfl'], "com_status"); ?>>상태</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="margin-top:5px;">
<input type="submit" class="btn_submit" value="검색" style="margin-top:5px;">
</form>

<div class="local_desc01 local_desc sound_only">
    <p>업체측 담당자를 관리하시려면 업체담당자 항목의 <i class="fa fa-users"></i> 편집아이콘을 클릭하세요. 담당자는 여러명일 수 있고 이직을 하는 경우 다른 업체에 소속될 수도 있습니다. </p>
</div>

<form name="form01" id="form01" action="./company_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="ser_com_type" value="<?php echo $ser_com_type; ?>">
<input type="hidden" name="ser_trm_idx_salesarea" value="<?php echo $ser_trm_idx_salesarea; ?>">

<div class="tbl_head01 tbl_wrap">
	<table class="table table-bordered table-condensed">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
	</thead>
	<tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        
		// 메타 분리
        if($row['com_namagers_info']) {
            $pieces = explode(',', $row['com_namagers_info']);
            for ($j1=0; $j1<sizeof($pieces); $j1++) {
                $sub_item = explode('^', $pieces[$j1]);
                for ($j2=0; $j2<sizeof($sub_item); $j2++) {
                    list($key, $value) = explode('=', $sub_item[$j2]);
//                    echo $key.'='.$value.'<br>';
                    $row['com_managers'][$j1][$key] = $value;
                }
            }
            unset($pieces);unset($sub_item);
        }
//		print_r2($row);
        
        // 담당자(들)
        if( is_array($row['com_managers']) ) {
            for ($j=0; $j<sizeof($row['com_managers']); $j++) {
//                echo $key.'='.$value.'<br>';
				if($row['com_managers'][$j]['mb_hp']){
					$row['com_managers_text'] .= $row['com_managers'][$j]['mb_name'].' '.$g5['set_mb_ranks_value'][$row['com_managers'][$j]['cmm_title']];
					$row['com_managers_text'] .= ' <span class="font_size_8">('.$row['com_managers'][$j]['mb_hp'].')</span><br>';
				}else{
					$row['com_managers_text'] .= $row['com_managers'][$j]['mb_name'].' '.$g5['set_mb_ranks_value'][$row['com_managers'][$j]['cmm_title']].'<br>';
				}
            }
        }

        // 직함까지 다 표현하려면 GROUP_CONCAT로 단순하게 합쳐버리면 안 됨
        $sql1 = "   SELECT mb_id, mb_name, mb_3
                    FROM {$g5['company_saler_table']} AS cms
                        LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cms.mb_id_saler
                    WHERE com_idx='".$row['com_idx']."' 
                        AND cms_status IN ('ok')
                        {$sql_mb_firms}
        ";
        $rs1 = sql_query($sql1,1);
        for($j=0;$row1=sql_fetch_array($rs1);$j++) {
            //print_r2($row1);
            $row['mb_name_salers'] .= $row1['mb_name'].' '.$g5['set_mb_ranks_value'][$row1['mb_3']].'<br>';
        }
        
		// 수정 및 발송 버튼
//		if($is_delete) {
			$s_mod = '<a href="./company_form.php?'.$qstr.'&amp;w=u&amp;com_idx='.$row['com_idx'].'&amp;ser_com_type='.$ser_com_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" class="a_mod"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
			$s_pop = '<a href="javascript:company_popup(\'./company_order_list.popup.php?com_idx='.$row['com_idx'].'\',\''.$row['com_idx'].'\')">보기</a>';
//		}
		//$s_del = '<a href="./company_form_update.php?'.$qstr.'&amp;w=d&amp;com_idx='.$row['com_idx'].'&amp;ser_com_type='.$ser_com_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        // 메모 갯수
		$sql3 = " 	SELECT count(wr_id) AS cnt_total
						, SUM( if( TIMESTAMPDIFF( HOUR, wr_datetime ,now() ) < '".(int)$g5['setting']['set_new_icon_hour']."', 1, 0 ) ) AS cnt_new
					FROM g5_write_company1
                    WHERE wr_is_comment = 0 
                        AND wr_2 = '".$row['com_idx']."'
		";
        $row['board'] = sql_fetch($sql3,1);
        //print_r3($row['board']);
        $row['board']['cnt_total_text'] = ($row['board']['cnt_total']) ? $row['board']['cnt_total']:'코멘트';
        $row['board']['cnt_new_text'] = ($row['board']['cnt_new']) ? '<span class="company_comment_new">('.$row['board']['cnt_new'].')</span>':'';
        
 
		// 삭제인 경우 그레이 표현
		if($row['com_status'] == 'trash')
			$row['com_status_trash_class']	= " tr_trash";

        $bg = 'bg'.($i%2);
    ?>

	<tr class="<?php echo $bg; ?> <?=$row['com_status_trash_class']?>" tr_id="<?php echo $row['com_idx'] ?>">
		<td>
			<div class="td_chk">
				<input type="hidden" name="com_idx[<?php echo $i ?>]" value="<?php echo $row['com_idx'] ?>" id="com_idx_<?php echo $i ?>">
				<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['com_name']); ?></label>
				<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
			</div>
			<p><b><?php echo get_text($row['com_name']); ?></b>&nbsp;&nbsp;&nbsp;<?php echo get_text($row['com_president']); ?></p>
			<p><?php echo $row['com_email']; ?></p>
			<p>TEL: <?php echo $row['com_tel']; ?>&nbsp;&nbsp;&nbsp;등록일: <?php echo substr($row['com_reg_dt'],0,10) ?></p>
			<div class="mng_box">
				<a href="javascript:" com_idx="<?=$row['com_idx']?>" class="btn_manager"><i class="fa fa-users"></i></a>
				<?php echo $row['com_managers_text']; ?>
			</div>
			<?php echo $s_mod ?>
		</td>
	</tr>
	<?php
	}
	if ($i == 0)
		echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn" style="border-radius:0;">
    <?php } ?>
    <a href="./company_form.php" id="bo_add" class="btn_01 btn"><i class="fa fa-plus" aria-hidden="true"></i></a>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_com_type='.$ser_com_type.'&amp;page='); ?>

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
        var href = "./company_member_list.php?com_idx="+$(this).attr('com_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=600,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_company_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_company_board = window.open(this_href,'win_company_board','left=100,top=100,width=770,height=650');
        win_company_board.focus();
	});
	
	
	$('#chkall').on('change',function(){
		//alert($(this).is(':checked'));
		var chk = document.getElementsByName("chk[]");
		//alert(chk.length);
		for (i=0; i<chk.length; i++)
			chk[i].checked = $(this).is(':checked');
	});
});

function form01_submit(f)
{
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
