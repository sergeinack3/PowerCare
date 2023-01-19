<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Stock\CProductCategory;

CCanDo::checkRead();

if (!CModule::getActive('bcb')) {
  CAppUI::stepMessage(
    UI_MSG_ERROR,
    "Le module de médicament autonome est en cours de developpement.
    Pour être utilisé, ce module a pour le moment besoin d'être connecté à une base de données de médicaments externe"
  );
  return;
}

$lettre = CValue::get("lettre");
$category_id  = CValue::getOrSession("category_id", CAppUI::conf('dPmedicament CBcbProduitLivretTherapeutique product_category_id'));

$listProduits = array();

// Chargement des functions
$function = new CFunctions;
$functions = $function->loadSpecialites(PERM_EDIT);


// Si on est admin ou que $functions contient plus d'un élément,
// on récupère la fonction en session
if (CAppUI::$user->isAdmin() || count($functions) > 1) {
  $function_guid = CValue::getOrSession("function_guid", reset($functions)->_guid);
  
}
else {
  // Sinon, c'est la fonction de l'utilisateur
  $function_guid = "CFunctions-".CAppUI::$user->function_id;
}


$function = CMbObject::loadFromGuid($function_guid);

$tabLettre = range('A', 'Z');

// --- Chargement de l'arbre ATC ---
$codeATC     = CValue::get("codeATC");

$classeATC   = new CMedicamentClasseATC();
$chapitreATC = $codeATC ? $classeATC->getLibelle($codeATC) : ''; // Nom du chapitre selectionné
$arbreATC    = $classeATC->loadArbre($codeATC); // Chargements des sous chapitres

$categories = array();

if (CModule::getActive("dPstock")) {
  $category = new CProductCategory;
  $categories = $category->loadList(null, "name");
}
 
// Création du template
$smarty = new CSmartyDP("modules/dPmedicament");

$smarty->assign("listProduits", $listProduits);
$smarty->assign("arbreATC"    , $arbreATC);
$smarty->assign("codeATC"     , $codeATC);
$smarty->assign("chapitreATC" , $chapitreATC);
$smarty->assign("lettre"      , $lettre);
$smarty->assign("produits_livret", array());
$smarty->assign("tabLettre"   , $tabLettre);
$smarty->assign("category_id" , $category_id);
$smarty->assign("categories"  , $categories);
$smarty->assign("livret_cabinet", 1);
$smarty->assign('functions'   , $functions);
$smarty->assign("function_guid" , $function_guid);

$smarty->display("vw_idx_livret");
