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
 * @MongoDB\Document(collection="Countries", indexes={
 * })
 * @MongoDB\HasLifecycleCallbacks
 */
class CountryModel implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $dm;

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @MongoDB\Index(order="asc")
     */
    protected $iso;

    /**
     * @MongoDB\Field(type="hash")
     * @MongoDB\Index(order="asc")
     */
    protected $title;

    /**
     * @MongoDB\Field(type="string")
     * @MongoDB\Index(unique=true, sparse=true, dropDups=true)
     */
    protected $token;

    public function __construct()
    {
        $this->datecreate = new \DateTime();
        $this->token = \Metacope\Mcedit\Model\UserModel::newPassword(16, null, 0);
    }

    public function toArray($lang = null)
    {
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

    public function exchangeArray($data, $dm, $flag = 'user')
    {

    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('Not used');
    }

    public function getInputFilter()
    {
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set iso
     *
     * @param string $iso
     * @return self
     */
    public function setIso($iso)
    {
        $this->iso = $iso;
        return $this;
    }

    /**
     * Get iso
     *
     * @return string $iso
     */
    public function getIso()
    {
        return $this->iso;
    }

    /**
     * Set title
     *
     * @param hash $title
     * @return self
     */
    public function setTitle($title, $lang = 'de')
    {
        $this->title[$lang] = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return hash $title
     */
    public function getTitle($lang = 'de')
    {
        return isset($this->title[$lang]) ? $this->title[$lang] : '';
    }

    /**
     * Set token
     *
     * @param string $token
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }
}
