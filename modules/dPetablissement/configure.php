<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$departements = range(1, 95);
foreach ($departements as &$_departement) {
  $_departement = sprintf("%02d", $_departement);
}

$departements = array_merge($departements, array("2A", "2B", "9A", "9B", "9C", "9D", "9E", "9F"));

// Création du template
$smarty = new CSmartyDP();

$smarty->display("configure.tpl");
