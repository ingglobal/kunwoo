<?php
//print_r2($com);
//print_r2($_REQUEST);
?>
<style>
@charset "utf-8";
/* KPI report index */


/* kpi report uppder area */
.title01 {padding:10px;font-size: 1.5em;color: #0889a5;}
.title01 .text01 {margin-left:20px;font-size:0.8em;}
.form01 {border: solid 1px #dfdfdf;border-radius: 10px;background-color: #fafafa;padding:15px;}
.form01 .frm_input
,.form01 .text01
,.form01 .text02
,.form01 .btn_submit {vertical-align:top;}
.form01 .text01 {height:42px;line-height:42px;margin-right:5px;}
.form01 .frm_input {width:95px;text-align:center;height:42px;line-height:42px;border:solid 1px #ddd;border-radius:5px;font-size:1.1em;margin-right:5px;}
#en_date {margin-right:0;}
.form01 .btn_submit {border:none;border-radius:5px;background:#989cb8;padding:11px 30px 12px;}
.tab_wrapper .content_wrapper{margin-top:-4px;}

.fsch2{margin-top:10px;}
.fsch:after{display:block;visibility:hidden;clear:both;content:'';}
.fsch .fsch_dti input{width:48%;}
.fsch .text02{width:16.7%;float:left;text-align:center;height:42px;line-height:42px;border:solid 1px #ddd;margin-left:-1px;background:#f1f1f1;cursor:pointer;}
.fsch_smb input{width:100% !important;}
.fsch_dti input{width:47.5% !important;display:block;float:left;}
.fsch_dti .text01{width:1.5% !important;display:block;float:left;}
.fsch_sel:after{display:block;visibility:hidden;clear:both:content:'';}
.fsch_sel > div{float:left;margin-top:10px;}
/* iframe area
.tab_content {min-height:400px;}
.tab_content iframe {width:100%;} */
</style>
<div class="title01">
	<?=$com['com_name']?>
	<span class="title_breadcrumb"></span><!-- > 제1공장 > 1라인 -->
	<span class="text01 title_date"><?=$st_date?><?=$en_date2?></span>
</div>

<!-- selections -->
<form id="form01" name="form01" class="form01" method="get">
	<input type="hidden" name="com_idx" value="<?=$com_idx?>" class="frm_input">
	<div class="fsch fsch_dti">
		<input type="text" name="st_date" id="st_date" value="<?=$st_date?>" class="frm_input">
		<span class="text01">~</span>
		<input type="text" name="en_date" id="en_date" value="<?=$en_date?>" class="frm_input">
	</div>
	<div class="fsch fsch2 fsch_sbt">
		<div class="text02 prev_month"><i class="fa fa-chevron-left"></i></div>
		<div class="text02 this_month" s_ymd="<?=$st_ymd?>" e_ymd="<?=$en_ymd?>">이번달</div>
		<div class="text02 next_month"><i class="fa fa-chevron-right"></i></div>
		<div class="text02 prev_day"><i class="fa fa-chevron-left"></i></div>
		<div class="text02 this_day" s_ymd="<?=$today?>" e_ymd="<?=$today?>">오늘</div>
		<div class="text02 next_day"><i class="fa fa-chevron-right"></i></div>
	</div>
	<div class="fsch fsch2 fsch_sel">
		<div class="first_select">
			<select name="mmg0" id="mmg0">
				<option value="">전체</option>
			</select>
		</div>
	</div>
	<div class="fsch fsch2 fsch_smb">
		<input type="submit" class="btn_submit" value="확인">
	</div>
</form>

<!-- the start of .div_stat  -->
<div class="div_stat">
	<ul>
		<li>
			<div>
				<div class="in">
					<span class="title">목표달성율</span>
					<span class="content" id="sum_target">&nbsp;</span>
					<span class="unit">%</span>
				</div>
			</div>
		</li>
		<li>
			<div>
				<div class="in">
					<span class="title">불량율</span>
					<span class="content" id="sum_defect">&nbsp;</span>
					<span class="unit">%</span>
				</div>
			</div>
		</li>
		<li>
			<div>
				<div class="in">
					<span class="title">설비가동율</span>
					<span class="content" id="sum_runtime"><?=number_format($run_rate,1)?></span>
					<span class="unit">%</span>
				</div>
			</div>
		</li>
		<li>
			<div>
				<div class="in">
					<span class="title">알람발생</span>
					<span class="content" id="sum_alarm">&nbsp;</span>
					<span class="unit">건</span>
				</div>
			</div>
		</li>
		<li>
			<div>
				<div class="in">
					<span class="title">예지발생</span>
					<span class="content" id="sum_predict">&nbsp;</span>
					<span class="unit">건</span>
				</div>
			</div>
		</li>
		<li>
			<div>
				<div class="in">
					<span class="title">계획정비<d style="font-size:0.8em;">(D-10)</d></span>
					<span class="content" id="sum_plan">&nbsp;</span>
					<span class="unit">건</span>
				</div>
			</div>
		</li>
	</ul>
</div>
<!-- the end of .div_stat  -->
