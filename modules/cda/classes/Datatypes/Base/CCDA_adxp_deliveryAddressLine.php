<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * CCDA_adxp_deliveryAddressLine class
 */
class CCDA_adxp_deliveryAddressLine extends CCDAADXP {

  private $XMLName = "adxp.deliveryAddressLine";

  /**
   * Return the name of the class
   *
   * @return string
   */
  function getNameClass() {
    return $this->XMLName;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["partType"] = "CCDAAddressPartType xml|attribute fixe|DAL";
    return $props;
  }

  /**
   * Fonction permettant de tester la validité de la classe
   *
   * @return array()
   */
  function test() {

    $tabTest = parent::test();

    /**
     * Test avec le parttype correcte
     */

    $tabTest[] = $this->sample("Test avec le parttype fixe correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
