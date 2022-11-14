<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$admin = get_admin("super");

// 사용자 화면 우측과 하단을 담당하는 페이지입니다.
// 우측, 하단 화면을 꾸미려면 이 파일을 수정합니다.
?>
</div><!-- container End -->

<div id="ft_logo">
    <img src="<?php echo G5_DATA_URL; ?>/common/mobile_logo_img2">
</div>
 
<div id="ft">
    <h2><?php echo $config['cf_title']; ?> 정보</h2>
    <div id="ft_company" style="display:no ne;">
        <a href="<?php echo get_pretty_url('content', 'privacy'); ?>">개인정보보호정책</a>
        <a href="<?php echo get_pretty_url('content', 'provision'); ?>">이용약관</a>

    </div>
    <p>
        Copyright &copy; 2020 <?php echo $default['de_admin_company_name']; ?>. All Rights Reserved.
    </p>
 
    <?php if(G5_DEVICE_BUTTON_DISPLAY && G5_IS_MOBILE && defined('_INDEX_') && $member['mb_level']>=8) { ?>
    <a href="<?php echo get_device_change_url(); ?>" id="device_change" style="display:none;">PC 버전</a>
    <?php } ?>

    <div class="ft_icons">
        <a href="https://www.youtube.com/channel/UC3jbuDWNhfdHl05VF-rUHUg" target="_blank"><i class="fa fa-youtube-play"></i></a>
        <a href="http://www.ingglobal.net" target="_blank"><i class="fa fa-home"></i></a>
        <?php if(G5_DEVICE_BUTTON_DISPLAY && G5_IS_MOBILE && defined('_INDEX_') && $member['mb_level']>=8) { ?>
        <a href="<?php echo get_device_change_url(); ?>"><i class="fa fa-desktop"></i></a>
        <?php } ?>
    </div>

</div>

<?php
if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}

$sec = get_microtime() - $begin_time;
$file = $_SERVER['SCRIPT_NAME'];

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<script src="<?php echo G5_JS_URL; ?>/sns.js"></script>

<?php
include_once(G5_THEME_PATH.'/tail.sub.php');
?>
