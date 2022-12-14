<?php
include_once('./_common.php');

if ($member['mb_level'] < 3)
    goto_url(G5_BBS_URL."/login.php?url=".urlencode(G5_URL."/device/half_reg/form.php"));

$g5['title'] = '절단재등록';
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
    {$sql_common} {$sql_search} {$sql_group} {$sql_order}
    LIMIT {$from_record}, {$rows}
";
// echo $sql;
$result = sql_query($sql,1);

add_stylesheet('<link rel="stylesheet" href="'.G5_DEVICE_URL.'/'.$g5['dir_name'].'/style.css">', 1);
include('../head_menu.php'); 
?>
<div id="snd_div">
<strong>[API URL] : http://kunwoo.epcs.co.kr/device/half_reg/index.php</strong><br>
<strong>[API TEST] : http://kunwoo.epcs.co.kr/device/half_reg/form.php</strong><br>
<strong>[제품확인] : http://kunwoo.epcs.co.kr/device/index.php</strong><br><br>
<h5>[API에 넘겨줄 데이터]</h5>
<p>
<?php
$data_str = "
'token' : {$g5['setting']['set_api_token']}
'oop_idx' : g5_1_order_out_practice 테이블의 oop_idx
'mms_idx' : 절단설비idx g5_1_mms 테이블에서 조건 mms_type = 'cut' , mms_status NOT IN('delete','del','trash','cancel')을 참조
'bundle' : g5_1_material 테이블의 조건mtr_type='material' AND mtr_bundle = '번들번호'를 조회해서 존재하는 번들번호를 넘겨 주세요 
";
echo nl2br($data_str);
// $start_date = '2022-11-01 '.substr(G5_TIME_YMDHIS,-8);
// $s = 1;
// $date_plus = strtotime($start_date."+".($s*5)." second");
// $dt = date('Y-m-d H:i:s',$date_plus);
// echo $start_date."<br>";
// echo $dt;
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
                    <th scope="col">재고량<br><span>[mtr_total]</span></th>
                    <th scope="col">계획설비<br><span>(mms_idx)</span></th>
                    <th scope="col">절단설비<br><span>[mms_idx]</span></th>
                    <th scope="col">번들넘버<br><span>캐시저장</span></th>
                    <th scope="col">생성갯수</th>
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

                /*
                , ( SELECT COUNT(oop_idx) FROM {$g5['material_table']} WHERE oop_idx = oop.oop_idx AND mtr_type = 'half' AND mtr_status NOT IN('delete','del','trash','cancel') ) AS mtr_total
                */
                $mtr_total_res = sql_fetch(" SELECT COUNT(oop_idx) AS mtr_total FROM {$g5['material_table']} WHERE oop_idx = '{$row['oop_idx']}' AND mtr_type = 'half' AND mtr_status NOT IN('delete','del','trash','cancel') ");
                $row['mtr_total'] = $mtr_total_res['mtr_total'];
				?>

            <tr class="<?php echo $bg.$tr_focus; ?>" orp_idx="<?php echo $row['orp_idx'] ?>" bom_idx="<?=$row['bom_idx']?>">
                <td class="td_oop_idx"><?=$row['oop_idx']?></td>
                <td class="td_bom_name">
                    <?php
                    $bom_name = $bom['bom_name'];
                    // echo ($row['bct_name_tree'])?'<span class="sp_cat">'.$row['bct_name_tree'].'</span><br>':'';
                    echo '<span style="color:yellow;">'.$bom_name.'</span>';
                    echo '<br><span style="color:skyblue">'.$bom['bom_std'].'</span>';
                    ?><br>
                    <span style="color:orange;"><?=$bom['bom_part_no']?></span>
                </td>
                <td class="td_start_date"><?=substr($row['orp_start_date'],5,5)?></td>
                <td class="td_oop_cnt"><?=number_format($row['oop_count'])?></td>
                <td class="td_mtr_total"><?=number_format($row['mtr_total'])?></td>
                <td class="td_cut_mms"><?php echo (($g5['trms']['cut_idx_arr'][$row['cut_mms_idx']])?$g5['trms']['cut_idx_arr'][$row['cut_mms_idx']]:'외주절단')?></td><!-- 계획설비 -->
                <td class="td_mms_idx">
                    <select name="" class="cut_mms_idx">
                        <option value="">::설비선택::</option>
                        <?=$g5['cut_options']?>
                    </select>
                </td>
                <td class="td_mtr_bundle">
                    <input type="text" name="" value="" class="frm_input" onkeyup="javascript:chk_Code(this);">
                    <div class="sp_notice"></div>
                </td>
                <td class="td_mtr_num">
                    <input type="text" name="" value="" class="frm_input" style="text-align:right;width:50px;" onkeyup="javascript:chk_number(this)">
                </td>
                <td class="td_mtr_reg"><button type="button" oop_idx="<?=$row['oop_idx']?>" class="btn btn_reg">등록</button></td>
                <td class="td_mtr_detail"><a href="./form.php?oop_idx=<?=$row['oop_idx']?>" class="btn btn_detail">상세</a></td>
            </tr>
            <?php
            }
            if ($i == 0)
            echo "<tr><td colspan='11' class=\"empty_table\">자료가 없습니다.</td></tr>";
            ?>
            </tbody>
        </table>
        <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
    </div><!--//.tbl_head02-->
    <?php if($oop_idx){
        $sql = " SELECT * FROM {$g5['material_table']} WHERE oop_idx = '{$oop_idx}' AND mtr_type = 'half' ORDER BY mtr_idx DESC LIMIT 50 ";
        $result = sql_query($sql,1);
        //전체카운트
        $csql = " select count(*) as cnt FROM {$g5['material_table']} WHERE oop_idx = '{$oop_idx}' AND mtr_type = 'half' ";
        $crow = sql_fetch($csql);
        $tcount = $crow['cnt'];
    ?>
    <div class="tbl_head02 tbl_wrap">
        <table>
        <caption>해당제품의 상세재고목록(전체 : <?=$tcount?>개)</caption>
            <thead>
                <tr>
                    <th scope="col">ID<br><span>mtr_idx</span></th>
                    <th scope="col">BOMid<br><span>bom_idx</span></th>
                    <th scope="col">BOMPa<br><span>bom_idx_parent</span></th>
                    <th scope="col">출생계<br><span>oop_idx</span></th>
                    <th scope="col">P/NO<br><span>bom_part_no</span></th>
                    <th scope="col">품명<br><span>mtr_name</span></th>
                    <th scope="col">유형<br><span>mtr_type</span></th>
                    <th scope="col">설비<br><span>mms_idx</span></th>
                    <th scope="col">히트<br><span>mtr_heat</span></th>
                    <th scope="col">번들<br><span>mtr_bundle</span></th>
                    <th scope="col">무게<br><span>mtr_weight</span></th>
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
                    <td class="t_mms_idx"><?=$g5['trms']['cut_idx_arr'][$row['mms_idx']]?></td>
                    <td class="t_mtr_heat"><?=$row['mtr_heat']?></td>
                    <td class="t_mtr_bundle"><?=$row['mtr_bundle']?></td>
                    <td class="t_mtr_weight"><?=$row['mtr_weight']?></td>
                    <td class="t_mtr_status"><?=$row['mtr_status']?></td>
                    <!-- <td class="t_mtr_input_date"><?=$row['mtr_input_date']?></td> -->
                    <td class="t_mtr_reg_dt"><?=$row['mtr_reg_dt']?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div><!--//.tbl_head02-->
    <?php } ?>
</div><!--//#tbl_box-->
<form id="form" action="./index.php" method="POST">
    <input type="hidden" name="page" value="<?=$page?>">
</form>
<script>

$('.btn_reg').on('click',function(){
    var mms_idx = $(this).parent().siblings('.td_mms_idx').find('select').val();
    var bundle = $(this).parent().siblings('.td_mtr_bundle').find('input').val();
    var notice = $(this).parent().siblings('.td_mtr_bundle').find('.sp_notice').text();
    var number = $(this).parent().siblings('.td_mtr_num').find('input').val();
    
    if(mms_idx == ''){
        alert('설비를 선택하셔야 합니다.');
        return false;
    }
    if(bundle == ''){
        alert('번들넘버를 입력하셔야 합니다.');
        return false;
    }
    if(notice != '등록가능'){
        alert('번들넘버는 미리 등록되어 있는 번호이여야 합니다.');
        return false;
    }
    if(number == ''){
        alert('등록할 재고갯수를 입력해 주세요.');
        return false;
    }
    form_reg($(this).attr('oop_idx'),mms_idx,bundle,number,'<?=$g5['setting']['set_api_token']?>');
});


function chk_Code(object){
    // console.log(object.parentElement);
    // return false;
    var ex = /[\{\}\[\]\/?.,;:|\)*~`!^\+┼<>@\#$%&\'\"\\\(\=ㄱ-ㅎㅏ-ㅣ가-힣]*/g;
    var hx = /[A-Z0-9-_\s]{3,20}/;
    object.value = object.value.replace(ex,"");//-_\s제외한 특수문자,한글입력 불가
    var str = object.value; 
    var spn = object.nextElementSibling;
    if(str == ''){
        spn.textContent = '';
        $(spn).removeClass('sp_error');
        return false;
    }

    if(hx.test(str)){
        var oop_idx = '<?=$oop_idx?>';
        var com_chk_url = '<?=G5_DEVICE_URL?>/_ajx/mtr_bundle_overlap_chk.php';
        // var com_chk_url = './ajax/bom_part_no_overlap_chk.php';
        var st = $.trim(str.toUpperCase());
        var msg = '등록가능';
        object.value = st;
        spn.textContent = msg;
        $(spn).removeClass('sp_error');
        //디비에 bom_part_no가 존재하는지 확인하고 존재하면 에러를 발생
        //console.log(st);
        $.ajax({
            type : 'POST',
            url : com_chk_url,
            dataType : 'text',
            data : {'oop_idx' : oop_idx,'mtr_bundle' : st},
            success : function(res){
                //console.log(res);
                if(res == 'no'){
                    spn.textContent = '미등록넘버';
                    $(spn).removeClass('sp_error');
                    $(spn).addClass('sp_error');
                }
                else if(res == 'overlap'){
                    spn.textContent = '등록가능';
                    $(spn).removeClass('sp_error');
                }
            },
            error : function(xmlReq){
                alert('Status: ' + xmlReq.status + ' \n\rstatusText: ' + xmlReq.statusText + ' \n\rresponseText: ' + xmlReq.responseText);
            }
        });
    }
    else {
        spn.textContent = '코드규칙위반';
        $(spn).removeClass('sp_error');
        $(spn).addClass('sp_error');
    }
}

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

// 절단재 재고 등록 함수
function form_reg(oop_idx,mms_idx,bundle,number,token){
    var info = {
        'test' : 1
        ,'oop_idx' : oop_idx
        ,'mms_idx' : mms_idx
        ,'bundle' : bundle
        ,'number' : number
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
