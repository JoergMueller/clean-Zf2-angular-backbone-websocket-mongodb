#!/usr/bin/env php
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

// Tours, Food & Drinks, Attractions, Services, Activities, Shopping, Nightlife
// Touren,Essen & Trinken,Attraktionen,Dienstleistungen,Aktivitäten,Einkaufen,Nachtleben
// About, Planning a Trip, Getting Around, History & Culture, Top Attractions, Travel Resources, Highlights, Events, Itineraries
// Über, Eine Ausflug planen, Herumkommen, Geschichte & Kultur, Top Sehenswürdigkeiten, Reise-Informationen, Highlights, Veranstaltungen, Rundfahrten

$path = askConsole("Path of Parent Document: [/]", "/");
$folders = askConsole("Folders Commaseparated:", "Über, Einen Ausflug planen, Herumkommen, Geschichte & Kultur, Top Sehenswürdigkeiten, Reise-Informationen, Highlights, Veranstaltungen, Rundfahrten");
$folders = explode(',', $folders);

$parent = $dm->getRepository("Metacope\Mcedit\Model\DocumentModel")->findOneBy([
	'path.de' => '/entdecken/staedte',
]);
$children = $parent->getChildList([], $dm);

$tpl = file_get_contents(getcwd() . '/data/templates/default/default.tpl');

foreach ($children as $child) {

	$_parent = $dm->getRepository("Metacope\Mcedit\Model\DocumentModel")->findOneBy([
		'path.de' => $child->getPath('de') . '/informationen',
	]);

	$sort = 1;
	foreach ($folders as $folder) {

		$config = $app->getServiceManager()->get('Config');

		$langs = array_keys($config['locales']['list']);

		$tpl = file_get_contents(getcwd() . '/data/templates/default/default.tpl');
		$document = new \Metacope\Mcedit\Model\DocumentModel();
		$sheet = new \Metacope\Mcedit\Model\DocumentSheetModel();

		$document->setParent($_parent);
		$document->setCoordinates($child->getCoordinates());
		$document->setDocumentManager($dm);
		$document->setInlanguage($langs);
		$document->setVisible(1);
		$document->setSort($sort);

		// $document->setParent($parent);

		foreach ($langs as $lang) {
			$document->setStructname(trim($folder), $lang);
			$document->setLayout($tpl, $lang);

			$sheet->setTitle(trim($folder), $lang);
			$sheet->setDescription('', $lang);
			$sheet->setIndexfollow('follow', $lang);
			$sheet->setKeywords([], $lang);
		}

		$document->setSheet($sheet);

		$path = $document->generateUrl('de');
		$path = preg_replace("/[\/]+/s", '/', $path);

		foreach ($langs as $lang) {
			$document->setPath($path, $lang);
		}

		$dm->persist($document);
		$dm->flush();

		print $document->getPath('de') . "\n";

		$sort++;
	}

}
