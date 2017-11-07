<?php
require 'util.php';
$n = filename_safe(trim($_POST['n']));
if (empty($n) || is_dir("experiments/$n")) {
  header('HTTP/1.0 400 Bad Request');
  exit();
}

mkdir("experiments/$n");
header("HTTP/1.0 204 No Content");
