<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CModeleToPack;
use Ox\Mediboard\CompteRendu\CPack;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Files\CFilesCategory;

/**
 * Modification de pack de documents
 */

CCanDo::checkEdit();

$pack_id = CView::get("pack_id", "ref class|CPack");
$user_id = CView::get("user_id", "ref class|CMediusers default|" . CAppUI::$user->_id);

CView::checkin();

// Pour la création d'un pack, on affecte comme utilisateur celui de la session par défaut.
$user = CMediusers::get($user_id);

// Chargement du pack
$pack = new CPack();
$pack->load($pack_id);

// Accès aux packs de modèle de la fonction et de l'établissement
$module          = CModule::getActive("dPcompteRendu");
$is_admin        = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_function");
$access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CCompteRenduAcces access_group");

if ($pack->_id) {
    if ($pack->function_id && !$access_function) {
        CAppUI::accessDenied();
    }
    if ($pack->group_id && !$access_group) {
        CAppUI::accessDenied();
    }
    $pack->loadRefsNotes();
    $pack->loadBackRefs("modele_links", "modele_to_pack_id");
} else {
    if (!CMediusers::get()->isAdmin()) {
        $pack->user_id = $user->_id;
    }
}

$pack->loadRefOwner();

$listCategory        = CFilesCategory::listCatClass();
$listObjectClass     = [];
$listObjectAffichage = [];

foreach (CCompteRendu::getTemplatedClasses() as $valueClass => $localizedClassName) {
    $listObjectClass[$valueClass]     = [];
    $listObjectAffichage[$valueClass] = $localizedClassName;
}

$cats = CFilesCategory::loadListByClass(false);
foreach ($listObjectClass as $keyClass => $value) {
    if (!isset($cats[$keyClass])) {
        continue;
    }
    $listCategory = $cats[$keyClass];
    foreach ($listCategory as $cat) {
        $listObjectClass[$keyClass][$cat->_id] = $cat->nom;
    }
}
if (isset($cats[""])) {
    foreach ($cats[""] as $_cat) {
        foreach ($listObjectClass as $keyClass => $_listObjectClass) {
            $listObjectClass[$keyClass][$_cat->_id] = $_cat->nom;
        }
    }
}

$modele_to_pack = new CModeleToPack();
$where["pack_id"] = "= '$pack_id'";
$modeles_to_pack = $modele_to_pack->loadList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("pack", $pack);
$smarty->assign("listCategory", $listCategory);
$smarty->assign("user_id", $user_id);
$smarty->assign("access_function", $access_function);
$smarty->assign("access_group", $access_group);
$smarty->assign("listObjectClass", $listObjectClass);
$smarty->assign("listObjectAffichage", $listObjectAffichage);
$smarty->assign("modeles_to_pack", $modeles_to_pack);

$smarty->display("inc_edit_pack");
