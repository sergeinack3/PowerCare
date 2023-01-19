<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\Api\CAPICFunctions;
use Ox\Mediboard\Mediusers\Api\CAPITools;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$prettify = CValue::get('prettify');

$user     = CMediusers::get();
$function = $user->loadRefFunction();

$functions = $function->loadListWithPerms(PERM_READ);

uasort(
  $functions,
  function ($a, $b) {
    return strcmp($a->_view, $b->_view);
  }
);

$api_objects = array(
  'account'   => $user->_view,
  'functions' => array()
);

/** @var CFunctions $_function */
foreach ($functions as $_function) {
  /** @var CAPICFunctions $_api_object */
  $_api_object = CAPICFunctions::mbObjectToAPI($_function);

  $api_objects['functions'][] = array(
    'function' => $_api_object,
  );
}

CAPITools::json($api_objects, 200, $prettify);