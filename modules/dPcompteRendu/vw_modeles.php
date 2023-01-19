<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Dmp\CDMPTools;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Interface des modèles
 */
CCanDo::checkRead();

// Filtres
$filtre = new CCompteRendu();
$filtre->user_id         = CValue::getOrSession("user_id");
$filtre->function_id     = CValue::getOrSession("function_id");
$filtre->object_class    = CValue::getOrSession("object_class");
$filtre->type            = CValue::getOrSession("type");
$filtre->compte_rendu_id = CValue::get("compte_rendu_id");
$filtre->actif           = CValue::getOrSession("actif");

$order_col = CView::get("order_col", "enum list|nom|object_class|file_category_id|type|_count_utilisation|_image_status|_date_last_use default|object_class", true);
$order_way = CView::get("order_way", "enum list|ASC|DESC default|DESC", true);

CView::checkin();

// On ne met que les classes qui ont une methode fillTemplate
$filtre->_specs['object_class']->_locales = CCompteRendu::$templated_classes;

if (!$filtre->user_id && !$filtre->function_id) {
  $filtre->user_id = CMediusers::get()->_id;
}

$filtre->loadRefUser();
$filtre->loadRefFunction();

$smarty = new CSmartyDP();

$smarty->assign("filtre"   , $filtre);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);
$smarty->assign("document_item", new CCompteRendu());
$smarty->assign("dmp_doc_types", (CModule::getActive("dmp")) ? CDocumentItem::getDmpTypeDocs() : null);

$smarty->display("vw_modeles");
