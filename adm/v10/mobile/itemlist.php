<style>
#flist{position:relative;}
#flist .btn_s_cart{height:30px;line-height:30px;border:1px solid #ddd;background:#efefef;position:absolute;top:0;right:0;}
</style>
<form name="flist" id="flist" class="local_sch01 local_sch">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<label for="sca" class="sound_only">분류선택</label>
<select name="sca" id="sca" style="width:20%;position:relative;top:2px;">
    <option value="">전체분류</option>
    <?php
    $sql1 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} order by ca_order, ca_id ";
    $result1 = sql_query($sql1);
    for ($i=0; $row1=sql_fetch_array($result1); $i++) {
        $len = strlen($row1['ca_id']) / 2 - 1;
        $nbsp = '';
        for ($i=0; $i<$len; $i++) $nbsp .= '&nbsp;&nbsp;&nbsp;';
        echo '<option value="'.$row1['ca_id'].'" '.get_selected($sca, $row1['ca_id']).'>'.$nbsp.$row1['ca_name'].'</option>'.PHP_EOL;
    }
    ?>
</select>

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl" style="width:20%;position:relative;top:2px;">
    <option value="it_name" <?php echo get_selected($sfl, 'it_name'); ?>>부품명</option>
    <option value="it_id" <?php echo get_selected($sfl, 'it_id'); ?>>부품코드</option>
</select>

<label for="stx" class="sound_only">검색어</label>
<input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" class="frm_input" style="margin-top:5px;width:20%;">
<input type="submit" value="검색" class="btn_submit" style="margin-top:5px;">
<!--a href="./order_cart.php" class="btn btn_s_cart">견적목록보기</a-->
<div style="text-align:right;position:absolute;bottom:-5px;right:5px;">
    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
    <label for="chkall">부품 전체</label>
</div>
</form>

<!--div class="local_desc01 local_desc">
    <p>[담기] 버튼을 클릭하면 부품이 장바구니에 담깁니다. <a href="./order_cart.php">[장바구니 바로가기]</a> 장바구니에 담긴 부품들을 가격 조정하거나 혹은 수량을 조절한 후 견적을 따로 진행할 수 있습니다.</p>
</div-->

<form name="fitemlistupdate" method="post" action="./itemlistupdate.php" onsubmit="return fitemlist_submit(this);" autocomplete="off" id="fitemlistupdate">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div class="tbl_head01 tbl_wrap">
    <table class="mtbl">
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <?php if($member['mb_manager_yn']){ ?>
    <thead>
    </thead>
    <?php } ?>
    <tbody>
<style>
.mtbl td{text-align:left;}
.mtbl label{font-size:1em;display:inline-block;width:50px;}
.mtbl .sound_only{display:none;}
.mtbl select{width:100%;max-width:200px;}
.mtbl input[type="text"]{width:100%;max-width:200px;padding-left:5px;padding-right:5px;}
.mtbl div[class^="td_"]{margin-top:5px;}
.td_id{text-align:left;}
.td_ca{text-align:left;}
.td_price{text-align:left !important;}
.td_buy_price{text-align:left !important;}
.td_stock_qty{text-align:left !important;}
.td_buyer{text-align:left !important;}
.td_btn{position:absolute;bottom:5px;right:5px;width:30px;}
.td_btn .btn{font-size:0;width:30px;margin-top:5px;background:none;}
.td_btn .itemmodify{background-image:url(https://icongr.am/fontawesome/pencil-square-o.svg?size=128&color=348c5b);background-size:auto 66%;background-position:center center;background-repeat:no-repeat;background-color:none;}
.td_btn .itemcopy{background-image:url(https://icongr.am/feather/copy.svg?size=128&color=777777);background-size:auto 66%;background-position:center center;background-repeat:no-repeat;}
.td_btn .itemcart{background-image:url(https://icongr.am/feather/shopping-cart.svg?size=128&color=ffffff) !important;background-size:auto 66% !important;background-position:center center !important;background-repeat:no-repeat !important;}
</style>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $href = shop_item_url($row['it_id']);
        $bg = 'bg'.($i%2);
        // print_r2($row);

        $it_point = $row['it_point'];
        if($row['it_point_type'])
            $it_point .= '%';
    ?>
    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['it_id'] ?>">
        <td>
            <div class="td_chk">
                <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['it_name']); ?></label>
                <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i; ?>">
            </div>
            <div class="td_id"><?php echo $row['it_id']; ?></div>
            <div class="td_ca">
                <label for="ca_id_<?php echo $i; ?>">항목명</label>
                <select name="ca_id[<?php echo $i; ?>]" id="ca_id_<?php echo $i; ?>">
                    <?php echo conv_selected_option($ca_list, $row['ca_id']); ?>
                </select>
            </div>
            <div class="td_itname">
                <label for="name_<?php echo $i; ?>">부품명</label>
                <input type="text" name="it_name[<?php echo $i; ?>]" value="<?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?>" id="name_<?php echo $i; ?>" required class="tbl_input required" size="30"><br>
                <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
            </div>
            <div class="td_price">
                <label for="price_<?php echo $i; ?>">판매가</label>
                <input type="text" name="it_price[<?php echo $i; ?>]" value="<?php echo $row['it_price']; ?>" id="price_<?php echo $i; ?>" class="tbl_input sit_amt" size="7">
            </div>
            <div class="td_buy_price">
                <label for="cust_price_<?php echo $i; ?>">매입가</label>
                <input type="text" name="it_buy_price[<?php echo $i; ?>]" value="<?php echo $row['it_buy_price']; ?>" id="cust_price_<?php echo $i; ?>" class="tbl_input sit_camt" size="7">
            </div>
            <div class="td_buyer">
                <label for="com_id_<?php echo $i; ?>">매입처</label>
                <select name="com_id[<?php echo $i; ?>]" id="com_id_<?php echo $i; ?>">
                    <?=$g5['set_buyer_value_options']?>
                </select>
                <script>$('select[name="com_id[<?php echo $i; ?>]"]').val('<?=$row['com_idx']?>');</script>
            </div>
            <div class="td_stock_qty">
                <label for="stock_qty_<?php echo $i; ?>">재고</label>
                <input type="text" name="it_stock_qty[<?php echo $i; ?>]" value="<?php echo $row['it_stock_qty']; ?>" id="stock_qty_<?php echo $i; ?>" class="tbl_input sit_qty" size="7">
            </div>
            <div class="td_btn">
                <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>&amp;<?php echo $qstr; ?>" class="btn itemmodify"><span class="sound_only"><?php echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>수정</a>
                <!--a href="./itemcopy.php?it_id=<?php echo $row['it_id']; ?>&amp;ca_id=<?php echo $row['ca_id']; ?>" class="itemcopy btn btn_02" target="_blank"><span class="sound_only"><?php //echo htmlspecialchars2(cut_str($row['it_name'],250, "")); ?> </span>복사</a-->
                <!--a href="javascript:" it_id="<?php //echo $row['it_id']; ?>" class="itemcart btn btn_01">담기</a-->
            </div>
        </td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="4" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <!--a href="./itemlist_excel_down.php?<?=$qstr?>" id="btn_excel_down" class="btn btn_03">엑셀다운</a-->
    <a href="javascript:" id="btn_excel_upload2" class="btn btn_03" style="display:none;">엑셀등록(비표준)</a>
    <a href="javascript:" id="btn_excel_upload" class="btn btn_03" style="margin-right:20px;display:none;">엑셀등록</a>
    <!--input type="submit" name="act_button" value="선택담기" onclick="document.pressed=this.value" class="btn btn_02"-->
    <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02">
    <?php if ($is_admin == 'super') { ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>
    <a href="./itemform.php" id="btn_add" class="btn btn_01">부품등록</a>
</div>
<!-- <div class="btn_confirm01 btn_confirm">
    <input type="submit" value="일괄수정" class="btn_submit" accesskey="s">
</div> -->
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<div id="modal01" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./itemlist_excel_upload.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>
<div id="modal02" title="엑셀 파일 업로드" style="display:none;">
    <form name="form02" id="form02" action="./itemlist_excel_upload2.php" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
        <table>
        <tbody>
        <tr>
            <td style="line-height:130%;padding:10px 0;">
                <ol>
                    <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                    <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <input type="file" name="file_excel" onfocus="this.blur()">
            </td>
        </tr>
        <tr>
            <td style="padding:15px 0;">
                <button type="submit" class="btn btn_01">확인</button>
            </td>
        </tr>
        </tbody>
        </table>
    </form>
</div>

<script>
function fitemlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}

// 엑셀등록 실행
function form02_submit(f) {
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}

$(function() {
    // 엑셀등록 버튼
    $( "#modal01" ).dialog({
        autoOpen: false
        , position: { my: "right-40 top-10", of: "#btn_excel_upload"}
    });
    $( "#modal02" ).dialog({
        autoOpen: false
        , position: { my: "right-40 top-10", of: "#btn_excel_upload2"}
    });
    $( "#btn_excel_upload" ).on( "click", function() {
        $( "#modal01" ).dialog( "open" );
    });
    $( "#btn_excel_upload2" ).on( "click", function() {
        $( "#modal02" ).dialog( "open" );
    });

    $(".itemcopy").click(function() {
        var href = $(this).attr("href");
        window.open(href, "copywin", "left=100, top=100, width=300, height=200, scrollbars=0");
        return false;
    });

    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
            
        },
        mouseleave: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });

    // 선택한 상품을 장바구니에 추가한다. ajax처리 (주문 정보도 함께 처리해야 함)
    $(".itemcart").click(function(e) {
        e.preventDefault();
        var it_id = $(this).attr('it_id');
        $.ajax({
        	url:g5_user_admin_url+'/ajax/cart.json.php',
        	type:'get', data:{"aj":"put","it_id":it_id},
        	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res) {
                //alert(res.sql);
                if(res.result == true) {
                    alert("장바구니 담기 완료, 담긴 부품은 장바구니에서 확인하세요.");
                }
                else {
                    alert(res.msg);
                }				
            }, error:this_ajax_error	////-- 디버깅 Ajax --//
        });

    });

});

function excelform(url)
{
    var opt = "width=600,height=450,left=10,top=10";
    window.open(url, "win_excel", opt);
    return false;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>