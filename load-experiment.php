<?php
require 'util.php';
$e = filename_safe(trim($_GET['e']));
if (empty($e) || !is_dir("experiments/$e")) {
  header('HTTP/1.0 400 Bad Request');
  exit();
}

$data = array();
foreach (scandir("experiments/$e") as $f) {
  if ($f == '.' || $f == '..') continue;
  if (is_dir("experiments/$e/$f")) {
    $d = array();
    foreach (scandir("experiments/$e/$f") as $g)
      if ($g != '.' && $g != '..' && !is_dir("experiments/$e/$f/$g"))
	$d[] = $g;
    $data[$f] = $d;
  } else
    $data[''][] = $f;
}

echo json_encode($data);
