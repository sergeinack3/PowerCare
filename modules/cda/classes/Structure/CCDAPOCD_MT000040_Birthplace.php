<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClass;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_Birthplace Class
 */
class CCDAPOCD_MT000040_Birthplace extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Place
   */
  public $place;

  /**
   * Setter place
   *
   * @param CCDAPOCD_MT000040_Place $inst CCDAPOCD_MT000040_Place
   *
   * @return void
   */
  function setPlace($inst) {
    $this->place = $inst;
  }

  /**
   * Getter place
   *
   * @return CCDAPOCD_MT000040_Place
   */
  function getPlace() {
    return $this->place;
  }

  /**
   * Assigne classCode à BIRTHPL
   *
   * @return void
   */
  function setClassCode() {
    $class = new CCDARoleClass();
    $class->setData("BIRTHPL");
    $this->classCode = $class;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClass
   */
  function getClassCode() {
    return $this->classCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]     = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["place"]      = "CCDAPOCD_MT000040_Place xml|element required";
    $props["classCode"]  = "CCDARoleClass xml|attribute fixed|BIRTHPL";
    return $props;
  }

  /**
   * Fonction permettant de tester la classe
   *
   * @return array
   */
  function test() {
    $tabTest = parent::test();

    /**
     * Test avec un place correcte
     */

    $pla = new CCDAPOCD_MT000040_Place();
    $pla->setClassCode();
    $this->setPlace($pla);
    $tabTest[] = $this->sample("Test avec un place correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correcte
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}