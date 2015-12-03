#!/usr/bin/env php
<?php

if ($argc != 2) {
	die("Usage: ./fillDB my.csv\n");
}

require('functions.php');

$f = fopen($argv[1], 'r');
$rows = 0;
while ($row = fgets($f)) {
	$rows += 1;
	$inString = false;
	$cols = [];
	$currentCol = '';
	$firstByteOfCol = true;
	for ($i = 0; $i < strlen($row); $i++) {
		if ($inString) {
			if ($inString == $row[$i]) {
				$inString = false;
			}
			else {
				$currentCol .= $row[$i];
			}
		}
		else if ($firstByteOfCol && ($row[$i] == '"' || $row[$i] == "'")) {
			$inString = $row[$i];
		}
		else {
			if ($row[$i] == ',') {
				$cols[] = $currentCol;
				$currentCol = '';
				$firstByteOfCol = true;
			}
			else {
				$currentCol .= $row[$i];
			}
		}
		$firstByteOfCol = false;
	}
	$cols[] = $currentCol;
	if (!is_numeric($cols[0])) {
		$cols[0] = strtotime($cols[0]);
	}
	$cols[0] = intval($cols[0]); // datetime
	for ($i = 1; $i < count($cols); $i++) {
		$cols[$i] = SQLite3::escapeString($cols[$i]);
	}
	$ok = $db->exec("INSERT INTO events (datetime, device, source, content) "
		. "VALUES($cols[0], '$cols[2]', '$cols[1]', '$cols[3]')");
	if ($ok === false) {
		print_r($cols);
		die("Error inserting");
	}
}

die("Done. Inserted $rows rows.");

