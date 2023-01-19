<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Qualite\CEiCategorie;
use Ox\Mediboard\Qualite\CFicheEi;

$fiche_ei_id = CValue::getOrSession("fiche_ei_id", null);
$catFiche    = array();

$fiche = new CFicheEi;
if (!$fiche->load($fiche_ei_id)) {
  // Cette fiche n'est pas valide
  $fiche_ei_id = null;
  CValue::setSession("fiche_ei_id");
  $fiche = new CFicheEi();
}
else {
  $fiche->loadRefsFwd();
  $fiche->loadRefItems();

  // Liste des Catégories d'EI
  $categorie = new CEiCategorie();
  /** @var CEiCategorie[] $listCategories */
  $listCategories = $categorie->loadList(null, "nom");

  foreach ($listCategories as $keyCat => $_categorie) {
    foreach ($fiche->_ref_items as $_item) {
      if ($_item->ei_categorie_id == $keyCat) {
        if (!isset($catFiche[$listCategories[$keyCat]->nom])) {
          $catFiche[$listCategories[$keyCat]->nom] = array();
        }
        $catFiche[$listCategories[$keyCat]->nom][] = $_item;
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("catFiche", $catFiche);
$smarty->assign("fiche", $fiche);

$smarty->display("print_fiche.tpl");
