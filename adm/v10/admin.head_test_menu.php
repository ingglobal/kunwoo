<?php
if (!defined('_GNUBOARD_')) exit;
?>
<!--li class="tnb_li"><a href="<?php //echo G5_DEVICE_URL ?>/half_output/form.php" class="tnb_sql">반제품출력API</a></!--li>
<li class="tnb_li"><a href="<?php //echo G5_DEVICE_URL ?>/half_end/form.php" class="tnb_sql">반제품종료API</a></li>
<li class="tnb_li"><a href="<?php //echo G5_DEVICE_URL ?>/half_status/form.php" class="tnb_sql">반제품상태API</a></li>
<li class="tnb_li"><a href="<?php //echo G5_DEVICE_URL ?>/item_output/form.php" class="tnb_sql">완제품출력API</a></li>
<li class="tnb_li"><a href="<?php //echo G5_DEVICE_URL ?>/item_end/form.php" class="tnb_sql">완제품종료API</a></li>
<li class="tnb_li"><a href="<?php //echo G5_DEVICE_URL ?>/item_status/form.php" class="tnb_sql">완제품상태API</a></li>
<li class="tnb_li"><a href="<?php //echo G5_DEVICE_URL ?>/monitor/" class="tnb_sql">현황모니터</a></li>
<li class="tnb_li"><a href="<?php //echo G5_USER_ADMIN_TEST_URL ?>" class="tnb_sql">TEST</a></li>
<li class="tnb_li"><a href="<?php //echo G5_USER_ADMIN_URL ?>/?device=mobile" class="tnb_mobile">Mobile</a></li-->
<li class="tnb_li"><a href="<?php echo G5_USER_CRON_URL ?>/icmms_forge2_sync.php" class="tnb_sql">디비측정싱크</a></li>
<li class="tnb_li"><a href="<?php echo G5_USER_CRON_URL ?>/icmms_forge2_downtime_sync.php" class="tnb_sql">디비비가동싱크</a></li>
<li class="tnb_li"><a href="<?php echo G5_USER_CRON_URL ?>/icmms_forge2_error_sync.php" class="tnb_sql">디비에러싱크</a></li>
<li class="tnb_li"><a href="<?php echo G5_USER_CRON_URL ?>/icmms_forge2_errorsum_sync.php" class="tnb_sql">디비에러합산싱크</a></li>
<li class="tnb_li"><a href="<?php echo G5_DEVICE_URL ?>" class="tnb_sql">APIs</a></li>
<li class="tnb_li"><a href="<?php echo G5_USER_URL ?>/worker_plan.php" class="tnb_sql">현장생산일정</a></li>
<li class="tnb_li"><a href="<?php echo G5_USER_ADMIN_SQL_URL ?>" class="tnb_sql">데이터업로드</a></li>