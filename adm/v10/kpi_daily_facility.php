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

<div class="div_header" >
	<div class="div_title1"><b>KPI 5월 1일 Report</b></div>
	<div class="div_title2">INGGLOBAL 경주2공장</div>
	<div class="div_title4"><b>생산(Production)Report</b></div>
		<div class="div_calendar">
			<ul>
				<li><i class="fa fa-chevron-circle-left"></i>&nbsp;</li>
				<li>
					<input type="text" color="#cecece" value="2020-05-01">
				</li>
				<li><i class="fa fa-chevron-circle-right"></i>&nbsp;</li>
			</ul>
        </div>
    </div>
	<div class="div_stat1">
		<ul>
			<li>
				<span class="title">목표달성율</span>
				<span class="content"><?=rand(80,90)?>.95</span>
				<span class="unit">%</span>
			</li>
			<li>
				<span class="title">작업시간</span>
				<span class="content"><?=rand(80,90)?>.15</span>
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



<div class="div_graphs">
    <div class="div_left">
       <div class="item_title">시간별 설비 에러율</div>
       <img src="./img/kpi/q_01.JPG">
		<table class="item_table_03">
			  <tr class="item_th">
					 <td>시</td><td></td><td></td><td>01</td><td>.</td><td>02</td><td>.</td><td>03</td><td>.</td><td>04</td><td>.</td><td>05</td><td>.</td>
					 <td>06</td><td>.</td><td>07</td><td>.</td><td>08</td><td>.</td><td>09</td><td>.</td><td>10</td><td>.</td>
					 <td>11</td><td>.</td><td>12</td><td>.</td><td>13</td><td>.</td><td>14</td><td>.</td><td>15</td><td>.</td>
					 <td>16</td><td>.</td><td>17</td><td>.</td><td>18</td><td>.</td><td>19</td><td>.</td><td>20</td><td>.</td>
					 <td>21</td><td>.</td><td>22</td><td>.</td><td>23</td><td>.</td><td>24</td><td>
			  </tr>
			  <tr class="item_th1">
					 <td>에러건</td><td></td><td></td><td>3</td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td>15</td><td>05</td><td>99</td>
					 <td></td><td></td><td> </td><td>15</td><td> </td><td> </td><td>09</td><td></td><td> </td><td> </td>
					 <td></td><td>15</td><td> </td><td>15</td><td>3</td><td> </td><td>3</td><td> </td><td>15</td><td> </td>
					 <td> </td><td> </td><td> </td><td> </td><td> </td><td></td><td>11</td><td> </td><td> </td><td>12</td>
					 <td>10</td><td> </td><td> </td><td> </td><td>10</td><td> </td><td> </td><td>
			  </tr>
		</table>
		<div class="item_title">월 설비 에러율</div>
		<img src="./img/kpi/q_01_01.JPG">
		<table class="item_table_01">
			<tr class="item_th">
					  <td>일</td><td></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td>
					 <td>11</td><td>12</td><td>13</td><td>14</td><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td>
					 <td>21</td><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td><td>27</td><td>28</td><td>29</td><td>30</td><td>31</td>
			</tr>
			<tr class="item_th1">
					  <td>에러건</td><td></td><td> </td><td> </td><td> </td><td>4</td><td>40</td><td> </td><td>4 </td><td> </td><td> </td><td> </td>
					 <td>11</td><td> </td><td>13</td><td> </td><td> </td><td>20 </td><td> </td><td> </td><td> </td><td> </td>
					 <td>21</td><td>12</td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
			</tr>
	   </table> 
</div>
<div class="div_right">
        <div class="item_title">월 설비 에러 리스트</div>
        <img src="./img/kpi/q_02.JPG">
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
			<tr class="item_th">
              <td>8건</td>
              <td>38건</td>
              <td>20건</td>
              <td>41건</td>
              <td>2건</td>
            </tr>
          </table>
        <div class="item_title">계획(예방)점검 지표</div>
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
              <td>45일</td>
			</tr>
			</table>
        <div class="item_title">에러지표</div>
        <table class="item_table_01">
            <tr class="item_th">
              <td>No</td>
              <td>설비명</td>
              <td>품명</td>
              <td>내용</td>
			  <td>시간</td>
			 </tr>
            <tr class="item_th01">
              <td>1</td>
              <td>2500T 프레스1호기</td>
			  <td>프레스</td>
              <td>"Z"축 박스가이드","과부하","6회","발생"</td>
              <td>02:52</td>
			</tr>
		    <tr class="item_th01">
              <td>1</td>
              <td>2500T 프레스1호기</td>
			  <td>프레스</td>
              <td>"Z"축 박스가이드","과부하","6회","발생"</td>
              <td>02:52</td>
			</tr>
           <tr class="item_th01">
              <td>1</td>
              <td>2500T 프레스3호기</td>
			  <td>트랜스퍼</td>
              <td>"1"모터,"고온(7도)1회","발생"</td>
              <td>02:52</td>
			</tr>
          </table>

    </div>
</div>




<?php
include_once ('./_tail.php');
?>
