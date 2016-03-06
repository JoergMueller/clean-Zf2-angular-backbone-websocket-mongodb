<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id: cli.php 4 2012-08-02 10:01:53Z joerg.mueller $
 */
// }}}
function askConsole($question, $default = "") {
  if($default) {
	$question .= " [$default]";
  }
  $question .= "\t: ";
  echo $question;
  $answer = trim(fread(STDIN, 255));
  return $answer ? $answer : $default;
}
function getRandomDate() {
  return rand(mktime(0, 0, 0, 12, 31, 2008), mktime(0, 0, 0, 1, 6, 2011));
}


/**
 * Local Variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */