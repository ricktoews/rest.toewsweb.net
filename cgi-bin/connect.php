<?php
$host = 'localhost';
$user = 'rtoews';
$pass = '153846';
$database = 'toewsweb';

try {
	$dbh = new PDO('mysql:host=localhost;dbname=toewsweb', $user, $pass);
}
catch (PDOException $e) {
	echo "Failed to connect " . $e->getMessage() . "\n";
}
