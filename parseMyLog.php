<?php 
if (!isset($_POST['sum'])) {
	?>
		<form method=POST action="?step=3" enctype="multipart/form-data">
			Select log: <input type=file name=log ><br>
			Separator: <input name=sep value="<?php echo "\t";?>"> (default: tab)<br>
			Max cols: <input type=numeric name=maxcols size=2 value=2><br>
			Timestamp in column: <input type=numeric name=tscol size=2 value=1> (1=first)<br>
			Timestamp is string: <input type=checkbox name=strTS><br>
			Device: <input name=dev> (home|work|phone)<br>
			Source: <input name=src> (e.g. "logins" or "whatsapp log" or "whatsapp db")<br>
			Summary column: <input type=numeric name=sum value=2 size=2> (0=not applicable)<br>
			Details column: <input type=numeric name=dtls value=0 size=2> (0=not applicable)<br>
			<input type=submit value="Download parsed.csv">
		</form>
	<?php 
	exit;
}

if (filesize($_FILES['log']['tmp_name']) > 1024 * 1024 * 64) {
	unlink($_FILES['log']['tmp_name']);
	die("File too large (>64MB).");
}
$data = file_get_contents($_FILES['log']['tmp_name']);
unlink($_FILES['log']['tmp_name']);

if (!in_array($_POST['dev'], ['home','work','phone'], true)) {
	die("Invalid device.");
}
if (empty($_POST['src'])) {
	die("Invalid source.");
}

$out = '';
$dev = $_POST['dev'];
$src = $_POST['src'];

$lines = explode("\n", $data);
foreach ($lines as $line) {
	$line = explode($_POST['sep'], $line, intval($_POST['maxcols']));
	if ($_POST['strTS']) {
		$ts = strtotime($line[intval($_POST['tscol']) - 1]);
	}
	else {
		$ts = intval($line[intval($_POST['tscol']) - 1]);
	}
	if ($_POST['sum'] == 0) {
		$sum = '';
	}
	else {
		$sum = $line[intval($_POST['sum'])];
	}
	if (strpos('"', $sum) !== false) {
		$sum = str_replace('"', '`', $sum); // oops.
	}
	if ($_POST['dtls'] == 0) {
		$dtls = '';
	}
	else {
		$dtls = $line[intval($_POST['dtls'])];
	}
	if (empty($ts) || $ts < 10 || $ts > time()) {
		continue;
	}
	$out .= "$ts,\"$dev\",\"$src\",\"$sum\",\"$dtls\"\n";
}

header("Content-Type: text/csv");
header("Content-Length: " . strlen($out));
header("Content-Disposition: attachment; filename=\"parsed.csv\"");
die($out);

