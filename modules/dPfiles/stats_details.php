<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkEdit();
$doc_class  = CView::get("doc_class", "str default|CFile");
$doc_id     = CView::get("doc_id", "ref class|$doc_class");
$owner_guid = CView::get("owner_guid", "str");
$date_min   = CView::get("date_min", "dateTime");
$date_max   = CView::get("date_max", "dateTime");
$period     = CView::get("period", "enum list|year|month|week|day|hour");
$factory    = CView::get('factory', 'str');
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

// Period alternative to date interval
if ($date_min && $period) {
    $date_max = CMbDT::dateTime("+ 1 $period", $date_min);
}

// Query prepare
/** @var CDocumentItem $doc */
$doc          = $doc_class::findOrNew($doc_id);
if ($doc instanceof CCompteRendu) {
    $user_details = $doc->getUsersStatsDetails($user_ids, $date_min, $date_max, $factory);
} else {
    $user_details = $doc->getUsersStatsDetails($user_ids, $date_min, $date_max);
}


$is_doc = get_class($doc) === CCompteRendu::class;

// Reorder and make totals
$class_totals     = [];
$class_details    = [];
$category_totals  = [];
$category_details = [];

$big_totals = [
    "count"  => array_sum(CMbArray::pluck($user_details, "docs_count")),
    "weight" => array_sum(CMbArray::pluck($user_details, "docs_weight")),
];

if ($is_doc) {
    $big_totals['docs_read_time']  = array_sum(CMbArray::pluck($user_details, "docs_read_time"));
    $big_totals['docs_write_time'] = array_sum(CMbArray::pluck($user_details, "docs_write_time"));
}

// Details
foreach ($user_details as &$_details) {
    $_details["count"]          = $_details["docs_count"];
    $_details["weight"]         = $_details["docs_weight"];
    $_details["count_percent"]  = $big_totals["count"] ? ($_details["count"] / $big_totals["count"]) : 0;
    $_details["weight_percent"] = $big_totals["weight"] ? ($_details["weight"] / $big_totals["weight"]) : 0;
    $_details["weight_average"] = $_details["count"] ? ($_details["weight"] / $_details["count"]) : 0;

    if ($is_doc) {
        $_detauls['read_percent']  = $big_totals["docs_read_time"] ?
            ($_details['docs_read_time'] / $big_totals["docs_read_time"]) : 0;
        $_detauls['write_percent'] = $big_totals["docs_write_time"] ?
            ($_details['docs_write_time'] / $big_totals["docs_write_time"]) : 0;
    }
}

CMbArray::pluckSort($user_details, SORT_DESC, "weight");

// Totals
$report = error_reporting(0);
unset($_details);
foreach ($user_details as $_details) {
    $count        = $_details["count"];
    $weight       = $_details["weight"];
    $object_class = $_details["object_class"];
    $category_id  = $_details["category_id"];

    if ($is_doc) {
        $docs_read_time  = $_details['docs_read_time'];
        $docs_write_time = $_details['docs_write_time'];
    }

    $class_totals[$object_class]["count"]    += $count;
    $class_totals[$object_class]["weight"]   += $weight;
    $category_totals[$category_id]["count"]  += $count;
    $category_totals[$category_id]["weight"] += $weight;

    if ($is_doc) {
        //prepare les données pour le calcul de la moyenne de moyenne
        $class_totals[$object_class]["docs_read_time"]    += $docs_read_time * $_details["count"];
        $class_totals[$object_class]["docs_write_time"]   += $docs_write_time * $_details["count"];
        $category_totals[$category_id]["docs_read_time"]  += $docs_read_time * $_details["count"];
        $category_totals[$category_id]["docs_write_time"] += $docs_write_time * $_details["count"];
    }
}
error_reporting($report);

foreach ($class_totals as &$_total) {
    $_total["count_percent"]  = $big_totals["count"] ? ($_total["count"] / $big_totals["count"]) : 0;
    $_total["weight_percent"] = $big_totals["weight"] ? ($_total["weight"] / $big_totals["weight"]) : 0;
    $_total["weight_average"] = $_total["count"] ? ($_total["weight"] / $_total["count"]) : 0;
}

CMbArray::pluckSort($class_totals, SORT_DESC, "weight");

foreach ($category_totals as &$_total) {
    $_total["count_percent"]  = $big_totals["count"] ? ($_total["count"] / $big_totals["count"]) : 0;
    $_total["weight_percent"] = $big_totals["weight"] ? ($_total["weight"] / $big_totals["weight"]) : 0;
    $_total["weight_average"] = $_total["count"] ? ($_total["weight"] / $_total["count"]) : 0;
}

CMbArray::pluckSort($category_totals, SORT_DESC, "weight");

if ($is_doc) {
    foreach ($user_details as $_key => $_user_detail) {
        $user_details[$_key]['docs_read_time']  = CMbDT::friendlyDuration($_user_detail['docs_read_time'])['locale'];
        $user_details[$_key]['docs_write_time'] = CMbDT::friendlyDuration($_user_detail['docs_write_time'])['locale'];
    }

    foreach ($class_totals as $_key => $_class_total) {
        $_class_total['docs_read_time']  = $_class_total["count"] ?
            $_class_total["docs_read_time"] / $_class_total["count"] : 0;
        $_class_total['docs_write_time'] = $_class_total["count"] ?
            $_class_total["docs_write_time"] / $_class_total["count"] : 0;
        $class_totals[$_key]['docs_read_time']  = CMbDT::friendlyDuration($_class_total['docs_read_time'])['locale'];
        $class_totals[$_key]['docs_write_time'] = CMbDT::friendlyDuration($_class_total['docs_write_time'])['locale'];
    }

    foreach ($category_totals as $_key => $_category_total) {
        $_category_total["docs_read_time"]  = $_category_total["count"] ?
            $_category_total["docs_read_time"] / $_category_total["count"] : 0;
        $_category_total["docs_write_time"] = $_category_total["count"] ?
            $_category_total["docs_write_time"] / $_category_total["count"] : 0;

        $category_totals[$_key]['docs_read_time']  = CMbDT::friendlyDuration(
            $_category_total['docs_read_time']
        )['locale'];
        $category_totals[$_key]['docs_write_time'] = CMbDT::friendlyDuration(
            $_category_total['docs_write_time']
        )['locale'];
    }
}

// All categories
$category   = new CFilesCategory();
$categories = $category->loadAll(array_keys($category_totals));

// All classes
$classes = array_keys($class_totals);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("doc", $doc);
$smarty->assign("owner", $owner);
$smarty->assign("owner_guid", $owner_guid);
$smarty->assign("user_details", $user_details);
$smarty->assign("class_totals", $class_totals);
$smarty->assign("category_totals", $category_totals);
$smarty->assign("big_totals", $big_totals);
$smarty->assign("categories", $categories);
$smarty->assign("classes", $classes);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("is_doc", $is_doc);
$smarty->assign("factory", $factory);
$smarty->display("stats_details");
