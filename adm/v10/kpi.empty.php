<?php
// 에러가 있을 때 보여주는 공백 페이지
include_once('./_common.php');

$g5['title'] = '정보 없음';
include_once('./_head.sub.php');
?>
<style>
    #mms_empty {width:100%;height:200px;line-height:200px;text-align:center;color:#818181;}
    #empty_text {margin-top:80px;line-height:2em;}
</style>

<div id="mms_empty">
    <div id="empty_text" style="display:<?=($text)?'block':'none'?>">
        보고서 결과가 없습니다.<br>기간 및 범위를 선택하신 후 [확인] 버튼을 클릭해 주세요.
    </div>
</div>

<?php
include_once('./_tail.sub.php');
?>
