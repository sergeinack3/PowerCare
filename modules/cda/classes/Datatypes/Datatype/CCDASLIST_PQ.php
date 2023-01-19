<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Datatype;

use Ox\Core\CClassMap;
use Ox\Interop\Cda\Datatypes\Base\CCDAANY;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;

/**
 * CCDASLIST_PQ class
 */
class CCDASLIST_PQ extends CCDAANY {

  /**
   * The origin of the list item value scale, i.e., the
   * physical quantity that a zero-digit in the sequence
   * would represent.
   *
   * @var CCDAPQ
   */
  public $origin;

  /**
   * A ratio-scale quantity that is factored out of the
   * digit sequence.
   *
   * @var CCDAPQ
   */
  public $scale;

  /**
   * A sequence of raw digits for the sample values. This is
   * typically the raw output of an A/D converter.
   *
   * @var CCDAlist_int
   */
  public $digits;

  /**
   * Setter digits
   *
   * @param String[] $digits String[]
   *
   * @return void
   */
  public function setDigits($digits) {
    $listInt = new CCDAlist_int();
    foreach ($digits as $_digits) {
      $listInt->addData($_digits);
    }
    $this->digits = $listInt;
  }

  /**
   * Getter digits
   *
   * @return CCDAlist_int
   */
  public function getDigits() {
    return $this->digits;
  }

  /**
   * Setter origin
   *
   * @param CCDAPQ $origin \CCDAPQ
   *
   * @return void
   */
  public function setOrigin($origin) {
    $this->origin = $origin;
  }

  /**
   * Getter origin
   *
   * @return CCDAPQ
   */
  public function getOrigin() {
    return $this->origin;
  }

  /**
   * Setter scale
   *
   * @param CCDAPQ $scale \CCDAPQ
   *
   * @return void
   */
  public function setScale($scale) {
    $this->scale = $scale;
  }

  /**
   * Getter scale
   *
   * @return CCDAPQ
   */
  public function getScale() {
    return $this->scale;
  }

  /**
   * retourne le nom du type CDA
   *
   * @return string
   */
  function getNameClass() {
    $name = CClassMap::getSN($this);
    $name = substr($name, 4);

    return $name;
  }

  /**
   * Get the properties of our class as strings
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["origin"] = "CCDAPQ xml|element required";
    $props["scale"] = "CCDAPQ xml|element required";
    $props["digits"] = "CCDAlist_int xml|element required";
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
     * Test avec les valeurs null
     */

    $tabTest[] = $this->sample("Test avec les valeurs null", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une origin correcte
     */

    $ori= new CCDAPQ();
    $ori->setUnit("test");
    $this->setOrigin($ori);
    $tabTest[] = $this->sample("Test avec une origin correcte, séquence incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un scale correcte
     */

    $sca= new CCDAPQ();
    $sca->setUnit("test");
    $this->setScale($sca);
    $tabTest[] = $this->sample("Test avec un scale correcte, séquence incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un digits correcte
     */

    $this->setDigits(array("10"));
    $tabTest[] = $this->sample("Test avec un digts correcte, séquence correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
