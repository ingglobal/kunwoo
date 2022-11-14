<?php
$sub_menu = '950600';
include_once('./_common.php');

//echo $member['mb_level'].'<br>';	// meta 확장, u.default.php
//echo $member['mb_1'].'<br>';	// meta 확장, u.default.php
//echo $member['mb_2'].'<br>';	// company_member 추출, u.project.php
//echo $member['mb_group_yn'].'<br>';	// meta 확장, u.default.php
//echo $g5['department_uptop_idx'][$member['mb_2']].'<br>'; // 최상위조직코드, u.project.php
//echo $g5['department_down_idxs'][$member['mb_2']].'<br>'; // 하부조직코드(들), u.project.php

auth_check($auth[$sub_menu], "r");

$g5['title'] = '도메인관리';
include_once('./_top_menu_work.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$sql_common = " FROM {$g5['domain_table']} AS dmn
                    LEFT JOIN {$g5['g5_shop_cart_table']} AS ct ON ct.ct_id = dmn.ct_id
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = dmn.com_idx
";

// 기본 검색
$where = array();
$where[] = " dmn_status NOT IN ('auto-draft','trash') ";

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'dmn_idx' || $sfl == 'mb_id_manager' || $sfl == 'ct_id' || $sfl == 'it_id' ) :
			$where[] = " $sfl = '".trim($stx)."' ";
            break;
		case ( $sfl == 'dmn_domains' ) :
			$where[] = " dmn_domain1 LIKE '%".trim($stx)."%' OR dmn_domain2 LIKE '%".trim($stx)."%' ";
            break;
		case ( $sfl == 'dmn_expire_date' ) :
			$where[] = " $sfl >= '".trim($stx)."' AND $sfl != '9999-12-31' AND $sfl != '0000-00-00' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 운영관리 레벨이 아니면 자기 조직 것만 리스트에 나옴, 2=회원,4=업체,6=영업자,8=관리자,10=수퍼관리자
if (!$member['mb_manager_or_account']) {
	// 디폴트 그룹 접근 레벨
	$my_access_department_idx = $member['mb_2'];

	// 팀장 이하는 자기 업체만 리스트, 0=사원,2=주임,4=대리,6=팀장,8=부서장,10=대표
	if ($member['mb_1'] < 6) {
        $where[] = " ct.mb_id_saler = '".$member['mb_id']."' ";
	}
	// 팀장 이상
	else {
        // 팀장 이상이면서 상위 그룹 접근이 가능하다면..
        if ($member['mb_group_yn'] == 1) {
            // 조직 검색이 있으면 조직 리스트만
            if ($_GET['ser_trm_idxs']) {
                $my_department_idxs = $_GET['ser_trm_idxs'];
            }
            else {
                $my_department_idxs = $g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]];
            }
        }
        // 아니면 내 조직만
        else
            $my_department_idxs = $g5['department_down_idxs'][$member['mb_2']];
        
        //$where[] = " ct.trm_idx_department IN (".$my_department_idxs.") ";
        $where[] = " ct.mb_id_saler IN ( SELECT mb_id FROM {$g5['member_table']} 
                                                WHERE mb_2 IN (".$my_department_idxs.") )
        ";
	}
}
// 관리자인 경우
else {
	// 조직 검색
	if ($_GET['ser_trm_idxs']) {
		$where[] = " ct.mb_id_saler IN ( SELECT mb_id FROM {$g5['member_table']} 
											WHERE mb_2 IN (".$_GET['ser_trm_idxs'].")  )
		";
	}
}

// 기간 검색
if ($st_date)	// 시작일 있는 경우
	$where[] = " sls_sales_date >= '{$st_date} 00:00:00' ";
if ($en_date)	// 종료일 있는 경우
	$where[] = " sls_sales_date <= '{$en_date} 23:59:59' ";

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// 정렬 설정
if (!$sst)
	$sql_order = " ORDER BY dmn_update_dt DESC ";
else 
	$sql_order = " ORDER BY {$sst} {$sod} ";

// 전체 카운트
$sql = " SELECT count(dmn_idx) AS cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = "	SELECT *
			$sql_common
			$sql_search
			$sql_order
			LIMIT $from_record, $rows
";
//echo $sql;
$result = sql_query($sql,1);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';


// 넘겨줄 변수가 추가로 있어서 qstr 추가 (한글이 있으면 encoding)
$qstr = $qstr."&amp;sfl_date=$sfl_date&amp;st_date=$st_date&amp;en_date=$en_date&amp;ser_trm_idxs=$ser_trm_idxs";

?>
<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">검색 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<?php if ($is_delete || $member['mb_1'] >= 6) { ?>
<select name="ser_trm_idxs" title="부서선택">
	<option value="">전체부서</option>
	<?=$department_select_options?>
</select>
<script>$('select[name=ser_trm_idxs]').val('<?=$_GET[ser_trm_idxs]?>').attr('selected','selected');</script>
&nbsp;&nbsp;
<?php } ?>
<select name="sfl" id="sfl">
    <option value="ct.com_name" <?php echo get_selected($sfl, 'ct.com_name'); ?>>업체명</option>
    <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>상품명</option>
    <option value="ct.mb_id_saler" <?php echo get_selected($sfl, 'ct.mb_id_saler'); ?>>영업자아이디</option>
    <option value="dmn_domain" <?php echo get_selected($sfl, 'dmn_domain'); ?>>도메인</option>
    <option value="dmn_domains" <?php echo get_selected($sfl, 'dmn_domains'); ?>>신청도메인</option>
    <option value="dmn_expire_date" <?php echo get_selected($sfl, 'dmn_expire_date'); ?>>도메인만료일</option>
    <option value="ct.od_id" <?php echo get_selected($sfl, 'ct.od_id'); ?>>접수번호</option>
    <option value="dmn.mb_id" <?php echo get_selected($sfl, 'dmn.mb_id'); ?>>고객아이디</option>
    <option value="dmn_name" <?php echo get_selected($sfl, 'dmn_name'); ?>>소유자</option>
    <option value="dmn_apply_name" <?php echo get_selected($sfl, 'dmn_apply_name'); ?>>신청자</option>
    <option value="dmn.com_idx" <?php echo get_selected($sfl, 'dmn.com_idx'); ?>>업체번호</option>
    <option value="dmn_status" <?php echo get_selected($sfl, 'dmn_status'); ?>>상태</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
<input type="submit" class="btn_submit" value="검색">
</form>


<form name="form01" id="form01" action="./domain_list_update.php" onsubmit="return form01_submit(this);" method="post" autocomplete="off">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs; ?>">


<div class="tbl_head01 tbl_wrap">
    <table id="sodr_list">
    <caption>신청 내역 목록</caption>
    <thead>
    <tr>
        <th rowspan="3" scope="col" style="width:40px;">
            <label for="chkall" class="sound_only">신청 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" style="width:150px;">상품</th>
        <th scope="col">업체명</th>
        <th scope="col">실도메인</th>
        <th scope="col" style="width:100px;"><?php echo subject_sort_link('dmn_expire_date', $qstr, 1) ?>만료일</a></th>
        <th scope="col">희망도메인</th>
        <th scope="col" style="width:130px;">영업자</th>
        <th scope="col" style="width:70px;"><?php echo subject_sort_link('dmn_reg_dt', $qstr, 1) ?>등록일</a></th>
        <th scope="col" style="width:110px;"><?php echo subject_sort_link('dmn_status', $qstr, 1) ?>상태</a></th>
        <th scope="col" rowspan="2" id="mb_list_mng">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 영업자 조직 정보 추출
        $row['mb1'] = get_saler($row['mb_id_saler']);
        $row['department_name'] = $g5['department_name'][$row['mb1']['mb_2']];
    
        // 도메인
        $row['dmn_domain'] = ($row['dmn_domain']) ? $row['dmn_domain'] : '-';
    
        // 만료일이 있으면 표시
        $row['dmn_expire_date_text'] = ($row['dmn_expire_date'] != '9999-12-31' && $row['dmn_expire_date'] != '0000-00-00') ? '<span style="color:darkorange;font-size:0.8em;">'.$row['dmn_expire_date'].'</span> ' : '-';
            
        // 희망 도메인2
        $row['dmn_domain2'] = ($row['dmn_domain1']) ? '<br>'.$row['dmn_domain2'] : $row['dmn_domain2'];
    
        // 업데이트날짜
        $row['dmn_update_dt_text'] = ($row['dmn_status'] == 'reject' || $row['dmn_status'] == 'retry') ? '<br><span style="font-size:0.8em;">'.substr($row['dmn_update_dt'],0,10).'</span>' : '';
        
        // 버튼
        //$s_mod = ($member['mb_manager']) ? '<a href="./domain_form.php?'.$qstr.'&amp;w=u&amp;dmn_idx='.$row['dmn_idx'].'">수정</a>' : '';
        $s_mod = '<a href="./domain_form.php?'.$qstr.'&amp;w=u&amp;dmn_idx='.$row['dmn_idx'].'">수정</a>';
        //$s_work = '<a href="./work_domain.php?ct_id='.$row['ct_id'].'" target="_blank">작업</a>';
    
        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?> tr_<?=$row['dmn_status']?>">
        <td><!-- 체크박스 -->
            <input type="hidden" name="dmn_idx[<?php echo $i ?>]" value="<?php echo $row['dmn_idx'] ?>" id="dmn_idx_<?php echo $i ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td><!-- 상품 -->
            <a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>" target="_blank"><?=$row['dmn_status_text']?><?=$row['it_name']?></a>
        </td>
        <td style="text-align:left;text-align:center;"><!-- 업체명 -->
            <a href="?sfl=dmn.com_idx&stx=<?php echo $row['com_idx']; ?>"><?php echo $row['com_name'] ?></a> 
            <a style="display:inline-block;cursor:pointer;" href="javascript:company_popup('./company_order_list.popup.php?com_idx=<?=$row['com_idx']?>','<?=$row['com_idx']?>')"><i class="fa fa-window-restore"></i></a> 
        </td>
        <td><!-- 도메인-->
            <?php echo $row['dmn_domain']; ?>
        </td>
        <td><!-- 만료일-->
            <?php echo $row['dmn_expire_date_text']; ?>
        </td>
        <td><!-- 희망도메인 -->
            <?=$row['dmn_domain1']?> <?=$row['dmn_domain2']?>
        </td>
        <td><!-- 영업자-->
            <?php echo $row['department_name']; ?>
            <br>
            <?php echo $row['mb1']['mb_name']; ?>
        </td>
        <td style="font-size:0.8em;"><!-- 등록일 -->
            <?php echo (is_null_time($row['ct_time']) ? '-' : substr($row['ct_time'],2,8)); ?>
        </td>
        <td><!-- 상태 -->
            <?php echo $g5['set_dmn_status_value'][$row['dmn_status']]; ?>
            <?=$row['dmn_update_dt_text']?>
        </td>
        <td class="td_mngsmall"><!-- 관리 -->
            <?=$s_work?>
            <?=$s_mod?>
        </td>
    </tr>
    <?php
        $tot_itemcount     += $row['od_cart_count'];
        $tot_orderprice    += ($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']);
        $tot_ordercancel   += $row['od_cancel_price'];
        $tot_receiptprice  += $row['od_receipt_price'];
        $tot_couponprice   += $row['couponprice'];
        $tot_misu          += $row['od_misu'];
    }
    sql_free_result($result);
    if ($i == 0)
        echo '<tr><td colspan="12" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<?php if($is_delete || $member['mb_1'] >= 20) { ?>
<div class="btn_list01 btn_list">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value">

	<div style="float:right;display:none;">
		<a href="javascript:alert('준비중')">엑셀출력</a>
	</div>
</div>
<?php } ?>


<div class="btn_fixed_top">
    <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
    <?php } ?>
    <a href="./domain_form.php" class="btn_01 btn" style="display:no ne;">도메인신청</a>
</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_trm_idxs='.$ser_trm_idxs.'&amp;page='); ?>

<script>
$(function(){
	// 부서 검색 추출, 해당 부서가 아닌 정보들은 숨김
	<?php if (!$is_delete && $member['mb_1'] >= 6) { ?>
	var dept_array = [<?=$g5['department_down_idxs'][$g5['department_uptop_idx'][$member['mb_2']]]?>];
	$('select[name=ser_trm_idxs] option').each(function(e) {
		//alert( $(this).val() );
		if($(this).val() !='') {
			var this_option = $(this);
			var dept_option_array = $(this).val().split(',');
			dept_option_array.forEach( function (value) {
				//console.log( value + ' / ' + this_option.val() + ' / ' + this_option.text() );
				//console.log( dept_array.indexOf( parseInt(value) ) );
				//console.log( '---' );
				// 배열 안에 해당 값이 없으면 옵션값 숨김
				if( dept_array.indexOf( parseInt(value) ) == -1 ) {
					//console.log( this_option.val() );
					//console.log( '제거' );
					this_option.remove();
				}
			});
		}
	});
	<?php } ?>
	
    $("#st_date,#en_date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
        showButtonPanel: true,
        yearRange: "c-99:c+99",
        //maxDate: "+0d"
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
        if (!confirm("선택한 게시물을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
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
