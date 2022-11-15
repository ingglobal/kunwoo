<div id="modal" class="mdl_hide">
    <div class="mdl_bg"></div>
    <div class="mdl_box">
        <?=svg_icon('close','mdl_close',50,50)?>
        <div class="mdl_head">
            <h1 class="h1_item"></h1>
        </div>
        <div class="mdl_cont">
            <p><strong class="st_mtr">원자재 : </strong><span class="sp_mtr"></span></p>
            <p><strong class="st_cut">절단설비 : </strong><span class="sp_cut"></span></p>
            <p><strong class="st_forge">단조설비 : </strong><span class="sp_forge"></span></p>
            <p><strong class="st_day_cnt">오전수량 : </strong><span class="sp_day_cnt"></span></p>
            <p><strong class="st_night_cnt">오후수량 : </strong><span class="sp_night_cnt"></span></p>
            <p><strong class="st_cnt">전체수량 : </strong><span class="sp_cnt"></span></p>
            <p><strong class="st_start_date">생산일 : </strong><span class="sp_start_date"></span></p>
            <p><strong class="st_status">상태 : </strong><span class="sp_status"></span></p>
        </div>
    </div>
</div>
<script>
$('#modal').removeClass('mdl_hide');
</script>