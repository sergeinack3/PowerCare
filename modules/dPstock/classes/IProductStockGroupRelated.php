<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

/**
 * Product stock group related
 */
interface IProductStockGroupRelated {
  /**
   * Get related product stock group
   *
   * @return CProductStockGroup
   */
  function loadRelProductStockGroup();
}
