<?php
$sub_menu = "910120";
include_once('./_common.php');

// show com_name for super or manager level.
if($_SESSION['ss_com_idx']&&$member['mb_level']>=8) {
    $com = get_table_meta('company','com_idx',$_SESSION['ss_com_idx']);
    // print_r3($com);
    $com_name = $com['com_name'] ? ' ('.$com['com_name'].')' : '';
}

$g5['title'] = '대시보드설정'.$com_name;
//include_once('./_top_menu_default.php');
include_once('./_head.php');
//echo $g5['container_sub_title'];

// echo $_SESSION['ss_com_idx'].'<br>';  // 디폴트 업체 (user.08.hook.defaut.php 설정)
// echo $member['mms_list'];     // 설비리스트
// echo $member['mms_graph'].'<br>';    // 그래프 리스트

$where = array();
$where[] = " mb_id = '".$member['mb_id']."' AND mbd_type = 'list' ";

// 그룹명 추출 (그룹명, 업체명은 기본 조건 하에서 추출해야 함)
$sql1 = "SELECT mbd_idx, mbd.mms_idx, mms_name, mmg.mmg_idx AS mmg_idx, mmg_name
        FROM {$g5['member_dash_table']} AS mbd
            LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = mbd.mms_idx
            LEFT JOIN {$g5['mms_group_table']} AS mmg ON mmg.mmg_idx = mms.mmg_idx
        WHERE {$where[0]}
            AND mms.com_idx = '".$_SESSION['ss_com_idx']."'
        GROUP BY mmg.mmg_idx
        ORDER BY mmg_name
";
// echo $sql1;
$rs1 = sql_query($sql1,1);
for ($i=0; $row=sql_fetch_array($rs1); $i++) {
    $hash_group .= '<a href="'.$_SERVER['SCRIPT_NAME'].'?mmg_idx='.$row['mmg_idx'].'">#'.$row['mmg_name'].'</a>'.PHP_EOL;
}

// 업체명 추출 (1개 업체명만 있으면 노출 안 함)
$sql1 = "SELECT mbd_idx, mbd.mms_idx, mms_name, com.com_idx AS com_idx, com_name
        FROM {$g5['member_dash_table']} AS mbd
            LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = mbd.mms_idx
            LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = mms.com_idx
        WHERE {$where[0]}
            AND mms.com_idx = '".$_SESSION['ss_com_idx']."'
        GROUP BY com.com_idx
        ORDER BY com_name
";
// echo $sql1;
$rs1 = sql_query($sql1,1);
for ($i=0; $row=sql_fetch_array($rs1); $i++) {
    $hash_com .= '<a href="'.$_SERVER['SCRIPT_NAME'].'?com_idx='.$row['com_idx'].'">#'.$row['com_name'].'</a>'.PHP_EOL;
}
if($i==1)
    unset($hash_com);



// 그룹명, 업체명 검색
if ($mmg_idx)
    $where[] = " mmg_idx = '".$mmg_idx."' ";

// 업체검색조건
$where[] = " mms.com_idx = '".$_SESSION['ss_com_idx']."' ";

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

// orderby가 없으면 디폴트 mbd_value
$sql_order = $_REQUEST['orderby'] ?: "mbd_value";

$sql = "SELECT *
        FROM {$g5['member_dash_table']} AS mbd
            LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = mbd.mms_idx AND mms.com_idx = mbd.com_idx
        {$sql_search}
        ORDER BY {$sql_order}
";
// echo $sql;
$result = sql_query($sql,1);

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_URL.'/css/index.css">', 0);
?>
<style>
</style>

<div id="div_filter">
    <a href="<?=$_SERVER['SCRIPT_NAME']?>">#전체</a>
    <a href="<?=$_SERVER['SCRIPT_NAME']?>?orderby=<?=urlencode('mms_reg_dt DESC')?>" style="display:none;">#최신순</a>
    <a href="<?=$_SERVER['SCRIPT_NAME']?>?orderby=<?=urlencode('mms.mms_idx')?>" style="display:none;">#오래된순</a>
    <?=$hash_group?>
    <?=$hash_com?>
</div>

<form name="form01" id="form01"  method="post">
<input type="hidden" name="file_name" value="<?=$file_name?>">
<!-- iMMS List -->
<ul class="mms_wrapper">
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // $img_container_style = ($i==0) ? ' on':' off';
        // print_r2($row);
        $row['com'] = get_table_meta('company','com_idx',$row['com_idx']);
        $row['mmg'] = get_table_meta('mms_group','mmg_idx',$row['mmg_idx']);
        // print_r2($row['mmg']);
        // 기종 추출 (해당 설비 맨 마지막 1개)
        $sql1 = "SELECT mmi_no FROM {$g5['mms_item_table']}
                WHERE mms_idx = '".$row['mms_idx']."'
                    AND mmi_status NOT IN ('trash','delete')
                ";
        $row['item'] = sql_fetch($sql1,1);

        $row['show_hide'] = ($row['mbd_status']=='show') ? '숨김' : '보임';
        $row['show_hide_class'] = ($row['mbd_status']=='show') ? '' : 'mms_hide';
    ?>
    <li class="ui-state-default mms_container <?=$img_container_style?>">
        <input type="hidden" name="chk[]" value="<?php echo $i ?>">
        <input type="hidden" name="mms_idx[<?php echo $i ?>]" value="<?php echo $row['mms_idx'] ?>">
        <input type="hidden" name="mbd_status[<?php echo $i ?>]" value="<?php echo $row['mbd_status'] ?>">
        <table class="list_mms_table <?=$row['show_hide_class']?>">
        <tr class="tr_title">
            <td colspan="2" class="td_center" title="<?=$row['mms_name']?>"><?=cut_str($row['mms_name'],12,'..')?></td>
        </tr>
        <tr>
            <td>모델 <a href="javascript:" class="btn_mms_view"><i class="fa fa-external-link-square"></i></a></td>
            <td><?=cut_str($row['mms_model'],9,'..')?></td>
        </tr>
        <tr>
            <td>생산기종</td>
            <td><?=($row['item']['mmi_no'])?:'-'?></td>
        </tr>
        <tr>
            <td>생산통계</td>
            <td><?=$g5['set_mms_set_data_value'][$row['mms_set_output']]?></td>
        </tr>
        <tr>
            <td>그룹</td>
            <td><?=cut_str($row['mmg']['mmg_name'],9,'..')?></td>
        </tr>
        <tr>
            <td>업체명</td>
            <td><?=cut_str($row['com']['com_name'],9,'..')?></td>
        </tr>
        </table>
        <div class="div_control">
            <a href="javascript:" class="mms_show_hide"><i class="fa fa-th-large"></i> <span><?=$row['show_hide']?></span></a>
            <a href="javascript:" class="mms_delete"><i class="fa fa-trash"></i> 삭제</a>
        </div>
    </li>
    <?php
    }
    ?>
    <li class="mms_container plus">
        +
    </li>
</ul>    

<div class="btn_fixed_top">
    <a href="./dashboard_mms_group.php" com_idx="<?=$_SESSION['ss_com_idx']?>" class="btn btn_02 btn_mms_group"><i class="fa fa-gears"></i> 배치도</a>
    <a href="./dashboard_mms_add.php" com_idx="<?=$_SESSION['ss_com_idx']?>" class="btn btn_02 btn_mms_add"><i class="fa fa-plus"></i> 설비불러오기</a>
    <input type="submit" name="act_button" value="설정완료" class="btn_01 btn">
</div>
</form>

<script>
$(document).on('click','.mms_container.plus',function(e){
    alert('설비(iMMS)추가는 INGGlobal 담당자에게 문의주시기 바랍니다.');
});

$( ".mms_wrapper" ).sortable({
    items:"> li:not(.plus)",    // .plus 요소는 sorable 되지 않는다.
    handle: ".tr_title"
});    

// 마우스 오버
$(document).on({
    mouseenter: function () {
        //stuff to do on mouse enter
        // $(this).find('tr').not('.tr_title').addClass('tr_selected');
        $(this).find('.div_control').fadeIn(200);
    },
    mouseleave: function () {
        //stuff to do on mouse leave
        // $(this).find('tr').not('.tr_title').removeClass('tr_selected');
        $(this).find('.div_control').hide();
    }
}, ".mms_container:not(.plus)");

// 보임, 숨김 설정
$(document).on('click','.mms_show_hide',function(e){
    var this_li = $(this).closest('li.mms_container');
    if( this_li.find('input[name^=mbd_status]').val()=='show' ) {
        this_li.find('input[name^=mbd_status]').val('hide');
        this_li.find('table').addClass('mms_hide');
        this_li.find('span').text('보임');
    }
    else {
        this_li.find('input[name^=mbd_status]').val('show');
        this_li.find('table').removeClass('mms_hide');
        this_li.find('span').text('숨김');
    }
});

// 삭제 설정
$(document).on('click','.mms_delete',function(e){
    $(this).closest('li.mms_container').remove();
});


// mms_view button click
$(document).on('click','.btn_mms_view',function(e){
    e.preventDefault();
    var mms_idx = $(this).closest('li').find('input[name^=mms_idx]').val();
    var href = './mms_view.popup.php?mms_idx='+mms_idx;
    winMMSView = window.open(href+'?file_name=<?php echo $g5['file_name']?>',"winMMSView","left=100,top=100,width=520,height=600,scrollbars=1");
    winMMSView.focus();
});


// 설비추가, 배치도 button, window popup
$(document).on('click','.btn_mms_add, .btn_mms_group',function(e){
    e.preventDefault();
    var com_idx = $(this).attr('com_idx') || '';
    if(com_idx=='') {
        alert('소속 업체 정보가 존재하지 않습니다.');
    }    
    else {
        var href = $(this).attr('href');
        winAddChart = window.open(href+'?file_name=<?php echo $g5['file_name']?>&com_idx='+com_idx,"winAddChart","left=100,top=100,width=520,height=600,scrollbars=1");
        winAddChart.focus();
    }    
});

$("#form01").submit(function(e){
    e.preventDefault();
    this.action = './dashboard_setting_update.php';
    this.submit();
});
</script>



<div style="height:30px;border:solid 0px red;"></div>
<?php
include_once ('./_tail.php');
?>
