<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Set;
use Ox\Interop\Cda\Datatypes\Voc\CCDATelecommunicationAddressUse;

/**
 * CCDAset_TelecommunicationAddressUse Class
 */
class CCDAset_TelecommunicationAddressUse extends CCDA_Datatype_Set {

  /**
   * ADD a class
   *
   * @param String $data String
   *
   * @return void
   */
  function addData($data) {
    $tel = new CCDATelecommunicationAddressUse();
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
    $props["listData"] = "CCDATelecommunicationAddressUse xml|data";
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
     * Test avec un TelecommunicationAddressUse incorrecte
     */

    $this->addData("TESTTEST");
    $tabTest[] = $this->sample("Test avec un TelecommunicationAddressUse incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un TelecommunicationAddressUse correcte
     */

    $this->resetListData();
    $this->addData("AS");
    $tabTest[] = $this->sample("Test avec un TelecommunicationAddressUse correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/
    /**
     * Test avec deux TelecommunicationAddressUse correcte
     */

    $this->addData("BAD");
    $tabTest[] = $this->sample("Test avec deux TelecommunicationAddressUse correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
