<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once($board_skin_path.'/_common.php');
include_once($board_skin_path.'/view.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->

<article id="bo_v" style="width:<?php echo $width; ?>">
    <header>
        <h2 id="bo_v_title">
            <?php if ($category_name) { ?>
            <span class="bo_v_cate"><?php echo $view['ca_name']; // 분류 출력 끝 ?></span> 
            <?php } ?>
            <span class="bo_v_tit">
            <?php
            echo cut_str(get_text($view['wr_subject']), 70); // 글제목 출력
            ?></span>
        </h2>
    </header>

    <section id="bo_v_info">
        <h2>페이지 정보</h2>
        <span class="sound_only">작성자</span> <strong><?php echo $view['name'] ?><?php if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></strong>
        <span class="sound_only">댓글</span><strong><a href="#bo_vc"> <i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo number_format($view['wr_comment']) ?>건</a></strong>
        <span class="sound_only">조회</span><strong><i class="fa fa-eye" aria-hidden="true"></i> <?php echo number_format($view['wr_hit']) ?>회</strong>
        <strong class="if_date"><span class="sound_only">작성일</span><i class="fa fa-clock-o" aria-hidden="true"></i> <?php echo date("y-m-d H:i", strtotime($view['wr_datetime'])) ?></strong>

    </section>
    <?php
    // 등급보기 가능 관리자인 경우만 나타나는 영역 (조직 관리자 설정인 경우도 포함) [ ---------------
    if($member['board_manager_yn'] || in_array($member['mb_2'],$board['worker_trm_idxs']) ) {
        //print_r3($write);
        //print_r2($member);
        //print_r2($board);
    ?>
    <section id="bo_control">
    <form name="fwrite" id="fwrite" autocomplete="off">
        <input type="hidden" name="wr_id" value="<?=$write['wr_id']?>">
        <input type="hidden" name="bo_table" value="<?=$bo_table?>">

        <div style="display:<?=($member['board_manager_yn'])?'inline-block':'none'?>;">
            <label for="mb_name_worker" class="sound_only">작업자</label>
            <input type="hidden" name="mb_id_worker" id="mb_id_worker" value="<?=$write['mb_id_worker']?>"><!-- 작업자아이디 -->
            <input type="hidden" name="trm_idx_department_worker" id="trm_idx_department_worker" value="<?=$write['trm_idx_department_worker']?>"><!-- 작업자조직코드 -->
            <input type="text" name="mb_name_worker" id="mb_name_worker" href="<?php echo G5_USER_ADMIN_URL?>/employee_select.php?frm=fwrite&tar1=mb_id_worker&tar2=mb_name_worker&tar3=trm_idx_department_worker" value="<?php echo $write['mb_name_worker'] ?>" id="mb_name_worker" class="frm_input" style="width:80px;" placeholder="작업자" readonly>

            <label for="wr_5" class="sound_only">작업등급</label>
            <select name="wr_5" id="wr_5" class="frm_input">
                <option value="">작업등급</option>
                <?php echo $g5['set_maintain_grades_options_value'] ?>
            </select>
            <script>$('select[name=wr_5]').val('<?php echo $write['wr_5'] ?>').attr('selected','selected');</script>

            <label for="wr_6" class="sound_only">완료예정일</label>
            <input type="text" name="wr_6" value="<?php echo $write['wr_6'] ?>" id="wr_6" class="frm_input" placeholder="완료예정일">
        </div>

        <label for="wr_10" class="sound_only">상태</label>
        <select name="wr_10" id="wr_10" class="frm_input">
            <option value="">작업상태</option>
            <?php echo $g5['set_maintain_status_options'] ?>
        </select>
        <script>
            $('select[name=wr_10]').val('<?php echo $write['wr_10'] ?>').attr('selected','selected');
            $('select[name=wr_10]').css('margin-right','0');
        </script>
        <button class="btn btn_admin" id="btn_set">설정변경</button>
        <button class="btn btn_b01" id="btn_reset"  style="display:<?=(in_array($member['mb_2'],$board['worker_trm_idxs']))?'none':''?>;">초기화</button>
    </form>
    </section>
    <?php
    }
    // ]관리자인 경우만 나타나는 영역 (조직 관리자 설정인 경우도 포함) ---------------
    ?>

    <?php
    // 직원인 경우만 보임 {---------------
    if($member['mb_level']>=6 && $member['mb_2']) {
        $view['sried'] = get_serialized($view['wr_9']);
        //print_r3($view['sried']);
        // 구매상품(장바구니) 정보
        if($view['sried']['ct_id']) {
            //print_r3($view['sried']['ct_id']);
            $ct1 = get_table_meta('g5_shop_cart','ct_id',$view['sried']['ct_id'],'shop_cart');
            //print_r3($ct1);
            $ct1['ct_price_total'] = ($ct1['ct_price']+$ct1['io_price'])*$ct1['ct_qty'];
            // 상태값 색상은 클래스로 설정합니다.
            $ct1['ct_status_class'] = (in_array($ct1['ct_status'],$g5['set_workable_status_array'])) ? 'workable' : 'no_work';
            // 영업자
            $mb1 = get_saler($ct1['mb_id_saler']);
            //print_r3($mb1);
            $ct1['mb_name_saler_rank'] = $mb1['mb_name'].$g5['set_mb_ranks_value'][$mb1['mb_3']];
        }
        // 업체정보
        if($view['sried']['com_idx']) {
            $com1 = get_table_meta('company','com_idx',$view['sried']['com_idx']);
            //print_r3($com1);
            $com1['com_manager_hp'] = ($com1['com_manager_hp']) ? '('.$com1['com_manager_hp'].')' : '';
            // 고객
            $mb = get_member($com1['mb_id']);
            $com1['com_president_hp'] = ($mb['mb_hp']) ? '('.$mb['mb_hp'].')' : '';
        }
        
        // 통합인트라일때만 보임 [
        if($view['sried']['com_idx']) {
    ?>
    <section id="bo_item">
        <span><b>상품</b> <?php echo $ct1['it_name']?> (<?php echo number_format($ct1['ct_price_total'])?>원)</span>
        <span><b>작업상태</b> <span class="item_status_<?php echo $ct1['ct_status_class']?>"><?php echo $ct1['ct_status']?></span></span>
        <span><b>영업담당자</b> <?php echo $ct1['mb_name_saler_rank']?>(<?php echo $g5['department_name'][$view['wr_7']]?>)</span>
    </section>
    <!-- 통합인트라 업체정보 -->
    <section id="bo_company">
        <span><b>업체명</b> <?php echo $com1['com_name']?> (<?php echo $com1['com_tel']?> <?php echo $com1['com_email']?>) <span class="com_idx"><?php echo $view['sried']['com_idx']?></span></span>
        <span><b>대표자</b> <?php echo $com1['com_president']?> <?php echo $com1['com_president_hp']?></span>
        <span><b>매니저</b> <?php echo $com1['com_manager']?> <?php echo $com1['com_manager_hp']?></span>
    </section>
    <?php
        }   // ]통합인트라일때만 보임
    }
    // } 직원인 경우만 보임 ---------------
    ?>
    
    <section id="bo_v_atc">
        <h2 id="bo_v_atc_title">본문</h2>

        <?php
        // 파일 출력
        $v_img_count = count($view['file']);
        if($v_img_count) {
            echo "<div id=\"bo_v_img\">\n";

            for ($i=0; $i<=count($view['file']); $i++) {
                if ($view['file'][$i]['view']) {
                    //echo $view['file'][$i]['view'];
                    echo get_view_thumbnail($view['file'][$i]['view']);
                }
            }

            echo "</div>\n";
        }
         ?>

        <!-- 본문 내용 시작 { -->
        <div id="bo_v_con"><?php echo get_view_thumbnail($view['content']); ?></div>
        <?php //echo $view['rich_content']; // {이미지:0} 과 같은 코드를 사용할 경우 ?>
        <!-- } 본문 내용 끝 -->

        <?php if ($is_signature) { ?><p><?php echo $signature ?></p><?php } ?>


        <!--  추천 비추천 시작 { -->
        <?php if ( $good_href || $nogood_href) { ?>
        <div id="bo_v_act">
            <?php if ($good_href) { ?>
            <span class="bo_v_act_gng">
                <a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="bo_v_good"><span class="sound_only">추천</span><strong><?php echo number_format($view['wr_good']) ?></strong></a>
                <b id="bo_v_act_good"></b>
            </span>
            <?php } ?>
            <?php if ($nogood_href) { ?>
            <span class="bo_v_act_gng">
                <a href="<?php echo $nogood_href.'&amp;'.$qstr ?>" id="nogood_button" class="bo_v_nogood"><span class="sound_only">비추천</span><strong><?php echo number_format($view['wr_nogood']) ?></strong></a>
                <b id="bo_v_act_nogood"></b>
            </span>
            <?php } ?>
        </div>
        <?php } else {
            if($board['bo_use_good'] || $board['bo_use_nogood']) {
        ?>
        <div id="bo_v_act">
            <?php if($board['bo_use_good']) { ?><span class="bo_v_good"><span class="sound_only">추천</span><strong><?php echo number_format($view['wr_good']) ?></strong></span><?php } ?>
            <?php if($board['bo_use_nogood']) { ?><span class="bo_v_nogood"><span class="sound_only">비추천</span><strong><?php echo number_format($view['wr_nogood']) ?></strong></span><?php } ?>
        </div>
        <?php
            }
        }
        ?>
        <!-- }  추천 비추천 끝 -->
    </section>

    <div id="bo_v_share">
        <?php if ($scrap_href) { ?><a href="<?php echo $scrap_href;  ?>" target="_blank" class="btn btn_b03" onclick="win_scrap(this.href); return false;"><i class="fa fa-thumb-tack" aria-hidden="true"></i> 스크랩</a><?php } ?>

        <?php
        include_once(G5_SNS_PATH."/view.sns.skin.php");
        ?>
    </div>

    <?php
    $cnt = 0;
    if ($view['file']['count']) {
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
                $cnt++;
        }
    }
     ?>

    <?php if($cnt) { ?>
    <!-- 첨부파일 시작 { -->
    <section id="bo_v_file">
        <h2>첨부파일</h2>
        <ul>
        <?php
        // 가변 파일
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
         ?>
            <li>
                <i class="fa fa-download" aria-hidden="true"></i>
                <a href="<?php echo $view['file'][$i]['href'];  ?>" class="view_file_download">
                    <strong><?php echo $view['file'][$i]['source'] ?></strong>
                </a>
                <?php echo $view['file'][$i]['content'] ?> (<?php echo $view['file'][$i]['size'] ?>)
                <span class="bo_v_file_cnt"><?php echo $view['file'][$i]['download'] ?>회 다운로드 | DATE : <?php echo $view['file'][$i]['datetime'] ?></span>
            </li>
        <?php
            }
        }
         ?>
        </ul>
    </section>
    <!-- } 첨부파일 끝 -->
    <?php } ?>

    <?php if(isset($view['link'][1]) && $view['link'][1]) { ?>
    <!-- 관련링크 시작 { -->
    <section id="bo_v_link">
        <h2>관련링크</h2>
        <ul>
        <?php
        // 링크
        $cnt = 0;
        for ($i=1; $i<=count($view['link']); $i++) {
            if ($view['link'][$i]) {
                $cnt++;
                $link = cut_str($view['link'][$i], 70);
            ?>
            <li>
                <i class="fa fa-link" aria-hidden="true"></i> <a href="<?php echo $view['link_href'][$i] ?>" target="_blank">
                    
                    <strong><?php echo $link ?></strong>
                </a>
                <span class="bo_v_link_cnt"><?php echo $view['link_hit'][$i] ?>회 연결</span>
            </li>
            <?php
            }
        }
        ?>
        </ul>
    </section>
    <!-- } 관련링크 끝 -->
    <?php } ?>

    <!-- 게시물 상단 버튼 시작 { -->
    <div id="bo_v_top">
        <?php
        ob_start();
        ?>

        <ul class="bo_v_left">
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="btn_b01 btn"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="btn_b01 btn" onclick="del(this.href); return false;"><i class="fa fa-trash-o" aria-hidden="true"></i> 삭제</a></li><?php } ?>
            <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" class="btn_admin btn" onclick="board_move('<?php echo $board_skin_url;;?>/move.php?sw=copy&bo_table=<?php echo $bo_table;?>&wr_id=<?php echo $wr_id;?>'); return false;"><i class="fa fa-files-o" aria-hidden="true"></i> 복사</a></li><?php } ?>
            <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" class="btn_admin btn" onclick="board_move('<?php echo $board_skin_url;?>/move.php?sw=move&bo_table=<?php echo $bo_table;?>&wr_id=<?php echo $wr_id;?>'); return false;"><i class="fa fa-arrows" aria-hidden="true"></i> 이동</a></li><?php } ?>
            <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>" class="btn_b01 btn"><i class="fa fa-search" aria-hidden="true"></i> 검색</a></li><?php } ?>
        </ul>

        <ul class="bo_v_com">
           <li><a href="<?php echo $list_href ?>" class="btn_b01 btn"><i class="fa fa-list" aria-hidden="true"></i> 목록</a></li>
            <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn"><i class="fa fa-reply" aria-hidden="true"></i> 답변</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02 btn"><i class="fa fa-pencil" aria-hidden="true"></i> 글쓰기</a></li><?php } ?>
        </ul>

        <?php if ($prev_href || $next_href) { ?>
        <ul class="bo_v_nb">
            <?php if ($prev_href) { ?><li class="btn_prv"><span class="nb_tit"><i class="fa fa-caret-up" aria-hidden="true"></i> 이전글</span><a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a> <span class="nb_date"><?php echo str_replace('-', '.', substr($prev_wr_date, '2', '8')); ?></span></li><?php } ?>
            <?php if ($next_href) { ?><li class="btn_next"><span class="nb_tit"><i class="fa fa-caret-down" aria-hidden="true"></i> 다음글</span><a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a>  <span class="nb_date"><?php echo str_replace('-', '.', substr($next_wr_date, '2', '8')); ?></span></li><?php } ?>
        </ul>
        <?php } ?>
        <?php
        $link_buttons = ob_get_contents();
        ob_end_flush();
         ?>
    </div>
    <!-- } 게시물 상단 버튼 끝 -->

    <?php
    // 코멘트 입출력
    include_once(G5_BBS_PATH.'/view_comment.php');
     ?>


</article>
<!-- } 게시판 읽기 끝 -->

<script>
// 상단 제목 수정
$('#container_title').text('<?php echo $board['bo_subject']?> 게시물 보기');

$("#wr_6").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });

// 작업자 검색
$("#mb_name_worker").click(function() {
    var href = $(this).attr("href");
    memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=600,scrollbars=1");
    memberwin.focus();
    return false;
});

// 설정변경 바로 적용
$(document).on('click','#btn_set',function(e){
    e.preventDefault();
    var target_form = $(this).closest('form');

    //폼 validation
    if(target_form.find('input[name=wr_10]').val() == '') {
        alert("상태값을 선택해 주세요.");
        return false;
    }
    else {
        // form 설정값 serialize 
        //alert( target_form.serialize() );
        data_serialized = target_form.serialize();
        
        //-- 디버깅 Ajax --//
        //$.ajax({
        //	url:'<?php echo $board_skin_url?>/write_update.ajax.php',
        //	data:{"type":"set","data_serialized":data_serialized},
        //	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
        $.getJSON('<?php echo $board_skin_url?>/write_update.ajax.php',{"type":"set","data_serialized":data_serialized},function(res) {
            //alert(res.sql);
            //console.log(res);
            var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
            
            if(res.result == true) {
                //alert(res.msg);
                alert('설정값을 변경하였습니다.');
            }
            else {
                alert(res.msg);
            }
            //}, error:this_ajax_error	//<-- 디버깅 Ajax --//
        });
    }
});

// 설정리셋 바로 적용
$(document).on('click','#btn_reset',function(e){
    e.preventDefault();
    var target_form = $(this).closest('form');

    //폼 validation
    if(confirm('설정값을 리셋하시겠습니까?')) {
        // form 설정값 serialize 
        //alert( target_form.serialize() );
        data_serialized = target_form.serialize();
        
        //-- 디버깅 Ajax --//
        //$.ajax({
        //	url:'<?php echo $board_skin_url?>/write_update.ajax.php',
        //	data:{"type":"reset","data_serialized":data_serialized},
        //	dataType:'json', timeout:10000, beforeSend:function(){}, success:function(res){
        $.getJSON('<?php echo $board_skin_url?>/write_update.ajax.php',{"type":"reset","data_serialized":data_serialized},function(res) {
            //alert(res.sql);
            console.log(res);
            var prop1; for(prop1 in res.rows) { console.log( prop1 +': '+ res.rows[prop1] ); }
            
            if(res.result == true) {
                //alert(res.msg);
                alert('설정값을 변경하였습니다.');
                self.location.reload();
            }
            else {
                alert(res.msg);
            }
            //}, error:this_ajax_error	//<-- 디버깅 Ajax --//
        });
    }
});	

</script>


<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }

        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

        if(confirm(msg)) {
            var href = $(this).attr("href")+"&js=on";
            $(this).attr("href", href);

            return true;
        } else {
            return false;
        }
    });
});
<?php } ?>

function board_move(href)
{
    boardmove = window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
    boardmove.focus();
}
</script>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    // 추천, 비추천
    $("#good_button, #nogood_button").click(function() {
        var $tx;
        if(this.id == "good_button")
            $tx = $("#bo_v_act_good");
        else
            $tx = $("#bo_v_act_nogood");

        excute_good(this.href, $(this), $tx);
        return false;
    });

    // 이미지 리사이즈
    $("#bo_v_atc").viewimageresize();

    //sns공유
    $(".btn_share").click(function(){
        $("#bo_v_sns").fadeIn();
   
    });

    $(document).mouseup(function (e) {
        var container = $("#bo_v_sns");
        if (!container.is(e.target) && container.has(e.target).length === 0){
        container.css("display","none");
        }	
    });
});

function excute_good(href, $el, $tx)
{
    $.post(
        href,
        { js: "on" },
        function(data) {
            if(data.error) {
                alert(data.error);
                return false;
            }

            if(data.count) {
                $el.find("strong").text(number_format(String(data.count)));
                if($tx.attr("id").search("nogood") > -1) {
                    $tx.text("이 글을 비추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                } else {
                    $tx.text("이 글을 추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                }
            }
        }, "json"
    );
}
</script>
<!-- } 게시글 읽기 끝 -->