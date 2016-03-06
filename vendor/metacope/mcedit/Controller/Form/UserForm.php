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

class UserForm extends Form
{
    public function __construct($name, $options = [])
    {
        $controller = $options['c'];

        parent::__construct("Metacope\Mcedit\Model\{$name}");

        $this->setAttributes([
            'id' => 'userForm',
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
            'name' => 'nickname',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Nickname'),
            ],
        ]);

        $this->add([
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Login'),
            ],
        ]);

        $this->add([
            'name' => 'password',
            'type' => 'Zend\Form\Element\Password',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Password'),
            ],
        ]);

        $this->add([
            'name' => 'sheet[firstname]',
            'type' => 'Zend\Form\Element\Text',
            'required' => true,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[required]',
                'placeholder' => $controller->translator->translate('Firstname'),
            ],
        ]);

        $this->add([
            'name' => 'sheet[name]',
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
            'name' => 'sheet[birthday]',
            'type' => 'Zend\Form\Element\DateSelect',
            'required' => false,
            'attributes' => [
                'type' => 'text',
                'autocomplete' => 'off',
                'data-prompt-position' => 'topLeft',
                'class' => 'form-control validate[date]',
                'placeholder' => $controller->translator->translate('Birthday'),
            ],
            'options' => [
                'render_delimiters' => false,
                'min_year' => date('Y') - 58,
                'max_year' => date('Y') - 12,
                'day_attributes' => [
                    'class' => 'form-control wp32 pull-left margin-right-15',
                ],
                'month_attributes' => [
                    'class' => 'form-control wp32 pull-left margin-right-15',
                ],
                'year_attributes' => [
                    'class' => 'form-control wp32 pull-left',
                ],
            ],
        ]);

        $this->add([
            'name' => 'sheet[zipcode]',
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
            'name' => 'sheet[city]',
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
            'name' => 'sheet[streetnr]',
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

        $hydrator = new DoctrineHydrator($controller->dm, '\Metacope\Mcedit\Model\CountryModel');
        $this->setHydrator($hydrator);

        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'sheet[country]',
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
