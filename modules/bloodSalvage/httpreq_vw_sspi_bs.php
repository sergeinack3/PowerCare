<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\BloodSalvage\CCellSaver;
use Ox\Mediboard\BloodSalvage\CTypeEi;
use Ox\Mediboard\BloodSalvage\Services\BloodSalvageService;
use Ox\Mediboard\Personnel\CPersonnel;

$blood_salvage   = new CBloodSalvage();
$date            = CValue::getOrSession("date", CMbDT::date());
$op              = CValue::getOrSession("op");
$totaltime       = "00:00:00";
$modif_operation = CCanDo::edit() || $date >= CMbDT::date();
$timing          = [];
$tabAffected     = [];
/*
 * Liste des cell saver.
 */
$cell_saver      = new CCellSaver();
$list_cell_saver = $cell_saver->loadList();

/*
 * Liste du personnel présent en SSPI.
 */
$list_nurse_sspi = CPersonnel::loadListPers("reveil");

/*
 * Liste d'incidents transfusionnels possibles.
 */
$incident       = new CTypeEi();
$liste_incident = $incident->loadList();

/*
 * Création du tableau d'affectation et de celui des timings.
 */
$tabAffected  = [];
$timingAffect = [];

if ($op) {
    $where                 = [];
    $where["operation_id"] = "='$op'";
    $blood_salvage->loadObject($where);
    $blood_salvage->loadRefsFwd();
    $blood_salvage->loadRefPlageOp();
    $blood_salvage->_ref_operation->loadRefPatient();
    $timing["_recuperation_start"] = [];
    $timing["_recuperation_end"]   = [];
    $timing["_transfusion_start"]  = [];
    $timing["_transfusion_end"]    = [];
    $max_add_minutes               = CAppUI::gconf("dPsalleOp Timing_list max_add_minutes");
    foreach ($timing as $key => $value) {
        for (
            $i = -CAppUI::gconf(
                "dPsalleOp Timing_list max_sub_minutes"
            ); $i < $max_add_minutes && $blood_salvage->$key !== null; $i++
        ) {
            $timing[$key][] = CMbDT::time("$i minutes", $blood_salvage->$key);
        }
    }

    BloodSalvageService::loadAffected($blood_salvage->_id, $list_nurse_sspi, $tabAffected, $timingAffect);
}

$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("totaltime", $totaltime);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("timing", $timing);
$smarty->assign("timingAffect", $timingAffect);
$smarty->assign("tabAffected", $tabAffected);
$smarty->assign("list_cell_saver", $list_cell_saver);
$smarty->assign("list_nurse_sspi", $list_nurse_sspi);
$smarty->assign("liste_incident", $liste_incident);

$smarty->display("inc_vw_sspi_bs.tpl");
