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
 * A DCE Universal Unique Identifier is a globally unique
 * string consisting of 5 groups of upper- or lower-case
 * hexadecimal digits having 8, 4, 4, 4, and 12 places
 * respectively. UUIDs are assigned using Ethernet MAC
 * addresses, the point in time of creation and some random
 * components. This mix is believed to generate sufficiently
 * unique identifiers without any organizational policy for
 * identifier assignment (in fact this piggy-backs on the
 * organization of MAC address assignment.)
 */
class CCDA_base_uuid extends CCDA_Datatype_Base {

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["data"] = "str xml|data pattern|[0-9a-zA-Z]{8}-[0-9a-zA-Z]{4}-[0-9a-zA-Z]{4}-[0-9a-zA-Z]{4}-[0-9a-zA-Z]{12}";
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

    $this->setData("azer1254-azer-azer-azer-Azert1257825");
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
