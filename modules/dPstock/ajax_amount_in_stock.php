<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Stock\CProduct;
use Ox\Mediboard\Stock\CProductStockGroup;
use Ox\Tamm\Cabinet\TammStockException;

CCanDo::checkRead();

$code_cip    = CView::get("code_cip", "str notNull");
$category_id = CView::get("category_id", "ref class|CProductCategory");
$place_id    = CView::get("location_id", "ref class|CProductStockLocation");
CView::checkin();

try {
    if (!$place_id || !$category_id || !$code_cip) {
        throw new TammStockException("Missing parameters");
    }

    if (strlen($code_cip) == 13) {
        $code = substr($code_cip, 5, -1);
    } elseif (strlen($code_cip) > 13) {
        if (substr($code_cip, 0, 8) == CProduct::QR_PRODUCT) {
            $code = substr($code_cip, 8, 7);
        }
    } else {
        $code = $code_cip;
    }

    $medication = CMedicamentArticle::get($code);
    if (!$medication->code_cis) {
        throw new TammStockException("Wrong CIP code");
    }

    $product              = new CProduct();
    $product->category_id = $category_id;
    $product->code        = $code;
    $product->loadMatchingObjectEsc();

    if (!$product->_id) {
        CApp::json(["code" => $code, "quantity" => 0]);
    }

    $product_stock              = new CProductStockGroup();
    $product_stock->group_id    = CGroups::get()->_id;
    $product_stock->location_id = $place_id;
    $product_stock->product_id  = $product->_id;
    $product_stock->loadMatchingObjectEsc();

    CApp::json(["code" => $code, "quantity" => $product_stock->quantity ?? 0]);
} catch (TammStockException $e) {
    CApp::json(["error" => $e->getMessage()]);
}
