<?php
$sub_menu = "915130";
include_once('./_common.php');

auth_check($auth[$sub_menu],'w');

$pre = 'bom';
${$pre} = get_table_meta('bom', $pre.'_idx', ${$pre."_idx"});
if (!${$pre}[$pre.'_idx'])
    alert('존재하지 않는 자료입니다.');
${$pre}['com_customer'] = get_table('company','com_idx',${$pre}['com_idx_customer']);
// print_r3(${$pre});

// BOM structure
/*
$sql = "SELECT bom.bom_idx, com_idx_customer, bom.bom_name, bom_part_no, bom_price, bom_status
            , bit1.bit_idx, bit1.bom_idx_child, bit1.bit_reply, bit1.bit_count
            , COUNT(bit2.bit_idx) AS group_count
        FROM {$g5['bom_item_table']} AS bit1
            JOIN {$g5['bom_item_table']} AS bit2
            LEFT JOIN {$g5['bom_table']} AS bom ON bom.bom_idx = bit2.bom_idx_child
        WHERE bit1.bom_idx = '".$bom_idx."' AND bit2.bom_idx = '".$bom_idx."'
            AND bit1.bit_num = bit2.bit_num
            AND bit2.bit_reply LIKE CONCAT(bit1.bit_reply,'%')
        GROUP BY bit1.bit_num, bit1.bit_reply
        ORDER BY bit1.bit_num DESC, bit1.bit_reply
";
*/
$sql = " SELECT bom_name, bom_part_no, bom_price, bom_status, com_idx_provider
                , bit_idx, bit.bom_idx, bit.bom_idx_child, bit_num, bit_reply
                , bit_count
        FROM {$g5['bom_item_table']} AS bit
            LEFT JOIN {$g5['bom_table']} AS bom ON bit.bom_idx_child = bom.bom_idx
        WHERE bit.bom_idx = '{$bom_idx}'
            AND bom_status = 'ok'
        ORDER BY bit_num DESC, bit_reply
";

// print_r3($sql);
$result = sql_query($sql,1);
$total_count = sql_num_rows($result);


$g5['title'] = 'BOM 구성';
include_once ('./_head.php');

add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_JS_URL.'/nestable/jquery.nestable.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.G5_USER_ADMIN_CSS_URL.'/nestable.css">', 0);
?>
<style>
.tbl_frm01:after {display:block;visibility:hidden;clear:both;content:'';}
.div_wrapper {display:inline-block;background:#1e2531;width:49.5%;}
.dd {min-width: 100%;}
.div_title {background:#000204;padding:15px;}
.div_title .bom_title {color:#00ffe7;font-weight:bold;}
.bom_detail:before {content:"(";margin-left:10px;}
.bom_detail:after {content:")";}
#del-item {margin-top:-6px;}
#nestable3 {padding:10px 20px;min-height:616px;}
.div_bom_list {min-height:600px;padding:10px 20px;}
#frame_bom_list {width:100%;height:600px;}
.empty_table {background:#1e2531;}
</style>

<form name="form01" id="form01" action="./<?=$g5['file_name']?>_update.php" onsubmit="return form01_submit(this);" method="post" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="bom_idx" value="<?php echo $bom_idx ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">

<div class="local_desc01 local_desc" style="display:no ne;">
    <p>오른편에서 품명을 검색하고 입력한 다음 BOM을 구성하세요.</p>
    <p>구성이 끝났으면 상단 [확인] 버튼을 클릭하여 저장하세요.</p>
</div>

<div class="tbl_frm01">
    <div class="div_wrapper div_left">
        <div class="div_title">
            <span class="bom_title"><?=$bom['bom_name']?></span>의 BOM 설정
            <span class="bom_detail"><?=$bom['bom_part_no']?>, <?=$bom['com_customer']['com_name']?>, <?=number_format($bom['bom_price'])?>원</span>
            <a href="javascript:" id="del-item" class="btn_03 btn float_right"> 초기화</a>
        </div>

        <div class="dd" id="nestable3">
        <ol class="dd-list">
        <?php
        $depth = 0;
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            $row['idx'] = $i+1;
            $row['bit_depth'] = strlen($row['bit_reply']);
            $row['com_customer'] = get_table('company','com_idx',$row['com_idx_customer']);
            $row['bit_count'] = $row['bit_count'] ?: 1;
            // print_r2($row);

            if($row['bit_depth']<$depth) {
                for($j=0;$j<$depth-$row['bit_depth'];$j++) {
                    echo '</ol></li>'.PHP_EOL;
                }
            }

            echo '
            <li class="dd-item dd3-item" data-id="'.$row['idx'].'" data-depth="'.$row['bit_depth'].'" data-bit_idx="'.$row['bit_idx'].'">
                <div class="dd-handle dd3-handle">Drag</div>
                <div class="dd3-content" bom_idx_child="'.$row['bom_idx_child'].'">
                    <span class="bom_name">'.cut_str($row['bom_name'],11).'</span>
                    <div class="add_items">
                        <span class="bom_part_no">'.$row['bom_part_no'].'</span>
                        <span class="bom_company">'.cut_str($row['com_customer']['com_name'],16,'..').'</span>
                        <span class="bom_price">'.number_format($row['bom_price']).'원</span>
                        <span class="span_count"><span class="bit_count">'.$row['bit_count'].'</span>개</span>
                        <img src="https://icongr.am/clarity/times.svg?size=30&color=444444" class="btn_remove" title="삭제">
                    </div>
                </div>
            '.PHP_EOL;

            if( $row['group_count'] > 1 )
                echo '<ol class="dd-list">'.PHP_EOL;
            else
                echo '</li>'.PHP_EOL;

            $depth = $row['bit_depth'];
        }
        if( $i == 0 ) {
            echo '<li class="empty_table">구성품이 없습니다.</li>';
        }
        else {
            for($j=0;$j<$depth;$j++) {
                echo '</ol></li>'.PHP_EOL;
            }
        }
        ?>
        </ol>
        </div>

        <div style="clear:both;"></div>
        <div class="btn_control">
            <!-- ========================================= -->
            <div class="div_serialize" style="display:none;">
            <p><strong>Serialised Output (per list)</strong></p>
            <textarea class="navi_result" id="nestable3-output" name="serialized"></textarea>
            </div>
            <!-- ========================================= -->
        </div>


    </div>
    <div class="div_wrapper div_right float_right">
        <div class="div_title">
            <span class="bom_title2">제품(자재) 리스트</span>
        </div>
        <div class="div_bom_list">
            <iframe id="frame_bom_list" src="./bom_structure_list.php?file_name=<?=$g5['file_name']?>" frameborder="0" scrolling="auto"></iframe>
        </div>

    </div>
</div>

<div class="btn_fixed_top">
    <a href="./bom_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" value="확인" class="btn_submit btn" accesskey='s'>
</div>
</form>


<script src="<?=G5_USER_ADMIN_JS_URL?>/nestable/jquery.nestable.js"></script>
<!--<script src="//cdnjs.cloudflare.com/ajax/libs/nestable2/1.5.0/jquery.nestable.min.js"></script>-->
<script>
listNodeName = 'ol';
itemNodeName = 'li';
listClass = 'dd-list';
contentClass = 'dd3-content';
includeContent = false;
var naviLastId = <?=$total_count?>;    // 항목수

$(document).ready(function() {
    // activate Nestable for navi
    $('#nestable3').nestable({
        group: 10,
        contentCallback: function(item) {
            var content = item.content || '' ? item.content : item.id;
            content += '';

            return content;
        },
        maxDepth: 5,
        itemClass:'dd-item dd3-item',
        handleClass:'dd-handle dd3-handle',
        contentNodeName: 'div',
        contentClass: 'dd3-content',
        callback: function(l, e, p) {
            printOutput();
        },
        itemRenderer: function(item_attrs, content, children, options, item) {
            var item_attrs_string = $.map(item_attrs, function(value, key) {
                return ' ' + key + '="' + value + '"';
            }).join(' ');

            var html = '<' + options.itemNodeName + item_attrs_string + '>';
            html += '<' + options.handleNodeName + ' class="' + options.handleClass + '">';
            html += '</' + options.handleNodeName + '>';
            html += content;
            html += '</' + options.contentNodeName + '>';
            html += children;
            html += '</' + options.itemNodeName + '>';

            return html;
        }
    });

    // 초기값 입력
    printOutput();

});


// 변경 내용 출력
var printOutput = function() {
    // output initial serialised data
    var result_array = list_update( $("#nestable3").find(listNodeName).first() );
    resultOutput(result_array,'nestable3-output')
};
// 변경 내용 출력 함수
var resultOutput = function(arr, obj) {
    result_text = ((window.JSON)) ? window.JSON.stringify(arr) : 'JSON browser support required for this demo.';
    $('#'+obj).val( result_text );
};

// Serialized output 내용 업데이트 함수
function list_update(obj) {
    var array = [],
        items = obj.children(itemNodeName);

    items.each(function() {
        var li = $(this),
            item = $.extend({}, li.data()),
            sub = li.children(listNodeName);
        if(!item.id) return true;   
        // depth 속성 추가
        var li_depth = li.parents('.dd-list').length - 1;
        item.depth = li_depth;
        item.bom_name = li.find('.'+contentClass).first().find('span.bom_name').text();
        item.bom_idx_child = li.find('.'+contentClass).first().attr('bom_idx_child');
        item.bit_count = li.find('.'+contentClass).first().find('span.bit_count').text();
        // item.bit_2 = li.find('.'+contentClass).first().attr('bit_2');

        if (includeContent) {
            var content = li.find('.' + contentClass).html();

            if (content) {
                item.content = content;
            }
        }

        if (sub.length) {
            item.children = list_update(sub);
        }
        array.push(item);
    });
    return array;
}
</script>



<script>
// 내용 수정
$(document).on('click','.dd3-content',function(e){
    e.stopPropagation();
    // 안에 input 박스가 존재하면 input 벗겨내고
    if( $(this).find('input').length ) {
        //console.log('있다.');
        var this_value = $(this).find('input').val();
        $(this).find('span').html( this_value );

        printOutput();
    }
    // 아니면 input 박스 추가해서 내용 변경할 수 있도록 한다.
    else {
        //console.log('없다.');
        var this_value = $(this).find('span.bit_count').text();
        $(this).find('span.bit_count').html('<input type="" name="" value="'+this_value+'" class="dd3-content-input">');
        $(this).find('span input').select().focus();
    }

});

// 내용수정 input 클릭하면 div 에 영향을 주지 않게 stopPropagation
$(document).on('click','.dd3-content input',function(e){
    e.stopPropagation();
});
// input박스 Blur or keyup 되면 현재값 입력
$(document).on('blur','.dd3-content input',function(e){
    e.stopPropagation();
    var this_value = $(this).val();
    $(this).closest('span').html( this_value );
    printOutput();
});

// 항목 삭제 클릭
$(document).on('click','.dd3-content .add_items img',function(e){
    e.preventDefault();
    e.stopPropagation();
    var this_id = $(this).closest('.dd-item').attr('data-id');
//    var this_bit_idx = $(this).closest('.dd-item').attr('data-bit_idx');
    var this_subject = $(this).closest('.dd3-content').find('span:first').text();

    if( $(this).hasClass('btn_remove') ) {
        // if(confirm('해당 항목을 삭제하시겠습니까?\n수정하신 후 [확인] 버튼을 클릭해 주셔야 최종 적용됩니다.')) {
            $('#nestable3').nestable('remove', this_id);
        // }
        printOutput();
    }

});


// 초기화 클릭
$(document).on('click','#del-item',function(e){
    e.preventDefault();
    if(confirm('전체 항목을 삭제하시겠습니까?\n수정하신 후 [확인] 버튼을 클릭해 주셔야 최종 적용됩니다.')) {
        $('.dd-item').each(function(i,v){
            $('#nestable3').nestable('remove', $(this).attr('data-id'));
        });
        naviLastId = 0; // id 초기화
        printOutput();
    }
});

// 항목추가 함수
function add_item(bom_idx, bom_name, bom_part_no, com_name, bom_price) {

    var li_dom ='<div class="dd3-content" bom_idx_child="'+bom_idx+'">'
                +'  <span class="bom_name">'+bom_name+'</span>'
                +'  <div class="add_items">'
                +'      <span class="bom_part_no">'+bom_part_no+'</span>'
                +'      <span class="bom_company">'+com_name+'</span>'
                +'      <span class="bom_price">'+bom_price+'원</span>'
                +'      <span class="span_count"><span class="bit_count">1</span>개</span>'
                +'      <img src="https://icongr.am/clarity/times.svg?size=30&color=444444" class="btn_remove" title="삭제">'
                +'  </div>';
                +'</div>';

    var newItem = {
        "id": ++naviLastId,
        "content": li_dom
    };
    $('#nestable3').nestable('add', newItem);
    printOutput();

    // 항목이 한개 이상이면 empty_table 제거
    if( $('.dd-item').length > 0 ) {
        $('.empty_table').remove();
    }
}

// 폼 validateion
function form01_submit(f) {

    // 폼에 input 박스가 한개라도 있으면 안 된다.
    // input 처리를 하고 return false
    if( $('.dd3-content input').length ) {
        //alert('수정하시던 작업이 있습니다.\n작업을 마무리해 주세요.');
        var this_value = $('.dd3-content input').val();
        $('.dd3-content input').closest('span').html( this_value );
        printOutput();
        return false;
    }

    // // 적용할 항목이 한개 이상이어야 한다.
    // if( $('.dd-item').length <= 0 ) {
    //     alert('구성품을 추가해 주세요.');
    //     return false;
    // }

    return true;
}

</script>

<script>
// 숫자만 입력
function chk_Number(object){
    $(object).keyup(function(){
        $(this).val($(this).val().replace(/[^0-9|-]/g,""));
    });
}
</script>


<?php
include_once ('./_tail.php');
?>
