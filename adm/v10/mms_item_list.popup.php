<?php
$sub_menu = "925220";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

if(!$mms_idx)
    alert_close('설비 정보가 존재하지 않습니다.');
$mms = get_table_meta('mms','mms_idx',$mms_idx);
$com = get_table_meta('company','com_idx',$mms['com_idx']);

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'mms_item';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = preg_replace("/_list/","",$g5['file_name']); // _list을 제외한 파일명
$qstr .= '&mms_idx='.$mms_idx; // 추가로 확장해서 넘겨야 할 변수들



$sql_common = " FROM {$g5_table_name} AS ".$pre."
                 LEFT JOIN {$g5['mms_table']} AS mms ON mms.mms_idx = ".$pre.".mms_idx
";

$where = array();
$where[] = " ".$pre."_status NOT IN ('trash','delete') AND ".$pre.".mms_idx = '".$mms_idx."' ";   // 디폴트 검색조건

// 검색어 설정
if ($stx != "") {
    switch ($sfl) {
		case ( $sfl == 'mb_hp' ) :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
        default :
			$where[] = " $sfl LIKE '%".trim($stx)."%' ";
            break;
    }
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = $pre."_idx";
    $sod = "DESC";
}

$sql_order = " order by {$sst} {$sod} ";

// 테이블의 전체 레코드수
$sql = " SELECT COUNT(*) AS cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$rows = 10;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$config['cf_write_pages'] = $config['cf_mobile_pages'] = 5;


$sql = "SELECT * {$sql_common} {$sql_search} {$sql_order}
        LIMIT $from_record, $rows
";
//echo $sql.'<br>';
$result = sql_query($sql);


$g5['title'] = '생산기종 설정';
include_once('./_head.sub.php');

// 각 항목명 및 항목 설정값 정의, 형식: 항목명, colspan, rowspan, 정렬링크여부(타이틀클릭)
// arr0:name, arr1:colspan, arr2:rowspan, arr3: sort
$items1 = array(
    "mmi_no"=>array("기종번호",0,0,0)
    ,"mmi_name"=>array("기종명",0,0,0)
    ,"mmi_price_info"=>array("가격정보",0,0,0)
);
?>
<!-- 체크박스 전체 선택 -->
<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>
    <div class=" new_win_con">

        <div>설비명: <b style="font-size:1.2em;"><?php echo $mms['mms_name']; ?></b></div>

        <form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
        <input type="hidden" name="mms_idx" value="<?=$mms_idx?>">
        <label for="sfl" class="sound_only">검색대상</label>
        <select name="sfl" id="sfl">
            <?php
            $skips = array('com_idx','mmg_idx');
            if(is_array($items1)) {
                foreach($items1 as $k1 => $v1) {
                    if(in_array($k1,$skips)) {continue;}
                    echo '<option value="'.$k1.'" '.get_selected($sfl, $k1).'>'.$v1[0].'</option>';
                }
            }
            ?>
        </select>
        <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input">
        <input type="submit" class="btn_submit" value="검색">
        </form>
        
        <form name="form01" id="form01" action="./mms_item_list_update.popup.php" onsubmit="return form01_submit(this);" method="post">
        <input type="hidden" name="w" value="">
        <input type="hidden" name="sst" value="<?php echo $sst ?>">
        <input type="hidden" name="sod" value="<?php echo $sod ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">
        <input type="hidden" name="token" value="">
        <input type="hidden" name="mms_idx" value="<?php echo $mms_idx ?>">
        <div class="tbl_head01 tbl_wrap">
            <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
            <tr>
                <th scope="col" id="mb_list_chk">
                    <label for="chkall" class="sound_only">전체항목</label>
                    <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                </th>
                <?php
                $skips = array();
                if(is_array($items1)) {
                    foreach($items1 as $k1 => $v1) {
                        if(in_array($k1,$skips)) {continue;}
                        $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                        $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                        // 정렬 링크
                        if($v1[3]>0)
                            echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.subject_sort_link($k1).$v1[0].'</a></th>';
                        else
                            echo '<th scope="col" '.$row['colspan'].' '.$row['rowspan'].'>'.$v1[0].'</th>';
                    }
                }
                ?>
                <th scope="col" id="mb_list_mng">관리</th>
            </tr>
            </thead>
            <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {

                // price info, the last one is showing
                $sql = " SELECT * FROM {$g5['mms_item_price_table']} WHERE mmi_idx = '".$row['mmi_idx']."' ORDER BY mip_start_date DESC LIMIT 1 ";
                // echo $sql.'<br>';
                $mip1 = sql_fetch($sql,1);
                // print_r2($mip1);
                $row['mmi_price_text'] = $mip1['mip_price'] ? number_format($mip1['mip_price']).' <span style="font-size:0.8em;">('.$mip1['mip_start_date'].'~)</span>' : '';

                $s_mod = '<a href="./mms_item_form.popup.php?'.$qstr.'&w=u&'.$pre.'_idx='.$row[$pre.'_idx'].'" class="btn btn_03">수정</a>';

                $bg = 'bg'.($i%2);
                // 1번 라인 ================================================================================
                echo '<tr class="'.$bg.' tr_'.$row[$pre.'status'].'" tr_id="'.$row[$pre.'idx'].'">'.PHP_EOL;
                ?>
                <td class="td_chk">
                    <input type="hidden" name="<?=$pre?>_idx[<?php echo $i ?>]" value="<?php echo $row[$pre.'_idx'] ?>" id="<?=$pre?>_idx_<?php echo $i ?>">
                    <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row[$pre.'name']); ?></label>
                    <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                </td>
                <?php
                $skips = array();
                if(is_array($items1)) {
        //            print_r2($items1);
                    foreach($items1 as $k1 => $v1) {
                        if(in_array($k1,$skips)) {continue;}
        //                echo $k1.'<br>';
        //                print_r2($v1);

                        $list[$k1] = $row[$k1];

                        if(preg_match("/_price$/",$k1)) {
                            $list[$k1] = number_format($row[$k1]);
                        }
                        else if(preg_match("/_dt$/",$k1)) {
                            $list[$k1] = substr($row[$k1],0,10);
                        }
                        else if($k1 == 'mmi_price_info') {
                            $list[$k1] = $row['mmi_price_text'];
                        }

                        $row['colspan'] = ($v1[1]>1) ? ' colspan="'.$v1[1].'"' : '';   // colspan 설정
                        $row['rowspan'] = ($v1[2]>1) ? ' rowspan="'.$v1[2].'"' : '';   // rowspan 설정
                        echo '<td class="td_'.$k1.'" '.$row['colspan'].' '.$row['rowspan'].'>'.$list[$k1].'</td>';
                    }
                }
                echo '<td class="td_mngsmall">'.$s_mod.'</td>'.PHP_EOL;
                echo '</tr>'.PHP_EOL;

            }
            if ($i == 0)
                echo '<tr><td colspan="20" class="empty_table">자료가 없습니다.</td></tr>';
            ?>
            </tbody>
            </table>
        </div>
        <?php if(!auth_check($auth[$sub_menu],"d",1)) { ?>
        <div>
            <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn" style="display:none;">
            <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        </div>
        <?php } ?>

        <div class="btn_fixed_top">
            <a href="<?=$_SERVER['SCRIPT_NAME']?>?mms_idx=<?=$mms_idx?>" id="member_add" class="btn btn_02">전체</a>
            <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
            <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02" style="display:none;">
            <a href="./mms_item_form.popup.php?mms_idx=<?=$mms_idx?>" id="btn_add" class="btn btn_01">추가하기</a>
        </div>
        </form>

        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;ser_mms_type='.$ser_mms_type.'&amp;page='); ?>

    </div>
</div>

<script>
// 윈도우 크기 재설정
window.onload = reSize;
window.onresize = reSize;
function reSize() {
	resizeTo(520, 680);    // 여는 페이지 설정 높이 80 차이
}
    
$(function() {
    // 설비선택
    $("#btn_member").click(function() {
        var href = $(this).attr("href");
        memberwin = window.open(href, "memberwin", "left=100,top=100,width=520,height=600,scrollbars=1");
        memberwin.focus();
        return false;
    });
	$(".btn_delete").click(function(e) {
		if(confirm('해당 항목을 정말 삭제하시겠습니까?')) {
			var token = get_ajax_token();
			self.location="./mms_item_list_update.popup.php?<?=$qstr?>&token="+token+"&w=d&<?=$pre?>_idx="+$(this).attr('<?=$pre?>_idx');
		}
	});
});

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

	if(document.pressed == "선택수정") {
		$('input[name="w"]').val('u');
	}
	if(document.pressed == "선택삭제") {
		if (!confirm("선택한 항목(들)을 정말 삭제 하시겠습니까?\n복구가 어려우니 신중하게 결정 하십시오.")) {
			return false;
		}
		else {
			$('input[name="w"]').val('d');
		} 
	}
    return true;
}
</script>


<?php
include_once('./_tail.sub.php');
?>