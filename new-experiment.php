<?php
$n = 1;
while (is_dir("experiments/Unnamed-$n"))
  $n++;
mkdir("experiments/Unnamed-$n");
header("HTTP/1.0 201 Created");
echo "Unnamed-$n";
