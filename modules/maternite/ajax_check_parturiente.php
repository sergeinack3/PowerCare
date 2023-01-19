<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$patient_id = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

// Dernière grossesse active
$grossesse = new CGrossesse();
$grossesse->parturiente_id = $patient->_id;
$grossesse->active = 1;
$grossesse->loadMatchingObject("terme_prevu DESC");

$consult = new CConsultation();

$ljoin = [
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
];

$where = [
  "patient_id" => "= '$patient->_id'",
  "date"       => "= '" . CMbDT::date() . "'",
  "grossesse_id" => "IS NOT NULL",
  'annule'     => "= '0'"
];

$nb_consults = $consult->countList($where, null, $ljoin);

$last_sejour = new CSejour();

// Si entrée réelle, la consultation sera liée à ce séjour d'hospit
foreach ($grossesse->loadRefsSejours() as $_sejour) {
    if ($_sejour->type === 'comp' && $_sejour->entree_reelle && !$_sejour->sortie_reelle) {
        $last_sejour = $_sejour;
        break;
    }
}

$result = [
  "grossesse" => [
    "terme_prevu"  => $grossesse->terme_prevu,
    "grossesse_id" => $grossesse->_id,
    "last_sejour"  => $last_sejour->_id
  ],

  "nb_consults" => intval($nb_consults)
];

CApp::json($result);
