<?php
$sub_menu = "955510";
include_once('./_common.php');

$g5['title'] = 'M-ERP 월간보고서';
include_once('./_top_merp_daily.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
?>
<style>
</style>

<div class="div_header" >
 <div class="div_title1"><b>M-ERP 일 매출 보고서</b></div>
  <div class="div_title2">INGGLOBAL 경주2공장</div><div class="div_title5"><b>일 매출 보고서</b></div><br>
   <div><br></div>
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

<div class="div_graphs">
    <div class="div_left">
        <div class="item_title">
          일 생산
          <div class="item_title_icons">
            <i class="fa fa-bar-chart"></i>
            <i class="fa fa-plus"></i>
          </div>
        </div>
        <div class="item_content">
          <img src="./img/kpi/q_01.JPG">
		  <table class="item_table_01">
			  <tr class="item_th">
				  <td>일</td><td></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td>
				  <td>11</td><td>12</td><td>13</td><td>14</td><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td>
				  <td>21</td><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td><td>27</td><td>28</td><td>29</td><td>30</td><td>31</td>
			   </tr>
			   <tr class="item_th1">
				  <td>수량</td><td></td><td>26</td><td>30 </td><td>26</td><td>4</td><td>40</td><td>26</td><td>4 </td><td>40</td><td>30</td><td> 50</td>
				  <td>11</td><td>26</td><td>13</td><td>30</td><td>30</td><td>20</td><td>26</td><td>26</td><td>30 </td><td> 26</td>
				  <td>21</td><td>12</td><td>50</td><td>50</td><td>26</td><td>40</td><td>26</td><td>40</td><td>26 </td><td>40 </td><td>50</td>
			   </tr>
		 </table>
         </div>
        <div class="item_title">일 불량율</div>
                <img src="./img/kpi/q_02.JPG">
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
	<div class="div_right">
				<div class="item_title">
				 일 매출 지표
					  <div class="item_title_icons">
						<i class="fa fa-bar-chart"></i>
						<i class="fa fa-plus"></i>
				</div>
		</div>
        <div class="item_content">
            <table class="item_table_01">
				   <tr class="item_th">
				      <td>생산금액</td>
				      <td>운영비용</td>
				      <td>불량손실액</td>
				      <td>일 매출</td>
				    </tr>
				    <tr class="item_th1">
			  	      <td>2,207,500</td>
				      <td>2,207,500</td>
				      <td>2,207,500</td>
				      <td>2,207,500</td>
				    </TR>
				</table>
        </div>
    </div>
</div>


    </div>
</div>

 
    

    
<?php
include_once ('./_tail.php');
?>
