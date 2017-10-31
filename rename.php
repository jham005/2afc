<?php
foreach (array('e', 'prev', 'curr') as $f)
  if (!is_string($_REQUEST[$f]) || $_REQUEST[$f] == '.' || $_REQUEST[$f] == '..') {
    header('HTTP/1.0 400 Bad Request');
    exit();    
  }

require 'util.php';

$e = filename_safe($_REQUEST['e']);
$prev = filename_safe($_REQUEST['prev']);
$curr = filename_safe($_REQUEST['curr']);

if (!rename("experiments/$e/$prev", "experiments/$e/$curr"))
  header('HTTP/1.0 404 Not Found');
else
  header('HTTP/1.0 204 No Content');
