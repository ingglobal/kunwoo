<?php
// 각 항목명 및 항목 설정값 정의, 형식: 항목명, required, 폭, 단위(개, 개월, 시, 분..), 설명, tr숨김, 한줄두항목여부
$items1 = array(
    "prj_idx"=>array("프로젝트선택","required",60,0,'','',0)
    ,"prs_role"=>array("역할","required",100,0,'','',0)
    ,"mb_id_worker"=>array("담당자","required",100,0,'','',0)
    ,"prs_type"=>array("일정타입","required",100,0,'','',0)
    ,"prs_percent"=>array("진행율","",70,'%','','',0)
    ,"prs_task"=>array("작업","required",300,0,'','',0)
    ,"prs_department"=>array("부서선택","required",300,0,'','',0)
    ,"prs_start_date"=>array("작업시작일","",100,0,'','',0)
    ,"prs_end_date"=>array("작업종료일","",100,0,'','',0)
    ,"prj_content"=>array("프로젝트지시사항","",70,0,'','',0)
    ,"prs_content"=>array("담당업무기록","",70,0,'','',0)
);
?>
<style>
.bt_select{height:35px;line-height:35px;}
</style>
<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="url" value="<?php echo $url ?>">
<input type="hidden" name="gant" value="<?php echo $gant ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="<?=$pre?>_idx" value="<?php echo ${$pre."_idx"} ?>">

<div class="local_desc01 local_desc" style="display:none;">
    <p>견적추가 페이지입니다.</p>
</div>

<div class="tbl_frm01 tbl_wrap">
	<table>
	<caption><?php echo $g5['title']; ?></caption>
	<tbody>
    <tr style="display:"><!-- 첫줄은 무조건 출력 -->
    <?php
    // 폼 생성 (폼형태에 따른 다른 구조)
    $skips = array($pre.'_idx',$pre.'_reg_dt',$pre.'_update_dt');
    foreach($items1 as $k1 => $v1) {
        if(in_array($k1,$skips)) {continue;}
//        echo $k1.'<br>';
        //print_r2(${$pre}).'<br>';
        // 폭
        $form_width = ($k1 == 'prs_percent') ? 'width:10% !important':'width:100%';//($items1[$k1][2]) ? 'width:'.$items1[$k1][2].'px' : '';
        // 단위
        $form_unit = ($items1[$k1][3]) ? ' '.$items1[$k1][3] : '';
        // 설명
        $form_help = ($items1[$k1][4]) ? ' '.help($items1[$k1][4]) : '';
        // tr 숨김
        $form_none = ($items1[$k1][5]) ? 'display:'.$items1[$k1][5] : '';
        // 한줄 두항목
        $form_span = ($items1[$k1][6]) ? ' colspan="'.$items1[$k1][6].'"' : '';

        $item_name = $items1[$k1][0];
        // 기본적인 폼 구조 먼저 정의
        $item_form = '<input type="text" name="'.$k1.'" value="'.${$pre}[$k1].'" placeholder="'.$item_name.'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'"'.$data_readonly.' style="'.$form_width.';'.$data_bg_change.'">'.$form_unit;
		
		if(preg_match("/prj_idx$/",$k1)){
			if($w == 'u') $pj_nm = sql_fetch('SELECT prj_name FROM '.$g5['project_table'].' WHERE prj_idx = "'.${$pre}[$k1].'" ');
			$item_form = '<input type="hidden" name="'.$k1.'" id="'.$k1.'" value="'.${$pre}[$k1].'">'.PHP_EOL;
			$item_form .= '<input type="text" id="prj_name" value="'.$pj_nm['prj_name'].'" placeholder="'.$item_name.'" readonly required class="frm_input readonly required" style="width:50% !important;'.$data_bg_change.'">'.PHP_EOL;
			if($inf_grade_ok){
			$item_form .= '<a href="javascript:" link="./_win_project_select.php" class="btn btn_02 prj_select bt_select">선택</a>'.PHP_EOL;
			}
		}
        // 폼이 다른 구조를 가질 때 재정의
        else if(preg_match("/_price$/",$k1)||preg_match("/_receivable$/",$k1)||preg_match("/_percent$/",$k1)) {
            $item_form = '<input type="text" name="'.$k1.'" value="'.number_format(${$pre}[$k1]).'" placeholder="'.$item_name.'" '.$items1[$k1][1].'
                        class="frm_input '.$items1[$k1][1].'" style="'.$form_width.';">'.$form_unit;
        }
        else if(preg_match("/_memo$/",$k1)||preg_match("/_content$/",$k1)) {
			if(preg_match("/prj_content/",$k1)){
				$item_form = '<div class="" style="white-space:pre-line;">'.PHP_EOL;
				$item_form .= ${$pre}['prj_content'];
				$item_form .= '</div>'.PHP_EOL;
			}else{
				$item_form = '<textarea name="'.$k1.'" id="'.$k1.'" placeholder="'.$item_name.'"'.$data_readonly.' style="'.$data_bg_change.'">'.${$pre}[$k1].'</textarea>';
			}
        }
		else if(preg_match("/_id_worker$/",$k1)) {
			if($w == 'u') $mbinfo = sql_fetch('SELECT mb_name,mb_3 FROM '.$g5['member_table'].' WHERE mb_id = "'.${$pre}[$k1].'" ');
			$item_form = '<input type="hidden" name="'.$k1.'" id="'.$k1.'" value="'.${$pre}[$k1].'">'.PHP_EOL;
			$item_form .= '<input type="text" id="mb_name" value="'.$mbinfo['mb_name'].'" placeholder="'.$item_name.'" readonly required class="frm_input readonly required" style="width:20% !important;'.$data_bg_change.'">'.PHP_EOL;
			//$item_form .= '<input type="text" id="mb_rank" value="'.$g5['set_mb_ranks_value'][$mbinfo['mb_3']].'" placeholder="직함" readonly required class="frm_input readonly required" style="width:30% !important;">'.PHP_EOL;
			if($inf_grade_ok){
			$item_form .= '<a href="javascript:" link="./_win_worker_select.php" class="btn btn_02 wrk_select bt_select">선택</a>'.PHP_EOL;
			}
        }
        else if(preg_match("/_date$/",$k1)) {

        }
        else if(preg_match("/_dt$/",$k1)) {

        }
		else if(preg_match("/_graph_color$/",$k1)){
			
			$item_form = '<select name="prs_graph_color" id="prs_graph_color">'.PHP_EOL;
			$item_form .= '<option value="">'.$item_name.'선택</option>';
			$item_form .= $g5['set_gantt_color_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_graph_color\"]').val('".${$pre}['prs_graph_color']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
		}
		else if(preg_match("/_graph_thickness$/",$k1)){
			
			$item_form = '<select name="prs_graph_thickness" id="prs_graph_thickness">'.PHP_EOL;
			$item_form .= '<option value="">'.$item_name.'선택</option>';
			$item_form .= $g5['set_gantt_thickness_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_graph_thickness\"]').val('".${$pre}['prs_graph_thickness']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
			
		}
		else if(preg_match("/_graph_type$/",$k1)) {
			$item_form = '<select name="prs_graph_type" id="prs_graph_type">'.PHP_EOL;
			$item_form .= '<option value="">'.$item_name.'선택</option>';
			$item_form .= $g5['set_gantt_graphtype_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_graph_type\"]').val('".${$pre}['prs_graph_type']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
        }
		else if(preg_match("/prs_role/",$k1)){
			$item_form = '<select name="prs_role" id="prs_role"'.$select_disabled.'>'.PHP_EOL;
			$item_form .= '<option value="">'.$item_name.'선택</option>';
			$item_form .= $g5['set_worker_type_value_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_role\"]').val('".${$pre}['prs_role']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
		}
		else if(preg_match("/prs_type/",$k1)){
			$item_form = '<select name="prs_type" id="prs_type"'.$select_disabled.'>'.PHP_EOL;
			$item_form .= '<option value="">'.$item_name.'선택</option>';
			$item_form .= $g5['set_prs_type_value_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_type\"]').val('".${$pre}['prs_type']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
			//$item_form .= '<input type="text" id="prs_percent" value="'.${$pre}['prs_percent'].'" placeholder="진행율" class="frm_input" style="width:10% !important;"> %'.PHP_EOL;
		}
		else if(preg_match("/prs_department/",$k1)){
			$item_form = '<select name="prs_department" id="prs_department"'.$select_disabled.'>'.PHP_EOL;
			$item_form .= '<option value="">'.$item_name.'선택</option>';
			$item_form .= $g5['set_department_name_value_options'];
			$item_form .= '</select>'.PHP_EOL;
			$item_form .= '<script>'.PHP_EOL;
			$item_form .= "$('select[name=\"prs_department\"]').val('".${$pre}['prs_department']."');".PHP_EOL;
			$item_form .= '</script>'.PHP_EOL;
			//$item_form .= '<input type="text" id="prs_percent" value="'.${$pre}['prs_percent'].'" placeholder="진행율" class="frm_input" style="width:10% !important;"> %'.PHP_EOL;
		}
		
		
        // 기종별 목표 설정
        if(preg_match("/shf_target_/",$k1) && $w!='') {
            $item_shf_no = substr($k1,-1);
            $item_btn = '<a href="javascript:" shf_idx = "'.$shf['shf_idx'].'" shf_no="'.$item_shf_no.'" class="btn btn_02 btn_item_target" style="margin-left:10px;">기종별목표</a>';
        }
        else {
            $item_btn = '';
        }

        // 이전(두줄 항목)값이 2인 경우 <tr>열지 않고 td 바로 연결
        if($span_old<=1) {
			//if($k1 == 'prs_percent') continue;
            echo '<tr style="'.$form_none.'">';
        }
        ?>
            <td>
				<?='<span class="sound_only">'.$item_name.'</span>'?>
                <?=$form_help?>
                <?=$item_form?>
                <?=$item_btn?>
            </td>
            <?php
            // 현재(두줄 항목)값이 2가 아닌 경우만 </tr>닫기
            if($items1[$k1][6]<=1) {
                echo '</tr>'.PHP_EOL;
            }
            ?>
        <?php
        // 이전값 저장
        $span_old = $items1[$k1][6];
    }
    ?>
    </tr>
	<tr>
		<td>
			<label for="com_status" class="sound_only">상태</label>
			<?php //echo help("상태값은 관리자만 수정할 수 있습니다."); ?>
			<select name="<?=$pre?>_status" id="<?=$pre?>_status"
				<?php if (auth_check($auth[$sub_menu],"d",1) || (!$inf_grade_ok && !$copy)) { ?>style="background:#efefef;" onFocus='this.initialSelect=this.selectedIndex;' onChange='this.selectedIndex=this.initialSelect;'<?php } ?>>
				<?=$g5['set_prs_status_options']?>
			</select>
			<script>$('select[name="<?=$pre?>_status"]').val('<?=${$pre}[$pre.'_status']?>');</script>
		</td>
	</tr>
	</tbody>
	</table>
</div>

<div class="btn_fixed_top">
    <a href="./<?=$fname?>_list.php?<?php echo $qstr ?>" class="btn btn_02"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>

<script>
$(function() {
    $(document).on('click','.btn_item_target',function(e){
        var shf_idx = $(this).attr('shf_idx');
        var shf_no = $(this).attr('shf_no');
        // alert( shf_idx +'/'+ shf_no );
		var url = "./shift_item_goal_list.php?file_name=<?=$g5['file_name']?>&shf_idx="+shf_idx+"&shf_no="+shf_no;
		win_item_goal = window.open(url, "win_item_goal", "left=300,top=150,width=550,height=600,scrollbars=1");
        win_item_goal.focus();
    });

	<?php if($inf_grade_ok){ ?>
    $("input[name='prs_start_date'],input[name='prs_end_date']").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99" });
	<?php } ?>

    // 가격 입력 쉼표 처리
	$(document).on( 'keyup','input[name$=_price]',function(e) {
//        console.log( $(this).val() )
//		console.log( $(this).val().replace(/,/g,'') );
        if(!isNaN($(this).val().replace(/,/g,'')))
            $(this).val( thousand_comma( $(this).val().replace(/,/g,'') ) );
	});
	
	$('.prj_select').on('click',function(){
		var href = $(this).attr('link');
		var win_prj_select = window.open(href, "win_prj_select", "left=10,top=10,width=500,height=800");
		win_prj_select.focus();
		return false;
	});
	$('.wrk_select').on('click',function(){
		var href = $(this).attr('link');
		var win_wrk_select = window.open(href, "win_wrk_select", "left=10,top=10,width=500,height=800");
		win_wrk_select.focus();
		return false;
	});
});

function form01_submit(f) {

    return true;
}

</script>

<?php
include_once ('./_tail.php');
?>