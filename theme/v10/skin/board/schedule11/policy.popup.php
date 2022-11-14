<?php
include_once('./_common.php');

$g5['title'] = '약관 상세보기';
include_once(G5_PATH.'/head.sub.php');

//print_r2($_REQUEST);

// 게시물 정보
//print_r2($board);

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>
<style>
</style>

<div id="menu_frm" class="new_win">
    <h1 id="win_title"><?php echo $g5['title']; ?></h1>
    <div class="new_win_con">

        <div class="win_content">
            <?php echo conv_content(stripslashes(base64_decode($board['set_policy_content'])),2) ?>
        </div>
    
        <div class="win_btn">
            <button type="button" onclick="window.close();" class="btn_close">창닫기</button>
        </div>
        
    </div>
</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>