<?php
$sub_menu = '950200';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g5['title'] = '매출상품내역';
include_once('./_top_menu_order.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

// 주문내역 추출 초기화 설정(ct_history 첫줄에 줄바꿈 제거해서 \n분리를 통한 리스트 추출에 중복을 제거한 후 리스팅)
order_init();

$where = array();


// 관리자가 아닌 경우 (d 권한없음) 
if(auth_check($auth[$sub_menu],"d",1)) {
    //print_r3(get_dept_idxs());
    if(get_dept_idxs()) {
        $where[] = " trm_idx_department IN (".get_dept_idxs().") ";
    }
    // 팀원이면 무조건 자기것만 보임
    if($member['mb_1']<=4) {
        $where[] = " mb_id_saler = '".$member['mb_id']."' ";
    }
}

// (d 권한이 있어도) 법인접근 권한이 없으면 자기 법인만 조회 가능
if(!$member['mb_firm_yn']) {
    $where[] = " ct_company = '".$member['mb_4']."' ";
}


$sel_field = get_search_string($sel_field);
$ct_status = get_search_string($ct_status);
$search = get_search_string($search);
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $fr_date) ) $fr_date = '';
if(! preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $to_date) ) $to_date = '';


$where[] = " tally.n <= 1 + (LENGTH(ct.ct_history) - LENGTH(REPLACE(ct.ct_history, '\n', ''))) AND ct_select = '1' AND ct_status NOT IN ('쇼핑','삭제') ";
if ($search != "") {
    if ($sel_field == "ct_id"||$sel_field == "od_id")
        $where[] = " $sel_field = '$search' ";
    else if ($sel_field == "line_cnt")
        $where[] = " ( LENGTH(ct.ct_history) - LENGTH(REPLACE(ct.ct_history, '\n', '')) +1 ) = ".(int)$search." ";
    else
        $where[] = " $sel_field like '%$search%' ";

    if ($save_search != $search) {
        $page = 1;
    }
}

// 상태
if ($ct_status) {
    $where[] = " IF( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', 1 )!='', 
		SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', 1 ), ct_status ) IN ('".$ct_status."') ";
}

// 정산일자
if ($fr_date && $to_date) {
    $where[] = " IF( SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 )!='',
		SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 ), ct_select_time) BETWEEN '$fr_date 00:00:00' AND '$to_date 23:59:59' ";
}
else if ($fr_date) {
    $where[] = " IF( SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 )!='',
		SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 ), ct_select_time) >= '$fr_date 00:00:00' ";
}
else if ($to_date) {
    $where[] = " IF( SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 )!='',
		SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 ), ct_select_time) <= '$to_date 23:59:59' ";
}

if ($where) {
    $sql_search = ' WHERE '.implode(' AND ', $where);
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "ct_history_line_date";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " FROM {$g5['g5_shop_cart_table']} AS ct CROSS JOIN {$g5['tally_table']} AS tally $sql_search ";

$sql = " select count(ct_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " SELECT *
				, IF( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', 1 )!='', 
					SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', 1 ), ct_status ) AS ct_history_line_status
				, IF( SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 )!='',
					SUBSTRING_INDEX( SUBSTRING_INDEX( SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1), '|', -2 ), '|', 1 ), ct_select_time) AS ct_history_line_date
				, SUBSTRING_INDEX(SUBSTRING_INDEX(ct.ct_history, '\n', tally.n), '\n', -1) AS ct_history_line
                , ( LENGTH(ct.ct_history) - LENGTH(REPLACE(ct.ct_history, '\n', '')) + 1 ) AS line_cnt
           $sql_common
           ORDER BY $sort1 $sort2
           LIMIT $from_record, $rows
";
//echo $sql;  // 그냥 echo로 찍은 거 복사해서 보면 에러!!
$result = sql_query($sql);

$qstr1 = "ct_status=".urlencode($ct_status)."&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>
<style>
.text_top_left {text-align:left !important;vertical-align:top;}
.text_ct_id, .text_ct_id a, .text_od_id, .text_od_id a {color:#818181;}
.text_ct_id a:hover, .text_od_id a:hover {color:#222;}
.td_item {min-width:165px;}
.td_price {min-width:165px;}
.td_status {min-width:130px;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 주문내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
</div>

<form name="frmorderlist" class="local_sch01 local_sch">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_search" value="<?php echo $search; ?>">

<label for="sel_field" class="sound_only">검색대상</label>
<select name="sel_field" id="sel_field">
    <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>주문번호</option>
    <option value="ct_id" <?php echo get_selected($sel_field, 'ct_id'); ?>>주문상품번호</option>
    <option value="it_name" <?php echo get_selected($sel_field, 'it_name'); ?>>상품명</option>
    <option value="ct_option" <?php echo get_selected($sel_field, 'ct_option'); ?>>옵션명</option>
    <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
    <option value="line_cnt" <?php echo get_selected($sel_field, 'line_cnt'); ?>>상태변경횟수</option>
</select>

<label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="search" value="<?php echo $search; ?>" id="search" required class="required frm_input" autocomplete="off">
<input type="submit" value="검색" class="btn_submit">

</form>

<form class="local_sch03 local_sch">
<div>
    <strong>상태</strong>
    <input type="radio" name="ct_status" value="" id="ct_status_all"    <?php echo get_checked($ct_status, '');     ?>>
    <label for="ct_status_all">전체</label>
    <input type="radio" name="ct_status" value="주문" id="ct_status_odr" <?php echo get_checked($ct_status, '주문'); ?>>
    <label for="ct_status_odr">주문</label>
    <input type="radio" name="ct_status" value="입금" id="ct_status_income" <?php echo get_checked($ct_status, '입금'); ?>>
    <label for="ct_status_income">입금</label>
    <input type="radio" name="ct_status" value="준비" id="ct_status_rdy" <?php echo get_checked($ct_status, '준비'); ?>>
    <label for="ct_status_rdy">준비</label>
    <input type="radio" name="ct_status" value="배송" id="ct_status_dvr" <?php echo get_checked($ct_status, '배송'); ?>>
    <label for="ct_status_dvr">배송</label>
    <input type="radio" name="ct_status" value="완료" id="ct_status_done" <?php echo get_checked($ct_status, '완료'); ?>>
    <label for="ct_status_done">완료</label>
    <input type="radio" name="ct_status" value="취소" id="ct_status_cancel" <?php echo get_checked($ct_status, '취소'); ?>>
    <label for="ct_status_cancel">취소</label>
    <input type="radio" name="ct_status" value="반품" id="ct_status_return" <?php echo get_checked($ct_status, '반품'); ?>>
    <label for="ct_status_return">반품</label>
    <input type="radio" name="ct_status" value="품절" id="ct_status_soldout" <?php echo get_checked($ct_status, '품절'); ?>>
    <label for="ct_status_soldout">품절</label>
</div>

<div class="sch_last">
    <strong>정산일자</strong>
    <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
    <button type="button" onclick="javascript:set_date('오늘');">오늘</button>
    <button type="button" onclick="javascript:set_date('어제');">어제</button>
    <button type="button" onclick="javascript:set_date('이번주');">이번주</button>
    <button type="button" onclick="javascript:set_date('이번달');">이번달</button>
    <button type="button" onclick="javascript:set_date('지난주');">지난주</button>
    <button type="button" onclick="javascript:set_date('지난달');">지난달</button>
    <button type="button" onclick="javascript:set_date('전체');">전체</button>
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p>(<span style="color:red;">※주의</span>) <span style="color:red;">메인 상품 및 선택(필수)옵션 상품 판매에 대해서만 분배 금액이 할당</span>됩니다. (추가옵션상품 판매에 대해서는 수당 없음)</p>
    <p>비율(%) 설정인 경우 <?=$g5['setting']['set_rate_unit']?>단위에서 절사됩니다. ex. 155,445 > <?=number_format(share_rate_money(155445))?></p>
</div>


<form name="form01" id="form01" onsubmit="return form01_submit(this);" method="post" autocomplete="off">
<input type="hidden" name="search_ct_status" value="<?php echo $ct_status; ?>">

<div class="tbl_head01 tbl_wrap">
    <table id="sodr_list">
    <caption>주문 내역 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="2">
            <label for="chkall" class="sound_only">주문 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><a href="<?php echo title_sort("it_name", 1)."&amp;$qstr1"; ?>">상품</a></th>
        <th scope="col">판매가</th>
        <th scope="col">상태/정산일</th>
        <th scope="col" style="width:400px;">정산</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        //print_r2($row);
        //echo $row['ct_history_line'].'<br>';
        // 배송비
        switch($row['ct_send_cost'])
        {
            case 1:
                $ct_send_cost = '착불';
                break;
            case 2:
                $ct_send_cost = '무료';
                break;
            default:
                $ct_send_cost = '선불';
                break;
        }

        // 조건부무료
        if($row['it_sc_type'] == 2) {
            $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $od['od_id']);

            if($sendcost == 0)
                $ct_send_cost = '무료';
        }

        // 추가옵션 상품인 경우
		if($row['io_type'])
            $row['unit_price'] = $row['io_price'];
		// 선택(필수) 옵션 상품인 경우
        else
            $row['unit_price'] = $row['ct_price'] + $row['io_price'];

        // 소계
        $row['sub_total'] = $row['unit_price'] * $row['ct_qty'];
        $row['point_total'] = $row['ct_point'] * $row['ct_qty'];
        
        // 옵션명 = 상품명이면 두번 표시할 필요 없음
        $row['ct_option_text'] = ($row['ct_option']==$row['it_name']) ? '' : '<br>'.$row['ct_option'];
        
        // 관련건수
        $row['line_cnt_text'] = ($row['line_cnt']<=1) ? '' : ' ('.$row['line_cnt'].')';


		// 수당(매출) 리스트 -------------------------------------------------------------
		$sql4 = "	SELECT * 
					FROM {$g5['sales_table']}
					WHERE ct_id = '".$row['ct_id']."'
                        AND sls_ct_status = '".$row['ct_history_line_status']."'
                        AND sls_sales_dt = '".$row['ct_history_line_date']."'
                        AND sls_status IN ('ok') 
                    ORDER BY sls_sales_dt
		";
		//echo $sql4.'<br>';
		$rs4 = sql_query($sql4,1);
		for($y=0; $row4=sql_fetch_array($rs4); $y++) {
			$row4['sra_array'] = unserialize($row4['sls_sra_values']);
			// 장바구니 상태
			$row4['sls_ct_status_text'] = $row4['sls_ct_status'].'|';
			// 수당 종류
			$row4['sra_type_text'] = ', '.$g5['set_sra_type_value'][$row4['sra_type']];
			// 비율로 받으면 비율 표시
			$row4['sls_share_type_text'] = ($row4['sra_array']['sra_price_type']=='rate') ? ', '.$row4['sra_array']['sra_price'].'%' : '';
			// 날짜 표시
			$row4['sls_sales_dt_text'] = substr($row4['sls_sales_dt'],0,10);
			$row['sales_list_text'] .= $row4['mb_name_emp'].'(<span class="span_sales_date">'.$row4['sls_ct_status_text'].$row4['sls_sales_dt_text'].$row4['sra_type_text'].$row4['sls_share_type_text'].'</span>): '
											.number_format($row4['sls_share']).'<br>';	// 홍길동(2019-01-28, 2%): 3,250 / 홍길순(2019-01-28): 1,000... 
		}
		$row['sales_list'] = '<div class="div_sales">'.$row['sales_list_text'].'</div>';
        
        $bg = 'bg'.($i%2);
		// 입금, 준비, 배송, 완료 가 아닌 경우
        if(!in_array($row['ct_history_line_status'], $g5['set_sales_status_array']))
            $bg .= 'cancel';
    ?>
    <tr class="orderlist<?php echo ' '.$bg; ?>">
        <td class="td_chk">
            <input type="hidden" name="od_id[<?php echo $i ?>]" value="<?php echo $row['od_id'] ?>" id="od_id_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>" class="sound_only">주문번호 <?php echo $row['od_id']; ?></label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="text_top_left td_item"><!-- 상품 -->
            <b style="margin-right:10px;"><?php echo $row['it_name']; ?></b><?php echo $row['ct_option_text']; ?>
            <div class="text_ct_id">주문상품번호: <a href="?sel_field=ct_id&search=<?=$row['ct_id']?>"><?=$row['ct_id']?><?php echo $row['line_cnt_text'];?></a></div>
            <div class="text_od_id"><a href="?sel_field=od_id&search=<?=$row['od_id']?>"><?=$row['od_id']?></a></div>
        </td>
        <td class="text_top_left td_price"><!-- 판매가 -->
            <?php echo number_format($row['unit_price']); ?> ×
            <?php echo number_format($row['ct_qty']); ?> =
            <?php echo number_format($row['sub_total']); ?>
            <br>
            포인트: <?php echo number_format($row['point_total']); ?>
            <br>
            쿠폰: <?php echo number_format($row['cp_price']); ?>
            <br>
            배송비: <?php $ct_send_cost; ?>
        </td>
        <td class="text_top_left td_status"><!-- 상태/정산일 -->
            <?php echo $row['ct_history_line_status']; ?>
            <br>
            <?php echo $row['ct_history_line_date']; ?>
        </td>
        <td class="text_top_left"><!-- 정산 -->
			<?=$row['sales_list_text']?>
		</td>
        <td class="td_mng">
            <a href="./order_share.php?od_id=<?php echo $row['od_id']; ?>&ct_id=<?php echo $row['ct_id']; ?>" class="btn btn_03 btn_setting">정산</a>
            <a href="../shop_admin/orderform.php?od_id=<?php echo $row['od_id']; ?>&amp;<?php echo $qstr; ?>" class="mng_mod btn btn_02"><span class="sound_only"><?php echo $row['od_id']; ?> </span>상세</a>
        </td>
    </tr>
    <?php
    }
    sql_free_result($result);
    if ($i == 0)
        echo '<tr><td colspan="12" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function(){
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

	// 정산 버튼 클릭
	$(".btn_setting").click(function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		win_order_share = window.open(url, "win_order_share", "left=500,top=50,width=520,height=600,scrollbars=1");
        win_order_share.focus();
	});
});

function set_date(today)
{
    <?php
    $date_term = date('w', G5_SERVER_TIME);
    $week_term = $date_term + 7;
    $last_term = strtotime(date('Y-m-01', G5_SERVER_TIME));
    ?>
    if (today == "오늘") {
        document.getElementById("fr_date").value = "<?php echo G5_TIME_YMD; ?>";
        document.getElementById("to_date").value = "<?php echo G5_TIME_YMD; ?>";
    } else if (today == "어제") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME - 86400); ?>";
    } else if (today == "이번주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$date_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "이번달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', G5_SERVER_TIME); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', G5_SERVER_TIME); ?>";
    } else if (today == "지난주") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-d', strtotime('-'.$week_term.' days', G5_SERVER_TIME)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-d', strtotime('-'.($week_term - 6).' days', G5_SERVER_TIME)); ?>";
    } else if (today == "지난달") {
        document.getElementById("fr_date").value = "<?php echo date('Y-m-01', strtotime('-1 Month', $last_term)); ?>";
        document.getElementById("to_date").value = "<?php echo date('Y-m-t', strtotime('-1 Month', $last_term)); ?>";
    } else if (today == "전체") {
        document.getElementById("fr_date").value = "";
        document.getElementById("to_date").value = "";
    }
}
</script>

<script>
function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            f.action = "./order_list_delete.php";
            return true;
        }
        return false;
    }

    f.action = "./order_list_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
