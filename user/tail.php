<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
//모달관련
@include_once(G5_USER_PATH.'/modal/'.$g5['file_name'].'_modal.php');
?>
<script>
var interval = setInterval(page_move,<?=$reload?>);

function page_move(){
    location.href='<?=G5_USER_URL?>/worker_plan.php?start_date=<?=$first_date?>&end_date=<?=$last_date?>';
}
</script>
</body>
</html>