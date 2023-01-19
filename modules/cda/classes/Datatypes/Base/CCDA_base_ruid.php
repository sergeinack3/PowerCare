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
 * HL7 reserved identifiers are strings consisting only of
 * (US-ASCII) letters, digits and hyphens, where the first
 * character must be a letter. HL7 may assign these reserved
 * identifiers as mnemonic identifiers for major concepts of
 * interest to HL7.
 */
class CCDA_base_ruid extends CCDA_Datatype_Base {

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["data"] = "str xml|data pattern|[A-Za-z][A-Za-z0-9\\-]*";
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

    $this->setData("HL7");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur incorrecte
     */

    $this->setData("4TESTTEST");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
