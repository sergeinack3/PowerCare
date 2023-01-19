<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_ActRelationshipDocument;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_RelatedDocument Class
 */
class CCDAPOCD_MT000040_RelatedDocument extends CCDARIMActRelationship {

  /**
   * @var CCDAPOCD_MT000040_ParentDocument
   */
  public $parentDocument;

  /**
   * Setter parentDocument
   *
   * @param CCDAPOCD_MT000040_ParentDocument $inst CCDAPOCD_MT000040_ParentDocument
   *
   * @return void
   */
  function setParentDocument(CCDAPOCD_MT000040_ParentDocument $inst) {
    $this->parentDocument = $inst;
  }

  /**
   * Getter parentDocument
   *
   * @return CCDAPOCD_MT000040_ParentDocument
   */
  function getParentDocument() {
    return $this->parentDocument;
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
    $act = new CCDAx_ActRelationshipDocument();
    $act->setData($inst);
    $this->typeCode = $act;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAx_ActRelationshipDocument
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
    $props["parentDocument"] = "CCDAPOCD_MT000040_ParentDocument xml|element required";
    $props["typeCode"] = "CCDAx_ActRelationshipDocument xml|attribute required";
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
     * Test avec un parentDocument correct
     */

    $parent = new CCDAPOCD_MT000040_ParentDocument();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $parent->appendId($ii);
    $this->setParentDocument($parent);
    $tabTest[] = $this->sample("Test avec un parentDocument correct", "Document invalide");

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

    $this->setTypeCode("RPLC");
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}