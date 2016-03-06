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
class DocumentSheetModel
{
    /**
     * @MongoDB\Field(type="hash")
     * @MongoDB\Index(order="asc")
     */
    public $title;

    /**
     * @MongoDB\Field(type="hash")
     * @MongoDB\Index(order="asc")
     */
    public $description;

    /**
     * @MongoDB\Field(type="hash")
     * @MongoDB\Index(order="asc")
     */
    public $keywords;

    /**
     * @MongoDB\Field(type="hash")
     * @MongoDB\Index(order="asc")
     */
    public $indexfollow;

    public function __construct(array $params = [], $lang = 'de')
    {

        foreach ($params as $k => $v) {
            if (isset($this->{$k})) {
                $this->{$k} = $v;
            }

        }
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
     * Set keywords
     *
     * @param hash $keywords
     * @return self
     */
    public function setKeywords($keywords, $lang = 'de')
    {
        $this->keywords[$lang] = $keywords;
        return $this;
    }

    /**
     * Get keywords
     *
     * @return hash $keywords
     */
    public function getKeywords($lang = 'de')
    {
        return isset($this->keywords[$lang]) && is_array($this->keywords[$lang])
        ? $this->keywords[$lang]
        : [];
    }

    /**
     * Set description
     *
     * @param hash $description
     * @return self
     */
    public function setDescription($description, $lang = 'de')
    {
        $this->description[$lang] = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return hash $description
     */
    public function getDescription($lang = 'de')
    {
        return isset($this->description[$lang]) ? $this->description[$lang] : (isset($this->description['de']) ? $this->description['de'] : '');
    }

    /**
     * Set indexfollow
     *
     * @param hash $indexfollow
     * @return self
     */
    public function setIndexfollow($indexfollow, $lang = 'de')
    {
        $this->indexfollow[$lang] = $indexfollow;
        return $this;
    }

    /**
     * Get indexfollow
     *
     * @return hash $indexfollow
     */
    public function getIndexfollow($lang = 'de')
    {
        return isset($this->indexfollow[$lang]) ? $this->indexfollow[$lang] : (isset($this->indexfollow['de']) ? $this->indexfollow['de'] : '');
    }
}
