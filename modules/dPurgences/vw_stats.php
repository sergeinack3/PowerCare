<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkAdmin();

$axe            = CView::get('axe', 'str', true);
$entree         = CView::get('entree', array('date', 'default' => CMbDT::date('-1 MONTH')), true);
$sortie         = CView::get('sortie', array('date', 'default' => CMbDT::date()), true);
$hide_cancelled = CView::get('hide_cancelled', 'bool default|1', true);
$days           = CView::get('days', array('str', 'default' => array(1, 2, 3, 4, 5, 6, 7)), true);
$holidays       = CView::get('holidays', 'bool default|0', true);
$age_min        = CView::get('age_min', array('str', 'default' => array(0, 15, 75, 85)), true);
$age_max        = CView::get('age_max', array('str', 'default' => array(14, 74, 84)), true);

CView::checkin();

if (!$days) {
  $days = array();
}

if (!$entree) {
  $entree = CMbDT::date('-1 MONTH');
}
if (!$sortie) {
  $sortie = CMbDT::date();
}
$filter         = new CSejour;
$filter->entree = $entree;
$filter->sortie = $sortie;

if (!$axe) {
  $axe = "age";
}

$axes = array(
  "age"                    => "Tranche d'âge",
  "sexe"                   => CAppUI::tr("CPatient-sexe"),
  "ccmu"                   => CAppUI::tr("CRPU-ccmu"),
  "mode_entree"            => CAppUI::tr("CSejour-mode_entree"),
  "mode_sortie"            => CAppUI::tr("CSejour-mode_sortie"),
  "provenance"             => CAppUI::tr("CSejour-provenance"),
  "destination"            => CAppUI::tr("CSejour-destination"),
  "orientation"            => CAppUI::tr("CRPU-orientation"),
  "transport"              => CAppUI::tr("CSejour-transport"),
  "without_rpu"            => CAppUI::tr("CSejour-Sejours without_rpu"),
  "transfers_count"        => CAppUI::tr("CRPU-Count of transferts"),
  "mutations_count"        => CAppUI::tr("CRPU-Count mutations"),
  "accident_travail_count" => CAppUI::tr("CRPU-Count work accidents"),
  'motif_sfmu'             => CAppUI::tr('CRPU-motif_sfmu'),
  'DP'                     => CAppUI::tr('CSejour-DP'),
  "passages_uhcd"          => CAppUI::tr("CRPU-Passages UHCD"),
  "pec_ioa"                => CAppUI::tr("CRPU-stats_pec_ioa")
);

$axes_other = array(
  "radio"          => "Attente radio",
  "bio"            => "Attente biologie",
  "spe"            => "Attente spécialiste",
  "duree_sejour"   => "Durée de séjour",
  "duree_pec"      => "Durée de prise en charge",
  "duree_attente"  => "Durée d'attente",
  "diag_infirmier" => "Diagnostic infirmier",
);

$service           = new CService();
$service->group_id = CGroups::get()->_id;
$service->urgence  = "1";
$services          = $service->loadMatchingListEsc();

if ($age_ranges = CAppUI::pref('stats_urgences_age_ranges')) {
  $age_min = [];
  $age_max = [];

  foreach (explode('|', $age_ranges) as $range) {
    if (strpos($range, '-') !== false) {
      list($age_min[], $age_max[]) = explode('-', $range);
    }
    else {
      $age_min[] = $range;
    }
  }
}

$smarty = new CSmartyDP();

$smarty->assign('filter', $filter);
$smarty->assign('filter_rpu', new CRPU());
$smarty->assign('axe', $axe);
$smarty->assign('axes', $axes);
$smarty->assign('axes_other', $axes_other);
$smarty->assign('hide_cancelled', $hide_cancelled);
$smarty->assign('days', $days);
$smarty->assign('holidays', $holidays);
$smarty->assign('age_min', $age_min);
$smarty->assign('age_max', $age_max);
$smarty->assign('services', $services);

$smarty->display('vw_stats');
