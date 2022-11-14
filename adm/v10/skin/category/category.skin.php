<?php
$len = strlen($this->value);
$no = ($len == 0)?0:$len / 2;
?>
<div id="dv_<?=$this->id?>">
    <input type="hidden" id="<?=$this->id?>" name="<?=$this->name?>" value="<?=$this->value?>">
    <select id="<?=$this->id1?>" no="1" class="frm_input bct1<?=$this->required_str?><?=$this->readonly_str?>"<?=$this->required_str?><?=$this->readonly_str?><?=$this->readonly_scr?>>
        <option value="">::1차분류::</option>
        <?php if(count($cats1)){ ?>
        <?php foreach($cats1 as $k => $v){ ?>
        <option value="<?=$k?>"<?=(($cats[0] == $k)?' selected':'')?>><?=$v?></option>
        <?php } ?>
        <?php } ?>
    </select>
    <select id="<?=$this->id2?>" no="2" class="frm_input bct2<?=$this->required_str?><?=$this->readonly_str?>"<?=$this->required_str?><?=$this->readonly_str?><?=$this->readonly_scr?>>
        <option value="">::2차분류::</option>
        <?php if(count($cats2)){ ?>
        <?php foreach($cats2 as $k => $v){ ?>
        <option value="<?=$k?>"<?=(($cats[1] == $k)?' selected':'')?>><?=$v?></option>
        <?php } ?>
        <?php } ?>
    </select>
    <select id="<?=$this->id3?>" no="3" class="frm_input bct3<?=$this->required_str?><?=$this->readonly_str?>"<?=$this->required_str?><?=$this->readonly_str?><?=$this->readonly_scr?>>
        <option value="">::3차분류::</option>
        <?php if(count($cats3)){ ?>
        <?php foreach($cats3 as $k => $v){ ?>
        <option value="<?=$k?>"<?=(($cats[2] == $k)?' selected':'')?>><?=$v?></option>
        <?php } ?>
        <?php } ?>
    </select>
    <select id="<?=$this->id4?>" no="4" class="frm_input bct4<?=$this->required_str?><?=$this->readonly_str?>"<?=$this->required_str?><?=$this->readonly_str?><?=$this->readonly_scr?>>
        <option value="">::4차분류::</option>
        <?php if(count($cats4)){ ?>
        <?php foreach($cats4 as $k => $v){ ?>
        <option value="<?=$k?>"<?=(($cats[3] == $k)?' selected':'')?>><?=$v?></option>
        <?php } ?>
        <?php } ?>
    </select>
</div>
<script>
var <?=$d?>_len = '<?=$len?>';
var <?=$d?>_no = '<?=$no?>';
var <?=$d?>_did = '#dv_<?=$this->id?>';
var <?=$d?>_id = '#<?=$this->id?>';
var <?=$d?>_id1 = '#<?=$this->id1?>';
var <?=$d?>_id2 = '#<?=$this->id2?>';
var <?=$d?>_id3 = '#<?=$this->id3?>';
var <?=$d?>_id4 = '#<?=$this->id4?>';
var <?=$d?>_sbtn = $(<?=$d?>_did).find('select');
var <?=$d?>_sbtn_nxt = '';
var <?=$d?>_btn = '';
var <?=$d?>_val = '';
var <?=$d?>_call_url = '<?=$call_url?>';

//초기설정
if(<?=$d?>_no == '1') <?=$d?>_btn = $(<?=$d?>_id1);
else if(<?=$d?>_no == '2') <?=$d?>_btn = $(<?=$d?>_id2);
else if(<?=$d?>_no == '3') <?=$d?>_btn = $(<?=$d?>_id3);
<?=$d?>_val = (<?=$d?>_btn != '')?<?=$d?>_btn.val():'';
if(<?=$d?>_no == '1' || <?=$d?>_no == '2' || <?=$d?>_no == '3'){
    <?=$d?>_sbtn_nxt = <?=$d?>_btn.next();
    <?=$d?>_cat_call();
} 

//이벤트 시작
<?=$d?>_event_on();

function <?=$d?>_cat_call(){
    //잠시 선택박스 이벤트 비활성화
    <?=$d?>_event_off();
    $.ajax({
        type : "POST",
        url : <?=$d?>_call_url,
        dataType : "json",
        data : {"no" : <?=$d?>_btn.attr("no"), "val" : <?=$d?>_val},
        success : function(res){
            if(res.error){
                ;//alert('하위분류목록이 없습니다.');
            }
            else {
                <?=$d?>_set_select(res);
            }
        },
        error : function(xreq){
            alert('Status: ' + xreq.status + ' \n\rstatusText: ' + xreq.statusText + ' \n\rresponseText: ' + xreq.responseText);
        }
    });
}

function <?=$d?>_blank_select(){
    var no = <?=$d?>_btn.attr('no');
    //hidden 인풋에 선택한 현재값을 저장
    $(<?=$d?>_id).val(<?=$d?>_val);
    if(no == '1'){
        //2차분류,3차분류,4차분류 비우기
        $(<?=$d?>_id2).empty().html('<option value="">::2차분류::</option>');
        $(<?=$d?>_id3).empty().html('<option value="">::3차분류::</option>');
        $(<?=$d?>_id4).empty().html('<option value="">::4차분류::</option>');
    }
    else if(no == '2'){
        //3차분류,4차분류 비우기
        $(<?=$d?>_id3).empty().html('<option value="">::3차분류::</option>');
        $(<?=$d?>_id4).empty().html('<option value="">::4차분류::</option>');
    }
    else if(no == '3'){
        //4차분류 비우기
        $(<?=$d?>_id4).empty().html('<option value="">::4차분류::</option>');
    }
    else {
        ;
    }
}

function <?=$d?>_set_select(res){
    <?=$d?>_sbtn_nxt.empty().html('<option value="">::'+<?=$d?>_sbtn_nxt.attr('no')+'차분류::</option>');
    $.each(res,function(idx,val){
        $('<option value="'+idx+'">'+val+'</option>').appendTo(<?=$d?>_sbtn_nxt);
    });
    <?=$d?>_event_on();
}

function <?=$d?>_event_on(){
    $(<?=$d?>_sbtn).on('change',function(){
        <?=$d?>_btn = $(this);
        <?=$d?>_val = $(this).val();
        <?=$d?>_blank_select();
        if($(this).attr('no') == '4')
            return;
        else {
            <?=$d?>_sbtn_nxt = $(this).next();
        }
        <?=$d?>_cat_call();
    });
}

function <?=$d?>_event_off(){
    $(<?=$d?>_sbtn).off('change');
}
</script>