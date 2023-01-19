<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CGrossesse;

CCanDo::checkRead();
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse", true);
CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);

//Modifier cet array pour changer les semaines de rendez-vous
$semaines_rdv = array(
  "mois_3" => 10,
  "mois_4" => 14,
  "mois_5" => 18,
  "mois_6" => 22,
  "mois_7" => 26,
  "mois_8" => 30,
  "mois_9" => 34
);

//Modifier cette variable pour changer l'écart de la période conseillée
$ecart_semaines = 2;

$debut_grossesse = $grossesse->_date_debut_grossesse;
foreach ($semaines_rdv as $key => $value) {
  $list_rdv[$key]["dates"] = array(CMbDT::date("+$value weeks", $debut_grossesse),
    CMbDT::date("+" . ($value + $ecart_semaines) . " weeks", $debut_grossesse));
}

$list_rdv["echographie_1"]["dates"] = array(CMbDT::date("+11 weeks", $grossesse->_date_fecondation),
  CMbDT::date("+13 weeks", $grossesse->_date_fecondation));
$list_rdv["echographie_2"]["dates"] = array(CMbDT::date("+22 weeks", $grossesse->_date_fecondation),
  CMbDT::date("+24 weeks", $grossesse->_date_fecondation));
$list_rdv["echographie_3"]["dates"] = array(CMbDT::date("+31 weeks", $grossesse->_date_fecondation),
  CMbDT::date("+33 weeks", $grossesse->_date_fecondation));

// Tri par date : 0 correspond au premier element
CMbArray::pluckSort($list_rdv, SORT_ASC, "dates", 0);

$list_consultations = $grossesse->loadRefsConsultations();

foreach ($list_rdv as $type_rdv => $_rdv) {
  $list_rdv[$type_rdv]["consultations"] = array();
  foreach ($list_consultations as $_consult) {
    if ($_rdv["dates"][0] <= $_consult->_date && $_rdv["dates"][1] >= $_consult->_date) {
      $list_rdv[$type_rdv]["consultations"][$_consult->_id] = $_consult;
      unset($list_consultations[$_consult->_id]);
    }
  }
}

$grossesse->loadRefParturiente();

$smarty = new CSmartyDP();

$smarty->assign("list_rdv", $list_rdv);
$smarty->assign("grossesse", $grossesse);
$smarty->assign("consultations_restantes", $list_consultations);
$smarty->display("vw_calculatrice_obstetricale");