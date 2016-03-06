<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

$env = 'local';
if (function_exists('apache_getenv')) {
	$env = apache_getenv('APP_ENV');
}

return [
	'controllers' => [
		'invokables' => [
			'Metacope\Mcedit\Controller\Ajax' => 'Metacope\Mcedit\Controller\AjaxController',
			'Metacope\Mcedit\Controller\Index' => 'Metacope\Mcedit\Controller\IndexController',
			'Metacope\Mcedit\Controller\Developer' => 'Metacope\Mcedit\Controller\DeveloperController',
			'Metacope\Mcedit\Controller\Document' => 'Metacope\Mcedit\Controller\DocumentController',
			'Metacope\Mcedit\Controller\User' => 'Metacope\Mcedit\Controller\UserController',
			'Metacope\Mcedit\Controller\Widget' => 'Metacope\Mcedit\Controller\WidgetController',
			'Metacope\Mcedit\Controller\Images' => 'Metacope\Mcedit\Controller\ImagesController',
			'Metacope\Mcedit\Controller\Client' => 'Metacope\Mcedit\Controller\ClientController',
		],
	],
	'router' => [
		'routes' => [
			'mcedit' => [
				'type' => 'Zend\Mvc\Router\Http\Hostname',
				'options' => [
					'route' => ':subdomain.[:domain]',
					'constraints' => [
						'subdomain' => 'www|edit|stage|preview',
						'domain' => '(.*)',
					],
					'defaults' => [
						'__NAMESPACE__' => 'Metacope\Mcedit\Controller',
						'controller' => 'Index',
						'action' => 'view',
						'role' => 'guest',
					],
				],
				'may_terminate' => true,
				'child_routes' => [
					'edit' => [
						'type' => 'Segment',
						'options' => [
							'route' => '/mc[/:controller[/:action]]',
							'constraints' => [
								'subdomain' => 'edit|preview',
							],
							'defaults' => [
								'__NAMESPACE__' => 'Metacope\Mcedit\Controller',
								'controller' => 'Developer',
								'action' => 'index',
								'role' => 'guest',
							],
						],
						'may_terminate' => true,
						'child_routes' => [
							'wildcard' => [
								'type' => 'Wildcard',
							],
						],
					],
					'login' => [
						'type' => 'Literal',
						'options' => [
							'route' => '/login',
							'defaults' => [
								'__NAMESPACE__' => 'Metacope\Mcedit\Controller',
								'controller' => 'User',
								'action' => 'login',
								'role' => 'guest',
							],
						],
						'may_terminate' => true,
						'child_routes' => [
							'wildcard' => [
								'type' => 'Wildcard',
							],
						],
					],
					'logout' => [
						'type' => 'Literal',
						'options' => [
							'route' => '/logout',
							'defaults' => [
								'__NAMESPACE__' => 'Metacope\Mcedit\Controller',
								'controller' => 'User',
								'action' => 'logout',
								'role' => 'user',
							],
						],
						'may_terminate' => true,
						'child_routes' => [
							'wildcard' => [
								'type' => 'Wildcard',
							],
						],
					],
					'404' => [
						'type' => 'Literal',
						'options' => [
							'route' => '/check404',
							'defaults' => [
								'__NAMESPACE__' => 'Metacope\Mcedit\Controller',
								'controller' => 'Index',
								'action' => 'check404',
								'role' => 'guest',
							],
						],
						'may_terminate' => true,
						'child_routes' => [
							'wildcard' => [
								'type' => 'Wildcard',
							],
						],
					],
					'pages' => [
						'type' => 'Segment',
						'options' => [
							'route' => '/[:lang[/:pageuri]]',
							'constraints' => [
								'pageuri' => '[a-zA-Z0-9-_,\.\/]+',
								'lang' => '(de|en|it|zh|ja)',
							],
							'defaults' => [
								'__NAMESPACE__' => 'Metacope\Mcedit\Controller',
								'controller' => 'Index',
								'action' => 'view',
								'pageuri' => '/',
								'role' => 'guest',
								'lang' => 'de',
							],
						],
					],
				],
			],

		],
	],
	'view_manager' => [
		'display_not_found_reason' => ('production' != $env),
		'display_exceptions' => ('production' != $env),
		'doctype' => 'HTML5',
		'not_found_template' => 'error/404_' . $env,
		'exception_template' => 'error/index',
		'template_map' => [
			'mcedit/layout' => __DIR__ . '/../view/layout/layout.phtml',
			'metacope/controller/index/view' => __DIR__ . '/../view/mcedit/index/view.phtml',
			'mcedit/client/index' => __DIR__ . '/../view/mcedit/client/index.phtml',
			'mcedit/user/index' => __DIR__ . '/../view/mcedit/user/index.phtml',
			'mcedit/user/set' => __DIR__ . '/../view/mcedit/user/set.phtml',
			'header' => __DIR__ . '/../view/partials/header.phtml',
			'modalhelper' => __DIR__ . '/../view/partials/modalhelper.phtml',
			'searchfield' => __DIR__ . '/../view/partials/editor/searchfield.phtml',
			'dublincore' => __DIR__ . '/../view/partials/dublin-core.phtml',
			'topnavigation' => __DIR__ . '/../view/partials/topnavigation.phtml',
			'userFormShort' => __DIR__ . '/../view/partials/user.form.short.login.phtml',
			'finder' => __DIR__ . '/../view/partials/finder.phtml',
			'2colSearch' => __DIR__ . '/../view/partials/2colSearch.phtml',
		],
		'template_path_stack' => [
			__DIR__ . '/../view',
			realpath(__DIR__ . '/../view'),
			realpath(__DIR__ . '/../view/mcedit'),
		],
		'prefix_template_path_stack' => [
			realpath(__DIR__ . '/../view'),
			realpath(__DIR__ . '/../view/mcedit'),
		],
		'strategies' => [
			'ViewJsonStrategy',
		],
	],
	'controller_plugins' => [
		'invokables' => [
			'Rbac' => 'Metacope\Mcedit\Plugin\MetacopeRbacPlugin',
		],
	],
	'asset_bundle' => [
		'assets' => [
			'less' => ['@zfRootPath/vendor/twitter/bootstrap/less/bootstrap.less'],
		],
	],
];
