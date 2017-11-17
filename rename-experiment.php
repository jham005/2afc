<?php
require 'util.php';
$e = filename_safe(trim($_POST['e']));
if (empty($e) || $e == '.' || $e ==  '..' || !is_dir("experiments/$e")) {
  header('HTTP/1.0 404 Not Found');
  exit();
}

$n = filename_safe(trim($_POST['n']));
if (empty($n) || is_dir("experiments/$n")) {
  header('HTTP/1.0 400 Bad Request');
  exit();
}

logger("rename experiments/$e experiments/$n");
rename("experiments/$e", "experiments/$n");
header("HTTP/1.0 200 OK");
echo $n;
