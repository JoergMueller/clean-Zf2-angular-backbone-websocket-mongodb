<?php

return [
	'doctrine' => [
		'connection' => [
			'odm_default' => [
				'server' => 'server',
				'port' => 27017,
				'dbname' => 'database',
				'user' => '',
				'password' => '',
				'options' => ['socketTimeoutMS' => 600, 'connectTimeoutMS' => 22000],
			],
		],
		'configuration' => [
			'odm_default' => [
				'metadata_cache' => 'filesystem',
				'driver' => 'odm_default',
				'generate_proxies' => true,
				'proxy_dir' => getcwd() . '/data/DoctrineMongoODMModule/Proxy',
				'proxy_namespace' => 'DoctrineMongoODMModule\Proxy',
				'generate_hydrators' => true,
				'hydrator_dir' => getcwd() . '/data/DoctrineMongoODMModule/Hydrator',
				'hydrator_namespace' => 'DoctrineMongoODMModule\Hydrator',
				'default_db' => 'database',
				'retryConnect' => 3,
				'filters' => [],
			],
			'filesystem' => [
				'dirLevel' => 2,
				'cacheDir' => getcwd() . '/data/cache',
				'dirPermission' => 0755,
				'filePermission' => 0666,
				'namespaceSeparator' => '-db-',
				'ttl' => (60 * 5),
			],
		],
		'driver' => [
			'odm_driver' => [
				'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
				'cache' => 'filesystem',
				'paths' => [
					getcwd() . '/vendor/metacope/mcedit/Model',
				],
			],
			'odm_default' => [
				'drivers' => [
					'Model' => 'odm_driver',
				],
			],
		],
		'documentmanager' => [
			'odm_default' => [
				'connection' => 'odm_default',
				'configuration' => 'odm_default',
				'eventmanager' => 'odm_default',
			],
		],
		'authentication' => [
			'odm_default' => [
				'objectManager' => 'doctrine.documentmanager.odm_default',
				'identityClass' => 'Metacope\Mcedit\Model\UserModel',
				'identityProperty' => 'nickname',
				'credentialProperty' => 'password',
				'credential_callable' => function (Metacope\Mcedit\Model\UserModel $user, $password) {
					if ($user->getPassword() === $password && $user->getVisible() === 1) {
						return true;
					}
					return false;
				},
			],
		],
	],
];
