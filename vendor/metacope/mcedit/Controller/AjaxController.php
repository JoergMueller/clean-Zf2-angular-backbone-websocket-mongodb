<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}
namespace Metacope\Mcedit\Controller;

use \Zend\Mvc\Controller\AbstractActionController;
use \Zend\View\Model\JsonModel;

class AjaxController extends AbstractActionController
{

    public function dtreeAction()
    {

        if ($editor = $this->identity()) {
            $client = $editor->getClient();
        } else {
            $client = $this->dm->getRepository("Metacope\Mcedit\Model\ClientModel")->findOneBy(['shortname' => 'meta:cope']);
        }

        $requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());
        $id = isset($requestparams['id']) ? $requestparams['id'] : null;

        $qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\DocumentModel');

        if (isset($requestparams['id']) && 0 == $requestparams['id']) {
            $qb->addOr($qb->expr()->field('parent')->equals(null));
            $qb->addOr($qb->expr()->field('parent')->exists(false));
        } else {
            $parent = $this->dm->getRepository("Metacope\Mcedit\Model\DocumentModel")->find($requestparams['id']);
            $qb->field('parent')->references($parent);
        }
        // $qb->field('client')->references($client);

        $qb->sort('sort', 'ASC');
        $documents = $qb->getQuery()->execute();

        $return = [];
        foreach ($documents as $document) {
            $return[] = $document->toArray();
        }

        return new \Zend\View\Model\JsonModel(['documents' => $return]);
    }

    public function dsearchAction()
    {
        $requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());
        $qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\DocumentModel');

        $q = $requestparams['term'];

        $qb->addOr($qb->expr()->field("structname.{$this->lang}")->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field("title.{$this->lang}")->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field("sheet.description.{$this->lang}")->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field("sheet.keywords.{$this->lang}")->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field('georeverse')->equals(new \MongoRegex("/{$q}/i")));

        $documents = $qb->getQuery()->execute();

        $return = [];
        foreach ($documents as $document) {
            $return[] = $document->toArray();
        }

        return new \Zend\View\Model\JsonModel(['documents' => $return]);
    }

    public function userTreeAction()
    {
        $requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());
        $id = isset($requestparams['id']) ? $requestparams['id'] : null;

        $qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\UserModel');

        if (0 == $requestparams['id']) {
            $qb->addOr($qb->expr()->field('parent')->exists(false));
            $qb->addOr($qb->expr()->field('parent')->equals(null));
        } else {
            $parent = $this->dm->getRepository("Metacope\Mcedit\Model\UserModel")->find($id);
            $qb->field('parent')->references($parent);
        }

        $users = $qb->getQuery()->execute();

        $return = [];
        foreach ($users as $user) {
            $return[] = $user->toArray();
        }

        return new \Zend\View\Model\JsonModel(['users' => $return]);
    }

    public function usersAction()
    {
        $requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());
        $q = isset($requestparams['q']) ? $requestparams['q'] : (isset($requestparams['term']) ? $requestparams['term'] : null);

        $qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\UserModel');
        $qb->addOr($qb->expr()->field('nickname')->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field('email')->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field('sheet.firstname')->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field('sheet.lastname')->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field('sheet.city')->equals(new \MongoRegex("/{$q}/i")));
        $qb->addOr($qb->expr()->field('sheet.streetnr')->equals(new \MongoRegex("/{$q}/i")));

        $users = $qb->getQuery()->execute();

        $return = [];
        foreach ($users as $user) {
            $user_array = $user->toArray();
            $user_array['fullname'] = $user->getFullname();
            $return[] = $user_array;
        }
        return new \Zend\View\Model\JsonModel($return);
    }

    public function clientTreeAction()
    {
        $identity = $this->identity();

        $id = $this->params()->fromQuery('id');
        $qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\ClientModel');
        $requestparams = array_merge($this->params()->fromQuery(), $this->params()->fromPost(), $this->params()->fromRoute());

        if ($this->rbac->getRole($identity->getRole())->hasPermission('editor') == false) {
            $qb->field('employee')->equals($identity->getId());
        }

        if (isset($requestparams['id']) && 0 != $requestparams['id']) {
            $qb->field('parent.id')->equals($requestparams['id']);
        } else {
            $qb->addOr($qb->expr()->field('parent.id')->exists(false));
            $qb->addOr($qb->expr()->field('parent.id')->equals(null));
        }

        $clients = $qb->getQuery()->execute();

        $return = [];
        foreach ($clients as $client) {
            $return[] = $client->toArray();
        }

        return new \Zend\View\Model\JsonModel(['clients' => $return]);
    }

    public function tokenAction()
    {
        $token = \Metacope\Mcedit\Model\User::newPassword(16, null, 0);
        return new \Zend\View\Model\JsonModel(['token' => $token]);
    }

    public function thumbsAction()
    {
        $lang = $this->lang;
        $query = $this->params()->fromQuery('term');
        $folder = $this->params()->fromQuery('folder');
        $qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\Image');

        $limit = $this->params()->fromQuery('limit', 10);
        $page = $this->params()->fromQuery('page', 1);
        $offset = $limit * $page - $limit;

        if ($query && strlen(trim($query))) {
            $qb->addOr($qb->expr()->field('name')->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field('filename')->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field('attributes.copyright')->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field("attributes.title.{$lang}")->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field("attributes.alt.{$lang}")->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field('attributes.tag')->in([$query]));
        }
        if ($folder && strlen(trim($folder))) {
            $qb->field('folder')->equals($folder);
        }

        $qb->sort('uploadDate', 'desc')
            ->limit($limit)
            ->skip($offset);

        $images = $qb->getQuery()
            ->execute();

        $return = [];
        foreach ($images as $image) {
            $return[] = $image->toArray();
        }

        return new \Zend\View\Model\JsonModel($return);
    }

    public function documentSearchAction()
    {

        return new \Zend\View\Model\JsonModel($this->params());
    }

    public function isearchAction()
    {

        $folders = $this->params()->fromQuery('folder');
        $query = $this->params()->fromQuery('q');

        $qb = $this->dm->createQueryBuilder('Metacope\Mcedit\Model\Image');

        if (isset($folders) && is_array($folders)) {
            $qb->addOr($qb->expr()->field('folder')->in($folders));
        }

        if ($query && strlen(trim($query))) {
            $qb->addOr($qb->expr()->field('name')->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field('filename')->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field('attributes.copyright')->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field("attributes.title.{$this->lang}")->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field("attributes.alt.{$this->lang}")->equals(new \MongoRegex("/.*{$query}.*/i")));
            $qb->addOr($qb->expr()->field('attributes.tag')->in([$query]));
        }

        $images = $qb->getQuery()->execute();

        $return = [];
        foreach ($images as $image) {
            $return[] = $image->toArray();
        }

        return new \Zend\View\Model\JsonModel($return);
    }
}
