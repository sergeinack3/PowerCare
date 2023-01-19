<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CExamAudio;
use Ox\Mediboard\Cabinet\CExamAudioGraph;
use Ox\Mediboard\Cabinet\CExamAudioGraphAudiometrieTonale;
use Ox\Mediboard\Cabinet\CExamAudioGraphAudiometrieVocale;
use Ox\Mediboard\Cabinet\CExamAudioGraphTympanometrie;

CCanDo::checkRead();

$examaudio_id      = CView::get("examaudio_id", "ref class|CExamAudio notNull");
$type              = CView::get("type", "str notNull");
$side              = CView::get("side", "enum list|gauche|droite");
$old_exam_audio_id = CView::get("old_exam_audio_id", "ref class|CExamAudio");

CView::checkin();



$exam_audio = new CExamAudio();
$exam_audio->load($examaudio_id);

if ($type === "all") {
    $graphs = [];

    $_graph = new CExamAudioGraphAudiometrieTonale($exam_audio);
    $_graph->make("droite");
    $graphs[] = $_graph->getStruct();

    $_graph = new CExamAudioGraphAudiometrieTonale($exam_audio);
    $_graph->make("gauche");
    $graphs[] = $_graph->getStruct();

    $_graph = new CExamAudioGraphAudiometrieVocale($exam_audio);
    $_graph->make();
    $graphs[] = $_graph->getStruct();

    $_graph = new CExamAudioGraphTympanometrie($exam_audio);
    $_graph->make("droite");
    $graphs[] = $_graph->getStruct();

    $_graph = new CExamAudioGraphTympanometrie($exam_audio);
    $_graph->make("gauche");
    $graphs[] = $_graph->getStruct();

    //Ancien exam audio
    if ($old_exam_audio_id) {
        $exam_audio_old = CExamAudio::findOrFail($old_exam_audio_id);
        $exam_audio_old->loadRefConsult();

        $graphs_old = [];

        $graph_old = new CExamAudioGraphAudiometrieTonale($exam_audio_old);
        $graph_old->make("droite", true);
        $graphs_old[] = $graph_old->getStruct();

        $graph_old = new CExamAudioGraphAudiometrieTonale($exam_audio_old);
        $graph_old->make("gauche", true);
        $graphs_old[] = $graph_old->getStruct();

        $graph_old = new CExamAudioGraphAudiometrieVocale($exam_audio_old);
        $graph_old->make();
        $graphs_old[] = $graph_old->getStruct();

        $graph_old = new CExamAudioGraphTympanometrie($exam_audio_old);
        $graph_old->make("droite");
        $graphs_old[] = $graph_old->getStruct();

        $graph_old = new CExamAudioGraphTympanometrie($exam_audio_old);
        $graph_old->make("gauche");
        $graphs_old[] = $graph_old->getStruct();

        //Changement des couleurs et des libellés pour tous les graphs
        for ($i = 0; $i < count($graphs_old); $i++) {
            for ($j = 0; $j < count($graphs_old[$i]["series"]); $j++) {
                CExamAudioGraph::setGraphAsOldGraph($graphs_old[$i]["series"][$j], $exam_audio_old);
            }
        }

        foreach ($graphs as $graph) {
            foreach ($graphs_old as $graph_old) {
                if ($graph["id"] == $graph_old["id"]) {
                    $graphs_final[] = [
                        "id"     => $graph["id"],
                        "series" => array_merge($graph_old["series"], $graph["series"]),
                    ];
                }
            }
        }
        CApp::json($graphs_final);
    }
    CApp::json($graphs);
}

if (!isset(CExamAudioGraph::$types[$type])) {
    CApp::json(false);
}

$class = CExamAudioGraph::$types[$type];
/** @var CExamAudioGraphTympanometrie|CExamAudioGraphAudiometrieTonale|CExamAudioGraphAudiometrieVocale $graph */
$graph = new $class($exam_audio);
$graph->make($side);

//Ancien exam audio
if ($old_exam_audio_id) {
    $old_exam_audio = CExamAudio::findOrFail($old_exam_audio_id);
    $old_exam_audio->loadRefConsult();
    $graph_old = new $class($old_exam_audio);
    $graph_old->make($side, true);

    //Changement des couleurs et des libellés pour un graph
    for ($i = 0; $i < count($graph_old->series); $i++) {
        CExamAudioGraph::setGraphAsOldGraph($graph_old->series[$i], $old_exam_audio);
    }

    $struct     = $graph->getStruct();
    $struct_old = $graph_old->getStruct();

    $graph_final["id"]     = $struct["id"];
    $graph_final["series"] = array_merge($struct_old["series"], $struct["series"]);
    CApp::json($graph_final);
}

CApp::json($graph->getStruct());
