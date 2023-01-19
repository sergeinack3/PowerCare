<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$start        = (int)CValue::post("start", 0);
$step         = (int)CValue::post("step", 5);
$praticien_id = CValue::post("praticien_id");

$date_min = CValue::post("date_min");
$date_max = CValue::post("date_max");

$date_max_limit = CMbDT::dateTime("-5 DAYS");
if (!$date_max || ($date_max > $date_max_limit)) {
    $date_max = $date_max_limit;
}

if (!$praticien_id) {
    CAppUI::stepAjax("Veuillez choisir au moins un praticien", UI_MSG_WARNING);

    return;
}

CValue::setSession("praticien_id", $praticien_id);

$sejour = new CSejour();
$ds     = $sejour->getDS();

$where = [
    "praticien_id" => $ds->prepareIn(explode(',', $praticien_id)),
];

if ($date_min) {
    $where["sortie"] = $ds->prepare("BETWEEN ?1 AND ?2", $date_min, $date_max);
} else {
    $where["sortie"] = $ds->prepare("< ?", $date_max);
}

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, "sortie ASC", "$start,$step");

foreach ($sejours as $_sejour) {
    $_sejour->makePDFarchive();

    CAppUI::stepAjax(
        "Archive créée pour le %s du patient '%s'",
        UI_MSG_OK,
        $_sejour->_view,
        $_sejour->loadRefPatient()->_view
    );

    if (CModule::getActive("dPprescription")) {
        $prescriptions = $_sejour->loadRefsPrescriptions();

        foreach ($prescriptions as $_type => $_prescription) {
            if ($_prescription->_id && in_array($_type, ["pre_admission", "sortie"])) {
                if (
                    ($_prescription->countBackRefs("prescription_line_medicament") > 0) ||
                    ($_prescription->countBackRefs("prescription_line_element") > 0) ||
                    ($_prescription->countBackRefs("prescription_line_comment") > 0) ||
                    ($_prescription->countBackRefs("prescription_line_mix") > 0) ||
                    ($_prescription->countBackRefs("administration_dm") > 0)
                ) {
                    $query = [
                        "m"               => "prescription",
                        "raw"             => "print_prescription",
                        "prescription_id" => $_prescription->_id,
                        "dci"             => 0,
                        "in_progress"     => 0,
                        "preview"         => 0,
                    ];

                    $base = $_SERVER["SCRIPT_NAME"] . "?" . http_build_query($query, "", "&");

                    CApp::serverCall("http://127.0.0.1$base");

                    CAppUI::stepAjax(
                        "Archive créée pour la prescription de %s",
                        UI_MSG_OK,
                        CAppUI::tr("CPrescription.type.$_type")
                    );
                }
            }
        }
    }
}

if (count($sejours)) {
    CAppUI::js("nextStepSejours()");
}
