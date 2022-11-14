<?php
// 호출페이지들
// /adm/v10/data_measure_real_chart.php: 차트추가하기
include_once('./_common.php');

if($member['mb_level']<4)
	alert_close('접근할 수 없는 메뉴입니다.');

// 권한이 없는 경우는 자기것만 리스트

// com_idx가 있는 경우
if(!$com_idx)
	alert_close('업체정보가 존재하지 않습니다.');
$com = get_table_meta('company','com_idx',$com_idx);

// 시작, 종료를 timestamp 값으로!!
$st_time = $st_time ?: '00:00:00';
$en_time = $en_time ?: '23:59:59';
$start = strtotime($st_date.' '.$st_time);
$end = strtotime($en_date.' '.$en_time);


$sql_common = " FROM {$g5['mms_table']} AS mms
                    LEFT JOIN {$g5['company_table']} AS com ON com.com_idx = mms.com_idx
";

$where = array();
// 디폴트 검색조건
$where[] = " mms_status NOT IN ('trash','delete') ";

// 업체조건
$where[] = " mms.com_idx = '".$_REQUEST['com_idx']."' ";


// 검색어 설정
if ($sch_word != "") {
    switch ($sch_field) {
		case ( $sch_field == 'com_idx' || $sch_field == 'imp_idx' || $sch_field == 'mms_idx' ) :
			$where[] = " $sch_field = '".trim($sch_word)."' ";
            break;
        default :
			$where[] = " $sch_field LIKE '%".trim($sch_word)."%' ";
            break;
    }
}
else
    $sch_field = 'mms_name';

// 최종 WHERE 생성
if ($where)
    $sql_search = ' WHERE '.implode(' AND ', $where);


// 정렬기준
$sql_order = " ORDER BY mms_idx DESC ";


// 테이블의 전체 레코드수
$sql = " SELECT COUNT(*) AS cnt " . $sql_common . $sql_search;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$config['cf_write_pages'] = $config['cf_mobile_pages'] = 5;

// 리스트 쿼리
$sql = "SELECT *
        " . $sql_common . $sql_search . $sql_order . "
        LIMIT $from_record, $rows
";
// echo $sql;
$result = sql_query($sql,1);

$qstr0 = 'frm='.$frm.'&file_name='.$file_name.'&com_idx='.$com_idx;
$qstr1 = '&st_date='.$st_date.'&st_time='.$st_time.'&en_date='.$en_date.'&en_time='.$en_time;
$qstr = $qstr0.$qstr1.'&sch_field='.$sch_field.'&sch_word='.urlencode($sch_word);

$g5['title'] = $com['com_name'].' 그래프 추가';
include_once('./_head.sub.php');
?>
<style>
    .div_title {position:relative;height:30px;line-height:30px;background:#eee;padding-left:5px;}
    .div_title span b {font-weight:normal;font-size:0.9em;color:#818181;}
    .div_title span b:after {content:':';margin-right:3px;}
    .div_title .spsn_mms_name {font-size:1.2em;}
    .div_title .spsn_mms_name:before {content:'\2713';margin-right:5px;}
    .div_title .spsn_mms_group {position:absolute;top:1px;right:10px;}
    .btn_mesaure, .btn_product {cursor:pointer;margin-right:10px;}
    .btn_mesaure:hover, .btn_product:hover {color:red;}
    #spinner {display:none;}
    .tbl_head01 tbody td {
        text-align:left;
        border-left:none;
        border-right:none;
    }
    .div_measure, .div_product {padding:10px 0 0;}
    .div_measure span {display:inline-block;}
    #scp_list_find {
        border: none;
    }
</style>

<div id="sch_target_frm" class="new_win scp_new_win">
    <h1 id="g5_title"><?php echo $g5['title'];?></h1>

    <form name="ftarget" method="get">
    <input type="hidden" name="frm" value="<?php echo $_GET['frm']; ?>">
    <input type="hidden" name="file_name" value="<?php echo $_REQUEST['file_name']; ?>">
    <input type="hidden" name="com_idx" value="<?php echo $_REQUEST['com_idx']; ?>">
    <input type="hidden" name="st_date" value="<?php echo $_REQUEST['st_date']; ?>">
    <input type="hidden" name="st_time" value="<?php echo $_REQUEST['st_time']; ?>">
    <input type="hidden" name="en_date" value="<?php echo $_REQUEST['en_date']; ?>">
    <input type="hidden" name="en_time" value="<?php echo $_REQUEST['en_time']; ?>">

    <div id="scp_list_find">
        <select name="sch_field" id="sch_field">
            <option value="mms_name">설비명</option>
            <option value="mms_idx">설비번호</option>
        </select>
        <script>$('select[name=sch_field]').val('<?php echo $sch_field?>').attr('selected','selected')</script>
        <input type="text" name="sch_word" id="sch_word" value="<?php echo get_text($sch_word); ?>" class="frm_input required" required style="width:50%;">
        <input type="submit" value="검색" class="btn_frmline">
        <a href="<?php echo $_SERVER['SCRIPT_NAME']?>?file_name=<?=$file_name?>&<?=$qstr0.$qstr1?>" class="btn btn_b10">검색취소</a>
    </div>
    
    <div class="local_desc01 local_desc" style="display:none;">
        <p>그래프선택 항목의 측정타입을 선택하시면 그래프가 추가됩니다.</p>
    </div>

    <div class="tbl_head01 tbl_wrap new_win_con">
        <i class="fa fa-spin fa-spinner" id="spinner" style="position:absolute;top:280px;left:46%;font-size:4em;"></i>
        <table>
        <caption>검색결과</caption>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $row['mta'] = get_meta('mms',$row['mms_idx']);  // 측정값 레이블형식: [dta_type_label-1-1] => 좌측온도
            $row['group'] = get_table_meta('mms_group','mmg_idx',$row['mmg_idx']);
            // print_r2($row);

            // 해당 기간 내 각 측정값 추출
            $sql1 = "SELECT dta_type, dta_no, COUNT(dta_idx) AS dta_count
                    FROM {$g5['data_measure_table']}
                    WHERE mms_idx = '".$row['mms_idx']."' AND dta_status = '0'
                        AND dta_dt >= '".$start."'
                        AND dta_dt <= '".$end."'
                    GROUP BY dta_type, dta_no
                    ORDER BY dta_type, dta_no
            ";
            // echo $sql1.'<br>';
            $rs1 = sql_query($sql1,1);
            for($j=0;$row1=sql_fetch_array($rs1);$j++){
                // 레이블값
                $row1['dta_label'] = $row['mta']['dta_type_label-'.$row1['dta_type'].'-'.$row1['dta_no']] ?:
                                        $g5['set_data_type_value'][$row1['dta_type']].$row1['dta_no'];
                // 차트(그래프) 항목 배열
                $row['charts'][] = '<span class="btn_mesaure" mms_idx="'.$row['mms_idx'].'" '
                                    .'mms_data_url="'.$row['mms_data_url'].'" '
                                    .'dta_type="'.$row1['dta_type'].'" dta_no="'.$row1['dta_no'].'" '
                                    .'graph_name="'.$row1['dta_label'].'" '
                                    .'>'.$row1['dta_label']
                                    .' ('.number_format($row1['dta_count']).')</span>';
            }

            // 생산량
            $sql1 = "SELECT SUM(dta_value) AS dta_value
                        , SUM( IF(dta_defect=0, dta_value, 0) ) AS dta_good 
                        , SUM( IF(dta_defect=1, dta_value, 0) ) AS dta_defect 
                    FROM {$g5['data_output_table']}
                    WHERE mms_idx = '".$row['mms_idx']."' AND dta_status = '0'
                        AND dta_dt >= '".$start."'
                        AND dta_dt <= '".$end."'
            ";
            // echo $sql1.'<br>';
            $rs1 = sql_fetch($sql1,1);
            $row['product_all'] = number_format($rs1['dta_value']);
            $row['product_good'] = number_format($rs1['dta_good']);
            $row['product_defect'] = number_format($rs1['dta_defect']);
            if($row['product_defect']) {
                $row['products'] = $row['product_all'].' (정상: '.$row['product_good'].', 불량: '.$row['product_defect'].')';
            }
       ?>
        <tr>
            <td>
                <div class="div_title">
                    <span class="spsn_mms_name"><?php echo $row['mms_name']; ?></span>
                    <span class="spsn_mms_model">(<?php echo $row['mms_model']; ?>)</span>
                    <span class="spsn_mms_group"><b>배치</b><?php echo $row['group']['mmg_name']; ?></span>
                </div>
                <div class="div_product">
                    <span class="btn_product" mms_idx="<?=$row['mms_idx']?>" mms_data_url="<?=$row['mms_data_url']?>">
                        <b>생산:</b> <?=$row['products']?>
                    </span>
                </div>
                <div class="div_measure">
                    <?=(is_array($row['charts']))?implode(" ",$row['charts']):'그래프 없음'?>
                </div>
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
    </form>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>

    <div class="win_btn ">
        <button type="button" onclick="window.close();" class="btn btn_close">창닫기</button>
        <a href="javascript:" class="btn btn_test">테스트</a>
    </div>

    <div class="btn_fixed_top">
        <a href="javascript:window.close();" id="member_add" class="btn btn_02">창닫기</a>
        <?php if($member['mb_level']>6) { ?>
        <a href="./company_select.popup.php?file_name=<?=$g5['file_name']?>" id="btn_company" class="btn btn_03">업체검색</a>
        <?php } ?>
    </div>

</div>

<script>
console.log( $("#chart1", opener.document).attr("aa") );
// var obj =  $.parseJSON( $("#chart1", opener.document).attr("aa") );
var obj =  JSON.parse( $("#chart1", opener.document).attr("aa") );
console.log( obj );
console.log( obj.a1 );
console.log( obj.a2 );
console.log( '---------------' );

if( $("#chart1", opener.document).attr("tests") != undefined ) {
    var obj1 =  JSON.parse( $("#chart1", opener.document).attr("tests") );
    console.log( obj1 );
    console.log( obj1[0].dta_data_url );
}

// 아래 전역 변수들를 만들어 두고 바꿔가면서 applyGraphs()함수를 실행하여 graphs 배열변수를 생성(추가)한다.
var dta_data_url;
var dta_json_file;
var dta_group;  // mea, product, run, error
var mms_idx;
var dta_type;  // 
var dta_no;    // 
var shf_no = 0;
var dta_mmi_no = 0;
var dta_defect = "0,1";
var dta_defect_type = 0;
var dta_code = "";
var graph_type = "spline";
var graph_line = "solid";
var graph_name = "Graph";
// console.log(window.opener.graphs);

$(function() {
    $(".btn_test").click(function() {
        // window.opener.tests[0] = {
            // dta_data_url: "bogwang.epcs.co.kr/device/json",
            // dta_no: 0
        // };
        // window.opener.tests[1] = {
            // dta_data_url: "bogwang.epcs.co.kr/device/json1",
            // dta_no: 1
        // };

var tests =[];
tests[0] = {
    dta_data_url: "bogwang.epcs.co.kr/device/json",
    dta_no: 0,
};
tests[1] = {
    dta_data_url: "bogwang.epcs.co.kr/device/json",
    dta_no: 1,
};
$("#chart1", opener.document).attr("tests",JSON.stringify(tests) );

        
        window.close();
    });


    $("#btn_company").click(function() {
        var href = $(this).attr("href");
        winCompany = window.open(href, "winCompany", "left=70,top=70,width=520,height=600,scrollbars=1");
        winCompany.focus();
        return false;
    });

    // 측정그래프 추가
    $('.btn_mesaure').click(function(e){
        e.preventDefault();
        dta_group = "mea";  // mea, product, run, error
        dta_json_file = "measure";
        dta_data_url = $(this).attr('mms_data_url') || "<?=$g5['set_data_url']?>";
        mms_idx = $(this).attr('mms_idx');
        dta_type = $(this).attr('dta_type');  // 
        dta_no = $(this).attr('dta_no');    // 
        shf_no = 0;
        dta_mmi_no = 0;
        dta_defect = "0,1";
        dta_defect_type = 0;
        dta_code = "";
        graph_name = encodeURIComponent($(this).attr('graph_name'));
        $('#spinner').show();

        <?php
        // 측정 그래프 (실측)
        if($file_name=='data_measure_real_chart'||$file_name=='dashboard_graph_multi_real') {
        ?>
            setTimeout(function(e){
                $("input[name=mms_idx]", opener.document).val( mms_idx );
                $("input[name=dta_type]", opener.document).val( dta_type );
                $("input[name=dta_no]", opener.document).val( dta_no );
                $("#fsearch button[type=submit]", opener.document).trigger('click');
                window.close();
            },100);
        <?php
        }
        // 측정 그래프 (정주기)
        else if($file_name=='data_measure_chart'||$file_name=='dashboard_graph_multi'
                ||$file_name=='dashboard_graph_multi2'
                ||$file_name=='iframe.graph'||$file_name=='iframe.graph5') {
        ?>
            var graphs = window.opener.graphs;
            console.log(graphs);
            var chr_idx = graphs.length || 0;
            var graph_id1 = getGraphId(dta_json_file,dta_group,mms_idx,dta_type,dta_no,shf_no,dta_mmi_no,dta_defect,dta_defect_type,dta_code);

// var tests = window.opener.graphs;
// console.log(tests);
// console.log(tests[0]);
// // console.log(tests[0].dta_data_url);
// console.log('------------------');

            for(i=0;i<graphs.length;i++) {
                console.log(i);
                console.log(graphs[i].dta_data_url);
                if( graph_id1 == graphs[i].graph_id) {
                    chr_idx = i;
                }
            }
            console.log(chr_idx);
            applyGraphs(chr_idx);

            $("#fsearch button[type=submit]", opener.document).trigger('click');
            setTimeout(function(e){
                // window.close();
            }, 400);
        <?php
        }
        ?>
    });

    // 생산그래프 추가 (전제생산, 목표 등 여러 그래프를 함께 입력함)
    $('.btn_product').click(function(e){
        e.preventDefault();
        $('#spinner').show();

        <?php
        if($file_name=='data_measure_chart'||$file_name=='dashboard_graph_multi'
                ||$file_name=='dashboard_graph_multi2'
                ||$file_name=='iframe.graph'||$file_name=='iframe.graph5') {
        ?>

            // 전제 생산량 (합격+불량) --------------------------------
            dta_group = "product";  // mea, product, run, error
            dta_json_file = "output";
            dta_data_url = $(this).attr('mms_data_url') || "<?=$g5['set_data_url']?>";
            mms_idx = $(this).attr('mms_idx');
            dta_type = 0;  // 
            dta_no = 0;    // 
            shf_no = 0;
            dta_mmi_no = 0;
            dta_defect = "0,1"; // 생산전체(합격, 불량 둘다)
            dta_defect_type = 0;
            dta_code = "";
            graph_type = "column";  // column graph for output
            graph_line = "solid";
            graph_name = encodeURIComponent("생산");

            var graphs = window.opener.graphs;
            var chr_idx = graphs.length || 0;
            var graph_id1 = getGraphId(dta_json_file,dta_group,mms_idx,dta_type,dta_no,shf_no,dta_mmi_no,dta_defect,dta_defect_type,dta_code);
            // console.log(window.opener.graphs);

            for(i=0;i<graphs.length;i++) {
                if( graph_id1 == graphs[i].graph_id)
                    chr_idx = i;
            }
            console.log(chr_idx);
            applyGraphs(chr_idx);

            // 생산량 (불량) --------------------------------
            dta_group = "product";  // mea, product, run, error
            dta_json_file = "output";
            dta_data_url = $(this).attr('mms_data_url') || "<?=$g5['set_data_url']?>";
            mms_idx = $(this).attr('mms_idx');
            dta_type = 0;  // 
            dta_no = 0;    // 
            shf_no = 0;
            dta_mmi_no = 0;
            dta_defect = "1"; // 불량만
            dta_defect_type = 0; // 불량타입은 일단 모두
            dta_code = "";
            graph_type = "column";  // column graph for output
            graph_line = "solid";
            graph_name = encodeURIComponent("불량");

            var graphs = window.opener.graphs;
            var chr_idx = graphs.length || 0;
            var graph_id1 = getGraphId(dta_json_file,dta_group,mms_idx,dta_type,dta_no,shf_no,dta_mmi_no,dta_defect,dta_defect_type,dta_code);
            // console.log(window.opener.graphs);

            for(i=0;i<graphs.length;i++) {
                if( graph_id1 == graphs[i].graph_id)
                    chr_idx = i;
            }
            console.log(chr_idx);
            applyGraphs(chr_idx);

            // 목표 ---------------------------------------------
            // 중복값은 선언할 필요 없음
            dta_json_file = "output.target";
            dta_data_url = "bogwang.epcs.co.kr/adm/v10/ajax";
            graph_type = "spline";
            graph_line = "shortdot";    // 점선
            graph_name = encodeURIComponent("목표");

            var graphs = window.opener.graphs;
            var chr_idx = graphs.length || 0;
            var graph_id1 = getGraphId(dta_json_file,dta_group,mms_idx,dta_type,dta_no,shf_no,dta_mmi_no,dta_defect,dta_defect_type,dta_code);
            // console.log(window.opener.graphs);

            for(i=0;i<graphs.length;i++) {
                if( graph_id1 == graphs[i].graph_id)
                    chr_idx = i;
            }
            console.log(chr_idx);
            applyGraphs(chr_idx);


            // 가동시간 ---------------------------------------------
            dta_group = "run";  // mea, product, run, error
            dta_json_file = "run";
            dta_data_url = $(this).attr('mms_data_url') || "<?=$g5['set_data_url']?>";
            graph_type = "spline";
            graph_line = "solid";    // 실선
            graph_name = encodeURIComponent("가동시간(분)");

            var graphs = window.opener.graphs;
            var chr_idx = graphs.length || 0;
            var graph_id1 = getGraphId(dta_json_file,dta_group,mms_idx,dta_type,dta_no,shf_no,dta_mmi_no,dta_defect,dta_defect_type,dta_code);
            // console.log(window.opener.graphs);

            for(i=0;i<graphs.length;i++) {
                if( graph_id1 == graphs[i].graph_id)
                    chr_idx = i;
            }
            console.log(chr_idx);
            applyGraphs(chr_idx);



            // 전체 그래프 호출
            $("#fsearch button[type=submit]", opener.document).trigger('click');
            setTimeout(function(e){
                window.close();
            }, 400);
        <?php
        }
        ?>
    });

});

// graphs 배열 변수 업데이트
function applyGraphs(idx) {
    var graph_id1 = getGraphId(dta_json_file,dta_group,mms_idx,dta_type,dta_no,shf_no,dta_mmi_no,dta_defect,dta_defect_type,dta_code);
    window.opener.graphs[idx] = {
        dta_data_url: dta_data_url,
        dta_json_file: dta_json_file,
        dta_group: dta_group,
        mms_idx: mms_idx,
        dta_type: dta_type,
        dta_no: dta_no,
        shf_no: shf_no,
        dta_mmi_no: dta_mmi_no,
        dta_defect: dta_defect,
        dta_defect_type: dta_defect_type, // 1,2,3,4...
        dta_code: dta_code,    // only if err, pre
        graph_type: graph_type,
        graph_line: graph_line,
        graph_name: graph_name,
        graph_id: graph_id1
    };
    console.log(window.opener.graphs);
}

</script>

<?php
include_once('./_tail.sub.php');
?>