<?php
$sub_menu = "990100";
include_once('./_common.php');

auth_check($auth[$sub_menu],"r");

// 변수 설정, 필드 구조 및 prefix 추출
$table_name = 'user_log';
$g5_table_name = $g5[$table_name.'_table'];
$fields = sql_field_names($g5_table_name);
$pre = substr($fields[0],0,strpos($fields[0],'_'));
$fname = $g5['file_name'];


$g5['title'] = '사용자로그통계';
include_once('./_top_menu_setting.php');
include_once('./_head.php');
echo $g5['container_sub_title'];

$menu_arr = array();

foreach($menu as $pmk=>$pmv){
    foreach($pmv as $mv){
        $menu_arr[$mv[0]] = $mv[1];
    }
}

// print_r3($menu_arr);
$sql_common = " FROM {$g5['user_log_table']} AS usl
                    LEFT JOIN {$g5['member_table']} AS mb ON usl.mb_id = mb.mb_id
";

$f_dt = ($from_dt)?$from_dt.' 00:00:00':date("Y-m-d H:i:s",strtotime("-1 year",strtotime(G5_TIME_YMD)));
$t_dt = ($to_dt)?$to_dt.' 23:59:59':G5_TIME_YMD.' 23:59:59';
$where = array();
$where[] = " usl_reg_dt <= '{$t_dt}' ";   // 디폴트 검색조건
$where[] = " usl_reg_dt >= '{$f_dt}' ";   // 디폴트 검색조건

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


if (!$sst) {
    $sst = "usl_menu_cd";
    $sod = "";
}

if (!$sst2) {
    $sst2 = ""; //", mb_name"
    $sod2 = "";
}

$sql_group = " GROUP BY usl_menu_cd ";

$sql_order = " ORDER BY {$sst} {$sod} {$sst2} {$sod2} ";

$sql = " SELECT usl_menu_cd
            ,( SELECT COUNT(*) FROM {$g5['user_log_table']}
                    WHERE usl_menu_cd = usl.usl_menu_cd
                        AND usl_type = '접속'
                        AND usl_reg_dt >='{$f_dt}'
                        AND usl_reg_dt <='{$t_dt}'
            ) AS usl_cnt_login
            ,( SELECT COUNT(*) FROM {$g5['user_log_table']}
                    WHERE usl_menu_cd = usl.usl_menu_cd
                        AND usl_type = '검색'
                        AND usl_reg_dt >='{$f_dt}'
                        AND usl_reg_dt <='{$t_dt}'
            ) AS usl_cnt_search
            ,( SELECT COUNT(*) FROM {$g5['user_log_table']}
                    WHERE usl_menu_cd = usl.usl_menu_cd
                        AND usl_type = '등록'
                        AND usl_reg_dt >='{$f_dt}'
                        AND usl_reg_dt <='{$t_dt}'
            ) AS usl_cnt_register
            ,( SELECT COUNT(*) FROM {$g5['user_log_table']}
                    WHERE usl_menu_cd = usl.usl_menu_cd
                        AND usl_type = '수정'
                        AND usl_reg_dt >='{$f_dt}'
                        AND usl_reg_dt <='{$t_dt}'
            ) AS usl_cnt_modify
            ,( SELECT COUNT(*) FROM {$g5['user_log_table']}
                    WHERE usl_menu_cd = usl.usl_menu_cd
                        AND usl_type = '삭제'
                        AND usl_reg_dt >='{$f_dt}'
                        AND usl_reg_dt <='{$t_dt}'
            ) AS usl_cnt_delete
        {$sql_common}
        {$sql_search}
        {$sql_group}
        {$sql_order}
";

// echo $sql;
$result = sql_query($sql,1);

$rows = 100;
$count = sql_fetch_array( sql_query(" SELECT FOUND_ROWS() as total ") );
$total_count = $count['total'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산

$colspan = 7;
?>
<style>
#container{min-width:1800px !important;}

#tot_box{position:absolute;display:none;top:10px;right:10px;font-size:1.3em;}
#tot_box:after{display:block;visibility:hidden;clear:both;content:'';}
#tot_box strong{color:#555;float:left;font-weight:500;}
#tot_box #tot_price{float:left;margin-left:10px;font-weight:700;color:darkblue;font-size:1.2em;}

.td_usl_menu{width:90px;}
.td_usl_type{width:170px;}
.td_usl_type1{width:170px;}
.td_usl_type2{width:170px;}
.td_usl_type3{width:170px;}
.td_usl_type4{width:170px;}
.tr_even{background:#efefef !important;}
</style>
<script type = "text/javascript" src = "https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>
<script type = "text/javascript" src = "https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

<form id="fsearch" name="fsearch" class="local_sch01 local_sch" method="get">
<input type="text" name="from_dt" placeholder="통계시작일" value="<?php echo $from_dt ?>" id="from_dt" readonly class="frm_input readonly" style="width:130px;">
<input type="text" name="to_dt" placeholder="통계종료일" value="<?php echo $to_dt ?>" id="to_dt" readonly class="frm_input readonly" style="width:130px;">
<input type="submit" class="btn_submit" value="검색">
</form>
<div id="usl_box">
    <p style="padding:10px;font-size:2em;"><?=substr($f_dt,0,10)?> ~ <?=substr($t_dt,0,10)?></p>
    <div class="tbl_head01 tbl_wrap">
        <table class="table table-bordered table-condensed">
        <caption><?php echo $g5['title']; ?> 목록</caption>
        <thead>
        <tr>
            <th scope="col">번호</th>
            <th scope="col">메뉴</th>
            <th scope="col">접속</th>
            <th scope="col">등록</th>
            <th scope="col">검색</th>
            <th scope="col">수정</th>
            <th scope="col">삭제</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $no = 0;
        for($i=0;$row=sql_fetch_array($result);$i++){
            $tr_bg = ($no % 2 == 0)?'tr_even':'';
            // print_r2($row);
            $list_num = $total_count - ($page - 1) * $rows;
            $row['num'] = $list_num - $i;
        ?>
        <tr class="<?=$tr_bg?>">
            <td class="td_no"><?=$row['num']?></td>
            <td class="td_usl_menu">
                <?=$menu_arr[$row['usl_menu_cd']]?>
            </td>
            <td class="td_usl_cnt_login">
                <?=(($row['usl_cnt_login'])?$row['usl_cnt_login']:'')?>
            </td>
            <td class="td_usl_cnt_register">
                <?=(($row['usl_cnt_register'])?$row['usl_cnt_register']:'')?>
            </td>
            <td class="td_usl_cnt_search">
                <?=(($row['usl_cnt_search'])?$row['usl_cnt_search']:'')?>
            </td>
            <td class="td_usl_cnt_modify">
                <?=(($row['usl_cnt_modify'])?$row['usl_cnt_modify']:'')?>
            </td>
            <td class="td_usl_cnt_delete">
                <?=(($row['usl_cnt_delete'])?$row['usl_cnt_delete']:'')?>
            </td>
        </tr>
        <?php }
        if ($i == 0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div><!--//.tbl_head01-->
</div><!--//#usl_box-->
<div class="btn_fixed_top">
    <a href="javascript:" class="btn btn_02 pdf_btn">PDF다운로드</a>
</div>
<script>
$(function(e){
    $("#from_dt").datepicker({ changeMonth: true, changeYear: true, closeText:'취소', dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect: function(selectedDate){$("#to_dt").datepicker('option','minDate',selectedDate);}, onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){$(this).val('');}} });

    $("#to_dt").datepicker({ changeMonth: true, changeYear: true, closeText:'취소', dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", onSelect:function(selectedDate){$("#form_dt").datepicker('option','maxDate',selectedDate);}, onClose: function(){if($(window.event.srcElement).hasClass('ui-datepicker-close')){$(this).val('');}}});
});
//pdf다운로드 버튼을 클릭하면
$('.pdf_btn').on('click',function(){
    //pdf_wrap을 canvas객체로 변환
    html2canvas($('#usl_box')[0]).then(function(canvas) {
        var doc = new jsPDF('p', 'mm', 'a4'); //jspdf객체 생성
        var imgData = canvas.toDataURL('image/png'); //캔버스를 이미지로 변환
        var imgWidth = 200;//pageHeight * 3; // 이미지 가로 210길이(mm) A4 기준
        var pageHeight = imgWidth * 1.414;//imgWidth * 1.414;  // 출력 페이지 세로 길이 계산 A4 기준
        var imgHeight = canvas.height * imgWidth / canvas.width;
        var heightLeft = imgHeight;
        var pos_x = 5;
        var pos_y = 5;

        doc.addImage(imgData, 'PNG', pos_x, pos_y, imgWidth, imgHeight); //이미지를 기반으로 pdf생성

        doc.save('<?php echo get_text($g5['title'].'_'.G5_TIME_YMD) ?>.pdf'); //pdf저장
    });
});
</script>

<?php
include_once ('./_tail.php');
?>
