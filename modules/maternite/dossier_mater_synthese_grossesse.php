<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CDepistageGrossesse;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
$print        = CView::get("print", "bool default|0");

CView::checkin();

$grossesse = new CGrossesse();
$grossesse->load($grossesse_id);
$grossesse->loadRefGroup();
$grossesse->loadRefPere();
$grossesse->loadRefsConsultations();
$grossesse->loadRefsSejours();
$grossesse->loadNbJoursHospi();
$grossesse->loadLastConsultAnesth();

$patient = $grossesse->loadRefParturiente();

$dossier = $grossesse->loadRefDossierPerinat();

/** @var CDepistageGrossesse[] $depistages */
$depistages = $grossesse->loadBackRefs("depistages", "date ASC");
foreach ($depistages as $depistage) {
  $depistage->getSA();
}

// Liste des consultants
$mediuser        = new CMediusers();
$listConsultants = $mediuser->loadProfessionnelDeSanteByPref(PERM_EDIT);

$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("listConsultants", $listConsultants);
$smarty->assign("print", $print);

$smarty->display("dossier_mater_synthese_grossesse.tpl");

