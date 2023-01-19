<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CMergeLog;

CCanDo::checkAdmin();

$date          = CValue::get("date_min", CMbDT::date());
$see_yesterday = CValue::getOrSession("see_yesterday", "1");

$date_min = $date;
$date_min = $see_yesterday ? CMbDT::date("-1 day", $date) : $date;
$date_max = CMbDT::date("+1 day", $date);

// Chargement des séjours concernés
$sejour = new CSejour();

$where                    = [];
$where["sejour.entree"]   = "BETWEEN '$date_min' AND '$date_max'";
$where["sejour.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";
$order                    = "entree";

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, $order);
$count   = 0;

/** @var CSejour[] _$sejours_merge */

$sejours_merge = [];
foreach ($sejours as $_sejour) {
    $_sejour->loadNDA();

    $sejours_merge[$_sejour->_NDA][] = $_sejour;
}

foreach ($sejours_merge as $NDA => $_sejours_merge) {
    // Regarde que les séjours qui ont exactement le même NDA
    if (count($_sejours_merge) <= 1) {
        unset($sejours_merge[$NDA]);
    }
}

CAppUI::stepAjax(count($sejours_merge) . " séjours à fusionner du $date_min au $date_max");

foreach ($sejours_merge as $NDA => $_sejours_merge) {
    if (count($_sejours_merge) > 2) {
        CAppUI::stepAjax("Il y a plus de deux séjour (" . count($_sejours_merge) . ")", UI_MSG_WARNING);

        continue;
    }

    $first_sejour = $_sejours_merge[0];
    $first_sejour->loadLastLog();
    $first_sejour_last_log = $first_sejour->_ref_last_log;

    $second_sejour = $_sejours_merge[1];
    $second_sejour->loadLastLog();
    $second_sejour_last_log = $second_sejour->_ref_last_log;


    // Si deux séjours de PA
    if ($first_sejour->_etat == "preadmission" && $second_sejour->_etat == "preadmission") {
        CAppUI::stepAjax("Fusion de deux séjours en pré-admissions");
    }

    // Si un séjour en cours / clôturé et un PA
    if (
        $first_sejour->_etat != "preadmission" && $second_sejour->_etat == "preadmission" ||
        $first_sejour->_etat == "preadmission" && $second_sejour->_etat != "preadmission"
    ) {
        if ($first_sejour->_etat == "preadmission") {
            [$second_sejour, $first_sejour] = [$first_sejour, $second_sejour];
        }

        CAppUI::stepAjax("Fusion d'un séjour en cours ou clôturé et un en pré-admission");
    }

    // Si deux séjours en cours
    if ($first_sejour->_etat == "encours" && $second_sejour->_etat == "encours") {
        if ($second_sejour_last_log->date > $first_sejour_last_log->date) {
            [$second_sejour, $first_sejour] = [$first_sejour, $second_sejour];
        }

        CAppUI::stepAjax("Fusion de deux séjours en cours");
    }

    // Si deux séjours clôturés
    if ($first_sejour->_etat == "cloture" && $second_sejour->_etat == "cloture") {
        if ($second_sejour_last_log->date > $first_sejour_last_log->date) {
            [$second_sejour, $first_sejour] = [$first_sejour, $second_sejour];
        }

        CAppUI::stepAjax("Fusion de deux séjours clôturés");
    }

    $first_sejour_id = $first_sejour->_id;

    // Passage en annulation du second pour supprimer les affectations
    $second_sejour->annule = 1;
    if ($msg = $second_sejour->store()) {
        CAppUI::stepAjax($msg, UI_MSG_WARNING);

        continue;
    }

    $array_second_sejour = [$second_sejour];

    try {
        $first_sejour->checkMerge($array_second_sejour);
    } catch (Throwable $t) {
        CAppUI::stepAjax($t->getMessage(), UI_MSG_WARNING);

        continue;
    }

    // @todo mergePlainFields resets the _id
    $first_sejour->_id = $first_sejour_id;

    $merge_log = CMergeLog::logStart(CUser::get()->_id, $first_sejour, $array_second_sejour, false);
    $merge_log->logCheck();

    try {
        $first_sejour->merge($array_second_sejour, false, $merge_log);
        $merge_log->logEnd();
    } catch (Throwable $t) {
        $merge_log->logFromThrowable($t);
        CAppUI::stepAjax($msg, UI_MSG_WARNING);
        continue;
    }

    CAppUI::stepAjax("Séjour fusionné");
}
