<?php
// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
$items1 = array(
    "com_name"=>array("업체명",0,0,0)
    ,"prj_name"=>array("공사프로젝트",0,0,1)
    ,"prj_ask_date"=>array("요청일",0,0,0)
    ,"mb_id_saler"=>array("영업담당",0,0,0)
    ,"prj_doc_no"=>array("발행번호",0,0,0)
    ,"prj_board_count"=>array("코멘트",0,2,0)
    ,"prj_reg_dt"=>array("등록일",0,0,0)
);
$items2 = array(
    "prj_end_company"=>array("최종고객",0,0,1)
    ,"prp_submit_price"=>array("제출금액",0,0,0)
    ,"prp_nego_price"=>array("NEGO금액",0,0,0)
    ,"mb_id_company"=>array("제출담당",0,0,0)
    ,"prj_submit_date"=>array("제출일",0,0,0)
    //,"prp_order_price"=>array("수주금액",0,0,0)
    ,"prj_quot_file"=>array("견적서",0,0,0)
    ,"prj_order_file"=>array("발주서",0,0,0)
    ,"prj_contract_file"=>array("계약서",0,0,0)
    ,"prj_status"=>array("상태",0,0,0)
);
$items = array_merge($items1,$items2);
?>
<style>
.sp_inf {}
.sp_ttl{margin-right:5px;}
.tbl_head01 td p{color:#333;}
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
    <option value="prj_idx">번호</option>
    <?php
    $skips = array('prj_set_output','prj_image','trm_idx_category','prj_idx2','prp_submit_price','prp_nego_price','prp_submit_price','prj_parts','prj_maintain','com_idx','mmg_idx','prj_checks','prj_item');
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
        
        // 수금결제 추출
        $sql = "SELECT * FROM {$g5['project_price_table']}
                WHERE prj_idx = '".$row['prj_idx']."'
                    AND prp_status NOT IN ('trash','delete')
                ";
        $row['ppr'] = sql_fetch($sql,1);

        // 관리 버튼
        $s_mod = '<a href="./'.$fname.'_form.php?'.$qstr.'&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" class="a_mod"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
        $s_cop = '<a href="./'.$fname.'_form.php?copy=1&amp;w=u&amp;'.$pre.'_idx='.$row['prj_idx'].'" class="a_cop"><i class="fa fa-clone" aria-hidden="true"></i></a>';
        //'<a href="./'.$fname.'_view.popup.php?&'.$pre.'_idx='.$row['prj_idx'].'" class="btn_view" style="margin-top:5px;"><i class="fa fa-eye" aria-hidden="true"></i></a>';
		//$s_del = '<a href="./prj_form_update.php?'.$qstr.'&amp;w=d&amp;prj_idx='.$row['prj_idx'].'&amp;ser_prj_type='.$ser_prj_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        //관련파일 추출
        $sql = "SELECT * FROM {$g5['file_table']} 
                    WHERE fle_db_table = 'quot' AND fle_type IN ('quot','order','contract') AND fle_db_id = '".$row['prj_idx']."' ORDER BY fle_reg_dt DESC ";
        $rs = sql_query($sql,1);
        //echo $rs->num_rows;echo "<br>";
        $row['prj_quot_fidxs'] = array();
        $row['prj_order_fidxs'] = array();
        $row['prj_contract_fidxs'] = array();
        for($j=0;$row2=sql_fetch_array($rs);$j++) {
            @array_push($row['prj_'.$row2['fle_type'].'_fidxs'],$row2['fle_idx']);
        }

        $bg = 'bg'.($i%2);

        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['prj_status'].'" tr_id="'.$row['prj_idx'].'">'.PHP_EOL;
        echo '<td style="text-align:left;">'.PHP_EOL;
		?>
		<div class="td_chk" rowspan="2" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="prj_idx[<?php echo $i ?>]" value="<?php echo $row['prj_idx'] ?>" id="prj_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['prj_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</div>
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
		
고운엔지니어링(<span color=gray>최종고객</span>: IAMR)
케이블 트레이 아크 용접라인 4호기
<span color=gray>요청일</span>: 2020-09-01   <span color=gray>제출일</span>: 2020-09-01   [날짜 데이터가 없으면 안보여준다]
<span color=gray>견적담당</span>: gowoo6
<span color=gray>제출금액</span>:9,000,000   <span color=gray>NEGO금액</span>: 90,000,000 [금액에 색상 넣지말고 볼드체로만 표현]

		*/
        //print_r2($row);
        echo '<p>번호: '.$row['prj_idx'].'</p>';
		$skips = array('prj_board_count');
		if(is_array($items)){
			foreach($items as $k => $v){
				if(in_array($k,$skips)) continue;
				//echo $k.":".$v[0].":".$row[$k]."<br>";
				//echo $row[$k]."<br>";
                //print_r2($row);
                //continue;
                $list[$k] = '';
                if($k == 'com_name') {
                    $row[$k] = '<p><span class="sp_inf sp_com_name">'.$row[$k].'</span>'.PHP_EOL;
					$row[$k] .= ($row['prj_end_company']) ? '(<sapn style="color:gray;">최종고객</span>: <span style="color:#333;">'.$row['prj_end_company'].'</span>)</p>'.PHP_EOL : '</p>'.PHP_EOL;
					$list[$k] = $row[$k];
                }
                else if($k=='prj_name') {
                    $row[$k] = '<p><span class="sp_inf sp_prj_name"><span style="font-size:1em;font-weight:bold;">'.$row[$k].'</span></span></p>'.PHP_EOL;
					$list[$k] = $row[$k];
                }
                else if($k=='prj_ask_date') {
					$row[$k] = ($row['prj_ask_date'] == '0000-00-00') ? '' : '<p><sapn style="color:gray;">요청일</span>: <span style="color:#333;">'.$row['prj_ask_date'].'</span></p>'.PHP_EOL;
					$row[$k] .= ($row['prj_submit_date'] == '0000-00-00') ? '' : '<p><sapn style="color:gray;">제출일</span>: <span style="color:#333;">'.$row['prj_submit_date'].'</span></p>'.PHP_EOL;
					$list[$k] = $row[$k];
                }
                else if($k=='mb_id_saler') {
					$row[$k] = ($row['mb_id_saler']) ? '<p><sapn style="color:gray;">견적담당</span>: <span style="color:#333;">'.$row['mb_id_saler'].'</span></p>' : '';
					$list[$k] = $row[$k];
                }
                /*
                else if($k=='prj_quot_file' || $k=='prj_order_file' || $k=='prj_contract_file'){
                    $file_str = '';
                    $file_str .= ($k=='prj_quot_file' && count($row['prj_quot_fidxs'])) ? '견('.count($row[$k]).')' : '';
                    $file_str .= ($k=='prj_order_file' && count($row['prj_order_fidxs'])) ? '주('.count($row[$k]).')' : '';
                    $file_str .= ($k=='prj_contract_file' && count($row['prj_contract_fidxs'])) ? '계('.count($row[$k]).')' : '';
                    $list[$k] = '<p class="file_box">'.$file_str.'</p>';
                }
                */
				else if($k=='mb_id_company') {
                    $row[$k] = (!$row[$k]) ? '' : '<p><span class="sp_inf sp_mb_id_company"><span class="sp_ttl">'.$v[0].'</span>'.$row[$k].'</span></p>'.PHP_EOL;
					$list[$k] = $row[$k];
                }
                else if($k=='prp_submit_price') {
					$row[$k] = ($row[$k]) ? '<p><span style="color:gray">제출금액</span>: <strong style="color:#333;">'.number_format($row[$k]).'</strong></p>'.PHP_EOL : '';
					$list[$k] = $row[$k];
                }
                else if($k=='prp_nego_price') {
					$row[$k] = ($row[$k]) ? '<p><span style="color:gray">NEGO금액</span>: <strong style="color:#333;">'.number_format($row[$k]).'</strong></p>'.PHP_EOL : '';
					$list[$k] = $row[$k];
                }
                else if($k == 'prj_status'){
                    if($row[$k] == 'ok') {
                        $list[$k] = '상태: <span style="color:blue;font-weight:bold;">'.$g5['set_prj_status_value'][$row[$k]].'</span>';
                    } else if($row[$k] == 'request') {
                        $list[$k] = '상태: <span class="txt_redblink">'.$g5['set_prj_status_value'][$row[$k]].'</span>';
                    } else {
                        $list[$k] = '상태: '.$g5['set_prj_status_value'][$row[$k]];
                    }
                }
                
                if($list[$k]) echo $list[$k];
			}
		}
        //echo '<div class="td_mngsmall">'.$s_mod.$s_view.'</div>'.PHP_EOL;
        echo $s_mod.$s_cop.PHP_EOL;
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