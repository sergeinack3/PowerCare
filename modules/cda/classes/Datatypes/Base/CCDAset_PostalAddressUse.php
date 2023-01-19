<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Set;
use Ox\Interop\Cda\Datatypes\Voc\CCDAPostalAddressUse;

/**
 * CCDAset_PostalAddressUse Class
 */
class CCDAset_PostalAddressUse extends CCDA_Datatype_Set {

  /**
   * ADD a class
   *
   * @param String $data String
   *
   * @return void
   */
  function addData($data) {
    $tel = new CCDAPostalAddressUse();
    $tel->setData($data);
    $this->listData[] = $tel;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["listData"] = "CCDAPostalAddressUse xml|data";
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
     * Test avec un PostalAddressUse incorrecte
     */

    $this->addData("TESTTEST");
    $tabTest[] = $this->sample("Test avec un PostalAddressUse incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un PostalAddressUse correcte
     */

    $this->resetListData();
    $this->addData("PST");
    $tabTest[] = $this->sample("Test avec un PostalAddressUse correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/
    /**
     * Test avec deux PostalAddressUse correcte
     */

    $this->addData("TMP");
    $tabTest[] = $this->sample("Test avec deux PostalAddressUse correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
