<?php
$sub_menu = "920100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');
$g5['title'] = '수주목록';
include_once('./_head.php');
echo $g5['container_sub_title'];
/*
if(!$com_idx)
    alert_close('업체 정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);
*/
$sql_common = " FROM {$g5['order_table']} AS ord
                 LEFT JOIN {$g5['company_table']} AS com ON ord.com_idx = com.com_idx
";

$where = array();
$where[] = " ord_status NOT IN ('trash','delete','del','cancel') AND ord.com_idx = '".$_SESSION['ss_com_idx']."' ";   // 디폴트 검색조건

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
//검색어가 없는 디폴트 상태에서는 오늘날짜에서 13일간의 목록을 보여준다.
/*else{
    $d_to_day = get_dayAddDate(G5_TIME_YMD,13);
    $where[] = " ord_date >= '".G5_TIME_YMD."' AND ord_date <= '".$d_to_day."' ";
}*/

if($ord_date_from && !$ord_date_to){
    $ord_date_to = get_dayAddDate($ord_date_from,13);
    $where[] = " ord.ord_date >= '".$ord_date_from."' AND ord.ord_date <= '".$ord_date_to."' ";
}
else if(!$ord_date_from && $ord_date_to){
    $ord_date_from = get_dayAddDate($ord_date_to,-13);
    $where[] = " ord.ord_date >= '".$ord_date_from."' AND ord.ord_date <= '".$ord_date_to."' ";
}
else if($ord_date_from && $ord_date_to && $ord_date_from != $ord_date_to){
    $where[] = " ord.ord_date >= '".$ord_date_from."' AND ord.ord_date <= '".$ord_date_to."' ";
}
else if($ord_date_from && $ord_date_to && $ord_date_from == $ord_date_to){
    $where[] = " ord.ord_date = '".$ord_date_from."' ";
}
else{
    // $d_to_day = get_dayAddDate(G5_TIME_YMD,13);
    // $where[] = " ord_date >= '".G5_TIME_YMD."' AND ord_date <= '".$d_to_day."' ";
    ;
}
/*else{
    $where[] = " ord.ord_date >= '".G5_TIME_YMD."' AND ord.ord_date <= '".G5_TIME_YMD."' ";
}*/

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "ord_date";
    $sod = "DESC";
}

$sql_order = " order by {$sst} {$sod} ";

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">기본목록</a>';


$sql = " select count(*) as cnt {$sql_common} {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "SELECT * {$sql_common} {$sql_search} {$sql_order} ";
//print_r3($sql);
$result = sql_query($sql);

?>
<style>
.tbl_head01 thead tr th{position:sticky;top:100px;z-index:100;}
.td_bom_name {text-align:left !important;width:270px;}
.td_bom_part_no, .td_com_name, .td_bom_maker
,.td_bom_items, .td_bom_items_title {text-align:left !important;}
.span_bom_price {margin-left:20px;}
.span_ori_count:before {content:'×';}
.td_bom_items {color:#818181 !important;}
.span_bom_part_no {margin-left:10px;}
.span_bom_price b, .span_ori_count b {color:#bb71bb;font-weight:normal;}
.div_item {font-size:0.9em;line-height:140%;border-bottom:1px dotted #555;}
.div_item > span{display:inline-block;}
.div_item .span_bom_name{width:240px;}
.div_item .span_bom_part_no{width:110px;}
.div_item .span_bom_std{width:210px;}
.div_item .span_bom_price{}
.div_item .span_bom_price b{display:inline-block;width:45px;text-align:right;}
.div_item .span_ori_count{}
.div_item .span_ori_count b{display:inline-block;width:58px;text-align:right;}
.sch_label{position:relative;}
.sch_label span{position:absolute;top:-23px;left:5px;z-index:2;}
.sch_label .date_blank{position:absolute;top:-21px;right:0px;z-index:2;font-size:1.1em;cursor:pointer;}
.slt_label{position:relative;}
.slt_label span{position:absolute;top:-23px;left:5px;z-index:2;}


.md_ol > li{margin-top:10px;}
.loading{display:inline-block;}
.loading_hide{display:none;}
</style>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">총 </span><span class="ov_num"> <?php echo number_format($total_count) ?>건 </span></span>
</div>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl" style="display:none;">
        <option value="bom_name"<?php echo get_selected($_GET['sfl'], "bom_name"); ?>>품명</option>
        <option value="bom_part_no"<?php echo get_selected($_GET['sfl'], "bom_part_no"); ?>>품번</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="frm_input" style="display:none;">
    <label for="ord_date_from" class="sch_label">
        <span>수주일(부터)</span>
        <i class="fa fa-times date_blank" aria-hidden="true"></i>
        <input type="text" name="ord_date_from" value="<?php echo $ord_date_from ?>" id="ord_date_from" readonly class="frm_input readonly" placeholder="수주일(부터)" style="width:100px;" autocomplete="off">
    </label>
    ~
    <label for="ord_date_to" class="sch_label">
        <span>수주일(까지)</span>
        <i class="fa fa-times date_blank" aria-hidden="true"></i>
        <input type="text" name="ord_date_to" value="<?php echo $ord_date_to ?>" id="ord_date_to" readonly class="frm_input readonly" placeholder="수주일(까지)" style="width:100px;" autocomplete="off">
    </label>
    <input type="submit" class="btn_submit" value="검색">
</form>
<script>
$('.date_blank').on('click',function(e){
    e.preventDefault();
    $(this).siblings('input').val('');
});
</script>
<div class="local_desc01 local_desc" style="display:no ne;">
    <p>제품명에 <span style="color:orange;">주황색</span>은 해당 BOM데이터에 가격과 카테고리 설정이 안되어 있다는 의미입니다.(해당 BOM페이지로 이동하여 설정완료 해 주세요.)</p>
</div>


<form name="form01" id="form01" action="./order_list_update.php" onsubmit="return form01_submit(this);" method="post">
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
    <thead>
    <tr>
        <th scope="col" id="bom_list_chk">
            <label for="chkall" class="sound_only">전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col"><?php echo subject_sort_link('ord_idx') ?>번호</a></th>
        <th scope="col">수주금액</th>
        <th scope="col">제품</th>
        <th scope="col">출하계획</th>
        <!--th scope="col">실행계획</th-->
        <th scope="col">수주일</th>
        <th scope="col">수주상태</th>
        <th scope="col">관리</th>
    </tr>
    <tr>
    </tr>
    </thead>
    <tbody>
    <?php
    //print_r2($result);
    $next_date = '';
    $next2_date = '';
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $csql = sql_fetch(" SELECT com_name FROM {$g5['company_table']} WHERE com_idx = '{$row['com_idx_customer']}' ");
        $row['com_name_customer'] = $csql['com_name'];

        // 출하 건수
        $sql2 = " SELECT COUNT(oro_idx) AS cnt FROM {$g5['order_out_table']} WHERE ord_idx = '".$row['ord_idx']."' AND oro_status NOT IN('delete','del','trash') ";
        $row['oro'] = sql_fetch($sql2,1);
        //print_r2($row['ord_date']);
        // 제품목록
        $sql1 = "SELECT bom.bom_idx, bom.bct_id, bom.bom_name, bom_part_no, bom.bom_std, bom.bom_price, bom.bom_sort, bom.bom_status, ori.ori_idx, ori.ori_count
                FROM {$g5['order_item_table']} AS ori
                    LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = ori.bom_idx
                WHERE ori.ord_idx = '".$row['ord_idx']."' AND ori.ori_status NOT IN('trash','delete','del','cancel')
                ORDER BY ori_idx LIMIT 0,5
        ";
        //print_r3($sql1);
        $rs1 = sql_query($sql1,1);

        //수주ID에 등록된 제품갯수
        $cnt1 = sql_fetch(" SELECT COUNT(*) AS cnt FROM {$g5['order_item_table']} AS ori
            LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = ori.bom_idx
            WHERE ori.ord_idx = '".$row['ord_idx']."' AND ori.ori_status NOT IN('trash','delete','del','cancel')
        ");
        $total_cnt1 = $cnt1['cnt'];
        //echo $total_cnt1;
        $out_flag = true;//출하가능여부 변수
        for ($j=0; $row1=sql_fetch_array($rs1); $j++) {
            // print_r2($row1);
			$otq_sql = " SELECT SUM(oro_count) AS ous FROM {$g5['order_out_table']} WHERE ord_idx = '{$row['ord_idx']}' AND ori_idx = '{$row1['ori_idx']}' AND oro_status NOT IN('trash','delete','del','cancel') ";
			//echo $otq_sql."<br>";
            $otq = sql_fetch($otq_sql);
			$out_cnt = ($otq['ous']) ? $otq['ous'] : 0;
			//echo $out_cnt;
			$cnt_blick = 'txt_brown';//($out_cnt != $row1['ori_count']) ? 'txt_red' : '';
			
            if(!$row1['bom_price']) $out_flag = false;//가격이 책정되어 있지 않으면 출하불가능
            //$bom_mod = ($row1['bom_price'] && $row1['bct_id']) ? $row1['nbsp'].$row1['bom_name'] : '<a href="'.G5_USER_ADMIN_URL.'/bom_form.php?w=u&bom_idx='.$row1['bom_idx'].'" target="_blank" class="txt_orangeblink">'.$row1['nbsp'].$row1['bom_name'].'</a>';
            $bom_mod = ($row1['bom_price'] && $row1['bct_id']) ? $row1['nbsp'].cut_str($row1['bom_name'], 20) : '<a href="'.G5_USER_ADMIN_URL.'/bom_form.php?w=u&bom_idx='.$row1['bom_idx'].'" target="_blank" class="txt_orange">'.$row1['nbsp'].$row1['bom_name'].'</a>';

            $row['item_list'][] = '<div class="div_item">
                                        <span class="span_bom_name">'.$bom_mod.'('.$row1['ori_idx'].')</span>
                                        <span class="span_bom_part_no">'.$row1['bom_part_no'].'</span>
                                        <span class="span_bom_std">'.$row1['bom_std'].'</span>
                                        <span class="span_bom_price"><b>'.number_format($row1['bom_price']).'</b>원</span>
                                        <span class="span_ori_count"><b><span class="'.$cnt_blick.'">&nbsp;'.number_format($row1['ori_count']).'</span></b> 개</span>
                                    </div>';
        }
		
		$oro_url = '';
        $oro_btn = '';
        

        $creat = 0;
        
        if($row['oro']['cnt']){
            $oro_url = './order_out_list.php?sfl=oro.ord_idx&stx='.$row['ord_idx'];
            $oro_btn = $row['oro']['cnt'].'건';
            
            //또 엑셀을 업데이트해서 새롭게 등록된 제품이 있을 수 있으므로 확인하여 추가 등록 가능하도록 하자
            $addSql = " SELECT COUNT(*) AS cnt ,(
                            SELECT COUNT(*) FROM {$g5['order_out_table']} WHERE ord_idx = '{$row['ord_idx']}' AND oro_status NOT IN('delete','del','trash')
                        ) AS cnt2 FROM {$g5['order_item_table']} AS ori
                        LEFT JOIN {$g5['order_out_table']} AS oro ON ori.ori_idx = oro.ori_idx
                    WHERE ori.ord_idx = '{$row['ord_idx']}' 
                        AND oro.oro_idx IS NULL 
                        AND ori.ori_status NOT IN('delete','del','trash')
            ";
            // echo $addSql."<br>";
            $addCnt = sql_fetch($addSql);
            if ( $addCnt['cnt'] && $addCnt['cnt2'] ){
                $create = 1;
                $oro_add_url = './order_out_create.php?w=&ord_idx='.$row['ord_idx'].'&ord_date='.$row['ord_date'].'&add=1';
                $oro_add_btn = '<spn style="color:orange;">추가계획<br>생성</span>';
            }
            else{
                $create = 0;
                $oro_add_url = '';
                $oro_add_btn = '';
            }
        }
        else {
            $create = 1;
            $oro_url = './order_out_create.php?w=&ord_idx='.$row['ord_idx'].'&ord_date='.$row['ord_date'];
            $oro_btn = '<spn style="color:orange;">임시계획<br>생성</span>';
            $oro_add_url = '';
            $oro_add_btn = '';
        }
        
        //$s_item = '<a href="./order_item.php?'.$qstr.'&amp;ord_idx='.$row['ord_idx'].'" class="btn btn_03">상품</a>';
        $s_mod = '<a href="./order_form.php?'.$qstr.'&amp;w=u&amp;ord_idx='.$row['ord_idx'].'" class="btn btn_03">수정</a>';
		//print_r2($next_date);
        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>" tr_id="<?php echo $row['ord_idx'] ?>">
        <td class="td_chk">
            <input type="hidden" name="ord_idx[<?php echo $row['ord_idx']; ?>]" value="<?php echo $row['ord_idx'] ?>" id="ord_idx_<?php echo $i ?>">
            <label for="chk_<?php echo $i; ?>">
            <input type="checkbox" name="chk[]" value="<?php echo $row['ord_idx'] ?>" id="chk_<?php echo $i ?>">
            </label>
        </td>
        <td class="td_num"><?php echo $row['ord_idx']; ?></td>
        <td class="td_ord_price" style="text-align:right;">
            <?=number_format($row['ord_price'])?> 원<br>
            총 <?=number_format($total_cnt1)?> 건
        </td><!-- 수주금액 -->
        <td class="td_com_name"><!-- 제품 -->
            <?=implode(" ",$row['item_list'])?>
        </td>
        <td class="td_ord_ship_date">
            <a href="<?=(($create)?'javascript:':$oro_url)?>" class="<?=(($create)?'oroButton':'')?>" link="<?=$oro_url?>"><?=$oro_btn?></a>
            <?php if($oro_add_url){ ?>
            <br><a href="javascript:" link="<?=$oro_add_url?>" class="oroButton"><?=$oro_add_btn?></a>
            <?php } ?>
        </td><!-- 출하 -->
        <!--td class="td_practice_cnt">
            
        </td-->
        <td class="td_ord_reg_dt">
            <?php if($row['ord_date'] == G5_TIME_YMD){ ?>
                <strong style="color:skyblue;"><?=$row['ord_date']?></strong>
            <?php }else { ?>
                <?=$row['ord_date']?>
            <?php } ?>
        </td><!-- 수주일 -->
        <td class="td_ord_status"><?=$g5['set_ord_status_value'][$row['ord_status']]?></td><!-- 수주상태 -->
        <td class="td_mng">
			<?=$s_mod?>
		</td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo "<tr><td colspan='8' class=\"empty_table\">자료가 없습니다.</td></tr>";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <?php if (!auth_check($auth[$sub_menu],'d')) { ?>
       <a href="./order_form.php" id="member_add" class="btn btn_01">추가하기</a>
    <?php } ?>
    <?php if ($is_admin){ //(!auth_check($auth[$sub_menu],'w')) { ?>
    <!--input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn btn_02"-->
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>

</div>


</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

<script>
$("input[name=ord_date_from]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=ord_date_to]").datepicker('option','minDate',selectedDate);} });
$("input[name=ord_date_to]").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("input[name=ord_date_to]").datepicker('option','maxDate',selectedDate);} });

$('.oroButton').on('click',function(){
    if(!confirm('데이터량에 따라 다소 시간이 걸릴수 있으니\n잠시만 기다려 주십시오.')){
        return false;
    }

    $(location).attr('href',$(this).attr('link'));
    //$(location).attr('href','https://google.com');
});


// 마우스 hover 설정
$(".tbl_head01 tbody tr").on({
    mouseenter: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','#0b1938');
        
    },
    mouseleave: function () {
        $('tr[tr_id='+$(this).attr('tr_id')+']').find('td').css('background','unset');
    }    
});

// 가격 입력 쉼표 처리
$(document).on( 'keyup','input[name^=bom_price], input[name^=bom_count], input[name^=bom_lead_time]',function(e) {
    if(!isNaN($(this).val().replace(/,/g,'')))
        $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
});

// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}
    

function form01_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
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

function form02_submit(f){
    if(!f.file_excel.value){
        alert('파일을 선택해 주세요.');
        return false;
    }

    if(!confirm("대량데이터처리이므로 시간이 1분이상 소요될수 있습니다.\n실행하는 동안 창을 닫거나, 다른버튼을 클릭해서는 안됩니다.\n작업을 진행하시겠습니까?")){
        return false;
    }
    $('.loading').removeClass('loading_hide');
    return true;
}
</script>

<?php
include_once('./_tail.php');

