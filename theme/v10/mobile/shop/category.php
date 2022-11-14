<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

?>

<div id="category" class="menu">
    <div class="menu_wr">
        <?php echo outlogin('theme/shop_basic'); // 외부 로그인 ?>
        
        <div class="btn_dash">
            <span class="sub_ct_toggle ct_op">이동</span>
            대시보드
        </div>

        <ul class="cate">
            <li class="li_gnb1">
                <a href="<?=G5_USER_URL?>/imp_list.php" class="">설비관리</a>
                <button class="sub_ct_toggle ct_op ct_cl">하위분류 닫기</button>
                <ul class="sub_cate sub_cate1">
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">계획정비</a>
                    </li>
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">정비이력</a>
                    </li>
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">부품재고</a>
                    </li>
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">메뉴얼</a>
                    </li>
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">설비도면</a>
                    </li>
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">A/S연락처</a>
                    </li>
                </ul>
            </li>
            <li class="li_gnb1">
                <a href="<?=G5_USER_URL?>/imp_list.php" class="">이상/예지 설정</a>
                <button class="sub_ct_toggle ct_op ct_cl">하위분류 닫기</button>
                <ul class="sub_cate sub_cate1">
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">알람/예지 조회</a>
                    </li>
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">알람패턴 관리</a>
                    </li>
                    <li class="li_gnb2">
                        <a href="<?=G5_USER_URL?>/imp_list.php" class="">알람분류관리</a>
                    </li>
                </ul>
            </li>
        </ul>
        
        <ul id="cate_tnb">
            <li><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=notice"><i class="fa fa-list-alt"></i> 공지사항</a></li>
            <li><a href="<?php echo G5_URL; ?>?device=pc"><i class="fa fa-laptop"></i> PC보기</a></li>
            <li style="display:no ne;"><a href="<?php echo G5_USER_ADMIN_URL; ?>/"><i class="fa fa-question"></i>관리자</a></li>
            <li style="display:no ne;"><a href="<?php echo G5_BBS_URL; ?>/qalist.php"><i class="fa fa-comments"></i>1:1문의</a></li>
        </ul> 
    </div>
</div>
<script>
jQuery(function ($){

    $("button.sub_ct_toggle").on("click", function() {
        var $this = $(this);
        $sub_ul = $(this).closest("li").children("ul.sub_cate");

        if($sub_ul.size() > 0) {
            var txt = $this.text();

            if($sub_ul.is(":visible")) {
                txt = txt.replace(/닫기$/, "열기");
                $this
                    .removeClass("ct_cl")
                    .text(txt);
            } else {
                txt = txt.replace(/열기$/, "닫기");
                $this
                    .addClass("ct_cl")
                    .text(txt);
            }

            $sub_ul.toggle();
        }
    });
});
</script>
