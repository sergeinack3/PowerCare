<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClassServiceDeliveryLocation;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_HealthCareFacility Class
 */
class CCDAPOCD_MT000040_HealthCareFacility  extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Place
   */
  public $location;

  /**
   * @var CCDAPOCD_MT000040_Organization
   */
  public $serviceProviderOrganization;

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAII $inst CCDAII
   *
   * @return void
   */
  function appendId(CCDAII $inst) {
    array_push($this->id, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListId() {
    $this->id = array();
  }

  /**
   * Getter id
   *
   * @return CCDAII[]
   */
  function getId() {
    return $this->id;
  }

  /**
   * Setter code
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setCode(CCDACE $inst) {
    $this->code = $inst;
  }

  /**
   * Getter code
   *
   * @return CCDACE
   */
  function getCode() {
    return $this->code;
  }

  /**
   * Setter location
   *
   * @param CCDAPOCD_MT000040_Place $inst CCDAPOCD_MT000040_Place
   *
   * @return void
   */
  function setLocation(CCDAPOCD_MT000040_Place $inst) {
    $this->location = $inst;
  }

  /**
   * Getter location
   *
   * @return CCDAPOCD_MT000040_Place
   */
  function getLocation() {
    return $this->location;
  }

  /**
   * Setter serviceProviderOrganization
   *
   * @param CCDAPOCD_MT000040_Organization $inst CCDAPOCD_MT000040_Organization
   *
   * @return void
   */
  function setServiceProviderOrganization(CCDAPOCD_MT000040_Organization $inst) {
    $this->serviceProviderOrganization = $inst;
  }

  /**
   * Getter serviceProviderOrganization
   *
   * @return CCDAPOCD_MT000040_Organization
   */
  function getServiceProviderOrganization() {
    return $this->serviceProviderOrganization;
  }

  /**
   * Setter classCode
   *
   * @param String $inst String
   *
   * @return void
   */
  function setClassCode($inst) {
    if (!$inst) {
      $this->classCode = null;
      return;
    }
    $role = new CCDARoleClassServiceDeliveryLocation();
    $role->setData($inst);
    $this->classCode = $role;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClassServiceDeliveryLocation
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
    $props["typeId"]                      = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                          = "CCDAII xml|element";
    $props["code"]                        = "CCDACE xml|element max|1";
    $props["location"]                    = "CCDAPOCD_MT000040_Place xml|element max|1";
    $props["serviceProviderOrganization"] = "CCDAPOCD_MT000040_Organization xml|element max|1";
    $props["classCode"]                   = "CCDARoleClassServiceDeliveryLocation xml|attribute default|SDLOC";
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

    $tabTest[] = $this->sample("Test avec les valeurs null", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeId correct
     */

    $this->setTypeId();
    $tabTest[] = $this->sample("Test avec un typeId correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un templateId incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->appendTemplateId($ii);
    $tabTest[] = $this->sample("Test avec un templateId incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un templateId correct
     */

    $ii->setRoot("1.2.250.1.213.1.1.1.1");
    $this->resetListTemplateId();
    $this->appendTemplateId($ii);
    $tabTest[] = $this->sample("Test avec un templateId correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un code incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code correct
     */

    $ce->setCode("SYNTH");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un code correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id incorrect
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un Id incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id correct
     */

    $ii->setRoot("1.2.250.1.213.1.1.9");
    $this->resetListId();
    $this->appendId($ii);
    $tabTest[] = $this->sample("Test avec un Id correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode incorrect
     */

    $this->setClassCode("TESTTEST");
    $tabTest[] = $this->sample("Test avec un classCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("ISDLOC");
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un location correct
     */

    $place = new CCDAPOCD_MT000040_Place();
    $place->setClassCode();
    $this->setLocation($place);
    $tabTest[] = $this->sample("Test avec un location correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un organization correct
     */

    $org = new CCDAPOCD_MT000040_Organization();
    $org->setClassCode();
    $this->setServiceProviderOrganization($org);
    $tabTest[] = $this->sample("Test avec un organization correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
