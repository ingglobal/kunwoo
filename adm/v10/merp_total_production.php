<?php
$sub_menu = "955510";
include_once('./_common.php');

$g5['title'] = 'M-ERP 월간보고서';
include_once('./_top_merp_total.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
?>
<style>
</style>

<div class="div_header">
	<div class="div_title1"><b>INGGLOBAL 경주2공장</b></div>
	<div class="div_title2">KPI M-ERP 통합 매출 Report</div>
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
    <!-- the start of .div_stat  -->
	<div class="div_stat1">
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

    <!-- start of 설비보고서  -->
	<div class="div_title_02"><i class="fa fa-plus" aria-hidden="true"> 통합 매출 보고서</i></div>
	<!-- start of .div_left_01 -->
		<div class="div_info_01">
        <div class="div_left_02">
            <div class="info_box left_info_01">
                <div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 월 생산율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_01.JPG"></div>
                <div class="table_box_01">
                    <table class="item_table_04">
						<tr class="item_th">
							<td>작업일수</td>
							<td>작업시간</td>
							<td>목표율</td>
							<td>평균목표량</td>
							<td>평균생산량</td>
						</tr>
						<tr class="item_th01">
							<td>20/31</td>
							<td>310Hour</td>
							<td>98.95%</td>
							<td>13Pallet</td>
							<td>12.8Pallet</td>
						</tr>
									
					</table>
                </div>
            </div>
            <div class="info_box left_info_02">
			<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 월 불량율</i></div>
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
        <!-- end of .div_left_01 -->

		<!-- start of .div_right_01 -->
		<div class="div_info_01">
        <div class="div_right_02">
            <div class="info_box left_info_02">
			<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 설비매출지표</i></div>
               <table class="item_table_04">
						<tr class="item_th">
							<td>No</td>
							<td>설비명</td>
							<td>생산금액</td>
							<td>운영비용</td>
							<td>불량</td>
							<td>월매출</td>
							<td>목표매출</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
						<tr class="item_th01">
							<td>1</td>
							<td>3000T 프레스 2호기</td>
							<td>39,8100,000</td>
							<td>9,090,000</td>
							<td>281,000</td>
							<td>21,670,000</td>
							<td>21,121,000</td>
						</tr>
					</table>
            </div>

        </div>
        <!-- end of .div_right_01 -->
		<!-- 통합 보고서 끝 -->
	    </div>

		<!-- 월 보고서 시작-->
		<div class="div_title_02"><i class="fa fa-plus" aria-hidden="true"> 월 매출 보고서</i></div>
		<!-- start of .div_left_01 -->
		<div class="div_info_01">
        <div class="div_left_01">
            <div class="info_box left_info_01">
                <div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 5월 생산율</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_01.JPG"></div>
                <div class="table_box_01">
                    <table class="item_table_04">
						<tr>
							<td>일</td><td>수량</td><td>비율</td>
							<td>일</td><td>수량</td><td>비율</td>
						</tr>
						<tr>
							<td>1</td><td>3</td><td>4</td><td>3</td><td>16</td><td>3</td>
						</tr>
						<tr>
							<td>2</td><td>3</td><td>4</td><td>3</td><td>17</td><td>3</td>
						</tr>
						<tr>
							<td>3</td><td>3</td><td>4</td><td>3</td><td>18</td><td>3</td>
						</tr>
						<tr>
							<td>4</td><td>3</td><td>4</td><td>3</td><td>19</td><td>3</td>
						</tr>
						<tr>
							<td>5</td><td>3</td><td>4</td><td>3</td><td>20</td><td>3</td>
						</tr>
						<tr>
							<td>6</td><td>3</td><td>4</td><td>3</td><td>16</td><td>3</td>
						</tr>
						<tr>
							<td>7</td><td>3</td><td>4</td><td>3</td><td>17</td><td>3</td>
						</tr>
						<tr>
							<td>8</td><td>3</td><td>4</td><td>3</td><td>18</td><td>3</td>
						</tr>
						<tr>
							<td>9</td><td>3</td><td>4</td><td>3</td><td>19</td><td>3</td>
						</tr>
						<tr>
							<td>10</td><td>3</td><td>4</td><td>3</td><td>20</td><td>3</td>
						</tr>
						<tr>
							<td>6</td><td>3</td><td>4</td><td>3</td><td>21</td><td>3</td>
						</tr>
						<tr>
							<td>7</td><td>3</td><td>4</td><td>3</td><td>22</td><td>3</td>
						</tr>
						<tr>
							<td>8</td><td>3</td><td>4</td><td>3</td><td>23</td><td>3</td>
						</tr>
						<tr>
							<td>9</td><td>3</td><td>4</td><td>3</td><td>24</td><td>3</td>
						</tr>
						<tr>
							<td>10</td><td>3</td><td>4</td><td>3</td><td>25</td><td>3</td>
						</tr>
						<tr>
							<td>11</td><td>3</td><td>4</td><td>3</td><td>26</td><td>3</td>
						</tr>
						<tr>
							<td>12</td><td>3</td><td>4</td><td>3</td><td>27</td><td>3</td>
						</tr>
						<tr>
							<td>13</td><td>3</td><td>4</td><td>3</td><td>28</td><td>3</td>
						</tr>
						<tr>
							<td>14</td><td>3</td><td>4</td><td>3</td><td>29</td><td>3</td>
						</tr>
						<tr>
							<td>15</td><td>3</td><td>4</td><td>3</td><td>30</td><td>3</td>
						</tr>
						<tr>
							<td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
							
						</tr>						
					</table>
                </div>
			</div>
			<div class="info_box left_info_02">
				<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 월 매출 지표</i></div>
				<table class="item_table_05">
					<tr class="item_th">
						<td>생산금액</td>
						<td>운영비용</td>
						<td>내용</td>
						<td>월 매출</td>
					</tr>
					<tr class="item_th01">
						<td>44,150,000</td>
						<td>13,200,000</td>
						<td>340,000</td>
						<td>340,000</td>
					</tr>
					<tr class="item_th01">
						<td>44,150,000</td>
						<td>13,200,000</td>
						<td>340,000</td>
						<td>340,000</td>
					</tr>
				</table>
            </div>
			<div class="info_box left_info_01">
				<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 감가상각</i></div>
					<table class="item_table_05">
					<tr class="item_th">
						<td> 20%</td>
						<td class="item_th1"> 예상 : 24주</td>
					</tr>
				</table>
            </div>
		</div>
		
        <!-- end of .div_left_01 -->
		<!-- start .div_right_01 -->
        <div class="div_right_01">
		<div class="info_box left_info_02">
                <div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 5월 불량율</i></div>
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
			
			<div class="info_box left_info_01">
				<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 월 매출액 그래프</i></div>
					<table class="item_table_04">
					<tr class="item_th">
						<td></td>
						<td>설비도입가</td>
						<td>일일지출비용</td>
						<td>일일생산비용</td>
					</tr>
					<tr class="item_th01">
						<td>프레스</td>
						<td>1,564,500</td>
						<td>1,564,500</td>
						<td>1,564,500</td>
					</tr>
					<tr class="item_th01">
						<td>트렌스퍼</td>
						<td>1,564,500</td>
						<td>1,564,500</td>
						<td>1,564,500</td>
					</tr>
				</table>
            </div>
			<div class="chart_01"><img src="./img/kpi/graph_06.jpg"></div>
        </div>
		<!-- start of .div_right_01 -->
		<div class="div_info_01">
        <div class="div_right_01">
            
        </div>
        <!-- end of .div_right_01 -->
        <!-- end of .div_left_01 -->	
	</div>
	<!-- 월 보고서 끝-->


	<!-- 일 보고서 시작-->
	<div class="div_title_02"><i class="fa fa-plus" aria-hidden="true"> 일 보고서</i></div>
	<!-- start of .div_left_01 -->
	<div class="div_info_01">
        <div class="div_left_01">
            <div class="info_box left_info_01">
                <div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 5월 15일 생산</i></div>
                <div class="chart_01"><img src="./img/kpi/graph_05.JPG"></div>
                <div class="table_box_01">
                   <table class="item_table_04">
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
	<!-- end of div_left_01-->
	<!-- start of div_right_01-->
	<div class="div_right_01">
		<div class="info_box left_info_01">
			<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 일 매출 지표</i></div>
			<table class="item_table_05">
				<tr class="item_th">
					<td>생산금액</td>
					<td>운영비용</td>
					<td>불량손실액</td>
					<td>일 매출</td>
				</tr>
				<tr class="item_th01">
					<td>2,207,500</td>
					<td>660,000</td>
					<td>17,000</td>
					<td>1,564,500</td>
				</tr>
			</table>
		</div>
		<div class="info_box left_info_01">
			<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 매출액그래프</i></div>
			<div class="chart_01"><img src="./img/kpi/graph_01.JPG"></div>
			<table class="item_table_04">
				<tr class="item_th">
				    <td></td>
					<td>설비도입가</td>
					<td>일일지출비용</td>
					<td>일일생산비용</td>
				</tr>
				<tr class="item_th01">
					<td>프레스</td>
					<td>1,564,500</td>
					<td>1,564,500</td>
					<td>1,564,500</td>
				</tr>
				<tr class="item_th01">
					<td>트렌스퍼</td>
					<td>1,564,500</td>
					<td>1,564,500</td>
					<td>1,564,500</td>
				</tr>
			</table>
		</div>
	
	<div class="info_box left_info_02">
			<div class="title_01_merp"><i class="fa fa-check" aria-hidden="true"> 월 불량</i></div>
			<div class="chart_01"><img src="./img/kpi/graph_04.JPG"></div>
			<div class="table_box_01">
				<table class="item_table_04">
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
		
	<!-- end of .div_right_01 -->
</div>
	<!-- 일 보고서 끝-->   
<?php
include_once ('./_tail.php');
?>

