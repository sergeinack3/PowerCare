<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CReglement;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
// Gestion des bouton radio des dates
$now             = CMbDT::date();
$yesterday       = CMbDT::date("-1 DAY", $now);
$week_deb        = CMbDT::date("last sunday", $now);
$week_fin        = CMbDT::date("next sunday", $week_deb);
$week_deb        = CMbDT::date("+1 day", $week_deb);
$rectif          = CMbDT::transform("+0 DAY", $now, "%d") - 1;
$month_deb       = CMbDT::date("-$rectif DAYS", $now);
$month_fin       = CMbDT::date("+1 month", $month_deb);
$three_month_deb = CMbDT::date("-3 month", $month_fin);
$month_fin       = CMbDT::date("-1 day", $month_fin);

$filter                  = new CConsultation;
$filter->_date_min       = CMbDT::date();
$filter->_date_max       = CMbDT::date("+ 0 day");
$filter->_etat_paiement  = CView::get("_etat_paiement", "str default|0", true);
$filter->_type_affichage = CView::get("_type_affichage", "str default|0", true);

$filter_reglement       = new CReglement();
$filter_reglement->mode = CView::get("mode", "str default|0", true);
$prat_id                = CView::get("prat_id", "ref class|CMediusers", true);
CView::checkin();

// L'utilisateur est-il praticien ?
$mediuser = CMediusers::get();
$mediuser->loadRefFunction();
$listPrat = CConsultation::loadPraticiensCompta();

$bloc  = new CBlocOperatoire();
$blocs = $bloc->loadGroupList();

// Tableaux pour les autres exports
$static_common_others_exports = [
    "paid",
    "unpaid",
];
$static_common_others_exports = array_merge(
    [],
    $static_common_others_exports
);
$static_others_exports        = [
    "CFactureCabinet"       => $static_common_others_exports,
    "CFactureEtablissement" => $static_common_others_exports,
];

$user = CMediusers::get();
$prat = new CMediusers();

if ($prat_id) {
    $prat->load($prat_id);
} elseif ($user->isPraticien()) {
    $prat = $user;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("filter_reglement", $filter_reglement);
$smarty->assign("mediuser", $mediuser);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("now", $now);
$smarty->assign("yesterday", $yesterday);
$smarty->assign("week_deb", $week_deb);
$smarty->assign("week_fin", $week_fin);
$smarty->assign("month_deb", $month_deb);
$smarty->assign("three_month_deb", $three_month_deb);
$smarty->assign("month_fin", $month_fin);
$smarty->assign("blocs", $blocs);
$smarty->assign("others_exports", $static_others_exports);
$smarty->assign("prat", $prat);
$smarty->display('../../dPfacturation/templates/vw_compta.tpl');
