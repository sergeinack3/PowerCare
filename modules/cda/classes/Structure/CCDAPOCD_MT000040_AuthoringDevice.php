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
use Ox\Interop\Cda\Datatypes\Base\CCDASC;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClassDevice;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminer;
use Ox\Interop\Cda\Rim\CCDARIMDevice;

/**
 * POCD_MT000040_AuthoringDevice Class
 */
class CCDAPOCD_MT000040_AuthoringDevice extends CCDARIMDevice {

  /**
   * @var CCDAPOCD_MT000040_MaintainedEntity[]
   */
  public $asMaintainedEntity = array();

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
   * Setter manufacturerModelName
   *
   * @param CCDASC $inst CCDASC
   *
   * @return void
   */
  function setManufacturerModelName(CCDASC $inst) {
    $this->manufacturerModelName = $inst;
  }

  /**
   * Getter manufacturerModelName
   *
   * @return CCDASC
   */
  function getManufacturerModelName() {
    return $this->manufacturerModelName;
  }

  /**
   * Setter softwareName
   *
   * @param CCDASC $inst CCDASC
   *
   * @return void
   */
  function setSoftwareName(CCDASC $inst) {
    $this->softwareName = $inst;
  }

  /**
   * Getter softwareName
   *
   * @return CCDASC
   */
  function getSoftwareName() {
    return $this->softwareName;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_MaintainedEntity $inst CCDAPOCD_MT000040_MaintainedEntity
   *
   * @return void
   */
  function appendAsMaintainedEntity(CCDAPOCD_MT000040_MaintainedEntity $inst) {
    array_push($this->asMaintainedEntity, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListAsMaintainedEntity() {
    $this->asMaintainedEntity = array();
  }

  /**
   * Getter asMaintainedEntity
   *
   * @return CCDAPOCD_MT000040_MaintainedEntity[]
   */
  function getAsMaintainedEntity() {
    return $this->asMaintainedEntity;
  }

  /**
   * Assigne classCode à DEV
   *
   * @return void
   */
  function setClassCode() {
    $entity = new CCDAEntityClassDevice();
    $entity->setData("DEV");
    $this->classCode = $entity;
  }

  /**
   * Getter classCode
   *
   * @return CCDAEntityClassDevice
   */
  function getClassCode() {
    return $this->classCode;
  }

  /**
   * Assigne determinerCode à INSTANCE
   *
   * @return void
   */
  function setDeterminerCode() {
    $entity = new CCDAEntityDeterminer();
    $entity->setData("INSTANCE");
    $this->determinerCode = $entity;
  }

  /**
   * Getter determinerCode
   *
   * @return CCDAEntityDeterminer
   */
  function getDeterminerCode() {
    return $this->determinerCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]                = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["code"]                  = "CCDACE xml|element max|1";
    $props["manufacturerModelName"] = "CCDASC xml|element max|1";
    $props["softwareName"]          = "CCDASC xml|element max|1";
    $props["asMaintainedEntity"]    = "CCDAPOCD_MT000040_MaintainedEntity xml|element";
    $props["classCode"]             = "CCDAEntityClassDevice xml|attribute fixed|DEV";
    $props["determinerCode"]        = "CCDAEntityDeterminer xml|attribute fixed|INSTANCE";
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
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un determinerCode correct
     */

    $this->setDeterminerCode();
    $tabTest[] = $this->sample("Test avec un determinerCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un typeId correct
     */

    $typeID = new CCDAPOCD_MT000040_InfrastructureRoot_typeId();
    $this->setTypeId($typeID);
    $tabTest[] = $this->sample("Test avec un typeId correct", "Document valide");

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
     * Test avec un Code incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un Code incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Code correct
     */

    $ce->setCode("TEST");
    $this->setCode($ce);
    $tabTest[] = $this->sample("Test avec un Code correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un manufacturerModelName incorrect
     */

    $sc = new CCDASC();
    $sc->setCode(" ");
    $this->setManufacturerModelName($sc);
    $tabTest[] = $this->sample("Test avec un manufacturerModelName incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un manufacturerModelName correct
     */

    $sc->setCode("TEST");
    $this->setManufacturerModelName($sc);
    $tabTest[] = $this->sample("Test avec un manufacturerModelName correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un softwareName incorrect
     */

    $sc = new CCDASC();
    $sc->setCode(" ");
    $this->setSoftwareName($sc);
    $tabTest[] = $this->sample("Test avec un softwareName incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un softwareName correct
     */

    $sc->setCode("TEST");
    $this->setSoftwareName($sc);
    $tabTest[] = $this->sample("Test avec un softwareName correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un asMaintainedEntity correct
     */

    $maintain = new CCDAPOCD_MT000040_MaintainedEntity();
    $person = new CCDAPOCD_MT000040_Person();
    $person->setClassCode();
    $maintain->setMaintainingPerson($person);
    $this->appendAsMaintainedEntity($maintain);
    $tabTest[] = $this->sample("Test avec un asMaintainedEntity correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
