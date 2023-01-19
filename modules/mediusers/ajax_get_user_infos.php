<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

$user_id = CValue::get('user_id');

$user = CMediusers::get($user_id);


if ($user->getPerm(PERM_READ)) {
  $data = array(
    'last_name' => utf8_encode($user->_user_last_name),
    'name'      => utf8_encode($user->_user_first_name),
    'address'   => utf8_encode($user->_user_adresse),
    'pc'        => utf8_encode($user->_user_cp),
    'city'      => utf8_encode($user->_user_ville),
    'phone'     => utf8_encode($user->_user_phone),
    'email'     => utf8_encode($user->_user_email),
    'apicrypt'  => utf8_encode($user->mail_apicrypt),
    'mssante'   => utf8_encode($user->mssante_address),
    'type'      => utf8_encode($user->_user_type),
    'adeli'     => utf8_encode($user->adeli),
    'rpps'      => utf8_encode($user->rpps)
  );
}
else {
  $data = array();
}
$str = json_encode($data);
echo json_encode($data);