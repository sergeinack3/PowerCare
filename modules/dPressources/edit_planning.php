<?php
/**
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Ressources\CPlageressource;

CCanDo::checkEdit();

// Liste des prats
$listPrat = new CMediusers;
$listPrat = $listPrat->loadPraticiens(PERM_EDIT);

// Période
$today = CMbDT::date();
$debut = CValue::getOrSession("debut", $today);
$debut = CMbDT::date("last sunday", $debut);
$fin   = CMbDT::date("next sunday", $debut);
$debut = CMbDT::date("+1 day", $debut);

$prec = CMbDT::date("-1 week", $debut);
$suiv = CMbDT::date("+1 week", $debut);

// Plage selectionnée
$plage_id    = CValue::getOrSession("plage_id", null);
$plage       = new CPlageressource;
$plage->date = $debut;
$plage->load($plage_id);
$plage->loadRefsNotes();

// Sélection des plages
$plages = array();
for ($i = 0; $i < 7; $i++) {
  $date          = CMbDT::date("+$i day", $debut);
  $where["date"] = "= '$date'";
  $plagesPerDay  = $plage->loadList($where);
  foreach ($plagesPerDay as $_plage) {
    $_plage->loadRefs();
  }
  $plages[$date] = $plagesPerDay;
}

// Liste des heures
for ($i = 8; $i <= 20; $i++) {
  $listHours[$i] = $i;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("debut", $debut);
$smarty->assign("prec", $prec);
$smarty->assign("suiv", $suiv);
$smarty->assign("plage", $plage);
$smarty->assign("plages", $plages);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("listHours", $listHours);

$smarty->display("edit_planning.tpl");