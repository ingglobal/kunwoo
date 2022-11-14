<?php
$sub_menu = "915125";
include_once('./_common.php');
auth_check_menu($auth, $sub_menu, "w");

$bct_id = isset($_GET['bct_id']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['bct_id']) : '';
$bct = array(
'bct_name'=>'',
'bct_order'=>'',
);

$sql_common = " FROM {$g5['bom_category_table']} ";

if ($w == "")
{
    $len = strlen($bct_id);
    if ($len == 10)
        alert("분류를 더 이상 추가할 수 없습니다.\\n\\n5단계 분류까지만 가능합니다.");

    $len2 = $len + 1;

    $sql = "SELECT MAX(SUBSTRING(bct_id,$len2,2)) as max_subid
            FROM {$g5['bom_category_table']}
            WHERE SUBSTRING(bct_id,1,$len) = '$bct_id' AND com_idx = '".$_SESSION['ss_com_idx']."'
    ";
    $row = sql_fetch($sql);

    $subid = base_convert($row['max_subid'], 36, 10);
    $subid += 36;
    if ($subid >= 36 * 36)
    {
        //alert("분류를 더 이상 추가할 수 없습니다.");
        // 빈상태로
        $subid = "  ";
    }
    $subid = base_convert($subid, 10, 36);
    $subid = substr("00" . $subid, -2);
    $subid = $bct_id . $subid;

    $sublen = strlen($subid);

    if ($bct_id) // 2단계이상 분류
    {
        $sql = " select * from {$g5['bom_category_table']} where bct_id = '$bct_id' ";
        $bct = sql_fetch($sql);
        $html_title = $bct['bct_name'] . " 하위분류추가";
        $bct['bct_name'] = "";
    }
    else // 1단계 분류
    {
        $html_title = "1단계분류추가";
    }
}
else if ($w == "u")
{
    $sql = " select * from {$g5['bom_category_table']} where bct_id = '$bct_id' ";
    $bct = sql_fetch($sql);
    if (! (isset($bct['bct_id']) && $bct['bct_id']))
        alert("자료가 없습니다.");

    $html_title = $bct['bct_name'] . " 수정";
    $bct['bct_name'] = get_text($bct['bct_name']);

    //관련파일 추출
    $flesql = " SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'bom_category'
        AND fle_type IN ('file1','file2','file3','file4','file5','file6')
        AND fle_db_id = '{$bct_id}' ORDER BY fle_reg_dt,fle_idx ";
    $fle_rs = sql_query($flesql,1);

    $row['cat_file1'] = array();//1번째 파일그룹
    $row['cat_file1_idxs'] = array();//(fle_idx) 목록이 담긴 배열
    $row['cat_file2'] = array();//2번째 파일그룹
    $row['cat_file2_idxs'] = array();//(fle_idx) 목록이 담긴 배열
    $row['cat_file3'] = array();//3번째 파일그룹
    $row['cat_file3_idxs'] = array();//(fle_idx) 목록이 담긴 배열
    $row['cat_file4'] = array();//4번째 파일그룹
    $row['cat_file4_idxs'] = array();//(fle_idx) 목록이 담긴 배열
    $row['cat_file5'] = array();//5번째 파일그룹
    $row['cat_file5_idxs'] = array();//(fle_idx) 목록이 담긴 배열
    $row['cat_file6'] = array();//6번째 파일그룹
    $row['cat_file6_idxs'] = array();//(fle_idx) 목록이 담긴 배열

    for($i=0;$flerow=sql_fetch_array($fle_rs);$i++){
        $file_del = (is_file(G5_PATH.$flerow['fle_path'].'/'.$flerow['fle_name'])) ? $flerow['fle_name_orig'].'&nbsp;&nbsp;<a href="'.G5_USER_ADMIN_URL.'/lib/download.php?file_fullpath='.urlencode(G5_PATH.$flerow['fle_path'].'/'.$flerow['fle_name']).'&file_name_orig='.$flerow['fle_name_orig'].'" file_path="'.$flerow['fle_path'].'">[파일다운로드]</a>&nbsp;&nbsp;'.$flerow['fle_reg_dt'].'&nbsp;&nbsp;<label for="del_'.$flerow['fle_idx'].'" style="position:relative;top:-3px;cursor:pointer;"><input type="checkbox" name="'.$flerow['fle_type'].'_del['.$flerow['fle_idx'].']" id="del_'.$flerow['fle_idx'].'" value="1"> 삭제</label><br><img src="'.G5_URL.$flerow['fle_path'].'/'.$flerow['fle_name'].'" style="width:200px;height:auto;">':''.PHP_EOL;
		@array_push($row['cat_'.$flerow['fle_type']],array('file'=>$file_del));
        @array_push($row['cat_'.$flerow['fle_type'].'_idxs'],$flerow['fle_idx']);
    }
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

$g5['title'] = $html_title;
include_once('./_head.php');
?>
<script src="<?=G5_USER_ADMIN_JS_URL?>/multifile/jquery.MultiFile.min.js" type="text/javascript" language="javascript"></script>

<style>
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

<form name="form01" action="./bom_category_form_update.php" onsubmit="return form01_check(this);" method="post" enctype="multipart/form-data" autocomplete="off">

<input type="hidden" name="codedup"  value="<?php echo $default['de_code_dup_use']; ?>">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<section id="anc_scatefrm_basic">
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>분류 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bct_id">분류코드</label></th>
            <td>
            <?php if ($w == "") { ?>
                <?php echo help("자동으로 보여지는 분류코드를 사용하시길 권해드리지만 직접 입력한 값으로도 사용할 수 있습니다.\n분류코드는 나중에 수정이 되지 않으므로 신중하게 결정하여 사용하십시오.\n\n분류코드는 2자리씩 10자리를 사용하여 5단계를 표현할 수 있습니다.\n0~z까지 입력이 가능하며 한 분류당 최대 1296가지를 표현할 수 있습니다.\n그러므로 총 3656158440062976가지의 분류를 사용할 수 있습니다."); ?>
                <input type="text" name="bct_id" value="<?php echo $subid; ?>" id="bct_id" required class="required frm_input" size="<?php echo $sublen; ?>" maxlength="<?php echo $sublen; ?>">
                <!-- <?php if ($default['de_code_dup_use']) { ?><a href="javascript:;" onclick="codedupcheck(document.getElementById('bct_id').value)">코드 중복검사</a><?php } ?> -->
            <?php } else { ?>
                <input type="hidden" name="bct_id" value="<?php echo $bct['bct_id']; ?>">
                <span class="frm_bct_id"><?php echo $bct['bct_id']; ?></span>
                <a href="./bom_category_form.php?bct_id=<?php echo $bct_id; ?>&amp;<?php echo $qstr; ?>" class="btn_frmline">하위분류 추가</a>
                <a href="./bom_list.php?sca=<?php echo $bct['bct_id']; ?>" class="btn_frmline">BOM리스트</a>
            <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bct_name">항목명</label></th>
            <td><input type="text" name="bct_name" value="<?php echo $bct['bct_name']; ?>" id="bct_name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="bct_desc">항목설명</label></th>
            <td><input type="text" name="bct_desc" value="<?php echo $bct['bct_desc']; ?>" id="bct_desc" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="bct_order">출력순서</label></th>
            <td>
                <?php echo help("숫자가 작을 수록 상위에 출력됩니다. 음수 입력도 가능하며 입력 가능 범위는 -2147483648 부터 2147483647 까지입니다.\n<b>입력하지 않으면 자동으로 출력됩니다.</b>"); ?>
                <input type="text" name="bct_order" value="<?php echo $bct['bct_order']; ?>" id="bct_order" class="frm_input" size="12">
            </td>
        </tr>
        <?php if ($w == "u") { ?>
        <tr style="display:none;">
            <th scope="row">하위분류</th>
            <td>
                <?php echo help("이 분류의 코드가 10 이라면 10 으로 시작하는 하위분류의 설정값을 이 분류와 동일하게 설정합니다.\n<strong>이 작업은 실행 후 복구할 수 없습니다.</strong>"); ?>
                <label for="sub_category">이 분류의 하위분류 설정을, 이 분류와 동일하게 일괄수정</label>
                <input type="checkbox" name="sub_category" value="1" id="sub_category" onclick="if (this.checked) if (confirm('이 분류에 속한 하위 분류의 속성을 똑같이 변경합니다.\n\n이 작업은 되돌릴 방법이 없습니다.\n\n그래도 변경하시겠습니까?')) return ; this.checked = false;">
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row"><label for="multi_file1">모니터 이미지파일#1</label></th>
            <td>
                <?php echo help("모니터 이미지파일#1을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file1" name="cat_f1[]" multiple class="cat_file">
                <?php
                if(@count($row['cat_file1'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['cat_file1']);$i++) {
                        echo "<li>[".($i+1).']'.$row['cat_file1'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file2">모니터 이미지파일#2</label></th>
            <td>
                <?php echo help("모니터 이미지파일#2을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file2" name="cat_f2[]" multiple class="cat_file">
                <?php
                if(@count($row['cat_file2'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['cat_file2']);$i++) {
                        echo "<li>[".($i+1).']'.$row['cat_file2'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file3">모니터 이미지파일#3</label></th>
            <td>
                <?php echo help("모니터 이미지파일#3을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file3" name="cat_f3[]" multiple class="cat_file">
                <?php
                if(@count($row['cat_file3'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['cat_file3']);$i++) {
                        echo "<li>[".($i+1).']'.$row['cat_file3'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file4">모니터 이미지파일#4</label></th>
            <td>
                <?php echo help("모니터 이미지파일#4을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file4" name="cat_f4[]" multiple class="cat_file">
                <?php
                if(@count($row['cat_file4'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['cat_file4']);$i++) {
                        echo "<li>[".($i+1).']'.$row['cat_file4'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file5">모니터 이미지파일#5</label></th>
            <td>
                <?php echo help("모니터 이미지파일#5을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file5" name="cat_f5[]" multiple class="cat_file">
                <?php
                if(@count($row['cat_file5'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['cat_file5']);$i++) {
                        echo "<li>[".($i+1).']'.$row['cat_file5'][$i]['file']."</li>".PHP_EOL;
                    }
                    echo '</ul>'.PHP_EOL;
                }
                ?>
            </td>	
        </tr>
        <tr>
            <th scope="row"><label for="multi_file6">모니터 이미지파일#6</label></th>
            <td>
                <?php echo help("모니터 이미지파일#6을 등록하고 관리해 주시면 됩니다."); ?>
                <input type="file" id="multi_file6" name="cat_f6[]" multiple class="cat_file">
                <?php
                if(@count($row['cat_file6'])){
                    echo '<ul>'.PHP_EOL;
                    for($i=0;$i<count($row['cat_file6']);$i++) {
                        echo "<li>[".($i+1).']'.$row['cat_file6'][$i]['file']."</li>".PHP_EOL;
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

<div class="btn_fixed_top">
    <a href="./bom_category_list.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>
</form>

<script>
var cat_file_cnt = $('.cat_file').length;
for(var i=1; i<=cat_file_cnt; i++){

    $('#multi_file'+i).MultiFile({
        max: <?=$g5['setting']['set_monitor_cnt']?>,
        accept: 'gif|jpg|png'
    });
}

function form01_check(f)
{
    if (f.w.value == "") {
        var error = "";
        $.ajax({
            url: "./ajax/ajax.bct_id.php",
            type: "POST",
            data: {
                "bct_id": f.bct_id.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                error = data.error;
            }
        });
        if (error) {
            alert(error);
            return false;
        }
    }
    return true;
}
</script>

<?php
include_once ('./_tail.php');
