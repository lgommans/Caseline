<?php 
if (isset($_GET['undo'])) {
	$_GET = ['undo' => ''];
	require('controlDB.php');
	exit;
}

if (isset($_GET['dbBackup'])) {
	header("Content-Type: application/x-sqlite3");
	header("Content-Length: " . filesize('db.sqlite'));
	header("Content-Disposition: attachment; filename=\"db.sqlite\"");
	readfile('db.sqlite');
	exit;
}

if (isset($_GET['importParsedcsv'])) {
	if (!file_exists('parsed.csv')) {
		die('No parsed.csv file on server.');
	}
	$_GET = ['load' => 'parsed.csv'];
	require('controlDB.php');
	unlink('parsed.csv');
	exit;
}

if (!isset($_FILES['log'])) {
	?>
		<b><i>Import any log into Caseline</i></b>
		Usage:<br>
		1. Fill in the fields and hit "Save and download parsed.csv". This saves parsed.csv on the server <i>and</i> downloads it (the same file) so you can view it.<br>
		2. Check/review your download. Is it how it should be? (Ask Luc the first few times!)<br>
		3. Backup the database.<br>
		4. Hit "Import parsed.csv into Caseline". Your events should now show up in Caseline.<br>
		Did something go wrong? Give Luc your database backup. Forgot to make a backup? You can use the undo button, but you can use it only ONCE.<br>
		<br>
		<form method=POST enctype="multipart/form-data">
			<b>Step 1</b><br>
			Select log: <input type=file name=log ><br>
			<span id=a>
				<input onclick="document.getElementById('a').style='display:none;'" type=checkbox name=isCSV> Use as CSV file, not as log. (Expert option. Reload page to undo.)<br>
				Separator: <input name=sep value="<?php echo "\t";?>"> (default: tab)<br>
				Max cols: <input type=numeric name=maxcols size=2 value=2><br>
				Timestamp in column: <input type=numeric name=tscol size=2 value=1> (1=first)<br>
				Timestamp is string: <input type=checkbox name=strTS><br>
				Device: <input name=dev> (home|work|phone)<br>
				Source: <input name=src> (e.g. "logins" or "whatsapp log" or "whatsapp db")<br>
				Summary column: <input type=numeric name=sum value=2 size=2> (0=not applicable)<br>
				Details column: <input type=numeric name=dtls value=0 size=2> (0=not applicable)<br>
			</span>
			<b>Step 2</b> <input type=submit value="Save and download parsed.csv">
		</form>
		<b>Step 3</b> <input type=button onclick='location="?dbBackup";' value="Database backup"><br><br>

		<b>Step 4</b> <input type=button onclick='location="?importParsedcsv";' value="Import parsed.csv into Caseline"> (This actually changes something. Steps above are harmless.)<br><br>

		<b>Oops</b> <input type=button onclick='location="?undo";' value="undo"> (Only once!)
	<?php 
	exit;
}

if (filesize($_FILES['log']['tmp_name']) > 1024 * 1024 * 64) {
	unlink($_FILES['log']['tmp_name']);
	die("File too large (>64MB).");
}
if (isset($_POST['isCSV'])) {
	move_uploaded_file($_FILES['log']['tmp_name'], 'parsed.csv');
	die("Done.");
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
	if (strpos($sum, '"') !== false) {
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

$fid = fopen('parsed.csv', 'w');
fwrite($fid, $out);
fclose($fid);

header("Content-Type: text/csv");
header("Content-Length: " . strlen($out));
header("Content-Disposition: attachment; filename=\"parsed.csv\"");
die($out);

