<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Dispensation\CProductDeliveryTrace;
use Ox\Mediboard\Pharmacie\CStockSejour;
use Ox\Mediboard\Stock\CStockMouvement;

CCanDo::checkRead();
$service_id   = CView::get('service_id', "ref class|CService", true);
$date_min     = CView::get('_date_min', 'dateTime', true);
$date_max     = CView::get('_date_max', 'dateTime', true);
$stock_mvt_id = CView::get('stock_mvt_id', 'ref class|CStockMouvement');
$source_class = CView::get('source_class', 'enum list|all|CProductStockGroup|CProductStockService default|all');
$etat         = CView::get('etat', 'enum list|en_cours|realise default|en_cours');
$order_way    = CView::get('order_way', 'enum list|ASC|DESC default|ASC');
$order_col    = CView::get('order_col', 'str');
CView::checkin();

$mvt_stock = new CStockMouvement();
$ds        = $mvt_stock->getDS();
$mvt_stock->load($stock_mvt_id);

if (!$stock_mvt_id) {
    $ljoin = [];
    $where = [];
    //Récupération des mouvements séjours étant affectés dans le service
    $ljoin["stock_sejour"]           = "stock_sejour.stock_sejour_id = product_stock_mouvement.cible_id
                              AND product_stock_mouvement.cible_class = 'CStockSejour'";
    $ljoin["sejour"]                 = "sejour.sejour_id = stock_sejour.sejour_id";
    $ljoin["affectation"]            = "sejour.sejour_id = affectation.sejour_id";
    $where[]                         = "product_stock_mouvement.datetime <= '$date_max'";
    $where[]                         = "product_stock_mouvement.datetime >= '$date_min'";
    $where["affectation.service_id"] = " = '$service_id'";
    $where["affectation.entree"]     = " <= '$date_max'";
    $where["affectation.sortie"]     = " >= '$date_min'";

    $where["product_stock_mouvement.etat"] = $ds->prepare("= ?", $etat);
    if ($source_class !== "all") {
        $where["product_stock_mouvement.source_class"] = $ds->prepare("= ?", $source_class);
    }

    $mvts_stock = $mvt_stock->loadList($where, "datetime", null, "product_stock_mouvement.stock_mvt_id", $ljoin);

    $ljoin = [];
    $where = [];
    //Récupération des mouvements séjours étant affectés dans le service
    $ljoin["product_stock_service"]           = "product_stock_service.stock_id = product_stock_mouvement.cible_id
                              AND product_stock_mouvement.cible_class = 'CProductStockService'";
    $where["product_stock_service.object_id"] = " = '$service_id'";
    $where["product_stock_mouvement.etat"] = $ds->prepare("= ?", $etat);
    if ($source_class !== "all") {
        $where["product_stock_mouvement.source_class"] = $ds->prepare("= ?", $source_class);
    }
    $where[]                                  = "product_stock_mouvement.datetime <= '$date_max'";
    $where[]                                  = "product_stock_mouvement.datetime >= '$date_min'";
    $mvts_stock_service                       = $mvt_stock->loadList(
        $where,
        "datetime",
        null,
        "product_stock_mouvement.stock_mvt_id",
        $ljoin
    );

    $ljoin = [];
    $where = [];
    //Récupération des mouvements séjours étant affectés dans le service
    $ljoin["product_stock_service"]           = "product_stock_service.stock_id = product_stock_mouvement.source_id
                                AND product_stock_mouvement.source_class = 'CProductStockService'";
    $where["product_stock_service.object_id"] = " = '$service_id'";
    $where[]                                  = "product_stock_mouvement.datetime <= '$date_max'";
    $where[]                                  = "product_stock_mouvement.datetime >= '$date_min'";
    $where["product_stock_mouvement.etat"]    = $ds->prepare("= ?", $etat);
    $mvts_stock_service2                       = $mvt_stock->loadList(
      $where,
      "datetime",
      null,
      "product_stock_mouvement.stock_mvt_id",
      $ljoin
    );

    $mvts_stock = array_merge($mvts_stock, $mvts_stock_service);
    if ($source_class !== "CProductStockGroup") {
        $mvts_stock = array_merge($mvts_stock, $mvts_stock_service2);
    }

    $type_sort    = $order_way == "ASC" ? SORT_ASC : SORT_DESC;
    CMbArray::pluckSort($mvts_stock, $type_sort, 'cible_id');
    $commenaire_dispensation = [];

    /* @var CStockMouvement[] $mvts_stock */
    foreach ($mvts_stock as $_mvt_stock) {
        $_mvt_stock->loadRefProduit();
        $_mvt_stock->loadAdministrationRefPatient("list_mvts");
        $cible  = $_mvt_stock->loadRefCible();
        $source = $_mvt_stock->loadRefSource();
        if ($cible instanceof CStockSejour) {
            $cible->loadRefSejour()->loadRefPatient();
            //Commentaire dispensation
            $where                   = [];
            $where["quantity"]       = "= '" . $_mvt_stock->quantite . "'";
            $where["date_delivery"]  = "= '" . $_mvt_stock->datetime . "'";
            $product_delivery_trace  = new CProductDeliveryTrace();
            $products_delivery_trace = $product_delivery_trace->loadList($where);

            foreach ($products_delivery_trace as $_product_trace) {
                $product = $_product_trace->loadRefDelivery();
                if ($product->patient_id == $cible->_ref_sejour->patient_id && $product->sejour_id == $cible->sejour_id) {
                    $commenaire_dispensation[$_mvt_stock->_id] = $product->comments_deliver;
                }
            }
        }
        if ($source instanceof CStockSejour) {
            $source->loadRefSejour()->loadRefPatient();
        }
    }
} else {
    $mvt_stock->loadRefProduit();
    $mvt_stock->loadAdministrationRefPatient("list_mvts");
    $cible                   = $mvt_stock->loadRefCible();
    $source                  = $mvt_stock->loadRefSource();
    $commenaire_dispensation = "";
    if ($cible instanceof CStockSejour) {
        $cible->loadRefSejour()->loadRefPatient();
        //Commentaire dispensation
        $where                   = [];
        $where["quantity"]       = "= '" . $mvt_stock->quantite . "'";
        $where["date_delivery"]  = "= '" . $mvt_stock->datetime . "'";
        $product_delivery_trace  = new CProductDeliveryTrace();
        $products_delivery_trace = $product_delivery_trace->loadList($where);

        foreach ($products_delivery_trace as $_product_trace) {
            $product = $_product_trace->loadRefDelivery();
            if ($product->patient_id == $cible->_ref_sejour->patient_id && $product->sejour_id == $cible->sejour_id) {
                $commenaire_dispensation = $product->comments_deliver;
            }
        }
    }
    if ($source instanceof CStockSejour) {
        $source->loadRefSejour()->loadRefPatient();
    }
}

$smarty = new CSmartyDP();
if (!$stock_mvt_id) {
    $smarty->assign("mvts_stock", $mvts_stock);
    $smarty->assign("order_way", $order_way);
    $smarty->assign("order_col", $order_col);
    $smarty->assign("commentaire_dispensation", $commenaire_dispensation);
    $smarty->display("vw_mouvements_list");
} else {
    $smarty->assign("_mvt_stock", $mvt_stock);
    $smarty->assign("order_way", $order_way);
    $smarty->assign("order_col", $order_col);
    $smarty->assign("commentaire_dispensation", $commenaire_dispensation);
    $smarty->display("vw_mouvement_stock");
}
