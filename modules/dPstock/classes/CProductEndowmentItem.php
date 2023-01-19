<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

use Ox\Core\Cache;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;

/**
 * Product Endowment Item
 */
class CProductEndowmentItem extends CMbObject {
  public $endowment_item_id;

  public $quantity;
  public $endowment_id;
  public $product_id;
  public $cancelled;

  /** @var CProductEndowment */
  public $_ref_endowment;

  /** @var CProduct */
  public $_ref_product;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec                    = parent::getSpec();
    $spec->table             = 'product_endowment_item';
    $spec->key               = 'endowment_item_id';
    $spec->uniques["unique"] = array("endowment_id", "product_id");

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $specs                 = parent::getProps();
    $specs['quantity']     = 'num notNull min|0';
    $specs['endowment_id'] = 'ref notNull class|CProductEndowment autocomplete|name back|endowment_items';
    $specs['product_id']   = 'ref notNull class|CProduct autocomplete|name dependsOn|cancelled seekable back|endowments';
    $specs['cancelled']    = 'bool notNull default|0';

    return $specs;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->loadRefsFwd();
    $this->_view = "$this->_ref_product x $this->quantity";
  }

  /**
   * @inheritdoc
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->_ref_endowment = $this->loadFwdRef("endowment_id", true);
    $this->_ref_product   = $this->loadFwdRef("product_id", true);
  }

  /**
   * @inheritdoc
   */
  function getPerm($permType) {
    $this->loadRefsFwd();

    return parent::getPerm($permType) &&
      $this->_ref_endowment->getPerm($permType) &&
      $this->_ref_product->getPerm($permType);
  }

  /**
   * Recherche si le produit fait partie des dotations paramétrées
   *
   * @param array $codes_cip  code cip du produit recherché
   * @param int   $service_id service
   *
   * @return int
   */
  static function produitInDotation($codes_cip, $service_id, $bdm) {
    $cache = new Cache('CProductEndowmentItem.produitInDotation', sha1(serialize([$codes_cip, $service_id, $bdm])), Cache::INNER);

    if ($cache->exists()) {
      return $cache->get();
    }

    $dotation = new CProductEndowmentItem();
    $where    = array(
      "product.code"                     => CSQLDataSource::prepareIn($codes_cip),
      "product.bdm"                      => "= '$bdm'",
      "product_endowment.service_id"     => "= '$service_id'",
      "product_endowment.actif"          => "= '1'",
      "product_endowment_item.cancelled" => "!= '1'",
    );
    $ljoin    = array(
      "product_endowment" => "product_endowment.endowment_id = product_endowment_item.endowment_id",
      "product"           => "product_endowment_item.product_id = product.product_id",
    );

    return $cache->put($dotation->countList($where, null, $ljoin));
  }
}
