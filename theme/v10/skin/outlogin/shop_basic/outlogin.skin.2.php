<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$outlogin_skin_url.'/style.css">', 0);
?>

<!-- 로그인 후 아웃로그인 시작 { -->
<section id="ol_after">
    <h2>
        <img src="<?=G5_THEME_IMG_URL?>/logo_bright3.png">
        <p>제조기업예측제어시스템<br>v 1.02</p>
    </h2>
    <p>
        <strong><?php echo $nick ?>님</strong> 승인대기 상태입니다.<br>조금만 기다려 주세요.<br>신속 승인이 필요한 경우 관리자에게 문의하세요.
    </p>
    <footer id="ol_after_ft">
        <a href="<?php echo G5_BBS_URL ?>/logout.php" id="ol_after_logout">로그아웃</a>
    </footer>
</section>

<script>
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
        location.href = "<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php";
}
</script>
<!-- } 로그인 후 아웃로그인 끝 -->
