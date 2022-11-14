<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<script src="<?=G5_USER_ADMIN_URL?>/js/bbs_function.js"></script>
<script src="<?=G5_USER_ADMIN_URL?>/js/bbs_common.js"></script>
<?php if(is_file(G5_USER_ADMIN_PATH.'/js/'.$g5['file_name'].'.js')){ ?>
<script src="<?=G5_USER_ADMIN_URL?>/js/<?=$g5['file_name']?>.js"></script>
<?php } ?>