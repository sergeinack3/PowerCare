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
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningMonth;

CCanDo::checkRead();
$date        = CView::get("date", "date default|now", true);
$prat_id     = CView::get("praticien_id", "ref class|CMediusers", true);
$function_id = CView::get("function_id", "ref class|CFunctions");
CView::checkin();

$group_id = CGroups::loadCurrent()->_id;
$user     = CMediusers::get($prat_id);

$where = array();
if ($function_id) {
  $where["function_id"] = " = '$function_id'";
}
else {
  $where["user_id"] = $user->getUserSQLClause();
}
$prats = $user->loadList($where);

$function = new CFunctions();
$function->load($function_id);

// Nombre de visites à domicile
$nb_visite_domicile = 0;

$ds = $function->getDS();

$calendar        = new CPlanningMonth($date);
$calendar->title = htmlentities(CMbDT::format($date, "%B %Y"), ENT_COMPAT);

$libelles_plages = CPlageconsult::getLibellesPref();
foreach ($prats as $_prat) {
  // plages de congés (si mode prat)
  if (!$function_id && CModule::getActive("dPpersonnel")) {
    $plage_cong  = new CPlageConge();
    $plages_cong = $plage_cong->loadListForRange($_prat->_id, $calendar->date_min, $calendar->date_max);
    foreach ($plages_cong as $_conge) {
      $first_day   = CMbDT::date($_conge->date_debut);
      $last_day    = CMbDT::date($_conge->date_fin);
      $replaced_by = new CMediusers();
      $replaced_by->load($_conge->replacer_id);
      while ($last_day >= $first_day) {
        $calendar->addClassesForDay("disabled", $first_day);
        if ($replaced_by->_id) {
          $event        = new CPlanningEvent($_conge->_guid, $first_day);
          $event->title = "<strong>" . ($_conge->_view ? $_conge->_view : CAppUI::tr("Holidays"));
          if ($function_id) {
            $event->title .= " (" . $_prat->_shortview . ")";
          }
          $event->title                               .= $replaced_by ? " " . CAppUI::tr("CConsultation.replaced_by") . " " . $replaced_by->_view . "</strong> " : null;
          $calendar->days[$first_day][$_conge->_guid] = $event;
        }
        $first_day = CMbDT::date("+1 DAY", $first_day);
      }
    }
  }

  //Filtre sur le nom des plages de consultations
  $where_plage_consult = array();
  if (count($libelles_plages)) {
    $where_plage_consult["libelle"] = CSQLDataSource::prepareIn($libelles_plages);
  }
  // plages consult
  $plage    = new CPlageconsult();
  $plages_c = $plage->loadForDays($_prat->_id, $calendar->date_min, $calendar->date_max, $where_plage_consult);
  foreach ($plages_c as $_plage) {
    $_plage->loadRefsConsultations(false);
    $count = count($_plage->_ref_consultations);
    $_plage->loadFillRate();
    $event        = new CPlanningEvent($_plage->_guid, $_plage->date . " $_plage->debut", CMbDT::minutesRelative($_plage->date . " " . $_plage->debut, $_plage->date . " " . $_plage->fin), null, "#" . $_plage->color);
    $title        = $_plage->libelle ? $_plage->libelle : CAppUI::tr($_plage->_class);
    $event->title = $_plage->locked ? "<img src=\"images/icons/lock.png\" style='max-height: 1em;' alt=\"\" />" : null;
    $event->title .= "<strong>" . CMbDT::format($_plage->debut, "%H:%M") . " - " . CMbDT::format($_plage->fin, "%H:%M") . "</strong> $count " . CAppUI::tr("CConsultation");
    $event->title .= $count > 1 ? "s" : null;
    $event->title .= "<small>";
    $event->title .= "<br/>$title";
    if ($function_id) {
      $event->title .= " - " . $_prat->_shortview . "";
    }
    $event->title     .= "<br/> " . CAppUI::tr("CPlageconsult.duree_cumule") . " : ";
    $event->title     .= $_plage->_cumulative_minutes ? CMbDT::time("+ $_plage->_cumulative_minutes MINUTES", "00:00:00") : "&mdash;";
    $event->title     .= "</small>";
    $event->type      = $_plage->_class;
    $event->datas     = array("id" => $_plage->_id);
    $event->css_class = $_plage->_class;
    $event->setObject($_plage);
    $calendar->days[$_plage->date][$_plage->_guid] = $event;
    $_plage->loadRefsConsultations(false);
    foreach ($_plage->_ref_consultations as $_consult) {
      /* @var CConsultation $_consult */
      if ($_consult->patient_id) {
        $_consult->loadRefPatient();
        if ($_consult->visite_domicile) {
          $nb_visite_domicile++;
        }
      }
    }
  }

  // plages op
  if (CModule::getInstalled('dPbloc')) {
    $plage     = new CPlageOp();
    $plages_op = $plage->loadForDays($_prat->_id, $calendar->date_min, $calendar->date_max);
    /** @var CPlageOp[] $plages_op */
    foreach ($plages_op as $_plage) {
      $_plage->loadRefsOperations(false);
      $_plage->loadRefSalle();
      $event = new CPlanningEvent($_plage->_guid, $_plage->date);
      $title = CAppUI::tr($_plage->_class);
      if ($_plage->spec_id) {
        $event->title .= "<img src=\"images/icons/user-function.png\" style=\" float:right;\" alt=\"\"/>";
      }
      $event->title .= "
    <strong>" . CMbDT::format($_plage->debut, "%H:%M") . " - " . CMbDT::format($_plage->fin, "%H:%M") . "</strong>
     " . count($_plage->_ref_operations) . " " . CAppUI::tr('COperation');
      if (count($_plage->_ref_operations) > 1) {
        $event->title .= "s";
      }
      $event->title .= "<small>";
      $event->title .= "<br/>$_plage->_ref_salle";
      if ($function_id && !$_plage->spec_id) {
        $event->title .= " - " . $_prat->_shortview . "";
      }
      $event->title     .= "<br/>" . CAppUI::tr("CPlageconsult.duree_cumule") . " : ";
      $event->title     .= $_plage->_cumulative_minutes ? CMbDT::transform("+ $_plage->_cumulative_minutes MINUTES", "00:00:00", "%Hh%M") : " &mdash;";
      $event->title     .= "</small>";
      $event->type      = $_plage->_class;
      $event->datas     = array("id" => $_plage->_id);
      $event->css_class = $_plage->_class;
      $event->setObject($_plage);
      $calendar->days[$_plage->date][$_plage->_guid] = $event;
    }

    // hors plage
    $sql = "
    SELECT plageop_id, date, chir_id,
      SEC_TO_TIME(SUM(TIME_TO_SEC(temp_operation))) as accumulated_time,
      MIN(time_operation) AS first_time,
      MAX(time_operation) AS last_time,
      COUNT(*) AS nb_op
    FROM operations, sejour
    WHERE (date BETWEEN  '$calendar->date_min' AND  '$calendar->date_max')
    AND plageop_id IS NULL
    AND sejour.sejour_id = operations.sejour_id
    AND sejour.group_id = '$group_id'
    AND (chir_id = '$_prat->_id' OR anesth_id = '$_prat->_id')
    AND operations.annulee = '0'
    AND sejour.annule = '0'
    GROUP BY date, plageop_id";
    $hps = $ds->loadList($sql);

    foreach ($hps as $_hp) {
      $guid         = "hps_" . $_hp["date"] . $_prat->_id;
      $event        = new CPlanningEvent($guid, $_hp["date"] . " " . $_hp["first_time"], CMbDT::minutesRelative($_hp["date"] . " 00:00:00", $_hp["date"] . " " . $_hp["accumulated_time"]));
      $event->title = "<strong>" . CMbDT::format($_hp["first_time"], '%H:%M') . " - " . CMbDT::format($_hp["last_time"], "%H:%M") . "</strong> " . $_hp["nb_op"] . " " . CAppUI::tr("CIntervHorsPlage") . "<small>";
      if ($function_id) {
        $event->title .= " - " . $_prat->_shortview;
      }
      $event->title .= "<br/>" . CAppUI::tr("CPlageconsult.duree_cumule") . " : " . CMbDT::format($_hp["accumulated_time"], '%Hh%M');
      $event->title .= "</small>";

      $event->datas = array("date" => $_hp['date'], "chir_id" => $_hp["chir_id"]);

      $event->css_class                    = $event->type = "CIntervHorsPlage";
      $event->css_class                    .= " date_" . $_hp["date"];
      $calendar->days[$_hp["date"]][$guid] = $event;
    }
  }
  else {
    $plages_op = array();
    $hps       = array();
  }
}

// Calcul des dates de début et fin de mois
$debut_mois = CMbDT::date("first day of this month", $date);
$fin_mois   = CMbDT::date("last day of this month", $date);

$smarty = new CSmartyDP();
$smarty->assign("calendar", $calendar);
$smarty->assign("plages_op", $plages_op);
$smarty->assign("plages_consult", $plages_c);
$smarty->assign("hors_plage", $hps);
$smarty->assign("nb_visite_dom", $nb_visite_domicile);
$smarty->assign("debut_mois", $debut_mois);
$smarty->assign("fin_mois", $fin_mois);
$smarty->display("inc_vw_month");
