<style>
.tbl_wrap ul{}
.tbl_wrap ul li{padding:10px;border-bottom:1px solid #ddd;}
.in_row{position:relative;padding-right:50%;}
.in_row:after{display:block;visibility:hidden;clear:both;content:'';}
.in_col{}
.in_col_r{position:absolute;top:0;right:0;bottom:0;width:50%;}
</style>
<form name="fitemform" action="./itemformupdate.php" method="post" enctype="MULTIPART/FORM-DATA" autocomplete="off" onsubmit="return fitemformcheck(this)">

<input type="hidden" name="codedup" value="<?php echo $default['de_code_dup_use']; ?>">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod"  value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx"  value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<section id="anc_sitfrm_ini">
    <div class="tbl_frm01 tbl_wrap">
        <ul>
            <li>
				<label for="ca_id" class="sound_only">분류</label>
                <select name="ca_id" id="ca_id" onchange="categorychange(this.form)">
                    <option value="">분류선택</option>
                    <?php echo conv_selected_option($category_select, $it['ca_id']); ?>
                </select>
                <script>
                    var ca_use = new Array();
                    var ca_stock_qty = new Array();
                    //var ca_explan_html = new Array();
                    var ca_sell_email = new Array();
                    var ca_opt1_subject = new Array();
                    var ca_opt2_subject = new Array();
                    var ca_opt3_subject = new Array();
                    var ca_opt4_subject = new Array();
                    var ca_opt5_subject = new Array();
                    var ca_opt6_subject = new Array();
                    <?php echo "\n$script"; ?>
                </script>
            </li>
            <li>
				<label for="it_id">부품코드</label>
                <?php if ($w == '') { // 추가 ?>
                    <!-- 최근에 입력한 코드(자동 생성시)가 목록의 상단에 출력되게 하려면 아래의 코드로 대체하십시오. -->
                    <!-- <input type=text class=required name=it_id value="<?php echo 10000000000-time()?>" size=12 maxlength=10 required> <a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a> -->
                    <?php echo help("부품의 코드는 10자리 숫자로 자동생성합니다."); ?>
                    <input type="text" name="it_id" value="<?php echo time(); ?>" id="it_id" required class="frm_input required" size="20" maxlength="20">
                    <!-- <?php if ($default['de_code_dup_use']) { ?><button type="button" class="btn_frmline" onclick="codedupcheck(document.all.it_id.value)">중복검사</a><?php } ?> -->
                <?php } else { ?>
                    <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
                    <span class="frm_ca_id"><?php echo $it['it_id']; ?></span>
                <?php } ?>
            </li>
            <li>
				<label for="it_maker" class="sound_only">매입처</label>
                <?php //echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
                <select name="com_idx" id="com_idx">
                    <option value="">++매입처선택++</option>
                    <?=$g5['set_buyer_value_options']?>
                </select>
                <script>$('select[name="com_idx"]').val('<?=$it['com_idx']?>');</script>
            </li>
            <li>
				<label for="it_name" class="sound_only">부품명</label>
                <input type="text" name="it_name" value="<?php echo get_text(cut_str($it['it_name'], 250, "")); ?>" placeholder="부품명" id="it_name" required class="frm_input required" style="width:100%;">
            </li>
            <li>
				<label for="it_basic" class="sound_only">간단설명</label>
                <input type="text" name="it_basic" value="<?php echo get_text(html_purifier($it['it_basic'])); ?>" placeholder="간단설명" id="it_basic" class="frm_input" style="width:100%;">
            </li>
            <li>
                <div class="in_row">
                    <div class="in_col in_col_l">
                        <label for="it_price" class="sound_only">판매가격</label>
                        <input type="text" name="it_price" value="<?php echo $it['it_price']; ?>" placeholder="판매가격" id="it_price" class="frm_input" size="8" style="text-align:right;"> 원
                    </div>
                    <div class="in_col in_col_r">
                        <label for="it_buy_price" class="sound_only">매입가격</label>
                        <input type="text" name="it_buy_price" value="<?php echo $it['it_buy_price']; ?>" placeholder="매입가격" id="it_buy_price" class="frm_input" size="8" style="text-align:right;"> 원
                    </div>
                </div>
            </li>
            <li>   
                <div class="in_row">
                    <div class="in_col in_col_l">
                        <?php //echo help("<b>주문관리에서 부품별 상태 변경에 따라 자동으로 재고를 가감합니다.</b> 재고는 규격/색상별이 아닌, 부품별로만 관리됩니다.<br>재고수량을 0으로 설정하시면 품절부품으로 표시됩니다."); ?>
                        <label for="it_stock_qty" class="sound_only">재고수량</label>
                        <input type="text" name="it_stock_qty" value="<?php echo $it['it_stock_qty']; ?>" placeholder="재고수량" id="it_stock_qty" class="frm_input" size="8" style="text-align:right;"> 개
                    </div>
                    <div class="in_col in_col_r">
                        <label for="it_notax" class="sound_only">부품과세 유형</label>
                        <?php //echo help("부품의 과세유형(과세, 비과세)을 설정합니다."); ?>
                        <select name="it_notax" id="it_notax">
                            <option value="0"<?php echo get_selected('0', $it['it_notax']); ?>>과세</option>
                            <option value="1"<?php echo get_selected('1', $it['it_notax']); ?>>비과세</option>
                        </select>
                    </div>
                </div>
            </li>
            <li>
                <div class="in_row">
                    <div class="in_col in_col_l">
                        <label for="it_order">출력순서</label>
                        <?php //echo help("숫자가 작을 수록 상위에 출력됩니다."); ?>
                        <input type="text" name="it_order" value="<?php echo $it['it_order']; ?>" id="it_order" class="frm_input" size="12">
                    </div>
                    <div class="in_col in_col_r">
                        <label for="it_use">견적가능</label><br>
                        <?php //echo help("잠시 중단하거나 재고가 없을 경우에 체크를 해제해 놓으면 출력되지 않으며, 견적할 수 없는 상품이 됩니다."); ?>
                        <input type="checkbox" name="it_use" value="1" id="it_use" <?php echo ($it['it_use']) ? "checked" : ""; ?>> 예
                    </div>
                </div>
            </li>
            <li>
				<label for="it_shop_memo" class="sound_only">메모</label>
				<textarea name="it_shop_memo" id="it_shop_memo" placeholder="메모"><?php echo html_purifier($it['it_shop_memo']); ?></textarea>
			</li>
        </ul>
    </div>
</section>


<div class="btn_fixed_top">
    <a href="./itemlist.php?<?php echo $qstr; ?>" class="btn btn_02"><i class="fa fa-list" aria-hidden="true"></i></a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>


<script>
var f = document.fitemform;

<?php if ($w == 'u') { ?>
$(".banner_or_img").addClass("sit_wimg");
$(function() {
    $(".sit_wimg_view").bind("click", function() {
        var sit_wimg_id = $(this).attr("id").split("_");
        var $img_display = $("#"+sit_wimg_id[1]);

        $img_display.toggle();

        if($img_display.is(":visible")) {
            $(this).text($(this).text().replace("확인", "닫기"));
        } else {
            $(this).text($(this).text().replace("닫기", "확인"));
        }

        var $img = $("#"+sit_wimg_id[1]).children("img");
        var width = $img.width();
        var height = $img.height();
        if(width > 700) {
            var img_width = 700;
            var img_height = Math.round((img_width * height) / width);

            $img.width(img_width).height(img_height);
        }
    });
    $(".sit_wimg_close").bind("click", function() {
        var $img_display = $(this).parents(".banner_or_img");
        var id = $img_display.attr("id");
        $img_display.toggle();
        var $button = $("#it_"+id+"_view");
        $button.text($button.text().replace("닫기", "확인"));
    });
});
<?php } ?>

function codedupcheck(id)
{
    if (!id) {
        alert('부품코드를 입력하십시오.');
        f.it_id.focus();
        return;
    }

    var it_id = id.replace(/[A-Za-z0-9\-_]/g, "");
    if(it_id.length > 0) {
        alert("부품코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.");
        return false;
    }

    $.post(
        "./codedupcheck.php",
        { it_id: id },
        function(data) {
            if(data.name) {
                alert("코드 '"+data.code+"' 는 '".data.name+"' (으)로 이미 등록되어 있으므로\n\n사용하실 수 없습니다.");
                return false;
            } else {
                alert("'"+data.code+"' 은(는) 등록된 코드가 없으므로 사용하실 수 있습니다.");
                document.fitemform.codedup.value = '';
            }
        }, "json"
    );
}

function fitemformcheck(f)
{
    if (!f.ca_id.value) {
        alert("기본분류를 선택하십시오.");
        f.ca_id.focus();
        return false;
    }

    if (f.w.value == "") {
        var error = "";
        $.ajax({
            url: "./ajax.it_id.php",
            type: "POST",
            data: {
                "it_id": f.it_id.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                error = data.error;
            }
        });

        if (error) {
            alert(error);
            return false;
        }
    }

    if(f.it_point_type.value == "1" || f.it_point_type.value == "2") {
        var point = parseInt(f.it_point.value);
        if(point > 99) {
            alert("포인트 비율을 0과 99 사이의 값으로 입력해 주십시오.");
            return false;
        }
    }

    if(parseInt(f.it_sc_type.value) > 1) {
        if(!f.it_sc_price.value || f.it_sc_price.value == "0") {
            alert("기본배송비를 입력해 주십시오.");
            return false;
        }

        if(f.it_sc_type.value == "2" && (!f.it_sc_minimum.value || f.it_sc_minimum.value == "0")) {
            alert("배송비 상세조건의 주문금액을 입력해 주십시오.");
            return false;
        }

        if(f.it_sc_type.value == "4" && (!f.it_sc_qty.value || f.it_sc_qty.value == "0")) {
            alert("배송비 상세조건의 주문수량을 입력해 주십시오.");
            return false;
        }
    }

    // 관련부품처리
    var item = new Array();
    var re_item = it_id = "";

    $("#reg_relation input[name='re_it_id[]']").each(function() {
        it_id = $(this).val();
        if(it_id == "")
            return true;

        item.push(it_id);
    });

    if(item.length > 0)
        re_item = item.join();

    $("input[name=it_list]").val(re_item);

    // 이벤트처리
    var evnt = new Array();
    var ev = ev_id = "";

    $("#reg_event_list input[name='ev_id[]']").each(function() {
        ev_id = $(this).val();
        if(ev_id == "")
            return true;

        evnt.push(ev_id);
    });

    if(evnt.length > 0)
        ev = evnt.join();

    $("input[name=ev_list]").val(ev);

    <?php echo get_editor_js('it_head_html'); ?>
    <?php echo get_editor_js('it_tail_html'); ?>
    <?php echo get_editor_js('it_mobile_head_html'); ?>
    <?php echo get_editor_js('it_mobile_tail_html'); ?>

    return true;
}

function categorychange(f)
{
    var idx = f.ca_id.value;

    if (f.w.value == "" && idx)
    {
        f.it_use.checked = ca_use[idx] ? true : false;
        f.it_stock_qty.value = ca_stock_qty[idx];
        f.it_sell_email.value = ca_sell_email[idx];
    }
}

categorychange(document.fitemform);
</script>

<?php
include_once ('./_tail.php');
?>