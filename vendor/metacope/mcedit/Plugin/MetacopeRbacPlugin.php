<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;
use Zend\Session\Container as Container;

class MetacopeRbacPlugin extends AbstractPlugin
{

    protected $acl;
    public $domain;

    public function init()
    {
        $this->domain = preg_replace("/^([a-zA-Z0-9]+\.)/i", '', $_SERVER['HTTP_HOST']);

        $this->rbac = new Rbac();
        $lastrole = false;

        $roles = [
            'guest' => ['guest']
            , 'user' => ['user', 'logout']
            , 'employee' => ['employee']
            , 'editor' => ['editor']
            , 'chiefeditor' => ['chiefeditor']
            , 'clientmanager' => ['clientmanager']
            , 'clientleader' => ['clientleader']
            , 'admin' => ['admin']
            , 'developer' => ['developer']
            , 'urml' => ['urml'],
        ];

        $roles = new \ArrayIterator($roles);
        foreach ($roles as $offset => $entry) {

            $role = new Role($offset);

            while ($permission = array_shift($entry)) {
                $role->addPermission($permission);
            }

            if ($lastrole) {
                $role->addChild($lastrole);
            }

            $this->rbac->addRole($role);
            $lastrole = $role;
        }

        return $this->rbac;
    }

    public function doAuthorization($rbac, $e, $matchedRoute)
    {

        if ($matchedRoute instanceof \Zend\Mvc\Router\Http\RouteMatch) {
            $matchedRouteName = $matchedRoute->getMatchedRouteName();
        }

        $matchedRouteName = isset($matchedRouteName) && is_string($matchedRouteName)
        ? $matchedRouteName
        : ($matchedRoute ? $matchedRoute->getMatchedRouteName() : 'Mcedit/pages');

        $matchedAction = $matchedRoute ? $matchedRoute->getParam('action', 'index') : 'index';
        if ($matchedRoute->getParam('__NAMESPACE__')) {
            $matchedStr = implode('\\', [$matchedRoute->getParam('__NAMESPACE__'), $matchedRoute->getParam('controller')]);
        } else {
            $matchedStr = $matchedRoute->getParam('controller');
        }

        $routeRole = $matchedRoute->getParam('role', 'guest');

        $servicemanager = $e->getApplication()->getServiceManager();
        $session = new \Zend\Session\Container('default');
        $lang = $session->offsetExists('lang') ? $session->offsetGet('lang') : 'de';

        $router = $servicemanager->get('router');
        $controller = $e->getTarget();
        $role = $session->offsetExists('role') ? $session->offsetGet('role') : 'guest';

        if ($rbac->getRole($role)->hasPermission($routeRole) == true) {
            return;
        }

        if ($rbac->getRole($role)->hasPermission($matchedAction) == false
            && $rbac->getRole($role)->hasPermission($matchedRouteName) == false
            && $rbac->getRole($role)->hasPermission($matchedStr) == false) {

            $response = $servicemanager->get('response');
            $response->setStatusCode(302);
            $url = $router->assemble(['lang' => $lang, 'subdomain' => 'edit', 'domain' => $matchedRoute->getParam('domain')], ['name' => 'mcedit']) . '#/login';

            /*
             * redirect to login
             */
            $response->getHeaders()->addHeaderLine('Location', $url);
        }

    }
}
