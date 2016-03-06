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

use Zend\Captcha\Image as Image;
use Zend\Session\Container;

$session = new Container('default');

$app = Zend\Mvc\Application::init(require 'config/application.config.php');

$captcha = new Image([
    'font' => getcwd() . '/public/fonts/arial.ttf',
    'width' => 150,
    'height' => 50,
    'wordlen' => 5,
    'dotNoiseLevel' => 25,
    'lineNoiseLevel' => 3]);

$captcha->setImgDir(getcwd() . '/public/images/captcha');
$captcha->setImgUrl('/images/captcha');

$id = $captcha->generate();

?>

<div class="form-group">
	<input type="hidden" name="captchaId" value="<?=$id;?>" />
	<img src="/images/captcha/<?=$id;?>.png" data-id="<?=$id;?>" class="captcha margin-20">
	<label class="block">Captcha: <small>klicken Sie auf das Bild um es neu zu laden</small><br>
	<input class="form-control" type="text" name="captcha" value="" placeholder="Buchstaben / Zahlenfolge"></label>
</div>
