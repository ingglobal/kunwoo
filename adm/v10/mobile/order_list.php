<style>
#sodr_list td{text-align:left;}
.tbl_head01 thead th{background:none;border:0;color:#777;}
.tbl_head01 thead th label{position:relative;top:-5px;}
.tbl_head01 tbody td{position:relative;text-align:left;vertical-align:top;padding-right:50px;}
.tbl_head01 tbody td .td_chk{position:absolute;top:5px;right:5px;display:none;}
.tbl_head01 tbody td .mng_box{position:relative;padding:10px 10px 10px 35px;border:1px solid #dedede;}
.tbl_head01 tbody td .btn_manager{position:absolute;left:7px;top:8px;font-size:1.4em;color:#348c5b;}
.tbl_head01 tbody td .a_mod{position:absolute;right:12px;bottom:10px;font-size:1.3em;color:#348c5b;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">전체 주문내역</span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>
</div>

<form name="frmorderlist" class="local_sch01 local_sch">
<!-- 없는 변수들을 선언해 줘야 함 -->
<input type="hidden" name="od_status" value="<?php echo $od_status; ?>">
<input type="hidden" name="od_settle_case" value="<?php echo $od_settle_case; ?>">
<input type="hidden" name="od_misu" value="<?php echo $od_misu; ?>">
<input type="hidden" name="od_cancel_price" value="<?php echo $od_cancel_price; ?>">
<input type="hidden" name="od_refund_price" value="<?php echo $od_refund_price; ?>">
<input type="hidden" name="od_receipt_point" value="<?php echo $od_receipt_point; ?>">
<input type="hidden" name="od_coupon" value="<?php echo $od_coupon; ?>">
<input type="hidden" name="fr_date" value="<?php echo $fr_date; ?>">
<input type="hidden" name="to_date" value="<?php echo $to_date; ?>">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="ser_trm_idxs" value="<?php echo $ser_trm_idxs; ?>">

<label for="sel_field" class="sound_only">검색대상</label>
<?php if ($member['mb_company_yn']) { ?>
<select name="ser_trm_idxs" class="cp_field" title="부서선택">
	<option value="">전체부서</option>
	<?=$department_select_options?>
</select>
<script>$('select[name=ser_trm_idxs]').val("<?=$_GET['ser_trm_idxs']?>").attr('selected','selected');</script>
<?php } ?>
<select name="sel_field" id="sel_field">
    <option value="com_name" <?php echo get_selected($sel_field, 'com_name'); ?>>업체명</option>
    <option value="mb_name_saler" <?php echo get_selected($sel_field, 'mb_name_saler'); ?>>영업자명</option>
    <option value="mb_id_saler" <?php echo get_selected($sel_field, 'mb_id_saler'); ?>>영업자 ID</option>
    <option value="mb_id" <?php echo get_selected($sel_field, 'mb_id'); ?>>회원 ID</option>
    <option value="od_id" <?php echo get_selected($sel_field, 'od_id'); ?>>접수번호</option>
</select>

<label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="search" value="<?php echo $search; ?>" id="search" class="frm_input" autocomplete="off">
<input type="submit" value="검색" class="btn_submit">

</form>

<form class="local_sch03 local_sch">
<!-- 없는 변수들을 선언해 줘야 함 -->
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<div class="form_search_more">
    <strong>견적내역상태</strong>
    <input type="radio" name="od_status" value="" id="od_status_all" <?php echo get_checked($od_status, '');     ?>>
    <label for="od_status_all">전체</label>
    <input type="radio" name="od_status" value="견적" id="od_status_quot" <?php echo get_checked($od_status, '견적'); ?>>
    <label for="od_status_dvr">견적</label>
    <input type="radio" name="od_status" value="전송" id="od_status_send" <?php echo get_checked($od_status, '전송'); ?>>
    <label for="od_status_done">전송</label>
</div>

<div class="sch_last">
    <strong>접수일자</strong>
    <input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"> ~
    <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" class="frm_input" size="10" maxlength="10">
    <input type="submit" value="검색" class="btn_submit">
</div>
</form>

<div class="local_desc01 local_desc">
    <p><span style="color:red;">상품변경은 불가</span>합니다. 상변인 경우 전체 신청을 취소하고 다시 신청해 주세요.</p>
    <p></p>
</div>


<form name="form01" method="post" action="./order_list_update.php" onsubmit="return form01_submit(this);" autocomplete="off" id="form01">
<!-- 없는 변수들을 선언해 줘야 함 -->
<input type="hidden" name="od_status" value="<?php echo $od_status; ?>">
<input type="hidden" name="od_settle_case" value="<?php echo $od_settle_case; ?>">
<input type="hidden" name="od_misu" value="<?php echo $od_misu; ?>">
<input type="hidden" name="od_cancel_price" value="<?php echo $od_cancel_price; ?>">
<input type="hidden" name="od_refund_price" value="<?php echo $od_refund_price; ?>">
<input type="hidden" name="od_receipt_point" value="<?php echo $od_receipt_point; ?>">
<input type="hidden" name="od_coupon" value="<?php echo $od_coupon; ?>">
<input type="hidden" name="fr_date" value="<?php echo $fr_date; ?>">
<input type="hidden" name="to_date" value="<?php echo $to_date; ?>">
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="file_name" value="<?php echo $g5[file_name]; ?>">

<div class="tbl_head01 tbl_wrap">
    <table id="sodr_list">
    <caption>주문 내역 목록</caption>
    <thead>
    <!--tr>
        <th scope="col" style="text-align:right;padding-right:13px;">
            <label for="chkall">주문 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col" id="th_ordnum" colspan="2"><a href="<?php //echo title_sort("od_id", 1)."&amp;$qstr1"; ?>">주문번호</a></th>
        <th scope="col">고객명</th>
        <th scope="col" id="th_odrcnt">주문상품수</th>
        <th scope="col">주문합계</th>
        <th scope="col" id="odrstat">주문상태</th>
        <th scope="col" style="width:80px;">영업자</th>
        <th scope="col">보기</th>
    </tr-->
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        
        $bg = 'bg'.($i%2);
    ?>
    <tr class="orderlist<?php echo ' '.$bg; ?>" tr_id="<?php echo $row['od_id'] ?>">
		<td>
			<div class="td_chk">
				<input type="hidden" name="od_id[<?php echo $i ?>]" value="<?php echo $row['od_id'] ?>" id="od_id_<?php echo $i ?>">
				<label for="chk_<?php echo $i; ?>" class="sound_only">접수번호 <?php echo $row['od_id']; ?></label>
				<input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
			</div>
			<p><!-- 업체명 -->
				<div class="ordercomname"><?php echo $row['com_name']; ?></div>
				<span>견적번호 : </span><a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>" class="orderitem"><?php echo $row['od_id']; ?></a>
				<?php echo $od_mobile; ?>
				<?php echo $od_paytype; ?>
			</p>
			<p><span>항목수 : </span><?php echo $row['od_cart_count']; ?>건<span style="margin-left:10px;">금액 : </span><strong><?php echo number_format($row['od_cart_price']); ?></strong>원</p><!-- 주문합계 -->
			<p><!-- 주문상태 -->
				<input type="hidden" name="current_status[<?php echo $i ?>]" value="<?php echo $row['od_status'] ?>">
				<span>상태 : </span><?php echo $row['od_status']; ?>
				<span style="margin-left:10px;">작성자 : </span><?=$row['od_name']?>
			</p>
			<a href="./order_form.php?od_id=<?php echo $row['od_id']; ?>&<?php echo $qstr; ?>" class="a_mod"><span class="sound_only"><?php echo $row['od_id']; ?> </span><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
        </td>
    </tr>
    <?php
    }
    //sql_free_result($result);
    if ($i == 0)
        echo '<tr><td colspan="9" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<!--div class="btn_fixed_top">
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
</div-->
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function(){
    // ct_history 정보 수정 (날짜 변경)
    $(document).on('click','.ct_edit_btn',function(){
        var this_ct_id = $(this).attr('ct_id');
        var href = './order_form_cart_edit.popup.php?ct_id='+this_ct_id+'&w=1';
        cartmemowin = window.open(href, "cartmemowin", "left=100, top=100, width=600, height=650, scrollbars=yes");
        cartmemowin.focus();
        return false;
    });
    
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
    
    
    // 값이 바뀌면 체크해 준다.
//    $('input[name^="mb_id_saler["]').on('change', function() {
    $(document).on('change', 'input[id^=mb_id_saler_]', function() {
//    $("input[id^=mb_id_saler_]").change(function() {
        console.log( $(this).closest('tr').html() );
        $(this).closest('tr').prev().find('input[id^=chk_]').attr('checked','checked');
        return false;
    });
    
    
    $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    // 주문상품보기
    $(".orderitem").on("click", function() {
        var $this = $(this);
        var od_id = $this.text().replace(/[^0-9]/g, "");

        if($this.next("#orderitemlist").size())
            return false;

        $("#orderitemlist").remove();

        $.post(
            "ajax/ajax.orderitem.php",
            { od_id: od_id },
            function(data) {
                $this.after("<div id=\"orderitemlist\"><div class=\"itemlist\"></div></div>");
                $("#orderitemlist .itemlist")
                    .html(data)
                    .append("<div id=\"orderitemlist_close\"><button type=\"button\" id=\"orderitemlist-x\" class=\"btn_frmline\">닫기</button></div>");
            }
        );

        return false;
    });

    // 상품리스트 닫기
    $(".orderitemlist-x").on("click", function() {
        $("#orderitemlist").remove();
    });

    $("body").on("click", function() {
        $("#orderitemlist").remove();
    });

	// 정산 버튼 클릭
	$(".btn_setting").click(function(e) {
		e.preventDefault();
		var url = $(this).attr('href');
		win_order_share = window.open(url, "win_order_share", "left=500,top=50,width=520,height=700,scrollbars=1");
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