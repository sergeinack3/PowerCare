<?php
/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Input arrays preperations
 */
function fixISOEncoding($str)
{
    return mb_convert_encoding($str, 'Windows-1252', 'UTF-8');
}

// UTF decode inputs from ajax requests
if ((isset($_REQUEST["ajax"]) && $_REQUEST["ajax"]) && empty($_REQUEST["accept_utf8"]) || isset($_REQUEST["is_utf8"])) {
    $_GET     = array_map_recursive("fixISOEncoding", $_GET);
    $_POST    = array_map_recursive("fixISOEncoding", $_POST);
    $_COOKIE  = array_map_recursive("utf8_decode", $_COOKIE);
    $_REQUEST = array_map_recursive("utf8_decode", $_REQUEST);
}


// Emulates magic quotes
$_GET     = array_map_recursive("addslashes", $_GET);
$_POST    = array_map_recursive("addslashes", $_POST);
$_COOKIE  = array_map_recursive("addslashes", $_COOKIE);
$_REQUEST = array_map_recursive("addslashes", $_REQUEST);
