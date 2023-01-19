<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\Api\CAPITools;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$prettify = CValue::get('prettify');

$user = CMediusers::get();

$types = CUser::$types;

$api_objects = array(
  'account' => $user->_view,
  'types'   => array()
);

foreach ($types as $_id => $_type) {
  $_api_type = array(
    'id'  => $_id,
    'nom' => trim($_type),
  );

  $api_objects['types'][] = array(
    'type' => $_api_type,
  );
}

CAPITools::json($api_objects, 200, $prettify);