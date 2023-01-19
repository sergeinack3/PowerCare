<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Voc\CCDARoleClassRoot;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_ParticipantRole Class
 */
class CCDAPOCD_MT000040_ParticipantRole extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_Device
   */
  public $playingDevice;

  /**
   * @var CCDAPOCD_MT000040_PlayingEntity
   */
  public $playingEntity;

  /**
   * @var CCDAPOCD_MT000040_Entity
   */
  public $scopingEntity;

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
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAAD $inst CCDAAD
   *
   * @return void
   */
  function appendAddr(CCDAAD $inst) {
    array_push($this->addr, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListAddr() {
    $this->addr = array();
  }

  /**
   * Getter addr
   *
   * @return CCDAAD[]
   */
  function getAddr() {
    return $this->addr;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDATEL $inst CCDATEL
   *
   * @return void
   */
  function appendTelecom(CCDATEL $inst) {
    array_push($this->telecom, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListTelecom() {
    $this->telecom = array();
  }

  /**
   * Getter telecom
   *
   * @return CCDATEL[]
   */
  function getTelecom() {
    return $this->telecom;
  }

  /**
   * Setter playingDevice
   *
   * @param CCDAPOCD_MT000040_Device $inst CCDAPOCD_MT000040_Device
   *
   * @return void
   */
  function setPlayingDevice(CCDAPOCD_MT000040_Device $inst) {
    $this->playingDevice = $inst;
  }

  /**
   * Getter playingDevice
   *
   * @return CCDAPOCD_MT000040_Device
   */
  function getPlayingDevice() {
    return $this->playingDevice;
  }

  /**
   * Setter playingEntity
   *
   * @param CCDAPOCD_MT000040_PlayingEntity $inst CCDAPOCD_MT000040_PlayingEntity
   *
   * @return void
   */
  function setPlayingEntity(CCDAPOCD_MT000040_PlayingEntity $inst) {
    $this->playingEntity = $inst;
  }

  /**
   * Getter playingEntity
   *
   * @return CCDAPOCD_MT000040_PlayingEntity
   */
  function getPlayingEntity() {
    return $this->playingEntity;
  }

  /**
   * Setter scopingEntity
   *
   * @param CCDAPOCD_MT000040_Entity $inst CCDAPOCD_MT000040_Entity
   *
   * @return void
   */
  function setScopingEntity(CCDAPOCD_MT000040_Entity $inst) {
    $this->scopingEntity = $inst;
  }

  /**
   * Getter scopingEntity
   *
   * @return CCDAPOCD_MT000040_Entity
   */
  function getScopingEntity() {
    return $this->scopingEntity;
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
    $role = new CCDARoleClassRoot();
    $role->setData($inst);
    $this->classCode = $role;
  }

  /**
   * Getter classCode
   *
   * @return CCDARoleClassRoot
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
    $props["typeId"]        = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]            = "CCDAII xml|element";
    $props["code"]          = "CCDACE xml|element max|1";
    $props["addr"]          = "CCDAAD xml|element";
    $props["telecom"]       = "CCDATEL xml|element";
    $props["playingDevice"] = "CCDAPOCD_MT000040_Device xml|element max|1";
    $props["playingEntity"] = "CCDAPOCD_MT000040_PlayingEntity xml|element max|1";
    $props["scopingEntity"] = "CCDAPOCD_MT000040_Entity xml|element max|1";
    $props["classCode"]     = "CCDARoleClassRoot xml|attribute default|ROL";
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
     * Test avec une addr incorrect
     */

    $ad = new CCDAAD();
    $ad->setUse(array("TESTTEST"));
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec une addr incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec une addr correct
     */

    $ad->setUse(array("PST"));
    $this->resetListAddr();
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec une addr correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un telecom incorrect
     */

    $tel = new CCDATEL();
    $tel->setUse(array("TESTTEST"));
    $this->appendTelecom($tel);
    $tabTest[] = $this->sample("Test avec une telecom incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un telecom correct
     */

    $tel->setUse(array("AS"));
    $this->resetListTelecom();
    $this->appendTelecom($tel);
    $tabTest[] = $this->sample("Test avec une telecom correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode incorrect
     */

    $this->setClassCode(" ");
    $tabTest[] = $this->sample("Test avec une classCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("ROL");
    $tabTest[] = $this->sample("Test avec une classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un scopingEntity correct
     */

    $entity = new CCDAPOCD_MT000040_Entity();
    $entity->setDeterminerCode();
    $this->setScopingEntity($entity);
    $tabTest[] = $this->sample("Test avec une scopingEntity correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un playingEntity correct
     */

    $playing = new CCDAPOCD_MT000040_PlayingEntity();
    $playing->setDeterminerCode();
    $this->setPlayingEntity($playing);
    $tabTest[] = $this->sample("Test avec un playingEntity correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un playingDevice correct
     */

    $device = new CCDAPOCD_MT000040_Device();
    $device->setDeterminerCode();
    $this->setPlayingDevice($device);
    $tabTest[] = $this->sample("Test avec un playingDevice correct, séquence invalide", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un playingDevice correct
     */

    $this->playingEntity = null;
    $tabTest[] = $this->sample("Test avec un playingDevice correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
