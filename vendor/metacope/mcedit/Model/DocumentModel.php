<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Model;

use Doctrine\ODM\MongoDB\DocumentManager;
use Metacope\Mcedit\Model\Coordinates;
use Metacope\Mcedit\Model\DocumentSheetModel;
use Metacope\Mcedit\Model\UserModel;
use \Doctrine\Common\Collections\Collection as Collection;
use \Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use \Zend\InputFilter\InputFilter;
use \Zend\InputFilter\InputFilterAwareInterface;
use \Zend\InputFilter\InputFilterInterface;

/**
 * @MongoDB\Document(collection="Documents", indexes={
 * @MongoDB\Index(keys={"coordinates"="2d"}),
 * })
 * @MongoDB\HasLifecycleCallbacks
 */
class DocumentModel implements InputFilterAwareInterface {
	protected $inputFilter;
	public $documentManager;

	protected $ignoreArray = ['Zend\InputFilter\InputFilter'];

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Distance
	 */
	public $distance;

	/**
	 * @MongoDB\Field(type="date")
	 * @MongoDB\Index(order="asc")
	 */
	protected $datecreate;

	/**
	 * @MongoDB\Field(type="date")
	 * @MongoDB\Index(order="asc")
	 */
	public $publishedOn;

	/**
	 * @MongoDB\Field(type="date")
	 * @MongoDB\Index(order="asc")
	 */
	public $publishedOff = null;

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $layout = [];

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $subnavlayout = [];

	/**
	 * @MongoDB\Field(type="hash")
	 * @MongoDB\Index(order="asc")
	 */
	protected $path = [];

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $structname = [];

	/**
	 * @MongoDB\Field(type="collection")
	 * @MongoDB\Index(order="asc")
	 */
	protected $inlanguage = [];

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $bgimage;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $structicon = null;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $documentclass;

	/**
	 * @MongoDB\Field(type="int")
	 * @MongoDB\Index(order="asc")
	 */
	protected $sort = 1;

	/**
	 * @MongoDB\Field(type="int")
	 * @MongoDB\Index(order="asc")
	 */
	protected $visible = 0;

	/**
	 * @MongoDB\Field(type="int")
	 * @MongoDB\Index(order="asc")
	 */
	protected $isdocument = 1;

	/** @MongoDB\EmbedOne(targetDocument="DocumentSheetModel") */
	protected $sheet;

	/** @MongoDB\EmbedOne(targetDocument="Coordinates") */
	protected $coordinates = null;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $georeverse = null;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="DocumentModel")
	 */
	protected $parent = null;

	/**
	 * @MongoDB\ReferenceMany(targetDocument="UserModel")
	 */
	protected $authors;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="UserModel")
	 */
	protected $owner = null;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="ClientModel")
	 */
	protected $client = null;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
	 */
	protected $token;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	protected $readPerms = 'guest';

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	protected $writePerms = 'editor';

	/**
	 * @MongoDB\Field(type="hash")
	 */
	public $workflow = null;

	/**
	 * @MongoDB\Field(type="boolean")
	 */
	protected $editable = true;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $checksum;

	/** @MongoDB\PreFlush */
	public function findGeoCoords() {
		$rotatecount = 20;

		if ($this->georeverse && strlen($this->georeverse) && $this->coordinates == null) {
			if ($coords = \Metacope\Mcedit\Model\Coordinates::findGeoCoords($this->georeverse)) {
				$this->setCoordinates($coords);
			}
		}

		if ($this->getAuthors() != null && $this->getAuthors()->count() > $rotatecount) {
			while ($this->getAuthors()->count() > $rotatecount) {
				$this->removeAuthor($this->getAuthors()->first());
			}
		}

	}

	/** @MongoDB\PreFlush */
	public function _PreFlushWorkflow() {
		if (!$this->readPerms) {
			$this->readPerms = 'guest';
		}

	}

	/** @MongoDB\PostLoad */
	public function _PostLoadWorkflow() {
		if (!$this->readPerms) {
			$this->readPerms = 'guest';
		}

	}

	public function setDocumentManager($dm) {
		$this->documentManager = $dm;
	}

	public function __construct(array $params = []) {
		$this->token = \Metacope\Mcedit\Model\UserModel::newPassword(16, null, 0);
		$this->datecreate = new \DateTime();
		$this->publishedOn = new \DateTime();
		$this->documentclass = '';
		$this->structicon = '';
		$this->bgimage = '';
		$this->authors = [];
		$this->layout = [];
		$this->subnavlayout = [];
		$this->readPerms = 'guest';
		$this->writePerms = 'editor';
	}

	public function toArray($lang = null) {
		$o = get_object_vars($this);
		$o = array_map(function ($e) {
			return is_object($e) && in_array(get_class($e), $this->ignoreArray, true) ? null : $e;
		}, $o);

		$o['sheet'] = isset($o['sheet']) && is_object($o['sheet']) ? $o['sheet']->toArray($lang) : $o['sheet'];
		$o['parent'] = isset($o['parent']) && is_object($o['parent']) ? $o['parent']->toArray() : $o['parent'];
		$o['owner'] = isset($o['owner']) && is_object($o['owner']) ? $o['owner']->toArray() : $o['owner'];
		$o['authors'] = isset($o['authors']) && is_object($o['authors']) ? $o['authors']->toArray() : $o['authors'];

		if (strlen($lang) > 0) {
			foreach ($o as $k => $v) {
				if (is_array($o) && isset($o[$lang])) {
					$o = $o[$lang];
				} else if (is_array($v) && isset($v[$lang])) {
					$o[$k] = $v[$lang];
				} elseif (is_object($v) && method_exists($v, 'toArray')) {
					$v->toArray();
				}

			}
		}
		return $o;
	}

	public function hasReadPerms() {
		return false;
	}

	public function exchangeArray($data, $lang) {
	}

	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception('Not used');
	}

	public function getInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();

			$inputFilter->add([
				'name' => 'path',
				'required' => false,
				'filters' => [
					['name' => 'StripTags'],
					['name' => 'StringTrim'],
				],
				'validators' => [
					[
						'name' => 'StringLength',
						'options' => [
							'encoding' => 'UTF-8',
							'min' => 5,
							'max' => 101,
						],
					],
				],
			]);

			$inputFilter->add([
				'name' => 'structname',
				'required' => true,
				'filters' => [
					['name' => 'StripTags'],
					['name' => 'StringTrim'],
				],
				'validators' => [
					[
						'name' => 'StringLength',
						'options' => [
							'encoding' => 'UTF-8',
							'min' => 5,
							'max' => 101,
						],
					],
				],
			]);

			$inputFilter->add([
				'name' => 'sort',
				'required' => true,
				'filters' => [
					['name' => 'StripTags'],
					['name' => 'StringTrim'],
				],
				'validators' => [
				],
			]);

			$inputFilter->add([
				'name' => 'visible',
				'required' => true,
				'filters' => [
					['name' => 'StripTags'],
					['name' => 'StringTrim'],
				],
				'validators' => [
				],
			]);

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}

	public $ids = null;

	/**
	 * getChildIds: get a list of all documentIds
	 * specified by entry-node
	 *
	 * @param (int|null) $depth
	 * @param (ArrayIterator) $ids
	 * @return ArrayIterator $ids
	 *
	 */
	public function getChildIds($ids = null) {

		if (null == $ids) {
			$ids = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS, 'ArrayIterator');
			$ids->offsetSet('children', new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS, 'ArrayIterator'));
			$ids->offsetSet('_ids', new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS, 'ArrayIterator'));
		}

		$children = $this->getChildren($this->documentManager);
		foreach ($children as $child) {
			$child->documentManager = $this->documentManager;

			$ids->offsetGet('children')->append($child);
			$ids->offsetGet('_ids')->append($child->getId());

			if ($child->hasChildren($this->documentManager) == true) {
				return $child->getChildIds($ids);
			}

		}

		return $ids;
	}

	public function getChildList(array $options = [], $dm, $auth = false) {
		$sort = isset($options['sort']) && !empty($options['sort']) && is_array($options['sort']) ? $options['sort'] : ['sort', 'ASC'];
		$options['lang'] = isset($options['lang']) ? $options['lang'] : 'de';

		$qb = $dm->createQueryBuilder("Metacope\Mcedit\Model\DocumentModel")
			->field('publishedOn')->lte(new \DateTime())
			->field('parent')->references($this);

		if (isset($options['isClientStruct']) && true === $options['isClientStruct']) {

			if (!$auth->hasIdentity()) {
				if (isset($options['clientToken']) && strlen($options['clientToken']) > 0) {
					if (!$client = $dm->getRepository("Metacope\Mcedit\Model\ClientModel")->findOneBy(['token' => $options['clientToken']])) {
						throw new \Exception('could not find client in database by given token');
					}
				}
			}

			$qb->field('client')->references($client);
		}

		$qb->field('inlanguage')->equals($options['lang']);
		if (!isset($options['iseditor']) || false == $options['iseditor']) {
			$qb->field('visible')->equals(1);
		}
		if (isset($options['hydrate']) && false === $options['hydrate']) {
			$qb->hydrate(false);
		}

		$qb->sort($sort[0], $sort[1]);

		return $qb->getQuery()->execute();
	}

	public function createPublishStruct($lang = 'de', $dm, $recursive = false) {
		if (!defined('RENDERED')) {
			define('RENDERED', getcwd() . '/data/cache');
		}

		if (!file_exists(RENDERED . '/' . $lang)) {
			mkdir(RENDERED . '/' . $lang, 0777);
		}

		$domain = 'http://edit.hhogv4.site/' . $lang;
		$opts = [
			'http' => [
				'method' => 'GET',
				'header' => "Accept-language: $lang\r\n" .
				"Cookie: mSID=null\r\n",
			],
		];
		$context = stream_context_create($opts);

		$path = rtrim(preg_replace('/[a-zA-Z0-9-_]+$/siU', '', $this->path[$lang]), '/');

		$prefix = '';
		if (null != $this->parent) {
			$_temp = $this;
			$root = $dm->getRepository(get_class($this))->findOneBy(["path.{$lang}" => '/']);
			while ($_temp && null != $_temp->parent) {
				$_temp = $dm->getRepository(get_class($this))->findOneBy(['id' => $_temp->parent->id]);
			}

			if ($_temp->getId() == $root->getId()) {
				$prefix = self::createUrl($root->sheet['title'][$lang]);
				$path = '/' . $prefix . $path;
			}
		}
		$site = RENDERED . '/' . $lang . $path . '/' . self::createUrl($this->sheet['title'][$lang]);

		if (!file_exists($site)) {
			mkdir($site, 0777, true);
		}

		try {
			$content = file_get_contents($domain . $this->getPath($lang, $dm) . '?publish=true', false, $context);
			file_put_contents($site . '.html', $content);
		} catch (Exception $e) {}

		if (false == $recursive) {
			return true;
		}

		if ($childrens = $dm->getRepository(get_class($this))->findBy(['parent.id' => $this->id, 'visible' => 1])) {
			foreach ($childrens as $child) {
				$child->createPublishStruct($lang, $dm, $recursive);
			}

		}

	}

	public function clearLayoutCache($lang = 'de') {
		$filter = new \Zend\Filter\Word\SeparatorToDash();
		$key = $lang . '-' . $filter->filter($this->getSheet()->getTitle());
		$filename = getcwd() . '/data/cache/' . $key . '.phtml';

		try {
			@unlink($filename);
		} catch (Exception $e) {}
	}

	public static function createUrl($str, $delimiter = '-') {
		$search = [
			'Ö', 'Ä', 'Ü', 'ö', 'ä', 'ü', 'ß',
		];
		$replace = [
			'Oe', 'Ae', 'Ue', 'oe', 'ae', 'ue', 'ss',
		];
		$str = str_replace($search, $replace, $str);

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/siU", '', $clean);
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
		$clean = strtolower(trim($clean, '-'));

		return $clean;
	}

	public function generateUrl($lang = 'de', $parent = null, $sheet = null) {
		$path_parent = '/';
		$path_current = '/';

		$parent = $parent ? $parent : $this->getParent();
		if ($parent) {
			$path_parent = $parent->getPath($lang);
		}

		$sheet = $sheet ? $sheet : $this->getSheet();
		if ($sheet && $sheet->getTitle($lang)) {
			$path_current .= self::createUrl($sheet->getTitle($lang));
		}

		$path = $path_parent . $path_current;
		return preg_replace("/[\/]+/si", '/', $path);
	}

	public function hasChildren($dm = null) {
		$dm = $this->documentManager ? $this->documentManager : $dm;
		$qb = $dm->createQueryBuilder("\Metacope\Mcedit\Model\DocumentModel")
			->field('parent')->references($this)
			->field('publishedOn')->lte(new \DateTime())
			->field('visible')->equals(1)
			->getQuery()->execute();

		return $qb->count() > 0 ? true : false;
	}

	public function getChildren($dm = null) {
		$dm = $this->documentManager ? $this->documentManager : $dm;
		return $dm->createQueryBuilder("\Metacope\Mcedit\Model\DocumentModel")
			->field('parent')->references($this)
			->field('publishedOn')->lte(new \DateTime())
			->field('visible')->equals(1)
			->sort('sort', 1)
			->getQuery()->execute();
	}

	public function getNthChild($number = 2, $dm = null) {
		$dm = $this->documentManager ? $this->documentManager : $dm;
		return $dm->createQueryBuilder("\Metacope\Mcedit\Model\DocumentModel")
			->field('parent')->references($this)
			->field('publishedOn')->lte(new \DateTime())
			->field('visible')->equals(1)
			->sort('sort', 1)
			->skip(($number - 1))
			->limit(1)
			->getQuery()->getSingleResult();
	}

	public function getArticleWidget($nth, $dm) {
		$dm = $this->documentManager ? $this->documentManager : $dm;
		$qb = $dm->createQueryBuilder("Metacope\Mcedit\Model\WidgetModel")
			->field('type')->equals('article')
			->field('parent')->references($this);

		return $qb->getQuery()->getSingleResult();
	}

	// interface

	public function getPages() {return [$this];}
	public function getOptions() {return ['object_manager' => 'doctrine.documentmanager.odm_default', 'target_class' => '\Metacope\Mcedit\Model\DocumentModel', 'ulClass' => 'nav navbar', 'maxDepth' => 1];}
	public function getAttributes() {}

	/**
	 * Get id
	 *
	 * @return id $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set datecreate
	 *
	 * @param date $datecreate
	 * @return self
	 */
	public function setDatecreate($datecreate) {
		$this->datecreate = $datecreate;
		return $this;
	}

	/**
	 * Get datecreate
	 *
	 * @return date $datecreate
	 */
	public function getDatecreate() {
		return $this->datecreate;
	}

	/**
	 * Set layout
	 *
	 * @param string $layout
	 * @return self
	 */
	public function setLayout($layout, $lang = 'de') {
		$this->layout[$lang] = $layout;
		return $this;
	}

	/**
	 * Get layout
	 *
	 * @return Doctrine\MongoDB\Collection $layout
	 */
	public function getLayout($lang = 'de') {
		if (null === $lang) {
			return $this->layout;
		}

		return isset($this->layout[$lang]) ? $this->layout[$lang] : null;
	}

	/**
	 * Set path
	 *
	 * @param string $path
	 * @return self
	 */
	public function setPath($path, $lang = 'de') {
		$this->path[$lang] = $path;
		return $this;
	}

	/**
	 * Get path
	 *
	 * @return Doctrine\MongoDB\Collection $path
	 */
	public function getPath($lang = 'de') {
		if (null === $lang) {
			return $this->path;
		}

		return isset($this->path[$lang]) ? $this->path[$lang] : null;
	}

	/**
	 * Set structname
	 *
	 * @param  $structname
	 * @return self
	 */
	public function setStructname($structname, $lang = 'de') {
		$this->structname[$lang] = $structname;
		return $this;
	}

	/**
	 * Get structname
	 *
	 * @return Doctrine\MongoDB\Collection $structname
	 */
	public function getStructname($lang = 'de') {
		if (null === $lang) {
			return $this->structname;
		}

		return isset($this->structname[$lang]) ? $this->structname[$lang] : null;
	}

	/**
	 * Set inlanguage
	 *
	 * @param collection $inlanguage
	 * @return self
	 */
	public function setInlanguage($inlanguage, $lang = null) {

		if (!is_array($inlanguage) && null != $lang) {
			if ($this->getInlanguage()->contains($lang) == false) {
				$this->add($lang);
			}

		} elseif (is_array($inlanguage)) {

			$this->inlanguage = $inlanguage;
		}

		return $this;
	}

	/**
	 * Get inlanguage
	 *
	 * @return Doctrine\MongoDB\Collections $inlanguage
	 */
	public function getInlanguage() {
		return $this->inlanguage;
	}

	/**
	 * Set sort
	 *
	 * @param int $sort
	 * @return self
	 */
	public function setSort($sort) {
		$this->sort = $sort;
		return $this;
	}

	/**
	 * Get sort
	 *
	 * @return int $sort
	 */
	public function getSort() {
		return $this->sort;
	}

	/**
	 * Set visible
	 *
	 * @param int $visible
	 * @return self
	 */
	public function setVisible($visible) {
		$this->visible = $visible;
		return $this;
	}

	/**
	 * Get visible
	 *
	 * @return int $visible
	 */
	public function getVisible() {
		return $this->visible;
	}

	/**
	 * Set parent
	 *
	 * @param \Metacope\Mcedit\Model\DocumentModel $parent
	 * @return self
	 */
	public function setParent(\Metacope\Mcedit\Model\DocumentModel $parent = null) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Get parent
	 *
	 * @return \Metacope\Mcedit\Model\DocumentModel $parent
	 */
	public function getParent() {
		return $this->parent ? $this->parent : null;
	}

	/**
	 * Set token
	 *
	 * @param string $token
	 * @return self
	 */
	public function setToken($token) {
		$this->token = $token;
		return $this;
	}

	/**
	 * Get token
	 *
	 * @return string $token
	 */
	public function getToken() {
		return $this->token;
	}

	/**
	 * Set sheet
	 *
	 * @param \Metacope\Mcedit\Model\DocumentSheetModel $sheet
	 * @return self
	 */
	public function setSheet(\Metacope\Mcedit\Model\DocumentSheetModel $sheet) {
		$this->sheet = $sheet;
		return $this;
	}

	/**
	 * Get sheet
	 *
	 * @return \Metacope\Mcedit\Model\DocumentSheetModel $sheet
	 */
	public function getSheet() {
		return $this->sheet ? $this->sheet : null;
	}

	/**
	 * Set coordinates
	 *
	 * @param \Metacope\Mcedit\Model\Coordinates $coordinates
	 * @return self
	 */
	public function setCoordinates($coordinates) {
		$this->coordinates = $coordinates;
		return $this;
	}

	/**
	 * Get coordinates
	 *
	 * @return \Metacope\Mcedit\Model\Coordinates $coordinates
	 */
	public function getCoordinates() {
		return $this->coordinates;
	}

	/**
	 * Set georeverse
	 *
	 * @param string $georeverse
	 * @return self
	 */
	public function setGeoreverse($georeverse) {
		$this->georeverse = $georeverse;
		return $this;
	}

	/**
	 * Get georeverse
	 *
	 * @return string $georeverse
	 */
	public function getGeoreverse() {
		return $this->georeverse;
	}

	/**
	 * Add author
	 *
	 * @param \Metacope\Mcedit\Model\UserModel $author
	 */
	public function addAuthor(\Metacope\Mcedit\Model\UserModel $author) {
		$this->authors[] = $author;
	}

	/**
	 * Remove author
	 *
	 * @param \Metacope\Mcedit\Model\UserModel $author
	 */
	public function removeAuthor(\Metacope\Mcedit\Model\UserModel $author) {
		$this->authors->removeElement($author);
	}

	/**
	 * Get authors
	 *
	 * @return Doctrine\Common\Collections\Collection $authors
	 */
	public function getAuthors() {
		return $this->authors;
	}

	/**
	 * Set owner
	 *
	 * @param \Metacope\Mcedit\Model\UserModel $owner
	 * @return self
	 */
	public function setOwner(\Metacope\Mcedit\Model\UserModel $owner) {
		$this->owner = $owner;
		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return \Metacope\Mcedit\Model\UserModel $owner
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * Set isdocument
	 *
	 * @param int $isdocument
	 * @return self
	 */
	public function setIsdocument($isdocument) {
		$this->isdocument = $isdocument;
		return $this;
	}

	/**
	 * Get isdocument
	 *
	 * @return int $isdocument
	 */
	public function getIsdocument() {
		return $this->isdocument;
	}

	/**
	 * Set bgimage
	 *
	 * @param string $bgimage
	 * @return self
	 */
	public function setBgimage($bgimage) {
		$this->bgimage = $bgimage;
		return $this;
	}

	/**
	 * Get bgimage
	 *
	 * @return string $bgimage
	 */
	public function getBgimage() {
		return $this->bgimage;
	}

	/**
	 * Set structicon
	 *
	 * @param string $structicon
	 * @return self
	 */
	public function setStructicon($structicon) {
		$this->structicon = $structicon;
		return $this;
	}

	/**
	 * Get structicon
	 *
	 * @return string $structicon
	 */
	public function getStructicon() {
		return $this->structicon;
	}

	/**
	 * Set documentclass
	 *
	 * @param string $documentclass
	 * @return self
	 */
	public function setDocumentclass($documentclass) {
		$this->documentclass = $documentclass;
		return $this;
	}

	/**
	 * Get documentclass
	 *
	 * @return string $documentclass
	 */
	public function getDocumentclass($default = '') {
		return $this->documentclass ? $this->documentclass : $default;
	}

	/**
	 * Set client
	 *
	 * @param \Metacope\Mcedit\Model\ClientModel $client
	 * @return self
	 */
	public function setClient(\Metacope\Mcedit\Model\ClientModel $client) {
		$this->client = $client;
		return $this;
	}

	/**
	 * Get client
	 *
	 * @return \Metacope\Mcedit\Model\ClientModel $client
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * Set publishedOn
	 *
	 * @param date $publishedOn
	 * @return self
	 */
	public function setPublishedOn($publishedOn) {
		if (is_string($publishedOn)) {
			$publishedOn = new \DateTime($publishedOn);
		}

		$this->publishedOn = $publishedOn;
		return $this;
	}

	/**
	 * Get publishedOn
	 *
	 * @return date $publishedOn
	 */
	public function getPublishedOn() {
		return $this->publishedOn;
	}

	/**
	 * Set publishedOff
	 *
	 * @param date $publishedOff
	 * @return self
	 */
	public function setPublishedOff($publishedOff = false) {
		if (false == $publishedOff) {
			$this->publishedOff = $publishedOff;
			return $this;
		}
		if (is_string($publishedOff)) {
			$publishedOff = new \DateTime($publishedOff);
		}

		$this->publishedOff = $publishedOff;
		return $this;
	}

	/**
	 * Get publishedOff
	 *
	 * @return date $publishedOff
	 */
	public function getPublishedOff() {
		return $this->publishedOff;
	}

	/**
	 * Set distance
	 *
	 * @param string $distance
	 * @return self
	 */
	public function setDistance($distance) {
		$this->distance = $distance;
		return $this;
	}

	/**
	 * Get distance
	 *
	 * @return string $distance
	 */
	public function getDistance() {
		return $this->distance;
	}

	/**
	 * Set workflow
	 *
	 * @param hash $workflow
	 * @return self
	 */
	public function setWorkflow($workflow) {
		$this->workflow = $workflow;
		return $this;
	}

	/**
	 * Get workflow
	 *
	 * @return hash $workflow
	 */
	public function getWorkflow() {
		return $this->workflow;
	}

	/**
	 * Set editable
	 *
	 * @param boolean $editable
	 * @return self
	 */
	public function setEditable($editable) {
		$this->editable = $editable;
		return $this;
	}

	/**
	 * Get editable
	 *
	 * @return boolean $editable
	 */
	public function getEditable() {
		return $this->editable;
	}

	/**
	 * Set checksum
	 *
	 * @param string $checksum
	 * @return self
	 */
	public function setChecksum($checksum) {
		$this->checksum = $checksum;
		return $this;
	}

	/**
	 * Get checksum
	 *
	 * @return string $checksum
	 */
	public function getChecksum() {
		return $this->checksum;
	}

	/**
	 * Set readPerms
	 *
	 * @param string $readPerms
	 * @return self
	 */
	public function setReadPerms($readPerms) {
		$this->readPerms = $readPerms;
		return $this;
	}

	/**
	 * Get readPerms
	 *
	 * @return string $readPerms
	 */
	public function getReadPerms() {
		return $this->readPerms;
	}

	/**
	 * Set writePerms
	 *
	 * @param string $writePerms
	 * @return self
	 */
	public function setWritePerms($writePerms) {
		$this->writePerms = $writePerms;
		return $this;
	}

	/**
	 * Get writePerms
	 *
	 * @return string $writePerms
	 */
	public function getWritePerms() {
		return $this->writePerms;
	}

	/**
	 * Set subnavlayout
	 *
	 * @param hash $subnavlayout
	 * @return self
	 */
	public function setSubnavlayout($subnavlayout, $lang = 'de') {
		$this->subnavlayout[$lang] = $subnavlayout;
		return $this;
	}

	/**
	 * Get subnavlayout
	 *
	 * @return Doctrine\MongoDB\Collection $subnavlayout
	 */
	public function getSubnavlayout($lang = 'de') {
		if (null === $lang) {
			return $this->subnavlayout;
		}

		return isset($this->subnavlayout[$lang]) ? $this->subnavlayout[$lang] : null;
	}
}
