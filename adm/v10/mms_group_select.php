<?php
// 호출페이지들
// /adm/v10/mms_form.php: 장비그룹찾기
// /adm/v10/mms_setting.php: 대시보드 > 설비설정 > 그룹찾기
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

if(!$com_idx)
    alert('업체코드가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);

$g5['title'] = $com['com_name'].' 설비그룹 선택';
include_once('./_head.sub.php');


//-- 카테고리 구조 추출 --//
$sql = "SELECT 
			mmg_idx
			, GROUP_CONCAT(name) AS mmg_name
			, mmg_type
			, GROUP_CONCAT(cast(depth as char)) AS depth
			, GROUP_CONCAT(up_names) AS up_names
			, leaf_node_yn
			, SUM(table_row_count) AS table_row_count
		FROM (	(
				SELECT mmg.mmg_idx
					, CONCAT( REPEAT('   ', COUNT(parent.mmg_idx) - 1), mmg.mmg_name) AS name
					, mmg.mmg_type
					, (COUNT(parent.mmg_idx) - 1) AS depth
					, GROUP_CONCAT(parent.mmg_name ORDER BY parent.mmg_left SEPARATOR '|') AS up_names
					, (CASE WHEN mmg.mmg_right - mmg.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
					, 0 AS table_row_count
					, mmg.mmg_left
				FROM {$g5['mms_group_table']} AS mmg,
				        {$g5['mms_group_table']} AS parent
				WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
					AND mmg.com_idx = '".$com_idx."'
					AND parent.com_idx = '".$com_idx."'
					AND mmg.mmg_status NOT IN ('trash','delete') AND parent.mmg_status NOT IN ('trash','delete')
                GROUP BY mmg.mmg_idx
				ORDER BY mmg.mmg_left
				)
			UNION ALL
				(
				SELECT parent.mmg_idx
					, NULL name
					, mmg.mmg_type
					, NULL depth
					, NULL up_names
					, (CASE WHEN parent.mmg_right - parent.mmg_left = 1 THEN 1 ELSE 0 END ) AS leaf_node_yn
					, SUM(mmg.mmg_count) AS table_row_count
					, parent.mmg_left
				FROM {$g5['mms_group_table']} AS mmg
						, {$g5['mms_group_table']} AS parent
				WHERE mmg.mmg_left BETWEEN parent.mmg_left AND parent.mmg_right
					AND mmg.com_idx = '".$com_idx."'
					AND parent.com_idx = '".$com_idx."'
					AND mmg.mmg_status NOT IN ('trash','delete') AND parent.mmg_status NOT IN ('trash','delete')
				GROUP BY parent.mmg_idx
				ORDER BY parent.mmg_left
				)
			) db_table
		GROUP BY mmg_idx
		ORDER BY mmg_left
";
//echo $sql;
$result = sql_query($sql,1);
?>
<style>
.btn_fixed_top {position:absolute;top: 12px;}
</style>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <div class="local_desc01 local_desc" style="display:no ne;">
        <p>설비(mms)는 맨 마지막 노드(체크항목 <i class="fa fa-check" style="color:red;"></i>)에 연결해 주셔야 합니다.</p>
    </div>

    <div class="tbl_head01 tbl_wrap new_win_con">
        <table>
        <caption>검색결과</caption>
        <thead>
        <tr>
            <th scope="col">그룹명</th>
            <th scope="col">타입</th>
            <th scope="col">설비수</th>
            <th scope="col">선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            //-- 들여쓰기
            $row['indent'] = ($row['depth']) ? $row['depth']*30:10;
            //-- 마지막노드(Leaf)
            $row['leaf_icon'] = ($row['leaf_node_yn']) ? ' <i class="fa fa-check" style="color:red;font-size:1.4em;"></i>' : '';
        ?>
        <tr>
            <td class="td_mmg_name td_left"  style="padding-left:<?=$row['indent']?>px;"><!-- 그룹명 -->
                <?php echo $row['mmg_name']; ?><?=$row['leaf_icon']?>
            </td>
            <td class="td_mmg_type"><?php echo $row['mmg_type']; ?></td>
            <td class="td_mmg_count"><?php echo number_format($row['mmg_count']); ?></td>
            <td class="td_mng td_mng_s" mmg_idx="<?php echo $row['mmg_idx']; ?>"
                                        mmg_name="<?php echo trim($row['mmg_name']); ?>"
                                        mmg_type="<?php echo $row['mmg_type']; ?>">
                <button type="button" class="btn btn_03 btn_select">선택</button>
            </td>
        </tr>
        <?php
        }
        if($i ==0)
            echo '<tr><td colspan="6" class="empty_table">검색된 자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    <?php
    echo $total_page;
    ?>
    <?php ;//echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr1.'&amp;page='); ?>

    <div class="win_btn ">
        <button type="button" onclick="window.close();" class="btn btn_close">창닫기</button>
    </div>
</div>

<div class="btn_fixed_top">
    <a href="./mms_group_list.php?com_idx=<?=$com_idx?>" id="btn_group" class="btn btn_02">그룹관리</a>
</div>

<script>
// 그룹관리 이동
$(document).on('click','#btn_group',function(e){
    e.preventDefault();
    var href = $(this).attr('href');
    opener.location=href;
    window.close();
});
    
$('.btn_select').click(function(e){
    e.preventDefault();
    var mmg_idx = $(this).closest('td').attr('mmg_idx');
    var mmg_name = $(this).closest('td').attr('mmg_name');
    var mmg_type = $(this).closest('td').attr('mmg_type');

    <?php
    // MMS 등록(수정)폼
    if($file_name=='mms_form'||$file_name=='mms_setting') {
    ?>
        $("input[name=mmg_idx]", opener.document).val( mmg_idx );
        $("input[name=mmg_name]", opener.document).val( mmg_name );
        
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>