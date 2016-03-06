<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Metacope\Mcedit;

use Metacope\Mcedit\ViewHelper\ObjectHelper;
use Zend\Console\Request as ConsoleRequest;
use Zend\Log\Logger as LogLogger;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as Mail;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container as Container;
use Zend\Session\SessionManager;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;

class Module {

	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig() {
		return [
			'Zend\Loader\StandardAutoloader' => [
				'namespaces' => [
					__NAMESPACE__=> __DIR__,
				],
			],
		];
	}

	public function onBootstrap(MvcEvent $e) {

		$serviceManager = $e->getApplication()->getserviceManager();
		$eventManager = $e->getApplication()->getEventManager();

		$config = $serviceManager->get('Configuration');

		$phpSettings = $config['phpSettings'];
		if ($phpSettings) {
			foreach ($phpSettings as $key => $value) {
				ini_set($key, $value);
			}
		}

		$array = [];
		foreach ($config['roles']['user'] as $k => $v) {

			$ukey = 'USR_' . strtoupper($k);
			$gkey = 'GRP_' . strtoupper($k);
			define("{$ukey}", $v);
			define("{$gkey}", $config['roles']['group'][$k]);

			$array[$ukey] = constant($ukey);
			$array[$gkey] = constant($gkey);
		}
		define('ROLES', \Zend\Json\Json::encode($array));

		$odm = $serviceManager->get('doctrine.documentmanager.odm_default');
		$odmConfig = $odm->getConfiguration();
		$odmConfig->setMetadataDriverImpl(\Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::create([getcwd() . '/vendor/metacope/mcedit/Model']));
		\Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

		$this->bootstrapSession($e);

		if (!($serviceManager->get('request') instanceof ConsoleRequest)) {
			if (apache_getenv('APP_ENV') != 'production') {
				require_once getcwd() . '/public/htaccess.php';
			}

			$serviceManager->get('viewhelpermanager')->setFactory('objecthelper', function ($sm) use ($e) {
				$viewHelper = new \Metacope\Mcedit\ViewHelper\ObjectHelper($e);
				return $viewHelper;
			});
			$serviceManager->get('viewhelpermanager')->setFactory('crypthelper', function ($sm) use ($e) {
				$viewHelper = new \Metacope\Mcedit\ViewHelper\CryptHelper($e);
				return $viewHelper;
			});

			$eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_ROUTE, [$this, 'onPreRoute'], 90);
			$eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 2);
			$eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'handleError'], 1);
			$eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER_ERROR, [$this, 'handleError'], 1);
		}

		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
	}

	public function onDispatch(\Zend\Mvc\MvcEvent $e) {

		$serviceManager = $e->getApplication()->getserviceManager();
		$sharedManager = $e->getApplication()->getEventManager()->getSharedManager();

		$router = $serviceManager->get('router');
		$request = $serviceManager->get('request');

		if (!$matchedRoute = $router->match($request)) {
			$matchedRoute = $e->getRouteMatch();
		}

		$sharedManager->attach('Zend\Mvc\Application', 'dispatch.debug', function ($e) use ($serviceManager) {
			$this->handleError($e);
		});
		$sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function ($e) use ($serviceManager, $matchedRoute) {

			$config = $serviceManager->get('Config');

			if ($matchedRoute instanceof \Zend\Mvc\Router\Http\RouteMatch) {
				$matchedRouteName = $matchedRoute->getMatchedRouteName();
			}

			$controller = $serviceManager->get('ControllerPluginManager')->getController();
			$controller->env = $GLOBALS['env'];
			$controller->cache = $serviceManager->get('cache');
			$controller->layout()->setVariable('config', $config);

			// attache session
			$session = new \Zend\Session\Container('default');
			$controller->session = $session;
			$controller->layout()->setVariable('session', $session);

			$documentmanager = $serviceManager->get('doctrine.documentmanager.odm_default');
			$controller->dm = $documentmanager;
			$controller->layout()->setVariable('dm', $documentmanager);

			// attache AuthenticationService
			$authentication = $serviceManager->get('Zend\Authentication\AuthenticationService');
			$controller->auth = $authentication;
			$controller->layout()->setVariable('auth', $authentication);

			// attache lang
			$this->lang = empty($this->lang) ? 'de' : $this->lang;
			$controller->lang = $this->lang;
			$controller->layout()->setVariable('lang', $this->lang);

			// attach $rbac
			$plugin = $serviceManager->get('ControllerPluginManager')->get('Rbac');

			$rbac = $plugin->init($serviceManager);
			$controller->rbac = $rbac;
			$controller->layout()->setVariable('rbac', $rbac);

			$plugin->doAuthorization($rbac, $e, $matchedRoute);

		}, 2);

	}

	public function onPreRoute(\Zend\Mvc\MvcEvent $e) {
		$sm = $e->getApplication()->getServiceManager();
		$router = $sm->get('router');

		$translator = $sm->get('translator');
		$translator->setFallbackLocale('en_GB');
		$this->lostInTranslation($e);

		$router->setTranslator($translator);

	}

	public function handleError($e) {
		$serviceManager = $e->getApplication()->getServiceManager();

		$controller = $e->getController();
		$logger = $serviceManager->get('Logger');
		$error = $e->getParam('error');
		$exception = $e->getParam('exception');

		$env = $GLOBALS['env'];

		$writer = new \Zend\Log\Writer\ChromePhp();
		$writer->addFilter(LogLogger::INFO);
		$logger->addWriter($writer);

		if ('production' == $env) {
			$mail = new \Zend\Mail\Message();
			$mail->setFrom('errors@' . $_SERVER['HTTP_HOST'])->addTo('joerg.mueller@metacope.com')->setSubject('Errors: WORK - ' . $env);

			$writer = new \Zend\Log\Writer\Mail($mail);
			$writer->addFilter(LogLogger::DEBUG);
			$logger->addWriter($writer);
		}
		LogLogger::registerErrorHandler($logger);

		$message = 'Error: ' . $error;
		if ($exception instanceof \Exception) {
			$message .= "\n " . 'Exception: ' . $exception->getMessage() .
			$exception->getTraceAsString();
			do {
				$c = LogLogger::WARN;
				$serviceManager->get('Logger')->log($c, sprintf("%s:%d %s (%d) [%s]\n", $exception->getFile(), $exception->getLine(), $exception->getMessage(), $exception->getCode(), get_class($exception)));
			} while ($exception = $exception->getPrevious());
		}

	}

	public function onError(\Zend\Mvc\MvcEvent $e) {

		// lets logging
		$env = $GLOBALS['env'];

		$serviceManager = $e->getApplication()->getServiceManager();
		$logger = $serviceManager->get('Logger');

		$writer = new \Zend\Log\Writer\FirePhp();
		$writer->addFilter(LogLogger::DEBUG);
		$logger->addWriter($writer);

		$writer = new \Zend\Log\Writer\ChromePhp();
		$writer->addFilter(LogLogger::DEBUG);
		$logger->addWriter($writer);

		if ('production' == $env) {
			$mail = new \Zend\Mail\Message();
			$mail->setFrom('errors@' . $_SERVER['HTTP_HOST'])->addTo('joerg.mueller@metacope.com')->setSubject('Errors: WORK - ' . $env);

			$writer = new \Zend\Log\Writer\Mail($mail);
			$writer->addFilter(LogLogger::DEBUG);
			$logger->addWriter($writer);
		}
		LogLogger::registerErrorHandler($logger);
	}

	public function bootstrapSession(\Zend\Mvc\MvcEvent $e) {

		$sessionManager = $e->getApplication()->getserviceManager()->get('Zend\Session\SessionManager');
		$sessionManager->getValidatorChain()->attach('session.validate', [
			new HttpUserAgent(),
			'isValid',
		]);
		$sessionManager->getValidatorChain()->attach('session.validate', [
			new RemoteAddr(),
			'isValid',
		]);

		Container::setDefaultManager($sessionManager);
		$session = new Container('default');
		$session->offsetSet('init', true);
		$sessionManager->start();
	}

	protected $lang;
	public function lostInTranslation(\Zend\Mvc\MvcEvent $e) {
		$serviceManager = $e->getApplication()->getserviceManager();

		$env = $GLOBALS['env'];
		$viewHelper = $e->getApplication()->getserviceManager()->get('ViewHelperManager');

		$session = new Container('default');
		$translator = $e->getApplication()->getserviceManager()->get('translator');

		$config = $e->getApplication()->getserviceManager()->get('Configuration');
		$localesConfig = $config['locales'];
		$localesList = $localesConfig['list'];

		$lang = $session->offsetExists('lang') ? $session->offsetGet('lang') : $localesConfig['defaults'];

		$router = $serviceManager->get('router');
		$request = $serviceManager->get('request');

		if (!$match = $router->match($request)) {
			$match = $e->getRouteMatch();
		}

		if ($match) {
			$lang = $match->getParam('lang', $lang);
		}

		$locale = $localesList[$lang]['locale'];
		$currency = $localesList[$lang]['currency'];

		$translator->setLocale($locale);
		$viewHelper->get('currencyFormat')->setCurrencyCode($currency)->setLocale($locale);
		$viewHelper->get('dateFormat')->setTimezone($localesList[$lang]['timezone'])->setLocale($locale);

		$session->offsetSet('locale', $locale);
		$session->offsetSet('lang', $lang);
		$session->offsetSet('APP_ENV', $env);

		setlocale(LC_ALL, $locale);

		$this->lang = $lang;
		return $lang;
	}

	public function getServiceConfig() {
		return [
			'factories' => [
				'Zend\Authentication\AuthenticationService' => function ($sm) {
					return $sm->get('doctrine.authenticationservice.odm_default');
				},
				'Logger' => function ($sm) {
					$logger = new \Zend\Log\Logger();
					if (!file_exists(getcwd() . '/data/logs')) {
						mkdir(getcwd() . '/data/logs', 0777, true);
					}

					$writer = new \Zend\Log\Writer\Stream(getcwd() . '/data/logs/' . date('Y-m-d') . '-debug.log');
					$writer->addFilter(\Zend\Log\Logger::DEBUG);
					$writer->setLogSeparator("\n" . str_repeat('#', 24) . "\n");
					$logger->addWriter($writer);
					return $logger;
				},

				'Zend\Session\SessionManager' => function ($sm) {
					$config = $sm->get('config');
					if (isset($config['session'])) {
						$session = $config['session'];

						$sessionConfig = null;
						if (isset($session['config'])) {
							$class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
							$options = isset($session['config']['options']) ? $session['config']['options'] : [];
							$sessionConfig = new $class();
							$sessionConfig->setOptions($options);
						}

						$sessionStorage = null;
						if (isset($session['storage']) && strlen(trim($session['storage']))) {
							$class = $session['storage'];
							$sessionStorage = new $class();
						}

						$sessionSaveHandler = null;
						if (isset($session['save_handler']) && strlen(trim($session['save_handler']))) {
							$sessionSaveHandler = $sm->get($session['save_handler']);
						}

						$sessionManager = new \Zend\Session\SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
					} else {
						$sessionManager = new \Zend\Session\SessionManager();
					}
					\Zend\Session\Container::setDefaultManager($sessionManager);
					return $sessionManager;
				},
			],
		];
	}
}
