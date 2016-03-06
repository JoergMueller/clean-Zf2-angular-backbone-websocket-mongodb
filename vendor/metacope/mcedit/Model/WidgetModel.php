<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Model;

use Doctrine\Common\Collections\Collection as Collection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Metacope\Mcedit\Model\Coordinates;
use Metacope\Mcedit\Model\DocumentModel;
use Metacope\Mcedit\Model\UserModel;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @MongoDB\Document(collection="Widgets", indexes={
 * @MongoDB\Index(keys={"coordinates"="2d"}),
 * }))
 * @MongoDB\HasLifecycleCallbacks
 */
class WidgetModel implements InputFilterAwareInterface {
	protected $inputFilter;
	public $documentManager;

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $datecreate;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $dateupdate = null;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $datestart = null;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $datestop = null;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $anker;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $type;

	/**
	 * @MongoDB\Field(type="collection")
	 */
	protected $tags;

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $path = [];

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $attributes;

	/**
	 * @MongoDB\Field(type="collection")
	 */
	protected $inlanguage = [];

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $draft = null;

	/**
	 * @MongoDB\Field(type="int")
	 * @MongoDB\Index(order="asc")
	 */
	protected $sort = 1;

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $editmode = null;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="DocumentModel")
	 */
	protected $parent = null;

	/**
	 * @MongoDB\EmbedOne(targetDocument="Coordinates")
	 */
	protected $coordinates = null;

	/**
	 * @MongoDB\ReferenceOne(targetDocument="UserModel")
	 */
	protected $author;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $georeverse = '';

	/** @MongoDB\ReferenceMany(targetDocument="UserModel") */
	protected $likes;

	/** @MongoDB\ReferenceMany(targetDocument="UserModel") */
	protected $dislikes;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, dropDups=true, order="asc")
	 */
	protected $token;

	/** @MongoDB\PreFlush */
	public function widgetPreFlush() {

		if ($this->georeverse && strlen($this->georeverse)) {
			if ($coords = \Metacope\Mcedit\Model\Coordinates::findGeoCoords($this->georeverse)) {
				$this->setCoordinates($coords);
			}
		}
		$this->dateupdate = new \DateTime();
	}

	public function __construct(array $params = []) {
		foreach ($params as $k => $v) {
			if ($this->{$k}) {
				$this->{$k} = $v;
			}
		}

		$this->datecreate = new \DateTime();
		$this->datestart = new \DateTime();
		$this->dateupdate = new \DateTime();
		$this->token = UserModel::newPassword(16, null, 0);
	}

	public function toArray() {
		$widget = get_object_vars($this);
		$widget['parent'] = !empty($widget['parent']) ? $widget['parent']->toArray() : null;
		$widget['attributes'] = !empty($widget['attributes']) ? (array) $this->getAttributes() : null;
		return $widget;
	}

	public function hasGeoCoords() {
		if ($coords = $this->getCoordinates()) {
			if ($coords->getLat() && $coords->getLng()) {
				return true;
			}

			return false;
		}
		return false;
	}

	public function getDocumentManager() {
		return $this->documentManager;
	}

	public function setDocumentManager(DocumentManager $dm) {
		$this->documentManager = $dm;
		return $this;
	}

	public function getDocument() {
		return $this->getParent();
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

			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	public function getArticles($lang = 'de') {
		$dm = $this->documentManager;
		$attributes = $this->getAttributes(1);

		$searchby = [
			'tags' => (isset($attributes['searchtags']) && is_array($attributes['searchtags']) && sizeof($attributes['searchtags']) > 0),
			'path' => (isset($attributes['searchpath']) && strlen(trim($attributes['searchpath'])) > 1),
		];

		$qb = $dm->createQueryBuilder("Metacope\Mcedit\Model\WidgetModel");
		$qb->field('inlanguage')->equals($lang)
			->field('type')->equals('article_inline');

		if ($searchby['tags']) {
			if (!is_array($attributes['searchtags'])) {
				$attributes['searchtags'] = new \MongoRegex("/{$attributes['searchtags']}/i");
			} else {
				foreach ($attributes['searchtags'] as $i => $tag) {
					$attributes['searchtags'][$i] = new \MongoRegex("/$tag/i");
				}
			}
			$qb->field('tags')->in($attributes['searchtags']);
		}
		if ($searchby['path']) {
			if ($parent = $this->documentManager->getRepository("Metacope\Mcedit\Model\DocumentModel")->findOneBy(["path.{$lang}" => $attributes['searchpath']])) {
				$parent->documentManager = $this->documentManager;
				$p = str_replace('/', '\/', $parent->getPath('de'));
				$parents = $this->documentManager->getRepository("Metacope\Mcedit\Model\DocumentModel")->findBy([
					'path.de' => new \MongoRegex("/$p/"),
				]);

				foreach ($parents as $p) {
					$qb->addOr($qb->expr()->field('parent')->references($p));
				}
			}
		}

		if (isset($attributes['count'])) {
			$qb->limit($attributes['count']);
		}

		$query = $qb->getQuery();
		return $query->execute();

	}

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
	 * Set dateupdate
	 *
	 * @param date $dateupdate
	 * @return self
	 */
	public function setDateupdate($dateupdate) {
		$this->dateupdate = $dateupdate;
		return $this;
	}

	/**
	 * Get dateupdate
	 *
	 * @return date $dateupdate
	 */
	public function getDateupdate() {
		return $this->dateupdate;
	}

	/**
	 * Set datestart
	 *
	 * @param date $datestart
	 * @return self
	 */
	public function setDatestart($datestart = null) {
		if (null == $datestart) {
			$datestart = new \DateTime();
		}

		$this->datestart = $datestart;
		return $this;
	}

	/**
	 * Get datestart
	 *
	 * @return date $datestart
	 */
	public function getDatestart($format = null) {

		if ($format && $this->datestart) {
			return $this->datestart->format($format);
		}

		if (!$format && $this->datestart) {
			return $this->datestart;
		}

		return null;
	}

	/**
	 * Set datestop
	 *
	 * @param date $datestop
	 * @return self
	 */
	public function setDatestop($datestop = null) {
		$this->datestop = $datestop;
		return $this;
	}

	/**
	 * Get datestop
	 *
	 * @return date $datestop
	 */
	public function getDatestop($format = null) {
		if ($format && $this->datestop) {
			return $this->datestop->format($format);
		}

		if (!$format && $this->datestop) {
			return $this->datestop;
		}

		return null;
	}

	/**
	 * Set anker
	 *
	 * @param string $anker
	 * @return self
	 */
	public function setAnker($anker) {
		$this->anker = $anker;
		return $this;
	}

	/**
	 * Get anker
	 *
	 * @return string $anker
	 */
	public function getAnker() {
		return $this->anker;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 * @return self
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Set path
	 *
	 * @param hash $path
	 * @return self
	 */
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * Get path
	 *
	 * @return hash $path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Set attributes
	 *
	 * @param hash $attributes
	 * @return self
	 */
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * Get attributes
	 *
	 * @return Doctrine\Common\Collections\Collection $attributes
	 */
	public function getAttributes($flag = null) {
		if (null !== $flag) {
			return is_array($this->attributes) && sizeof($this->attributes) > 0
			? (array) $this->attributes
			: [];
		}
		return is_array($this->attributes) && sizeof($this->attributes) > 0
		? new \ArrayObject($this->attributes, \ArrayObject::STD_PROP_LIST)
		: new \ArrayObject([], \ArrayObject::STD_PROP_LIST);
	}

	public function hasAttribute($index, $lang = false) {
		$return = null;
		if ($lang) {
			$return = isset($this->attributes[$index]) && isset($this->attributes[$index][$lang])
			? $this->attributes[$index][$lang]
			: null;
		} else {
			$return = isset($this->attributes[$index]) ? $this->attributes[$index] : false;
		}

		if ('image' === $index && $return == true && strlen($return) === 24 && preg_match("/^[a-z0-9]{24}$/", $return)) {
			$return = "/assetimage_{$return}_300sc200.png";
		}
		return $return;

	}

	/**
	 * Set inlanguage
	 *
	 * @param array $inlanguage
	 * @return self
	 */
	public function setInlanguage($inlanguage) {
		if (!is_array($this->inlanguage)) {
			$this->inlanguage = [];
		}

		if (!in_array($inlanguage, $this->inlanguage)) {
			$this->inlanguage[] = $inlanguage;
		}
		return $this;
	}

	/**
	 * Get inlanguage
	 *
	 * @return array $inlanguage
	 */
	public function getInlanguage() {
		return $this->inlanguage;
	}

	/**
	 * Set draft
	 *
	 * @param hash $draft
	 * @return self
	 */
	public function setDraft($draft) {
		$this->draft = $draft;
		return $this;
	}

	/**
	 * Get draft
	 *
	 * @return hash $draft
	 */
	public function getDraft() {
		return $this->draft;
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
	 * Set editmode
	 *
	 * @param hash $editmode
	 * @return self
	 */
	public function setEditmode($editmode) {
		$this->editmode = $editmode;
		return $this;
	}

	/**
	 * Get editmode
	 *
	 * @return hash $editmode
	 */
	public function getEditmode() {
		return $this->editmode;
	}

	/**
	 * Set parent
	 *
	 * @param Metacope\Mcedit\Model\DocumentModel $parent
	 * @return self
	 */
	public function setParent(DocumentModel $parent) {

		$this->parent = $parent;
		return $this;
	}

	/**
	 * Get parent
	 *
	 * @return Metacope\Mcedit\Model\DocumentModel $parent
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * Set coordinates
	 *
	 * @param Metacope\Mcedit\Model\Coordinates $coordinates
	 * @return self
	 */
	public function setCoordinates(Coordinates $coordinates) {
		$this->coordinates = $coordinates;
		return $this;
	}

	/**
	 * Get coordinates
	 *
	 * @return Metacope\Mcedit\Model\Coordinates $coordinates
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
	 * Set author
	 *
	 * @param Metacope\Mcedit\Model\UserModel $author
	 * @return self
	 */
	public function setAuthor(UserModel $author) {
		$this->author = $author;
		return $this;
	}

	/**
	 * Get author
	 *
	 * @return Metacope\Mcedit\Model\UserModel $author
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * Set tags
	 *
	 * @param collection $tags
	 * @return self
	 */
	public function setTags($tags) {
		$this->tags = $tags;
		return $this;
	}

	/**
	 * Get tags
	 *
	 * @return collection $tags
	 */
	public function getTags() {
		return $this->tags && is_array($this->tags) ? $this->tags : [];
	}

	/**
	 * Add like
	 *
	 * @param Metacope\Mcedit\Model\UserModel $like
	 */
	public function addLike(\Metacope\Mcedit\Model\UserModel $like) {
		$this->likes[] = $like;
	}

	/**
	 * Remove like
	 *
	 * @param Metacope\Mcedit\Model\UserModel $like
	 */
	public function removeLike(\Metacope\Mcedit\Model\UserModel $like) {
		$this->likes->removeElement($like);
	}

	/**
	 * Get likes
	 *
	 * @return \Doctrine\Common\Collections\Collection $likes
	 */
	public function getLikes() {
		return $this->likes;
	}

	/**
	 * Add dislike
	 *
	 * @param Metacope\Mcedit\Model\UserModel $dislike
	 */
	public function addDislike(\Metacope\Mcedit\Model\UserModel $dislike) {
		$this->dislikes[] = $dislike;
	}

	/**
	 * Remove dislike
	 *
	 * @param Metacope\Mcedit\Model\UserModel $dislike
	 */
	public function removeDislike(\Metacope\Mcedit\Model\UserModel $dislike) {
		$this->dislikes->removeElement($dislike);
	}

	/**
	 * Get dislikes
	 *
	 * @return \Doctrine\Common\Collections\Collection $dislikes
	 */
	public function getDislikes() {
		return $this->dislikes;
	}
}
