<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Controller;

use Metacope\Mcedit\Controller\Form\DocumentForm;
use \Zend\Mvc\Controller\AbstractActionController;
use \Zend\View\Model\JsonModel;
use \Zend\View\Model\ViewModel;

class DocumentController extends AbstractActionController {
	public function indexAction() {
		$identity = $this->identity();
		$this->config = $this->getServiceLocator()->get('Config');

		$params = array_merge(
			$this->params()->fromRoute(),
			$this->params()->fromPost(),
			$this->params()->fromQuery()
		);

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'auth' => $this->auth,
			'params' => $params,
		];

		$view = new ViewModel($viewParams);
		$view->setTerminal(true)
			->setTemplate('mcedit/document/index');
		return $view;
	}

	public function setAction() {
		$identity = $this->identity();
		$this->config = $config = $this->getServiceLocator()->get('Config');
		$docLang = $this->params()->fromRoute('lang', 'de');
		$perms = $this->dm->getRepository("Metacope\Mcedit\Model\SettingsModel")->findOneBy(['index' => 'rbac']);
		$fallbackurllang = $this->config['locales']['list'][$docLang]['fallbackurllang'];

		$params = array_merge(
			$this->params()->fromRoute(),
			$this->params()->fromPost(),
			$this->params()->fromQuery()
		);

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'auth' => $this->auth,
			'params' => $params,
			'config' => $config,
			'docLang' => $docLang,
		];
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		$document = false;
		$documentform = new DocumentForm('Metacope\Mcedit\Model\DocumentModel', ['c' => $this]);
		if ($this->params()->fromRoute('token') && strlen($this->params()->fromRoute('token')) >= 16) {
			if ($document = $this->dm->getRepository("Metacope\Mcedit\Model\DocumentModel")->findOneBy(['token' => $this->params()->fromRoute('token')])) {
				$document->documentManager = $this->dm;

				$documentArray = $document->toArray($docLang);
				$documentform->setData($documentArray);

				$documentform->get('sheet[title]')->setValue($documentArray['sheet']['title']);
				$documentform->get('sheet[description]')->setValue($documentArray['sheet']['description']);
				$documentform->get('sheet[keywords]')->setValue(implode(',', $documentArray['sheet']['keywords']));
				$documentform->get('sheet[indexfollow]')->setValue($documentArray['sheet']['indexfollow']);

				// $options = array_map(function ($e) use ($document) {
				//     $r = array_keys($e);
				//     $str = array_shift($r);
				//     return ['value' => $str, 'label' => $str, 'selected' => ($document->getReadPerms() == $str)];
				// }, $perms->getData());

				// $documentform->get('readPerms')->setValueOptions($options);

				// $options = array_map(function ($e) use ($document) {
				//     $r = array_keys($e);
				//     $str = array_shift($r);
				//     return ['value' => $str, 'label' => $str, 'selected' => ($document->getWritePerms() == $str)];
				// }, $perms->getData());

				// $documentform->get('writePerms')->setValueOptions($options);
			}
		}

		if (true == $this->params()->fromQuery('create')) {

			$parent = null;
			if ($this->params()->fromRoute('id')) {
				$parent = $this->dm->getRepository("\Metacope\Mcedit\Model\DocumentModel")->find($this->params()->fromRoute('id'));
			}
			$document = $this->_createDocument($docLang, $parent);
		}

		$request = $this->getRequest();
		if (true == $request->isPost()) {
			$documentform->setData($this->params()->fromPost());

			if (true == $documentform->isValid()) {
				$docLang = $this->params()->fromPost('check_lang', $this->lang);

				$fromPost = $this->params()->fromPost();

				if (false == $document) {
					$document = $this->_createDocument($docLang, $parent);
				}
				$sheet = $document->getSheet();

				$document->setStructname($fromPost['structname'], $docLang);
				$sheet->setTitle($fromPost['sheet']['title'], $docLang);
				$sheet->setDescription($fromPost['sheet']['description'], $docLang);
				$sheet->setIndexfollow($fromPost['sheet']['indexfollow'], $docLang);
				$sheet->setKeywords(explode(',', $fromPost['sheet']['keywords']), $docLang);

				$document->setSheet($sheet);

				$document->setVisible(intval($fromPost['visible']));
				$document->setBgimage($fromPost['bgimage']);
				$document->setStructicon($fromPost['structicon']);
				$document->setSort(intval($fromPost['sort']));
				$document->setLayout($fromPost['layout'], $docLang);

				$document->setDocumentclass($fromPost['documentclass']);
				$document->setGeoreverse($fromPost['georeverse']);
				$document->setInlanguage($fromPost['inlanguage']);

				if (!isset($fromPost['path']) || !strlen(trim($fromPost['path']))) {
					$path = $document->generateUrl($docLang);
					$path = preg_replace("/[\/]+/s", '/', $path);
					$document->setPath($path, $docLang);
				} else {
					if (isset($fromPost['path']) && strlen(trim($fromPost['path'])) && $document->getPath($this->lang) != $fromPost['path']) {
						$document->setPath($fromPost['path'], $docLang);
					}
				}

				$this->dm->persist($document);
				$this->dm->flush();

				$document->clearLayoutCache();

				$content = $translator->translate('Update Success');
				return new \Zend\View\Model\JsonModel([
					'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
					'document' => $document->toArray(),
				]);

			} else {
				var_dump($documentform->getMessages());
				die("\n" . __FILE__ . __LINE__ . "\n");
			}
		}

		$viewParams['document'] = $document;
		$viewParams['documentform'] = $documentform;

		$this->layout()->setVariables($viewParams);

		$view = new ViewModel($viewParams);
		$view->setTerminal(true)
			->setTemplate('mcedit/document/set');
		return $view;
	}

	public function visibleAction() {

		$params = array_merge(
			$this->params()->fromRoute(),
			$this->params()->fromPost(),
			$this->params()->fromQuery()
		);

		if ($document = $this->dm->getRepository("Metacope\Mcedit\Model\DocumentModel")->find($params['id'])) {

			if ($document->getVisible() == 1) {
				$document->setVisible(0);
			} elseif ($document->getVisible() == 0) {
				$document->setVisible(1);
			}

			$this->dm->persist($document);
			$this->dm->flush();
		}

		return new JsonModel(array(
			'document' => $document->toArray(),
		));
	}

	public function removeAction() {
		$params = array_merge(
			$this->params()->fromRoute(),
			$this->params()->fromPost(),
			$this->params()->fromQuery()
		);

		if ($document = $this->dm->getRepository("Metacope\Mcedit\Model\DocumentModel")->find($params['id'])) {
			$document->setDocumentManager($this->dm);
			$children = $document->getChildList([], $this->dm);
			foreach ($children as $child) {
				$child->setDocumentManager($this->dm);
				$_children = $child->getChildList([], $this->dm);
				foreach ($_children as $_child) {
					$this->dm->remove($_child);
					$this->dm->flush();
				}
				$this->dm->remove($child);
				$this->dm->flush();
			}
			$this->dm->remove($document);
			$this->dm->flush();
		}

		return new JsonModel(array(
			'success' => true,
		));
	}

	protected function _createDocument($docLang, $parent) {

		$config = $this->getServiceLocator()->get('Config');

		$langs = array_keys($config['locales']['list']);

		$tpl = file_get_contents(getcwd() . '/data/templates/default/default.tpl');
		$subtpl = file_get_contents(getcwd() . '/data/templates/default/subnav.tpl');
		$document = new \Metacope\Mcedit\Model\DocumentModel();
		$sheet = new \Metacope\Mcedit\Model\DocumentSheetModel();

		$document->setDocumentManager($this->dm);

		$document->setParent($parent);

		foreach ($langs as $lang) {
			$document->setStructname('New-Site', $lang);
			$document->setLayout($tpl, $lang);
			$document->setSubnavlayout($subtpl, $lang);

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

		return $document;
	}
}
