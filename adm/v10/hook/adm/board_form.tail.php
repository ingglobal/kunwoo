<?php
$bo_10_textarea = '<textarea id="tar_bo_10" name="bo_10" rows="20">'.$board['bo_10'].'</textarea>';
echo $bo_10_textarea;
?>

<script>
if($('caption:contains(게시판 여분필드)').length == 1){
    $('caption:contains(게시판 여분필드)').siblings('tbody').attr('id','exf');
    $('#exf').find('tr').eq($('#exf').find('tr').length-1).attr('id','lst_tr');
    $('#lst_tr').find('label:contains(여분필드 10 값)').attr('id','lb_val');
    $('#tar_bo_10').insertAfter('#lb_val');
    $('#lst_tr').find('.td_extra').find('.extra-value-input').remove();
}
</script>