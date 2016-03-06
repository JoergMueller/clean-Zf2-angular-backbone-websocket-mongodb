<?php
/**
 * @package Metacope
 * @subpackage Mcedit
 * @author joerg.mueller
 * @version
 **/

namespace Metacope\Mcedit\Controller\Form;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class ClientForm extends Form
{
    public function __construct($name, $options = [])
    {
        $controller = $options['c'];

        parent::__construct("Metacope\Mcedit\Model\{$name}");

        $this->setAttributes([
            'id' => 'clientForm',
            'method' => 'post',
            'class' => 'jqform',
            'novalidate' => true,
            'accept-charset' => 'utf8',
        ]);
        $this->add([
            'name' => 'token',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => ['id' => 'client-token'],
        ]);

        $this->add([
            'name' => 'shortname',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Shortname'),
            ],
        ]);

        $this->add([
            'name' => 'name',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Name'),
            ],
        ]);

        $this->add([
            'name' => 'firm',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Firm'),
            ],
        ]);

        $this->add([
            'name' => 'zipcode',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required,custom[zipcode]]',
                'placeholder' => $controller->translator->translate('Zipcode'),
            ],
            'options' => ['column-size' => 'md-4 padding-0 padding-right-10'],
        ]);

        $this->add([
            'name' => 'city',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('City'),
            ],
            'options' => ['column-size' => 'md-8 padding-0'],
        ]);

        $this->add([
            'name' => 'streetnr',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Streetnr'),
            ],
        ]);

        // ================================================

        $this->add([
            'name' => 'phone',
            'type' => 'Zend\Form\Element\Text',
            'required' => false,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control',
                'placeholder' => $controller->translator->translate('Phone'),
            ],
        ]);

        $this->add([
            'name' => 'fax',
            'type' => 'Zend\Form\Element\Text',
            'required' => false,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control',
                'placeholder' => $controller->translator->translate('Fax'),
            ],
        ]);

        $this->add([
            'name' => 'mail',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required, custom[email]]',
                'placeholder' => $controller->translator->translate('Mail'),
            ],
        ]);

        $hydrator = new DoctrineHydrator($controller->dm, '\Metacope\Mcedit\Model\CountryModel');
        $this->setHydrator($hydrator);

        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'country',
            'attributes' => [
                'class' => 'form-control  validate[required]',

            ],
            'options' => [
                'object_manager' => $controller->dm,
                'target_class' => "\Metacope\Mcedit\Model\CountryModel",
                'label_generator' => function ($targetEntity) {

                    if ($targetEntity->getIso() == 'DE') {
                        $this->setOption('default', $targetEntity);
                    }

                    return $targetEntity->getTitle();
                },
                'option_attributes' => [
                    'data-iso' => function ($targetEntity) {
                        return $targetEntity->getIso();
                    },
                    'selected' => function ($targetEntity) {
                        return $targetEntity->getIso() == 'DE' ? true : false;
                    },
                ],
            ],
        ]);

        // ================================================

        $this->add([
            'name' => 'layout',
            'type' => 'Zend\Form\Element\Textarea',
            'required' => false,
            'attributes' => [
                'type' => 'textarea',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control h780',
                'placeholder' => $controller->translator->translate('Layout'),
            ],
        ]);

        $this->add([
            'name' => 'stylesheet',
            'type' => 'Zend\Form\Element\Textarea',
            'required' => false,
            'attributes' => [
                'type' => 'textarea',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control h780',
                'placeholder' => $controller->translator->translate('Stylesheet'),
            ],
        ]);

        $this->add([
            'name' => 'description',
            'type' => 'Zend\Form\Element\Textarea',
            'required' => false,
            'attributes' => [
                'type' => 'textarea',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control h700 wysihtm',
                'placeholder' => $controller->translator->translate('Description'),
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'id' => 'clientFormButton',
                'value' => '<i class="fa fa-check fa-fw"></i> ' . $controller->translator->translate('Submit'),
                'label' => '<i class="fa fa-check fa-fw"></i> ' . $controller->translator->translate('Submit'),
                'value_options' => ['disable_html_escape' => true],
                'label_options' => ['disable_html_escape' => true],
                'class' => 'btn btn-primary pull-right margin-right-10',
                'id' => 'submitBtn',
            ],
        ]);
    }
}
