<?php
$fd = fopen("comments.dat", "c");
if ($fd && flock($fd, LOCK_EX)) {
  date_default_timezone_set('UTC');
  fseek($fd, 0, SEEK_END);
  fwrite($fd, date("Y-m-d H:i:s") . "\n");
  foreach ($_POST as $k => $v)
    fwrite($fd, "$k: $v\n");
  fwrite($fd, "\n------------------------------\n");
  flock($fd, LOCK_UN);
  fclose($fd);
}
?>

<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>
<div class="container">
<h1>Thank you!</h1>
</div>
</body>
</html>