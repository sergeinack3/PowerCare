<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CAnesthPeropChapitre;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkEdit();
$categorie_id = CView::get("categorie_id", "ref class|CAnesthPeropCategorie");
CView::checkin();

$evenement_categorie = new CAnesthPeropCategorie();
$evenement_categorie->load($categorie_id);
$evenement_categorie->loadRefFile();
$evenement_categorie->loadRefsGestesPerop();
$evenement_categorie->loadRefChapitre();

// Select current group
$evenement_categorie->group_id = CGroups::loadCurrent()->_id;

// Liste des Etablissements
$etablissements = CMediusers::loadEtablissements(PERM_READ);

$chapitre = new CAnesthPeropChapitre();
$chapitres = $chapitre->loadGroupList(array("actif" => " = '1'"));

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("evenement_categorie", $evenement_categorie);
$smarty->assign("etablissements"     , $etablissements);
$smarty->assign("geste_perop"        , new CGestePerop());
$smarty->assign("chapitres"          , $chapitres);
$smarty->display("inc_edit_evenement_perop_categorie");
