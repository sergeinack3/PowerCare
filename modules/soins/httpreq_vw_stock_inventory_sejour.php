<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Medicament\CProduitLivretTherapeutique;
use Ox\Mediboard\Pharmacie\CStockSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\Stock\CProduct;

CCanDo::checkRead();
$sejour_id = CView::get('sejour_id', "ref class|CSejour", true);
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);
$sejour->loadRefPatient();
$sejour->loadRefsStocksSejour();

$stocks_sejour_by_cis = array();
$presriptions_by_cis = array();
$use_validation_mvt = CAppUI::gconf("dPstock CProductStockGroup use_validation_mvt");
$use_dispentation_ucd  = CAppUI::gconf("dispensation general use_dispentation_ucd");

foreach ($sejour->_ref_stock_sejour as $_stock_sejour) {
  if (($_stock_sejour->quantite_reelle <= 0 && !$use_validation_mvt) || !$_stock_sejour->code_cip) {
    unset($sejour->_ref_stock_sejour[$_stock_sejour->_id]);
    continue;
  }
  $_stock_sejour->loadRefLibelle();
  $key_stock_sejour = $use_dispentation_ucd ? $_stock_sejour->code_cis : $_stock_sejour->code_cip;

  // Si on est en ucd, alors cumul des quantités réelles des différents produits en stock
  $qte_reelle_stock = isset($stocks_sejour_by_cis[(int)$_stock_sejour->code_cis][$key_stock_sejour]) ?
    $stocks_sejour_by_cis[(int)$_stock_sejour->code_cis][$key_stock_sejour]->quantite_reelle : 0;

  $stocks_sejour_by_cis[(int)$_stock_sejour->code_cis][$key_stock_sejour] = $_stock_sejour;
  $stocks_sejour_by_cis[(int)$_stock_sejour->code_cis][$key_stock_sejour]->quantite_reelle += $qte_reelle_stock;
  $_stock_sejour->loadRefProduct();
  $_stock_sejour->_ref_product->ucd_view = $_stock_sejour->_ref_product->_view;
  $presriptions_by_cis[$_stock_sejour->bdm."-".(int)$_stock_sejour->code_cis] = $_stock_sejour;
}

/* @var CPrescription $prescription */
$prescription = $sejour->loadRefPrescriptionSejour();
$prescription->loadRefsLinesMed();
$prescription->loadRefsPrescriptionLineMixes();

foreach ($prescription->_ref_prescription_lines as $_line_med) {
  /* @var CPrescriptionLineMedicament $_line_med */
  if (!isset($presriptions_by_cis[$_line_med->code_cis])) {
    $presriptions_by_cis["$_line_med->bdm-$_line_med->code_cis"] = $_line_med;
  }
}
foreach ($prescription->_ref_prescription_line_mixes as $_line_mix) {
  /* @var CPrescriptionLineMix $_line_mix */
  $_line_mix->loadRefsLines();
  foreach ($_line_mix->_ref_lines as $_line_mix_item) {
    if (!isset($presriptions_by_cis[$_line_mix_item->code_cis])) {
      $presriptions_by_cis["$_line_mix_item->bdm-$_line_mix_item->code_cis"] = $_line_mix_item;
    }
  }
}

$articles_by_cis = $produits_by_cis = array();
foreach ($presriptions_by_cis as $key_code_cis => $produit) {
  list($bdm, $code_cis) = explode("-", $key_code_cis);
  $articles_by_cis[$code_cis] = CProduitLivretTherapeutique::countArticlesFromCIS($code_cis);
  //Liste des articles à créer si besoin
  if (!$articles_by_cis[$code_cis]) {
    list($codes_cip, $produits) = CStockSejour::getProductsInLivret($code_cis);
    foreach ($codes_cip as $_code_cip) {
      $_product_stock       = new CProduct();
      $_product_stock->code = $_code_cip;
      $_product_stock->loadMatchingObject();
      $_product_stock->loadRefStock();
      if ($_product_stock->_id && $_product_stock->_ref_stock_group->_id) {
        $articles_by_cis[$code_cis] = 1;
      }
      else {
        $produits_by_cis[$code_cis][$_code_cip] = CMedicamentArticle::get($_code_cip);
      }
    }
  }

  $presriptions_by_cis[$key_code_cis] = $produit instanceof CStockSejour ? $produit->_ref_product : $produit->loadRefProduit();
}

$order_view = CMbArray::pluck($presriptions_by_cis, "ucd_view");
array_multisort($order_view, SORT_ASC, $presriptions_by_cis);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('sejour', $sejour);
$smarty->assign('presriptions_by_cis', $presriptions_by_cis);
$smarty->assign('stocks_sejour_by_cis', $stocks_sejour_by_cis);
$smarty->assign('articles_by_cis', $articles_by_cis);
$smarty->assign('produits_by_cis', $produits_by_cis);

$smarty->display('inc_stock_inventory_sejour');
