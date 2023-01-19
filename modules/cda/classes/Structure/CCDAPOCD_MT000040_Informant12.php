<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAContextControl;
use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationType;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Informant12 Class
 */
class CCDAPOCD_MT000040_Informant12 extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_AssignedEntity
   */
  public $assignedEntity;

  /**
   * @var CCDAPOCD_MT000040_RelatedEntity
   */
  public $relatedEntity;

  /**
   * Setter assignedEntity
   *
   * @param CCDAPOCD_MT000040_AssignedEntity $inst CCDAPOCD_MT000040_AssignedEntity
   *
   * @return void
   */
  function setAssignedEntity(CCDAPOCD_MT000040_AssignedEntity $inst) {
    $this->assignedEntity = $inst;
  }

  /**
   * Getter assignedEntity
   *
   * @return CCDAPOCD_MT000040_AssignedEntity
   */
  function getAssignedEntity() {
    return $this->assignedEntity;
  }

  /**
   * Setter relatedEntity
   *
   * @param CCDAPOCD_MT000040_RelatedEntity $inst CCDAPOCD_MT000040_RelatedEntity
   *
   * @return void
   */
  function setRelatedEntity(CCDAPOCD_MT000040_RelatedEntity $inst) {
    $this->relatedEntity = $inst;
  }

  /**
   * Getter relatedEntity
   *
   * @return CCDAPOCD_MT000040_RelatedEntity
   */
  function getRelatedEntity() {
    return $this->relatedEntity;
  }

  /**
   * Assigne typeCode à INF
   *
   * @return void
   */
  function setTypeCode() {
    $partType = new CCDAParticipationType();
    $partType->setData("INF");
    $this->typeCode = $partType;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAParticipationType
   */
  function getTypeCode() {
    return $this->typeCode;
  }

  /**
   * Assigne contextControlCode à OP
   *
   * @return void
   */
  function setContextControlCode() {
    $context = new CCDAContextControl();
    $context->setData("OP");
    $this->contextControlCode = $context;
  }

  /**
   * Getter contextControlCode
   *
   * @return CCDAContextControl
   */
  function getContextControlCode() {
    return $this->contextControlCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]             = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["assignedEntity"]     = "CCDAPOCD_MT000040_AssignedEntity xml|element required";
    $props["relatedEntity"]      = "CCDAPOCD_MT000040_RelatedEntity xml|element required";
    $props["typeCode"]           = "CCDAParticipationType xml|attribute fixed|INF";
    $props["contextControlCode"] = "CCDAContextControl xml|attribute fixed|OP";
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
     * Test avec un assignedEntity correct
     */

    $assigned = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assigned->appendId($ii);
    $this->setAssignedEntity($assigned);
    $tabTest[] = $this->sample("Test avec un assignedEntity correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/
    /**
     * Test avec un contextControlCode correct
     */

    $this->setContextControlCode();
    $tabTest[] = $this->sample("Test avec un contextControlCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un relatedEntity correct
     */

    $related = new CCDAPOCD_MT000040_RelatedEntity();
    $related->setClassCode("PRS");
    $this->setRelatedEntity($related);
    $tabTest[] = $this->sample("Test avec un relatedEntity correct, séquence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un relatedEntity correct
     */

    $this->assignedEntity = null;
    $tabTest[] = $this->sample("Test avec un relatedEntity correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}