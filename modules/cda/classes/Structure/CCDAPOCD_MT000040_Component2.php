<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDA_base_bl;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActRelationshipHasComponent;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_Component2 Class
 */
class CCDAPOCD_MT000040_Component2 extends CCDARIMActRelationship {

  /**
   * @var CCDAPOCD_MT000040_NonXMLBody
   */
  public $nonXMLBody;

  /**
   * @var CCDAPOCD_MT000040_StructuredBody
   */
  public $structuredBody;

  /**
   * Setter nonXMLBody
   *
   * @param CCDAPOCD_MT000040_NonXMLBody $inst CCDAPOCD_MT000040_NonXMLBody
   *
   * @return void
   */
  function setNonXMLBody(CCDAPOCD_MT000040_NonXMLBody $inst) {
    $this->nonXMLBody = $inst;
  }

  /**
   * Getter nonXMLBody
   *
   * @return CCDAPOCD_MT000040_NonXMLBody
   */
  function getNonXMLBody() {
    return $this->nonXMLBody;
  }

  /**
   * Setter structuredBody
   *
   * @param CCDAPOCD_MT000040_StructuredBody $inst CCDAPOCD_MT000040_StructuredBody
   *
   * @return void
   */
  function setStructuredBody(CCDAPOCD_MT000040_StructuredBody $inst) {
    $this->structuredBody = $inst;
  }

  /**
   * Getter structuredBody
   *
   * @return CCDAPOCD_MT000040_StructuredBody
   */
  function getStructuredBody() {
    return $this->structuredBody;
  }

  /**
   * Assigne typeCode à COMP
   *
   * @return void
   */
  function setTypeCode() {
    $actRela = new CCDAActRelationshipHasComponent();
    $actRela->setData("COMP");
    $this->typeCode = $actRela;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAActRelationshipHasComponent
   */
  function getTypeCode() {
    return $this->typeCode;
  }

  /**
   * Assigne contextConductionInd à true
   *
   * @return void
   */
  function setContextConductionInd() {
    $bl = new CCDA_base_bl();
    $bl->setData("true");
    $this->contextConductionInd = $bl;
  }

  /**
   * Getter contextConductionInd
   *
   * @return CCDA_base_bl
   */
  function getContextConductionInd() {
    return $this->contextConductionInd;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]               = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["nonXMLBody"]           = "CCDAPOCD_MT000040_NonXMLBody xml|element required";
    $props["structuredBody"]       = "CCDAPOCD_MT000040_StructuredBody xml|element required";
    $props["typeCode"]             = "CCDAActRelationshipHasComponent xml|attribute fixed|COMP";
    $props["contextConductionInd"] = "CCDA_base_bl xml|attribute fixed|true";
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
     * Test avec un nonXMLBody correcte
     */

    $nonXML = new CCDAPOCD_MT000040_NonXMLBody();
    $ed = new CCDAED();
    $ed->setLanguage("TEST");
    $nonXML->setText($ed);
    $this->setNonXMLBody($nonXML);
    $tabTest[] = $this->sample("Test avec un nonXMLBody correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un structuredBody correcte
     */

    $struc = new CCDAPOCD_MT000040_StructuredBody();
    $comp = new CCDAPOCD_MT000040_Component3();
    $sec = new CCDAPOCD_MT000040_Section();
    $sec->setClassCode();
    $comp->setSection($sec);
    $struc->appendComponent($comp);
    $this->setStructuredBody($struc);
    $tabTest[] = $this->sample("Test avec un structuredBody correct, séquence incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un structuredBody correcte
     */

    $this->nonXMLBody = null;
    $tabTest[] = $this->sample("Test avec un structuredBody correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correcte
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un contextConductionInd correcte
     */

    $this->setContextConductionInd();
    $tabTest[] = $this->sample("Test avec un contextConductionInd correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}