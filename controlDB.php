#!/usr/bin/env php
<?php 

if (isset($_SERVER['REMOTE_ADDR'])) {
	$argc = 1;
	if (isset($_GET['load'])) {
		$argv[] = '';
		$argv[] = 'load';
		$argv[] = $_GET['load'];
		$argc = 3;
	}
	else {
		$argv[] = '';
		foreach ($_GET as $key=>$val) {
			$argv[] = $key;
		}
	}
}

header("Content-Type: text/plain");

if ($argc < 2) {
	die("Usage: ./controlDB.php command [arguments]\n"
		. "Commands: create | truncate | load\n"
		. "The 'truncate' command only truncates events. To remove everything, just\ndelete the database file.\n"
		. "The 'load' command needs a csv file as argument.");
}

require('functions.php');

if ($argv[1] == "create") {
	dbsetup();
	die("Done.\n");
}

if ($argv[1] == "truncate") {
	copy('db.sqlite', 'db.sqlite.backup');
	$ok = $db->exec("DELETE FROM events");
	if ($ok === false) {
		die("Error truncating.");
	}
	die("Done.\n");
}

if ($argv[1] == "load") {
	copy('db.sqlite', 'db.sqlite.backup');
	$f = fopen($argv[2], 'r');
	if ($f === false) {
		die("Exiting because of file opening error.");
	}
	$rows = 0;
	while ($row = fgets($f)) {
		if (strlen($row) == 0) {
			continue;
		}
		$inString = false;
		$cols = [];
		$currentCol = '';
		$firstByteOfCol = true;
		for ($i = 0; $i < strlen($row); $i++) {
			if ($inString) {
				$firstByteOfCol = false;
				if ($inString == $row[$i]) {
					$inString = false;
				}
				else {
					$currentCol .= $row[$i];
				}
			}
			else if ($firstByteOfCol && ($row[$i] == '"' || $row[$i] == "'")) {
				$inString = $row[$i];
				$firstByteOfCol = false;
			}
			else {
				if ($row[$i] == ',') {
					$cols[] = $currentCol;
					$currentCol = '';
					$firstByteOfCol = true;
				}
				else {
					$currentCol .= $row[$i];
					$firstByteOfCol = false;
				}
			}
		}
		$cols[] = $currentCol;
		if (count($cols) < 3 || count($cols) > 5) {
			echo "Warning: error on line $row\n";
			print_r($cols);
			continue;
		}
		if (!is_numeric($cols[0])) {
			$cols[0] = strtotime($cols[0]);
		}
		$cols[0] = intval($cols[0]); // datetime
		for ($i = 1; $i < count($cols); $i++) {
			$cols[$i] = SQLite3::escapeString($cols[$i]);
		}
		$ok = $db->exec("INSERT INTO events (datetime, device, source, summary, details) "
			. "VALUES($cols[0], '$cols[1]', '$cols[2]', '$cols[3]', '$cols[4]')");
		if ($ok === false) {
			print_r($cols);
			die("Error inserting");
		}
		else {
			$rows += 1;
		}
	}

	die("Done. Inserted $rows rows.\n");
}

if ($argv[1] == 'undo') {
	rename('db.sqlite.backup', 'db.sqlite');
	die("Done.\n");
}

die("Invalid option.\n");

