<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

/**
 * CCDA_en_prefix class
 */
class CCDA_en_prefix extends CCDAENXP {

  private $XMLName = "en.prefix";

  /**
   * Return the name of the class
   *
   * @return string
   */
  function getNameClass() {
    return $this->XMLName;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["partType"] = "CCDAEntityNamePartType xml|attribute fixe|PFX";
    return $props;
  }

  /**
   * Fonction permettant de tester la validit� de la classe
   *
   * @return array()
   */
  function test() {

    $tabTest = parent::test();


    /**
     * Test avec le parttype correcte
     */

    $tabTest[] = $this->sample("Test avec le parttype fixe correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
