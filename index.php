<?php
echo '<!DOCTYPE lang="en">
<head>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript" src="script.js" ></script>
<title>2AFC</title>
<style>
img { max-width: 48%; max-height: 90vh; height: auto; width: auto; object-fit: contain; border: 2px solid; }
img.left { float: left; margin-right: 5px; }
</style>
</head>
<body>
<div class="container">';

if (!is_string($_REQUEST['experiment']) || !is_dir("experiments/$_REQUEST[experiment]")) {
  header('HTTP/1.1 303 See Other');
  header("Location: select.php");
  exit;
}

$experiment = $_REQUEST['experiment'];

global $ini;
$ini = array('breakAfter' => 15, 'shuffleGroups' => true, 'shuffleMembers' => false);
if (is_readable("experiments/$experiment/settings.ini"))
  $ini = array_merge($ini, parse_ini_file("experiments/$experiment/settings.ini"));

echo "<div id='consent'>";
echo readhtml('consent', 'experiments', $experiment);
echo '
    <button class="btn btn-primary" id="agree">I agree to take part</button>
    <button class="btn btn-primary" id="disagree">I do not agree to take part</button>
    <div id="goodbye" style="display: none">
      <p>Ok, goodbye!</p>
    </div>
  </div>
</div>
<div class="container">
<div id="tutorial" style="display: none">';
echo readhtml('tutorial', 'experiments', $experiment);
echo '</div></div>';

$fd = fopen("next-id.dat", "c+");
if (!$fd)
  $userId = mt_rand();
else {
  if (!flock($fd, LOCK_EX))
    $userId = mt_rand();
  else {
    $userId = intval(fgets($fd)) + 1;
    ftruncate($fd, 0);
    fseek($fd, 0, SEEK_SET);
    fwrite($fd, "$userId\n");
  }
  
  fclose($fd);
}

$trials = readTrials($experiment);
echo '<div id="trials" data-user="' . $userId . '" data-experiment="' . addslashes($experiment) . '" >';
$breakAfter = isset($ini['breakAfter']) ? $ini['breakAfter'] : 15;
$remainder = count($trials) % $breakAfter;
$countdown = $remainder > 0 ? $breakAfter + 1 : $breakAfter;
foreach ($trials as $i => $trial) {
  echo '<div style="display: none">';
  echo '<div class="instructions">' . $trial['instructions'] . '</div>';
  $left = $trial['left'];
  $right = $trial['right'];

  echo '<img class="lazy left"  data-src="' . addslashes($left)  . '" data-item="' . addslashes(basename($left)) . '" />';
  echo '<img class="lazy right" data-src="' . addslashes($right) . '" data-item="' . addslashes(basename($right)) . '" />';
  echo '</div>';
  $countdown--;
  if ($countdown == 0 && $i + $breakAfter < count($trials)) {
    $countdown = $breakAfter;
    if ($remainder > 0) {
      $countdown++;
      $remainder--;
    }

    $break = readhtml('break', 'experiments', $experiment);
    if (empty($break))
      $break = '<p>Take a break.</p>';
    echo '<div class="container" style="display: none">' . $break . '<br /><button type="button" class="btn btn-primary break">Continue</button></div>';
  }
}

echo '<div class="container" id="finished" style="display:none">
<h1>Thank you!  You have completed the experiment.</h1>';

echo '<form action="debrief.php" method="post">';
echo "<input type='hidden' name='userId' value='$userId' />";
echo readhtml('debrief', 'experiments', $experiment);
echo '<input class="btn btn-primary" role="button" type="submit" value="Submit" /></form>
</div>
</div>
</div>
</body>';

function readTrials($experiment) {
  $trials = array();
  foreach (scandir("experiments/$experiment") as $dir) {
    if (is_dir("experiments/$experiment/$dir") && $dir != '.' && $dir != '..') {
      $groups = array();
      foreach (scandir("experiments/$experiment/$dir") as $image) {
	if (is_image($image) && preg_match('/^(.*)-\w\.[^\\.]+$/', "$dir/$image", $matches)) {
	  $m = $matches[1];
	  if (!isset($groups[$m]))
	    $groups[$m] = array();
	  $groups[$m][] = "experiments/$experiment/$dir/$image";
	}
      }

      global $ini;

      if ($ini['shuffleGroups'])
	shuffle($groups);
      else
	sort($groups);
      foreach ($groups as $members)
	if (count($members) > 1) {
	  if ($ini['shuffleMembers'])
	    shuffle($members);
	  else
	    sort($members);
	  $trials[] = array('instructions' => readhtml('instructions', 'experiments', $experiment, $dir),
			    'left' => $members[0],
			    'right' => $members[1]);
	}
    }
  }
  
  return $trials;
}

function readhtml() {
  $file = func_get_arg(0);
  $args = func_get_args();
  for ($i = count($args) - 1; $i > 0; $i--) {
    $path = implode('/', array_slice($args, 1, $i));
    if (is_readable("$path/$file.html"))
      return file_get_contents("$path/$file.html");
  }
  
  return '';
}

function is_image($file) {
  $ext = pathinfo($file, PATHINFO_EXTENSION);
  return $ext == 'png' || $ext == 'jpg' || $ext == 'jpeg';
}
