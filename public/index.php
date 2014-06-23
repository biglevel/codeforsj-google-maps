<?php
/*
 * System Variables
 */
define('SOURCE',      realpath(dirname(__FILE__) . '/../'));
define('SITE_BASE',   '/');
define('ENVIRONMENT', (getenv('ENVIRONMENT') ? getenv('ENVIRONMENT') : 'production'));

/*
 * PHP Settings
 */
date_default_timezone_set('America/Los_Angeles');
ini_set('session.gc_maxlifetime', (3600*120));
session_set_cookie_params((3600*120));
session_name('badams');
session_start();

/*
 * Define Application Include Paths
 */
set_include_path(implode(PATH_SEPARATOR, array(
  // built-in functionality includes
  SOURCE . '/etc',
  SOURCE . '/library',
  SOURCE . '/includes',
  // 3rd party includes
  SOURCE . '/library/PEAR',
  get_include_path(),
)));

/*
 * Disable Magic Quotes (Brian Adam's machine GRRR)
 */
if (get_magic_quotes_gpc())
{
  function magicQuotes_awStripslashes(&$value, $key)
  {
    $value = stripslashes($value);
  }

  $gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
  array_walk_recursive($gpc, 'magicQuotes_awStripslashes');
}

/*
 * Initialize Autoloader
 */
require_once('Loader.php');
$loader = Loader::instance();

/*
 * Fire Application Up
 */
$site = new Site(SOURCE . '/layouts', SOURCE . '/modules');
$site->route();
$site->execute();
