<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CValue;

/**
 * Show exchange
 */
$exchange_id    = CValue::get("exchange_id");
$exchange_class = CValue::get("exchange_class");

$exchange = new $exchange_class;

$msg = "";
if ($exchange_id) {
  $exchange->load($exchange_id);

  if ($exchange->_id) {
    $msg = $exchange->_id;
  }
}

echo json_encode($msg);