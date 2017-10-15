<?php
echo '<!DOCTYPE html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
<title>2AFC</title>
</head>
<body>
<div class="container">
  <h1>2AFC Experiment Selection</h1>
  <h2>Select the experiment you would like to run</h2>
   <ul>';
foreach (scandir("experiments") as $exp)
  if ($exp != '.' && $exp != '..')
    echo "<li><a href='index.php?experiment=$exp'>$exp</a></li>";
echo '</ul>
</div>
</body>';
