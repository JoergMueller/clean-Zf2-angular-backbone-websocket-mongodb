<?php

$env = 'local';
if (function_exists('apache_getenv')) {
	$env = apache_getenv('APP_ENV');
}

return [
	'page' => [
		'title' => '',
		'description' => '',
		'keywords' => '',
		'latlng' => '',
		'indexfollow' => 'index, follow',
		'georeverse' => 'Trier',
	],
	'roles' => [
		'user' => [
			'urml' => 1,
			'developer' => 2,
			'admin' => 4,
			'clientleader' => 8,
			'clientmanager' => 16,
			'chiefeditor' => 32,
			'editor' => 64,
			'employee' => 128,
			'user' => 256,
			'guest' => 512,
		],
		'group' => [
			'urml' => 1,
			'developer' => (1 + 2),
			'admin' => (3 + 4),
			'clientleader' => (7 + 8),
			'clientmanager' => (15 + 16),
			'chiefeditor' => (31 + 32),
			'editor' => (63 + 64),
			'employee' => (127 + 128),
			'user' => (255 + 256),
			'guest' => (511 + 512),
		],
	],
	'locales' => [
		'defaults' => 'de',
		'list' => [
			'en' => [
				'short' => 'en',
				'locale' => 'en_GB',
				'name' => 'eglisch',
				'currency' => 'GBP',
				'timezone' => 'Europe/London',
				'fallbackurllang' => 'en',
			],
			'de' => [
				'short' => 'de',
				'locale' => 'de_DE',
				'name' => 'deutsch',
				'currency' => 'EUR',
				'timezone' => 'Europe/Berlin',
				'fallbackurllang' => 'de',
			],
		],
	],
	'phpSettings' => [
		'display_startup_errors' => true,
		'display_errors' => true,
		'max_execution_time' => 60000000,
		'memory_limit' => '5120M',
		'date.timezone' => 'Europe/Berlin',
	],
	'defaultClient' => [
		'token' => '',
	],
	'session' => [
		'config' => [
			'class' => 'Zend\Session\Config\SessionConfig',
			'options' => [
				'remember_me_seconds' => 60 * 60 * 24 * 365 * 10,
				'use_cookies' => true,
				'cookie_httponly' => true,
				'name' => 'mSID',
				'cookie_lifetime' => 60 * 60 * 24 * 365,
			],
		],
		'validators' => [
			'Zend\Session\Validator\RemoteAddr',
			'Zend\Session\Validator\HttpUserAgent',
		],
	],
	'service_manager' => [
		'abstract_factories' => [
			'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
			'Zend\Log\LoggerAbstractServiceFactory',
		],
		'aliases' => [
			'translator' => 'MvcTranslator',
		],
	],
	'translator' => [
		'locale' => 'de_DE',
		'translation_file_patterns' => [
			[
				'type' => 'gettext',
				'base_dir' => getcwd() . '/language',
				'pattern' => '%s.mo',
			],
		],
	],
	'router' => [
		'router_class' => 'Zend\Mvc\Router\Http\TranslatorAwareTreeRouteStack',
		'routes' => [],
	],
];
