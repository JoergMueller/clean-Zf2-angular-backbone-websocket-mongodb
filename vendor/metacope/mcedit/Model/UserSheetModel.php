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
use Metacope\Mcedit\Model\Coordinates;

/**
 * @MongoDB\EmbeddedDocument
 * @MongoDB\HasLifecycleCallbacks
 */
class UserSheetModel {
	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $gender = 'mr';

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $firstname;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $name;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $city;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $streetnr;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $zipcode;

	/** @MongoDB\EmbedOne(targetDocument="Coordinates") */
	public $coordinates;

	/**
	 * @MongoDB\Field(type="date")
	 * @MongoDB\Index(sparse=true)
	 */
	public $birthday;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $phone;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $fax;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $teaminfo;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(sparse=true)
	 */
	public $mobile;

	/** @MongoDB\PreFlush */
	public function findGeoCoords() {
		// $this->setCoordinates(\Metacope\Mcedit\Model\Coordinates::findGeoCoords($this->toArray(), true));
	}

	public function __construct(array $params = []) {
		foreach ($params as $k => $v) {
			if (isset($this->$k)) {
				$this->$k = $v;
			}
		}

	}

	public function toArray() {
		return get_object_vars($this);
	}

	/**
	 * Set gender
	 *
	 * @param string $gender
	 * @return self
	 */
	public function setGender($gender) {
		$this->gender = $gender;
		return $this;
	}

	/**
	 * Get gender
	 *
	 * @return string $gender
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * Set firstname
	 *
	 * @param string $firstname
	 * @return self
	 */
	public function setFirstname($firstname) {
		$this->firstname = $firstname;
		return $this;
	}

	/**
	 * Get firstname
	 *
	 * @return string $firstname
	 */
	public function getFirstname() {
		return $this->firstname;
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
	 * Set city
	 *
	 * @param string $city
	 * @return self
	 */
	public function setCity($city) {
		$this->city = $city;
		return $this;
	}

	/**
	 * Get city
	 *
	 * @return string $city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Set streetnr
	 *
	 * @param string $streetnr
	 * @return self
	 */
	public function setStreetnr($streetnr) {
		$this->streetnr = $streetnr;
		return $this;
	}

	/**
	 * Get streetnr
	 *
	 * @return string $streetnr
	 */
	public function getStreetnr() {
		return $this->streetnr;
	}

	/**
	 * Set zipcode
	 *
	 * @param string $zipcode
	 * @return self
	 */
	public function setZipcode($zipcode) {
		$this->zipcode = $zipcode;
		return $this;
	}

	/**
	 * Get zipcode
	 *
	 * @return string $zipcode
	 */
	public function getZipcode() {
		return $this->zipcode;
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

	/**
	 * Set birthday
	 *
	 * @param date $birthday
	 * @return self
	 */
	public function setBirthday($birthday) {
		$this->birthday = $birthday;
		return $this;
	}

	/**
	 * Get birthday
	 *
	 * @return date $birthday
	 */
	public function getBirthday() {
		return $this->birthday;
	}

	/**
	 * Set phone
	 *
	 * @param string $phone
	 * @return self
	 */
	public function setPhone($phone) {
		$this->phone = $phone;
		return $this;
	}

	/**
	 * Get phone
	 *
	 * @return string $phone
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Set fax
	 *
	 * @param string $fax
	 * @return self
	 */
	public function setFax($fax) {
		$this->fax = $fax;
		return $this;
	}

	/**
	 * Get fax
	 *
	 * @return string $fax
	 */
	public function getFax() {
		return $this->fax;
	}

	/**
	 * Set mobile
	 *
	 * @param string $mobile
	 * @return self
	 */
	public function setMobile($mobile) {
		$this->mobile = $mobile;
		return $this;
	}

	/**
	 * Get mobile
	 *
	 * @return string $mobile
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * Set teaminfo
	 *
	 * @param string $teaminfo
	 * @return self
	 */
	public function setTeaminfo($teaminfo) {
		$this->teaminfo = $teaminfo;
		return $this;
	}

	/**
	 * Get teaminfo
	 *
	 * @return string $teaminfo
	 */
	public function getTeaminfo() {
		return $this->teaminfo;
	}
}
