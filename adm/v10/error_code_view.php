<?php
$sub_menu = "919100";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'code';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_view/","",$g5['file_name']); // _form을 제외한 파일명
$qstr .= '&ser_cod_group='.$ser_cod_group.'&ser_cod_type='.$ser_cod_type; // 추가로 확장해서 넘겨야 할 변수들

${$pre} = get_table_meta($table_name, $pre.'_idx', ${$pre."_idx"});
if (!${$pre}[$pre.'_idx'])
    alert('존재하지 않는 자료입니다.');
$com = get_table_meta('company','com_idx',${$pre}['com_idx']);
$mms = get_table_meta('mms','mms_idx',${$pre}['mms_idx']);
// print_r3($cod);


// towhom_info variable
$reports = json_decode($cod['cod_reports'], true);
if(is_array($reports)) {
    foreach($reports as $k1 => $v1) {
        // echo $k1.'<br>';
        // print_r2($v1);
        for($i=0;$i<sizeof($v1);$i++) {
            $towhom_li[$i][$k1] = $v1[$i];
        }
    }
}
// print_r2($towhom_li);


// 라디오&체크박스 선택상태 자동 설정 (필드명 배열 선언!)
$check_array=array('mb_field');
for ($i=0;$i<sizeof($check_array);$i++) {
	${$check_array[$i].'_'.${$pre}[$check_array[$i]]} = ' checked';
}

$g5['title'] = '알람/예지 보기';
//include_once('./_top_menu_data.php');
include_once ('./_head.php');
//echo $g5['container_sub_title'];
?>
<style>
.cod_setting ul {padding:10px 0;color:#818181;}
</style>

<div class="cod_title">
    <?=$mms['mms_name']?> <?=$cod['cod_name']?>
    <span class="cod_sub_title"><?=$cod['cod_reg_dt']?></span>
</div>
<div class="cod_setting">
    <div class="cod_code" style="display:<?=(!$cod['cod_code'])?'none':''?>"><b>코드</b><?=$cod['cod_code']?></div>
    <div class="cod_group"><b>코드그룹</b><?=$g5['set_cod_group_value'][$cod['cod_group']]?></div>
    <div class="cod_type"><b>코드타입</b><?=$g5['set_cod_type'][$cod['cod_type']]?></div>
        <?php
        if($cod['cod_type']=='r')
            $cod['cod_text'] = '기록만 수행 (알림 없음)';
        else if($cod['cod_type']=='a')
            $cod['cod_text'] = '발생 시 알림';
        else if($cod['cod_type']=='p')
            $cod['cod_text'] = $g5['set_cod_interval_value'][$cod['cod_interval']].' '.$cod['cod_count'].'회 발생 시 알림';
        else if($cod['cod_type']=='p2')
            $cod['cod_text'] = '발생 시 즉시 알림';
        ?>
    <div class="cod_text"><b>설정</b><?=$cod['cod_text']?></div>
    <ul>
        <?php
        for($i=0;$i<sizeof($towhom_li);$i++) {
            echo '<li>
                    <span class="r_name">'.$towhom_li[$i]['r_name'].'</span>
                    <span class="r_role">'.$towhom_li[$i]['r_role'].'</span>
                    <span class="r_hp">'.$towhom_li[$i]['r_hp'].'</span>
                    <span class="r_email">'.$towhom_li[$i]['r_email'].'</span>
                </li>
            ';
        }
        ?>
    </ul>

</div>
<div class="cod_content">
    <?=conv_content($cod['cod_memo'],2)?>
</div>
<div class="cod_buttons">
    <button class="btn btn_01" style="display:<?=(!$member['mb_manager_yn']||$cod['cod_type']=='r')?'none':''?>;" onClick="javascript:self.location='./error_code_test.php?cod_idx=<?=$cod['cod_idx']?>'"><i class="fa fa-envelope-o"></i> 테스트</button>
    <button class="btn btn_02" onClick="javascript:self.location='./<?=$fname?>_list.php?<?php echo $qstr ?>'"><i class="fa fa-list"></i> 목록</button>
</div>


<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
</div>

<script>
$(function() {

});
</script>

<?php
include_once ('./_tail.php');
?>
