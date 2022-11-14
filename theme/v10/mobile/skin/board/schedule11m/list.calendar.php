<?php
// URL: G5_URL/theme/v10/skin/board/schedule11m/list.calendar.php
include_once('./_common.php');

//print_r2($board);
// 디폴트값 설정
$month = ($month)? $month:date('Ym', G5_SERVER_TIME);
$_month = substr($month,0,4).'-'.substr($month,-2);

$g5['title'] = $_month;
include_once('./_head.php');
?>

<div id="search_wrapper">
<fieldset id="bo_sch">
    <legend>검색</legend>
    <form name="fsearch" method="get">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sop" value="and">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="com_name"<?php echo get_selected($_GET['sfl'], "com_name"); ?>>업체명</option>
        <option value="mb_name"<?php echo get_selected($_GET['sfl'], "mb_name"); ?>>담당자명</option>
        <option value="crt_code"<?php echo get_selected($_GET['sfl'], "crt_code"); ?>>인증규격</option>
        <option value="crt_idx"<?php echo get_selected($_GET['sfl'], "crt_idx"); ?>>고유번호</option>
    </select>
    <input name="stx" value="<?php echo stripslashes($stx) ?>" placeholder="검색어를 입력하세요" required id="stx" class="sch_input" size="15" maxlength="20">
    <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i> <span class="sound_only">검색</span></button>
    <button type="submit" value="닫기" class="sch_btn_close"><i class="fa fa-times" aria-hidden="true"></i> <span class="sound_only">닫기</span></button>
    </form>
</fieldset>
</div>

<!-- 달력 시작 { -->
<div class="calendar">
    <div class="calendar_title" style="display:none;">
        <span class="this_month"><?=$_month?></span>
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

    <!-- 이달의 일정 { -->
    <div class="div_list">
        <div class="list_title">
            <span class="list_all"><?=$_month?></span>
        </div>
        <?php
        //////////////////////////////////////////////////////////////////////////////
        // 공휴일 추출
        $sql1 = "   SELECT *
                    FROM g5_5_ymd
                    WHERE ymd BETWEEN '".$month."01' AND '".$month."31'
                        AND ymd_more != ''
        ";
//        echo $sql1;
        $rs1 = sql_query($sql1,1);
        for ($i=0; $row=sql_fetch_array($rs1); $i++) {
            //echo $row['ymd_date'].'<br>';	// 2018-02-03
            $row['dates'] = explode("-",$row['ymd_date']);    // 날짜값 분리 배열
            $row['day_no'] = number_format($row['dates'][2]);    // 날짜만 숫자로

            // 해당 날짜의 개별 설정 unserialize 추출
            if($row['ymd_more']) {
                $unser = unserialize(stripslashes($row['ymd_more']));
                if( is_array($unser) && substr($row['ymd_date'],0,7) == $_month ) {
                    foreach ($unser as $key=>$value) {
                        $row[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_NOQUOTES); // " 와 ' 를 html code 로 변환
                    }    
                }
            }

            $day_content[$row['ymd_date']][] = array(
                "item_date" => $row['ymd_date']
                , "item_type" => 'holiday'
                , "holiday_name" => $row['holiday_name']
            );
        }
    
    
        // 만료 인증건 추출 [ ======================================================
        $sql = "SELECT * FROM (
                    SELECT crt_idx, crt.com_idx, mb_id, crt_code, crt_certify_date, crt_status, com_name
                        , date_add(crt_certify_date, interval +10 month) AS plus10m
                        , date_add(crt_certify_date, interval +11 month) AS plus11m
                        , date_add(crt_certify_date, interval +358 day) AS plus358d
                    FROM {$g5['certify_table']} AS crt
                        LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = crt.com_idx
                    WHERE crt_status NOT IN ('trash','delete')
                ) AS db1
                WHERE plus10m BETWEEN '{$_month}-01' AND '{$_month}-31'
                    OR plus11m BETWEEN '{$_month}-01' AND '{$_month}-31'
                    OR plus358d BETWEEN '{$_month}-01' AND '{$_month}-31'
                ORDER BY com_name
        ";
//        echo $sql.'<br>';
        $result = sql_query($sql,1);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 어떤 날짜가 해당 항목인지 추출(user.07.intra.default.php 내부설정)
            if(is_array($crt_items)) {
                foreach($crt_items as $key=>$val) {
//                    echo $row[$key].': '.$val.'<br>';
                    // 이번 달인 경우만 표현
                    if( substr($row[$key],0,7) == $_month ) {
                        // 업체명 & 담당자 추출
                        $sql2 = "   SELECT * 
                                    FROM {$g5['company_member_table']} AS cmm
                                        LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = cmm.mb_id
                                    WHERE cmm_status NOT IN ('trash','delete')
                                        AND cmm.com_idx = '".$row['com_idx']."'
                                        AND cmm.mb_id = '".$row['mb_id']."'
                                    ORDER BY cmm_reg_dt DESC
                                    LIMIT 1
                        ";
//                      echo $sql2.'<br>';
                        $row['cmm'] = sql_fetch($sql2,1);
//                      print_r2($row['cmm']);
                        $row['mb_rank'] = ($row['cmm']['cmm_title'])?' '.$g5['set_mb_ranks_value'][$row['cmm']['cmm_title']]:'';

                        
                        $day_expire[$row[$key]] .= $row['com_name'].' '.$val.'<br>';
//                        echo $row[$key].': '.$val.' ---- <br>';
                        $row['href'] = G5_USER_URL.'/certify_list.php?sfl=crt_idx&stx='.$row['crt_idx'];
                        $day_content[$row[$key]][] = array(
                            "item_date" => $row[$key]
                            , "item_type" => 'crt'
                            , "certify_status" => $row['crt_status']
                            , "certify_date" => $row['crt_certify_date']
                            , "certify_text" => $val
                            , "certify_code_name" => $g5['set_crt_codes_value'][$row['crt_code']]
                            , "certify_link" => G5_USER_URL.'/certify_list.php?sfl=crt_idx&stx='.$row['crt_idx']
                            , "certify_com_name" => $row['com_name']
                            , "mb_name_rank" => $row['cmm']['mb_name'].$row['mb_rank']
                        );
                    }
                }
            }
        }
//        print_r2($day_expire);
        // ] 만료 인증건 추출 ======================================================
        

        // 심사일자 추출
        $sql1 = "   SELECT *
                    FROM {$g5['certify_judge_table']} AS crj
                        LEFT JOIN {$g5['certify_table']} AS crt ON crt.crt_idx = crj.crt_idx
                        LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = crt.com_idx
                        LEFT JOIN {$g5['member_table']} AS mb ON mb.mb_id = crj.mb_id
                    WHERE crj_date BETWEEN '".$_month."-01' AND '".$_month."-31'
                        AND crj_status NOT IN ('trash','delete')
                    ORDER BY crj_date
        ";
//        echo $sql1;
        $rs1 = sql_query($sql1,1);
        for ($i=0; $row=sql_fetch_array($rs1); $i++) {
            //echo $row['crj_date'].'<br>';	// 2018-02-03

            $day_content[$row['crj_date']][] = array(
                "item_date" => $row['crj_date']
                , "item_type" => 'judge'
                , "com_idx" => $row['com_idx']
                , "com_name" => $row['com_name']
                , "crt_code_name" => $g5['set_crt_codes_value'][$row['crt_code']]
                , "crj_type_name" => $g5['set_crj_type_value'][$row['crj_type']]
                , "mb_name" => $row['mb_name']
                , "mb_name_rank" => $row['mb_name'].' '.$g5['set_mb_ranks_value'][$row['mb_3']]
            );
        }
    
           
           
        // 일정리스트 { -------
        $sql  = "SELECT *
                    FROM ".$g5['write_prefix']."schedule
                    WHERE wr_9 NOT IN ('trash','delete')
                        AND wr_2 BETWEEN '{$_month}-01 00:00:00' AND '{$_month}-31 23:59:59'
                    ORDER BY STR_TO_DATE(wr_2, '%Y-%m-%d %H:%i:%s'), STR_TO_DATE(wr_3, '%H:%i:%s')
        ";
    //    echo $sql.'<br>';
        $result = sql_query($sql,1);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            // 날짜 분리
            $row['wr_2_arr'] = date_parse($row['wr_2']);
            $row['wr_ymd'] = sprintf("%04d",$row['wr_2_arr']['year']).'-'.sprintf("%02d",$row['wr_2_arr']['month']).'-'.sprintf("%02d",$row['wr_2_arr']['day']);
            $row['wr_hi'] = sprintf("%02d",$row['wr_2_arr']['hour']).':'.sprintf("%02d",$row['wr_2_arr']['minute']);
            $row['wr_his'] = sprintf("%02d",$row['wr_2_arr']['hour']).':'.sprintf("%02d",$row['wr_2_arr']['minute']).':'.sprintf("%02d",$row['wr_2_arr']['second']);
            $row['wr_ampm'] = date("A h:i ", strtotime($row['wr_3']));
            $row['wr_ampm2'] = date("A h:i ", strtotime($row['wr_4']));
            $row['wr_range'] = date("A h:i",strtotime($row['wr_3']));
            $row['wr_range'] .= ($row['wr_4'])?'~'.date("A h:i",strtotime($row['wr_4'])):'';
            $row['href'] = get_pretty_url($bo_table, $row['wr_id']);

            $day_content[substr($row['wr_2'],0,10)][] = array(
                "item_date" => substr($row['wr_2'],0,10)
                , "item_type" => 'schedule'
                , "wr_range" => $row['wr_range']
                , "wr_link" => $row['href']
                , "wr_subject" => $row['wr_subject']
                , "wr_content" => strip_tags($row['wr_content'])
            );
            
        }
        //print_r2($day_content);
        // } 일정리스트 -------
        ksort($day_content);
           
//        print_r2($day_content);
        //////////////////////////////////////////////////////////////////////////////
        ?>
        <table class="table_list">
        <tbody>
        <?php
        // 만들어진 배열 표시 (2차배열 구조)
        $idx=0;
        if(is_array($day_content)) {
            foreach($day_content as $key => $value) {
//                print_r2($day_content[$key]);
                for ($i=0; $i<sizeof($day_content[$key]); $i++) {
//                    print_r2($day_content[$key][$i]); // <------------------------
                    $bg = 'bg'.($idx%2);
                    foreach($day_content[$key][$i] as $k1 => $v1) {
//                        echo $idx.'. '.$k1.' => '.$v1.'<br>';
                        $row[$k1] = $v1;
                    }
                    // 공휴일
                    if($row['item_type'] == 'holiday') {
                        $row['item_subject'] = $row['holiday_name'];
                    }
                    // 만료일
                    else if($row['item_type'] == 'crt') {
                        $row['item_subject'] = $row['com_name'];
                        $row['item_subject'] .= ' '.$row['certify_text'];
                        $row['item_21'] = '';
                        $row['item_22'] = '';
                    }
                    // 심사일
                    else if($row['item_type'] == 'judge') {
                        $row['item_subject'] = $row['com_name'];
                        $row['item_subject'] .= ' '.$row['crj_type_name'];
                        $row['item_21'] = $row['crt_code_name']; // 인증규격
                    }
                    // 일반일정
                    else if($row['item_type'] == 'schedule') {
                        $row['item_subject'] = $row['wr_subject'];
                        $row['item_21'] = $row['wr_range']; // 시간
                        $row['item_22'] = ''; // 구분
                        $row['item_23'] = ''; // 장소
                    }
                    else {
                        $row['item_subject'] = '';
                    }
                    ?>
                    <tr class="tr_line <?=$bg?> <?=$row['item_type']?>" tr_date="<?=$row['item_date']?>">
                        <td class="td_info">
                            <div class="item_line1"><!-- 날짜 / 제목 / 글쓴이 -->
                                <span><?=substr($row['item_date'],5)?></span>
                                <b><?php echo cut_str($row['item_subject'],20,'..'); ?></b>
                                <span class="item_line1_right"><!-- 글쓴사람(또는 해당 심사원명) -->
                                    <?=$s_mod?>
                                    <?=$row['mb_name_rank']?>
                                </span>
                            </div>
                            <div class="item_line2"><!-- 시간 / 구분 / 장소 -->
                                <span class="item_line2_1" style="display:<?=(!$row['item_21'])?'none':''?>">
                                    <?=$row['item_21']?>
                                </span>
                                <span class="item_line2_2" style="display:<?=(!$row['item_22'])?'none':''?>">
                                    <?=$row['item_22']?>
                                </span>
                                <span class="item_line2_3" style="display:<?=(!$row['item_23'])?'none':''?>">
                                    <?=$row['item_23']?>
                                </span>
                            </div>        
                            <div class="item_line3"><!-- 간단내용(2줄정도) -->
                                <div class="font_size_9"><?php echo $row['judge']; ?></div>
                            </div>        
                        </td>
                    </tr>
                    <?php
                    $idx++;
//                    echo '=====<br>';
                }
            }
        }
        else {
            echo '<tr class="tr_empty"><td>자료가 없습니다.</td></tr>';
        }
        ?>
        </tbody>
        </table>
    </div>
    <!-- } 이달의 일정 -->
    
</div>
<!-- } 달력 종료 -->

<div class="div_fixed_top">
    <?php if($member['mb_level']>=5) { ?>
    <ul class="top_btns" this_month="<?=$_month?>">
        <li title="이전달" class="li_prev_month"><a href="javascript:" class="prev_month btn001" cal_val="-1"><i class="fa fa-chevron-left"></i></a></li>
        <li title="다음달" class="li_next_month"><a href="javascript:" class="next_month btn001" cal_val="+1"><i class="fa fa-chevron-right"></i></a></li>
        <li title="달력" style="display:none;"><a href="<?php echo $board_skin_url?>/list.calendar.php?bo_table=<?php echo $bo_table?>" class="btn001"><i class="fa fa-calendar"></i></a></li>
        <li title="리스트"><a href="<?php echo G5_BBS_URL?>/board.php?bo_table=<?php echo $bo_table?>" class="btn001"><i class="fa fa-list-alt"></i></a></li>
        <li title="검색"><a href="javascript:" class="btn001 top_btn_search"><i class="fa fa-search"></i></a></li>
        <li><a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=<?php echo $bo_table;?>" class="btn001"><i class="fa fa-plus"></i> 일정</a></li>
    </ul>
    <?php } ?>
</div>


<script>
var g5_board_skin_url = '<?php echo $board_skin_url ?>';
var g5_board_config = 0;
</script>
<script src="<?=$board_skin_url?>/calendar.js" type="text/javascript" charset="utf-8"></script>

<script>
// 날짜 클릭 시 해당 위치로 스크롤!
$(document).on('click touchstart','.td_day',function(e){
    var this_date = $(this).attr('td_date');
    //console.log( $(this).attr('td_date') );
    var obj = $('tr[tr_date="'+this_date+'"]').eq(0);
	var position = obj.offset();
//    console.log( position );
	var speed = 200;  // 이동 속도
	var offset = 60; // 해당 위치보다 살짝 위
	if( obj.length >= 1) {
		$('html, body').stop(true, false).animate({scrollTop : position.top - offset}, speed);
        // 해당 항목들 배경 살짝 변경했다가 원복
        $('tr[tr_date="'+this_date+'"]').addClass('bg2');
        back_off(this_date, 'go', 400);
	}
});
function back_off(this_date, flag, speed) {
    setTimeout(function(e){
        $('tr[tr_date="'+this_date+'"]').removeClass('bg2');
        if(flag=='go')
            back_on(this_date, 'go', 400);
    },speed);
}
function back_on(this_date, flag, speed) {
    setTimeout(function(e){
        $('tr[tr_date="'+this_date+'"]').addClass('bg2');
        if(flag=='go')
            back_off(this_date, 'stop', 2000);
    },speed);
}
</script>


<?php
include_once('./_tail.php');
?>