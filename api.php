<?php 

require('functions.php');

if (isset($_GET['getEvents'])) {
	die(getEvents());
}

if (isset($_GET['getViews'])) {
	die(getViews());
}

if (!empty($_GET['saveView'])) {
	die(insertView($_GET['saveView'], $_GET['datefrom'], $_GET['dateuntil'], $_GET['filter']));
}

if (!empty($_GET['deleteView'])) {
	$d = $_GET['deleteView'];
	if ($d === false || $d < 1 || $d > 1000*1000*500) {
		die('huh');
	}
	$rowid = intval($d);
	$db->exec('DELETE FROM views WHERE rowid = ' . $rowid) or die('false');
	die('true');
}

if (!empty($_GET['deleteEvent'])) {
	$d = $_GET['deleteEvent'];
	if ($d === false || $d < 1 || $d > 1000*1000*500) {
		die('huh');
	}
	$rowid = intval($d);
	$db->exec('DELETE FROM events WHERE rowid = ' . $rowid) or die('false');
	die('true');
}

