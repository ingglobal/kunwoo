<?php
// 에러가 있을 때 보여주는 공백 페이지
include_once('./_common.php');

$g5['title'] = '정보 없음';
include_once('./_head.sub.php');
?>
<style>
    #mms_empty {width:100%;height:200px;line-height:200px;text-align:center;color:#818181;}
</style>

<div id="mms_empty">
    정보가 존재하지 않습니다.
</div>

<?php
include_once('./_tail.sub.php');
?>
