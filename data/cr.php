#!/usr/bin/env php
<?php

include_once 'cli.php';

$dir = date('Y-m-d');
$action = askConsole('c:create or r:restore ?', 'c');

if ('c' === strtolower($action)) {
	$dbsA = askConsole('From which database Server?', '127.0.0.1');
	$dbA = askConsole('From which database?', 'prod_bobcat');
	$u = askConsole('user?', $dbA);
	$p = askConsole('pass?', $dbA);
	system("mongodump -h {$dbsA} --db {$dbA} --username {$u} --password {$p} --out=dump/{$dir}");
} else {
	$dbs = askConsole('Into which database Server?', '78.47.93.36');
	$db = askConsole('From which database?', 'prod_bobcat');
	$u = askConsole('user?', $db);
	$p = askConsole('pass?', $db);
	$folder = askConsole('which folder?', "{$dir}");
	system("mongorestore --host {$dbs} --port 27017 --username {$u} --password {$p} --db {$db} dump/{$folder}/{$db}/");
}
