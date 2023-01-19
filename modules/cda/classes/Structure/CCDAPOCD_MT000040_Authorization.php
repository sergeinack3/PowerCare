<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActRelationshipType;
use Ox\Interop\Cda\Rim\CCDARIMActRelationship;
/**
 * POCD_MT000040_Authorization Class
 */
class CCDAPOCD_MT000040_Authorization extends CCDARIMActRelationship {

  /**
   * @var CCDAPOCD_MT000040_Consent
   */
  public $consent;

  /**
   * Setter consent
   *
   * @param CCDAPOCD_MT000040_Consent $inst CCDAPOCD_MT000040_Consent
   *
   * @return void
   */
  function setConsent(CCDAPOCD_MT000040_Consent $inst) {
    $this->consent = $inst;
  }

  /**
   * Getter consent
   *
   * @return CCDAPOCD_MT000040_Consent
   */
  function getConsent() {
    return $this->consent;
  }

  /**
   * Assigne typeCode à AUTH
   *
   * @return void
   */
  function setTypeCode() {
    $actRela = new CCDAActRelationshipType();
    $actRela->setData("AUTH");
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
    $props["typeId"]   = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["consent"]  = "CCDAPOCD_MT000040_Consent xml|element required";
    $props["typeCode"] = "CCDAActRelationshipType xml|attribute fixed|AUTH";
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
     * Test avec un consent incorrecte
     */
    $pocConsent = new CCDAPOCD_MT000040_Consent();
    $cs = new CCDACS();
    $cs->setCode(" ");
    $pocConsent->setStatusCode($cs);
    $this->setConsent($pocConsent);
    $tabTest[] = $this->sample("Test avec un consent incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeCode correcte
     */

    $this->setTypeCode();
    $tabTest[] = $this->sample("Test avec un typeCode correct", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un consent correct
     */

    $cs->setCode("TEST");
    $pocConsent->setStatusCode($cs);
    $this->setConsent($pocConsent);
    $tabTest[] = $this->sample("Test avec un consent correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}