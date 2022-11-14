<?php
// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
/*
$items1 = array(
    "com_name"=>array("업체명",0,0,0)
    ,"prj_name"=>array("프로젝트명",0,0,1)
    ,"prs_type"=>array("담당직",0,0,0)
    ,"prs_mb_name"=>array("담당자",0,0,0)
    ,"prs_task"=>array("작업",0,2,0)
    ,"prs_start_date"=>array("작업시작일",0,0,0)
    ,"prs_end_date"=>array("작업종료일",0,0,0)
    //,"prs_content"=>array("내용",0,0,0)
);
*/
// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
$items1 = array(
    "prj_name"=>array("프로젝트명",0,0,1)
    ,"prs_mb_name"=>array("담당자",0,0,0)
    ,"prs_start_date"=>array("작업시작일",0,0,0)
    ,"prs_end_date"=>array("작업종료일",0,0,0)
);
?>
<style>
.tbl_head01 thead th{background:#f1f1f1;}
.tbl_head01 thead th a{color:#777;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form class="local_sch01 local_sch">
    <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" placeholder="부터(날짜범위)" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" placeholder="까지(날짜범위)" class="frm_input" size="10" maxlength="10">
    <input type="submit" value="검색" class="btn_submit" style="height:30px;line-height:30px;">
</form>
<div class="local_desc01 local_desc" style="display:none;">
    <p>견적관리 페이지입니다.</p>
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
    <!-- 테이블 항목명 1번 라인 -->
	<tr>
        <?php
        $skips = array('');
        if(is_array($items1)) {
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = '';   // rowspan 설정
                //$row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                // 정렬 링크
				if($k1 == 'prj_name')
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else{
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
				}
            }
        }
        ?>
		<!--th scope="col" id="mb_list_mng">관리</th-->
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        //print_r2($row);
        
        // 수금결제 추출
        $sql = "SELECT * FROM {$g5['project_price_table']}
                WHERE prj_idx = '".$row['prj_idx']."'
                    AND prp_status NOT IN ('trash','delete')
                ";
        $row['ppr'] = sql_fetch($sql,1);

        // 관리 버튼
        $s_mod = '<a href="./project_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prs_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'">수정</a>';
        $s_view = '<a href="./'.$fname.'_view.popup.php?&'.$pre.'_idx='.$row['prs_idx'].'" class="btn_view">보기</a>';
		//$s_del = '<a href="./prj_form_update.php?'.$qstr.'&amp;w=d&amp;prj_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        $bg = 'bg'.($i%2);

        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['prj_status'].'" tr_id="'.$row['prj_idx'].'">'.PHP_EOL;
        ?>
		<!--td class="td_chk" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td-->
        <?php
        $skips = array();
        if(is_array($items1)) {
        //    print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // echo $k1.'<br>';
                // print_r2($v1);
                // 변수 재설정
                if($k1=='prj_name') {
                    $row[$k1] = '<span>'.$row[$k1].'</span><br><span>('.$row['com_name'].')</span>';
                }
                else if($k1=='prs_mb_name') {
                    $row[$k1] = '<span>'.$row[$k1].'</span><br><span>('.$row['prs_type'].')</span>';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = '';   // rowspan 설정
                //$row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$row[$k1].'</td>';
            }
        }
        //echo '<td class="td_mngsmall">'.$s_mod.'</td>'.PHP_EOL;
		//echo $td_items[$i];
        echo '</tr>'.PHP_EOL;



    }
	if ($i == 0)
		echo '<tr><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
	?>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <?php if($member['mb_manager_yn']) { ?>
        <!--input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn"-->
        <a href="./project_form.php" id="btn_add" class="btn btn_01">일정추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_prj_type='.$ser_prj_type.'&amp;page='); ?>

<script>
$(function(e) {
    $("input[name$=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

    // 마우스 hover 설정
    //$(".tbl_head01 tbody tr").on({
    //    mouseenter: function () {
    //        //stuff to do on mouse enter
    //        //console.log($(this).attr('od_id')+' mouseenter');
    //        //$(this).find('td').css('background','red');
    //        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
    //        
    //    },
    //    mouseleave: function () {
    //        //stuff to do on mouse leave
    //        //console.log($(this).attr('od_id')+' mouseleave');
    //        //$(this).find('td').css('background','unset');
    //        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    //    }    
    //});
	
    // 장비보기 클릭
	$(document).on('click','.btn_view, .btn_image',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winMMSView = window.open(href, "winMMSView", "left=100,top=100,width=520,height=600,scrollbars=1");
        winMMSView.focus();
        return false;
    });

    // 부속품 클릭
	$(document).on('click','.btn_parts',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winParts = window.open(href, "winParts", "left=100,top=100,width=520,height=600,scrollbars=1");
        winParts.focus();
        return false;
    });

    // 기종 클릭
	$(document).on('click','.btn_item',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winItem = window.open(href, "winItem", "left=100,top=100,width=520,height=600,scrollbars=1");
        winItem.focus();
        return false;
    });

    // 정비 클릭
	$(document).on('click','.btn_maintain',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winMaintain = window.open(href, "winMaintain", "left=100,top=100,width=520,height=600,scrollbars=1");
        winMaintain.focus();
        return false;
    });

    // 점검기준 클릭
	$(document).on('click','.btn_checks',function(e){
        e.preventDefault();
        var href = $(this).attr('href');
        winChecks = window.open(href, "winChecks", "left=100,top=100,width=520,height=600,scrollbars=1");
        winChecks.focus();
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