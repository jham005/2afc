<?php
require 'util.php';

$fields = array('e', 'dir', 'resumableIdentifier', 'resumableFilename', 'resumableChunkNumber', 'resumableTotalChunks');
foreach ($fields as $f)
  if (!is_string($_REQUEST[$f]) || ($f != 'dir' && trim($_REQUEST[$f]) == '') || $_REQUEST[$f] == '.' || $_REQUEST[$f] == '..' || strpos($_REQUEST[$f], '/') !== false) {
    logger("Bad request: $f is '" . $_REQUEST[$f] . "'");
    header('HTTP/1.0 400 Bad Request');
    exit;
  }

$e = filename_safe(trim($_REQUEST['e']));
$subdir = filename_safe(trim($_REQUEST['dir']));

$dir = "experiments/$e/$subdir";

if (!is_dir($dir)) {
  header("HTTP/1.0 400 Bad Request");
  exit();
}

$chunk = $dir . '/' . filename_safe($_GET['resumableFilename']) . '.part' . intval($_GET['resumableChunkNumber']);
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (file_exists($chunk))
    header("HTTP/1.0 200 Ok");
  else
    header("HTTP/1.0 204 No Content");
}

foreach ($_FILES as $file) {
  if ($file['error'] != 0) {
    logger('Error ' . $file['error'] . ' in file ' . $_POST['resumableFilename']);
    continue;
  }
  
  $fileName = filename_safe($_POST['resumableFilename']);
  
  if (!move_uploaded_file($file['tmp_name'], "$dir/$fileName.part$_POST[resumableChunkNumber]"))
    logger("Failed to save chunk $e/$subdir/$fileName.part$_POST[resumableChunkNumber]");
  else
    createFileFromChunks($dir, $fileName);
}

function createFileFromChunks($dir, $fileName) {
  $totalSize = intval($_POST['resumableTotalSize']);
  $totalChunks = intval($_POST['resumableTotalChunks']);

  $size = 0;
  for ($i = 1; $i <= $totalChunks; $i++)
    $size += filesize("$dir/$fileName.part$i");
  if ($size < $totalSize)
    return false;
  
  if (($fp = fopen("$dir/$fileName", 'w')) !== false) {
    for ($i = 1; $i <= $totalChunks; $i++) {
      logger("Appending $dir/$fileName.part$i to $dir/$fileName");
      $src = fopen("$dir/$fileName.part$i", "rb");
      stream_copy_to_stream($src, $fp);
      fclose($src);
    }
    
    fclose($fp);
  }
  
  for ($i = 1; $i <= $totalChunks; $i++) {
    logger("Removing $dir/$fileName.part$i");
    unlink("$dir/$fileName.part$i");
  }

  return true;
}
