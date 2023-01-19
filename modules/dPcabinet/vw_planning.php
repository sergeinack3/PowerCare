<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CViewHistory;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningRange;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkRead();

if (CAppUI::pref("new_semainier")) {
  CAppUI::redirect("m=cabinet&tab=weeklyPlanning");
}

// L'utilisateur est-il praticien ?
$chir = null;
$mediuser = CMediusers::get();
if ($mediuser->isPraticien()) {
  $chir = $mediuser->createUser();
}

// Type de vue
$show_payees   = CValue::getOrSession("show_payees"  , 1);
$show_annulees = CValue::getOrSession("show_annulees", 0);

// Praticien selectionné
$chirSel = CValue::getOrSession("chirSel", $chir ? $chir->user_id : null);

// Période
$today         = CMbDT::date();
$debut         = CValue::getOrSession("debut", $today);
$debut         = CMbDT::date("last sunday", $debut);
$fin           = CMbDT::date("next sunday", $debut);
$debut         = CMbDT::date("+1 day", $debut);
$bank_holidays = array_merge(CMbDT::getHolidays($debut), CMbDT::getHolidays($fin));

$is_in_period = ($today >= $debut) && ($today <= $fin);

$prec = CMbDT::date("-1 week", $debut);
$suiv = CMbDT::date("+1 week", $debut);

$user = CMediusers::get($chirSel);
$user->loadRefsSecondaryUsers();
$whereChir = $user->getUserSQLClause();
$chirs_id = array($chirSel);
if (count($user->_ref_secondary_users)) {
  $chirs_id = array_merge($chirs_id, CMbArray::pluck($user->_ref_secondary_users, '_id'));
}

// Plage de consultation selectionnée
$plageconsult_id = CValue::getOrSession("plageconsult_id", null);
$plageSel = new CPlageconsult();
$canEditPlage = $plageSel->getPerm(PERM_EDIT);
if (($plageconsult_id === null) && $chirSel && $is_in_period) {
  $nowTime = CMbDT::time();
  $where = array(
    "chir_id" => $whereChir,
    "date"    => "= '$today'",
    "debut"   => "<= '$nowTime'",
    "fin"     => ">= '$nowTime'"
  );
  $plageSel->loadObject($where);
}
if (!$plageSel->plageconsult_id) {
  $plageSel->load($plageconsult_id);
}
else {
  $plageconsult_id = $plageSel->plageconsult_id;
}

if (!in_array($plageSel->chir_id, $chirs_id) && $plageSel->remplacant_id != $chirSel) {
  $plageconsult_id = null;
  $plageSel = new CPlageconsult();
}

CValue::setSession("plageconsult_id", $plageconsult_id);

// Liste des consultations a avancer si desistement
$count_si_desistement = CConsultation::countDesistementsForDay($chirs_id);

$nbjours = 7;

$dateArr = CMbDT::date("+6 day", $debut);

$plage = new CPlageconsult();

//where interv/hp
$wherePlage = array();
$whereOp = array();
$where = array();

$where["date"] = "= '$dateArr'";
$where["chir_id"] = $whereChir;
$wherePlage["chir_id"] = $whereOp["chir_id"] =  " = '$chirSel'";
$wherePlage["date"] = $whereOp["date"] = "= '$dateArr'";



if (!$plage->countList($where)) {
  $nbjours--;
  // Aucune plage le dimanche, on peut donc tester le samedi.
  $dateArr = CMbDT::date("+5 day", $debut);
  $where["date"] = "= '$dateArr'";
  if (!$plage->countList($where)) {
    $nbjours--;
  }
}

$hours = CPlageconsult::$hours;

//Planning au format  CPlanningWeek
$debut = CValue::getOrSession("debut", $today);
$debut = CMbDT::date("-1 week", $debut);
$debut = CMbDT::date("next monday", $debut);

//Instanciation du planning
$user = new CMediusers();
$planning = new CPlanningWeek($debut, $debut, $fin, $nbjours, false, 450, null, true);
if ($user->load($chirSel)) {
  $planning->title = $user->load($chirSel)->_view;
}
else {
  $planning->title = "";
}
$planning->guid = $mediuser->_guid;
$planning->hour_min = "07";
$planning->hour_max = "20";
$planning->pauses = array("07", "12", "19");

// Save history
$params = array(
  "chirSel"         => $chirSel,
  "debut"           => $debut,
  "plageconsult_id" => $plageconsult_id,
  "show_payees"     => $show_payees,
  "show_annulees"   => $show_annulees,
);
CViewHistory::save($user, CViewHistory::TYPE_VIEW, $params);

$showIntervPlanning = CAppUI::pref("showIntervPlanning");

$where = array();
$where[] = "chir_id $whereChir OR remplacant_id = '$chirSel' OR pour_compte_id = '$chirSel'";

for ($i = 0; $i < 7; $i++) {
  $jour = CMbDT::date("+$i day", $debut);

  $is_holiday = array_key_exists($jour, $bank_holidays);

  $where["date"] = "= '$jour'";

  if ($is_holiday && !CAppUI::pref("show_plage_holiday")) {
    continue;
  }

  if ($showIntervPlanning) {
    $wherePlage["date"] = $whereOp["date"] = "= '$jour'";

    // Plages
    /** @var CPlageOp[] $plages */
    $plage_op = new CPlageOp();
    $plages_op = $plage_op->loadList($wherePlage);
    foreach ($plages_op as $_plage_op) {
      $range = new CPlanningRange(
        $_plage_op->_guid,
        $jour . " " . $_plage_op->debut, CMbDT::minutesRelative($_plage_op->debut, $_plage_op->fin),
        "",
        "bbccee",
        "plageop"
      );
      $planning->addRange($range);
    }

    // Interventions
    $op = new COperation();
    /** @var COperation[] $ops */
    $ops = $op->loadList($whereOp);
    foreach ($ops as $_op) {
      $lenght = (CMbDT::minutesRelative("00:00:00", $_op->temp_operation));
      $op = new CPlanningRange(
        $_op->_guid,
        $jour . " " . $_op->time_operation,
        $lenght,
        "",
        "3c75ea",
        "op"
      );
      $planning->addRange($op);
    }
  }

  /** @var CPlageconsult[] $listPlages */
  $listPlages = $plage->loadList($where, "date, debut");
  foreach ($listPlages as $_plage) {
    $_plage->loadRefsBack();
    $_plage->countPatients();
    $_plage->loadDisponibilities();
    $_plage->loadRefAgendaPraticien();

    $debute  = "$jour $_plage->debut";
    $libelle = "";
    if (CMbDT::minutesRelative($_plage->debut, $_plage->fin) >= 30 ) {
      $libelle = $_plage->libelle;
    }

    $color = CAppui::isMediboardExtDark() ? "#49484a"  : "#DDD";
    if ($_plage->desistee) {
      if (!$_plage->remplacant_id) {
        $color = CAppUI::isMediboardExtDark() ? "#59575a" : "#CCC";
      }
      elseif ($_plage->remplacant_id && !in_array($_plage->remplacant_id, $chirs_id)) {
        $color = CAppUI::isMediboardExtDark() ? "#2d83d5" : "#3E9DF4";
      }
      elseif ($_plage->remplacant_id && !$_plage->remplacant_ok) {
        $color = CAppUI::isMediboardExtDark() ? "#e49b6d" : "#FDA";
      }
      elseif ($_plage->remplacant_id && $_plage->remplacant_ok) {
        $color = CAppUI::isMediboardExtDark() ? "#61a53c" : "#BFB";
      }
    }
    elseif ($_plage->pour_compte_id) {
      $color = CAppUI::isMediboardExtDark() ? "#d0a675" : "#EDC";
    }

    $class = null;
    if ($_plage->pour_tiers) {
      $class = "pour_tiers";
    }

    $event = new CPlanningEvent(
      $_plage->_guid,
      $debute,
      CMbDT::minutesRelative($_plage->debut, $_plage->fin),
      $libelle,
      $color,
      true,
      $class,
      null
    );
    $event->useHeight = true;

    if ($_plage->_ref_agenda_praticien->sync) {
      $event->icon      = "fas fa-sync-alt";
      $event->icon_desc = CAppUI::tr("CAgendaPraticien-sync-desc");
    }

    //Menu des évènements
    $event->addMenuItem("list", "Voir le contenu de la plage");
    $nonRemplace = !$_plage->remplacant_id ||
      !in_array($_plage->remplacant_id, $chirs_id) ||
      (in_array($_plage->remplacant_id, $chirs_id) && in_array($_plage->chir_id, $chirs_id));
    $nonDelegue = !$_plage->pour_compte_id ||
      !in_array($_plage->pour_compte_id, $chirs_id) ||
      (in_array($_plage->pour_compte_id, $chirs_id) && in_array($_plage->chir_id, $chirs_id));
    if ($nonRemplace && $nonDelegue && $_plage->getPerm(PERM_EDIT)) {
      $event->addMenuItem("edit", "Modifier cette plage");
    }
    $event->addMenuItem("clock", "Planifier une consultation dans cette plage");

    //Paramètres de la plage de consultation
    $event->type = "consultation";
    $event->plage["id"] = $_plage->plageconsult_id;

    $pct = $_plage->_fill_rate;
    if ($pct > "100") {
      $pct = "100";
    }
    if ($pct == "") {
      $pct = 0;
    }

    $event->plage["pct"]          = $pct;
    $event->plage["locked"]       = $_plage->locked;
    $event->plage["_affected"]    = $_plage->_affected;
    $event->plage["_nb_patients"] = $_plage->_nb_patients;
    $event->plage["_total"]       = $_plage->_total;
    $event->plage["color"]        = $_plage->color;
    $event->plage["list_class"]   = "list";
    $event->plage["add_class"]    = "clock";
    $event->plage["list_title"]   = "Voir le contenu de la plage";
    $event->plage["add_title"]    = "Planifier une consultation dans cette plage";
    $event->_disponibilities    = $_plage->_disponibilities;

    //Ajout de l'évènement au planning
    $planning->addEvent($event);
  }
}

$planning->allow_superposition = false;
$planning->rearrange();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("planning"            , $planning);
$smarty->assign("show_payees"         , $show_payees);
$smarty->assign("show_annulees"       , $show_annulees);
$smarty->assign("chirSel"             , $chirSel);
$smarty->assign("canEditPlage"        , $canEditPlage);
$smarty->assign("plageSel"            , $plageSel);
$smarty->assign("today"               , $today);
$smarty->assign("debut"               , $debut);
$smarty->assign("fin"                 , $fin);
$smarty->assign("prec"                , $prec);
$smarty->assign("suiv"                , $suiv);
$smarty->assign("plageconsult_id"     , $plageconsult_id);
$smarty->assign("count_si_desistement", $count_si_desistement);
$smarty->assign("bank_holidays"       , $bank_holidays);
$smarty->assign("mediuser"            , $mediuser);

$smarty->display("vw_planning.tpl");
