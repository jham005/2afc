<?php
require 'util.php';
$e = filename_safe(trim($_POST['e']));
if (empty($e) || $e == '.' || $e ==  '..' || !is_dir("experiments/$e")) {
  header('HTTP/1.0 404 Not Found');
  exit();
}

$f = 1;
while (is_dir("experiments/$e/" . sprintf("%03d", $f)))
  $f++;

logger("mkdir experiments/$e/" . sprintf("%03d", $f));
mkdir("experiments/$e/" . sprintf("%03d", $f));
header("HTTP/1.0 204 No Content");
