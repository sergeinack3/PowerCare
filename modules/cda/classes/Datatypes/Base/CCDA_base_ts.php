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
 * A quantity specifying a point on the axis of natural time.
 * A point in time is most often represented as a calendar
 * expression.
 */
class CCDA_base_ts extends CCDA_Datatype_Base {
  
  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["data"] = "str xml|data pattern|[0-9]{1,8}|([0-9]{9,14}|[0-9]{14,14}\\.[0-9]+)([+\\-][0-9]{1,4})?";
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
     * Test avec un use incorrecte
     */

    $this->setData("TESTEST");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/
    /**
     * Test avec une valeur correcte
     */

    $this->setData("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
