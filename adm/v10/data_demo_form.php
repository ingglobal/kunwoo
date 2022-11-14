<?php
$sub_menu = "960100";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$g5['title'] = '데모 데이터 생성';
include_once('./_top_menu_data.php');
include_once ('./_head.php');
echo $g5['container_sub_title'];

// How many seconds far from current month-01(first day)
$date20 = date("Y-m-01 00:00:00", G5_SERVER_TIME);
$date20 = date("Y-m-d 00:00:00", G5_SERVER_TIME);
$time20 = strtotime($date20);   // current month start timestamp
$time21 = G5_SERVER_TIME;       // current timestamp
$time2_diff = $time21-$time20;
// echo date("Y-m-d H:i:s",$time21).'<br>';

// Compare to 2020-07 data
$time10 = strtotime("2020-07-01 00:00:00");
$time11 = $time10+$time2_diff;
// echo date("Y-m-d H:i:s",$time11).'<br>';


?>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>2020-07월 샘플 데이터를 기반으로 데모 데이터를 생성합니다.</p>
    <p>[확인] 버튼을 클릭하세요.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<colgroup>
		<col class="grid_4" style="width:15%;">
		<col style="width:85%;">
	</colgroup>
	<tbody>
	<tr>
		<th scope="row">Error Data</th>
		<td>
            <?php
            // count from 2020-07-01 ~ to time as much as the amount which differ from the current month first time and current time.
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_error_table']}
                    WHERE dta_dt >= ".$time10." AND dta_dt <= ".$time11."
            ";
            // echo $sql.'<br>';
            $row1 = sql_fetch($sql,1);
            $cnt1 = $row1['dta_count'];
            // print_r2($row1);

            // count from current first time to current time 
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_error_table']}
                    WHERE dta_dt >= ".$time20." AND dta_dt <= ".$time21."
            ";
            $row1 = sql_fetch($sql,1);
            $cnt2 = $row1['dta_count'];

            echo ($cnt1 - $cnt2 > 0) ? number_format($cnt1 - $cnt2) : 0;
            ?>
		</td>
	</tr>
	<tr>
		<th scope="row">Measure Data</th>
		<td>
            <?php
            // count from 2020-07-01 ~ to time as much as the amount which differ from the current month first time and current time.
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_measure_table']}
                    WHERE dta_dt >= ".$time10." AND dta_dt <= ".$time11."
            ";
            $row1 = sql_fetch($sql,1);
            $cnt1 = $row1['dta_count'];

            // count from current first time to current time 
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_measure_table']}
                    WHERE dta_dt >= ".$time20." AND dta_dt <= ".$time21."
            ";
            $row1 = sql_fetch($sql,1);
            $cnt2 = $row1['dta_count'];

            echo ($cnt1 - $cnt2 > 0) ? number_format($cnt1 - $cnt2) : 0;
            ?>
		</td>
	</tr>
	<tr>
		<th scope="row">Output Data</th>
		<td>
            <?php
            // count from 2020-07-01 ~ to time as much as the amount which differ from the current month first time and current time.
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_output_table']}
                    WHERE dta_dt >= ".$time10." AND dta_dt <= ".$time11."
            ";
            $row1 = sql_fetch($sql,1);
            $cnt1 = $row1['dta_count'];

            // count from current first time to current time 
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_output_table']}
                    WHERE dta_dt >= ".$time20." AND dta_dt <= ".$time21."
            ";
            $row1 = sql_fetch($sql,1);
            $cnt2 = $row1['dta_count'];

            echo ($cnt1 - $cnt2 > 0) ? number_format($cnt1 - $cnt2) : 0;
            ?>
		</td>
	</tr>
	<tr>
		<th scope="row">Run Data</th>
		<td>
            <?php
            // count from 2020-07-01 ~ to time as much as the amount which differ from the current month first time and current time.
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_run_table']}
                    WHERE dta_dt >= ".$time10." AND dta_dt <= ".$time11."
            ";
            $row1 = sql_fetch($sql,1);
            $cnt1 = $row1['dta_count'];

            // count from current first time to current time 
            $sql = "SELECT COUNT(dta_idx) AS dta_count FROM {$g5['data_run_table']}
                    WHERE dta_dt >= ".$time20." AND dta_dt <= ".$time21."
            ";
            $row1 = sql_fetch($sql,1);
            $cnt2 = $row1['dta_count'];

            echo ($cnt1 - $cnt2 > 0) ? number_format($cnt1 - $cnt2) : 0;
            ?>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="javascript:history.go(-1)" class="btn btn_02">뒤로</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>
