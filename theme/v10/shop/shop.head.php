<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$q = isset($_GET['q']) ? clean_xss_tags($_GET['q'], 1, 1) : '';

if(G5_IS_MOBILE) {
    include_once(G5_THEME_MSHOP_PATH.'/shop.head.php');
    return;
}

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
include_once(G5_LIB_PATH.'/latest.lib.php');

add_javascript('<script src="'.G5_JS_URL.'/owlcarousel/owl.carousel.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/owlcarousel/owl.carousel.css">', 0);
// print_r2($member);
?>

<!-- 상단 시작 { -->
<div id="hd">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>
    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <?php if(defined('_INDEX_')) { // index에서만 실행
        include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
	} ?>
</div>
<!-- } 상단 끝 -->
<?php
$idx_bg = '';
$wrapper_idx = '';
if(defined('_INDEX_') || $g5['file_name'] == 'register_result'){
    $idx_bg = G5_THEME_IMG_URL.'/index_bg5.jpg';
    $wrapper_idx = 'wrapper_idx';
}
?>
<!-- 전체 콘텐츠 시작 { -->
<div id="wrapper" class="<?=$wrapper_idx?>" style="background-image:url(<?=$idx_bg?>);background-color:#58b5d7;background-repeat:no-repeat;background-position:center;background-size:cover;">
    <!-- #container 시작 { -->
    <div id="container">