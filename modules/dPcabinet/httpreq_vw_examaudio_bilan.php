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
use Ox\Mediboard\Cabinet\CExamAudio;

CCanDo::checkRead();

$examaudio_id      = CView::get("examaudio_id", "ref class|CExamAudio");
$old_exam_audio_id = CView::get("old_exam_audio_id", "ref class|CExamAudio");

CView::checkin();

$exam_audio = new CExamAudio();
$exam_audio->load($examaudio_id);

$bilan = $exam_audio->getBilan();

if ($old_exam_audio_id) {
    $old_exam_audio = new CExamAudio();
    $old_exam_audio->load($old_exam_audio_id);
    $old_exam_audio->loadRefConsult();
    $old_bilan = $old_exam_audio->getBilan();
};

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("exam_audio", $exam_audio);
$smarty->assign("bilan", $bilan);
if ($old_exam_audio_id) {
    $smarty->assign("old_exam_audio", $old_exam_audio);
    $smarty->assign("old_consultation_id", $old_exam_audio->_ref_consult->_id);
    $smarty->assign("old_consultation", $old_exam_audio->_ref_consult);
    $smarty->assign("old_bilan", $old_bilan);
} else {
    $smarty->assign("old_consultation_id", null);
}

$smarty->display("inc_exam_audio/inc_examaudio_bilan.tpl");
