<?php
include_once('./_common.php');

if ($member['mb_level'] < 3)
    goto_url(G5_BBS_URL."/login.php?url=".urlencode(G5_URL."/device/half_output/form.php"));

$g5['title'] = '반제품출력';
include_once(G5_PATH.'/head.sub.php');

$sql_common = " FROM {$g5['order_out_practice_table']} AS oop
    LEFT JOIN {$g5['bom_table']} AS bom ON oop.bom_idx = bom.bom_idx
    LEFT JOIN {$g5['order_practice_table']} AS orp ON orp.orp_idx = oop.orp_idx
    LEFT JOIN {$g5['order_out_table']} AS oro ON oop.oro_idx = oro.oro_idx
    LEFT JOIN {$g5['order_table']} AS ord ON oro.ord_idx = ord.ord_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " oop.oop_status NOT IN ('del','delete','trash') AND orp.com_idx = '".$_SESSION['ss_com_idx']."' ";

if($orp_start_date){
    $where[] = " orp.orp_start_date = '".$orp_start_date."' ";
    $qstr .= '&orp_start_date='.$orp_start_date;
}

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);

if (!$sst) {
    $sst = "orp.orp_start_date";
    $sod = "desc";
}
if (!$sst2) {
    $sst2 = ", orp.trm_idx_line";
    $sod2 = "";
}
if (!$sst3) {
    $sst3 = ", oop.oop_idx";
    $sod3 = "desc";
}

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} {$sst3} {$sod3} ";
$sql_group = "";//" GROUP BY oop.orp_idx ";
$todate = G5_TIME_YMD;
$sql = " SELECT *
                , ( SELECT SUM(mtr_weight) FROM {$g5['material_table']} WHERE oop_idx = oop.oop_idx AND mtr_status = 'finish' ) AS mtr_total
                , ( SELECT COUNT(*) FROM {$g5['material_table']} WHERE oop_idx = oop.oop_idx AND mtr_status = 'finish' ) AS mtr_cnt
    {$sql_common} {$sql_search} {$sql_group} {$sql_order}
    LIMIT 15
";
// echo $sql;
$result = sql_query($sql,1);
?>
<style>
body{background:#333;color:#fff;padding:20px;}
#snd_div{padding-bottom:20px;}
#snd_div h5{margin-top:10px;margin-bottom:0px;}
#snd_div p{line-height:1.5em;}
#lst_div{padding-bottom:20px;display:none;}
#lst_div h5{margin-top:10px;margin-bottom:0px;}
#lst_div p{line-height:1.5em;}
a,a:hover{color:#fff;}
.home{color:skyblue;}
.home:hover{color:pink;}
#hd_login_msg{display:none;}
caption{text-align:left;}
#tbl_box{}
#tbl_box:after{display:none;visibility:hidden;clear:both;content:'';}
.tbl_head02{width:50%;float:left;}
.tbl_head02 table{width:100%;}
.tbl_head02 tbody tr{background:#555;}
.tbl_head02 tbody tr.bg0{background:#777;}
.tbl_head02 tbody tr.bg1{background:#666;}
.tbl_head02 tbody tr:hover{background:#2951A7;}
.tbl_head02 tbody tr.focus{background:#7E4416;}
.tbl_head02 thead th,.tbl_head02 tbody td{font-size:0.8em;}
.tbl_head02 thead th span{font-size:0.6em;}
.tbl_head02 tbody td{padding:5px;}
.td_oop_idx{text-align:right;}
.td_bom_name{width:110px;}
.td_ord_idx{text-align:right;}
.td_orp_idx{text-align:right;}
.td_ord_date{text-align:center;}
.td_trm_idx_line{text-align:center;}
.td_start_date{text-align:center;}
.td_end_date{text-align:center;}
.td_oro_cnt{text-align:right;}
.td_oop_cnt{text-align:right;}
.td_oop_status{text-align:right;overflow:hidden;width:100px;}
.td_mtr_total{text-align:right;width:50px;}
.td_mtr_cnt{text-align:right;width:50px;}
input.weight{background:#333;color:#fff;padding:0 10px;height:30px;width:30px;line-height:30px;text-align:right;}
button{cursor:pointer;}
.btn_output{background:#2951A7;color:#fff;}
.btn_detail{background:#213142;color:#fff;}
</style>
<h3>
    <a href="<?=G5_USER_ADMIN_URL?>" class="home"><i class="fa fa-home" aria-hidden="true"></i></a>
    <?=$g5['title']?>
</h3>
<?php include('../half_item_menu.php'); ?>
<div id="snd_div">
<h5>[출력버튼 클릭시 API에 넘겨줄 데이터]</h5>
<p>
<?php
$data_str = "
'token' : {$g5['setting']['set_api_token']}
'bom_idx' : 반제품 bom_idx 얻는 쿼리 => SELECT bom_idx_child FROM g5_1_bom_item WHERE bom_idx = '부모(완제품) bom_idx'
'bom_idx_parent' : 완제품의 bom_idx => g5_1_order_out_practice 테이블의 bom_idx
'oop_idx' : g5_1_order_out_practice 테이블의 oop_idx
'bom_part_no' : g5_1_bom 테이블의 반제품의 bom_part_no
'mtr_name' : g5_1_bom 테이블의 반제품의 bom_name
'mtr_barcode' : 반제품 barcode는 가공해서 만들어야하는 값
'trm_idx_location' : g5_1_order_practice 테이블의 trm_idx_line
'weight' : 무게 저울측정 또는 입력값
";
echo nl2br($data_str);
?>
</p>
</div>
<div id="lst_div">
<h5>[목록 호출쿼리 참조]</h5>
<p>
<?php
echo nl2br($sql);
?>
</p>
</div>
<div id="tbl_box">
    <div class="tbl_head02 tbl_wrap">
        <table>
            <caption>생산계획 최근 15개 목록</caption>
            <thead>
                <tr>
                    <th scope="col">출생계ID<br><span>(oop_idx)</span></th>
                    <th scope="col">
                        품명<br><span>(bom_idx)</span><br>
                        <span>(bom_part_no)</span>
                    </th>
                    <th scope="col">수주ID<br><span>(ord_idx)</span></th>
                    <th scope="col">계획ID<br><span>(orp_idx)</span></th>
                    <th scope="col">수주일<br><span>(ord_date)</span></th>
                    <th scope="col">설비<br><span>(trm_idx_line)</span></th>
                    <th scope="col">시작일<br><span>(orp_start_date)</span></th>
                    <th scope="col">종료일<br><span>(orp_done_date)</span></th>
                    <th scope="col">출하량<br><span>(oro_cnt)</span></th>
                    <th scope="col">지시량<br><span>(orp_cnt)</span></th>
                    <th scope="col">상태<br><span>(orp_status)</span></th>
                    <th scope="col">재고량<br><span>[mtr_total]</span></th>
                    <th scope="col">개수<br><span>[mtr_cnt]</span></th>
                    <th scope="col">측정량<br><span>(mtr_weight)</span></th>
                    <th scope="col">출력</th>
                    <th scope="col">상세</th>
                </tr>
            </thead>
            <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                $bg = 'bg'.($i%2);
                $bom = get_table_meta('bom','bom_idx',$row['bom_idx']);
                $bc_res = sql_fetch(" SELECT bom_idx_child FROM {$g5['bom_item_table']} WHERE bom_idx = '{$row['bom_idx']}' ");
                $bom2 = get_table_meta('bom','bom_idx',$bc_res['bom_idx_child']);
                $tr_focus = ($row['oop_idx'] == $oop_idx) ? ' focus' : '';
                // print_r2($bom2);
                ?>

            <tr class="<?php echo $bg.$tr_focus; ?>" orp_idx="<?php echo $row['orp_idx'] ?>" bom_idx="<?=$row['bom_idx']?>">
                <td class="td_oop_idx"><?=$row['oop_idx']?></td>
                <td class="td_bom_name">
                    <?php
                    $cat_tree = category_tree_array($bom['bct_id']);
                    $row['bct_name_tree'] = '';
                    for($k=0;$k<count($cat_tree);$k++){
                        $cat_str = sql_fetch(" SELECT bct_name FROM {$g5['bom_category_table']} WHERE bct_id = '{$cat_tree[$k]}' ");
                        $row['bct_name_tree'] .= ($k == 0) ? $cat_str['bct_name'] : ' > '.$cat_str['bct_name'];
                    }
                    $bom_name = $bom['bom_name'];
                    echo ($row['bct_name_tree'])?'<span class="sp_cat">'.$row['bct_name_tree'].'</span><br>':'';
                    echo '<span style="color:yellow;">'.$bom_name.'</span>';
                    echo '<br><span style="color:skyblue">'.$bom['bct_id'].'</span>';
                    ?><br>
                    <span style="color:orange;"><?=$bom['bom_part_no']?></span>
                </td>
                <td class="td_ord_idx"><?=$row['ord_idx']?></td>
                <td class="td_orp_idx"><?=$row['orp_idx']?></td>
                <td class="td_ord_date"><?=(($row['ord_date'])?substr($row['ord_date'],5,5):' - ')?></td>
                <td class="td_trm_idx_line"><?=$g5['line_name'][$row['trm_idx_line']]?><br><?=$row['trm_idx_line']?></td>
                <td class="td_start_date"><?=substr($row['orp_start_date'],5,5)?></td>
                <td class="td_end_date"><?=substr($row['orp_done_date'],5,5)?></td>
                <td class="td_oro_cnt"><?=number_format($row['oro_count'])?></td>
                <td class="td_oop_cnt"><?=number_format($row['oop_count'])?></td>
                <td class="td_oop_status"><?php echo $g5['set_oop_status_value'][$row['oop_status']]?><br>(<?=$row['oop_status']?>)</td><!-- 상태 -->
                <td class="td_mtr_total"><?=number_format($row['mtr_total'])?></td>
                <td class="td_mtr_cnt"><?=number_format($row['mtr_cnt'])?></td>
                <td class="td_mtr_weight"><input type="text" name="mtr_weight" class="weight frm_input" value="" onclick="javascript:chk_number(this)"></td>
                <td class="td_mtr_output">
                    <button type="button" class="btn btn_output"
                        bom_idx="<?=$bom2['bom_idx']?>"
                        bom_idx_parent="<?=$row['bom_idx']?>"
                        oop_idx="<?=$row['oop_idx']?>"
                        bom_part_no="<?=$bom2['bom_part_no']?>"
                        mtr_name="<?=$bom2['bom_name']?>"
                        trm_idx_location="<?=$row['trm_idx_line']?>"
                        mtr_cnt="<?=($row['mtr_cnt']+1)?>"
                    >출력</button>
                </td>
                <td class="td_mtr_detail"><a href="./form.php?oop_idx=<?=$row['oop_idx']?>" class="btn btn_detail">상세</a></td>
            </tr>
            <?php
            }
            if ($i == 0)
            echo "<tr><td colspan='16' class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
            </tbody>
        </table>
    </div><!--//.tbl_head02-->
    <?php if($oop_idx){
        $sql = " SELECT * FROM {$g5['material_table']} WHERE oop_idx = '{$oop_idx}' AND mtr_type = 'half' ORDER BY mtr_idx DESC ";
        $result = sql_query($sql,1);
    ?>
    <div class="tbl_head02 tbl_wrap">
        <table>
        <caption>해당제품의 상세재고목록</caption>
            <thead>
                <tr>
                    <th scope="col">반제ID<br><span>mtr_idx</span></th>
                    <th scope="col">BOMid<br><span>bom_idx</span></th>
                    <th scope="col">BOMPa<br><span>bom_idx_parent</span></th>
                    <th scope="col">출생계<br><span>oop_idx</span></th>
                    <th scope="col">P/NO<br><span>bom_part_no</span></th>
                    <th scope="col">품명<br><span>mtr_name</span></th>
                    <th scope="col">유형<br><span>mtr_type</span></th>
                    <th scope="col">바코드<br><span>mtr_barcode</span></th>
                    <th scope="col">무게<br><span>mtr_weight</span></th>
                    <th scope="col">LOT<br><span>mtr_lot</span></th>
                    <th scope="col">라인<br><span>trm_idx_location</span></th>
                    <th scope="col">상태<br><span>mtr_status</span></th>
                    <th scope="col">생산일<br><span>mtr_input_date</span></th>
                </tr>
            </thead>
            <tbody>
                <?php for($i=0;$row=sql_fetch_array($result);$i++) {
                ?>
                <tr>
                    <td class="t_mtr_idx"><?=$row['mtr_idx']?></td>
                    <td class="t_bom_idx"><?=$row['bom_idx']?></td>
                    <td class="t_bom_idx_parent"><?=$row['bom_idx_parent']?></td>
                    <td class="t_oop_idx"><?=$row['oop_idx']?></td>
                    <td class="t_bom_part_no"><?=$row['bom_part_no']?></td>
                    <td class="t_mtr_name"><?=$row['mtr_name']?></td>
                    <td class="t_mtr_type"><?=$row['mtr_type']?></td>
                    <td class="t_mtr_barcode"><?=$row['mtr_barcode']?></td>
                    <td class="t_mtr_weight"><?=$row['mtr_weight']?></td>
                    <td class="t_mtr_lot"><?=$row['mtr_lot']?></td>
                    <td class="t_trm_idx_location"><?=$row['trm_idx_location']?></td>
                    <td class="t_mtr_status"><?=$row['mtr_status']?></td>
                    <td class="t_mtr_input_date"><?=$row['mtr_input_date']?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div><!--//.tbl_head02-->
    <?php } ?>
</div><!--//#tbl_box-->
<form id="form" action="./index.php" method="POST">
</form>
<script>

$('.btn_output').on('click',function(){
    var tr_obj = $(this).parent().parent();
    var ipt = $(this).parent().siblings('.td_mtr_weight').find('.weight').val();
    if(!ipt){
        alert('측정량 데이터가 없으면 출력할 수 없습니다.');
        return false;
    }

    $('tr').removeClass('focus');
    tr_obj.addClass('focus')
    if(!confirm('현재 무게데이터로 바코드를 출력하시겠습니까?')){
        return false;
    }
    form_output($(this),ipt,'<?=$g5['setting']['set_api_token']?>');

});

// 숫자만 입력
function chk_number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

//자릿수만큼 앞단에 0으로 채우기 함수
function pad(n, width) {
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join('0') + n;
}

// 바코드 등록 함수
function form_output(obj,ipt,token){
    var dt = new Date(+new Date() + 3240 * 10000).toISOString().split("T")[0];
    dt = dt.replaceAll("-","").substring(2);
    var cnt = Number(obj.attr('mtr_cnt')) + 1;
    cnt = pad(cnt,3);
    var ab = 'B';
    var wt = ipt;
    var part_no = obj.attr('bom_part_no');
    var barcode = dt+cnt+ab+wt+part_no;
    // return false;
    var info = {
        'test' : 1
        ,'token' : token
        ,'bom_idx' : obj.attr('bom_idx')
        ,'bom_idx_parent' : obj.attr('bom_idx_parent')
        ,'oop_idx' : obj.attr('oop_idx')
        ,'bom_part_no' : obj.attr('bom_part_no')
        ,'mtr_name' : obj.attr('mtr_name')
        ,'mtr_barcode' : barcode
        ,'trm_idx_location' : obj.attr('trm_idx_location')
        ,'weight' : ipt
    };
    for(key in info){
        // console.log(key+':'+info[key]);
        $('<input type="hidden" name="'+key+'" value="'+info[key]+'">').appendTo('#form');
    }
    $('#form').submit();
}
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
