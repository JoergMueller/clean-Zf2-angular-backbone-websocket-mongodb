<?php
ini_set('memory_limit', '5120M');
ini_set('max_execution_time', 60000000);

chdir(dirname(__DIR__));

$env = 'local';
if (function_exists('apache_getenv')) {
	$env = apache_getenv('APP_ENV');
}

include 'data/cli.php';
include 'init_autoloader.php';

$app = Zend\Mvc\Application::init(require 'config/application.config.php');
$dm = $app->getServiceManager()->get('doctrine.documentmanager.odm_default');

$type = askConsole("Type ( [a]ll, [h]ome )", "a");

if ('c' == strtolower($type)) {
	$tpl = file_get_contents(getcwd() . '/data/templates/default/shortheader.tpl');
	$criteria = [
		'path.de' => '/entdecken/staedte',
	];
} elseif ('a' == strtolower($type)) {
	$tpl = file_get_contents(getcwd() . '/data/templates/default/default.tpl');

	$tpl = [
		"de" => $tpl,
		"en" => $tpl,
	];

	$query = $dm->createQueryBuilder("Metacope\Mcedit\Model\DocumentModel")
		->update()
		->multiple(true)
		->field('layout')->set($tpl)
		->getQuery()
		->execute();

	die("update success\n");
}
