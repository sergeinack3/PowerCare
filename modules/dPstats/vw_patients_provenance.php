<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::read();

$year = CValue::get("year", CMbDT::transform(null, null, "%Y"));
$type = CValue::get("type", "traitant");

CView::enforceSlave();

$group_id = CGroups::loadCurrent()->_id;

// Compteur d'années
$years = [];
for ($_year = 1980; $_year <= 2030; $_year++) {
    $years[] = $_year;
}

// En utilisant les médecins adressant le séjour
$queryAdresse = "SELECT
                   COUNT(DISTINCT(`sejour`.`sejour_id`)) AS total,
                   `medecin`.`nom`, `medecin`.`prenom`, `medecin`.`adresse`, `medecin`.`ville`, `medecin`.`cp`
                 FROM `sejour`
                 LEFT JOIN `medecin`
                   ON `medecin`.`medecin_id` = `sejour`.`adresse_par_prat_id`
                 WHERE `sejour`.`entree` BETWEEN '$year-01-01' AND '$year-12-31'
                   AND `sejour`.`group_id` = '$group_id'
                 GROUP BY `sejour`.`adresse_par_prat_id`
                 ORDER BY total DESC";

// En utilisant le médecin traitant
$queryTraitant = "SELECT
                    COUNT(DISTINCT(`sejour`.`sejour_id`)) AS total,
                    `medecin`.`nom`, `medecin`.`prenom`, `medecin`.`adresse`, `medecin`.`ville`, `medecin`.`cp`
                  FROM `sejour`
                  LEFT JOIN `patients`
                    ON `patients`.`patient_id` = `sejour`.`patient_id`
                  LEFT JOIN `medecin`
                    ON `medecin`.`medecin_id` = `patients`.`medecin_traitant`
                  WHERE `sejour`.`entree` BETWEEN '$year-01-01' AND '$year-12-31'
                    AND `sejour`.`group_id` = '$group_id'
                  GROUP BY `patients`.`medecin_traitant`
                  ORDER BY total DESC";

// En utilisant l'adresse du patient
$insee_dsn  = CSQLDataSource::get("INSEE");
$base_insee = $insee_dsn->config["dbname"];

$queryPatient = "SELECT
                COUNT(DISTINCT(`sejour`.`sejour_id`)) AS total,
                `$base_insee`.`communes_france`.`commune` AS ville, `patients`.`cp`
              FROM `sejour`
              LEFT JOIN `patients`
                ON `patients`.`patient_id` = `sejour`.`patient_id`
              LEFT JOIN `$base_insee`.`communes_france`
                ON `$base_insee`.`communes_france`.`code_postal` = `patients`.`cp`
              WHERE `sejour`.`entree` BETWEEN '$year-01-01' AND '$year-12-31'
                AND `sejour`.`group_id` = '$group_id'
              GROUP BY `patients`.`cp`
              ORDER BY total DESC";

$source     = CSQLDataSource::get("std");
$listResult = [];
switch ($type) {
    case "traitant":
        $listResult = $source->loadList($queryTraitant);
        break;
    case "adresse":
        $listResult = $source->loadList($queryAdresse);
        break;
    case "domicile":
        $listResult = $source->loadList($queryPatient);
        break;
    default:
        $listResult = $source->loadList($queryTraitant);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("years", $years);
$smarty->assign("year", $year);
$smarty->assign("type", $type);
$smarty->assign("listResult", $listResult);

$smarty->display("vw_patients_provenance.tpl");
