<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes;

use Ox\Core\CClassMap;

/**
 * Classe dont hériteront les classes set_*
 */
class CCDA_Datatype_Set extends CCDA_Datatype {
  public $listData = array();

  /**
   * Reinitialise la variable
   *
   * @return void
   */
  function resetListData () {
    $this->listData = array();
  }

  /**
   * retourne le nom de la classe
   *
   * @return string
   */
  function getNameClass() {
    $name = CClassMap::getSN($this);
    $name = substr($name, 4);

    return $name;
  }

  /**
   * Getter listData
   *
   * @return array
   */
  public function getData() {
    $listdata = "";
    foreach ($this->listData as $_tel) {
      /** @var CCDA_Datatype $_tel*/
      $listdata .= $_tel->getData()." ";
    }
    $listdata = substr($listdata, 0, strlen($listdata)-1);
    return $listdata;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();

    return $props;
  }

  /**
   * fonction permettant de tester la validité de la classe
   *
   * @return array()
   */
  function test() {
    $tabTest = array();

    /**
     * Test avec les valeurs nulls
     */

    $tabTest[] = $this->sample("Test avec les valeurs null", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
