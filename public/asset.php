<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id:$
 */
// }}}

chdir(dirname(__DIR__));

require 'init_autoloader.php';

ob_start();

class AssetHandler {

	protected $app;
	protected $dm;

	protected $key = false;
	protected $cache = true;

	protected $response = null;

	protected $_properties = [];

	public function __construct(array $params = []) {
		$this->app = Zend\Mvc\Application::init(require 'config/application.config.php');
		$this->dm = $this->app->getServiceManager()->get('doctrine.documentmanager.odm_default');

		$this->cache = $this->app->getServiceManager()->get('cache');

		$response = $this->app->getServiceManager()->get('response');
		$response->getHeaders()->clearHeaders();

		foreach ($params as $k => $v) {
			$this->{$k} = $v;
		}

		$this->suffix = 'jpg' == $this->suffix ? 'jpeg' : $this->suffix;

		$this->_encoding();
		$this->maxage = 60 * 60 * 24 * 30;
		$this->expire = date('D, d M Y H:i:s', time() + $this->maxage);

		$this->key = md5($this->file);
		$_source = $this->cache->getItem($this->key, $success);

		if ($success) {
			ob_end_clean();

			if ((isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"')) {
				if ($this->encoding != 'none') {
					$this->setHeader("Content-Encoding", $this->encoding);
				}

				$this->setHeader("Expires", $this->expire . " GMT", true);
				$this->setHeader("Cache-Control", "maxage=" . $this->maxage);
				$this->sendHeader();

				$this->sendRawHeader("Etag: " . md5($_source));
				$this->sendRawHeader("HTTP/1.0 304 Not Modified");
				$this->sendRawHeader("Content-Length: 0");
				exit();
			}

			$response->getHeaders()
				->addHeaderLine('Etag: ' . md5($_source))
				->addHeaderLine('Expires: ' . $this->expire . ' GMT')
				->addHeaderLine('Pragma: cache')
				->addHeaderLine('Cache-Control: max-age=' . $this->maxage)
				->addHeaderLine('Content-Type: image/' . $this->suffix)
				->addHeaderLine('Content-Transfer-Encoding: binary')
				->addHeaderLine('Content-Length: ' . mb_strlen($_source));

			$response->setContent($_source);
			$response->sendHeaders();
			$response->sendContent();
			die;

		}

		$this->asset = $asset = $this->dm->getRepository('Metacope\Mcedit\Model\Image')->find($this->id);
	}

	public function __set($name, $value) {
		$this->_properties[$name] = $value;
	}

	public function __get($name) {
		return isset($this->_properties[$name]) ? $this->_properties[$name] : null;
	}

	public function dump() {
		$response = $this->app->getServiceManager()->get('response');
		$response->getHeaders()->clearHeaders();
		$header = $response->getHeaders();

		switch ($this->type) {

		case 'image':

			$size = explode($this->delimiter, $this->size);
			$im = new Imagick();

			if (file_exists(getcwd() . "/public/img/{$this->id}.jpg")) {
				$_source = file_get_contents(getcwd() . "/public/img/{$this->id}.jpg");
				$im->readimageblob($_source);
			} else {
				$im->readimageblob($this->asset->getFile()->getBytes());
			}
			$im->setImageFormat($this->suffix);

			$orgWidth = $im->getImageWidth();
			$orgHeight = $im->getImageHeight();

			switch ($this->delimiter) {

			case 'cropFromBottom':
				list($newX, $newY) = $this->scaleImage($orgWidth, $orgHeight, $size[0] + 100, $size[1] + 100);
				$im->thumbnailImage($newX, $newY);
				$im->cropImage($size[0], $size[1], 0, 100);
				break;

			case 'c':
				$im->cropThumbnailImage($size[0], $size[1]);
				break;

			case 'h':
				$im->thumbnailImage(-1, $size[1]);
				break;

			case 's':
				list($newX, $newY) = $this->scaleImage($size[0], $size[1], $orgWidth, $orgHeight);
				$im->thumbnailImage($newX, $newY);
				break;

			case 'sc':
			case 'scsc':
				list($newX, $newY) = $this->scaleImage($size[0], $size[1], $orgWidth, $orgHeight);
				$im->thumbnailImage($newX, -1);
				$im->cropThumbnailImage($size[0], -1);
				break;

			case 'cs':
				$im->cropThumbnailImage($size[0], $size[1]);
				list($newX, $newY) = $this->scaleImage($size[0], $size[1], $orgWidth, $orgHeight);
				$im->thumbnailImage($newX, $newY);
				break;

			case 'w':
				$im->thumbnailImage($size[0], -1);
				break;

			case 'b':

				$im->thumbnailImage(600, 375);
				$im->blurImage(14, 9);

				if ('png' != $this->suffix) {
					$im->setCompressionQuality(90);
					$this->suffix = 'jpg';
				}

				$im->stripImage();
				$_source = $im->getImageBlob();

				if ($this->cache) {
					$this->cache->setItem($this->key, $_source);
				}

				$header->clearHeaders();
				$header->addHeaderLine('Etag: ' . md5($_source))
					->addHeaderLine('Expires: ' . $this->expire . ' GMT')
					->addHeaderLine('Pragma: cache')
					->addHeaderLine('Cache-Control: max-age=' . $this->maxage)
					->addHeaderLine('Content-Type: image/' . $this->suffix)
					->addHeaderLine('Content-Length: ' . strlen($_source));

				$response->setContent($_source);
				return $response;
				break;
			}

			break;

		case 'file':
			break;
		}

		if ('png' != $this->suffix) {

			$im->setCompressionQuality(90);
			$this->suffix = 'jpg';
		}

		$im->stripImage();
		$_source = $im->getImageBlob();

		if ($this->cache) {
			$this->cache->setItem($this->key, $_source);
		}

		$response->getHeaders()->clearHeaders();
		$response->getHeaders()
			->addHeaderLine('Etag: ' . md5($_source))
			->addHeaderLine('Expires: ' . $this->expire . ' GMT')
			->addHeaderLine('Pragma: cache')
			->addHeaderLine('Cache-Control: max-age=' . $this->maxage)
			->addHeaderLine('Content-Type: image/' . $this->suffix)
			->addHeaderLine('Content-Transfer-Encoding: binary')
			->addHeaderLine('Content-Length: ' . mb_strlen($_source));

		$response->setContent($_source);
		return $response;
	}

	public function scaleImage($x, $y, $cx, $cy) {
		// Set the default NEW values to be the old, in case it doesn't even
		// need scaling
		list($nx, $ny) = [
			$x,
			$y,
		];

		// If image is generally smaller, don't even bother
		if ($x >= $cx || $y >= $cx) {

			// Work out ratios
			if ($x > 0) {
				$rx = $cx / $x;
			}

			if ($y > 0) {
				$ry = $cy / $y;
			}

			// Use the lowest ratio, to ensure we don't go over the wanted image
			// size
			if ($rx > $ry) {
				$r = $ry;
			} else {
				$r = $rx;
			}

			// Calculate the new size based on the chosen ratio
			$nx = intval($x * $r);
			$ny = intval($y * $r);
		}

		// Return the results
		return [
			$nx,
			$ny,
		];
	}

	public function setHeader($name, $value, $replace = false) {
		$name = $this->_normalizeHeader($name);
		$value = (string) $value;

		$this->_header[] = [
			'name' => $name,
			'value' => $value,
			'replace' => $replace,
		];
	}

	public function sendHeader() {
		foreach ($this->_header as $header) {
			header($header['name'] . ': ' . $header['value'], $header['replace']);
		}
	}

	public function sendRawHeader($value) {
		header($value);
	}

	protected function _normalizeHeader($name) {
		$filtered = str_replace([
			'-',
			'_',
		], ' ', (string) $name);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);
		return $filtered;
	}

	protected function _encoding() {
		// Determine supported compression method
		$http_accept_encoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';
		$http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

		$gzip = strstr($http_accept_encoding, 'gzip');
		$deflate = strstr($http_accept_encoding, 'deflate');

		$this->gzip = $gzip;
		$this->deflate = $deflate;

		$encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

		// Check for buggy versions of Internet Explorer
		$matches = false;
		if (!strstr($http_user_agent, 'Opera') && preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $http_user_agent, $matches)) {
			$version = floatval($matches[1]);
			if ($version < 6 || (6 == $version && !strstr($http_user_agent, 'EV1'))) {
				$encoding = 'none';
			}
		}
		$this->encoding = $encoding; // $encoding;
	}

	private function drawWatermark(&$image, &$watermark, $padding = 10) {

		// Check if the watermark is bigger than the image
		$image_width = $image->getImageWidth();
		$image_height = $image->getImageHeight();
		$watermark_width = $watermark->getImageWidth();
		$watermark_height = $watermark->getImageHeight();

		if ($image_width < $watermark_width + $padding || $image_height < $watermark_height + $padding) {
			return false;
		}

		// Calculate each position
		$positions = [];
		$positions[] = [
			0 + $padding,
			0 + $padding,
		];
		$positions[] = [
			$image_width - $watermark_width - $padding,
			0 + $padding,
		];
		$positions[] = [
			$image_width - $watermark_width - $padding,
			$image_height - $watermark_height - $padding,
		];
		$positions[] = [
			0 + $padding,
			$image_height - $watermark_height - $padding,
		];

		// Initialization
		$min = null;
		$min_colors = 0;

		// Calculate the number of colors inside each region
		// and retrieve the minimum
		foreach ($positions as $position) {
			$colors = $image->getImageRegion($watermark_width, $watermark_height, $position[0], $position[1])->getImageColors();

			if (null === $min || $colors <= $min_colors) {
				$min = $position;
				$min_colors = $colors;
			}
		}

		// Draw the watermark
		$image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $min[0], $min[1]);

		return true;
	}
}

$asset = new AssetHandler($_GET);
$response = $asset->dump();

ob_end_clean();

$response->sendHeaders();
$response->sendContent();

/**
 * Local Variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
