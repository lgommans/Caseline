<?php 

$db = new SQLite3("db.sqlite");

function dbsetup() {
	global $db;
	$db->exec("CREATE TABLE events "
		. "(datetime INTEGER, device TEXT, source TEXT, content TEXT, annotation TEXT)");
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

