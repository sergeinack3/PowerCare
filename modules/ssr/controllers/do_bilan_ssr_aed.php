<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;
use Ox\Mediboard\Ssr\CBilanSSR;

$bilan = new CBilanSSR;
if ($bilan->sejour_id = CValue::post("sejour_id")) {
  if ($bilan->loadMatchingObject()) {
    $_POST["bilan_id"] = $bilan->_id;
  }
}

$do = new CDoObjectAddEdit("CBilanSSR");
$do->doIt();
