<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Controller;

use Metacope\Mcedit\Model\UserModel;
use Metacope\Mcedit\Model\UserSheetModel;
use \Zend\Mvc\Controller\AbstractActionController;
use \Zend\View\Model\JsonModel;
use \Zend\View\Model\ViewModel;

class UserController extends AbstractActionController {

	public function indexAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'session' => $session,
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
		];
		$this->layout()->setVariables($viewParams);
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		$view = new ViewModel();

		$view->setTerminal(true)
			->setTemplate('mcedit/user/index');

		return $view;
	}

	public function setAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'auth' => $this->auth,
			'config' => $config,
		];
		$this->layout()->setVariables($viewParams);
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		/*
			         * if token by query or by post ... edit
			         * if id per route new document
		*/
		$requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());

		$token = $this->params()->fromQuery('token')
		? $this->params()->fromQuery('token')
		: ($this->params()->fromPost('token') ? $this->params()->fromPost('token')
			: ($this->params()->fromRoute('token') ? $this->params()->fromRoute('token')
				: null));

		$foundHasFailed = false;
		if ($token) {
			if (!$user = $this->dm->getRepository("Metacope\Mcedit\Model\UserModel")->findOneBy(['token' => $token])) {
				$foundHasFailed = true;
			}
		}

		if (!$token || true == $foundHasFailed) {

			$parent = false;
			if ($this->params()->fromRoute('id')) {
				$parent = $this->dm->getRepository("Metacope\Mcedit\Model\UserModel")->find($this->params()->fromRoute('id'));
			}

			/**
			 * UserSheet
			 */
			$usersheet = new UserSheetModel();
			$usersheet->setFirstname('Jörg');
			$usersheet->setName('Müller');
			$usersheet->setGender('mr');
			$usersheet->setCity('Maintal Dörnigheim');
			$usersheet->setZipcode('63477');
			$usersheet->setStreetnr('Kennedystraße 25');

			/**
			 * user
			 */
			$user = new UserModel();
			$user->setNickname('demoNickname' . time());
			$user->setPassword($user->getNickname());
			$user->setEmail($user->getNickname() . '@demouser.com');

			$user->setSheet($usersheet);

			if ($parent && $parent instanceof UserModel) {
				$user->setParent($parent);
			}

			if (isset($requestparams['create'])) {
				$this->dm->persist($user);
				$this->dm->flush();
				return new \Zend\View\Model\JsonModel($user->toArray());
			}

		}

		$request = $this->getRequest();
		if ($request->isPost() == true) {
			$fromPost = $this->params()->fromPost();

			$fromPost['groups'] = isset($fromPost['groups']) && is_array($fromPost['groups'])
			? $fromPost['groups']
			: [];

			$sheet = $user->getSheet();
			$sheet->setFirstname($fromPost['firstname']);
			$sheet->setName($fromPost['name']);
			$sheet->setZipcode($fromPost['zipcode']);
			$sheet->setCity($fromPost['city']);
			$sheet->setStreetnr($fromPost['streetnr']);
			$sheet->setGender($fromPost['gender']);
			$sheet->setTeaminfo($fromPost['teaminfo']);

			if (isset($fromPost['password']) && strlen(trim($fromPost['password']))) {
				$user->setPassword(md5($fromPost['password']));
			}
			$user->setEmail($fromPost['email']);

			$criteria = [
				'nickname' => $fromPost['nickname'],
				'token' => ['$not' => $user->getToken()],
			];
			if (!$_tuser = $this->dm->getRepository("Metacope\Mcedit\Model\UserModel")->find($criteria)) {
				$user->setNickname($fromPost['nickname']);
			}

			$user->setRole($fromPost['role']);
			$user->setGroups($fromPost['groups']);

			$user->setSheet($sheet);

			$this->dm->persist($user);
			$this->dm->flush();

			$content = $translator->translate('Update Success');
			return new \Zend\View\Model\JsonModel([
				'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
				'user' => $user->toArray(),
			]);
		}

		$viewParams['user'] = $user;
		$this->layout()->setVariables($viewParams);

		$view = new \Zend\View\Model\ViewModel($viewParams);
		$view->setTerminal(true)
			->setTemplate('mcedit/user/set');

		return $view;

	}

	public function loginAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'session' => $session,
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
		];
		$this->layout()->setVariables($viewParams);
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		$data = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());
		$request = $this->getRequest();

		$view = new \Zend\View\Model\ViewModel($viewParams);

		if ($request->isPost() || (isset($data['u']) && isset($data['p']))) {
			$login = isset($data['u']) ? $data['u'] : (isset($data['login']) ? $data['login'] : false);
			$password = isset($data['p']) ? $data['p'] : (isset($data['password']) ? $data['password'] : false);
			if ($login && $password) {
				$adapter = $this->auth->getAdapter();

				if (false !== strpos($login, '@')) {
					$adapter->getOptions()->setIdentityProperty('email');
				} else {
					$adapter->getOptions()->setIdentityProperty('nickname');
				}

				$adapter->setIdentityValue($login);
				$adapter->setCredentialValue(md5($password));
				$authResult = $this->auth->authenticate();

				if ($authResult->isValid()) {

					$identity = $authResult->getIdentity();
					$identity->setLastlogin(new \DateTime());

					$session = new \Zend\Session\Container('default');
					$session->offsetSet('role', $identity->getRole());

					$this->dm->persist($identity);
					$this->dm->flush();

					$this->auth->getStorage()->write($identity);

					if (isset($data['remember']) && 'remember' == $data['remember']) {
						$sessionManager = new \Zend\Session\SessionManager();
						$sessionManager->rememberMe();
					}

					if ($request->isXmlHttpRequest()) {

						$content = sprintf($translator->translate('Welcome %s back'), $identity->getNickname());
						return new \Zend\View\Model\JsonModel([
							'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
						]);
					}

					$content = sprintf($translator->translate('Welcome, %s is back'), $identity->getNickname());
					return new \Zend\View\Model\JsonModel([
						'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
					]);
				}
				return new \Zend\View\Model\JsonModel(['status' => 'error', 'msg' => 'username or password wrong']);
			}
			$identity = $this->identity();

			if ($request->isXmlHttpRequest()) {

				return new \Zend\View\Model\JsonModel($identity ? $identity->toArray() : []);

				$view->setTerminal(true);
				$content = sprintf($translator->translate('Welcome %s you are currently loggedin.'), $identity->getNickname());

				return new \Zend\View\Model\JsonModel([
					'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
				]);
			}
			return new \Zend\View\Model\JsonModel($identity->toArray());
		}
		return $view;
	}

	public function logoutAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'session' => $session,
			'lang' => $this->lang,
			'rbac' => $this->rbac,
			'auth' => $this->auth,
		];
		$this->layout()->setVariables($viewParams);

		if ($this->auth->hasIdentity()) {
			$identity = $this->auth->getIdentity();
			$this->auth->clearIdentity();
		}
		$sessionManager = new \Zend\Session\SessionManager();
		$sessionManager->forgetMe();

		$session->offsetSet('role', 'guest');
		header('Location: /');
	}
}
