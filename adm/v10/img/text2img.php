<?php
include_once('./_common.php');
define('EMPTY_STRING', '');

//이미지 타입 설정
header('Content-type: image/png');

//이미지 가로 세로의 크기 (GET으로 받아옴)
$im = imagecreatetruecolor($_GET['width'], $_GET['height']);


//배경을 투명하게 하기 위한 설정
imagealphablending($im,false); //투명배경을 위해 필요한 함수
imagesavealpha($im,true); //투명배경을 위해 필요한 함수
$trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127); //투명배경을 위해 필요한 함수
imagefill($im, 0, 0, $trans_colour); //투명배경을 위해 필요한 함수
//배경투명색
$trans = imagecolorallocatealpha($im, 255, 255, 255, 127);
// 이미지 텍스트의 배경색상을 정의 RGB
$white = imagecolorallocate($im, 255, 255, 255);
$gray = imagecolorallocate($im, 128, 128, 128);
$magenta = imagecolorallocate($im, 255, 0, 255);
$black = imagecolorallocate($im, 0, 0, 0);
$violet = imagecolorallocate($im, 157, 60, 255);
$blue = imagecolorallocate($im, 68, 162, 255);
$gray = imagecolorallocate($im, 80, 80, 80);
$red = imagecolorallocate($im, 234, 0, 0);
$green = imagecolorallocate($im, 148, 203, 49);

if($_GET['test'] == 'test')
	$bgcolor = $black;
else
	$bgcolor = $trans;
// 텍스트 이미지 기울기 설정 (마지막 인수는 이미지의 배경색을 설정하는 변수이다. 위 변수 $trans, $white, $gray...등으로 설정)
imagefilledrectangle($im, 0, 0, $_GET['width'], $_GET['height'], $bgcolor);

// 폰트의 경로를 작성합니다.(네이버 나눔폰트) 아래의 경로는 환경에 맞게 수정해 줘야 한다.
$font_gothic = G5_BPWIDGET_CSS_URL.'/font/NanumGothic.ttf';
$font_pen = G5_BPWIDGET_CSS_URL.'/font/NanumPen.ttf';

// 폰트 설정
if($_GET['font'] == 'pen'){
	$font = $font_pen;
}else{
	$font = $font_gothic;
}

$text = foxy_utf8_to_nce($_GET['txt']);


//$int_x = $_GET['left'];
if($_GET['left'] == 'center' && !$_GET['right']){
	$text_box = imagettfbbox($_GET['font_size'],0,$font,$_GET['txt']);
	$text_width = $text_box[2]-$text_box[0];
	$int_x = ($_GET['width'] / 2) - ($text_width / 2);
}else if(!$_GET['left'] && $_GET['right']){
	$text_box = imagettfbbox($_GET['font_size'],0,$font,$_GET['txt']);
	$text_width = $text_box[2]-$text_box[0];
	$int_x = $_GET['width'] - ($text_width + $_GET['right']);
}else{
	$int_x = ($_GET['left']) ? $_GET['left'] : 1;
}
$int_y = ($_GET['height'] / 2) + ($_GET['font_size'] / 2);

$int_y = ($_GET['height'] / 2) + ($_GET['font_size'] / 2);


// get으로 받아온 색상값에 따라서 텍스트 이미지 생상 지정
if($_GET['color'] == 'black'){
	imagettftext($im, $_GET['font_size'], 0, $int_x , $int_y, $black, $font, $text);
}elseif($_GET['color'] == 'blue'){
	imagettftext($im, $_GET['font_size'], 0, $int_x , $int_y, $blue, $font, $text);
}elseif($_GET['color'] == 'gray'){
	imagettftext($im, $_GET['font_size'], 0, $int_x , $int_y, $gray, $font, $text);
}elseif($_GET['color'] == 'red'){
	imagettftext($im, $_GET['font_size'], 0, $int_x , $int_y, $red, $font, $text);
}elseif($_GET['color'] == 'green'){
	imagettftext($im, $_GET['font_size'], 0, $int_x , $int_y, $green, $font, $text);	
}elseif($_GET['color'] == 'violet'){
	imagettftext($im, $_GET['font_size'], 0, $int_x , $int_y, $violet, $font, $text);	
}

// imagepng()함수가 imagejpeg() 함수보다 텍스트가 더 깨끗하게 표현됨
imagepng($im);
imagedestroy($im);

// imagettftext 함수에서 한글 UTF-8 방식의 오류를 보정한다.
function foxy_utf8_to_nce($utf = EMPTY_STRING){
  if($utf == EMPTY_STRING) return($utf);

  $max_count = 5; // flag-bits in $max_mark ( 1111 1000 == 5 times 1)
  $max_mark = 248; // marker for a (theoretical ;-)) 5-byte-char and mask for a 4-byte-char;

  $html = EMPTY_STRING;
  for($str_pos = 0; $str_pos < strlen($utf); $str_pos++) {
    $old_chr = $utf{$str_pos};
    $old_val = ord( $utf{$str_pos} );
    $new_val = 0;

    $utf8_marker = 0;

    // skip non-utf-8-chars
    if( $old_val > 127 ) {
      $mark = $max_mark;
      for($byte_ctr = $max_count; $byte_ctr > 2; $byte_ctr--) {
        // actual byte is utf-8-marker?
        if( ( $old_val & $mark  ) == ( ($mark << 1) & 255 ) ) {
          $utf8_marker = $byte_ctr - 1;
          break;
        }
        $mark = ($mark << 1) & 255;
      }
    }

    // marker found: collect following bytes
    if($utf8_marker > 1 and isset( $utf{$str_pos + 1} ) ) {
      $str_off = 0;
      $new_val = $old_val & (127 >> $utf8_marker);
      for($byte_ctr = $utf8_marker; $byte_ctr > 1; $byte_ctr--) {

        // check if following chars are UTF8 additional data blocks
        // UTF8 and ord() > 127
        if( (ord($utf{$str_pos + 1}) & 192) == 128 ) {
          $new_val = $new_val << 6;
          $str_off++;
          // no need for Addition, bitwise OR is sufficient
          // 63: more UTF8-bytes; 0011 1111
          $new_val = $new_val | ( ord( $utf{$str_pos + $str_off} ) & 63 );
        }
        // no UTF8, but ord() > 127
        // nevertheless convert first char to NCE
        else {
          $new_val = $old_val;
        }
      }
      // build NCE-Code
      $html .= '&#'.$new_val.';';
      // Skip additional UTF-8-Bytes
      $str_pos = $str_pos + $str_off;
    }
    else {
      $html .= chr($old_val);
      $new_val = $old_val;
    }
  }
  return($html);
}
?>