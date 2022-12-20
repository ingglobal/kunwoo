<div id="modal_clone" class="modal mdl_hide">
    <div class="mdl_bg"></div>
    <div class="mdl_box">
        <?=svg_icon('close','mdl_close',50,50)?>
        <div class="mdl_head">
            <h1 class="h1_item"></h1>
        </div>
        <div class="mdl_cont">
            <form name="form01" id="form01" action="<?=G5_USER_ADMIN_URL?>/order_out_clone_update.php" onsubmit="return form01_submit(this);" method="post" autocomplete="off">
            <span class="cp_parent_date" style="display:none;"></span>
            <input type="hidden" name="start_date" value="<?=$first_date?>">
            <input type="hidden" name="end_date" value="<?=$last_date?>">
            <input type="hidden" name="oop_idx" value="<?=$oop_idx?>">
            <p><strong>실행계획</strong><span class="cp_oop_idx"></span></p>
            <p><strong>품목코드</strong><span class="cp_no"></span></p>
            <p><strong>규격</strong><span class="cp_std"></span></p>
            <p class="cp_date">
                <strong>작업날짜선택 : </strong>
                <span><input type="text" name="ooc_date" readonly class="frm_input" value="" style="width:100px;border:1px solid #888 !important;"></span>
            </p>
            <p class="cp_day_night">
                <strong>주간야간선택 : </strong>
                <span>
                    <select name="ooc_day_night" style="border:1px solid #888;">
                        <option value="D">주간</option>
                        <option value="N">야간</option>
                        <option value="A">종일</option>
                        <option value="T">삭제</option>
                    </select>
                </span>
            </p>
            <input type="submit" value="적용" class="btn_submit btn">
            </form>
        </div>
    </div>
    <script>
    $("input[name*=_date]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
    
    function form01_submit(f) {
        var parent_date = new Date($('.cp_parent_date').text());
        var ooc_date = new Date(f.ooc_date.value);
        if(!f.ooc_date.value){
            alert('날짜를 선택해 주세요.');
            f.ooc_date.focus();
            return false;
        }

        if(parent_date.getTime() >= ooc_date.getTime()){
            alert('날짜는 ['+$('.cp_parent_date').text()+']보다 큰 날짜로 설정하셔야 합니다.');
            f.ooc_date.focus();
            return false;
        }

        return true;
    }
    </script>
</div>