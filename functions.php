<?php 

$db = new SQLite3("db.sqlite");

function dbsetup() {
	global $db;
	$db->exec("CREATE TABLE events "
		. "(datetime INTEGER, device TEXT, source TEXT, content TEXT, annotation TEXT)");
	$db->exec("CREATE TABLE views "
		. "(name TEXT, datefrom INTEGER, dateuntil DATETIME, filter TEXT)");
}

function getEvents() {
	global $db;
	$result = $db->query('SELECT rowid, datetime, device, source, content, annotation FROM events '
		. 'ORDER BY datetime');
	$results = [];
	while ($row = $result->fetchArray()) {
		$row["printableDatetime"] = date('j M H:i', $row['datetime']);
		$results[] = $row;
	}
	return json_encode($results);
}

function getViews() {
	global $db;
	$result = $db->query('SELECT name, datefrom, dateuntil, filter FROM views');
	$results = [];
	while ($row = $result->fetchArray()) {
		$results[] = $row;
	}
	return json_encode($results);
}

function getDevices() {
	return ['home', 'work', 'phone'];
}

function insertEvent($unixtime, $device, $source, $content) {
	global $db;
	$unixtime = intval($unixtime);
	if (in_array($device, getDevices(), true) !== true) {
		die("Excuse me, what device is '$device'?");
	}
	if (empty($content)) {
		die("Error: empty content.");
	}
	$device = SQLite3::escapeString($device);
	$source = SQLite3::escapeString($source);
	$content = SQLite3::escapeString($content);

	$ok = $db->exec("INSERT INTO events VALUES($unixtime, '$device', '$source', '$content')");
	if (!$ok) {
		die("Error on $unixtime,$device,$source,$content");
	}
	return $ok;
}

function insertView($name, $datefrom, $dateuntil, $filter) {
	global $db;
	$name = SQLite3::escapeString($name);
	$datefrom = intval($datefrom);
	$dateuntil = intval($dateuntil);
	$filter = SQLite3::escapeString($filter);

	$ok = $db->exec("INSERT INTO views VALUES('$name', $datefrom, $dateuntil, '$filter')");
	if (!$ok) {
		die("Error on $name");
	}
	return $ok;
}

