<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CActiviteCsARR;

CCanDo::checkRead();

$needle      = CValue::post("code_activite_csarr", CValue::post("code_csarr", CValue::post("code")));
$type_seance = CValue::post("type_seance");

if (!$needle) {
  $needle = "%";
}

CView::enableSlave();

$activite = new CActiviteCsARR();
$activites = $activite->seek($needle, null, 300);

foreach ($activites as $cle_activite => $_activite) {
  $_activite->loadRefHierarchie();
  $reference = $_activite->loadRefReference();
  if (($type_seance == "dediee" && $reference->dedie != "oui") ||
    ($type_seance == "non_dediee" && $reference->non_dedie != "possible") ||
    ($type_seance == "collective" && $reference->collectif != "oui")) {
    unset($activites[$cle_activite]);
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("activites", $activites);
$smarty->assign("needle", $needle);
$smarty->assign("nodebug", true);

$smarty->display("inc_do_csarr_autocomplete");
