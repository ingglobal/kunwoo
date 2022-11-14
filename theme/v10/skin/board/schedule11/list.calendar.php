<?php
// URL: G5_URL/theme/v10/skin/board/schedule11/list.calendar.php
include_once('./_common.php');

$g5['title'] = $board['bo_subject'].' 달력';
include_once('./_head.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);

//print_r2($board);
// 디폴트값 설정
$month = ($month)? $month:date('Ym', G5_SERVER_TIME);
$_month = substr($month,0,4).'-'.substr($month,-2);
?>

<!-- 달력 시작 { -->
<div class="calendar">
    <div class="calendar_title">
        <a href="<?php echo $board_skin_url?>/list.calendar.php?bo_table=<?php echo $bo_table?>" class="btn_mode btn_calendar_skin"><i class="fa fa-calendar"></i> 달력</a>
        <a href="<?php echo G5_BBS_URL?>/board.php?bo_table=<?php echo $bo_table?>" class="btn_mode btn_list_skin"><i class="fa fa-list-alt"></i> 리스트</a>

        <a href="javascript:" class="prev_month" cal_val="-1" title="이전달"><i class="fa fa-arrow-circle-left"></i></a>
        <span class="this_month"><?=$_month?></span>
        <a href="javascript:" class="next_month" cal_val="+1" title="다음달"><i class="fa fa-arrow-circle-right"></i></a>

        <?php if ($member['mb_level']>=$board['bo_write_level']) { ?>
        <a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=<?php echo $bo_table;?>" class="btn_mode btn_write">
            <i class="fa fa-plus" aria-hidden="true"></i>
            <span>등록</span>
        </a>
        <?php } ?>

    </div>
	<div class="caution" style="display:<?php if(!$board['set_max_time_apply']&&!$board['set_max_apply']) echo 'none';?>;">
        동일시간대 예약가능 인원은 <span style=""><?php echo $board['set_max_time_apply'];?></span>명까지, 당일 예약가능 인원은 총 <span style=""><?php echo $board['set_max_apply'];?></span>명까지입니다.
	</div>
    <div class="div_calendar">
        <table class="table_calendar">
        <thead>
        <tr>
            <th class="th_sunday">일</th>
            <th>월</th>
            <th>화</th>
            <th>수</th>
            <th>목</th>
            <th>금</th>
            <th class="th_saturday">토</th>
        </tr>
        </thead>
        <tbody><!-- 달력 리스트 --></tbody>
        </table>
    </div>
</div>
<!-- } 달력 종료 -->


<script>
var g5_board_skin_url = '<?php echo $board_skin_url ?>';
var g5_board_config = 0;
</script>
<script src="<?=$board_skin_url?>/calendar.js" type="text/javascript" charset="utf-8"></script>


<?php
include_once('./_tail.php');
?>