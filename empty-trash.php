<?php
if (!is_string($_POST['e'])) {
  header('HTTP/1.0 400 Bad Request');
  exit();
}

require 'util.php';

$e = filename_safe(trim($_POST['e']));
if (empty($e) || invalidDir($e)) {
  header('HTTP/1.0 400 Bad Request');
  exit();
}


foreach (scandir("experiments/$e") as $dir)
  if ($dir == 'Trash') {
    foreach (scandir("experiments/$e/Trash") as $item)
      if (!invalidDir($item)) {
	logger("unlink experiments/$e/Trash/$item");
	unlink("experiments/$e/Trash/$item");
      }
  } else if (isEmptyDir("experiments/$e/$dir")) {
    logger("rmdir experiments/$e/$dir");
    rmdir("experiments/$e/$dir");
  }

if (!is_dir("experiments/$e/Trash")) {
  logger("mkdir experiments/$e/Trash");
  mkdir("experiments/$e/Trash");
}

header("HTTP/1.0 204 No Content");

function isEmptyDir($dir) {
  if (!is_dir($dir)) return false;
  foreach (scandir($dir) as $item)
    if (!invalidDir($item))
      return false;
  return true;
}