<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_ActRelationshipExternalReference;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_Reference Class
 */
class CCDAPOCD_MT000040_Reference extends CCDARIMActRelationship {

  /**
   * @var CCDAPOCD_MT000040_ExternalAct
   */
  public $externalAct;

  /**
   * @var CCDAPOCD_MT000040_ExternalObservation
   */
  public $externalObservation;

  /**
   * @var CCDAPOCD_MT000040_ExternalProcedure
   */
  public $externalProcedure;

  /**
   * @var CCDAPOCD_MT000040_ExternalDocument
   */
  public $externalDocument;

  /**
   * Setter seperatableInd
   *
   * @param CCDABL $inst CCDABL
   *
   * @return void
   */
  function setSeperatableInd(CCDABL $inst) {
    $this->seperatableInd = $inst;
  }

  /**
   * Getter seperatableInd
   *
   * @return CCDABL
   */
  function getSeperatableInd() {
    return $this->seperatableInd;
  }

  /**
   * Setter externalAct
   *
   * @param CCDAPOCD_MT000040_ExternalAct $inst CCDAPOCD_MT000040_ExternalAct
   *
   * @return void
   */
  function setExternalAct(CCDAPOCD_MT000040_ExternalAct $inst) {
    $this->externalAct = $inst;
  }

  /**
   * Getter externalAct
   *
   * @return CCDAPOCD_MT000040_ExternalAct
   */
  function getExternalAct() {
    return $this->externalAct;
  }

  /**
   * Setter externalObservation
   *
   * @param CCDAPOCD_MT000040_ExternalObservation $inst CCDAPOCD_MT000040_ExternalObservation
   *
   * @return void
   */
  function setExternalObservation(CCDAPOCD_MT000040_ExternalObservation $inst) {
    $this->externalObservation = $inst;
  }

  /**
   * Getter externalObservation
   *
   * @return CCDAPOCD_MT000040_ExternalObservation
   */
  function getExternalObservation() {
    return $this->externalObservation;
  }

  /**
   * Setter externalProcedure
   *
   * @param CCDAPOCD_MT000040_ExternalProcedure $inst CCDAPOCD_MT000040_ExternalProcedure
   *
   * @return void
   */
  function setExternalProcedure(CCDAPOCD_MT000040_ExternalProcedure $inst) {
    $this->externalProcedure = $inst;
  }

  /**
   * Getter externalProcedure
   *
   * @return CCDAPOCD_MT000040_ExternalProcedure
   */
  function getExternalProcedure() {
    return $this->externalProcedure;
  }

  /**
   * Setter externalDocument
   *
   * @param CCDAPOCD_MT000040_ExternalDocument $inst CCDAPOCD_MT000040_ExternalDocument
   *
   * @return void
   */
  function setExternalDocument(CCDAPOCD_MT000040_ExternalDocument $inst) {
    $this->externalDocument = $inst;
  }

  /**
   * Getter externalDocument
   *
   * @return CCDAPOCD_MT000040_ExternalDocument
   */
  function getExternalDocument() {
    return $this->externalDocument;
  }

  /**
   * Setter typeCode
   *
   * @param String $inst String
   *
   * @return void
   */
  function setTypeCode($inst) {
    if (!$inst) {
      $this->typeCode = null;
      return;
    }
    $act = new CCDAx_ActRelationshipExternalReference();
    $act->setData($inst);
    $this->typeCode = $act;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAx_ActRelationshipExternalReference
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
    $props["typeId"]              = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["seperatableInd"]      = "CCDABL xml|element max|1";
    $props["externalAct"]         = "CCDAPOCD_MT000040_ExternalAct xml|element required";
    $props["externalObservation"] = "CCDAPOCD_MT000040_ExternalObservation xml|element required";
    $props["externalProcedure"]   = "CCDAPOCD_MT000040_ExternalProcedure xml|element required";
    $props["externalDocument"]    = "CCDAPOCD_MT000040_ExternalDocument xml|element required";
    $props["typeCode"]            = "CCDAx_ActRelationshipExternalReference xml|attribute required";
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
     * Test avec un externalAct correct
     */

    $eAct = new CCDAPOCD_MT000040_ExternalAct();
    $eAct->setMoodCode();
    $this->setExternalAct($eAct);
    $tabTest[] = $this->sample("Test avec un externalAct correct, attribut manquant", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode incorrect
     */

    $this->setTypeCode("TESTTEST");
    $tabTest[] = $this->sample("Test avec un typeCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correct
     */

    $this->setTypeCode("SPRT");
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un seperatableInd incorrect
     */

    $bl = new CCDABL();
    $bl->setValue("TESTTEST");
    $this->setSeperatableInd($bl);
    $tabTest[] = $this->sample("Test avec un seperatableInd incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un seperatableInd correct
     */

    $bl->setValue("true");
    $this->setSeperatableInd($bl);
    $tabTest[] = $this->sample("Test avec un seperatableInd correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un externalObservation correct
     */

    $eAct = new CCDAPOCD_MT000040_ExternalObservation();
    $eAct->setMoodCode();
    $this->setExternalObservation($eAct);
    $tabTest[] = $this->sample("Test avec un externalObservation correct, séquence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un externalObservation correct
     */

    $this->externalAct = null;
    $tabTest[] = $this->sample("Test avec un externalObservation correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un externalProcedure correct
     */

    $eAct = new CCDAPOCD_MT000040_ExternalProcedure();
    $eAct->setMoodCode();
    $this->setExternalProcedure($eAct);
    $tabTest[] = $this->sample("Test avec un externalProcedure correct, séquence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un externalProcedure correct
     */

    $this->externalObservation = null;
    $tabTest[] = $this->sample("Test avec un externalProcedure correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un externalDocument correct
     */

    $eAct = new CCDAPOCD_MT000040_ExternalDocument();
    $eAct->setMoodCode();
    $this->setExternalDocument($eAct);
    $tabTest[] = $this->sample("Test avec un externalDocument correct, séquence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un externalDocument correct
     */

    $this->externalProcedure = null;
    $tabTest[] = $this->sample("Test avec un externalDocument correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}