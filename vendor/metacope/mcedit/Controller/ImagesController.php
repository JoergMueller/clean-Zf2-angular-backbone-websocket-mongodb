<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Controller;

use Metacope\Mcedit\Model\Image;
use Metacope\Mcedit\Model\ImageAttributes;
use Zend\Session\Container;
use \Zend\Mvc\Controller\AbstractActionController;
use \Zend\View\Model\JsonModel;
use \Zend\View\Model\ViewModel;

class ImagesController extends AbstractActionController {

	public function indexAction() {

		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$this->config = $config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
			'session' => $session,
			'config' => $config,
		];
		$this->layout()->setVariables($viewParams);

		$viewModel = new \Zend\View\Model\ViewModel($viewParams);
		$viewModel->setTerminal(true)
			->setTemplate('images/index');

		return $viewModel;
	}

	public function setAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$this->config = $config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
			'session' => $session,
			'config' => $config,
		];
		$this->layout()->setVariables($viewParams);

		$view = new \Zend\View\Model\ViewModel($viewParams);
		$view->setTerminal(true);
		return $view;
	}

	public function dropzoneAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$this->config = $config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
			'session' => $session,
			'config' => $config,
		];
		$this->layout()->setVariables($viewParams);
		$viewParams['image'] = $image = $this->dm->getRepository("Metacope\Mcedit\Model\Image")->find($this->params()->fromRoute('id'));

		$viewModel = new \Zend\View\Model\ViewModel($viewParams);
		$viewModel->setTerminal(true)
			->setTemplate('images/dropzone');
		return $viewModel;
	}

	public function cropperAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$this->config = $config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
			'session' => $session,
			'config' => $config,
		];
		$this->layout()->setVariables($viewParams);
		$viewParams['image'] = $image = $this->dm->getRepository("Metacope\Mcedit\Model\Image")->find($this->params()->fromRoute('id'));

		$viewModel = new \Zend\View\Model\ViewModel($viewParams);
		$viewModel->setTerminal(true)
			->setTemplate('images/cropper');
		return $viewModel;
	}

	public function croppedUploadAction() {
		var_dump($_POST, $_FILES);
	}

	public function uploadoAction() {
		$request = $this->getRequest();
		$lang = $this->lang;
		$dm = $this->dm;

		$_folder = $this->params()->fromPost('folder', 'Default');
		$_resturn = [];

		$i = 0;
		foreach ($_FILES['file'] as $k => $_file) {

			if (!isset($_FILES['file']['tmp_name'][$i])) {
				continue;
			}

			$tempFile = $_FILES['file']['tmp_name'][$i];
			$targetPath = getcwd() . '/public/images/data/uploads/';
			$targetFile = $targetPath . $_FILES['file']['name'][$i];
			move_uploaded_file($tempFile, $targetFile);

			$image = new Image();
			$image->setName($_FILES['file']['name'][$i]);
			$image->setFile($targetFile);

			if ($this->auth && $this->auth->hasIdentity()) {
				$image->setOwner($this->identity());
			}

			$image->setFolder($_folder);

			$attributes = new ImageAttributes();

			$image->setAttributes($attributes);

			$dm->persist($image);
			$dm->flush();

			$i += 1;

			if ($this->params()->fromQuery('widget')) {
				$id = $this->params()->fromQuery('widget');
				$widget_repository = $dm->getRepository("Metacope\Mcedit\Model\WidgetModel");
				$widget = $widget_repository->createQueryBuilder("Metacope\Mcedit\Model\WidgetModel")
					->field('images')->set($image)
					->field("id")->equals($id)
					->getQuery()
					->execute();
			}

			$_resturn[] = $image->getId();
		}
		return new \Zend\View\Model\JsonModel($_resturn);

	}

	public function uploadAction() {
		$request = $this->getRequest();
		$lang = $this->lang;
		$dm = $this->dm;

		$_folder = $this->params()->fromPost('folder', 'Default');

		$tempFile = $_FILES['file']['tmp_name'];
		$targetPath = getcwd() . '/data/uploads/';
		$targetFile = $targetPath . $_FILES['file']['name'];
		move_uploaded_file($tempFile, $targetFile);

		$image = new Image();
		$image->setName($_FILES['file']['name']);
		$image->setFile($targetFile);

		if ($this->auth && $this->auth->hasIdentity()) {
			$image->setOwner($this->identity());
		}

		$image->setFolder($_folder);

		$attributes = new ImageAttributes();

		$image->setAttributes($attributes);

		$dm->persist($image);
		$dm->flush();

		$ret = $image->getId();

		return new \Zend\View\Model\JsonModel([($ret)]);

	}

	public function attributesAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$this->config = $config = $this->getServiceLocator()->get('Config');

		$requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());
		$requestparams = array_merge($requestparams, $requestparams['attributes']);

		$request = $this->getRequest();
		$translator = $this->getServiceLocator()->get('translator');

		if ($request->isPost()) {
			$post = $request->getPost();
			$image = $this->dm->getRepository("Metacope\Mcedit\Model\Image")->find($post->id);

			$attributes = new ImageAttributes($image->getAttributes()->toArray());

			$attributes->setTitle($requestparams['title'], $this->lang)
				->setAlt($requestparams['alt'], $this->lang)
				->setCopyright($requestparams['copyright'])
				->setTag(isset($requestparams['tag']) && strlen($requestparams['tag']) ? explode(',', $requestparams['tag']) : []);

			$owner = $requestparams['owner'];
			if (isset($owner) && !empty($owner)) {
				$owner = $this->dm->getRepository("Metacope\Mcedit\Model\UserModel")->findOneByToken($requestparams['owner']);
				$image->setOwner($owner);
			} else {
				if ($this->auth->hasIdentity()) {
					$image->setOwner($this->identity());
				}

			}

			if (!empty($requestparams['expire'])) {
				$attributes->setExpire(new \DateTime($requestparams['expire']));
			} else {
				$attributes->setExpire(null);
			}
			$image->setAttributes($attributes);

			$this->dm->persist($image);
			$this->dm->flush();

			$content = $translator->translate('Edit success');
			return new \Zend\View\Model\JsonModel([
				'hasShowModal' => ['selector' => '#helper', 'label' => 'Edit success', 'content' => $content],
			]);

		}
		$content = $translator->translate('Edit failed');
		return new \Zend\View\Model\JsonModel([
			'hasShowModal' => ['selector' => '#helper', 'label' => 'Edit failed', 'content' => $content],
		]);

	}

	public function imageAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
			'session' => $session,
		];
		$this->layout()->setVariables($viewParams);

		return new \Zend\View\Model\ViewModel($viewParams);

	}

	public function imageeditAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
			'session' => $session,
		];
		$this->layout()->setVariables($viewParams);
		$viewParams['image'] = $image = $this->dm->getRepository("Metacope\Mcedit\Model\Image")->find($this->params()->fromRoute('id'));

		$viewModel = new \Zend\View\Model\ViewModel($viewParams);

		if (strpos($_SERVER['HTTP_HOST'], 'dbkt.site') !== false) {
//          $viewModel->setTemplate('application/images/imageedit-local');
		}

		$viewModel->setTerminal(true)
			->setTemplate('images/imageedit');
		return $viewModel;
	}

	public function metaAction() {

		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'rbac' => $this->rbac,
			'session' => $session,
			'config' => $config,
		];
		$this->layout()->setVariables($viewParams);
		$viewParams['image'] = $image = $this->dm->getRepository("Metacope\Mcedit\Model\Image")->find($this->params()->fromRoute('id'));

		$viewModel = new \Zend\View\Model\ViewModel($viewParams);
		$viewModel->setTerminal(true)
			->setTemplate('images/meta');
		return $viewModel;
	}

	public function cropAction() {

		$image = $this->dm->getRepository("Metacope\Mcedit\Model\Image")->find($this->params()->fromQuery('i'));

		$request = $this->getRequest();
		var_dump($request);
		die("\n" . __FILE__ . __LINE__ . "\n");

		$opts = [
			'http' => [
				'method' => 'GET',
				'header' => "Accept-language: en\r\n" .
				"Authorization: Basic Z286cHJldmlldw==\r\n",
			],
		];
		$context = stream_context_create($opts);

		$_src = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . $this->params()->fromQuery('s'), false, $context);

		$img_r = imagecreatefromstring($_src);
		$dst_r = imagecreatetruecolor($this->params()->fromQuery('w'), $this->params()->fromQuery('h'));
		imagecopyresampled($dst_r, $img_r, 0, 0, $this->params()->fromQuery('x'), $this->params()->fromQuery('y'), $this->params()->fromQuery('w'), $this->params()->fromQuery('h'), $this->params()->fromQuery('w'), $this->params()->fromQuery('h'));

		ob_start();
		imagejpeg($dst_r, null, 90);
		$img = ob_get_clean();

		imagedestroy($dst_r);
		imagedestroy($img_r);

		$file = getcwd() . '/data/cache/' . $image->getName() . '-Copy';
		file_put_contents($file, $img);

		$n = new Image();
		$n->setName($image->getName() . '-Copy');
		$n->setFile($file);

		$attributes = new ImageAttributes();
		$n->setAttributes($image->getAttributes());

		$this->dm->persist($n);
		$this->dm->flush();

		return new \Zend\View\Model\JsonModel();
	}

	public function removeAction() {

		$ids = $this->params()->fromQuery('ids') ? $this->params()->fromQuery('ids') : ($this->params()->fromQuery('id') ? [$this->params()->fromQuery('id')] : ($this->params()->fromRoute('id') ? [$this->params()->fromRoute('id')] : []));

		$qb = $this->dm->createQueryBuilder("Metacope\Mcedit\Model\Image")
			->remove()
			->field('id')->in($ids)
			->getQuery()
			->execute();

		return new \Zend\View\Model\JsonModel();
	}

	public function seekerAction() {
		$session = new \Zend\Session\Container('default');
		$this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
		$config = $this->getServiceLocator()->get('Config');

		$viewParams = [
			'lang' => $this->lang,
			'dm' => $this->dm,
			'targetId' => $this->params()->fromQuery('targetId'),
			'rbac' => $this->rbac,
			'session' => $session,
		];
		$this->layout()->setVariables($viewParams);

		$view = new \Zend\View\Model\ViewModel($viewParams);
		$view->setTerminal(true)
			->setTemplate('mcedit/images/seeker.phtml');

		return $view;
	}

	public function isbackgroundAction() {
		if ($token = $this->params()->fromRoute('token')) {
			$image = $this->dm->getRepository("Metacope\Mcedit\Model\Image")->findOneBy(['token' => $token]);
			$image->setIsbackground(($image->getIsbackground() == 1 ? 0 : 1));
			$this->dm->flush($image);

			return new \Zend\View\Model\JsonModel(['success' => true, 'isbackground' => $image->getIsbackground()]);
		}
		return new \Zend\View\Model\JsonModel(['success' => false, 'message' => 'token not found']);
	}

}
