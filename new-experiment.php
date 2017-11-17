<?php
require 'util.php';
$n = 1;
while (is_dir("experiments/Unnamed-$n"))
  $n++;
logger("mkdir experiments/Unnamed-$n");
mkdir("experiments/Unnamed-$n");
mkdir("experiments/Unnamed-$n/Trash");
header("HTTP/1.0 201 Created");
echo "Unnamed-$n";
