<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Voc\CCDAActRelationshipType;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_Precondition Class
 */
class CCDAPOCD_MT000040_Precondition extends CCDARIMActRelationship {

  /**
   * @var CCDAPOCD_MT000040_Criterion
   */
  public $criterion;

  /**
   * Setter criterion
   *
   * @param CCDAPOCD_MT000040_Criterion $inst CCDAPOCD_MT000040_Criterion
   *
   * @return void
   */
  function setCriterion(CCDAPOCD_MT000040_Criterion $inst) {
    $this->criterion = $inst;
  }

  /**
   * Getter criterion
   *
   * @return CCDAPOCD_MT000040_Criterion
   */
  function getCriterion() {
    return $this->criterion;
  }

  /**
   * Assigne typeCode à PRCN
   *
   * @return void
   */
  function setTypeCode() {
    $actRela = new CCDAActRelationshipType();
    $actRela->setData("PRCN");
    $this->typeCode = $actRela;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAActRelationshipType
   */
  function getTypeCode() {
    return $this->typeCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"] = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["criterion"] = "CCDAPOCD_MT000040_Criterion xml|element required";
    $props["typeCode"] = "CCDAActRelationshipType xml|attribute fixed|PRCN";
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
     * Test avec un criterion correct
     */

    $crit = new CCDAPOCD_MT000040_Criterion();
    $crit->setMoodCode();
    $this->setCriterion($crit);
    $tabTest[] = $this->sample("Test avec un criterion correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}