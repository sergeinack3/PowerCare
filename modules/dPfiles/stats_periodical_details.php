<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkEdit();
$doc_class    = CView::get("doc_class", "str default|CFile");
$doc_id       = CView::get("doc_id", "ref class|$doc_class");
$owner_guid   = CView::get("owner_guid", "str");
$category_id  = CView::get("category_id", "ref class|CFilesCategory");
$object_class = CView::get("object_class", "str");
$factory      = CView::get('factory', 'str');
CView::checkin();
CView::enableSlave();

// Get concrete class
if (!is_subclass_of($doc_class, CDocumentItem::class)) {
    trigger_error("Wrong '$doc_class' won't inerit from CDocumentItem", E_USER_ERROR);

    return;
}

// Users
$owner    = $owner_guid ? CStoredObject::loadFromGuid($owner_guid, true) : null;
$user_ids = CDocumentItem::getUserIds($owner);

// Stats
/** @var CDocumentItem $doc */
$doc = new $doc_class();
$doc->load($doc_id);

if ($doc instanceof CCompteRendu && $factory) {
    $periodical_details = $doc->getPeriodicalStatsDetails($user_ids, $object_class, $category_id, 10, null, $factory);
} else {
    $periodical_details = $doc->getPeriodicalStatsDetails($user_ids, $object_class, $category_id, 10);
}


$is_doc = get_class($doc) === CCompteRendu::class;

// Category
$category = new CFilesCategory();
$category->load($category_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("doc", $doc);
$smarty->assign("owner", $owner);
$smarty->assign("object_class", $object_class);
$smarty->assign("category", $category);
$smarty->assign("owner_guid", $owner_guid);
$smarty->assign("periodical_details", $periodical_details);
$smarty->assign('is_doc', $is_doc);
$smarty->assign("factory", $factory);
$smarty->display("stats_periodical_details");
