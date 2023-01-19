<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Voc\CCDAContextControl;
use Ox\Interop\Cda\Datatypes\Voc\CCDAParticipationTargetSubject;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_Subject Class
 */
class CCDAPOCD_MT000040_Subject extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_RelatedSubject
   */
  public $relatedSubject;

  /**
   * Setter awarenessCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setAwarenessCode(CCDACE $inst) {
    $this->awarenessCode = $inst;
  }

  /**
   * Getter awarenessCode
   *
   * @return CCDACE
   */
  function getAwarenessCode() {
    return $this->awarenessCode;
  }

  /**
   * Setter relatedSubject
   *
   * @param CCDAPOCD_MT000040_RelatedSubject $inst CCDAPOCD_MT000040_RelatedSubject
   *
   * @return void
   */
  function setRelatedSubject(CCDAPOCD_MT000040_RelatedSubject $inst) {
    $this->relatedSubject = $inst;
  }

  /**
   * Getter relatedSubject
   *
   * @return CCDAPOCD_MT000040_RelatedSubject
   */
  function getRelatedSubject() {
    return $this->relatedSubject;
  }

  /**
   * Assigne typeCode à SBJ
   *
   * @return void
   */
  function setTypeCode() {
    $parttarget = new CCDAParticipationTargetSubject();
    $parttarget->setData("SBJ");
    $this->typeCode = $parttarget;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAParticipationTargetSubject
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
    $props["awarenessCode"]      = "CCDACE xml|element max|1";
    $props["relatedSubject"]     = "CCDAPOCD_MT000040_RelatedSubject xml|element required";
    $props["typeCode"]           = "CCDAParticipationTargetSubject xml|attribute fixed|SBJ";
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
     * Test avec un relatedSubject correct
     */

    $relatedSub = new CCDAPOCD_MT000040_RelatedSubject();
    $relatedSub->setTypeId();
    $this->setRelatedSubject($relatedSub);
    $tabTest[] = $this->sample("Test avec un relatedSubject correct", "Document valide");

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
     * Test avec un awarenessCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setAwarenessCode($ce);
    $tabTest[] = $this->sample("Test avec un contextControlCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un awarenessCode correct
     */

    $ce->setCode("TEST");
    $this->setAwarenessCode($ce);
    $tabTest[] = $this->sample("Test avec un contextControlCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/


    return $tabTest;
  }
}