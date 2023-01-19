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
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$action = CValue::post("action", "modify");

$sejour = new CSejour();

switch ($action) {
    case "modify":
        while (!$sejour->load(rand(1, 5000))) {
            ;
        }

        // randomize libelle
        $sejour->libelle = $sejour->libelle ? $sejour->libelle : "un libelle pour le mettre dans l'ordre";
        $libelle         = str_split($sejour->libelle);
        shuffle($libelle);
        $sejour->libelle = implode("", $libelle);
        break;

    case "create":
        //$sejour->sample();
        $sejour->group_id      = 1;
        $sejour->praticien_id  = 73;
        $sejour->patient_id    = rand(1, 5000);
        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime("+1 day");
        //$patient->updateFormFields();
        break;
}

CAppUI::displayMsg($sejour->store(), "CSejour-msg-$action");

CApp::log('hl7 trigger sejour', $sejour);

echo CAppUI::getMsg();

CApp::rip();
