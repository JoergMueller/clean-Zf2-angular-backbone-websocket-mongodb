<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Controller;

use Metacope\Mcedit\Model\Coordinates;
use Metacope\Mcedit\Model\WidgetModel;
use \Zend\Mvc\Controller\AbstractActionController;
use \Zend\Session\Container;
use \Zend\View\Model\JsonModel;
use \Zend\View\Model\ViewModel;

class WidgetController extends AbstractActionController {

	public function indexAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'config' => $config,
		];
		$this->layout()->setVariables($viewParams);

		return new \Zend\View\Model\ViewModel($viewParams);
	}

	/**
	 *
	 *
	 * dateupdate
	 * datestart
	 * datestop
	 * anker
	 * type
	 * path.lang
	 * attributes
	 * inlanguage
	 * draft
	 * sort
	 * editmode
	 * parent
	 * coordinates
	 * author
	 * georeverse
	 * token
	 */
	public function setAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$lang = $this->params()->fromQuery('lang', $this->lang);

		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'auth' => $this->auth,
			'config' => $config,
		];

		$this->layout()->setVariables($viewParams);
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		$requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());

		$routeId = $this->params()->fromRoute('id') && $this->params()->fromRoute('id', 'undefined') != 'undefined'
		? $this->params()->fromRoute('id')
		: null;

		if (!$this->params()->fromRoute('create') && $this->params()->fromRoute('token')) {
			$widget = $this->dm->getRepository("Metacope\Mcedit\Model\WidgetModel")->findOneBy(['token' => $this->params()->fromRoute('token')]);
			$type = $widget->getType();
		} else {
			$widget = new \Metacope\Mcedit\Model\WidgetModel();
			// $widget->setAuthor($this->identity());

			foreach (array_keys($config['locales']['list']) as $l) {
				$widget->setInlanguage($l);
			}

			if ($routeId) {
				$id = $this->params()->fromRoute('id');
				$parent = $this->dm->getRepository("Metacope\Mcedit\Model\DocumentModel")->find($id);
				$widget->setParent($parent);
				$widget->setGeoreverse($parent->getGeoreverse());
			}

			$type = $this->params()->fromRoute('type', $this->params()->fromQuery('type'));
			$anker = $this->params()->fromQuery('path');

			$widget->setType($type);
			$widget->setAnker($anker);

			$widget->setDatestart();

			if ($this->params()->fromRoute('create') == 1) {
				$this->dm->persist($widget);
				$this->dm->flush();
			} else {
				$widget->setToken(null);
			}

		}

		$request = $this->getRequest();
		if ($request->isPost() == true) {
			$lang = $this->params()->fromPost('check_lang', $this->lang);

			switch ($type) {

			case 'slider_fullwidth':
				$attributes = $widget->getAttributes(1);

				$request_attributes = $this->params()->fromPost('attributes', []);
				$request_items = $this->params()->fromPost('slides', []);

				foreach ($request_items as $key => $index) {
					$attributes['slides'][$key]['text'][$lang] = $index['text'];
					$attributes['slides'][$key]['image'][$lang] = $index['image'];
				}

				foreach ($request_attributes as $k => $v) {
					$attributes[$k][$lang] = $v;
				}
				$widget->setAttributes($attributes);
				break;
			case 'intro':
				$attributes = $widget->getAttributes(1);

				$request_attributes = $this->params()->fromPost('attributes', []);

				foreach ($request_attributes as $k => $v) {
					if (!isset($attributes[$k])) {
						$attributes[$k] = array();
					}

					$attributes[$k][$lang] = $v;
				}
				$widget->setAttributes($attributes);
				break;
			case 'article':
			case 'article_inline':
				$attributes = $widget->getAttributes(1);
				if (isset($requestparams['georeverse']) && strlen(trim($requestparams['georeverse']))) {
					$widget->setGeoreverse($requestparams['georeverse']);
				}

				$attributes['headline'][$lang] = $this->params()->fromPost('headline');
				$attributes['subline'][$lang] = $this->params()->fromPost('subline');
				$attributes['link'][$lang] = $this->params()->fromPost('link');
				$attributes['intro'][$lang] = $this->params()->fromPost('intro');
				$attributes['body'][$lang] = $this->params()->fromPost('body');

				$request_attributes = $this->params()->fromPost('attributes');
				foreach ($request_attributes as $k => $v) {
					$attributes[$k][$lang] = $v;
				}

				$widget->setAttributes($attributes);
				break;
			case 'featured_blog':
				$attributes = $widget->getAttributes(1);

				$attributes['widgettags'][$lang] = explode(',', $this->params()->fromPost('widgettags'));

				$request_attributes = $this->params()->fromPost('attributes');
				foreach ($request_attributes as $k => $v) {
					$attributes[$k][$lang] = $v;
				}
				$widget->setAttributes($attributes);
				break;
			case 'featured_box':
				$attributes = $widget->getAttributes(1);

				$request_attributes = $this->params()->fromPost('attributes');
				foreach ($request_attributes as $k => $v) {
					if (!isset($attributes[$k][$lang])) {
						$attributes[$k] = [$lang => $v];
					}

					$attributes[$k][$lang] = $v;
				}
				$widget->setAttributes($attributes);
				break;
			case 'featured_destination':
				$attributes = $widget->getAttributes(1);
				if (isset($requestparams['georeverse']) && strlen(trim($requestparams['georeverse']))) {
					$widget->setGeoreverse($requestparams['georeverse']);
				}

				$attributes['headline'][$lang] = $this->params()->fromPost('headline');
				$attributes['geoinfo'][$lang] = $this->params()->fromPost('geoinfo');
				$attributes['target'][$lang] = $this->params()->fromPost('target');
				$attributes['image'][$lang] = $this->params()->fromPost('image');

				$request_attributes = $this->params()->fromPost('attributes', []);
				foreach ($request_attributes as $k => $v) {
					$attributes[$k][$lang] = $v;
				}

				$widget->setAttributes($attributes);
				break;
			case 'featured_accordioncard':
				$attributes = $widget->getAttributes(1);
				if (isset($requestparams['georeverse']) && strlen(trim($requestparams['georeverse']))) {
					$widget->setGeoreverse($requestparams['georeverse']);
				}

				$request_attributes = $this->params()->fromPost('attributes', []);
				$request_items = $this->params()->fromPost('items', []);

				foreach ($request_items as $key => $index) {
					$attributes['items'][$key]['headline'][$lang] = $index['headline'];
					$attributes['items'][$key]['subline'][$lang] = $index['subline'];
					$attributes['items'][$key]['target'][$lang] = $index['target'];
					$attributes['items'][$key]['image'][$lang] = $index['image'];
				}

				foreach ($request_attributes as $k => $v) {
					$attributes[$k][$lang] = $v;
				}

				$widget->setAttributes($attributes);
				break;
			case 'article_list':
				$attributes = $widget->getAttributes();
				if (isset($requestparams['georeverse']) && strlen(trim($requestparams['georeverse']))) {
					$widget->setGeoreverse($requestparams['georeverse']);
				}

				$request_attributes = $this->params()->fromPost('attributes');
				$request_attributes['searchtags'][$lang] = strpos($request_attributes['searchtags'], ',') !== false ? explode(',', $request_attributes['searchtags']) : [];
				$widget->setAttributes($request_attributes);
				break;
			default:
				if (isset($requestparams['georeverse']) && strlen(trim($requestparams['georeverse']))) {
					$widget->setGeoreverse($requestparams['georeverse']);
				}
				$request_attributes = $this->params()->fromPost('attributes');
				$widget->setAttributes($request_attributes);
				break;

			}

			if (isset($requestparams['georeverse']) && strlen($requestparams['georeverse'])) {
				if ($geo = \Metacope\Mcedit\Model\Coordinates::findGeoCoords($requestparams['georeverse'])) {
					$widget->setCoordinates($geo);
				}
			}

			$tags = isset($requestparams['tags']) ? explode(',', $requestparams['tags']) : [];

			$widget->setDateupdate(new \DateTime());
			$widget->setDatestart(isset($requestparams['datestart']) && strlen(trim($requestparams['datestart'])) ? new \DateTime($requestparams['datestart']) : null);
			$widget->setDatestop(isset($requestparams['datestop']) && strlen(trim($requestparams['datestop'])) ? new \DateTime($requestparams['datestop']) : null);

			if ($tags && is_array($tags) && sizeof($tags) >= 0) {
				$widget->setTags($tags);
			}

			$this->dm->persist($widget);
			$this->dm->flush();

			$content = $translator->translate('Update Success');
			return new \Zend\View\Model\JsonModel([
				'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
			]);

		}
		$type = $widget->getType();

		$viewParams['widget'] = $widget;
		$filter = new \Zend\Filter\Word\UnderscoreToSeparator('/');
		$type = $filter->filter($type);

		$view = new \Zend\View\Model\ViewModel($viewParams);
		$view->setTemplate('/mcedit/widget/' . $type . '/edit')
			->setTerminal(true);

		return $view;

	}

	public function removeAction() {
		$this->translator = $translator = $this->getServiceLocator()->get('translator');

		if ($token = $this->params()->fromRoute('token')) {
			$qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\WidgetModel');
			$qb->remove()
				->field('token')->equals($token)
				->getQuery()
				->execute();
		}

		$content = $translator->translate('Update Success');
		return new \Zend\View\Model\JsonModel([
			'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
		]);
	}
}
