<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

$context_guid      = CView::get("context_guid", "str");
$context_copy_guid = CView::get("context_copy_guid", "str");
$unique_all_docs   = CView::get("unique_all_docs", "str");
$tri               = CView::get("tri", "enum list|date|context|cat default|date");
$display           = CView::get("display", "enum list|icon|list default|" . CAppUI::pref("display_all_docs"));
$type_doc          = CView::get("type_doc", "str default|all");
$prop_cat_ids      = [
    "str",
    "default" => [],
];
$cat_ids           = CView::get("cat_ids", $prop_cat_ids);
$importance        = CView::get("importance", "str");
$user_id           = CView::get("user_id", "ref class|CMediusers");
$function_id       = CView::get("function_id", "ref class|CFunctions");
$order_col         = CView::get("order_col", "enum list|file_date|nom|author_id default|file_date");
$order_way         = CView::get("order_way", "enum list|ASC|DESC default|" . CAppUI::pref("choose_sort_file_date"));
$ondblclick        = CView::get("ondblclick", "str");
$with_docs         = CView::get("with_docs", "bool default|1");
$with_files        = CView::get("with_files", "bool default|1");
$with_forms        = CView::get("with_forms", "bool default|1");

CView::checkin();

$params = [
    "with_cancelled" => true,
    "type_doc"       => $type_doc,
    "cat_ids"        => $cat_ids,
    "importance"     => $importance,
    "user_id"        => $user_id,
    "function_id"    => $function_id,
    "tri"            => $tri,
    'with_docs'      => $with_docs,
    'with_files'     => $with_files,
    'with_forms'     => $with_forms,
];

$context = CMbObject::loadFromGuid($context_guid);
$context->loadAllDocs($params);
$context->filterDuplicatingDocs($context_copy_guid);
$context->filterDuplicatingDevis();

foreach ($context->_all_docs["docitems"] as $key => $_docitems_by_context) {
    $sorter     = [];
    $sorter_way = null;

    $sorter_way = $order_way == "ASC" ? SORT_ASC : SORT_DESC;

    switch ($order_col) {
        default:
        case "nom":
            $sorter = CMbArray::pluck($_docitems_by_context, "_view");
            break;
        case "author_id":
        case "file_date":
            foreach ($_docitems_by_context as $_key => $_docitem) {
                $field = null;

                switch ($_docitem->_class) {
                    case "CCompteRendu":
                        $field = $order_col === "author_id" ? $_docitem->_ref_author->_view : $_docitem->creation_date;
                        break;
                    case "CFile":
                        $field = $order_col === "author_id" ? $_docitem->_ref_author->_view : $_docitem->file_date;
                        break;
                    default:
                        $field = $order_col === "author_id" ?
                            $_docitem->_ref_ex_object->_ref_owner->_view : $_docitem->_ref_ex_object->datetime_edit;
                }
                $sorter[$_key] = $field;
            }
    }

    array_multisort($sorter, $sorter_way, $context->_all_docs["docitems"][$key]);

}

$smarty = new CSmartyDP();

$smarty->assign("context", $context);
$smarty->assign("unique_all_docs", $unique_all_docs);
$smarty->assign("display", $display);
$smarty->assign("tri", $tri);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);
$smarty->assign("ondblclick", $ondblclick);
$smarty->display("inc_all_docs");
