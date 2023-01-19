<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Base;

/**
 * Coded data in its simplest form, consists of a code.
 * The code system and code system version is fixed by
 * the context in which the CS value occurs. CS is used
 * for coded attributes that have a single HL7-defined
 * value set.
 */
class CCDA_base_cs extends CCDA_Datatype_Base {

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = array();
    $props["data"] = "str xml|data pattern|[^\\s]+";
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
     * Test avec un valeur incorrecte
     */

    $this->setData(" ");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    if (CClassMap::getSN($this) !== "CCDA_base_cs") {
      return $tabTest;
    }

    /**
     * Test avec un valeur correcte
     */

    $this->setData("test");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
