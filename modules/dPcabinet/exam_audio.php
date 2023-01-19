<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamAudio;
use Ox\Mediboard\Cabinet\CExamAudioGraphAudiometrieTonale;
use Ox\Mediboard\Cabinet\CExamAudioGraphAudiometrieVocale;
use Ox\Mediboard\Cabinet\CExamAudioGraphTympanometrie;

CCanDo::checkRead();

$_conduction         = CView::get("_conduction", "str default|aerien", true);
$_oreille            = CView::get("_oreille", "str default|gauche", true);
$consultation_id     = CView::get("consultation_id", "ref class|CConsultation notNull", true);
$old_consultation_id = CView::get("old_consultation_id", "ref class|CConsultation", false);

CView::checkin();

$exam_audio                  = new CExamAudio();
$exam_audio->consultation_id = $consultation_id;

if (!$exam_audio->loadMatchingObject()) {
    $exam_audio->store();
}

$exam_audio->needsRead();

$consult = $exam_audio->loadRefConsult();

CAccessMedicalData::logAccess($consult);

$consult->loadRefPatient();
$consult->loadRefPlageConsult();

$graphs = [
    "audiometrie_tonale" => [
        "gauche" => null,
        "droite" => null,
    ],
    "audiometrie_vocale" => null,
    "tympanometrie"      => [
        "gauche" => null,
        "droite" => null,
    ],
];

foreach (CExamAudio::$sides as $_side) {
    $graph = new CExamAudioGraphAudiometrieTonale($exam_audio);
    $graph->make($_side);
    $graphs[$graph->type][$_side] = $graph;
}

$graph = new CExamAudioGraphAudiometrieVocale($exam_audio);
$graph->make();
$graphs[$graph->type] = $graph;

foreach (CExamAudio::$sides as $_side) {
    $graph = new CExamAudioGraphTympanometrie($exam_audio);
    $graph->make($_side);
    $graphs[$graph->type][$_side] = $graph;
}

$bilan = $exam_audio->getBilan();

//Récupération des anciens examen audio
$consultations_anciennes = [];
$consultations           = $consult->_ref_patient->loadRefsConsultations();

foreach ($consultations as $_consultation) {
    if ($_consultation->_id != $consultation_id) {
        $examen = $_consultation->loadRefsExamAudio();
        if ($examen->_id) {
            $consultations_anciennes[$_consultation->_id] = $_consultation;
        }
    }
}

if ($old_consultation_id) {
    $old_consultation = new CConsultation();
    $old_consultation->load($old_consultation_id);

    $old_exam_audio = $consultations_anciennes[$old_consultation_id]->_ref_examaudio;

    $graphs_old = [
        "audiometrie_tonale" => [
            "gauche" => null,
            "droite" => null,
        ],
        "audiometrie_vocale" => null,
        "tympanometrie"      => [
            "gauche" => null,
            "droite" => null,
        ],
    ];

    foreach (CExamAudio::$sides as $_side) {
        $graph_old = new CExamAudioGraphAudiometrieTonale($old_exam_audio);
        $graph_old->make($_side, true);
        $graphs_old[$graph_old->type][$_side] = $graph_old;
    }

    $graph_old = new CExamAudioGraphAudiometrieVocale($old_exam_audio);
    $graph_old->make();
    $graphs_old[$graph_old->type] = $graph_old;

    foreach (CExamAudio::$sides as $_side) {
        $graph_old = new CExamAudioGraphTympanometrie($old_exam_audio);
        $graph_old->make($_side);
        $graphs_old[$graph_old->type][$_side] = $graph_old;
    }

    if (count(array_unique($old_exam_audio->_droite_pasrep)) === 1 && $old_exam_audio->_droite_pasrep[0] == ""){
        $old_exam_audio->_droite_pasrep=[];
    }
    if (count(array_unique($old_exam_audio->_gauche_pasrep)) === 1 && $old_exam_audio->_gauche_pasrep[0] == ""){
        $old_exam_audio->_gauche_pasrep=[];
    }
    $old_bilan = $old_exam_audio->getBilan();
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("graphs", $graphs);
$smarty->assign("_conduction", $_conduction);
$smarty->assign("_oreille", $_oreille);
$smarty->assign("exam_audio", $exam_audio);
$smarty->assign("bilan", $bilan);
$smarty->assign("consultations_anciennes", $consultations_anciennes);
$smarty->assign("consultation_id", $consultation_id);
$smarty->assign("old_consultation_id", $old_consultation_id);
if ($old_consultation_id) {
    $smarty->assign("graphs_old", $graphs_old);
    $smarty->assign("old_exam_audio", $old_exam_audio);
    $smarty->assign("old_bilan", $old_bilan);
    $smarty->assign("old_consultation", $old_consultation);
}

$smarty->display('exam_audio');
