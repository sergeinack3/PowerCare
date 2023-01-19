<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;

$do = new CDoObjectAddEdit("CIdSante400", "id_sante400_id");

// Indispensable pour ne pas écraser les paramètes dans action
if (!isset($_POST["ajax"]) || !$_POST["ajax"]) {
  $do->redirect = null;
}
$do->doIt();