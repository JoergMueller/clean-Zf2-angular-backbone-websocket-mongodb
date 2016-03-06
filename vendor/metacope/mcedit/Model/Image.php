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
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Metacope\Mcedit\Model\ImageAttributes;

/**
 * @MongoDB\Document
 * @MongoDB\Index(keys={"coordinates"="2d"})
 */
class Image {
	/** @MongoDB\Id */
	public $id;

	/**
	 * @MongoDB\Distance
	 */
	public $distance;

	/**
	 * @MongoDB\Field
	 * @MongoDB\Index(order="asc")
	 */
	public $name;

	/** @MongoDB\File */
	public $file;

	/**
	 * @MongoDB\Field
	 * @MongoDB\Index(order="asc")
	 */
	public $uploadDate;

	/** @MongoDB\EmbedOne(targetDocument="ImageAttributes") */
	public $attributes;

	/**
	 * @MongoDB\Field
	 */
	public $length;

	/** @MongoDB\Field */
	public $chunkSize;

	/** @MongoDB\Field */
	public $md5;

	/** @MongoDB\ReferenceOne(targetDocument="UserModel") */
	protected $owner;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(order="asc")
	 */
	protected $folder;

	/**
	 * @MongoDB\Field(type="int")
	 * @MongoDB\Index(order="asc")
	 */
	protected $isbackground = 0;

	/**
	 * @MongoDB\Field(type="collection")
	 * @MongoDB\Index(order="asc")
	 */
	protected $randompoint = [];

	/** @MongoDB\EmbedOne(targetDocument="Coordinates") */
	protected $coordinates = null;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
	 */
	protected $token;

	/** @MongoDB\PreFlush */
	public function findGeoCoords() {
		$georeverse = $this->getAttributes()->getGeoreverse();
		if ($georeverse && strlen($georeverse)) {

			if ($coords = Metacope\Mcedit\Model\Coordinates::findGeoCoords($georeverse)) {
				$this->setCoordinates($coords);
			}
		}
	}

	public function __construct() {
		$this->randompoint = [rand(-180, 180), rand(-180, 180)];
		$this->token = \Metacope\Mcedit\Model\UserModel::newPassword(16, null, 0);
	}

	public function toArray() {
		$return = get_object_vars($this);
		$return['attributes'] = $return['attributes']->toArray();
		return $return;
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
	 * Set name
	 *
	 * @param string $name
	 * @return self
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set file
	 *
	 * @param file $file
	 * @return self
	 */
	public function setFile($file) {
		$this->file = $file;
		return $this;
	}

	/**
	 * Get file
	 *
	 * @return file $file
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Set uploadDate
	 *
	 * @param string $uploadDate
	 * @return self
	 */
	public function setUploadDate($uploadDate) {
		$this->uploadDate = $uploadDate;
		return $this;
	}

	/**
	 * Get uploadDate
	 *
	 * @return string $uploadDate
	 */
	public function getUploadDate() {
		return $this->uploadDate;
	}

	/**
	 * Set attributes
	 *
	 * @param Metacope\Mcedit\Model\ImageAttributes $attributes
	 * @return self
	 */
	public function setAttributes(\Metacope\Mcedit\Model\ImageAttributes $attributes) {
		$this->attributes = $attributes;
		return $this;
	}

	/**
	 * Get attributes
	 *
	 * @return Metacope\Mcedit\Model\ImageAttributes $attributes
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Set length
	 *
	 * @param string $length
	 * @return self
	 */
	public function setLength($length) {
		$this->length = $length;
		return $this;
	}

	/**
	 * Get length
	 *
	 * @return string $length
	 */
	public function getLength() {
		return $this->length;
	}

	/**
	 * Set chunkSize
	 *
	 * @param string $chunkSize
	 * @return self
	 */
	public function setChunkSize($chunkSize) {
		$this->chunkSize = $chunkSize;
		return $this;
	}

	/**
	 * Get chunkSize
	 *
	 * @return string $chunkSize
	 */
	public function getChunkSize() {
		return $this->chunkSize;
	}

	/**
	 * Set md5
	 *
	 * @param string $md5
	 * @return self
	 */
	public function setMd5($md5) {
		$this->md5 = $md5;
		return $this;
	}

	/**
	 * Get md5
	 *
	 * @return string $md5
	 */
	public function getMd5() {
		return $this->md5;
	}

	/**
	 * Set owner
	 *
	 * @param Metacope\Mcedit\Model\UserModel $owner
	 * @return self
	 */
	public function setOwner(\Metacope\Mcedit\Model\UserModel $owner) {
		$this->owner = $owner;
		return $this;
	}

	/**
	 * Get owner
	 *
	 * @return Metacope\Mcedit\Model\UserModel $owner
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * Set folder
	 *
	 * @param string $folder
	 * @return self
	 */
	public function setFolder($folder) {
		$this->folder = $folder;
		return $this;
	}

	/**
	 * Get folder
	 *
	 * @return string $folder
	 */
	public function getFolder() {
		return $this->folder;
	}

	/**
	 * Set isbackground
	 *
	 * @param int $isbackground
	 * @return self
	 */
	public function setIsbackground($isbackground) {
		$this->isbackground = $isbackground;
		return $this;
	}

	/**
	 * Get isbackground
	 *
	 * @return int $isbackground
	 */
	public function getIsbackground() {
		return $this->isbackground;
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
	 * Set randompoint
	 *
	 * @param collection $randompoint
	 * @return self
	 */
	public function setRandompoint($randompoint) {
		$this->randompoint = $randompoint;
		return $this;
	}

	/**
	 * Get randompoint
	 *
	 * @return Doctrine\MongoDB\Collections $randompoint
	 */
	public function getRandompoint() {
		return $this->randompoint;
	}

	/**
	 * Set coordinates
	 *
	 * @param Metacope\Mcedit\Model\Coordinates $coordinates
	 * @return self
	 */
	public function setCoordinates(\Metacope\Mcedit\Model\Coordinates $coordinates) {
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
}
