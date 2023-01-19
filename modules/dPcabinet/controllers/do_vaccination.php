<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$vaccines          = (array)CView::post("vaccines", ["str", "default" => []]);
$recall_age        = CView::post("recall_age", "num");
$patient_id        = CView::post("patient_id", "ref class|CPatient");
$practitioner_name = utf8_decode(CView::post("practitioner_name", "str"));
$injection_date    = CView::post("date_injection", "birthDate");
$injection_heure   = CView::post("heure_injection", "time");
$speciality        = utf8_decode(CView::post("speciality", "str"));
// This code can be the same as the speciality. Later in the code, speciality will become text ;)
$code_cip        = CView::post("cip_product", "str");
$batch           = utf8_decode(CView::post("batch", "str"));
$expiration_date = CView::post("expiration_date", "date");
$remarques       = utf8_decode(CView::post("remarques", "text"));
$injection_id    = CView::post("injection_id", "ref class|CInjection");
$delete          = CView::post("delete", "bool default|false");
CView::checkin();

if (!$delete && !$injection_id && !$vaccines) {
    CApp::rip();
}

$patient = CPatient::findOrFail($patient_id);

if ($delete) {
    $injection = CInjection::findOrFail($injection_id);
    $vaccines  = array_values(CMbArray::pluck($injection->loadRefVaccinations(), "type"));

    $injection->delete();
    CApp::json(
        [
            "speciality" => null,
            "batch"      => null,
            "recall_age" => $injection->recall_age,
            "types"      => $vaccines,
            "remarques"  => "",
        ]
    );
}

$code = $code_cip;
if ($code_cip || (ctype_digit($speciality) && (strlen($speciality) === 13 || strlen($speciality) === 7))) {
    if ($code_cip || ctype_digit($speciality)) {
        $code_cip = strlen($code_cip) == 14 ? substr($code_cip, 1, 13) : $code_cip;
        if ($code_cip) {
            $code = (strlen($code_cip) === 13) ? substr($code_cip, 5, -1) : $code_cip;
        } elseif ($speciality) {
            $code = (strlen($speciality) === 13) ? substr($speciality, 5, -1) : $speciality;
        }
    }
    if ($code) {
        $medication = CMedicamentArticle::get($code);
        if ($medication->getLibelleCIP()) {
            $speciality = $medication->getLibelleCIP();
        }
    }
}

$injection                    = CInjection::findOrNew($injection_id);
$injection->patient_id        = $patient_id;
$injection->recall_age        = $recall_age;
$injection->practitioner_name = $practitioner_name;
$injection->speciality        = $speciality;
$injection->cip_product       = $code;
$injection->batch             = $batch;
$injection->expiration_date   = $expiration_date;
$injection->remarques         = $remarques;
if ($injection_heure) {
    $injection->injection_date = CMbDT::dateTime($injection_date . " " . $injection_heure);
} else {
    $injection->injection_date = CMbDT::dateTime($injection_date);
}

$msg = $injection->store();
if ($msg) {
    throw new Exception($msg);
}

if (!$injection_id) {
    $vaccinations = new CVaccination();

    foreach ($vaccines as $_vaccine) {
        $vaccination               = new CVaccination();
        $vaccination->injection_id = $injection->_id;
        $vaccination->type         = $_vaccine;
        $vaccination->store();
    }
}

if ($injection->_id) {
    $vaccines = array_values(CMbArray::pluck($injection->loadRefVaccinations(), "type"));
}

CApp::json(
    [
        "injection_id"    => $injection->_id,
        "speciality"      => $injection->speciality,
        "batch"           => $injection->batch,
        "expiration_date" => $injection->expiration_date,
        "injection_date"  => $injection->injection_date,
        "birthday"        => $patient->naissance,
        "recall_age"      => $injection->recall_age,
        "types"           => $vaccines,
        "remarques"       => $injection->remarques,
    ]
);
