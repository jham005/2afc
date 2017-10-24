<?php
$reqd = array('e', 'item', 'srcFolder', 'dstFolder');
foreach ($reqd as $f)
  if (!is_string($_POST[$f])) {
    header('HTTP/1.0 400 Bad Request');
    exit();
  }

require 'util.php';
$e = filename_safe(trim($_POST['e']));
$item = filename_safe($_POST['item']);
$srcFolder = filename_safe(trim($_POST['srcFolder']));
$dstFolder = filename_safe(trim($_POST['dstFolder']));

echo rename("experiments/$e/$srcFolder/$item", "experiments/$e/$dstFolder/$item") ? "ok" : "failed";

