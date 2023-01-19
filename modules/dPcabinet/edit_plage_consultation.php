<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\Tel3333\C3333TelTools;

CCanDo::checkRead();

$_firstconsult_time = null;
$_lastconsult_time  = null;
$today              = CMbDT::date();
$modal              = CValue::get("modal", 0);

// L'utilisateur est-il praticien ?
$chir     = null;
$mediuser = CMediusers::get();
if ($mediuser->isPraticien()) {
    $chir = $mediuser->createUser();
}

// Praticien selectionné
$chirSel         = CView::getRefCheckRead("chirSel", "ref class|CMediusers default|" . ($chir ? $chir->user_id : ""), true);
$date            = CView::get("debut", "date default|$today", true);
$plageconsult_id = CView::getRefCheckEdit("plageconsult_id", "ref class|CPlageconsult");
CView::checkin();

$debut = CMbDT::date("last sunday", $date);
$fin   = CMbDT::date("next sunday", $debut);
$debut = CMbDT::date("+1 day", $debut);

$is_in_period = ($today >= $debut) && ($today <= $fin);

//permission fonctionnelle
$pref = new CPreferences();

if ($chirSel) {
    $pref = CAppUI::loadPref('tamm_allow_teleconsultation', $chirSel);
}

// Plage de consultation selectionnée
$plageSel = new CPlageconsult();
if ($plageSel->load($plageconsult_id)) {
    $chirSel = $plageSel->chir_id;
}

$plageSel->loadRefsFwd(1);
$plageSel->loadRefsNotes();
$plageSel->loadRefsBack();

if ($plageSel->_id) {
    $plageSel->countDuplicatedPlages();
}

//check 3333tel
if (CModule::getActive("3333tel")) {
    C3333TelTools::checkPlagesConsult($plageSel, $plageSel->_ref_chir->function_id);
}

$pause = new CConsultation();
//find the unique pause;
if ($plageSel->_id) {
    $list                 = $plageSel->loadRefPauses();
    $plageSel->_pauses    = [];
    $plageSel->_pause_ids = [];
    foreach ($list as $_pause) {
        /** @var CConsultation $_pause */
        $pause                  = reset($list);
        $plageSel->_pauses[]    = [
            'hour'     => $_pause->heure,
            'duration' => $_pause->duree,
            'motif'    => $_pause->motif,
            'pause_id' => $_pause->_id,
        ];
        $plageSel->_pause_ids[] = $_pause->_id;
    }
}

if ($plageSel->_affected) {
    $firstconsult       = reset($plageSel->_ref_consultations);
    $_firstconsult_time = substr($firstconsult->heure, 0, 5);
    $lastconsult        = end($plageSel->_ref_consultations);
    $_lastconsult_time  = substr($lastconsult->heure, 0, 5);
}

// Détails sur les consultation affichées
foreach ($plageSel->_ref_consultations as $keyConsult => &$consultation) {
    // Cache les payées
    if ($consultation->loadRefFacture()->patient_date_reglement) {
        unset($plageSel->_ref_consultations[$keyConsult]);
        continue;
    }
    // Cache les annulées
    if ($consultation->annule) {
        unset($plageSel->_ref_consultations[$keyConsult]);
        continue;
    }
    $consultation->loadRefSejour(1);
    $consultation->loadRefPatient(1);
    $consultation->loadRefCategorie(1);
    $consultation->countDocItems();
}

if ($chirSel && $plageSel->chir_id != $chirSel) {
    $plageconsult_id = null;
    $plageSel        = new CPlageconsult();
}

CValue::setSession("plageconsult_id", $plageconsult_id);

// Liste des chirurgiens
$mediusers = new CMediusers();
$listChirs = $mediusers->loadProfessionnelDeSanteByPref(PERM_EDIT);

$listDaysSelect = [];
for ($i = 0; $i < 7; $i++) {
    $dateArr                  = CMbDT::date("+$i day", $debut);
    $listDaysSelect[$dateArr] = $dateArr;
}

$holidays = array_merge(CMbDT::getHolidays(), CMbDT::getHolidays(CMbDT::date("+1 YEAR")));

// Variable permettant de compter les jours pour la suppression du samedi et du dimanche
$i = 0;

// Détermination des bornes du semainier
$min = CPlageconsult::$hours_start . ":" . reset(CPlageconsult::$minutes) . ":00";
$max = CPlageconsult::$hours_stop . ":" . end(CPlageconsult::$minutes) . ":00";

// Extension du semainier s'il y a des plages qui dépassent des bornes
// de configuration hours_start et hours_stop
$hours = CPlageconsult::$hours;

$min_hour = sprintf("%01d", CMbDT::transform($min, null, "%H"));
$max_hour = sprintf("%01d", CMbDT::transform($max, null, "%H"));

if (!isset($hours[$min_hour])) {
    for ($i = $min_hour; $i < CPlageconsult::$hours_start; $i++) {
        $hours[$i] = sprintf("%02d", $i);
    }
}

if (!isset($hours[$max_hour])) {
    for ($i = CPlageconsult::$hours_stop + 1; $i < ($max_hour + 1); $i++) {
        $hours[$i] = sprintf("%02d", $i);
    }
}

// Vérifier le droit d'écriture sur la plage sélectionnée
$plageSel->canDo();
$plageSel->checkLimitHours();

ksort($hours);

//load pref
$allow_plage_holiday = CAppUI::loadPref('allow_plage_holiday', $chirSel);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_firstconsult_time", $_firstconsult_time);
$smarty->assign("_lastconsult_time", $_lastconsult_time);
$smarty->assign("plageconsult_id", $plageconsult_id);
$smarty->assign("user", CMediusers::get());
$smarty->assign("chirSel", $chirSel);
$smarty->assign("plageSel", $plageSel);
$smarty->assign("listChirs", $listChirs);
$smarty->assign("pause", $pause);
$smarty->assign("debut", $date);
$smarty->assign("listDaysSelect", $listDaysSelect);
$smarty->assign("holidays", $holidays);
$smarty->assign("listHours", $hours);
$smarty->assign("listMins", CPlageconsult::$minutes);
$smarty->assign("nb_intervals_hour", intval(60 / CPlageconsult::$minutes_interval));
$smarty->assign("modal", $modal);
$smarty->assign("selected_freq", $plageSel->_freq ?? CAppUI::gconf("dPcabinet CPlageconsult minutes_interval"));
$smarty->assign("allow_consultation", $pref);
$smarty->assign("allow_plage_holiday", $allow_plage_holiday);

$smarty->display("edit_plage_consultation");
