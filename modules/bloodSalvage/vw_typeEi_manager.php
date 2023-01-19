<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CTypeEi;
use Ox\Mediboard\Qualite\CEiCategorie;

$type_ei_id = CValue::getOrSession("type_ei_id");

$type_ei = new CTypeEi();

$type_ei_list = $type_ei->loadlist();
$type_ei->loadRefs();

if ($type_ei_id) {
    $type_ei->load($type_ei_id);
}

// Liste des Catégories
$firstdiv = null;

if (!$type_ei->_ref_evenement) {
    $type_ei->_ref_evenement = [];
}

$listCategories = new CEiCategorie();
$listCategories = $listCategories->loadList(null, "nom");
foreach ($listCategories as $key => $categorie) {
    if ($firstdiv === null) {
        $firstdiv = $key;
    }
    $categorie->loadRefsBack();
    $categorie->checked = null;
    foreach ($categorie->_ref_items as $keyItem => $item) {
        $item->checked = false;
        if (in_array($keyItem, $type_ei->_ref_evenement)) {
            $item->checked = true;
            if ($categorie->checked) {
                $categorie->checked .= "|" . $keyItem;
            } else {
                $categorie->checked = $keyItem;
            }
        }
    }
}

$smarty = new CSmartyDP();

$smarty->assign("type_ei", $type_ei);
$smarty->assign("type_ei_list", $type_ei_list);
$smarty->assign("firstdiv", $firstdiv);
$smarty->assign("listCategories", $listCategories);

$smarty->display("vw_typeEi_manager.tpl");
