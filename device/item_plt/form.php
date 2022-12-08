<?php
include_once('./_common.php');

if ($member['mb_level'] < 3)
    goto_url(G5_BBS_URL."/login.php?url=".urlencode(G5_URL."/device/half_plt/form.php"));

$g5['title'] = '절단재PLT등록';
add_stylesheet('<link rel="stylesheet" href="'.G5_DEVICE_URL.'/_css/default.css">', 0);
include_once(G5_PATH.'/head.sub.php');


$sql_common = " FROM {$g5['order_out_practice_table']} oop
    INNER JOIN {$g5['order_practice_table']} orp ON orp.orp_idx = oop.orp_idx
    INNER JOIN {$g5['bom_table']} bom ON oop.bom_idx = bom.bom_idx
";

$where = array();
// 디폴트 검색조건 (used 제외)
$where[] = " oop.oop_status NOT IN ('del','delete','trash') ";
$where[] = " orp.com_idx = '".$_SESSION['ss_com_idx']."' ";

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

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_group} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 15;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " SELECT *
            , ( SELECT COUNT(oop_idx) FROM {$g5['material_table']} WHERE oop_idx = oop.oop_idx AND mtr_type = 'half' AND mtr_status NOT IN('delete','del','trash','cancel') ) AS cut_total
            , ( SELECT COUNT(plt_idx) FROM {$g5['pallet_table']} WHERE oop_idx = oop.oop_idx AND plt_status NOT IN('delete','del','trash','cancel') ) AS plt_c
    {$sql_common} {$sql_search} {$sql_group} {$sql_order}
    LIMIT {$from_record}, {$rows}
";
// echo $sql;
$result = sql_query($sql,1);

add_stylesheet('<link rel="stylesheet" href="'.G5_DEVICE_URL.'/'.$g5['dir_name'].'/style.css">', 1);
include('../head_menu.php'); 
?>
<div id="snd_div">
<strong>[API URL] : http://kunwoo.epcs.co.kr/device/half_plt/index.php</strong><br>
<strong>[API TEST] : http://kunwoo.epcs.co.kr/device/half_plt/form.php</strong><br>
<strong>[제품확인] : http://kunwoo.epcs.co.kr/device/index.php</strong><br><br>
<h5>[API에 넘겨줄 데이터]</h5>
<p>
<?php
$data_str = "
[ token ] : {$g5['setting']['set_api_token']}
[ oop_idx ] : g5_1_order_out_practice 테이블의 oop_idx를 넘겨주세요.
[ heat ] : g5_1_material 테이블의 조건mtr_type='half' AND mtr_status NOT IN('delete','del','trash','cancel') group by mtr_heat를 조회해서 존재하는 히트넘버중에 한 개를 선택해서 넘겨주세요. 
[ plt_barcode ] : 년월일카운트_mmsidx_CUT_oopidx_bompartno_수량 ( 221109004_71_C_123_A1234-F1234_100 )
[ plt_cnt ] : 해당 PLT에 들어있는 수량
";
echo nl2br($data_str);
// echo "<br>=================<br>";
// $total = 1500;
// $cnt = 200;
// $i_cnt = $total / $cnt;
// $r_cnt = $total % $cnt;
// $p_cnt = ($total - $r_cnt) / $cnt;
// echo '전체수량 : '.$total."<br>";
// echo 'PLT개수 : '.$cnt."<br>";
// echo '나누기 : '.$total." / ".$cnt." = ".($total / $cnt)."<br>";
// echo '나머지 : '.$total." % ".$cnt." = ".($total % $cnt)."<br>";
// echo 'PLT안수량 : ('.$total.' - '.$r_cnt.') / '.$cnt.' = '.(($total - $r_cnt) / $cnt)."<br>";
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
                    <th scope="col">시작일<br><span>(orp_start_date)</span></th>
                    <th scope="col">지시량<br><span>(oop_cnt)</span></th>
                    <th scope="col">재고량<br><span>[cut_total]</span></th>
                    <th scope="col">계획설비<br><span>(mms_idx)</span></th>
                    <th scope="col">히트넘버선택</th>
                    <th scope="col">PLT갯수</th>
                    <th scope="col">PLT수량</th>
                    <th scope="col">생성</th>
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
                $loss_weight = ($row['oop_itm_weight']) ? $row['oop_mtr_weight'] - $row['oop_itm_weight'] : 0;
				$loss_rate = ($row['oop_itm_weight']) ? (($row['oop_mtr_weight'] - $row['oop_itm_weight'])/$row['oop_itm_weight']) * 100 : 0;
				$loss_rate = number_format($loss_rate,2,'.','');
                //히트넘버 선택박스 구성
                $hsql = " SELECT mtr_heat FROM {$g5['material_table']}
                            WHERE mtr_type = 'half'
                                AND mtr_status = 'stock'
                                AND oop_idx = '{$row['oop_idx']}'
                            GROUP BY mtr_heat ";
                $hres = sql_query($hsql,1);
                // echo print_r2($hres);
                $heat_slt = '';
                $heat_opt = '';
                for($j=0;$hrow=sql_fetch_array($hres);$j++){
                    $heat_opt .= '<option value="'.$hrow['mtr_heat'].'">'.$hrow['mtr_heat'].'</option>';
                }
                if($heat_opt){
                    $heat_slt .= '<select>'.$heat_opt.'</select>';
                }
                else{
                    $heat_slt= '<b style="color:red;">히트넘버 없음</b>';
                }
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
                    // echo ($row['bct_name_tree'])?'<span class="sp_cat">'.$row['bct_name_tree'].'</span><br>':'';
                    echo '<span style="color:yellow;">'.$bom_name.'</span>';
                    echo '<br><span style="color:skyblue">'.$bom['bom_std'].'</span>';
                    ?><br>
                    <span style="color:orange;"><?=$bom['bom_part_no']?></span>
                </td>
                <td class="td_start_date"><?=substr($row['orp_start_date'],5,5)?></td>
                <td class="td_oop_cnt"><?=number_format($row['oop_count'])?></td>
                <td class="td_cut_total" total="<?=$row['cut_total']?>"><?=number_format($row['cut_total'])?></td>
                <td class="td_cut_mms"><?php echo (($g5['trms']['cut_idx_arr'][$row['cut_mms_idx']])?$g5['trms']['cut_idx_arr'][$row['cut_mms_idx']]:'외주절단')?></td><!-- 계획설비 -->
                <td class="td_heat"><?=$heat_slt?></td>
                <td class="td_plt_num">
                    <input type="text" name="" value="" class="frm_input" style="text-align:right;width:50px;" onkeyup="javascript:chk_number(this)">
                </td>
                <td class="td_plt_c" style="text-align:right;"><?=(($row['plt_c'])?number_format($row['plt_c']):'')?></td>
                <td class="td_plt_reg">
                    <?php if($heat_opt){ ?>
                        <button type="button" oop_idx="<?=$row['oop_idx']?>" class="btn btn_reg">등록</button>
                    <?php } ?>
                </td>
                <td class="td_mtr_detail"><a href="./form.php?oop_idx=<?=$row['oop_idx']?>" class="btn btn_detail">상세</a></td>
            </tr>
            <?php
            }
            if ($i == 0)
            echo "<tr><td colspan='10' class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
            </tbody>
        </table>
        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
    </div><!--//.tbl_head02-->
    <?php if($oop_idx){
        $sql = " SELECT plt_idx
                        , plt.bom_idx
                        , plt.oop_idx
                        , plt.bom_std
                        , com.com_name
                        , bom.bom_name
                        , plt.bom_part_no
                        , plt.plt_heat
                        , plt.plt_barcode
                        , plt.plt_count
                        , plt.plt_type
                        , plt.plt_status
                        , plt.plt_reg_dt
                 FROM {$g5['pallet_table']} plt
                    LEFT JOIN {$g5['order_out_practice_table']} oop ON plt.oop_idx = oop.oop_idx
                    LEFT JOIN {$g5['bom_table']} bom ON plt.bom_idx = bom.bom_idx
                    LEFT JOIN {$g5['company_table']} com ON bom.com_idx_customer = com.com_idx
                 WHERE oop.oop_idx = '{$oop_idx}' AND plt_type = 'half' ORDER BY plt_idx DESC LIMIT 25 ";
        // echo $sql;
        $result = sql_query($sql,1);
        //전체카운트
        $csql = " SELECT count(*) AS cnt FROM {$g5['pallet_table']} plt
                INNER JOIN {$g5['order_out_practice_table']} oop ON plt.oop_idx = oop.oop_idx
                INNER JOIN {$g5['bom_table']} bom ON plt.bom_idx = bom.bom_idx
                LEFT JOIN {$g5['company_table']} com ON bom.com_idx_customer = com.com_idx
            WHERE oop.oop_idx = '{$oop_idx}' AND plt_type = 'half' ORDER BY plt_idx 
        ";
        $crow = sql_fetch($csql);
        $tcount = $crow['cnt'];
    ?>
    <div class="tbl_head02 tbl_wrap">
        <table>
        <caption>해당PLT의 상세재고목록(전체 : <?=$tcount?>개)</caption>
            <thead>
                <tr>
                    <!--
                        plt_idx
                        , com.com_name
                        , bom.bom_name
                        , bom.bom_part_no
                        , plt.plt_heat
                        , plt.plt_barcode
                        , plt.plt_type
                        , plt.plt_status
                        , plt.plt_reg_dt
                    -->
                    <th scope="col">ID<br><span>plt_idx</span></th>
                    <th scope="col">BOMid<br><span>bom_idx</span></th>
                    <th scope="col">출생계<br><span>oop_idx</span></th>
                    <th scope="col">P/NO<br><span>bom_part_no</span></th>
                    <th scope="col">규격<br><span>bom_std</span></th>
                    <th scope="col">품명<br><span>bom_name</span></th>
                    <th scope="col">히트넘버<br><span>plt_heat</span></th>
                    <th scope="col">바코드<br><span>plt_barcode</span></th>
                    <th scope="col">수량<br><span>plt_count</span></th>
                    <th scope="col">유형<br><span>plt_type</span></th>
                    <th scope="col">상태<br><span>plt_status</span></th>
                    <th scope="col">등록일시<br><span>plt_reg_dt</span></th>
                </tr>
            </thead>
            <tbody>
                <?php for($i=0;$row=sql_fetch_array($result);$i++) {
                    
                ?>
                <tr>
                    <td class="t_mtr_idx"><?=$row['plt_idx']?></td>
                    <td class="t_bom_idx"><?=$row['bom_idx']?></td>
                    <td class="t_oop_idx"><?=$row['oop_idx']?></td>
                    <td class="t_bom_part_no"><?=$row['bom_part_no']?></td>
                    <td class="t_bom_std"><?=$row['bom_std']?></td>
                    <td class="t_bom_name"><?=$row['bom_name']?></td>
                    <td class="t_plt_heat"><?=$row['plt_heat']?></td>
                    <td class="t_plt_barcode"><?=$row['plt_barcode']?></td>
                    <td class="t_plt_count"><?=$row['plt_count']?></td>
                    <td class="t_plt_type"><?=$row['plt_type']?></td>
                    <td class="t_plt_status"><?=$row['plt_status']?></td>
                    <td class="t_plt_reg_dt"><?=$row['plt_reg_dt']?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div><!--//.tbl_head02-->
    <?php } ?>
</div><!--//#tbl_box-->
<form id="form" action="./index.php" method="POST"></form>
<script>
/*
[ token ] : {$g5['setting']['set_api_token']}
[ oop_idx ] : g5_1_order_out_practice 테이블의 oop_idx를 넘겨주세요.
[ heat ] : g5_1_material 테이블의 조건mtr_type='half' AND mtr_status NOT IN('delete','del','trash','cancel') group by mtr_heat를 조회해서 존재하는 히트넘버중에 한 개를 선택해서 넘겨주세요. 
[ plt_barcode ] : 년월일카운트_mmsidx_CUT_oopidx_bompartno_수량 ( 221109004_71_C_123_A1234-F1234_100 )
[ plt_cnt ] : 해당 PLT에 들어있는 수량
*/
$('.btn_reg').on('click',function(){
    //oop_idx
    //token
    var cut_total = $(this).parent().siblings('.td_cut_total').attr('total');
    var heat = $(this).parent().siblings('.td_heat').find('select').val();
    var plt_cnt = $(this).parent().siblings('.td_plt_num').find('input').val();
    
    if(!cut_total){
        alert('해당제품의 재고수량이 없습니다.');
        return false;
    }

    if(Number(cut_total) < Number(plt_cnt)){
        alert('해당제품의 재고수량이 PLT의 수량보다 작으면 안됩니다.');
    }

    if(heat == ''){
        alert('히트넘버를 선택하셔야 합니다.');
        return false;
    }

    if(plt_cnt == ''){
        alert('등록할 PLT갯수를 입력해 주세요.');
        return false;
    }
    form_reg($(this).attr('oop_idx'),cut_total,heat,plt_cnt,'<?=$g5['setting']['set_api_token']?>');
});

// 숫자만 입력
function chk_number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}

// 절단재 재고 등록 함수
function form_reg(oop_idx,cut_total,heat,plt_cnt,token){
    var info = {
        'test' : 1
        ,'oop_idx' : oop_idx
        ,'cut_total' : cut_total
        ,'heat' : heat
        ,'plt_cnt' : plt_cnt
        ,'token' : token
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