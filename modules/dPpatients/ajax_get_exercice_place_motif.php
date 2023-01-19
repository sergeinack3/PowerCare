<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;

CCanDo::checkEdit();

$praticien_id = CView::getRefCheckRead("praticien_id", "ref class|CMediusers", true);
$motif_id     = CView::getRefCheckRead("motif_id", "ref class|CConsultationCategorie");
CView::checkin();

$mediuser = new CMediusers();
$mediuser->load($praticien_id);

$consult_cat = new CConsultationCategorie();
$consult_cat->load($motif_id);

$medecin = new CMedecin();
$leftjoin["medecin_exercice_place"] = "medecin.medecin_id = medecin_exercice_place.medecin_id";
$where["rpps"]                      = " = '$mediuser->rpps' ";
$where[]    = "medecin_exercice_place_id IS NOT NULL";

$medecins = $medecin->loadList($where, null, null, 'medecin.medecin_id', $leftjoin);

if (count($medecins) == 0) {
    $medecin->rpps = $mediuser->rpps;
    $medecin->loadMatchingObjectEsc();
}
else {
    $medecin = reset($medecins);
}

$smarty = new CSmartyDP();
$smarty->assign('consult_cat', $consult_cat);

$exercice_places = [];
if (!$medecin || !$medecin->_id) {
    $smarty->assign('exercice_places', $exercice_places);
    $smarty->display('inc_get_exercice_place_motif');
    return;
}

$exercice_places = $medecin->getExercicePlaces();
if (!$exercice_places) {
    $smarty->assign('exercice_places', $exercice_places);
    $smarty->display('inc_get_exercice_place_motif');
    return;
}

$smarty->assign('exercice_places', $exercice_places);
$smarty->display('inc_get_exercice_place_motif');
