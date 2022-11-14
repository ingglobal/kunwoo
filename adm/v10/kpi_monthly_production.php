<?php
$sub_menu = "955410";
include_once('./_common.php');

$g5['title'] = 'KPI 보고서';
// include_once('./_top_kpi_monthly.php');
include_once('./_head.php');
// echo $g5['container_sub_title'];

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi_monthly_production.css">', 0);

add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/js/jquery.nice-select.min.js"></script>', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/jquery-nice-select-1.1.0/css/nice-select.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/js/multipurpose_tabcontent/css/style.css">', 0);
add_javascript('<script src="'.G5_USER_ADMIN_URL.'/js/multipurpose_tabcontent/js/jquery.multipurpose_tabcontent.js"></script>', 0);
?>
<style>
.title01 {padding:10px;font-size: 1.5em;color: #0889a5;}
.title01 .text01 {margin-left:20px;font-size:0.8em;}
.form01 {border: solid 1px #dfdfdf;border-radius: 10px;background-color: #fafafa;padding:15px;}
.form01 .frm_input
,.form01 .text01
,.form01 .text02
,.form01 .btn_submit {vertical-align:top;}
.form01 .text01 {height:42px;line-height:42px;margin-left:-5px;}
.form01 .text02 {padding:0 5px;height:42px;line-height:42px;margin-left:-5px;border:solid 1px #ddd;border-radius:5px;background:#f1f1f1;cursor:pointer;}
.form01 .frm_input {width:95px;text-align:center;height:42px;line-height:42px;border:solid 1px #ddd;border-radius:5px;font-size:1.1em;margin-right:5px;}
.form01:after {display:block;visibility:hidden;clear:both;content:'';}
.form01 > div {display:inline-block;margin-right:5px;}
.form01 .btn_submit {border:none;border-radius:5px;background:#989cb8;padding:11px 30px 12px;}
.tab_wrapper .content_wrapper{margin-top:-4px;}
</style>

<div class="title01">
	아진산업 > 제1공장 > 1라인
	<span class="text01">2020.07.01 ~ 2020.07.31</span>
</div>

<!-- selections -->
<div id="form01" name="form01" class="form01" method="get">
	<input type="text" name="st_date" value="2020-08-01" class="frm_input">
	<span class="text01">~</span>
	<input type="text" name="en_date" value="2020-08-01" class="frm_input">
	<div class="text02">당월</div>
	<div class="text02">당일</div>
	<div>
		<select name="mmg01" id="mmg01">
			<option value="">전체</option>
			<option value="2">1공장서울공장</option>
			<option value="3">2공장</option>
		</select>
		<script>$('select[id=mmg01]').val(3).attr('selected','selected')</script>
	</div>
	<div>
		<select name="mmg02" id="mmg02">
			<option value="">전체</option>
			<option value="2">1공장</option>
			<option value="3">2공장</option>
		</select>
		<script>$('select[id=mmg02]').val(2).attr('selected','selected')</script>
	</div>
	<input type="submit" class="btn_submit" value="검색">
</div>
<script>
$(function(e){
	$('select[name^=mmg]').niceSelect();
});
</script>

<div class="tab_wrapper reports">
  <ul class="tab_list">
    <li>통합 보고서</li>
    <li>장비별 보고서</li>
  </ul>
  <div class="content_wrapper">
    <div class="tab_content active">
      <h3>Tab content 1</h3>
    </div>
    <div class="tab_content">
      <h3>Tab content 2</h3>
    </div>
  </div>
</div>
<script>
$(function(e){
	$(".reports").champ({active_tab :"2"});
});
</script>

<div class="div_header">
	<div class="div_title1"><b>INGGLOBAL 경주2공장</b></div>
	<div class="div_title2">KPI 5월 통합 Report</div>
		<div class="div_calendar">
			<ul>
				<li><i class="fa fa-chevron-circle-left"></i>&nbsp;</li>
				<li>
					<input type="text" color="#cecece" value="2020-05">
				</li>
				<li><i class="fa fa-chevron-circle-right"></i>&nbsp;</li>
			</ul>
		</div>
    </div>

    <div class="div_report_button">
        <ul class="button">
            <li class="on"><a href="kpi_monthly_production.php">월 보고서</a></li>
            <li ><a href="kpi_daily_production.php">일 보고서</a></li>
			<li><a href="kpi_facility2.php">장비별 보고서</a></li>
        </ul>
    </div>
    <!-- the start of .div_stat  -->
	<div class="div_stat">
		<ul>
			<li>
			   <span class="title">목표달성율</span>
				<span class="content"><?=rand(80,90)?>.95</span>
				<span class="unit">%</span>
			</li>
			<li>
				<span class="title">작업시간</span>
				<span class="content"><?=rand(300,400)?></span>
				<span class="unit">hours</span>
			</li>
			<li>
				<span class="title">매출액</span>
				<span class="content"><?=rand(300,400)?></span>
				<span class="unit">억</span>
			</li>
			<li>
				<span class="title">가동율</span>
				<span class="content"><?=rand(80,90)?></span>
				<span class="unit">%</span>
			</li>
			<li>
				<span class="title">설비고장</span>
				<span class="content"><?=rand(10,30)?></span>
				<span class="unit">건</span>
			</li>
			<li>
				<span class="title">불량</span>
				<span class="content"><?=rand(100,300)?></span>
				<span class="unit">건</span>
			</li>
		</ul>
	</div>
	<!-- the end of .div_stat  -->

	<!-- start of 생산보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 생산보고서</i></div>
	<!-- start of .div_left_01 -->
		<div class="div_info_01">
        <div class="div_left_01">
            <div class="info_box left_info_01">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 생산</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_01.JPG"></div>
                <div class="table_box_01">
					<table class="item_table_01">
						<tr class="item_th">
                            <td>작업일수</td>
                            <td>작업시간</td>
                            <td>목표율</td>
                            <td>평균목표량</td>
                            <td>평균생산량</td>
                        </tr>
						<tr class="item_th1">
                            <td>19/31</td>
                            <td>310 hours</td>
                            <td>96.95%</td>
                            <td>13 Plt</td>
                            <td>12.8 Plt</td>
                        </tr>
                     </table>
                </div>
            </div>
            <div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 년생산율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_03.JPG"></div>
                <div class="table_box_01">
					<table class="item_table_01 table_12">
						<tr>
							<td>월</td>
							<td>1</td>
							<td>2</td>
							<td>3</td>
							<td>4</td>
							<td>5</td>
							<td>6</td>
							<td>7</td>
							<td>8</td>
							<td>9</td>
							<td>10</td>
							<td>11</td>
							<td>12</td>
						</tr>
						<tr>
							<td>수량</td>
							<td>1200</td>
							<td>3000</td>
							<td>1500</td>
							<td>4500</td>
							<td>4500</td>
							<td>6544</td>
							<td>7500</td>
							<td>8700</td>
							<td>1500</td>
							<td>7500</td>
							<td>8700</td>
							<td>1500</td>
							</tr>
						<tr>
							<td>목표</td>
							<td>1</td>
							<td>2</td>
							<td>3</td>
							<td>4</td>
							<td>5</td>
							<td>6</td>
							<td>7</td>
							<td>8</td>
							<td>9</td>
							<td>10</td>
							<td>11</td>
							<td>12</td>
						</tr>
					  </table>
				</div>
            </div>
        </div>
        <!-- end of .div_left_01 -->
		<!-- start .div_right_01 -->
        <div class="div_right_01">
            <div class="info_box right_info_01">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 전년도 대비</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_02.JPG"></div>
            </div>
            <div class="info_box right_info_01">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 설비 생산 지표</i></div>
                <div class="item_content">
			  <table class="item_table_02">
				<tr class="item_th">
				  <td>No</td>
				  <td>설비명</td>
				  <td>설비별 생산율</td>
				  <td>수량</td>
				  <td>비율</td>
				</tr>
				<tr>
				  <td>1</td>
				  <td>1500TON 프레스1호기</td>
				  <td class="td_graph">
					<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="8px">
					<img class="grp2" src="" style="width:90%" height="2px">
				  </td>
				  <td><?=rand(10000,99999)?></td>
				  <td><?=rand(70,110)?>%</td>
				</tr>
				<tr>
				  <td>2</td>
				  <td>1500TON 프레스1호기</td>
				  <td class="td_graph">
					<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="8px">
					<img class="grp2" src="" style="width:90%" height="2px">
				  </td>
				  <td><?=rand(10000,99999)?></td>
				  <td><?=rand(70,110)?>%</td>
				</tr>
				<?php
				for($i=3;$i<13;$i++) {
				  $percent = rand(50,110);
				  if($percent<70) {
					$color = '#d1c594';
				  }
				  else if($percent<100) {
					$color = '#94bfd1';
				  }
				  else if($percent<200) {
					$color = '#8fd1c4';
				  }
				  else {
					$color = 'gray';
				  }
				  echo '
					<tr>
					<td>'.$i.'</td>
					<td>1500TON 프레스'.$i.'호기</td>
					<td class="td_graph">
					  <img class="grp1" src="'.G5_USER_ADMIN_URL.'/img/dot.gif" style="width:'.$percent.'%;background:'.$color.';" height="8px">
					  <img class="grp2" src="'.G5_USER_ADMIN_URL.'/img/dot.gif" style="width:90%" height="2px" title="목표">
					</td>
					<td>'.rand(10000,99999).'</td>
					<td>'.$percent.'%</td>
				  </tr>
				  ';
				}
				?>
			  </table>
			</div>
				</div>
            </div>
        </div>
        <!-- end of .div_right_01 -->
        <!-- end of .div_info_01 -->


    <!-- start of 품질보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 품질보고서</i></div>
<!-- start of .div_left_01 -->
		<div class="div_info_01">
        <div class="div_left_01">
            <div class="info_box left_info_01">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 불량지표</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_01.JPG"></div>
                <div class="table_box_01">
                    <table class="item_table_01 table_td_date">
						<tr>
							<td>일</td><td>형상</td><td>치수</td><td>스크래치</td><td>기타</td><td>통합</td>
							<td>일</td><td>형상</td><td>치수</td><td>스크래치</td><td>기타</td><td>통합</td>
						</tr>
						<tr>
							<td>1</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>16</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>2</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>17</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>3</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>18</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>4</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>19</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>5</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>20</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>6</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>16</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>7</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>17</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>8</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>18</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>9</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>19</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>10</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>20</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>6</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>21</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>7</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>22</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>8</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>23</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>9</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>24</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>10</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>25</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>11</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>26</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>12</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>27</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>13</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>28</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>14</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>29</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td>15</td><td>3</td><td>4</td><td>3</td><td>100</td><td>22</td>
							<td>30</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>
						<tr>
							<td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
							<td>31</td><td>3</td><td>4</td><td>5</td><td>100</td><td>200</td>
						</tr>						
				</table>
                </div>
            </div>
         </div>
        <!-- end of .div_left_01 -->
		<!-- start of .div_right_01 -->

		<div class="div_right_01">
			<div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 불량</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_04.JPG"></div>
                <div class="table_box_01">
					<table class="item_table_01">
						<tr class="item_th">
							<td>형상불량</td>
							<td>치수불량</td>
							<td>스크래치불량</td>
							<td>기타불량</td>
						</tr>
						<tr class="item_th1">
							<td>53%</td>
							<td>23%</td>
							<td>33%</td>
							<td>3%</td>
						</TR>
				</table>
			</div>
        </div>
		<div class="info_box left_info_01">
             <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 불량 건수 지표</i></div>
                <div class="table_box_01">
					<table class="item_table_02">
						<tr class="item_th">
						  <td>No</td>
						  <td>설비명</td>
						  <td>불량건수</td>
						  <td>횟수</td>
						  <td>비율</td>
						</tr>
						<tr>
						  <td>1</td>
						  <td>1500TON 프레스1호기</td>
						  <td class="td_graph">
							<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="8px">
							<img class="grp2" src="" style="width:90%" height="2px">
						  </td>
						  <td><?=rand(10000,99999)?></td>
						  <td><?=rand(70,110)?>%</td>
						</tr>
						<tr>
						  <td>2</td>
						  <td>1500TON 프레스1호기</td>
						  <td class="td_graph">
							<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="8px">
							<img class="grp2" src="" style="width:90%" height="2px">
						  </td>
						  <td><?=rand(10000,99999)?></td>
						  <td><?=rand(70,110)?>%</td>
						</tr>
						<?php
						for($i=3;$i<13;$i++) {
						  $percent = rand(50,110);
						  if($percent<70) {
							$color = '#d1c594';
						  }
						  else if($percent<100) {
							$color = '#94bfd1';
						  }
						  else if($percent<200) {
							$color = '#8fd1c4';
						  }
						  else {
							$color = 'gray';
						  }
						  echo '
							<tr>
							<td>'.$i.'</td>
							<td>1500TON 프레스'.$i.'호기</td>
							<td class="td_graph">
							  <img class="grp1" src="'.G5_USER_ADMIN_URL.'/img/dot.gif" style="width:'.$percent.'%;background:'.$color.';" height="8px">
							  <img class="grp2" src="'.G5_USER_ADMIN_URL.'/img/dot.gif" style="width:90%" height="2px" title="목표">
							</td>
							<td>'.rand(10000,99999).'</td>
							<td>'.$percent.'%</td>
						  </tr>
						  ';
						}
						?>
					</table>
				</div>
            </div>
        </div>
  
	</div>
    <!-- end of 품질보고서  -->



    <!-- start of 설비보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 설비보고서</i></div>
	<!-- start of .div_left_01 -->
		<div class="div_info_01">
        <div class="div_left_01">
            <div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 설비 에러율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_01.JPG"></div>
                <div class="table_box_01">
                    <table class="item_table_01">
						<table class="item_table_01_date">
						<tr >
							<td>일</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td><td>13</td><td>14</td><td>15</td><td></td>
						</tr>
						<tr>
							<td>%</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
						</tr>
						<tr>
							<td>일</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td><td>21</td><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td><td>27</td><td>28</td><td>29</td><td>30</td><td>31</td>
						</tr>
						<tr>
							<td>%</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
						</tr>
					</table>
					</table>
                </div>
            </div>
            <div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 년 설비 에러율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_03.JPG"></div>
                <div class="table_box_01">
					<table class="item_table_01">
						<tr class="item_th">
							<td>월</td>
							<td>1</td>
							<td>2</td>
							<td>3</td>
							<td>4</td>
							<td>5</td>
							<td>6</td>
							<td>7</td>
							<td>8</td>
							<td>9</td>
							<td>10</td>
							<td>11</td>
							<td>12</td>
						</tr>
						<tr class="item_th1">
							<td>가동율</td>
							<td>98%</td>
							<td>98%</td>
							<td>97%</td>
							<td>98%</td>
							<td>98%</td>
							<td>97%</td>
							<td>98%</td>
							<td>98%</td>
							<td>97%</td>
							<td>98%</td>
							<td>98%</td>
							<td>97%</td>
						</tr>
						<tr class="item_th1">
							<td>예지알람</td>
							<td>33</td>
							<td>10</td>
							<td>54</td>
							<td>34</td>
							<td>12</td>
							<td>65</td>
							<td>10</td>
							<td>54</td>
							<td>34</td>
							<td>12</td>
							<td>65</td>
							<td>65</td>
						</tr>
				</table>
				</div>
            </div>
        </div>
        <!-- end of .div_left_01 -->
		<!-- start of .div_right_01 -->
		<div class="div_right_01">
		<div class="info_box left_info_02">
             <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 설비 에러리스트</i></div>
             <div class="chart_01"><img src="./img/kpi/graph_04_1.JPG"></div>
			 <table class="item_table_01">
				<tr class="item_th">
					<td>트랜스퍼</td>
					<td>프레스</td>
					<td>이형제</td>
					<td>히터기</td>
					<td>기타</td>
				</tr>
				<tr class="item_th01">
					<td>53%</td>
					<td>23%</td>
					<td>33%</td>
					<td>3%</td>
					<td>3%</td>
				</tr>
				<tr class="item_th1">
					<td>8건</td>
					<td>38건</td>
					<td>20건</td>
					<td>41건</td>
					<td>2건</td>
				</tr>
          </table>
         </div>
        </div>
		<div class="div_right_01">
			<div class="info_box left_info_02">
				<div class="title_01"><i class="fa fa-check" aria-hidden="true"> 계획(예방)점검 지표</i></div>
					<table class="item_table_01">
						<tr class="item_th">
							<td>No</td>
							<td>설비명</td>
							<td>내용</td>
							<td>점검 기한</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>프레스 7호기</td>
							<td>프레스 휠 브레이크패드 예방점검</td>
							<td>45일</td>
						</tr>

						<tr class="item_th01">
							<td>1</td>
							<td>프레스 4호기</td>
							<td>프레스 휠 브레이크패드 예방점검</td>
							<td>45일</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>프레스 8호기</td>
							<td>프레스 휠 브레이크패드 예방점검</td>
							<td>45일</td>
						</tr>
					  </table>
				  </div>
				  <!-- end of 계획 예방점검지표 -->
				<div class="title_01"><i class="fa fa-check" aria-hidden="true"> 설비 재고</i></div>
				<table class="item_table_01">
					<tr class="item_th">
						<td>No</td>
						<td>설비명</td>
						<td>규격</td>
						<td>수량</td>
					</tr>
					<tr class="item_th01">
						<td>1</td>
						<td>SERVO AMP</td>
						<td>MR-J4-100B</td>
						<td>1개</td>
					</tr>
					<tr class="item_th01">
						<td>1</td>
						<td>다관절로봇</td>
						<td>HS200L</td>
						<td>1개</td>
					</tr>
					<tr class="item_th01">
						<td>1</td>
						<td>SERVO MOTOR
						<td>HG-SR152B</td>
						<td>1개</td>
					 </tr>
				  </table>
			</div>
			<!-- end of 계획 예방점검지표 -->
		</div>
	<!-- end of .div_right_01 -->
<?php
include_once ('./_tail.php');
?>

