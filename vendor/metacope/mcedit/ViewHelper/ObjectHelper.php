<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\ViewHelper;

use Zend\Mvc\MvcEvent;
use Zend\View\Helper\AbstractHelper;

class ObjectHelper extends AbstractHelper {

	protected $routeMatch;
	protected $router;
	protected $e;
	protected $dm;
	public $acl;
	public $servicemanager;

	public function __construct(MvcEvent $e) {
		$this->servicemanager = $e->getApplication()->getServiceManager();
		$this->dm = $this->servicemanager->get('doctrine.documentmanager.odm_default');
		$this->rbac = $this->servicemanager->get('ControllerPluginManager')->get('Rbac');
		$this->rbac = $this->rbac->init();

		$this->e = $e;
		if ($e->getRouteMatch()) {
			$this->routeMatch = $e->getRouteMatch();
		}

	}

	public function __invoke() {
		return $this;
	}

	public function getDocumentManager() {
		return $this->dm;
	}

	public function getServiceManager() {
		return $this->servicemanager;
	}

	public function getCache() {
		return $this->e->getApplication()->getServiceManager()->get('cache');
	}

	public function getRouteInfo() {
		if ($this->routeMatch) {
			$controller = $this->routeMatch->getParams();
			return $controller;
		} else {
			return 'unknow controller';
		}

	}

	public function getRequest() {
		return $this->e->getApplication()->getServiceManager()->get('request');
	}

	public function getServiceLocator() {
		return $this->e->getApplication()->getServiceManager();
	}

	public function getRoleManagement() {
		if ($this->rbac) {
			return $this->rbac;
		}

		$this->rbac = $this->e->getApplication()->getServiceManager()->get('ControllerPluginManager')->get('Rbac');
		$this->rbac->init();

		return $this->rbac;
	}
}
