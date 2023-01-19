<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Import\CMbObjectExport;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductCategory;

CCanDo::checkEdit();

$category_id = CValue::get("category_id");

$category = new CProductCategory();
$category->load($category_id);

CStoredObject::$useObjectCache = false;

$backrefs_tree = array(
  "CProductCategory"     => array(
    "products",
  ),
  "CProduct"             => array(
    'references',
    'stocks_group',
    'stocks_service',
  ),
  "CProductReference"    => array(// None
  ),
  "CProductStockGroup"   => array(// None
  ),
  "CProductStockService" => array(// None
  ),
);

$fwdrefs_tree = array(
  "CProduct"              => array(
    "category_id",
    "societe_id",
  ),
  "CProductReference"     => array(
    "product_id",
    "societe_id",
  ),
  "CProductStockGroup"    => array(
    "product_id",
    "group_id",
    "location_id",
  ),
  "CProductStockService"  => array(
    "product_id",
    "object_id",
  ),
  "CProductStockLocation" => array(
    "group_id",
    "object_id",
  ),
);

$export               = new CMbObjectExport($category, $backrefs_tree);
$export->empty_values = false;
$export->setForwardRefsTree($fwdrefs_tree);

$export->streamXML();
