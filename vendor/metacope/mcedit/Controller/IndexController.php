<?php
/**
 * @package Metacope
 * @subpackage Metacope\Mcedit
 * @author joerg.mueller
 * @version
 **/

namespace Metacope\Mcedit\Controller;

use Metacope\Mcedit\Model;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public $dm;
    public $lang;
    public $auth;

    public $document = null;

    /**
     * Index - Metacope\Mcedit
     *
     * @return Zend\View\Model\ViewModel Zend View Model
     */
    public function indexAction()
    {

        $view = new ViewModel($viewParams);
        $view->setTemplate('mcedit/index/index');
        return $view;
    }

    public function check404Action()
    {
        $this->notFoundAction();
    }

    public function viewAction()
    {

        $servicelocator = $this->getServiceLocator();
        $identity = $this->identity();

        $params = array_merge(
            $this->params()->fromRoute(),
            $this->params()->fromPost(),
            $this->params()->fromQuery()
        );

        $params['pageuri'] = trim($params['pageuri'], " \t\n\r\0\x0B\/");

        $viewParams = [
            'lang' => $this->lang,
            'dm' => $this->dm,
            'auth' => $this->auth,
        ];
        $this->layout()->setVariables(array_merge($viewParams, $params));
        $this->layout()->setTemplate('mcedit/layout');

        $qb = $this->dm->createQueryBuilder("\Metacope\Mcedit\Model\DocumentModel");

        $query = $qb->field("path.{$this->lang}")->equals('/' . $params['pageuri'])
            ->field('publishedOn')->lt(new \DateTime())
            ->field('inlanguage')->equals($this->lang)
            ->getQuery();

        if ($document = $query->getSingleResult()) {
            $this->getServiceLocator()->get('viewHelperManager')->get('headTitle')->append($document->getSheet()->getTitle($this->lang));
            $viewParams['document'] = $document;
        } else {
            $this->notFoundAction();
        }

        $viewParams['EditorData'] = $this->dm->createQueryBuilder("Metacope\Mcedit\Model\SettingsModel")
                                            ->hydrate(false)
                                            ->select("data")
                                            ->field("index")->equals("editor_buttons")
                                            ->getQuery()
                                            ->getSingleResult();


        $this->layout()->setVariables(array_merge($viewParams, $params));
        $this->layout()->setTemplate('mcedit/layout');

        $view = new ViewModel($viewParams);

        return $view;
    }

    public function contactAction()
    {
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

        $params = array_merge(
            $this->params()->fromPost(),
            $this->params()->fromRoute(),
            $this->params()->fromQuery()
        );

        if ((isset($params['fax']) && strlen($params['fax']))) {
            //
        } else {
            $message = [];
            $_sess = new Container('Zend_Form_Captcha_' . $params['captchaId']);
            // check captcha

            if (!$_sess->word || ($_sess->word != $params['captcha'])) {
                $message[] = 'Captcha leider nicht korrekt';
            }

            if (sizeof($message) <= 0) {
                $data = $this->params()->fromPost('contact');
                $data['captchaId'] = $params['captchaId'];
                $data['captcha'] = $params['captcha'];
                $data['captchaword'] = $_sess->word;

                $contactLog = new \Metacope\Mcedit\Model\ContactLogModel();
                $contactLog->setData($data);

                $this->dm->persist($contactLog);
                $this->dm->flush();
            }

            if (sizeof($message) > 0) {
                return new \Zend\View\Model\JsonModel([
                    'hasShowModal' => ['selector' => '#helper', 'label' => 'Info', 'content' => implode("<br>\n", $message)],
                ]);
            } else {

                $html = new MimePart($htmlMarkup);
                $html->type = 'text/html';

                $body = new MimeMessage();
                $body->setPart($html);

                ob_start();
                include WIDGET . '/mail/contact.phtml';
                $tpl = ob_get_clean();

                $message = new Message();
                $message
                    ->setEncoding('UTF-8')
                    ->setFrom('ssh@adipositas-netzwerk.ch', 'Website')
                    // ->setTo('ssh@adipositas-netzwerk.ch')
                    ->setTo('joerg.mueller.ffm@gmail.com')
                    ->setSubject('E-Mail Kontakt: ' . $data['destination'])
                    ->setBody($tpl);

                $transport = new SendmailTransport();
                $transport->send($message);

                $message[] = '<p>';
                $message[] = 'Vielen Dank für Ihre Anfrage.';
                $message[] = 'Wir haben Ihre Anfrage entgegen genommen und an die zuständigen Stellen weitergeleitet.';
                $message[] = '</p>';

                return new \Zend\View\Model\JsonModel([
                    'message' => implode("<br>\n", $message),
                ]);
            }

        }

    }
}
