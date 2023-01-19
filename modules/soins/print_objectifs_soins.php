<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Addictologie\CDossierAddictologie;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::read();

$sejour_id               = CView::get("sejour_id", "ref class|CSejour");
$dossier_addictologie_id = CView::get("dossier_addictologie_id", "ref class|CDossierAddictologie");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefsObjectifsSoins();
$sejour->countObjectifsSoins();
$sejour->loadRefPatient();
$praticien = $sejour->loadRefDossierAddictologie()->loadRefReferentUser();

//si on est dans le contexte du module addictologie
if ($dossier_addictologie_id) {
  $dossier = new CDossierAddictologie();
  $dossier->load($dossier_addictologie_id);
  $dossier->loadRefReferentUser();
}
$_objectifs               = $sejour->_ref_objectifs_soins;
$objectifs_by_categorie   = array();
$objectifs_sans_categorie = array();

CStoredObject::massLoadFwdRef($_objectifs, "objectif_soin_categorie_id");
foreach ($_objectifs as $_objectif) {
  $_objectif->loadRefCategorie();
  $_objectif->loadRefsReevaluations();
  $_objectif->loadRefClotureUser();
  if ($_objectif->_ref_categorie->libelle != "") {
    $objectifs_by_categorie[$_objectif->_ref_categorie->libelle][] = $_objectif;
  }
  else {
    $objectifs_sans_categorie[] = $_objectif;
  }
}
$template = new CTemplateManager();
$header   = CCompteRendu::getSpecialModel($praticien, "CSejour", "[ENTETE OBJECTIFS SOINS]");
$footer   = CCompteRendu::getSpecialModel($praticien, "CSejour", "[PIED DE PAGE OBJECTIFS SOINS]");

if ($header->_id || $footer->_id) {
  $sejour->fillTemplate($template);

  if ($header->_id) {
    $header->loadContent();
    $template->renderDocument($header->_source);
    $header_content = $template->document;
  }

  if ($footer->_id) {
    $footer->loadContent();
    $template->renderDocument($footer->_source);
    $footer_content = $template->document;
  }
}
array_push($objectifs_by_categorie, $objectifs_sans_categorie);

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("objectifs_by_categorie", $objectifs_by_categorie);
$smarty->assign("header_content", $header->_id ? $header_content : "");
$smarty->assign("footer_content", $footer->_id ? $footer_content : "");
$smarty->assign("dossier_addictologie", $dossier_addictologie_id ? $dossier : "");

$smarty->display("print_objectifs_soins");