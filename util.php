<?php
function logger($str) {
  $msg = date('d.m.Y') . ": $str\n";
  if (($fp = fopen('upload.log', 'a+')) !== false) {
    fputs($fp, $msg);
    fclose($fp);
  }
}

function filename_safe($str) {
  return strtr($str,
	       "\01\02\03\04\05\06\07\10\11\12\13\14\15\16\17\20\21\22\23\24\25\26\27\30\31\32\33\34\35\36\37\\\":/'*<>?|",
		"-----------------------------------------");
}

function invalidDir($dir) {
  return empty($dir) || $dir ==  '.' || $dir == '..';
}
