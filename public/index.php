<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

define('REQUEST_MICROTIME', microtime(true));

error_reporting(E_ALL);
// // print every log message possible
// MongoLog::setLevel(MongoLog::ALL);  // all log levels
// MongoLog::setModule(MongoLog::ALL); // all parts of the driver

// // print significant events about replica set failover
// MongoLog::setLevel(MongoLog::INFO);
// MongoLog::setModule(MongoLog::RS);

// // print info- and diagnostic-level events for replica sets and connections
// MongoLog::setLevel(MongoLog::INFO | MongoLog::FINE);
// MongoLog::setModule(MongoLog::RS | MongoLog::CON);

$env = 'local';
if (function_exists('apache_getenv')) {
	$env = apache_getenv('APP_ENV');
}

if ($env !== 'production') {
	include getcwd() . '/public/htaccess.php';
}

if (!defined('WIDGET')) {
	define('WIDGET', getcwd() . '/elements/widgets');
}
if (!defined('PARTIAL')) {
	define('PARTIAL', getcwd() . '/vendor/metacope/mcedit/view/partials');
}

ini_set('display_errors', ('production' == $env ? 'off' : 'on'));

function remFiles($f) {
	$files = glob($f . '/*');
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}

		if (is_dir($file)) {
			remFiles($file);
		}

	}
	$files = glob($f . '/*');
	if (sizeof($files) <= 0 && getcwd() . '/data/cache' !== $f) {
		if (is_dir($f)) {
			rmdir($f);
		}
	}

};

if (isset($_GET['clean'])) {
	remFiles(getcwd() . '/data/cache');
}

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
	return false;
}

$GLOBALS['minify'] = true;
ob_start();

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();

$out = ob_get_clean();

ini_set('pcre.recursion_limit', '16777');
$re = '%# Collapse whitespace everywhere but in blacklisted elements.
		(?>			 # Match all whitespans other than single space.
		[^\S ]\s*	 # Either one [\t\r\n\f\v] and zero or more ws,
		| \s{2,}		# or two or more consecutive-any-whitespace.
		) # Note: The remaining regex consumes no text at all...
		(?=			 # Ensure we are not in a blacklist tag.
		[^<]*+		# Either zero or more non-"<" {normal*}
		(?:			 # Begin {(special normal*)*} construct
		<			 # or a < starting a non-blacklist tag.
		(?!/?(?:textarea|pre|script)\b)
		[^<]*+		# more non-"<" {normal*}
		)*+			 # Finish "unrolling-the-loop"
		(?:			 # Begin alternation group.
		<			 # Either a blacklist start tag.
		(?>textarea|pre|script)\b
		| \z			# or end of file.
		)			 # End alternation group.
		)	# If we made it here, we are not in a blacklist tag.
		%Six';

header('Access-Control-Allow-Origin: *');
if (!isset($GLOBALS['minify']) || true === $GLOBALS['minify']) {
	$out = preg_replace($re, ' ', $out);
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

print $out;
ob_end_flush();
