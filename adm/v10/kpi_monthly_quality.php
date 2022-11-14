<?php
$sub_menu = "955410";
include_once('./_common.php');

$g5['title'] = 'KPI 월간보고서';
include_once('./_top_kpi_monthly.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
?>
<style>
</style>

<div class="div_header" >
 <div class="div_title1"><b>KPI 5월 Report</b></div>
  <div class="div_title2">INGGLOBAL 경주2공장</div> <div class="div_title3"><b>품질(Quality)Report</b></div><br>
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
        <div class="item_title">월 불량지표</div>
				<img src="./img/kpi/q_01.JPG">
				<table class="item_table_01">
				  <tr class="item_th">
							  <td>일</td><td></td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td>
							 <td>11</td><td>12</td><td>13</td><td>14</td><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td><td>20</td>
							 <td>21</td><td>22</td><td>23</td><td>24</td><td>25</td><td>26</td><td>27</td><td>28</td><td>29</td><td>30</td><td>31</td>
				   </tr>
				   <tr class="item_th1">
							  <td>형상불량</td><td></td><td> </td><td> </td><td> </td><td>4</td><td>40</td><td> </td><td>4 </td><td> </td><td> </td><td> </td>
							 <td>11</td><td> </td><td>13</td><td> </td><td> </td><td>20 </td><td> </td><td> </td><td> </td><td> </td>
							 <td>21</td><td>12</td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
				   </tr>
				    <tr class="item_th">
							  <td>치수불량</td><td></td><td> </td><td> </td><td> </td><td>4</td><td>40</td><td> </td><td> </td><td>10 </td><td> </td><td> </td>
							 <td>11</td><td> </td><td>13</td><td> </td><td> </td><td>2 </td><td> </td><td> </td><td> </td><td> </td>
							 <td>21</td><td>12</td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
				   </tr>
				    <tr class="item_th1">
							  <td>기타불</td><td></td><td> </td><td> </td><td> </td><td>4</td><td>40</td><td> </td><td> </td><td> </td><td> </td><td> </td>
							 <td>11</td><td> </td><td>3</td><td> </td><td> </td><td>20 </td><td> </td><td> </td><td> </td><td> </td>
							 <td>21</td><td>12</td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
				   </tr>
				    <tr class="item_th">
							  <td>기타불량</td><td></td><td> </td><td> </td><td> </td><td>4</td><td>40</td><td> </td><td> </td><td> </td><td> </td><td> </td>
							 <td>11</td><td> </td><td>13</td><td> </td><td> </td><td>20 </td><td> </td><td> </td><td> </td><td> </td>
							 <td>21</td><td>12</td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
				   </tr>
				    <tr class="item_th1">
							  <td>통합</td><td></td><td> </td><td> </td><td> </td><td>4</td><td>40</td><td> </td><td> </td><td> </td><td> </td><td> </td>
							 <td>11</td><td> </td><td>13</td><td> </td><td> </td><td>20 </td><td> </td><td> </td><td> </td><td> </td>
							 <td>21</td><td>12</td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td><td> </td>
				   </tr>
				</table>
			<div class="div_left">	
        <div class="item_title">월 불량율</div>
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
                $color = '#ffa8a8';
              }
              else if($percent<100) {
                $color = '#72ddf5';
              }
              else if($percent<200) {
                $color = '#ffd400';
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
</div>

 
    

    
<?php
include_once ('./_tail.php');
?>
