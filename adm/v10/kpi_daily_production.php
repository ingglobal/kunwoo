<?php
$sub_menu = "955420";
include_once('./_common.php');

$g5['title'] = 'KPI 일간보고서';
include_once('./_top_kpi_daily.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
?>
<style>
</style>


<div class="div_header">
	<div class="div_title1"><b>INGGLOBAL 경주2공장</b></div>
	<div class="div_title2">KPI 5월 15일 통합 Report</div>
	<div class="div_calendar">
		<ul>
			<li><i class="fa fa-chevron-circle-left"></i>&nbsp;</li>
			<li>
				<input type="text" color="#cecece" value="2020-05일-15일">
			</li>
			<li><i class="fa fa-chevron-circle-right"></i>&nbsp;</li>
		</ul>
	</div>
</div>
<div class="div_report_button">
	<ul class="button">
		<li><a href="kpi_monthly_production.php">월 보고서</a></li>
		<li class="on"><a href="kpi_daily_production.php">일 보고서</a></li>
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
			<div class="title_01"><i class="fa fa-check" aria-hidden="true"> 5월 15일 시간별 생산</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_05.JPG"></div>
                <div class="table_box_01">
                    <table class="item_table_01">
						<tr>
							<td>시간</td><td>에러</td>
							<td>시간</td><td>에러</td>
							<td>시간</td><td>에러</td>
						</tr>
						<tr>
							<td>00:00</td><td>4</td>
							<td>08:00</td><td>4</td>
							<td>16:00</td><td>5</td>
						</tr>
						<tr>
							<td>00:30</td><td>4</td>
							<td>08:30</td><td>4</td>
							<td>16:30</td><td>5</td>
						</tr>
						<tr>
							<td>01:00</td><td>4</td>
							<td>09:00</td><td>4</td>
							<td>17:00</td><td>5</td>
						</tr>
						<tr>
							<td>01:30</td><td>4</td>
							<td>09:30</td><td>4</td>
							<td>17:30</td><td>5</td>
						</tr>
						<tr>
							<td>02:00</td><td>4</td>
							<td>10:00</td><td>4</td>
							<td>18:00</td><td>5</td>
						</tr>
						<tr>
							<td>02:30</td><td>4</td>
							<td>10:30</td><td>4</td>
							<td>18:30</td><td>5</td>
						</tr>
						<tr>
							<td>03:00</td><td>4</td>
							<td>11:00</td><td>4</td>
							<td>19:00</td><td>5</td>
						</tr>
						<tr>
							<td>03:30</td><td>4</td>
							<td>11:30</td><td>4</td>
							<td>19:30</td><td>5</td>
						</tr>
						<tr>
							<td>04:00</td><td>4</td>
							<td>12:00</td><td>4</td>
							<td>20:00</td><td>5</td>
						</tr>
						<tr>
							<td>04:30</td><td>4</td>
							<td>12:30</td><td>4</td>
							<td>20:30</td><td>5</td>
						</tr>
						<tr>
							<td>05:00</td><td>4</td>
							<td>13:00</td><td>4</td>
							<td>21:00</td><td>5</td>
						</tr>
						<tr>
							<td>05:30</td><td>4</td>
							<td>13:30</td><td>4</td>
							<td>21:30</td><td>5</td>
						</tr>
						<tr>
							<td>06:00</td><td>4</td>
							<td>14:00</td><td>4</td>
							<td>22:00</td><td>5</td>
						</tr>
						<tr>
							<td>06:30</td><td>4</td>
							<td>14:30</td><td>4</td>
							<td>22:30</td><td>5</td>
						</tr>
						<tr>
							<td>07:00</td><td>4</td>
							<td>15:00</td><td>4</td>
							<td>23:00</td><td>5</td>
						</tr>
						<tr>
							<td>07:30</td><td>4</td>
							<td>15:30</td><td>4</td>
							<td>23:30</td><td>5</td>
						</tr>	
					</table>	
                </div>
		</div>
		</div>
        <!-- end of .div_left_01 -->
		<!-- start .div_right_01 -->

        <div class="div_right_01">
		<div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 생산률</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_03.JPG"></div>
                <div class="table_box_01">
					<table class="item_table_01">
						<tr class="item_th">
							<td>작업일수</td>
							<td>작업시간</td>
							<td>목표율</td>
							<td>평균목표량</td>
							<td>평균생산량</td>
						</tr>
						<tr>
							<td>19/31</td>
							<td>310 hours</td>
							<td>96.95%</td>
							<td>13 Plt</td>
							<td>12.8 Plt</td>
						</tr>
					  </table>
				</div>
            </div>
            <div class="info_box right_info_02">
                <div class="title_01">일 설비 생산 지표</div>
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
							<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="10px">
							<img class="grp2" src="" style="width:90%" height="2px">
						  </td>
						  <td><?=rand(10000,99999)?></td>
						  <td><?=rand(70,110)?>%</td>
						</tr>
						<tr>
						  <td>2</td>
						  <td>1500TON 프레스1호기</td>
						  <td class="td_graph">
							<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="10px">
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
							  <img class="grp1" src="'.G5_USER_ADMIN_URL.'/img/dot.gif" style="width:'.$percent.'%;background:'.$color.';" height="10px">
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
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 5월 15일 시간별 불량율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_05.JPG"></div>
                <div class="table_box_01">
                    <table class="item_table_01">
						<tr>
							<td>시간</td><td>에러</td>
							<td>시간</td><td>에러</td>
							<td>시간</td><td>에러</td>
						</tr>
						<tr>
							<td>00:00</td><td>4</td>
							<td>08:00</td><td>4</td>
							<td>16:00</td><td>5</td>
						</tr>
						<tr>
							<td>00:30</td><td>4</td>
							<td>08:30</td><td>4</td>
							<td>16:30</td><td>5</td>
						</tr>
						<tr>
							<td>01:00</td><td>4</td>
							<td>09:00</td><td>4</td>
							<td>17:00</td><td>5</td>
						</tr>
						<tr>
							<td>01:30</td><td>4</td>
							<td>09:30</td><td>4</td>
							<td>17:30</td><td>5</td>
						</tr>
						<tr>
							<td>02:00</td><td>4</td>
							<td>10:00</td><td>4</td>
							<td>18:00</td><td>5</td>
						</tr>
						<tr>
							<td>02:30</td><td>4</td>
							<td>10:30</td><td>4</td>
							<td>18:30</td><td>5</td>
						</tr>
						<tr>
							<td>03:00</td><td>4</td>
							<td>11:00</td><td>4</td>
							<td>19:00</td><td>5</td>
						</tr>
						<tr>
							<td>03:30</td><td>4</td>
							<td>11:30</td><td>4</td>
							<td>19:30</td><td>5</td>
						</tr>
						<tr>
							<td>04:00</td><td>4</td>
							<td>12:00</td><td>4</td>
							<td>20:00</td><td>5</td>
						</tr>
						<tr>
							<td>04:30</td><td>4</td>
							<td>12:30</td><td>4</td>
							<td>20:30</td><td>5</td>
						</tr>
						<tr>
							<td>05:00</td><td>4</td>
							<td>13:00</td><td>4</td>
							<td>21:00</td><td>5</td>
						</tr>
						<tr>
							<td>05:30</td><td>4</td>
							<td>13:30</td><td>4</td>
							<td>21:30</td><td>5</td>
						</tr>
						<tr>
							<td>06:00</td><td>4</td>
							<td>14:00</td><td>4</td>
							<td>22:00</td><td>5</td>
						</tr>
						<tr>
							<td>06:30</td><td>4</td>
							<td>14:30</td><td>4</td>
							<td>22:30</td><td>5</td>
						</tr>
						<tr>
							<td>07:00</td><td>4</td>
							<td>15:00</td><td>4</td>
							<td>23:00</td><td>5</td>
						</tr>
						<tr>
							<td>07:30</td><td>4</td>
							<td>15:30</td><td>4</td>
							<td>23:30</td><td>5</td>
						</tr>	
					</table>	
                </div>
            </div>
            <div class="info_box left_info_02">
            <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 불량율</i></div>
                <div class="table_box_01">
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
								<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="10px">
								<img class="grp2" src="" style="width:90%" height="2px">
							</td>
							<td><?=rand(10000,99999)?></td>
							<td><?=rand(70,110)?>%</td>
						</tr>
						<tr>
							<td>2</td>
							<td>1500TON 프레스1호기</td>
							<td class="td_graph">
								<img class="grp1" src="" style="width:<?=rand(60,98)?>%;background:#72ddf5;" height="10px">
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
								  <img class="grp1" src="'.G5_USER_ADMIN_URL.'/img/dot.gif" style="width:'.$percent.'%;background:'.$color.';" height="10px">
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
        <!-- end of .div_left_01 -->
		<!-- start of .div_right_01 -->
		<div class="div_right_01">
		<div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 5월 생산</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_04.JPG"></div>
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
  
	</div>
    <!-- end of 품질보고서  -->



    <!-- start of 설비보고서  -->
	<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 설비보고서</i></div>
	<!-- start of .div_left_01 -->
		<div class="div_info_01">
        <div class="div_left_01">
            <div class="info_box left_info_01">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 5월 15일 설비 에러율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_05.JPG"></div>
                <div class="table_box_01">
                     <table class="item_table_01">
						<tr>
							<td>시간</td><td>에러</td>
							<td>시간</td><td>에러</td>
							<td>시간</td><td>에러</td>
						</tr>
						<tr>
							<td>00:00</td><td>4</td>
							<td>08:00</td><td>4</td>
							<td>16:00</td><td>5</td>
						</tr>
						<tr>
							<td>00:30</td><td>4</td>
							<td>08:30</td><td>4</td>
							<td>16:30</td><td>5</td>
						</tr>
						<tr>
							<td>01:00</td><td>4</td>
							<td>09:00</td><td>4</td>
							<td>17:00</td><td>5</td>
						</tr>
						<tr>
							<td>01:30</td><td>4</td>
							<td>09:30</td><td>4</td>
							<td>17:30</td><td>5</td>
						</tr>
						<tr>
							<td>02:00</td><td>4</td>
							<td>10:00</td><td>4</td>
							<td>18:00</td><td>5</td>
						</tr>
						<tr>
							<td>02:30</td><td>4</td>
							<td>10:30</td><td>4</td>
							<td>18:30</td><td>5</td>
						</tr>
						<tr>
							<td>03:00</td><td>4</td>
							<td>11:00</td><td>4</td>
							<td>19:00</td><td>5</td>
						</tr>
						<tr>
							<td>03:30</td><td>4</td>
							<td>11:30</td><td>4</td>
							<td>19:30</td><td>5</td>
						</tr>
						<tr>
							<td>04:00</td><td>4</td>
							<td>12:00</td><td>4</td>
							<td>20:00</td><td>5</td>
						</tr>
						<tr>
							<td>04:30</td><td>4</td>
							<td>12:30</td><td>4</td>
							<td>20:30</td><td>5</td>
						</tr>
						<tr>
							<td>05:00</td><td>4</td>
							<td>13:00</td><td>4</td>
							<td>21:00</td><td>5</td>
						</tr>
						<tr>
							<td>05:30</td><td>4</td>
							<td>13:30</td><td>4</td>
							<td>21:30</td><td>5</td>
						</tr>
						<tr>
							<td>06:00</td><td>4</td>
							<td>14:00</td><td>4</td>
							<td>22:00</td><td>5</td>
						</tr>
						<tr>
							<td>06:30</td><td>4</td>
							<td>14:30</td><td>4</td>
							<td>22:30</td><td>5</td>
						</tr>
						<tr>
							<td>07:00</td><td>4</td>
							<td>15:00</td><td>4</td>
							<td>23:00</td><td>5</td>
						</tr>
						<tr>
							<td>07:30</td><td>4</td>
							<td>15:30</td><td>4</td>
							<td>23:30</td><td>5</td>
						</tr>	
					</table>	
                </div>
            </div>
            <div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 5월 설비 에러율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_03.JPG"></div>
                <div class="table_box_01">
					<table class="item_table_01_date">
						<tr>
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
				</div>
            </div>
        </div>
        <!-- end of .div_left_01 -->
		<!-- start of .div_right_01 -->
		<div class="div_right_01">
		<div class="info_box left_info_02">
             <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 설비 에러리스트</i></div>
             <div class="chart_01"><img src="./img/kpi/graph_04.JPG"></div>
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
				<tr class="item_th01">
					<td>8건</td>
					<td>38건</td>
					<td>20건</td>
					<td>41건</td>
					<td>2건</td>
				</tr>
				<tr class="item_th01">
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
						  <td>45일</td></tr>
						  
						<tr class="item_th01">
						  <td>1</td>
						  <td>프레스 4호기</td>
						  <td>프레스 휠 브레이크패드 예방점검</td>
						  <td>45일</td></tr>
						 
						<tr class="item_th01">
						  <td>1</td>
						  <td>프레스 8호기</td>
						  <td>프레스 휠 브레이크패드 예방점검</td>
						  <td>45일</td>
						</tr>
						<tr class="item_th01">
						  <td>1</td>
						  <td>프레스 8호기</td>
						  <td>프레스 휠 브레이크패드 예방점검</td>
						  <td>45일</td>
						</tr>
						<tr class="item_th01">
						  <td>1</td>
						  <td>프레스 8호기</td>
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
				<!-- start of 설비재고 -->
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
		        <!-- end of 설비 재고 -->
		</div>
	    <!-- end of .div_right_01 -->
<?php
include_once ('./_tail.php');
?>

