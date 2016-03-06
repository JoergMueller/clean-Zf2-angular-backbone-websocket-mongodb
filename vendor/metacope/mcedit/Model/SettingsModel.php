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
 * @MongoDB\Document(collection="Settings", indexes={
 * })
 * @MongoDB\HasLifecycleCallbacks
 */
class SettingsModel implements InputFilterAwareInterface {
	protected $inputFilter;
	protected $dm;

	/**
	 * @MongoDB\Id
	 */
	protected $id;

	/**
	 * @MongoDB\Field(type="string")
	 * @MongoDB\Index(unique=true, dropDups=true)
	 */
	protected $index;

	/**
	 * @MongoDB\Field(type="hash")
	 * @MongoDB\Index(sparse=true)
	 */
	protected $data;

	public function __construct(array $params = []) {
		//
	}

	public function setDocumentManager($dm) {
		$this->dm = $dm;
	}

	public function setInputFilter(InputFilterInterface $inputFilter) {
		throw new \Exception('Not used');
	}

	public function getInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();

			$inputFilter->add([
				'name' => 'index',
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

	/**
	 * Get id
	 *
	 * @return id $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set index
	 *
	 * @param string $index
	 * @return self
	 */
	public function setIndex($index) {
		$this->index = $index;
		return $this;
	}

	/**
	 * Get index
	 *
	 * @return string $index
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Set data
	 *
	 * @param hash $data
	 * @return self
	 */
	public function setData($data) {
		$this->data = $data;
		return $this;
	}

	/**
	 * Get data
	 *
	 * @return hash $data
	 */
	public function getData() {
		return $this->data;
	}
}
