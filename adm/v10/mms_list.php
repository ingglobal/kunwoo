<?php
$sub_menu = "   ";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

$g5['title'] = '설비(iMMS)관리';
include_once('./_top_menu_mms.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


$sql_common = " FROM {$g5['mms_table']} AS mms
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = mms.com_idx
                    LEFT JOIN {$g5['imp_table']} AS imp ON imp.imp_idx = mms.imp_idx
                    LEFT JOIN {$g5['mms_group_table']} AS mmg ON mmg.mmg_idx = mms.mmg_idx
"; 

$where = array();
$where[] = " mms_status NOT IN ('trash','delete') ";   // 디폴트 검색조건

// com_idx 조건
$where[] = " mms.com_idx IN (".$_SESSION['ss_com_idx'].") ";


if (isset($stx)&&$stx!='') {
    switch ($sfl) {
		case ( $sfl == 'mms.com_idx' || $sfl == 'mms_idx' ) :
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
    $sst = "mms_idx";
    $sod = "DESC";
}
$sql_order = " ORDER BY {$sst} {$sod} ";

$rows = $config['cf_page_rows'];
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT SQL_CALC_FOUND_ROWS DISTINCT *
            , com.com_idx AS com_idx
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
$items1 = array(
    "mms_image"=>array("이미지",0,2,0)
    ,"mms_name"=>array("설비명",0,0,1)
    // ,"trm_idx_category"=>array("설비분류",0,0,0)
    ,"mms_sort"=>array("순서",0,0,0)
    ,"mms_model"=>array("모델명",0,0,1)
    ,"mms_parts"=>array("부속품수",0,0,0)
    ,"mms_maintain"=>array("정비횟수",0,0,0)
    ,"com_name"=>array("업체명",0,0,0)
    ,"mms_idx"=>array("DB고유번호",0,0,1)
    ,"mms_reg_dt"=>array("등록일",0,0,1)
);
$items2 = array(
    "mms_idx2"=>array("관리번호",0,0,1)
    ,"mmg_name"=>array("그룹",0,0,0)
    ,"mms_price"=>array("도입가격",0,0,1)
    ,"mms_install_date"=>array("도입날짜",0,0,1)
    ,"mms_item"=>array("생산기종수",0,0,0)
    ,"imp_name"=>array("IMP명",0,0,0)
    ,"mms_set_output"=>array("생산통계기준",0,0,0)
    ,"mms_status"=>array("상태",0,0,1)
);
$items = array_merge($items1,$items2);
?>
<style>
.td_mms_image {width:120px;position:relative;}
.no_img{position:relative;width:100px;height:80px;line-height:80px;background:#444;display:inline-block;color:#777;font-weight:700;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총</span><span class="ov_num"> <?php echo number_format($total_count) ?></span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <?php
    $skips = array('mms_idx','mms_status','mms_set_output','mms_image','trm_idx_category','mms_idx2','mms_price','mms_parts','mms_maintain','com_idx','mmg_idx','mms_checks','mms_item');
    if(is_array($items)) {
        foreach($items as $k1 => $v1) {
            if(in_array($k1,$skips)) {continue;}
            echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
        }
    }
    ?>
    <?php if($member['mb_manager_yn']) { ?>
	<option value="mms.mmg_idx"<?php echo get_selected($_GET['sfl'], "mms.mmg_idx"); ?>>그룹번호</option>
	<option value="mms.com_idx"<?php echo get_selected($_GET['sfl'], "mms.com_idx"); ?>>업체번호</option>
    <?php } ?>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>프레스, 트랜스퍼, 인덕션히트와 같은 설비장치들(iMMS)들을 관리하는 페이지입니다. 설비를 최대 <?=$g5['setting']['set_imp_count']?>개씩 묶어서 iMP로 관리합니다.</p>
</div>

<form name="form01" id="form01" action="./mms_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
		<th scope="col" rowspan="2" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
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
                if($v1[3]>0)
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                else
                    echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
            }
        }
        ?>
		<th scope="col" id="mb_list_mng" rowspan="2">관리</th>
	</tr>
    <!-- 테이블 항목명 2번 라인 -->
	<tr>
        <?php
        $skips = array();
        if(is_array($items2)) {
            foreach($items2 as $k1 => $v1) {
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
	</tr>
	</thead>
	<tbody>
    <?php
    $fle_width = 100;
    $fle_height = 80;
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // print_r2($row);
        
        // mms_img 타입중에서 대표 이미지 한개만
        $sql = "SELECT * FROM {$g5['file_table']}
                WHERE fle_db_table = 'mms' AND fle_db_id = '".$row['mms_idx']."'
                    AND fle_type = 'mms_img'
                    AND fle_sort = 0
                ";
//        echo $sql.'<br>';
        $rs1 = sql_query($sql,1);
        for($j=0;$row1=sql_fetch_array($rs1);$j++) {
//            print_r2($row1);
            if( $row1['fle_name'] && is_file(G5_PATH.$row1['fle_path'].'/'.$row1['fle_name']) ) {
                $row['img'] = $row[$row1['fle_type']][$row1['fle_sort']]; // 변수명 좀 짧게
                $row['img']['thumbnail'] = thumbnail($row1['fle_name'], 
                                G5_PATH.$row1['fle_path'], G5_PATH.$row1['fle_path'],
                                $fle_width, $fle_width, 
                                false, true, 'center', true, $um_value='85/3.4/15');	// is_create, is_crop, crop_mode
            }
            else {
                $row[$row1['fle_type']][$row1['fle_sort']]['thumbnail'] = 'default.png';
                $row1['fle_path'] = '/data/mms_img';	// 디폴트 경로 결정해야 합니다.
            }
            $row['img']['thumbnail_img'] = '<img src="'.G5_URL.$row1['fle_path'].'/'.$row['img']['thumbnail'].'"
                                                width="'.$fle_width.'" height="'.$fle_height.'">';
        }
        
        // 부품 추출
        $sql = "SELECT count(mmp_idx) AS total_count FROM {$g5['mms_parts_table']}
                WHERE mms_idx = '".$row['mms_idx']."'
                    AND mmp_status NOT IN ('trash','delete')
                ";
        $row['parts'] = sql_fetch($sql,1);

        // 기종 추출
        $sql = "SELECT count(mmi_idx) AS total_count FROM {$g5['mms_item_table']}
                WHERE mms_idx = '".$row['mms_idx']."'
                    AND mmi_status NOT IN ('trash','delete')
                ";
        $row['item'] = sql_fetch($sql,1);

        // 점검항목 추출
        $sql = "SELECT count(mmc_idx) AS total_count FROM {$g5['mms_checks_table']}
                WHERE mms_idx = '".$row['mms_idx']."'
                    AND mmc_status NOT IN ('trash','delete')
                ";
        $row['checks'] = sql_fetch($sql,1);

        // 정비 추출
        $sql = "SELECT count(mnt_idx) AS total_count FROM {$g5['maintain_table']}
                WHERE mms_idx = '".$row['mms_idx']."'
                    AND mnt_status NOT IN ('trash','delete')
                ";
        $row['maintain'] = sql_fetch($sql,1);

        
        // 관리 버튼
        $s_mod = '<a href="./mms_form.php?'.$qstr.'&amp;w=u&amp;mms_idx='.$row['mms_idx'].'&amp;ser_mms_type='.$ser_mms_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'">수정</a>';
        $s_view = '<a href="./mms_view.popup.php?&mms_idx='.$row['mms_idx'].'" class="btn_view">보기</a>';
		//$s_del = '<a href="./mms_form_update.php?'.$qstr.'&amp;w=d&amp;mms_idx='.$row['mms_idx'].'&amp;ser_mms_type='.$ser_mms_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" onclick="return delete_confirm();" style="color:darkorange;">삭제</a>';
        
        $bg = 'bg'.($i%2);

        // 1번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['mms_status'].'" tr_id="'.$row['mms_idx'].'">'.PHP_EOL;
        ?>
		<td class="td_chk" rowspan="2" style="display:<?=(!$member['mb_manager_yn'])?'none':''?>;">
			<input type="hidden" name="mms_idx[<?php echo $i ?>]" value="<?php echo $row['mms_idx'] ?>" id="mms_idx_<?php echo $i ?>">
			<label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['mms_name']); ?></label>
			<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
		</td>
        <?php
        $skips = array();
        if(is_array($items1)) {
        //    print_r2($items1);
            foreach($items1 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // echo $k1.'<br>';
                // print_r2($v1);
                // 변수 재설정
                if($k1=='mms_image') {
                    if($row['img']['thumbnail_img']){
                        $row[$k1] = '<a href="./mms_view.popup.php?&mms_idx='.$row['mms_idx'].'" class="btn_image">'.$row['img']['thumbnail_img'].'</a>';
                    } else {
                        $row[$k1] = '<div class="no_img">NO IMAGE</div>';
                    }
                }
                else if($k1=='mms_reg_dt') {
                    $row[$k1] = substr($row[$k1],0,10);
                }
                else if($k1=='mms_parts') {
                    $row[$k1] = '<a href="./mms_parts_list.php?mms_idx='.$row['mms_idx'].'" class="btn_parts">'.$row['parts']['total_count'].'</a>';
                }
                else if($k1=='mms_maintain') {
                    $row[$k1] = '<a href="./maintain_list.php?mms_idx='.$row['mms_idx'].'" class="btn_maintain">'.$row['maintain']['total_count'].'</a>';
                }
                else if($k1=='mms_sort'){ //($k1=='trm_idx_category') {
                    // $row[$k1] = ($row[$k1]) ? $g5['mms_type_name'][$row[$k1]] : '-';
                    $row[$k1] = '<input type="text" name="mms_sort['.$i.']" value="'.$row['mms_sort'].'" class="frm_input" onclick="javascript:chk_Number(this)" style="text-align:right;width:60px;">';
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$row[$k1].'</td>';
            }
        }
        echo '<td class="td_mngsmall" rowspan="2">'.$s_mod.'<br>'.$s_view.'</td>'.PHP_EOL;
		//echo $td_items[$i];
        echo '</tr>'.PHP_EOL;


        // 2번 라인 ================================================================================
        echo '<tr class="'.$bg.' tr_'.$row['mms_status'].'" tr_id="'.$row['mms_idx'].'">'.PHP_EOL;
        $skips = array();
        if(is_array($items2)) {
            foreach($items2 as $k1 => $v1) {
                if(in_array($k1,$skips)) {continue;}
                // 변수 재설정
                if($k1=='mms_checks') {
                    $row[$k1] = '<a href="./mms_checks_list.php?mms_idx='.$row['mms_idx'].'" class="btn_checks">'.$row['checks']['total_count'].'</a>';
                }
                else if($k1=='mms_price') {
                    $row[$k1] = number_format($row[$k1]);
                }
                else if($k1=='mms_item') {
                    $row[$k1] = '<a href="./mms_item_list.php?mms_idx='.$row['mms_idx'].'" class="btn_checks">'.$row['item']['total_count'].'</a>';
                }
                else if($k1=='mms_set_output') {
                    $row[$k1] = (!$row[$k1]) ? $g5['set_mms_set_data_value']['shift'] : $g5['set_mms_set_data_value'][$row[$k1]];
                }
                else if($k1=='mms_set_error') {
                    $row[$k1] = (!$row[$k1]) ? $g5['set_mms_set_data_value']['shift'] : $g5['set_mms_set_data_value'][$row[$k1]];
                }
                else if($k1=='mms_status') {
                    $row[$k1] = $g5['set_status_value'][$row[$k1]];
                }

                $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$row[$k1].'</td>';
            }
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
    <?php if($member['mb_manager_yn']) { ?>
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:no ne;">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./mms_form.php" id="btn_add" class="btn_01 btn">추가하기</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_mms_type='.$ser_mms_type.'&amp;page='); ?>

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
        var href = "./mms_member_list.php?mms_idx="+$(this).attr('mms_idx');
        winCompanyMember = window.open(href, "winCompanyMember", "left=100,top=100,width=520,height=600,scrollbars=1");
        winCompanyMember.focus();
        return false;
    });

	// 코멘트 클릭 - 모달
	$(document).on('click','.btn_mms_comment',function(e){
        e.preventDefault();
        var this_href = $(this).attr('href');
        //alert(this_href);
        win_mms_board = window.open(this_href,'win_mms_board','left=100,top=100,width=770,height=650');
        win_mms_board.focus();
	});
	
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}


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