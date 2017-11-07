<?php
$experiments = array();
foreach (scandir('experiments') as $e)
  if ($e != '.' && $e != '..' && is_dir("experiments/$e"))
    $experiments[] = $e;
echo json_encode($experiments);
