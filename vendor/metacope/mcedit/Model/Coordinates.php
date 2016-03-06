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
use Zend\Http\Client;

/**
 * @MongoDB\EmbeddedDocument
 */
class Coordinates
{
    protected static $_attemp = 0;

    /** @MongoDB\Float */
    protected $lng;

    /** @MongoDB\Float */
    protected $lat;

    public function __construct(array $options = [])
    {
        if (isset($options['lng']) && isset($options['lat'])) {
            $this->lng = $options['lng'];
            $this->lat = $options['lat'];
        }
    }

    /*
     * static
     */
    public static function findGeoCoords($option, $flag = true)
    {
        if (!is_array($option) && is_string($option)) {
            $option = urldecode($option);
            $option = trim(preg_replace('/[^a-z0-9öäüß ]/si', '', $option));
            $uriparam = trim(str_replace(' ', '+', $option));
        } elseif (is_array($option)) {
            $street = isset($option['streetnr']) ? $option['streetnr'] : '';
            $city = isset($option['city']) ? $option['city'] : '';
            $zipcode = isset($option['zipcode']) ? $option['zipcode'] : '';
            $uriparam = implode('+', [$street, $city, $zipcode]);
        }
        $uriparam = urlencode($uriparam);
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address={$uriparam}&sensor=false";

        $client = new Client();
        $client->setUri($url);
        $client->setOptions([
            'maxredirects' => 0,
            'timeout' => 30,
        ]);
        $response = $client->send();
        $content = gzdecode($response->getContent());

        $return = (array) json_decode($content);
        if (isset($return['status']) && 'OVER_QUERY_LIMIT' == $return['status']) {
            self::$_attemp += 1;
            if (self::$_attemp > 3) {
                throw new \Exception('3 Attemps on Google-GeoReverse');
            }

            sleep(2);
            $return = self::findGeoCoords($option, $flag);
        }

        if ($return instanceof \Metacope\Mcedit\Model\Coordinates) {
            return $return;
        } elseif (isset($return['status']) && 'OK' == $return['status']) {
            if (true == $flag) {
                return new \Metacope\Mcedit\Model\Coordinates((array) $return['results'][0]->geometry->location);
            }
            return $return;
        }
        return null;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return self
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    /**
     * Get lng
     *
     * @return float $lng
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return self
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * Get lat
     *
     * @return float $lat
     */
    public function getLat()
    {
        return $this->lat;
    }
}
