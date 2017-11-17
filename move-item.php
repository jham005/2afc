<?php
$reqd = array('e', 'i', 's', 'd');
foreach ($reqd as $f)
  if (!is_string($_POST[$f])) {
    header('HTTP/1.0 400 Bad Request');
    exit();
  }

require 'util.php';

$e = filename_safe(trim($_POST['e']));
$item = filename_safe($_POST['i']);
$srcFolder = filename_safe(trim($_POST['s']));
$dstFolder = filename_safe(trim($_POST['d']));
if (empty($e) || invalidDir($e) || invalidDir($srcFolder) || invalidDir($dstFolder)) {
  header('HTTP/1.0 400 Bad Request');
  exit();
}

logger("rename experiments/$e/$srcFolder/$item experiments/$e/$dstFolder/$item");
rename("experiments/$e/$srcFolder/$item", "experiments/$e/$dstFolder/$item");
header("HTTP/1.0 204 No Content");
