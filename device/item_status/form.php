<?php
include_once('./_common.php');

if ($member['mb_level'] < 3)
    goto_url(G5_BBS_URL."/login.php?url=".urlencode(G5_URL."/device/item_status/form.php"));

$g5['title'] = '완제품상태';
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
                , ( SELECT SUM(itm_weight) FROM {$g5['item_table']} WHERE oop_idx = oop.oop_idx ) AS itm_total
                , ( SELECT COUNT(*) FROM {$g5['item_table']} WHERE oop_idx = oop.oop_idx AND itm_reg_dt LIKE '{$todate}%' ) AS itm_cnt
    {$sql_common} {$sql_search} {$sql_group} {$sql_order}
    LIMIT 15
";

$result = sql_query($sql,1);
?>
<style>
body{background:#333;color:#fff;padding:20px;}
#tbl_des{display:table;border-collapse:collapse;border-spacing:0px;border:1px solid #777;width:100%;}
#tbl_des th{width:33.333333%;}
#tbl_des th,#tbl_des td{border:1px solid #777;}
#tbl_des td{padding:10px;line-height:1.6em;vertical-align: top;}
dl:after{display:block;visibility:hidden;clear:both;content:'';}
dt{float:left;width:120px;}
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
.td_bom_name{width:90px;}
.td_ord_idx{text-align:right;}
.td_orp_idx{text-align:right;}
.td_ord_date{text-align:center;}
.td_trm_idx_line{text-align:center;}
.td_start_date{text-align:center;width:50px !important;}
.td_end_date{text-align:center;width:50px !important;}
.td_oro_cnt{text-align:right;}
.td_oop_cnt{text-align:right;}
.td_oop_status{text-align:right;overflow:hidden;width:70px;}
.td_itm_total{text-align:right;width:50px;}
.td_itm_detail{text-align:center;}
button{cursor:pointer;}
.t_itm_weight{text-align:center;}
input.weight{background:#333;color:#fff;padding:0 5px;height:20px;line-height:20px;width:30px;text-align:right;}
.t_btn{text-align:center;}
.btn{font-size:0.8em;}
.select{height:20px !important;line-height:20px !important;background:#333;color:#fff;}
</style>
<h3>
    <a href="<?=G5_USER_ADMIN_URL?>" class="home"><i class="fa fa-home" aria-hidden="true"></i></a>
    <?=$g5['title']?>
</h3>
<?php include('../half_item_menu.php'); ?>
<div id="snd_div">
<h5>[각 기능별 버튼 클릭시 API에 넘겨줄 데이터]</h5>
<table id="tbl_des">
    <thead>
        <tr>
            <th>재출력</th>
            <th>상태</th>
            <th>검색</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <dl>
                    <dt>token</dt><dd>: <?=$g5['setting']['set_api_token']?></dd>
                    <dt>type</dt><dd>: 작업유형 => "reoutput"</dd>
                    <dt>itm_idx</dt><dd>: g5_1_item 테이블</dd>
                    <dt>itm_weight</dt><dd>: g5_1_item 테이블 완제품 변경된 무게</dd>
                    <p style="color:yellow;">수정한 바코드정보의 라벨을 반드시 재부착 할거라는 보장이 없으므로</p>
                    <p style="color:yellow;">바코드 정보는 수정하면 안됨 itm_weight만 수정해서 라벨 재출력함</p>
                </dl>
            </td>
            <td>
                <dl>
                    <dt>token</dt><dd>: <?=$g5['setting']['set_api_token']?></dd>
                    <dt>type</dt><dd>: 작업유형 => "status"</dd>
                    <dt>itm_barcode</dt><dd>: 리딩한 바코드정보</dd>
                    <dt>itm_status</dt><dd>: pending=대기,finish=생산완료,delivery=출하,compounding=컴파운딩,merge=병합,scrap=폐기,trash=삭제,error_color=색상불량,error_properties=물성불량,error_etc=기타불량</dd>
                    <p style="color:yellow;">실제로 현장에서 필요한 상태값은 아래정도의 상태값들만 있으면 된다</p>
                    <p style="color:yellow;">finish=생산완료,delivery=출하,compounding=컴파운딩,merge=병합,scrap=폐기,error_color=색상불량,error_properties=물성불량,error_etc=기타불량</p>
                </dl>
            </td>
            <td>
                <dl>
                    <dt>token</dt><dd>: <?=$g5['setting']['set_api_token']?></dd>
                    <dt>type</dt><dd>: 작업유형 => "search"</dd>
                    <dt>itm_barcode</dt><dd>: 리딩한 바코드정보</dd>
                    <p style="color:yellow;">바코드 정보로 완제품IMP상에서 해당 itm_idx의 제품을 포커싱해줌</p>
                    <p style="color:yellow;">현장에 search=검색에 해당하는 바코드가 필요함</p>
                </dl>
            </td>
        </tr>
    </tbody>
</table>
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
                    <th scope="col">재고량<br><span>itm_total</span></th>
                    <th scope="col">상세</th>
                </tr>
            </thead>
            <tbody>
            <?php
            for ($i=0; $row=sql_fetch_array($result); $i++) {
                $bg = 'bg'.($i%2);
                $bom = get_table_meta('bom','bom_idx',$row['bom_idx']);
                $tr_focus = ($row['oop_idx'] == $oop_idx) ? ' focus' : '';
                // print_r2($bom);
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
                <td class="td_itm_total"><?=number_format($row['itm_total'])?></td>
                <td class="td_itm_detail"><a href="./form.php?oop_idx=<?=$row['oop_idx']?>" class="btn btn_detail">상세</a></td>
            </tr>
            <?php
            }
            if ($i == 0)
            echo "<tr><td colspan='13' class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
            </tbody>
        </table>
    </div><!--//.tbl_head02-->
    <?php if($oop_idx || $itm_idx){
        $sql = " SELECT * FROM {$g5['item_table']} WHERE oop_idx = '{$oop_idx}' ORDER BY itm_idx DESC ";
        $result = sql_query($sql,1);

        // print_r2($g5['line_name']);
    ?>
    <div class="tbl_head02 tbl_wrap">
        <table>
        <caption>해당제품의 상세재고목록</caption>
            <thead>
                <tr>
                    <th scope="col">ID<br><span>itm_idx</span></th>
                    <th scope="col">BOMid<br><span>bom_idx</span></th>
                    <th scope="col">출생계<br><span>oop_idx</span></th>
                    <th scope="col">바코드<br><span>itm_barcode</span></th>
                    <th scope="col">무게<br><span>itm_weight</span></th>
                    <th scope="col">상태<br><span>itm_status</span></th>
                    <th scope="col">검색<br><span>search</span></th>
                </tr>
            </thead>
            <tbody>
                <?php for($i=0;$row=sql_fetch_array($result);$i++) {
                    $bg = 'bg'.($i%2);
                    $t_focus = ($row['itm_idx'] == $itm_idx) ? ' focus' : '';
                ?>
                <tr class="<?php echo $bg.$t_focus; ?>">
                    <td class="t_itm_idx"><?=$row['itm_idx']?></td>
                    <td class="t_bom_idx"><?=$row['bom_idx']?></td>
                    <td class="t_oop_idx"><?=$row['oop_idx']?></td>
                    <td class="t_itm_barcode"><?=$row['itm_barcode']?></td>
                    <td class="t_itm_weight">
                        <input type="text" name="itm_weight" class="frm_input weight" value="<?=$row['itm_weight']?>"  onclick="javascript:chk_number(this)">
                        <button type="button" itm_idx="<?=$row['itm_idx']?>" class="btn btn_01 btn_reoutput">재출력</button>
                    </td>
                    <td class="t_itm_status">
                        <select name="itm_status" class="select itm_status_<?=$i?>">
                            <?=$g5['set_itm_status_options']?>
                        </select>
                        <button type="button" itm_barcode="<?=$row['itm_barcode']?>" class="btn btn_01 btn_status">변경</button>
                        <script>$('.itm_status_<?=$i?>').val('<?=$row['itm_status']?>');</script>
                    </td>
                    <td class="t_btn">
                        <button type="button" itm_barcode="<?=$row['itm_barcode']?>" class="btn btn_01 btn_search">검색</button>
                    </td>
                </tr>
                <?php
                }
                if ($i == 0)
                echo "<tr><td colspan='7' class=\"empty_table\">자료가 없습니다.</td></tr>";
                ?>
            </tbody>
        </table>
    </div><!--//.tbl_head02-->
    <?php } ?>
</div><!--//#tbl_box-->
<form id="form" action="./index.php" method="POST">
</form>
<script>
//라벨재출력 버튼
$('.btn_reoutput').on('click',function(){
    form_status('<?=$g5['setting']['set_api_token']?>','reoutput',$(this));
});
//상태변경 버튼
$('.btn_status').on('click',function(){
    form_status('<?=$g5['setting']['set_api_token']?>','status',$(this));
});
//검색 버튼
$('.btn_search').on('click',function(){
    form_status('<?=$g5['setting']['set_api_token']?>','search',$(this));
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

// 재출력 함수
function form_status(token,type,btn){
    if(!token) {
        alert('토큰값이 없습니다.');
        return false;
    }

    var info = {'test' : 1, 'token' : token, 'type' : type};

    if(type == 'reoutput') {
        var itm_idx = btn.attr('itm_idx');
        var wt = btn.siblings('input').val();
        if(!wt){
            alert('무게데이터가 없습니다.');
            return false;
        }

        if(!itm_idx) {
            alert('itm_idx 데이터가 없습니다.');
            return false;
        }

        info['itm_idx'] = itm_idx;
        info['itm_weight'] = wt;
    }
    else if(type == 'status') {
        var itm_barcode = btn.attr('itm_barcode');
        var itm_status = btn.siblings('.select').val();
        if(!itm_barcode) {
            alert('바코드정보가 없습니다.');
            return false;
        }

        if(!itm_status) {
            alert('상태정보가 없습니다.');
            return false;
        }

        info['itm_barcode'] = itm_barcode;
        info['itm_status'] = itm_status;
    }
    else if(type == 'search') {
        var itm_barcode = btn.attr('itm_barcode');
        if(!itm_barcode) {
            alert('바코드정보가 없습니다.');
            return false;
        }

        info['itm_barcode'] = itm_barcode;
    }
    else {
        alert('작업유형값이 없습니다.');
        return false;
    }
    //#form안을 텅비운다.
    $('#form').empty();
    for(key in info){
        // console.log(key+':'+info[key]);
        $('<input type="hidden" name="'+key+'" value="'+info[key]+'">').appendTo('#form');
    }

    // alert(JSON.stringify(info));

    $('#form').submit();
}


</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
