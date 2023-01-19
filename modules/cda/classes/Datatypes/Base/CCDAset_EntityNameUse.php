<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use Ox\Interop\Cda\Datatypes\CCDA_Datatype_Set;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityNameUse;

/**
 * CCDAset_EntityNameUse Class
 */
class CCDAset_EntityNameUse extends CCDA_Datatype_Set {

  /**
   * ADD a class
   *
   * @param String $data String
   *
   * @return void
   */
  function addData($data) {
    $ent = new CCDAEntityNameUse();
    $ent->setData($data);
    $this->listData[] = $ent;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["listData"] = "CCDAEntityNameUse xml|data";
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
     * Test avec un EntityNameUse incorrecte
     */

    $this->addData("TESTTEST");
    $tabTest[] = $this->sample("Test avec un EntityNameUse incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un EntityNameUse correcte
     */

    $this->resetListData();
    $this->addData("C");
    $tabTest[] = $this->sample("Test avec un EntityNameUse correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/
    /**
     * Test avec deux EntityNameUse correcte
     */

    $this->addData("I");
    $tabTest[] = $this->sample("Test avec deux EntityNameUse correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
