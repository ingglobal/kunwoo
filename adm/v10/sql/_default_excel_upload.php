<?php
include_once('./_head.sub.php');

?>
<style>
.update_select:after{display:block;visibility:hidden;clear:both;content:''}
.update_select li{float:left;margin-right:5px;}
label[for="target"] i{cursor:pointer;margin-left:10px;font-size:1.2em;}
</style>
<ul class="update_select">
	<li><a href="javascript:" page="add_companies" class="btn btn_01 target_btn">기존업체DB등록</a></li>
    <li><a href="javascript:" page="add_products" class="btn btn_01 target_btn">기존제품DB등록</a></li>
</ul>
<form name="form02" id="form02" action="" onsubmit="return form02_submit(this);" method="post" enctype="multipart/form-data">
    <table>
    <tbody>
    <tr>
        <td style="padding:15px 0;">
            <label for="target">
                <input type="text" name="target_file" id="target" readonly class="frm_input readonly" style="width:300px;">
                <i class="fa fa-times" aria-hidden="true"></i>
            </label>
        </td>
    </tr>
    <tr>
        <td style="line-height:130%;padding:10px 0;">
            <ol>
                <li>엑셀은 97-2003통합문서만 등록가능합니다. (*.xls파일로 저장)</li>
                <li>엑셀은 하단에 탭으로 여러개 있으면 등록 안 됩니다. (한개의 독립 문서이어야 합니다.)</li>
            </ol>
        </td>
    </tr>
    <tr>
        <td style="padding:15px 0;">
            <input type="file" name="file_excel" onfocus="this.blur()">
        </td>
    </tr>
    <tr>
        <td style="padding:15px 0;">
            <button type="submit" class="btn btn_01">확인</button>
        </td>
    </tr>
    </tbody>
    </table>
</form>
<script>
var frm = $('#form02');
var target_close = $('label[for="target"]').find('i');
$('.target_btn').on('click',function(){
    frm.attr('action','./'+$(this).attr('page')+'.php');
    $('#target').val($(this).text());
});

target_close.on('click',function(){
    $('#target').val('');
    frm.attr('action','');
});


function form02_submit(f) {
    if (!f.target_file.value) {
        alert('타겟파일을 선택해 주세요.');
        return false;
    }
    if (!f.file_excel.value) {
        alert('엑셀 파일(.xls)을 입력하세요.');
        return false;
    }
    else if (!f.file_excel.value.match(/\.xls$|\.xlsx$/i) && f.file_excel.value) {
        alert('엑셀 파일만 업로드 가능합니다.');
        return false;
    }

    return true;
}
</script>

<?php
include_once('./_tail.sub.php');
