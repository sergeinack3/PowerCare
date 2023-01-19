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
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$patient_id      = CView::get('patient_id', 'ref class|CPatient');
$function_id     = CView::get('function_id', 'ref class|CFunctions');
$praticien_id    = CView::get('praticien_id', 'ref class|CMediusers');
$categorie_id    = CView::get('categorie_id', 'ref class|CConsultationCategorie', true);
$onchange        = CView::get('onchange', 'str');
$form            = CView::get('form', 'str');
$consultation_id = CView::get('consultation_id', 'ref class|CConsultation');
CView::checkin();

CAccessMedicalData::logAccess("CConsultation-$consultation_id");

$user     = ($praticien_id) ? CMediusers::findOrFail($praticien_id) : CMediusers::get();
$function = ($function_id) ? CFunctions::findOrFail($function_id) : $user->loadRefFunction();

$categorie              = new CConsultationCategorie();
$categorie->function_id = $function->_id;

$categories = $categorie->loadMatchingListEsc("nom_categorie ASC");

$cerfa_entente_prealable = 0;

// Simplified categories array for JSON treatment
$listCat = [];
foreach ($categories as $key => $cat) {
  if ($cat->seance) {
    $cerfa_entente_prealable = $cat->isCerfaEntentePrealable($patient_id);
  }

  $listCat[$cat->_id] = [
    "nom_icone"               => $cat->nom_icone,
    "duree"                   => $cat->duree,
    "commentaire"             => $cat->commentaire,
    "seance"                  => $cat->seance,
    "max_seances"             => $cat->max_seances,
    "anticipation"            => $cat->anticipation,
    "nb_consult"              => $patient_id ? $cat->countRefConsultations($patient_id) : 0,
    "cerfa_entente_prealable" => $cerfa_entente_prealable
  ];
}

$smarty = new CSmartyDP();
$smarty->assign("categories", $categories);
$smarty->assign("listCat", $listCat);
$smarty->assign("categorie_id", $categorie_id);
$smarty->assign('onchange', $onchange);
$smarty->assign('form', $form);
$smarty->assign('consultation_id', $consultation_id);
$smarty->display("httpreq_view_list_categorie");
