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
use Metacope\Mcedit\Model\CountryModel;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @MongoDB\Document(collection="Clients", indexes={
 *     @MongoDB\Index(keys={"sheet.coordinates"="2d"})
 * }))
 * @MongoDB\HasLifecycleCallbacks
 */
class ClientModel implements InputFilterAwareInterface {
	protected $inputFilter;

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Distance
	 */
	public $distance;

	/** @MongoDB\EmbedOne(targetDocument="Coordinates") */
	protected $coordinates = null;

	/** @MongoDB\ReferenceOne(targetDocument="CountryModel") */
	protected $country = null;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $datecreate;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index()
	 */
	protected $name;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $firm;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $zipcode;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $streetnr;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $city;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $phone;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $fax;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $mail;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $description;

	/**
	 * @MongoDB\Field(type="string")
	 */
	protected $stylesheet;

	/**
	 * @MongoDB\Field(type="hash")
	 */
	protected $layout = [];

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
	 */
	protected $token;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
	 */
	protected $shortname;

	/** @MongoDB\PreFlush */
	public function findGeoCoords() {
		if ($this->city && strlen($this->city)) {

			if ($coords = \Metacope\Mcedit\Model\Coordinates::findGeoCoords("$this->zipcode,$this->city,$this->streetnr")) {
				$this->setCoordinates($coords);
			}
		}

	}

	public function __construct(array $params = []) {
		foreach ($params as $k => $v) {
			$this->{$k} = $v;
		}

		$this->datecreate = new \DateTime();
		$this->token = \Metacope\Mcedit\Model\UserModel::newPassword(16, null, 0);
	}

	public function toArray($lang = null) {
		$o = get_object_vars($this);
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

	public function getFullname() {
		return $this->name . ' ' . $this->firm . ' &nbsp;' . ' ( ' . $this->shortname . ' )';
	}

	public function exchangeArray($data, $dm, $flag = 'user') {

	}

	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception('Not used');
	}

	public function getInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
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
	 * Set shortname
	 *
	 * @param string $shortname
	 * @return self
	 */
	public function setShortname($shortname) {
		$this->shortname = $shortname;
		return $this;
	}

	/**
	 * Get shortname
	 *
	 * @return string $shortname
	 */
	public function getShortname() {
		return $this->shortname;
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
	 * Set firm
	 *
	 * @param string $firm
	 * @return self
	 */
	public function setFirm($firm) {
		$this->firm = $firm;
		return $this;
	}

	/**
	 * Get firm
	 *
	 * @return string $firm
	 */
	public function getFirm() {
		return $this->firm;
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
	 * Set coordinates
	 *
	 * @param Model\Coordinates $coordinates
	 * @return self
	 */
	public function setCoordinates(Coordinates $coordinates) {
		$this->coordinates = $coordinates;
		return $this;
	}

	/**
	 * Get coordinates
	 *
	 * @return Model\Coordinates $coordinates
	 */
	public function getCoordinates() {
		return $this->coordinates;
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
		return str_replace(' ', '.', $this->phone);
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
		return str_replace(' ', '.', $this->fax);
	}

	/**
	 * Set mail
	 *
	 * @param string $mail
	 * @return self
	 */
	public function setMail($mail) {
		$this->mail = $mail;
		return $this;
	}

	/**
	 * Get mail
	 *
	 * @return string $mail
	 */
	public function getMail() {
		return $this->mail;
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 * @return self
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set stylesheet
	 *
	 * @param string $stylesheet
	 * @return self
	 */
	public function setStylesheet($stylesheet) {
		$this->stylesheet = $stylesheet;
		return $this;
	}

	/**
	 * Get stylesheet
	 *
	 * @return string $stylesheet
	 */
	public function getStylesheet() {
		return $this->stylesheet;
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
	 * Set country
	 *
	 * @param Metacope\Mcedit\Model\CountryModel $country
	 * @return self
	 */
	public function setCountry(\Metacope\Mcedit\Model\CountryModel $country) {
		$this->country = $country;
		return $this;
	}

	/**
	 * Get country
	 *
	 * @return Metacope\Mcedit\Model\CountryModel $country
	 */
	public function getCountry() {
		return $this->country;
	}
}
