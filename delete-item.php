<?php
$reqd = array('e', 'item', 'f');
foreach ($reqd as $f)
  if (!is_string($_POST[$f])) {
    header('HTTP/1.0 400 Bad Request');
    exit();
  }

require 'util.php';

$e = filename_safe(trim($_POST['e']));
$item = filename_safe($_POST['item']);
$folder = filename_safe(trim($_POST['f']));
if (invalidDir($e) || invalidDir($folder)) {
  header('HTTP/1.0 400 Bad Request');
  exit();
}

unlink("experiments/$e/$folder/$item");
header("HTTP/1.0 204 No Content");
