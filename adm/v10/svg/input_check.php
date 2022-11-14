<label id="<?=$ipid?>">
	<input type="checkbox" name="<?=$name?>" value="1">
	<svg style="width:<?=$width?>;height:<?=$height?>;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
		 y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve">
		<style type="text/css">
			#<?=$ipid?>{}
			#<?=$ipid?> input{}
			#<?=$ipid?> .st0{fill:none;stroke:#4D4D4D;stroke-width:5;stroke-miterlimit:10;}
			#<?=$ipid?> .st1{fill:none;stroke:#4D4D4D;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}
			<?php if(!$chk_flag){ ?>
			#<?=$ipid?> .st1{display:none;}
			<?php } ?>
		</style>
		<path class="st0" d="M76,88H24c-6.6,0-12-5.4-12-12V24c0-6.6,5.4-12,12-12h52c6.6,0,12,5.4,12,12v52C88,82.6,82.6,88,76,88z"/>
		<polyline class="st1" points="27,50.5 49,69.5 76.5,25.5 "/>
	</svg>
	<?php if($txt != ''){ ?>
	<span>
	
	</span>
	<?php } ?>
</label>
