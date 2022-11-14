<?php
$items = array(
    "prj_idx"=>array("번호",0,0,0)
    ,"prj_name"=>array("프로젝트명",0,0,0)
    ,"prj_com_name"=>array("업체명",0,0,0)
    ,"prj_content"=>array("지시사항",0,0,0)
    ,"prj_percent"=>array("진행율",0,0,0)
    ,"prj_status"=>array("상태",0,0,0)
    //,"prs_content"=>array("내용",0,0,0)
);
?>
<style>
.sp_inf {display:block;}
.td_mngsmall {position:absolute;bottom:10px;right:10px;width:34px;}
.td_mngsmall a{font-size:1em;text-align:center;color:#fff;}
.td_mngsmall a.btn_mdfy{background:#f5f5f5;color:#777;}
.td_mngsmall a.btn_view{background:#9eacc6;}
.tbl_head01 tbody td{padding:20px 30px 20px 5px;}
.tbl_head01 tbody td p{line-height:1.7em !important;overflow:hidden;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
	<div class="allchk_box">
		<input type="checkbox" value="1" id="chkall">
		<label for="chkall">전체선택</label>
	</div>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>

<select name="sfl" id="sfl">
    <?php
    $skips = array('prj_status','prj_set_output','prj_image','trm_idx_category','prj_idx2','prp_submit_price','prp_nego_price','prp_submit_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item');
    
	if(is_array($items)) {
        foreach($items as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
	<option value="prj.com_idx"<?php echo get_selected($_GET['sfl'], "prj.com_idx"); ?>>업체번호</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>프로젝트관리 페이지입니다.</p>
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
	<table class="table table-bordered table-condensed mtbl">
	<caption><?php echo $g5['title']; ?> 목록</caption>
	<thead>
    <!-- 테이블 항목명 1번 라인 -->
	
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r2($row);
        
        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" class="a_mod"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
        
        $bg = 'bg'.($i%2);

        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['prj_status'].'" tr_id="'.$row['prj_idx'].'">'.PHP_EOL;
        echo '<td style="text-align:left; line-height:1.5em;">'.PHP_EOL;
		?>
		<div class="td_chk" rowspan="2" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</div>
        <p>번호: <?=$row['prj_idx']?></p>
		<p style="font-weight:bold;">
            <?php if($row['prj_name_req']) { ?>
                <span class="pm_req txt_redblink" style="font-size:0.8em;">(수정요청)</span>
            <?php } ?>
            <?=$row['prj_name']?>
        </p>
		<p><?=$row['prj_com_name']?> <?php if($row['prj_end_company']){ ?>[<span style="color:gray;">최종고객: </span><?=$row['prj_end_company']?>]<?php } ?></p>
        <?php
		/*
		prj_board_count:코멘트--------------------
		imp_order_file:발주서/계약서-------------
		
		com_name:업체명
		prj_name:공사프로젝트
		prj_ask_date:요청날짜
		mb_id_saler:견적담당자
		prj_doc_no:발행번호
		prj_quot_file:견적서
		prj_reg_dt:등록일
		prj_end_company:최종고객
		mb_id_company:제출담당자
		prj_submit_date:제출날짜
		prp_submit_price:제출금액
		prp_nego_price:NEGO금액
		prj_status:상태
		*/
        
        echo $s_mod.PHP_EOL;
		//echo $td_items[$i];
        echo '</td>'.PHP_EOL;
        echo '</tr>'.PHP_EOL;

    }
	if ($i == 0)
		echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./<?=$fname?>_form.php" id="btn_add" class="btn btn_01">견적추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>

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

    // 장비보기 클릭
	$(document).on('click','.btn_view, .btn_image',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winMMSView = window.open(href, "winMMSView", "left=100,top=100,width=520,height=600,scrollbars=1");
        winMMSView.focus();
        return false;
    });

   

    // 담당자 클릭
    $(".btn_manager").click(function(e) {
        var href = "./prj_member_list.php?prj_idx="+$(this).attr('prj_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=600,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_prj_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_prj_board = window.open(this_href,'win_prj_board','left=100,top=100,width=770,height=650');
        win_prj_board.focus();
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