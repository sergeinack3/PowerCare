<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\CompteRendu\CCompteRendu;

/**
 * Onglet de configuration
 */
CCanDo::checkAdmin();

$horodatage = array(
  "day"            => "dd",
  "month"          => "MM",
  "yearlong"       => "y",
  "yearshort"      => "yy",
  "hourlong"       => "HH",
  "hourshort"      => "hh",
  "minute"         => "mm",
  "second"         => "ss",
  "meridian"       => "a",
  "name_firstname" => "%p",
  "name_lastname"  => "%n",
  "name_initials"  => "%i"
);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("horodatage", $horodatage);
$smarty->assign("object_classes", CCompteRendu::getTemplatedClasses());

$smarty->display("configure");
