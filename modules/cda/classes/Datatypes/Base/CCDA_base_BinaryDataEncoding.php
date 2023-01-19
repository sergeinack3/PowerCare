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
 * CCDA_base_BinaryDataEncoding class
 */
class CCDA_base_BinaryDataEncoding extends CCDA_Datatype_Base {

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    parent::getProps();
    $props["data"] = "str xml|data enum|B64|TXT";
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

    $this->setData("B64");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur incorrecte
     */

    $this->setData(" ");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
