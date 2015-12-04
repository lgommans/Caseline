#!/usr/bin/env php
<?php 

if ($argc != 2) {
	die("Usage: ./controlDB.php option\nOptions: create | truncate");
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

die("Invalid option.");

