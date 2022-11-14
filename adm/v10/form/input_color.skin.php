<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<ul class="color_ul">
	<?php if($alpha_flag) {?>
	<li class="color_li color_li1">
		<input type="color" id="<?=$aid?>" readonly value="<?=$bg16?>" style="width:50px;">
	</li>
	<li class="color_li color_li2">
		<span class="color_alpha_ttl">투명도</span>
		<?php //echo wgf_input_range('',$bga,$w,0,1,0.05,100); ?>
		<span class="range_span wg_wdx100" style="padding-right:29px;">
			<input type="range" value="<?=$bga?>" id="<?=$bid?>" min="0" max="1" step="0.05" size="30">
			<span class="output_span">
				<output id="<?=$did?>" style="padding-right:5px;"><?=$bga?></output>
			</span>
		</span>
	</li>
	<li class="color_li color_li3">
		<input type="hidden" name="<?=$name?>" id="<?=$cid?>" value="<?=$input_color?>">
		<div class="color_result_bg"><div class="color_result"></div></div>
	</li>
	<?php }else{ ?>
	<li class="color_li color_li1"><input type="color" name="<?=$name?>" id="<?=$aid?>" readonly value="<?=$input_color?>" style="width:50px;"></li>
	<?php } ?>
</ul>
<script>
$(function(){
	//여기서 부터는 ie버전에서 input 박스의 색상을 표시해 준다.
	<?php if($g5['is_explorer']){?>
	var <?=$aid?>_val = $('#<?=$aid?>').val();
	$('#<?=$aid?>').css({'border':'1px solid #ddd','font-size':0,'background':<?=$aid?>_val});
	<?php } ?>

	<?php if($alpha_flag) {?>
		//색상과 투명도 설정
		var <?=$eid?>_rgbacolor = wgf_hex2rgba($('#<?=$aid?>').val(), <?=$bga?>);
		$('#<?=$cid?>').val(<?=$eid?>_rgbacolor);
		$('#<?=$cid?>').siblings('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);

		$('#<?=$aid?>').colpick({
			onSubmit:function(hsb,hex,rgb,el,bySetColor) {
				$(el).val('#'+hex);
				$(el).colpickHide();
				<?=$eid?>_rgbacolor = wgf_hex2rgba($(el).val(), $('#<?=$bid?>').val());
				$(el).parent().siblings('.color_li3').find('input').val(<?=$eid?>_rgbacolor);
				$(el).parent().siblings('.color_li3').find('input').siblings('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);
				
				
				<?php if($g5['is_explorer']){?>
				$(el).css({'border':'1px solid #ddd','font-size':0,'background':$(el).val()});
				<?php } ?>
			}
		});
		
		$('#<?=$bid?>').on('change',function(){
			<?=$eid?>_rgbacolor = bwg_hex2rgba($('#<?=$aid?>').val(), $(this).val());
			$(this).siblings('.output_span').find('output').text($(this).val());
			//console.log(<?=$eid?>_rgbacolor);
			$(this).parent().parent().siblings('.color_li3').find('input').val(<?=$eid?>_rgbacolor);
			$(this).parent().parent().siblings('.color_li3').find('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);
			$(this).parent().parent().siblings('.color_li3').find('.color_result_bg').find('.color_result').css('background',<?=$eid?>_rgbacolor);
		});
	<?php }else{ // 여기까지는 $alpha_flag == true ?>
		//색상만 설정
		$('#<?=$aid?>').colpick({
			onSubmit:function(hsb,hex,rgb,el,bySetColor) {
				$(el).val('#'+hex);
				$(el).colpickHide();
				<?php if($g5['is_explorer']){?>
				$(el).css({'border':'1px solid #ddd','font-size':0,'background':$(el).val()});
				<?php } ?>
			}
		});
	<?php } // 여기까지는 $alpha_flag == false ?>
});
</script>