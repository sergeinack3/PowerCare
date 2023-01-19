<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$classes = array(
  "CCorrespondantPatient",
  "CMedecin",
  "CPatient",
  // Cas particulier de l'assuré qui est stocké dans la même table que le patient...
  "CPatient"
);

$smarty = new CSmartyDP();

$smarty->assign("classes", $classes);

$smarty->display("vw_guess_sexe.tpl");