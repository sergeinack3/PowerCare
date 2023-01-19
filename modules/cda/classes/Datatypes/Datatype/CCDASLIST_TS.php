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
use Ox\Interop\Cda\Datatypes\Base\CCDATS;

/**
 * CCDASLIST_TS class
 */
class CCDASLIST_TS extends CCDAANY {

  /**
   * The origin of the list item value scale, i.e., the
   * physical quantity that a zero-digit in the sequence
   * would represent.
   *
   * @var CCDATS
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
   * @param CCDATS $origin \CCDATS
   *
   * @return void
   */
  public function setOrigin($origin) {
    $this->origin = $origin;
  }

  /**
   * Getter origin
   *
   * @return CCDATS
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
    $props["origin"] = "CCDATS xml|element required";
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

    $ori= new CCDATS();
    $ori->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
    $this->setOrigin($ori);
    $tabTest[] = $this->sample("Test avec une origin correcte, s�quence incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un scale correcte
     */

    $sca= new CCDAPQ();
    $sca->setUnit("test");
    $this->setScale($sca);
    $tabTest[] = $this->sample("Test avec un scale correcte, s�quence incorrecte", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un digits correcte
     */

    $this->setDigits(array("10"));
    $tabTest[] = $this->sample("Test avec un digts correcte, s�quence correcte", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
