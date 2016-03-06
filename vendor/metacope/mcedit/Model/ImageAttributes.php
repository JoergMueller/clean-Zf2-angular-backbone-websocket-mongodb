<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class ImageAttributes {
	/**
	 * @MongoDB\Field(type="string")
	 */
	public $copyright;

	/**
	 * @MongoDB\Field(type="date")
	 */
	public $expire;

	/**
	 * @MongoDB\Field(type="hash")
	 */
	public $title;

	/**
	 * @MongoDB\Field(type="hash")
	 */
	public $alt;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $georeverse = null;

	/**
	 * @MongoDB\Field(type="hash")
	 */
	public $tag;

	public function __construct(array $params = []) {
		foreach ($params as $k => $v) {
			$this->{$k} = $v;
		}

	}

	public function toArray() {
		return get_object_vars($this);
	}

	/**
	 * Set copyright
	 *
	 * @param string $copyright
	 * @return self
	 */
	public function setCopyright($copyright) {
		$this->copyright = $copyright;
		return $this;
	}

	/**
	 * Get copyright
	 *
	 * @return string $copyright
	 */
	public function getCopyright() {
		return $this->copyright;
	}

	/**
	 * Set expire
	 *
	 * @param date $expire
	 * @return self
	 */
	public function setExpire($expire) {
		if (is_string($expire)) {
			$expire = new \DateTime($expire);
		}

		$this->expire = $expire;
		return $this;
	}

	/**
	 * Get expire
	 *
	 * @return date $expire
	 */
	public function getExpire() {
		return $this->expire;
	}

	/**
	 * Set title
	 *
	 * @param hash $title
	 * @return self
	 */
	public function setTitle($title, $lang = 'de') {
		$this->title[$lang] = $title;
		return $this;
	}

	/**
	 * Get title
	 *
	 * @return hash $title
	 */
	public function getTitle($lang = 'de') {
		return isset($this->title[$lang]) ? $this->title[$lang] : '';
	}

	/**
	 * Set alt
	 *
	 * @param hash $alt
	 * @return self
	 */
	public function setAlt($alt, $lang = 'de') {
		$this->alt[$lang] = $alt;
		return $this;
	}

	/**
	 * Get alt
	 *
	 * @return hash $alt
	 */
	public function getAlt($lang = 'de') {
		return isset($this->alt[$lang]) ? $this->alt[$lang] : '';
	}

	/**
	 * Set tag
	 *
	 * @param hash $tag
	 * @return self
	 */
	public function setTag($tag) {
		if (!empty($tag) && is_string($tag)) {
			$tag = explode(',', $tag);
		} elseif (!is_array($tag)) {
			$tag = [];
		}

		$this->tag = $tag;
		return $this;
	}

	/**
	 * Get tag
	 *
	 * @return hash $tag
	 */
	public function getTag($flag = 0) {

		return $flag === 0 ? $this->tag : implode(',', isset($this->tag) && is_array($this->tag) ? $this->tag : []);
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
}
