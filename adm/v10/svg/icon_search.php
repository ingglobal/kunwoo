<svg id="<?=$icid?>" class="icon_<?=$svg_name?>" style="width:<?=$width?>;height:<?=$height?>;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
	 y="0px" viewBox="0 0 100 100" style="enable-background:new 0 0 100 100;" xml:space="preserve">
<style type="text/css">
	#<?=$icid?> .st1{fill:none;stroke:<?=$svg_color?>;stroke-width:10;stroke-linecap:round;stroke-miterlimit:10;}
	#<?=$icid?> .st2{fill:none;stroke:<?=$svg_color?>;stroke-width:7;stroke-miterlimit:10;}
	<?php if($fill && $svg_color2){ ?>
	#<?=$icid?> .st2{fill:<?=$svg_color2?>}
	<?php } ?>
</style>
<g>
	<g>
		<line class="st1" x1="75" y1="75" x2="93.5" y2="93.5"/>
	</g>
	<circle class="st2" cx="44.9" cy="44.7" r="39.1"/>
</g>
</svg>
