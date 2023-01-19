<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stock;

/**
 * Societe
 */
interface IProductRelated {
  /**
   * Get related product
   *
   * @return CProduct
   */
  function loadRelProduct();
}
