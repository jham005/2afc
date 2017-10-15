<?php
echo '<!DOCTYPE lang="en">
<head>
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
<title>2AFC Experiment Upload</title>
</head>
<body>
<div class="container">
<h1>2AFC Experiment Upload</h1>';

$files = $_FILES['zip'];
if (is_string($_REQUEST['access']) && is_array($files) && $files['error'] == UPLOAD_ERR_OK && is_uploaded_file($files['tmp_name'])) {
  if ($_REQUEST['access'] != 'secret')
    echo '<div class="alert alert-warning" role="alert">The access code is incorrect.</div>';
  else {
    echo "<pre>";
    $zip = zip_open($files['tmp_name']);
    $experimentName = filename_safe(basename($files['name'], ".zip"));
    echo "Experiment name: $experimentName\n";
    if (isset($_REQUEST['replace'])) {
      rmdirr("experiments/$experimentName");
      echo "Replacing $experimentName\n";
    }
    
    mkdir("experiments/$experimentName", 0755, true);
    while ($entry = zip_read($zip)) {
      $name = zip_entry_name($entry);
      if (preg_match(',/$,', $name)) continue;
      $info = pathinfo($name);
      switch ($info['extension']) {
      case 'html':
      case 'png':
      case 'jpg':
      case 'jpeg':
	$dir = basename(dirname($name));
	if ($dir == $experimentName || $dir == '.' || $dir == '..' || $dir == '' || $dir[0] == '/') $dir = '';
	$path = "experiments/$experimentName/$dir/";
	mkdir($path, 0755);
	$target = $path . filename_safe($info['basename']);
	file_put_contents($target, zip_entry_read($entry, zip_entry_filesize($entry)));
	chmod($target, 0644);
	echo "Added $target\n";
	break;
      default:
	echo "Ignoring unrecognised file $name\n";
      }
    }
    
    echo "\nContents:\n";
    ls("experiments");
    
    echo "</pre>";
  }
} else {
  echo '<p>Your experiment data should be in a ZIP file of the form:</p>
<ul>
  <li>.../experiment-name/consent.html</li>
  <li>.../experiment-name/tutorial.html</li>
  <li>.../experiment-name/instructions.html</li>
  <li>.../experiment-name/folder-1/image-1a</li>
  <li>.../experiment-name/folder-1/image-1b</li>
  <li>.../experiment-name/folder-1/etc.</li>
  <li>.../experiment-name/folder-2/image-1a</li>
  <li>.../experiment-name/folder-2/image-1b</li>
  <li>.../experiment-name/folder-2/etc.</li>
</ul>

<p>Images must be provided in pairs that differ only in the last
letter of the name; e.g. image-1a.png and image-1b.png will be
recognised as a choice pair. If a group of three or more images is
provided (e.g. image-1a.png, image-1b.png, image-1c.png), then, for
each trial, two images from the group will be selected at random. Any
unpaired images will be ignored.</p>

<p>Any of the folder-1, etc. may contain its own instructions.html
file to be used in preference to the default instructions.</p>

<p>The maximum upload size is around 16Mb, so you may need to create
and upload the experiment in several ZIP files.</p>

<form enctype="multipart/form-data" method="post" action="install.php">
<div class="form-group">
 <label for="zip">Experiment zip:</label>
 <input class="form-control" type="file" id="zip" name="zip" />
</div>
<div class="form-group">
 <label class="form-check-label">
  <input class="form-check-input" type="checkbox" name="replace" value="1" />
  Replace existing files for this experiment
 </label>
</div>
<div class="form-group">
 <label for="access">Access code</label>
 <input class="form-control" type="text" name="access" />
</div>
 <input type="submit" class="btn btn-primary" value="Submit" />
</form>';
}

echo '</div>
</body>
</html>';

function filename_safe( $str ) {
  return strtr( $str,
		"\01\02\03\04\05\06\07\10\11\12\13\14\15\16\17\20\21\22\23\24\25\26\27\30\31\32\33\34\35\36\37\\\":/'*<>?|",
		"-----------------------------------------" );
}

function rmdirr($dir) { 
  if (is_dir($dir)) {
    foreach (scandir($dir) as $file) {
      if ($file != '.' && $file != '..')
	rmdirr("$dir/$file");
    }
    rmdir($dir);
  } else
    unlink($dir);
}


function ls($dir) { 
  echo "$dir\n";
  if (is_dir($dir))
    foreach (scandir($dir) as $file) {
      if ($file == '.' || $file == '..') continue;
      ls("$dir/$file");
    }
}
