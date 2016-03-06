#!/usr/bin/env php
<?php

ini_set('memory_limit', '5120M');
ini_set('max_execution_time', 60000000);

chdir(dirname(__DIR__));

$env = 'preview';
if (function_exists('apache_getenv')) {
	$env = apache_getenv('APP_ENV');
}

include 'data/cli.php';
include 'init_autoloader.php';

$app = Zend\Mvc\Application::init(require 'config/application.config.php');
$dm = $app->getServiceManager()->get('doctrine.documentmanager.odm_default');

$widgets = $dm->createQueryBuilder("Metacope\Mcedit\Model\WidgetModel")
// ->update()
// ->multiple(true)
	->field('type.type')->exists(true)
// ->set('type', 'article_inline')
	->getQuery()
	->execute();

while ($widget = $widgets->getNext()) {
	$widget->setType('article_inline');
	$dm->flush($widget);

}