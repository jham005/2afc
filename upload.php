<?php
/**
 * This is the implementation of the server side part of
 * Resumable.js client script, which sends/uploads files
 * to a server in several chunks.
 *
 * The script receives the files in a standard way as if
 * the files were uploaded using standard HTML form (multipart).
 *
 * This PHP script stores all the chunks of a file in a temporary
 * directory (`temp`) with the extension `_part<#ChunkN>`. Once all 
 * the parts have been uploaded, a final destination file is
 * being created from all the stored parts (appending one by one).
 *
 * @author Gregory Chris (http://online-php.com)
 * @email www.online.php@gmail.com
 *
 * @editor Bivek Joshi (http://www.bivekjoshi.com.np)
 * @email meetbivek@gmail.com
 */

function logger($str) {
  $msg = date('d.m.Y') . ": $str\n";
  echo $msg;
  if (($fp = fopen('upload.log', 'a+')) !== false) {
    fputs($fp, $msg);
    fclose($fp);
  }
}

function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
	if (filetype($dir . "/" . $object) == "dir")
	  rrmdir($dir . "/" . $object); 
	else
	  unlink($dir . "/" . $object);
      }
    }

    reset($objects);
    rmdir($dir);
  }
}

function createFileFromChunks($temp_dir) {
  $fileName = strval($_POST['resumableFilename']);
  $totalSize = intval($_POST['resumableTotalSize']);
  $totalChunks = intval($_POST['resumableTotalChunks']);

  $size = 0;
  for ($i = 1; $i <= $totalChunks; $i++)
    $size += filesize("$temp_dir/$fileName.part$i");
  if ($size < $totalSize)
    return false;
  
  if (($fp = fopen("temp/$fileName", 'w')) !== false) {
    for ($i = 1; $i <= $totalChunks; $i++) {
      fwrite($fp, file_get_contents("$temp_dir/$fileName.part$i"));
      logger("Writing chunk $i");
    }
    
    fclose($fp);
  }
  
  if (rename($temp_dir, $temp_dir . '_UNUSED'))
    rrmdir($temp_dir . '_UNUSED');
  else
    rrmdir($temp_dir);
  return true;
}

$fields = array('resumableIdentifier', 'resumableFilename', 'resumableChunkNumber');
foreach ($fields as $f)
  if (!is_string($_REQUEST[$f]) || trim($_REQUEST[$f]) == '' || $_REQUEST[$f] == '.' || $_REQUEST[$f] == '..' || strpos($_REQUEST[$f], '/') !== false) {
    logger("Bad request: $f is '" . $_REQUEST[$f] . "'");
    header('HTTP/1.0 400 Bad Request');
    exit;
  }

$temp_dir = 'temp/' . $_GET['resumableIdentifier'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $chunk_file = $temp_dir . '/' . $_GET['resumableFilename'] . '.part' . intval($_GET['resumableChunkNumber']);
  if (file_exists($chunk_file))
    header("HTTP/1.0 200 Ok");
  else
    header("HTTP/1.0 404 Not Found");
}

foreach ($_FILES as $file) {
  if ($file['error'] != 0) {
    logger('error ' . $file['error'] . ' in file ' . $_POST['resumableFilename']);
    continue;
  }
  
  $temp_dir = 'temp/' . $_POST['resumableIdentifier'];
  if (!is_dir($temp_dir))
    mkdir($temp_dir, 0777, true);
  
  $dest_file = $temp_dir . '/' . $_POST['resumableFilename'] . '.part' . $_POST['resumableChunkNumber'];
  
  if (!move_uploaded_file($file['tmp_name'], $dest_file))
    logger('Error saving (move_uploaded_file) chunk ' . $_POST['resumableChunkNumber'] . ' for file ' . $_POST['resumableFilename']);

  createFileFromChunks($temp_dir);
}
