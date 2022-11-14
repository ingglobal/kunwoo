<?php
$sub_menu = "915122";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

if(!$config['cf_faq_skin']) $config['cf_faq_skin'] = "basic";
if(!$config['cf_mobile_faq_skin']) $config['cf_mobile_faq_skin'] = "basic";

$g5['title'] = '제품(BOM) 환경설정';
//include_once('./_top_menu_setting.php');
include_once('./_head.php');
echo $g5['container_sub_title'];


//관련파일 추출
$flesql = " SELECT * FROM {$g5['file_table']}
WHERE fle_db_table = 'bom_common_setting'
AND fle_type IN ('file1','file2','file3','file4','file5','file6')
AND fle_db_id = '{$bct_id}' ORDER BY fle_reg_dt,fle_idx DESC ";
$fle_rs = sql_query($flesql,1);

$row['bom_file1'] = array();//1번째 파일그룹
$row['bom_file1_idxs'] = array();//(fle_idx) 목록이 담긴 배열
$row['bom_file2'] = array();//2번째 파일그룹
$row['bom_file2_idxs'] = array();//(fle_idx) 목록이 담긴 배열
$row['bom_file3'] = array();//3번째 파일그룹
$row['bom_file3_idxs'] = array();//(fle_idx) 목록이 담긴 배열
$row['bom_file4'] = array();//4번째 파일그룹
$row['bom_file4_idxs'] = array();//(fle_idx) 목록이 담긴 배열
$row['bom_file5'] = array();//5번째 파일그룹
$row['bom_file5_idxs'] = array();//(fle_idx) 목록이 담긴 배열
$row['bom_file6'] = array();//6번째 파일그룹
$row['bom_file6_idxs'] = array();//(fle_idx) 목록이 담긴 배열

for($i=0;$flerow=sql_fetch_array($fle_rs);$i++){
    $file_del = (is_file(G5_PATH.$flerow['fle_path'].'/'.$flerow['fle_name'])) ? $flerow['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$flerow['fle_path'].'/'.$flerow['fle_name']).'&file_name_orig='.$flerow['fle_name_orig'].'" file_path="'.$flerow['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$flerow['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$flerow['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$flerow['fle_type'].'_del['.$flerow['fle_idx'].']" id="del_'.$flerow['fle_idx'].'" value="1"> 삭제</label><br><img src="'.G5_URL.$flerow['fle_path'].'/'.$flerow['fle_name'].'" style="width:200px;height:auto;">':''.PHP_EOL;
    @array_push($row['bom_'.$flerow['fle_type']],array('file'=>$file_del));
    @array_push($row['bom_'.$flerow['fle_type'].'_idxs'],$flerow['fle_idx']);
}


$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_cf_default">BOM 공통 모니터 이미지설정</a></li>
</ul>';

?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>
<style>
.tbl_wrap table{}
.tbl_wrap table tbody{}
.tbl_wrap table tbody th{width:200px !important;}
/*멀티파일관련*/
input[type="file"]{position:relative;width:250px;height:80px;border-radius:10px;overflow:hidden;cursor:pointer;border:1px solid #333;}
input[type="file"]::before{display:block;content:'';position:absolute;left:0;top:0;width:100%;height:100%;background:#000;opacity:1;z-index:3;}
input[type="file"]::after{display:block;content:'파일선택\A(드래그앤드롭 가능)';position:absolute;z-index:4;left:50%;top:50%;transform:translate(-50%,-50%);text-align:center;}
.MultiFile-wrap ~ ul{margin-top:10px;}
.MultiFile-wrap ~ ul > li{margin-top:10px;}
.MultiFile-wrap .MultiFile-list{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label{position:relative;padding-left:25px;margin-top:10px;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove{position:absolute;top:0;left:0;font-size:0;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label .MultiFile-remove::after{content:'×';display:block;position:absolute;left:0;top:0;width:20px;height:20px;border:1px solid #ccc;border-radius:50%;font-size:14px;line-height:20px;text-align:center;}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span{}
.MultiFile-wrap .MultiFile-list > .MultiFile-label > span span.MultiFile-label{display:inline-block;font-size:14px;border:1px solid #444;background:#333;padding:2px 5px;border-radius:3px;line-height:1.2em;margin-top:5px;}
</style>
<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="token" value="" id="token">

<section id="anc_cf_default">
	<h2 class="h2_frm">BOM 기본 모니터 이미지설정</h2>
	<?php echo $pg_anchor ?>
	
	<div class="tbl_frm01 tbl_wrap">
		<table>
		<caption>BOM 기본 모니터 이미지설정</caption>
		<colgroup>
			<col class="">
			<col>
			<col class="">
			<col>
		</colgroup>
		<tbody>
		<tr>
            <th scope="row"><label for="multi_file1">모니터 이미지파일#1(공통)</label></th>
            <td colspan="3">
                <?php echo help("모니터 이미지파일#1을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file1" name="bom_f1[]" multiple class="bom_file">
                <?php
                if(@count($row['bom_file1'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['bom_file1']);$i++) {
                        echo "<li>[".($i+1).']'.$row['bom_file1'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file2">모니터 이미지파일#2(공통)</label></th>
            <td colspan="3">
                <?php echo help("모니터 이미지파일#2을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file2" name="bom_f2[]" multiple class="bom_file">
                <?php
                if(@count($row['bom_file2'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['bom_file2']);$i++) {
                        echo "<li>[".($i+1).']'.$row['bom_file2'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file3">모니터 이미지파일#3(공통)</label></th>
            <td colspan="3">
                <?php echo help("모니터 이미지파일#3을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file3" name="bom_f3[]" multiple class="bom_file">
                <?php
                if(@count($row['bom_file3'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['bom_file3']);$i++) {
                        echo "<li>[".($i+1).']'.$row['bom_file3'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file4">모니터 이미지파일#4(공통)</label></th>
            <td colspan="3">
                <?php echo help("모니터 이미지파일#4을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file4" name="bom_f4[]" multiple class="bom_file">
                <?php
                if(@count($row['bom_file4'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['bom_file4']);$i++) {
                        echo "<li>[".($i+1).']'.$row['bom_file4'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file5">모니터 이미지파일#5(공통)</label></th>
            <td colspan="3">
                <?php echo help("모니터 이미지파일#5을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file5" name="bom_f5[]" multiple class="bom_file">
                <?php
                if(@count($row['bom_file5'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['bom_file5']);$i++) {
                        echo "<li>[".($i+1).']'.$row['bom_file5'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file6">모니터 이미지파일#6(공통)</label></th>
            <td colspan="3">
                <?php echo help("모니터 이미지파일#6을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file6" name="bom_f6[]" multiple class="bom_file">
                <?php
                if(@count($row['bom_file6'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['bom_file6']);$i++) {
                        echo "<li>[".($i+1).']'.$row['bom_file6'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
		</tbody>
		</table>
	</div>
</section>



<div class="btn_fixed_top btn_confirm">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>

<script>
$(function(){
    var bom_file_cnt = $('.bom_file').length;
    for(var i=1; i<=bom_file_cnt; i++){

        $('#multi_file'+i).MultiFile({
            max: 1,
            accept: 'gif|jpg|png'
        });
    }
});

function fconfigform_submit(f) {

    <?php //echo get_editor_js("set_expire_email_content"); ?>
    <?php //echo chk_editor_js("set_expire_email_content"); ?>
    <?php //echo get_editor_js("set_maintain_plan_content"); ?>
    <?php //echo chk_editor_js("set_maintain_plan_content"); ?>
    <?php //echo get_editor_js("set_error_content"); ?>
    <?php //echo chk_editor_js("set_error_content"); ?>

    f.action = "./bom_common_setting_form_update.php";
    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
