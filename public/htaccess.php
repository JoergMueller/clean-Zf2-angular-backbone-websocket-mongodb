<?php
// {{{ Header
/**
 *
 * @author joerg.mueller
 * @version $Id: htaccess.php 2 2012-05-30 05:44:53Z joerg.mueller $
 */
// }}}

$user_array = [
    'bb-baumaschinen' => 'grZ@wo8T$QL_4MO8',
    'xsus' => '44life',
];

function authenticate()
{
    header('WWW-Authenticate: Basic realm="development"');
    header('HTTP/1.0 401 Unauthorized');
    echo "You must enter a valid login ID and password to access this resource\n";
    exit;
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    authenticate();
} else {
    if (!isset($user_array[$_SERVER['PHP_AUTH_USER']])
        || $user_array[$_SERVER['PHP_AUTH_USER']] != $_SERVER['PHP_AUTH_PW']) {
        authenticate();
    }
}

/**
 * Local Variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
