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
use Metacope\Mcedit\Model\ClientModel;
use \Zend\Mvc\Controller\AbstractActionController;
use \Zend\Session\Container;
use \Zend\View\Model\JsonModel;
use \Zend\View\Model\ViewModel;

class ClientController extends AbstractActionController
{

    public function indexAction()
    {
        $session = new \Zend\Session\Container('default');
        $this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
        $config = $this->getServiceLocator()->get('Config');

        $viewParams = [
            'lang' => $this->lang,
            'dm' => $this->dm,
            'rbac' => $this->rbac,
        ];
        $this->translator = $translator = $this->getServiceLocator()->get('translator');
        $this->layout()->setVariables($viewParams);

        $viewModel = new \Zend\View\Model\ViewModel($viewParams);
        $viewModel->setTerminal(true)
            ->setTemplate('mcedit/client/index');
        return $viewModel;
    }

    public function editAction()
    {

        $session = new \Zend\Session\Container('default');
        $this->lang = $this->params()->fromQuery('lang') ? $this->params()->fromQuery('lang') : ($this->lang ? $this->lang : $session->offsetGet('lang'));
        $config = $this->getServiceLocator()->get('Config');

        $viewParams = [
            'lang' => $this->lang,
            'dm' => $this->dm,
            'rbac' => $this->rbac,
        ];
        $this->layout()->setVariables($viewParams);
        $this->translator = $translator = $this->getServiceLocator()->get('translator');

        $form = new ClientForm('ClientModel', ['c' => $this]);
        if ($this->params()->fromRoute('id')) {
            $client = $this->dm->getRepository("\Metacope\Mcedit\Model\ClientModel")->find($this->params()->fromRoute('id'));
            $form->setData($client->toArray());
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $data = $this->params()->fromPost();

                $client->setName($data['name']);
                $client->setFirm($data['firm']);
                $client->setZipcode($data['zipcode']);
                $client->setCity($data['city']);
                $client->setStreetnr($data['streetnr']);
                $client->setPhone($data['phone']);
                $client->setFax($data['fax']);
                $client->setMail($data['mail']);

                $client->setDescription($data['description']);
                $client->setLayout($data['layout'], $this->lang);
                $client->setStylesheet($data['stylesheet']);

                if (!$country = $this->dm->getRepository("\Metacope\Mcedit\Model\CountryModel")->find($data['country'])) {
                    $country = $this->dm->getRepository("\Metacope\Mcedit\Model\CountryModel")->findOneBy(['iso' => 'DE']);
                }
                $client->setCountry($country);

                if ($client->getId()) {
                    $this->dm->flush($client);
                } else {
                    $this->dm->persist($client);
                    $this->dm->flush();
                }

                $viewParams['client'] = $client;

                $content = $translator->translate('Edit success');
                return new \Zend\View\Model\JsonModel([
                    'client' => $client->toArray(),
                    'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
                ]);

            } else {
                $messages = $form->getMessages();
                $content = $translator->translate('Edit failed!');
                return new \Zend\View\Model\JsonModel([
                    'client' => $client->toArray(),
                    'hasShowModal' => ['selector' => '#helper', 'label' => 'ERROR', 'content' => $content],
                ]);
            }
        }

        $viewParams['form'] = $form;
        $viewParams['client'] = $client;

        $viewModel = new \Zend\View\Model\ViewModel($viewParams);
        $viewModel->setTemplate('mcedit/client/edit')
            ->setTerminal(true);
        return $viewModel;
    }

    public function createAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        $country = $this->dm->getRepository("Metacope\Mcedit\Model\CountryModel")->findOneBy(['iso' => 'DE']);

        $data = [
            'name' => 'demo-' . microtime(),
            'fullname' => 'demo-' . microtime(),
            'country' => $country,
            'stylesheet' => '.styleClass { color: \'#333\'; }',
        ];
        $client = new \Metacope\Mcedit\Model\ClientModel($data);

        $country = $this->dm->getRepository("Metacope\Mcedit\Model\CountryModel")->findOneBy(['iso' => 'DE']);
        $client->setCountry($country);

        if ($this->params()->fromRoute('id')) {
            $parent = $this->dm->getRepository("Metacope\Mcedit\Model\ClientModel")->find($this->params()->fromRoute('id'));
            $client->setParent($parent);
        }

        $tpl = file_get_contents(getcwd() . '/data/templates/defaults/default.tpl');
        foreach ($config['locales']['list'] as $k => $e) {
            $client->setLayout($tpl, $k);
        }

        $this->dm->persist($client);
        $this->dm->flush();

        $viewParams['form'] = $form = new ClientForm('ClientModel', ['c' => $this]);

        $view = new \Zend\View\Model\ViewModel($viewParams);
        $view->setTerminal(true)
            ->setTemplate('mcedit/client/edit');
        return $view;

    }

    public function removeAction()
    {
        $viewParams = [
            'lang' => $this->lang,
            'dm' => $this->dm,
            'rbac' => $this->rbac,
        ];
        $this->translator = $translator = $this->getServiceLocator()->get('translator');

        if ($this->params()->fromRoute('id')) {
            // $client = $this->dm->getRepository("Metacope\Mcedit\Model\ClientModel")->find($this->params()->fromRoute('id'));
            $qb = $this->dm->createQueryBuilder("Metacope\Mcedit\Model\ClientModel");
            $qb->remove()
                ->field('id', $this->params()->fromRoute('id'))
                ->getQuery()
                ->execute();

            $content = $translator->translate('Remove success');
            return new \Zend\View\Model\JsonModel([
                'client' => $client->toArray(),
                'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => $content],
            ]);
        }
    }
}
