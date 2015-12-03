<?php 

require('functions.php');

if (isset($_GET['getEvents'])) {
	die(getEvents());
}

