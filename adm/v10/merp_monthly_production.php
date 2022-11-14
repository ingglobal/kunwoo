<?php
$sub_menu = "955510";
include_once('./_common.php');

$g5['title'] = 'M-ERP 월간보고서';
include_once('./_top_merp_monthly.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/kpi.css">', 0);
?>
<style>
</style>

<div class="div_header" >
 <div class="div_title1"><b>M-ERP 월 매출 보고서</b></div>
  <div class="div_title2">INGGLOBAL 경주2공장</div><div class="div_title5"><b>월 매출 보고서</b></div><br>
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


<div class="div_stat2">
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
        <div class="item_title">월 생산</div>
        <img src="<?=G5_DATA_URL?>/tmp1/graph11.png">
        <div class="item_title">년 생산</div>
        <img src="<?=G5_DATA_URL?>/tmp1/graph12.png">
    
    </div>
    <div class="div_right">
        <div class="item_title">월 설비 생산 지표</div>
        <img src="<?=G5_DATA_URL?>/tmp1/graph13.png">

    </div>
</div>

<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tbody>
    <tr>
      <th width="40%" align="center" valign="top" scope="row"><table width="90%" border="0" cellpadding="2" cellspacing="1">
        <tbody>
          <tr>
            <th height="25" align="left" bgcolor="#E0E0E0" scope="row"> 월생산</th>
          </tr>
          <tr>
            <th height="258" valign="top" bgcolor="#ffffff" scope="row">그래픽 이미지</th>
          </tr>
          <tr>
            <th height="40" bgcolor="#E0E0E0" scope="row"><table width="100%" border="0" cellpadding="2" cellspacing="1">
              <tbody>
                <tr>
                  <th width="12%" height="22" valign="top" bgcolor="#ffffff" scope="row">수량</th>
                  <th width="8%" height="20" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" height="20" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" height="20" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                  <th width="8%" valign="top" bgcolor="#ffffff" scope="row">12.2</th>
                </tr>
                <tr>
                  <th height="22" bgcolor="#E0E0E0" scope="row">목표</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                  <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                </tr>
              </tbody>
            </table></th>
          </tr>
        </tbody>
      </table></th>
      <th width="60%" height="393" align="center" valign="top" scope="row"><table width="90%" border="0" cellpadding="2" cellspacing="1">
        <tbody>
          <tr>
            <th height="25" align="left" bgcolor="#E0E0E0" scope="row"> 월설비 생산지표<br></th>
            </tr>
          <tr>
            <th height="190" valign="top" bgcolor="#ffffff" scope="row"><br>
              <table width="90%" border="0" cellpadding="2" cellspacing="1">
                <tbody>
                  <tr>
                    <th width="10%" height="25" align="center" bgcolor="#E0E0E0" scope="row">No</th>
                    <th width="24%" align="center" bgcolor="#E0E0E0" scope="row">설비명</th>
                    <th width="36%" align="center" bgcolor="#E0E0E0" scope="row">설비별 생산율</th>
                    <th width="15%" align="center" bgcolor="#E0E0E0" scope="row">수량</th>
                    <th width="15%" align="center" bgcolor="#E0E0E0" scope="row">비율</th>
                    </tr>
                  <tr>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>1</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>1500TON 프레스1호기</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>113434</strong></th>
                    <th valign="top" bgcolor="#ffffff" scope="row">104%</th>
                    </tr>
                  <tr>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>2</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>1500TON 프레스1호기</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    <th valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    </tr>
                  <tr>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>3</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>1500TON 프레스1호기</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    <th valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    </tr>
                  <tr>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>4</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row"><strong>1500TON 프레스1호기</strong></th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    <th height="20" valign="top" bgcolor="#ffffff" scope="row">&nbsp;</th>
                    </tr>
                  <tr>
                    <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                    <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                    <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                    <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                    <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
                    </tr>
                  </tbody>
              </table></th>
            </tr>
          <tr>
            <th bgcolor="#E0E0E0" scope="row">&nbsp;</th>
            </tr>
          </tbody>
      </table></th>
    </tr>
  </tbody>
</table>


<?php
include_once ('./_tail.php');
?>

