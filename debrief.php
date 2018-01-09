<?php
$fd = fopen("comments.dat", "c");
if ($fd && flock($fd, LOCK_EX)) {
  date_default_timezone_set('UTC');
  fseek($fd, 0, SEEK_END);
  fwrite($fd, date("Y-m-d H:i:s") . "\n");
  foreach ($_POST as $k => $v)
    fwrite("$k: $v\n");
  fwrite("\n------------------------------\n");
  flock($fd, LOCK_UN);
  fclose($fd);
}

header('HTTP/1.1 303 See Other');
header("Location: select.php");
