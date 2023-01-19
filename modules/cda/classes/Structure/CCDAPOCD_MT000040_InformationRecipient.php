<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Voc\CCDAx_InformationRecipient;
use Ox\Interop\Cda\Rim\CCDARIMParticipation;
/**
 * POCD_MT000040_InformationRecipient Class
 */
class CCDAPOCD_MT000040_InformationRecipient extends CCDARIMParticipation {

  /**
   * @var CCDAPOCD_MT000040_IntendedRecipient
   */
  public $intendedRecipient;

  /**
   * Setter intendedRecipient
   *
   * @param CCDAPOCD_MT000040_IntendedRecipient $inst CCDAPOCD_MT000040_IntendedRecipient
   *
   * @return void
   */
  function setIntendedRecipient(CCDAPOCD_MT000040_IntendedRecipient $inst) {
    $this->intendedRecipient = $inst;
  }

  /**
   * Getter intendedRecipient
   *
   * @return CCDAPOCD_MT000040_IntendedRecipient
   */
  function getIntendedRecipient() {
    return $this->intendedRecipient;
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
    $info = new CCDAx_InformationRecipient();
    $info->setData($inst);
    $this->typeCode = $info;
  }

  /**
   * Getter typeCode
   *
   * @return CCDAx_InformationRecipient
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
    $props["typeId"]            = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["intendedRecipient"] = "CCDAPOCD_MT000040_IntendedRecipient xml|element required";
    $props["typeCode"]          = "CCDAx_InformationRecipient xml|attribute default|PRCP";
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
     * Test avec un intendedRecipient correct
     */

    $inten = new CCDAPOCD_MT000040_IntendedRecipient();
    $inten->setTypeId();
    $this->setIntendedRecipient($inten);
    $tabTest[] = $this->sample("Test avec un intendedRecipient correct", "Document valide");

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

    $this->setTypeCode("TRC");
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}