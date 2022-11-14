<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<span class="range_span<?=$wd_class?>" style="<?=$padding_right_style?>" unit="<?=$unit?>">
<input type="range" name="<?=$rname?>" value="<?=$val?>" id="<?=$rinid?>" min="<?=$min?>" max="<?=$max?>" step="<?=$step?>" size="30">
<span class="output_span">
	<output style="<?=$output_show?>"><?=$val?></output>
	<?php if($unit){ ?>
	<span class="unit_span"><?=$unit?></span>
	<?php } ?>
</span>
</span>
<script>
$('#<?=$rinid?>').on('change',function(){
	$(this).siblings('.output_span').find('output').text($(this).val());
});
</script>
