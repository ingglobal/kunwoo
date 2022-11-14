<style>
.td_mms {position:relative;}
.div_category_mod {margin-bottom:3px;}
.div_category_mod:after {display:block;visibility:hidden;clear:both;content:"";}
.btn_category {float:left;border:solid 1px #aaa;padding:1px 4px;background:#fff;font-size:0.9em;}
.btn_mod {float:right;border:solid 1px #aaa;padding:1px 4px;background:#eee;font-size:0.9em;}
.td_mms_name {font-size:1.2em;margin-bottom:3px;}
.td_mms_price {color:#1164a3;}
.span_mms_idx {color:#818181;margin-left:10px;font-size:0.8em;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="mms_name"<?php echo get_selected($_GET['sfl'], "mms_name"); ?>>설비명</option>
    <option value="mms_model"<?php echo get_selected($_GET['sfl'], "mms_model"); ?>>모델명</option>
    <option value="mms_memo"<?php echo get_selected($_GET['sfl'], "mms_hp"); ?>>메모</option>
</select>
<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
<input type="submit" class="btn_submit" value="검색">

</form>

<div class="local_desc01 local_desc" style="display:none;">
    <p>새로운 고객을 등록</p>
</div>


<form name="form01" id="form01" action="./mms2_list_update.php" onsubmit="return form01_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="w" value="">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <tbody>
    <?php
    $fle_width = 120;
    $fle_height = 100;
    for ($i=0; $row=sql_fetch_array($result); $i++) {

        // mms_img 타입중에서 대표 이미지 한개만
        $sql = "SELECT * FROM {$g5['file_table']}
                WHERE fle_db_table = 'mms' AND fle_db_id = '".$row['mms_idx']."'
                    AND fle_type = 'mms_img'
                    AND fle_sort = 0
        ";
//        echo $sql.'<br>';
        $rs1 = sql_query($sql,1);
        for($j=0;$row1=sql_fetch_array($rs1);$j++) {
//            print_r2($row1);
            if( $row1['fle_name'] && is_file(G5_PATH.$row1['fle_path'].'/'.$row1['fle_name']) ) {
                $row['img'] = $row[$row1['fle_type']][$row1['fle_sort']]; // 변수명 좀 짧게
                $row['img']['thumbnail'] = thumbnail($row1['fle_name'], 
                                G5_PATH.$row1['fle_path'], G5_PATH.$row1['fle_path'],
                                $fle_width, $fle_width, 
                                false, true, 'center', true, $um_value='85/3.4/15');	// is_create, is_crop, crop_mode
            }
            else {
                $row[$row1['fle_type']][$row1['fle_sort']]['thumbnail'] = 'default.png';
                $row1['fle_path'] = '/data/mms_img';	// 디폴트 경로 결정해야 합니다.
            }
            $row['img']['thumbnail_img'] = '<img src="'.G5_URL.$row1['fle_path'].'/'.$row['img']['thumbnail'].'"
                                                width="'.$fle_width.'" height="'.$fle_height.'">';
        }
        
        // 관리 버튼
        $s_mod = '<a href="./mms2_form.php?'.$qstr.'&amp;w=u&amp;mms_idx='.$row['mms_idx'].'&amp;ser_mms_type='.$ser_mms_type.'&amp;ser_trm_idx_salesarea='.$ser_trm_idx_salesarea.'" class="btn_mod">수정 <i class="fa fa-edit"></i></a>';
        
        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['mms_idx'] ?>">
        <td class="td_image" style="width:130px;">
            <?=$row['img']['thumbnail_img']?>
        </td>
        <td class="td_mms">
            <div class="div_category_mod">
                <span class="btn_category"><?=$g5['mms_type_name'][$row['trm_idx_category']]?></span>
                <span class="span_mms_idx">mms_idx <?=$row['mms_idx']?></span>
                <?=$s_mod?>
            </div>
            <div class="td_mms_name"><?php echo get_text($row['mms_name']); ?></div>
            <div class="td_mms_model"><?php echo get_text($row['mms_model']); ?></div>
            <div class="td_mms_price"><?php echo number_format($row['mms_price']); ?></div>
        </td>
    </tr>

        <?php
    }
    if ($i == 0)
        echo "<tr><td colspan=\"".$colspan."\" class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./mms2_form.php" id="btn_add" class="btn_01 btn">추가하기</a>
</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script>
    // 마우스 hover 설정
    $(".tbl_head01 tbody tr").on({
        mouseenter: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#e8e8e8');
            
        },
        mouseleave: function () {
            $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
        }    
    });
    

function fmemberlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once ('./_tail.php');
?>
