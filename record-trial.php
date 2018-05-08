<?php
date_default_timezone_set('UTC');
$data = array(date("Y-m-d H:i:s"));

$required = array('user', 'experiment', 'trial', 'time', 'left', 'right', 'choice', 'width', 'height');
foreach ($required as $f)
  if (is_string($_REQUEST[$f]))
    $data[] = strtr($_REQUEST[$f], "\t\n\r", "   ");
  else {
    echo 'bad request';
    return;
  }

$fd = fopen("data.csv", "c");
if (!$fd) {
  echo '1';
  return;
}

if (!flock( $fd, LOCK_EX)) {
  echo '2';
  fclose($fd);
  return;
}
  
fseek($fd, 0, SEEK_END);
fwrite($fd, implode("\t", $data) . "\n");
flock($fd, LOCK_UN);
fclose($fd);
echo 'ok';
