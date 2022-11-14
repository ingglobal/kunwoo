<?php
include_once('./_common.php');

// clean the output buffer
ob_end_clean();

//-- 파일경로 및 다운로드할 파일명 두개 변수 필요함
//-- http://file1.ddmc.kr/download.php?file_fullpath=/data/pgroup/3531011152_vXxCy4WP_0124_05.JPG&file_name_orig=0124_05.JPG
if(!$file_fullpath)
	alert('파일 경로가 없습니다.');
        
if(!$file_name_orig)
	alert('파일 이름이 없습니다.');

$filepath = $file_fullpath;
$filepath = preg_replace("/\s+/", "+", $filepath); // 파일명에 공백이 들어가는 경우가 있어서 공백=>+기호로 강제치환함
$filepath = addslashes($filepath);
if (!is_file($filepath) || !file_exists($filepath))
    alert('파일이 존재하지 않습니다.');

//$original = urlencode($file['bf_source']);
$original = iconv('utf-8', 'euc-kr', $file_name_orig); // SIR 잉끼님 제안코드
if(!$original)
	$original = $file_name_orig;

//if(preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {
//	header("Cache-control: private");  //<---- 이부분 추가 
//    header("content-type: doesn/matter");
//    header("content-length: ".filesize("$filepath"));
//    header("content-disposition: attachment; filename=\"$original\"");
//    header("content-transfer-encoding: binary");
//} else {
//    header("content-type: file/unknown");
//    header("content-length: ".filesize("$filepath"));
//    header("content-disposition: attachment; filename=\"$original\"");
//    header("content-description: php generated data");
//}
//header("pragma: no-cache");
//header("expires: 0");
//flush();


// Must be fresh start 
if( headers_sent() ) 
  die('Headers Already Sent'); 

// Required for some browsers 
if(ini_get('zlib.output_compression')) 
  ini_set('zlib.output_compression', 'Off'); 

// Parse Info / Get Extension 
$fsize = filesize($filepath); 
$path_parts = pathinfo($filepath); 
$ext = strtolower($path_parts["extension"]); 

// Determine Content Type 
switch ($ext) 
{ 
  case "pdf": $ctype="application/pdf"; break; 
  case "exe": $ctype="application/octet-stream"; break; 
  case "zip": $ctype="application/zip"; break; 
  case "doc": $ctype="application/msword"; break; 
  case "xls": $ctype="application/vnd.ms-excel"; break; 
  case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
  case "gif": $ctype="image/gif"; break; 
  case "png": $ctype="image/png"; break; 
  case "jpeg": 
  case "jpg": $ctype="image/jpg"; break; 
  default: $ctype="application/force-download"; 
} 

header("Pragma: public"); // required 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("Cache-Control: private",false); // required for certain browsers 
header("Content-Type: $ctype"); 
header("Content-Disposition: attachment; filename=\"".$original."\";" ); 
header("Content-Transfer-Encoding: binary"); 
header("Content-Length: ".$fsize); 
ob_clean(); 
flush(); 



$fp = fopen($filepath, 'rb');

$download_rate = 10;

while(!feof($fp)) {
    print fread($fp, round($download_rate * 1024));
    flush();
    usleep(1000);
}
fclose ($fp);
flush();
?>
