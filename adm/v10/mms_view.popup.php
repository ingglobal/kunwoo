<?php
// 호출페이지들
// /adm/v10/mms_parts_form.php: 설비검색
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

$mms = get_table_meta('mms','mms_idx',$mms_idx);
if (!$mms['mms_idx'])
    alert_close('존재하지 않는 자료입니다.');
$com = get_table_meta('company','com_idx',$mms['com_idx']);
$imp = get_table_meta('imp','imp_idx',$mms['imp_idx']);
$mmg = get_table_meta('mms_group','mmg_idx',$mms['mmg_idx']);


$g5['title'] = $mms['mms_name'].' 설비 이력카드';
include_once('./_head.sub.php');

$fle_width = 440;
$fle_height = 300;
// mms_img 타입중에서 대표 이미지 한개만
$sql = "SELECT * FROM {$g5['file_table']}
        WHERE fle_db_table = 'mms' AND fle_db_id = '".$mms['mms_idx']."'
            AND fle_type = 'mms_img'
            AND fle_sort = 0
        ";
//echo $sql.'<br>';
$rs1 = sql_query($sql,1);
for($j=0;$row1=sql_fetch_array($rs1);$j++) {
//  print_r2($row1);
    if( $row1['fle_name'] && is_file(G5_PATH.$row1['fle_path'].'/'.$row1['fle_name']) ) {
        $row['img'] = $row[$row1['fle_type']][$row1['fle_sort']]; // 변수명 좀 짧게
        $row['img']['thumbnail'] = thumbnail($row1['fle_name'], 
                        G5_PATH.$row1['fle_path'], G5_PATH.$row1['fle_path'],
                        $fle_width, $fle_height, 
                        false, true, 'center', true, $um_value='85/3.4/15');	// is_create, is_crop, crop_mode
    }
    else {
        $row[$row1['fle_type']][$row1['fle_sort']]['thumbnail'] = 'default.png';
        $row1['fle_path'] = '/data/mms_img';	// 디폴트 경로 결정해야 합니다.
    }
    $mms['thumbnail_img'] = '<img src="'.G5_URL.$row1['fle_path'].'/'.$row['img']['thumbnail'].'"
                                        width="'.$fle_width.'" height="'.$fle_height.'">';
}
?>
<script>
// 윈도우 크기 재설정
window.onload = reSize;
window.onresize = reSize;
function reSize() {
	resizeTo(700, 750);
}
</script>

<div id="mms_view" class="new_win scp_new_win">
    <h1><?php echo $g5['title'];?></h1>

    <div class="view01">
	<table>
	<tbody>
        <tr>
            <td style="width:35%">

                <table class="table01">
                <tbody>
                <tr>
                    <td>설비번호</td>
                    <td><?=$mms['mms_number']?></td>
                </tr>
                <tr>
                    <td>설비명</td>
                    <td><?=$mms['mms_name']?></td>
                </tr>
                <tr>
                    <td>고유번호</td>
                    <td><?=$mms['mms_unique_number']?></td>
                </tr>
                <tr>
                    <td>구입처</td>
                    <td><?=$mms['mms_dealer']?></td>
                </tr>
                <tr>
                    <td>취득일자</td>
                    <td><?=$mms['mms_install_date']?></td>
                </tr>
                <tr>
                    <td>취득금액</td>
                    <td><?=number_format($mms['mms_price'])?></td>
                </tr>
                </tbody>
                </table>
            
            </td>
            <td rowspan="2" class="td_image" style="width:65%">
                <?=$mms['thumbnail_img']?>
            </td>
        </tr>
        <tr>
            <td>

                <table class="table02">
                <tbody>
                <tr class="tr_title02">
                    <td colspan="2">제원 및 사양</td>
                </tr>
                <tr>
                    <td class="td_item">모델명</td>
                    <td><?=$mms['mms_model']?></td>
                </tr>
                <tr>
                    <td colspan="2" class="td_center"><?=($mms['mms_size'])?:'&nbsp;'?></td>
                </tr>
                </tbody>
                </table>

            </td>
        </tr>
	</tbody>
	</table>
    </div>

    <div class="view01">
	<table>
	<tbody>
        <tr>
            <td style="width:100%;vertical-align:top;">

                <table class="table03">
                <tbody>
                <tr class="tr_title03">
                    <td colspan="3">
                        <a href="./mms_parts_list.php?mms_idx=<?=$mms_idx?>" class="btn_more">더보기</a>
                        주요부속품관리기준
                    </td>
                </tr>
                <tr class="item_title03">
                    <td>품명</td>
                    <td>규격</td>
                    <td>교체주기</td>
                </tr>
                <?php
                $sql = "SELECT * FROM {$g5['mms_parts_table']}
                        WHERE mms_idx = '".$mms_idx."'
                            AND mmp_status NOT IN ('trash','delete')
                        ORDER BY mmp_idx DESC
                        LIMIT 5
                        ";
                $rs = sql_query($sql,1);
                for($i=0;$row=sql_fetch_array($rs);$i++) {
//                    print_r2($row);
                    ?>
                    <tr>
                        <td><?=$row['mmp_name']?></td>
                        <td><?=$row['mmp_size']?></td>
                        <td><?=$row['mmp_change_cycle']?>일</td>
                    </tr>
                    <?php
                }
                if($i<=0)
                    echo '<tr><td colspan="5" class="empty_table">자료가 없습니다.</td></tr>';
                ?>
                </tbody>
                </table>
            
            </td>
        </tr>
	</tbody>
	</table>
    </div>

    <div class="view01">
	<table>
	<tbody>
        <tr>
            <td>
                
                <table class="table03">
                <tbody>
                <tr class="tr_title03">
                    <td colspan="5">
                        <a href="./mms_checks_list.php?mms_idx=<?=$mms_idx?>" class="btn_more">더보기</a>
                        주요 점검기준
                    </td>
                </tr>
                <tr class="item_title03">
                    <td style="width:50px;">NO</td>
                    <td>점검항목</td>
                    <td>점검내용</td>
                    <td>점검주기</td>
                    <td>점검기준</td>
                </tr>
                <?php
                $sql = "SELECT * FROM {$g5['mms_checks_table']}
                        WHERE mms_idx = '".$mms_idx."'
                            AND mmc_status NOT IN ('trash','delete')
                        ORDER BY mmc_idx DESC
                        LIMIT 5
                        ";
                $rs = sql_query($sql,1);
                for($i=0;$row=sql_fetch_array($rs);$i++) {
//                    print_r2($row);
                    ?>
                    <tr>
                        <td><?=($i+1)?></td>
                        <td><?=$row['mmc_name']?></td>
                        <td><?=$row['mmc_memo']?></td>
                        <td><?=$row['mmc_cycle']?></td>
                        <td><?=$row['mmc_checks']?></td>
                    </tr>
                    <?php
                }
                if($i<=0)
                    echo '<tr><td colspan="5" class="empty_table">자료가 없습니다.</td></tr>';
                ?>
                </tbody>
                </table>
            
            </td>
        </tr>
	</tbody>
	</table>
    </div>

    <div class="btn_fixed_top">
        <a href="./mms_setting.php?mms_idx=<?=$mms_idx?>" class="btn btn_02" title="설비설정"><i class="fa fa-gear"></i></a>
        <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
    </div>
</div>

<script>
$('.btn_select').click(function(e){
    e.preventDefault();
    var mms_idx = $(this).closest('td').attr('mms_idx');
    var mms_name = $(this).closest('td').attr('mms_name');  // 
    var mms_install_date = $(this).closest('td').attr('mms_install_date');    // 

    <?php
    // 게시판 글쓰기
    if($file_name=='mms_parts_form') {
    ?>
        $("input[name=mms_idx]", opener.document).val( mms_idx );
        $("input[name=mms_name]", opener.document).val( mms_name );
        
    <?php
    }
    ?>

    window.close();
});
</script>

<?php
include_once('./_tail.sub.php');
?>