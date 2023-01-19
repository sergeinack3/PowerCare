<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

global $m;

// On sauvegarde le module pour que les mises en session des paramètes se fassent
// dans le module depuis lequel on accède à la ressource
$save_m = $m;

$ds = CSQLDataSource::get("std");

// Initialisation de variables
$current_m = CView::get("current_m", "str");

$m = $current_m;

$date = CView::get("date", "date default|now", true);

$month_min = CMbDT::date("first day of +0 month", $date);
$lastmonth = CMbDT::date("last day of -1 month", $date);
$nextmonth = CMbDT::date("first day of +1 month", $date);

$recuse     = CView::get("recuse", "str default|-1", true);
$envoi_mail = CView::get("envoi_mail", "bool default|0", true);
$service_id = CView::get("service_id", "ref class|CService", true);
$prat_id    = CView::get("prat_id", "ref class|CMediusers", true);

CView::checkin();

$bank_holidays = CMbDT::getHolidays($date);

$hier   = CMbDT::date("- 1 day", $date);
$demain = CMbDT::date("+ 1 day", $date);

// Initialisation du tableau de jours
$days = [];
for ($day = $month_min; $day <= $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
    $days[$day] = [
        "num1" => "0",
        "num2" => "0",
        "num3" => "0",
        // Réservation
        "num4" => "0",
        // Séjours facturés
        "num5" => "0",
    ];
}

// filtre sur les types d'admission
$filterType = "";
if ($current_m == "ssr" || $current_m == "psy") {
    $filterType = "AND (`sejour`.`type` = '$current_m')";
}

// filtre sur les services
if ($service_id) {
    $leftjoinService = "LEFT JOIN affectation
                        ON affectation.sejour_id = sejour.sejour_id AND affectation.sortie = sejour.sortie_prevue
                      LEFT JOIN lit
                        ON affectation.lit_id = lit.lit_id
                      LEFT JOIN chambre
                        ON lit.chambre_id = chambre.chambre_id
                      LEFT JOIN service
                        ON chambre.service_id = service.service_id";
    $filterService   = "AND service.service_id = '$service_id'";
} else {
    $leftjoinService = $filterService = "";
}

$leftJoinPrat = "";
// filtre sur le praticien
if ($prat_id) {
    $user = CMediusers::get($prat_id);

    if ($user->isAnesth()) {
        $leftJoinPrat = "LEFT JOIN operations ON sejour.sejour_id = operations.sejour_id
                     LEFT JOIN plagesop ON plagesop.plageop_id = operations.plageop_id";
        $filterPrat   = "AND (operations.anesth_id = '$prat_id' OR plagesop.anesth_id = '$prat_id' OR sejour.praticien_id = '$prat_id')";
    } else {
        $filterPrat = "AND sejour.praticien_id = '$prat_id'";
    }
} else {
    $filterPrat = "";
}

$group = CGroups::loadCurrent();

// Liste des séjours en attente par jour
$query = "SELECT DATE_FORMAT(`sejour`.`entree`, '%Y-%m-%d') AS `date`, COUNT(`sejour`.`sejour_id`) AS `num`
  FROM `sejour`
  $leftjoinService
  $leftJoinPrat
  WHERE `sejour`.`entree` BETWEEN '$month_min' AND '$nextmonth'
    AND `sejour`.`group_id` = '$group->_id'
    AND `sejour`.`recuse` = '-1'
    AND `sejour`.`annule` = '0'
    $filterType
    $filterService
    $filterPrat
  GROUP BY `date`
  ORDER BY `date`";
foreach ($ds->loadHashList($query) as $day => $num1) {
    if (isset($days[$day])) {
        $days[$day]["num1"] = $num1;
    }
}

// Liste des séjours validés par jour
$query = "SELECT DATE_FORMAT(`sejour`.`entree`, '%Y-%m-%d') AS `date`, COUNT(`sejour`.`sejour_id`) AS `num`
  FROM `sejour`
  $leftjoinService
  $leftJoinPrat
  WHERE `sejour`.`entree_prevue` BETWEEN '$month_min' AND '$nextmonth'
    AND `sejour`.`group_id` = '$group->_id'
    AND `sejour`.`recuse` = '0'
    AND `sejour`.`annule` = '0'
    $filterType
    $filterService
    $filterPrat
  GROUP BY `date`
  ORDER BY `date`";
foreach ($ds->loadHashList($query) as $day => $num2) {
    if (isset($days[$day])) {
        $days[$day]["num2"] = $num2;
    }
}

// Liste des séjours récusés par jour
$query = "SELECT DATE_FORMAT(`sejour`.`entree`, '%Y-%m-%d') AS `date`, COUNT(`sejour`.`sejour_id`) AS `num`
    FROM `sejour`
  $leftjoinService
  $leftJoinPrat
  WHERE `sejour`.`entree` BETWEEN '$month_min' AND '$nextmonth'
    AND `sejour`.`group_id` = '$group->_id'
    AND `sejour`.`recuse` = '1'
    $filterType
    $filterService
    $filterPrat
  GROUP BY `date`
  ORDER BY `date`";
foreach ($ds->loadHashList($query) as $day => $num3) {
    if (isset($days[$day])) {
        $days[$day]["num3"] = $num3;
    }
}


if ($current_m == "reservation") {
    // Une nouvelle colonne de la forme : Mails répondus / Mails envoyés

    // Mails envoyés
    $query = "SELECT DATE_FORMAT(`sejour`.`entree`, '%Y-%m-%d') AS `date`, COUNT(`sejour`.`sejour_id`) AS `num`
    FROM `sejour`
    $leftjoinService
    $leftJoinPrat
    LEFT JOIN `operations` ON `operations`.`sejour_id` = `sejour`.`sejour_id`
    WHERE `sejour`.`entree` BETWEEN '$month_min' AND '$nextmonth'
      AND `operations`.`operation_id` IS NOT NULL
      AND `operations`.`envoi_mail` IS NOT NULL
      AND `sejour`.`group_id` = '$group->_id'
      AND `sejour`.`recuse` != '1'
      $filterType
      $filterService
      $filterPrat
    GROUP BY `date`
    ORDER BY `date`";
    foreach ($ds->loadHashList($query) as $day => $num4) {
        if (isset($days[$day])) {
            $days[$day]["num4"] = "0/$num4";
        }
    }

    // Mails répondus
    // Sur la base d'un user log du praticien (qui a donc modifié la DHE)
    // On ajout DISTINCT sur le sejour_id, car il peut y avoir plusieurs entrées dans la table
    // user_log qui correspondent
    $query = "SELECT DATE_FORMAT(`sejour`.`entree`, '%Y-%m-%d') AS `date`, COUNT(DISTINCT `sejour`.`sejour_id`) AS `num`
    FROM `sejour`
    $leftjoinService
    $leftJoinPrat
    LEFT JOIN `operations` ON `operations`.`sejour_id` = `sejour`.`sejour_id`
    LEFT JOIN `user_log` ON `user_log`.`user_id` = `operations`.`chir_id`
      AND `user_log`.`object_class` = 'COperation' AND `user_log`.`object_id` = `operations`.`operation_id`
    WHERE `sejour`.`entree` BETWEEN '$month_min' AND '$nextmonth'
      AND `operations`.`operation_id` IS NOT NULL
      AND `operations`.`envoi_mail` IS NOT NULL
      AND `user_log`.`user_log_id` IS NOT NULL
      AND `sejour`.`group_id` = '$group->_id'
      AND `sejour`.`recuse` != '1'
      $filterType
      $filterService
      $filterPrat
    GROUP BY `date`
    ORDER BY `date`";

    foreach ($ds->loadHashList($query) as $day => $num4) {
        if (isset($days[$day])) {
            $days[$day]["num4"] = preg_replace("/0\//", "$num4/", $days[$day]["num4"]);
        }
    }
}

$m = $save_m;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("current_m", $current_m);
$smarty->assign("hier", $hier);
$smarty->assign("demain", $demain);

$smarty->assign("recuse", $recuse);
$smarty->assign("envoi_mail", $envoi_mail);

$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign('date', $date);
$smarty->assign('lastmonth', $lastmonth);
$smarty->assign('nextmonth', $nextmonth);
$smarty->assign('days', $days);

$smarty->display('inc_vw_all_sejours.tpl');
