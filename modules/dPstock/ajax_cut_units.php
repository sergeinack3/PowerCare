<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Bcb\CBcbObject;
use Ox\Mediboard\Bcb\CBcbProduit;
use Ox\Mediboard\Medicament\CMedicamentArticle;
use Ox\Mediboard\Stock\CProduct;
use Ox\Mediboard\Stock\CProductStockGroup;
use Ox\Mediboard\Stock\CProductStockService;

CApp::setTimeLimit(2000);

$classes = array(
  CProduct::class,
);

$distincts_stocks = array(
  "CProductStockGroup",
  "CProductStockService"
);

$ds     = CSQLDataSource::get("std");
$ds_bcb = CBcbObject::getDataSource();

foreach ($classes as $_classe) {
  $obj = new $_classe;

  switch ($_classe) {
    case CProduct::class:
      $table = $obj->_spec->table;

      $request = new CRequest();
      $request->addSelect("$table.product_id, $table.code, $table.item_title, $table.unit_title, $table.unit_quantity");
      $request->addTable($table);
      /*$request->addWhere(
        array(
          "$table.code_up_disp" => "IS NULL",
        )
      );*/

      $products = $ds->loadList($request->makeSelect());

      $count = 0;

      foreach ($products as $_product) {
        $query = null;

        $code_cip         = $_product["code"];
        $product_id       = $_product["product_id"];
        $unite_delivrance = $_product["item_title"];
        $unite_adm        = $_product["unit_title"];


        // Mise à jour du produit
        $request = new CRequest();
        $request->addSelect("*");
        $request->addTable("IDENT_PRODUITS");
        $request->addLJoin(
          array(
            "IDENT_PRESENTATIONS"          =>
              "IDENT_PRESENTATIONS.CODE_PRESENTATION = IDENT_PRODUITS.CODE_PRESENTATION",
            "IDENT_UNITES_DE_PRESENTATION" =>
              "IDENT_UNITES_DE_PRESENTATION.CODE_UNITE_DE_PRESENTATION = IDENT_PRODUITS.CODE_UNITE_DE_PRESENTATION"
          )
        );
        $request->addWhere(
          array(
            "IDENT_PRODUITS.CODE_CIP" => "= '$code_cip'"
          )
        );

        $result = $ds_bcb->loadHash($request->makeSelect());

        $nb_presentation               = $result["NB_PRESENTATION"];
        $libelle_presentation          = $result["LIBELLE_PRESENTATION"];
        $nb_unite_de_presentation      = $result["NB_UNITE_DE_PRESENTATION"];
        $libelle_unite_de_presentation = $result["LIBELLE_UNITE_DE_PRESENTATION_PLURIEL"];

        $code_up_ucd = $result["CODE_UP_UCD"];
        $nombre_ucd  = $result["NOMBRE_UCD"];

        $code_up_ucd_libelle = CBcbProduit::getLibelleUniteUP($code_up_ucd);

        // On vide la partie composition si nécessaire (par exemple si on passe de plaquettes à gélules)
        $update_qte_unite_adm = null;
        if (preg_match("/" . preg_quote($code_up_ucd_libelle) . "/i", $libelle_unite_de_presentation)) {
          $update_qte_unite_adm = ", unit_quantity = NULL, unit_title = NULL";
        }

        // Mise à jour de l'unité d'administration (si inchangée par rapport à la bdm ou alors elle est à null)
        $update_code_up_adm = null;

        $request = new CRequest();
        $request->addSelect("IDENT_PRODUITS.CODE_UNITE_DE_PRESENTATION, LIBELLE_UNITE_DE_PRESENTATION, LIBELLE_UNITE_DE_PRESENTATION_PLURIEL");
        $request->addTable("IDENT_PRODUITS");
        $request->addLJoin(
          array(
            "IDENT_UNITES_DE_PRESENTATION" => "IDENT_UNITES_DE_PRESENTATION.CODE_UNITE_DE_PRESENTATION = IDENT_PRODUITS.CODE_UNITE_DE_PRESENTATION"
          )
        );
        $request->addWhere(
          array(
            "CODE_CIP" => "= '$code_cip'"
          )
        );

        $unite = $ds_bcb->loadHash($request->makeSelect());

        if (!$unite_adm || $unite["LIBELLE_UNITE_DE_PRESENTATION"] == $unite_adm || $unite["LIBELLE_UNITE_DE_PRESENTATION_PLURIEL"] == $unite_adm) {
          $code_up_adm        = $unite["CODE_UNITE_DE_PRESENTATION"];
          $update_code_up_adm = ", code_up_adm = '$code_up_adm', unit_title = '$unite_adm'";
        }

        $query = "UPDATE $table
          SET code_up_disp = '$code_up_ucd',
              item_title = '$code_up_ucd_libelle',
              quantity = '$nombre_ucd'
              $update_qte_unite_adm
              $update_code_up_adm
          WHERE code = '$code_cip';";

        $ds->exec($query);

        // Mise à jour des délivrances et des stocks si nécessaire
        if (!$unite_delivrance || preg_match("/" . preg_quote($code_up_ucd_libelle) . "/", $unite_delivrance)) {
          $count++;
          continue;
        }

        $denominateur = null;

        preg_match("/([^ -]+)/", $libelle_presentation, $matches);
        if (isset($matches[0])) {
          $libelle_presentation = $matches[0];
        }

        preg_match("/([^ -]+)/", $libelle_unite_de_presentation, $matches);
        if (isset($matches[0])) {
          $libelle_unite_de_presentation = $matches[0];
        }

        preg_match("/([^ -]+)/", $unite_delivrance, $matches);
        if (isset($matches[0])) {
          $unite_delivrance = $matches[0];
        }

        if ($libelle_presentation && $unite_delivrance == $libelle_presentation) {
          $denominateur = $nb_presentation;
        }
        elseif ($libelle_unite_de_presentation && $unite_delivrance == $libelle_unite_de_presentation) {
          $denominateur = $nb_unite_de_presentation;
        }

        if ($denominateur) {
          $count++;

          foreach ($distincts_stocks as $_classe) {
            $stock = new $_classe;

            $table_stock = $stock->_spec->table;

            // Délivrances
            $query = "UPDATE product_delivery
              LEFT JOIN $table_stock ON $table_stock.stock_id = product_delivery.stock_id AND product_delivery.stock_class = '$_classe'
              SET product_delivery.quantity = (product_delivery.quantity * $nombre_ucd / $denominateur)
              WHERE product_id = '$product_id'";
            $ds->exec($query);

            // Traces de délivrance
            $query = "UPDATE product_delivery_trace
              LEFT JOIN product_delivery ON product_delivery.delivery_id = product_delivery_trace.delivery_id
              LEFT JOIN $table_stock ON $table_stock.stock_id = product_delivery.stock_id AND product_delivery.stock_class = '$_classe'
              SET product_delivery_trace.quantity = (product_delivery_trace.quantity * $nombre_ucd / $denominateur)
              WHERE product_id = '$product_id'";
            $ds->exec($query);

            // Stock
            $query = "UPDATE $table_stock
              SET quantity = (quantity * $nombre_ucd / $denominateur)
              WHERE product_id = '$product_id'";
            $ds->exec($query);
          }
        }
        else {
          $produit = CMedicamentArticle::get($code_cip);
            CApp::log($produit->ucd_view);
            CApp::log("unite délivrance", $unite_delivrance);
            CApp::log("libelle presentaion", $libelle_presentation);
            CApp::log("libelle unite presentation", $libelle_unite_de_presentation);
        }
      }

        CApp::log($count . "/" . count($products) . " produits dont l'unite de délivrance est dans les up");

      break;
    default:
  }
}
