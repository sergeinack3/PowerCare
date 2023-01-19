<?php
/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ccam\CFilterCotation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();
$chir     = new CMediusers();
$mediuser = CMediusers::get();

if ($mediuser->isProfessionnelDeSante()) {
    $chir = $mediuser;
}

$chirSel                  = CView::get(
    "praticien_id",
    'ref class|CMediusers' . $chir->_id ? " default|$chir->_id" : '',
    true
);
$function_id              = CView::get('function_id', 'ref class|CFunctions', true);
$all_prats                = CView::get("all_prats", 'bool', false);
$end_date                 = CView::get("end_date", 'date default|' . CMbDT::date());
$begin_date               = CView::get("begin_date", 'date default|' . CMbDT::date("-1 week", $end_date));
$objects_whithout_codes   = CView::get('objects_whithout_codes', 'bool default|1');
$show_unexported_acts     = CView::get('show_unexported_acts', 'bool default|0');
$display_operations       = CView::get('display_operations', 'bool default|1');
$display_consultations    = CView::get('display_consultations', 'bool default|0');
$display_sejours          = CView::get('display_sejours', 'bool default|0');
$display_seances          = CView::get('display_seances', 'bool default|0');
$libelle                  = CView::get('libelle', 'str');
$protocole_id             = CView::get('protocole_id', 'ref class|CProtocole');
$ccam_codes               = CView::get('ccam_codes', 'str');
$nda                      = CView::get('nda', 'str');
$patient_id               = CView::get('patient_id', 'bool default|0');
$codage_lock_status       = CView::get('codage_lock_status', 'enum list|unlocked|locked_by_chir|locked');
$excess_fee_chir_status   = CView::get('excess_fee_chir_status', 'enum list|non_regle|cb|cheque|espece|virement');
$excess_fee_anesth_status = CView::get('excess_fee_anesth_status', 'enum list|non_regle|cb|cheque|espece|virement');

$check_all_interventions = CAppUI::pref("check_all_interventions");
$display_all             = CView::get("display_all", "bool default|$check_all_interventions");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$function = new CFunctions();
if ($chirSel) {
    $chir = CMediusers::get($chirSel);
} elseif ($function_id) {
    $function->load($function_id);
    $chir = new CMediusers();
}

$object_classes = [];
if ($display_consultations) {
    $object_classes[] = 'CConsultation';
}
if ($display_operations) {
    $object_classes[] = 'COperation';
}
if ($display_sejours) {
    $object_classes[] = 'CSejour';
}
if ($display_seances) {
    $object_classes[] = 'CSejour-seances';
}

$date             = CMbDT::date('now');
$curr_week_start  = CMbDT::date('monday this week', $date);
$curr_week_end    = CMbDT::date('sunday this week', $date);
$curr_month_start = CMbDT::date('first day of this month', $date);
$curr_month_end   = CMbDT::date('last day of this month', $date);
$last_month_start = CMbDT::date('first day of previous month', $date);
$last_month_end   = CMbDT::date('last day of previous month', $date);

$list_excess_fee_payment_status = [
    "non_regle",
    "cb",
    "cheque",
    "espece",
    "virement",
];

$smarty = new CSmartyDP("modules/dPboard");

$smarty->assign("chirSel", $chirSel);
$smarty->assign('chir', $chir);
$smarty->assign('function', $function);
$smarty->assign('user', CMediusers::get());
$smarty->assign('perm_fonct', CAppUI::pref("allow_other_users_board"));
$smarty->assign("begin_date", $begin_date);
$smarty->assign("end_date", $end_date);
$smarty->assign("all_prats", $all_prats);
$smarty->assign("today", $date);
$smarty->assign("curr_week_start", $curr_week_start);
$smarty->assign("curr_week_end", $curr_week_end);
$smarty->assign("curr_month_start", $curr_month_start);
$smarty->assign("curr_month_end", $curr_month_end);
$smarty->assign("last_month_start", $last_month_start);
$smarty->assign("last_month_end", $last_month_end);
$smarty->assign('objects_whithout_codes', $objects_whithout_codes);
$smarty->assign('show_unexported_acts', $show_unexported_acts);
$smarty->assign('display_operations', $display_operations);
$smarty->assign('display_consultations', $display_consultations);
$smarty->assign('display_sejours', $display_sejours);
$smarty->assign('display_seances', $display_seances);
$smarty->assign('libelle', $libelle);
$smarty->assign('protocole_id', $protocole_id);
$smarty->assign('ccam_codes', $ccam_codes != '' ? explode('|', $ccam_codes) : []);
$smarty->assign('nda', $nda);
$smarty->assign('patient', $patient);
$smarty->assign('codage_lock_status', $codage_lock_status);
$smarty->assign('excess_fee_chir_status', $excess_fee_chir_status);
$smarty->assign('excess_fee_anesth_status', $excess_fee_anesth_status);
$smarty->assign('display_all', $display_all);
$smarty->assign('list_codage_lock_status', CFilterCotation::$list_codage_lock_status);
$smarty->assign('list_excess_fee_payment_status', $list_excess_fee_payment_status);
$smarty->assign('object_classes', $object_classes);
$smarty->display("inc_vw_interv_non_cotees");
