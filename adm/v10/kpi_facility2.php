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
		<li><a href="kpi_daily_production.php">일 보고서</a></li>
		<li class="on"><a href="kpi_facility2.php">장비별 보고서</a></li>
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
<div class="div_title_01"><i class="fa fa-plus" aria-hidden="true"> 경주공장 2500ton 설비 생산 보고서</i></div>
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

		<div class="info_box left_info_02">
                <div class="title_01"><i class="fa fa-check" aria-hidden="true"> 월 불량율</i></div>
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
		</div>
        <!-- end of .div_left_01 -->
		<!-- start .div_right_01 -->

        <div class="div_right_01">
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
            <div class="info_box right_info_02">
                <div class="title_01">에러 알람</div>
                <div class="item_content">
					<table class="item_table_01">
						<tr class="item_th">
							<td>1</td>
							<td>내용</td>
							<td>횟수</td>
							<td>시간</td>
						</tr>
						<tr class="item_th1">
							<td>2</td>
							<td>‘’스핀들모터’’ ‘’고RPM(↑21RPM)’’  발생</td>
							<td>33%</td>
							<td>06:31</td>
						</TR>
						<tr class="item_th1">
							<td>3</td>
							<td>‘’프레스지그’’ ‘’공차(11mm)‘’  발생</td>
							<td>33%</td>
							<td>06:31</td>
						</TR>
						<tr class="item_th1">
							<td>4</td>
							<td>“Z축 볼스크류“ “과부화 발생</td>
							<td>33%</td>
							<td>06:31</td>
						</TR>
						<tr class="item_th1">
							<td>5</td>
							<td>“Z축 볼스크류“ “과부화 발생</td>
							<td>33%</td>
							<td>06:31</td>
						</TR>
					</table>	
				</div>
			</div>
			<div class="info_box right_info_02">
                <div class="title_01">예지 알람</div>
                <div class="item_content">
					<table class="item_table_01">
						<tr class="item_th">
							<td>1</td>
							<td>내용</td>
							<td>횟수</td>
						</tr>
						<tr class="item_th1">
							<td>2</td>
							<td>‘’Z축 박스가이드 과부화 3회 발생으로 인한 점검 필요‘’ (#17)</td>
							<td>3회</td>
						</TR>
						<tr class="item_th1">
							<td>3</td>
							<td>‘’프레스지그 공차 2회 발생으로 인한 점검 필요’’ (#02)</td>
							<td>2회</td>
						</TR>
						<tr class="item_th1">
							<td>4</td>
							<td>‘’12번 공압솔 유압 낙하 2회 발생으로 인한 점검 필요’’ (#11)</td>
							<td>1회</td>
						</TR>
						<tr class="item_th1">
							<td>5</td>
							<td>“Z축 볼스크류“ “과부화 발생</td>
							<td>회</td>
						</TR>
					</table>	
				</div>
			</div>
            </div>
        </div>
        <!-- end of .div_right_01 -->
        <!-- end of .div_info_01 -->


<?php
include_once ('./_tail.php');
?>

