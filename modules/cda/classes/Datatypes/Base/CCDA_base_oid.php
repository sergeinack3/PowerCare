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
 * A globally unique string representing an ISO Object Identifier
 * (OID) in a form that consists only of non-negative numbers with
 * no leading zeros and dots (e.g., "2.16.840.1.113883.3.1").
 * According to ISO, OIDs are paths in a tree structure, with the
 * left-most number representing the root and the right-most number
 * representing a leaf.
 */
class CCDA_base_oid extends CCDA_Datatype_Base {

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["data"] = "str xml|data pattern|[0-2](\\.(0|[1-9][0-9]*))*";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array()
   */
  function test() {
    $tabTest = parent::test();

    /**
     * Test avec une valeur correcte
     */

    $this->setData("2.16.840.1.113883.3.1");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur incorrecte
     */

    $this->setData("TESTTEST");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
