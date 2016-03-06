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
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @MongoDB\Document(collection="Subscribers", indexes={
 * }))
 * @MongoDB\HasLifecycleCallbacks
 */
class Subscribers implements InputFilterAwareInterface {
	protected $inputFilter;

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="date")
	 */
	protected $datecreate;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, dropDups=true)
	 */
	protected $mail;

	public function __construct(array $params = []) {
		foreach ($params as $k => $v) {
			$this->{$k} = $v;
		}

		$this->datecreate = new \DateTime();
		$this->token = \Metacope\Mcedit\Model\UserModel::newPassword(16, null, 0);
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
}
