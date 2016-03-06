<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Controller;

use Metacope\Mcedit\Controller\Form\ClientForm;
use Metacope\Mcedit\Controller\Form\UserForm;
use \Zend\Mvc\Controller\AbstractActionController;
use \Zend\View\Model\JsonModel;
use \Zend\View\Model\ViewModel;

class DeveloperController extends AbstractActionController {
	public function indexAction() {
		return new ViewModel();
	}

	public function documentAction() {
		return $this->forward()->dispatch('Metacope\Mcedit\Controller\Document', [
			'action' => 'set',
		]);
	}

	public function updateLayoutAction() {

		$config = $this->getServiceLocator()->get('Config');
		$langs = array_keys($config['locales']['list']);

		$inclSubtpl = $this->params()->fromQuery('subtpl', false);

		$tpl = file_get_contents(getcwd() . '/data/templates/default/' . $this->params()->fromQuery('tpl', 'default.tpl'));
		$subtpl = file_get_contents(getcwd() . '/data/templates/default/subnav.tpl');

		$qb = $this->dm->createQueryBuilder("Metacope\Mcedit\Model\DocumentModel");
		$qb->update();

		if ($token = $this->params()->fromQuery('entry')) {
			$parent = $this->dm->getRepository("\Metacope\Mcedit\Model\DocumentModel")->findOneBy(['token' => $token]);
			$parent->setDocumentManager($this->dm);
			$children = $parent->getChildIds();
			$wherein = (array) $children->offsetGet('_ids');

			$qb->field('id')->in($wherein);

		}

		if ($token = $this->params()->fromQuery('token')) {
			$qb->field('token')->equals($token);
		} else {
			$qb->multiple(true);
		}

		$qb->field('authors')->set([]);

		if ($lang = $this->params()->fromQuery('lang')) {
			$qb->field("layout.{$lang}")->set($tpl);

			if (true === $inclSubtpl) {
				$qb->field("subnavlayout.{$lang}")->set($subtpl);
			}
		} else {
			foreach ($langs as $lang) {
				$qb->field('layout')->set(['de' => $tpl, 'en' => $tpl]);

				if (true === $inclSubtpl) {
					$qb->field('subnavlayout')->set(['de' => $subtpl, 'en' => $subtpl]);
				}

			}
		}
		$qb->getQuery()->execute();

		return new JsonModel([]);
	}

	public function prettifyCoordsAction() {
		$config = $this->getServiceLocator()->get('Config');
		$destinations = [];

		$qb = $this->dm->createQueryBuilder("\Metacope\Mcedit\Model\DocumentModel");
		$qb->update();

		// single dokument update
		if ($token = $this->params()->fromQuery('token')) {
			$qb->field('token')->equals($token);
		} else {
			$qb->multiple(true);
		}

		// multiple update by entry-point
		if ($token = $this->params()->fromQuery('entry')) {

			$parent = $this->dm->getRepository("\Metacope\Mcedit\Model\DocumentModel")->findOneBy(['token' => $token]);
			$parent->setDocumentManager($this->dm);
			$children = $parent->getChildIds();
			$wherein = (array) $children->offsetGet('_ids');

			$qb->field('id')->in($wherein);
		}
		// $qb->field('georeverse')->equals(new \MongoRegex('/.*(maintal|frankfurt).*/i'));
		$qb->field('georeverse')->set($config['page']['georeverse']);

		$qb->getQuery()->execute();
		return new JsonModel([]);
	}

	public function createDocumentAction() {

		$config = $this->getServiceLocator()->get('Config');

		$langs = array_keys($config['locales']['list']);

		$tpl = file_get_contents(getcwd() . '/data/templates/default/default.tpl');
		$document = new \Metacope\Mcedit\Model\DocumentModel();
		$sheet = new \Metacope\Mcedit\Model\DocumentSheetModel();

		$document->setDocumentManager($this->dm);
		$document->setInlanguage($langs);

		// $document->setParent($parent);

		foreach ($langs as $lang) {
			$document->setStructname('New-Site', $lang);
			$document->setLayout($tpl, $lang);

			$sheet->setTitle('New-Site', $lang);
			$sheet->setDescription('', $lang);
			$sheet->setIndexfollow('follow', $lang);
			$sheet->setKeywords([], $lang);
		}

		$document->setSheet($sheet);

		$path = $document->generateUrl(false, $parent, $sheet);
		$path = preg_replace("/[\/]+/s", '/', $path);

		foreach ($langs as $lang) {
			$document->setPath($path, $lang);
		}

		$this->dm->persist($document);
		$this->dm->flush();

	}

	public function initCountriesAction() {

		$file = getcwd() . '/data/templates/Country.json';

		if (file_exists($file)) {

			$this->dm->createQueryBuilder("\Metacope\Mcedit\Model\CountryModel")->remove()->getQuery()->execute();
			if ($c = $this->dm->getRepository("\Metacope\Mcedit\Model\CountryModel")->find($this->params()->fromPost('country'))) {
				$client->setCountry($c);
			}

			$lines = file($file);

			foreach ($lines as $line) {
				$entry = (array) \Zend\Json\Json::decode($line);

				$c = new \Metacope\Mcedit\Model\CountryModel();
				$c->setTitle($entry['title_de'], 'de');
				$c->setTitle($entry['title_en'], 'en');
				$c->setIso($entry['iso']);

				$this->dm->persist($c);
				$this->dm->flush();
			}
		}
		$this->redirect()->toUrl('/');

	}

	public function clientAction() {
		$identity = $this->identity();
		$this->config = $this->getServiceLocator()->get('Config');

		$params = array_merge(
			$this->params()->fromRoute(),
			$this->params()->fromPost(),
			$this->params()->fromQuery()
		);
		$this->config = $config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'auth' => $this->auth,
			'params' => $params,
		];
		$this->layout()->setVariables($viewParams);
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		$clientForm = new \Metacope\Mcedit\Controller\Form\ClientForm('ClientModel', ['c' => $this]);

		$client = false;
		if ($this->params()->fromRoute('id')) {
			if ($client = $this->dm->getRepository("Metacope\Mcedit\Model\ClientModel")->find($this->params()->fromRoute('id'))) {
				$viewParams['client'] = $client;
				$clientForm->setData($client->toArray());
			}
		} else {
			$client = new \Metacope\Mcedit\Model\ClientModel();
		}

		$request = $this->getRequest();
		if (true == $request->isPost()) {

			$clientForm->setData($this->params()->fromPost());
			if (true == $clientForm->isValid()) {
				if ($c = $this->dm->getRepository("\Metacope\Mcedit\Model\CountryModel")->find($this->params()->fromPost('country'))) {
					$client->setCountry($c);
				}
				$client->setShortname($this->params()->fromPost('shortname'));
				$client->setName($this->params()->fromPost('name'));
				$client->setFirm($this->params()->fromPost('firm'));
				$client->setZipcode($this->params()->fromPost('zipcode'));
				$client->setCity($this->params()->fromPost('city'));
				$client->setStreetnr($this->params()->fromPost('streetnr'));
				$client->setPhone($this->params()->fromPost('phone'));
				$client->setMail($this->params()->fromPost('mail'));

				$this->dm->persist($client);
				$this->dm->flush();
			}

		}

		$viewParams['clientForm'] = $clientForm;

		$view = new ViewModel($viewParams);
		$view->setTemplate('mcedit/developer/client');
		return $view;
	}

	public function userAction() {
		$identity = $this->identity();
		$this->config = $this->getServiceLocator()->get('Config');

		$params = array_merge(
			$this->params()->fromRoute(),
			$this->params()->fromPost(),
			$this->params()->fromQuery()
		);
		$this->config = $config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'auth' => $this->auth,
			'params' => $params,
		];
		$this->layout()->setVariables($viewParams);
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		$userForm = new UserForm('UserModel', ['c' => $this]);

		$request = $this->getRequest();
		if (true == $request->isPost()) {

			$fromPost = $this->params()->fromPost();
			$userForm->setData($fromPost);

			$user = new \Metacope\Mcedit\Model\UserModel();
			$sheet = new \Metacope\Mcedit\Model\UserSheetModel();

			$sheet->setFirstname($fromPost['sheet']['firstname']);
			$sheet->setName($fromPost['sheet']['name']);
			$sheet->setZipcode($fromPost['sheet']['zipcode']);
			$sheet->setCity($fromPost['sheet']['city']);
			$sheet->setStreetnr($fromPost['sheet']['streetnr']);

			$dateString = implode('-', [$fromPost['sheet']['birthday']['year'], $fromPost['sheet']['birthday']['month'], $fromPost['sheet']['birthday']['day']]);
			$birthday = new \DateTime($dateString);

			$sheet->setBirthday($birthday);

			$user->setSheet($sheet);
			$user->setNickname($fromPost['nickname']);
			$user->setEmail($fromPost['email']);
			$user->setPassword(md5($fromPost['password']));

			$this->dm->persist($user);
			$this->dm->flush();
		}

		$viewParams['userForm'] = $userForm;

		$view = new ViewModel($viewParams);
		$view->setTemplate('mcedit/developer/user');
		return $view;
	}

}
