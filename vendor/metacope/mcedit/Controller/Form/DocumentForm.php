<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Controller\Form;

use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use \Zend\Form\Form;

class DocumentForm extends Form {

	public function __construct($name, $options = []) {
		$controller = $options['c'];

		$this->setAttributes([
			'id' => 'documentForm',
			'method' => 'post',
			'class' => 'jqform',
			'novalidate' => true,
			'accept-charset' => 'utf8',
		]);

		parent::__construct($name);

		$this->setHydrator(new ClassMethodsHydrator(false));

		$this->add([
			'name' => 'token',
			'type' => 'Zend\Form\Element\Hidden',
			'attributes' => ['id' => 'token'],
		]);

		$this->add([
			'name' => 'id',
			'type' => 'Zend\Form\Element\Hidden',
			'attributes' => ['id' => 'id'],
		]);

		$options = [];
		foreach ($controller->config['locales']['list'] as $lang => $entry) {
			$options[$lang] = $entry['short'] . ': ' . strtoupper($entry['name']);
		}

		$this->add([
			'type' => 'Zend\Form\Element\MultiCheckbox',
			'name' => 'inlanguage',
			'required' => true,
			'options' => [
				'label' => $controller->translator->translate('Inlanguage'),
				'value_options' => $options,
				'label_attributes' => [
					'class' => 'block bold',
				],
			],
		]);

		$this->add([
			'name' => 'structname',
			'type' => 'Zend\Form\Element\Text',
			'required' => true,
			'options' => [
				'label' => $controller->translator->translate('Structname'),
			],
			'attributes' => ['class' => 'form-control validate[required]', 'placeholder' => 'Structname'],
		]);

		$this->add([
			'name' => 'path',
			'type' => 'Zend\Form\Element\Text',
			'required' => false,
			'options' => [
				'label' => $controller->translator->translate('Path'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Path'],
		]);

		$this->add([
			'name' => 'sort',
			'type' => 'Zend\Form\Element\Number',
			'options' => [
				'label' => $controller->translator->translate('Position'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Position'],
		]);

		$this->add([
			'name' => 'bgimage',
			'id' => 'bgImageInput',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Headerimage'),
			],
			'attributes' => ['class' => 'form-control bs-image', 'data-seeker-target' => 'bgImageInput', 'placeholder' => $controller->translator->translate('Headerimage'), 'id' => 'bgImageInput'],
		]);

		$this->add([
			'name' => 'documentclass',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Documentclass'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Documentclass'],
		]);

		$this->add([
			'name' => 'structicon',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Structicon'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Structicon'],
		]);

		$this->add([
			'name' => 'parent',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Parent'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Parent'],
		]);

		$this->add([
			'name' => 'visible',
			'type' => 'Zend\Form\Element\Number',
			'options' => [
				'label' => $controller->translator->translate('Visible'),
			],
			'attributes' => ['class' => 'form-control validate[required]', 'placeholder' => 'Visible'],
		]);

		$this->add([
			'name' => 'georeverse',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Georeverse'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Georeverse'],
		]);

		$this->add([
			'name' => 'layout',
			'type' => 'Zend\Form\Element\Textarea',
			'attributes' => ['class' => 'form-control hp100', 'placeholder' => 'Layout'],
		]);

		$this->add([
			'name' => 'subnavlayout',
			'type' => 'Zend\Form\Element\Textarea',
			'attributes' => ['class' => 'form-control hp100', 'placeholder' => 'Sub-Nav-Layout ( Megamenu )'],
		]);

		$this->add([
			'name' => 'sheet[title]',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Pagetitle'),
			],
			'attributes' => ['class' => 'form-control validate[required]', 'placeholder' => 'Titel'],
		]);

		$this->add([
			'name' => 'sheet[description]',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Pagedescription'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Beschreibung'],
		]);

		$this->add([
			'name' => 'sheet[keywords]',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Pagekeywords'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Keywords'],
		]);

		$this->add([
			'name' => 'sheet[indexfollow]',
			'type' => 'Zend\Form\Element\Text',
			'options' => [
				'label' => $controller->translator->translate('Indexfollow'),
			],
			'attributes' => ['class' => 'form-control', 'placeholder' => 'Indexfollow'],
		]);

		$this->add([
			'type' => 'Zend\Form\Element\Text',
			'name' => 'readPerms',
			'options' => [
				'label' => $controller->translator->translate('read Permissions'),
			],
			'attributes' => [
				'class' => 'form-control validate[required]',
			],
		]);

		$this->add([
			'type' => 'Zend\Form\Element\Text',
			'name' => 'writePerms',
			'options' => [
				'label' => $controller->translator->translate('write Permissions'),
			],
			'attributes' => [
				'class' => 'form-control validate[required]',
			],
		]);

		$this->add([
			'name' => 'submit',
			'attributes' => [
				'type' => 'Zend\Form\Element\Button',
				'id' => 'documentFormButton',
				'icon' => 'check',
				'value' => $controller->translator->translate('Submit'),
				'class' => 'btn btn-primary pull-right inline',
				'id' => 'submitBtn',
			],
		]);
	}
}
