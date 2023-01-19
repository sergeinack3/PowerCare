<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Datatypes\Base\CCDA_base_bl;
use Ox\Interop\Cda\Datatypes\Base\CCDA_base_int;

/**
 * POCD_MT000040_RegionOfInterest_value Class
 */
class CCDAPOCD_MT000040_RegionOfInterest_value extends CCDAClasseCda {

  /**
   * @var CCDA_base_bl
   */
  public $unsorted;

  /**
   * @var CCDA_base_int
   */
  public $value;

  /**
   * Setter value
   *
   * @param String $value String
   *
   * @return void
   */
  public function setValue($value) {
    if (!$value) {
      $this->value = null;
      return;
    }
    $int = new CCDA_base_int();
    $int->setData($value);
    $this->value = $int;
  }

  /**
   * Getter value
   *
   * @return CCDA_base_int
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Setter unsorted
   *
   * @param String $inst String
   *
   * @return void
   */
  function setUnsorted($inst) {
    if (!$inst) {
      $this->unsorted = null;
      return;
    }
    $bl = new CCDA_base_bl();
    $bl->setData($inst);
    $this->unsorted = $bl;
  }

  /**
   * Getter unsorted
   *
   * @return CCDA_base_bl
   */
  function getUnsorted() {
    return $this->unsorted;
  }

  /**
   * Retourne le nom de la classe
   *
   * @return String
   */
  function getNameClass() {
    $name = CClassMap::getSN($this);
    $part = substr($name, strpos($name, "_")+1);
    $part = str_replace("_", ".", $part);
    $name = substr_replace($name, $part, strpos($name, "_")+1);
    $name = substr($name, 4);
    return $name;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = array();
    $props["unsorted"] = "CCDA_base_bl xml|attribute default|false";
    $props["value"]    = "CCDA_base_int xml|attribute";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array
   */
  function test() {
    $tabTest = array();

    /**
     * Test avec un unsorted incorrect
     */

    $this->setUnsorted("TEST");
    $tabTest[] = $this->sample("Test avec un unsorted incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un unsorted correct
     */

    $this->setUnsorted("true");
    $tabTest[] = $this->sample("Test avec un unsorted correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur incorrecte
     */

    $this->setValue("10.25");
    $tabTest[] = $this->sample("Test avec une valeur incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une valeur correcte
     */

    $this->setValue("10");
    $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}