<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Base;

/**
 * Integer numbers (-1,0,1,2, 100, 3398129, etc.) are precise
 * numbers that are results of counting and enumerating.
 * Integer numbers are discrete, the set of integers is
 * infinite but countable.  No arbitrary limit is imposed on
 * the range of integer numbers. Two NULL flavors are
 * defined for the positive and negative infinity.
 */
class CCDA_base_int extends CCDA_Datatype_Base {

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "integer xml|data";
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
     * Test avec data incorrecte
     */
    $this->setData("10.25");
    $tabTest[] = $this->sample("Test avec un data incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec data correcte
     */
    $this->setData("10");
    $tabTest[] = $this->sample("Test avec un data correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
