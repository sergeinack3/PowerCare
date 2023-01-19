<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$fields_email = array(
  "URL",
  "PRATICIEN - NOM",
  "PRATICIEN - PRENOM",
  "DATE INTERVENTION",
  "HEURE INTERVENTION"
);

$smarty = new CSmartyDP();

$smarty->assign("fields_email", $fields_email);
$smarty->assign("hours", range(0, 23));

$smarty->display("configure");
