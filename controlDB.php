#!/usr/bin/env php
<?php 

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
	$ok = $db->exec("DELETE FROM events");
	if ($ok === false) {
		die("Error truncating.");
	}
	die("Done.\n");
}

if ($argv[1] == "load") {
	$f = fopen($argv[2], 'r');
	$rows = 0;
	while ($row = fgets($f)) {
		if (strlen($row) == 0) {
			continue;
		}
		$rows += 1;
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
		if (count($cols) != 5) {
			echo "Warning: ".count($cols)."error on line $row\n";
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
	}

	die("Done. Inserted $rows rows.\n");
}

die("Invalid option.\n");

