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

