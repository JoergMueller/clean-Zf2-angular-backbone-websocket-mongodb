<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

namespace Metacope\Mcedit\ViewHelper;

use Zend\Mvc\MvcEvent;
use Zend\View\Helper\AbstractHelper;

class CryptHelper extends AbstractHelper
{

    protected static $idmagic = 'JoergMu3LlERAvqQ87c92Fxmp4GSdO6jVYikBZKItW0Hf5XayDNbnPUChwTs1';

    public $e;
    public $servicemanager;

    public function __construct(MvcEvent $e)
    {
        $this->e = $e;
        $this->servicemanager = $e->getApplication()->getServiceManager();

    }

    public function __invoke()
    {
        return $this;
    }

    /**
     *
     */
    public function stringToId($string)
    {

        if (strlen($string) <= 5) {
            return 0;
        }
        $base = 61;
        $index = false;

        $string = substr($string, 5);
        $string = strrev($string);

        if (!$base) {
            $base = strlen($index);
        } else if (!$index) {
            $index = substr(self::$idmagic, 0, $base);
        }
        $out = 0;
        $len = strlen($string) - 1;
        for ($t = 0; $t <= $len; $t++) {
            $out = $out + strpos($index, substr($string, $t, 1)) * pow($base, $len - $t);
        }
        if ('' === $out) {
            return 0;
        }

        return intval($out);
    }

    /**
     *
     */
    public function idToString($id = false)
    {

        $base = 61;
        $index = false;

        if (!$base) {
            $base = strlen($index);
        } else if (!$index) {
            $index = substr(self::$idmagic, 0, $base);
        }
        $out = '';
        for ($t = floor(log10($id) / log10($base)); $t >= 0; $t--) {
            $a = floor($id / pow($base, $t));
            $out = $out . substr($index, $a, 1);
            $id = $id - ($a * pow($base, $t));
        }
        $out = strrev($out);
        $out = substr(md5($out), 0, 5) . $out;
        return $out;
    }
}
