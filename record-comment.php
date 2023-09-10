<?php
if (!is_string( $_REQUEST['data'])) {
  echo 'bad request';
  return;
}

$fd = fopen("comments.dat", "c");
if (!$fd) {
  echo '1';
  return;
}

if (!flock($fd, LOCK_EX)) {
  echo '2';
  return;
}

date_default_timezone_set('UTC');
fseek($fd, 0, SEEK_END);
fwrite($fd, date("Y-m-d H:i:s") . "\n$_REQUEST[data]\n------------------------------\n");
flock($fd, LOCK_UN);
fclose($fd);
echo 'ok';

