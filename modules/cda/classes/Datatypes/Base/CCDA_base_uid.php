<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\CCDAClasseBase;
use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Base;

/**
 * A unique identifier string is a character string which
 * identifies an object in a globally unique and timeless
 * manner. The allowable formats and values and procedures
 * of this data type are strictly controlled by HL7. At this
 * time, user-assigned identifiers may be certain character
 * representations of ISO Object Identifiers (OID) and DCE
 * Universally Unique Identifiers (UUID). HL7 also reserves
 * the right to assign other forms of UIDs, such as mnemonic
 * identifiers for code systems.
 */
class CCDA_base_uid extends CCDA_Datatype_Base {

  public $union = array("oid", "uuid", "ruid");

  /**
   * Récupère les valeurs présent dans les autres classes présent
   * dans les unions
   *
   * @return string
   */
  function getPropsUnion() {
    $pattern = "";
    foreach ($this->union as $_union) {
      $_union = "CCDA_base_".$_union;
      /** @var CCDAClasseBase $class */
      $class = new $_union;
      $spec = $class->getSpecs();
      $pattern .= $spec["data"]["pattern"];
    }
    return $pattern;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    $props["data"] = "str xml|data pattern|".$this->getPropsUnion();
    return $props;
  }

  /**
   * Fonction qui permet de tester si la classe fonctionne
   *
   * @return array
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
