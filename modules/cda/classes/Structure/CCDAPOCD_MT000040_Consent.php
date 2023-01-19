<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMAct;

/**
 * POCD_MT000040_Consent Class
 */
class CCDAPOCD_MT000040_Consent extends CCDARIMAct {

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
   * Setter statusCode
   *
   * @param CCDACS $inst CCDACS
   *
   * @return void
   */
  function setStatusCode(CCDACS $inst) {
    $this->statusCode = $inst;
  }

  /**
   * Getter statusCode
   *
   * @return CCDACS
   */
  function getStatusCode() {
    return $this->statusCode;
  }

  /**
   * Assigne classCode à CONS
   *
   * @return void
   */
  function setClassCode() {
    $actClass = new CCDAActClass();
    $actClass->setData("CONS");
    $this->classCode = $actClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDAActClass
   */
  function getClassCode() {
    return $this->classCode;
  }

  /**
   * Assigne moodCode à EVN
   *
   * @return void
   */
  function setMoodCode() {
    $actMood = new CCDAActMood();
    $actMood->setData("EVN");
    $this->moodCode = $actMood;
  }

  /**
   * Getter moodCode
   *
   * @return CCDAActMood
   */
  function getMoodCode() {
    return $this->moodCode;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]     = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]         = "CCDAII xml|element";
    $props["code"]       = "CCDACE xml|element max|1";
    $props["statusCode"] = "CCDACS xml|element required";
    $props["classCode"]  = "CCDAActClass xml|attribute fixed|CONS";
    $props["moodCode"]   = "CCDAActMood xml|attribute fixed|EVN";
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
     * Test avec un statusCode incorrect
     */

    $cs = new CCDACS();
    $cs->setCode(" ");
    $this->setStatusCode($cs);
    $tabTest[] = $this->sample("Test avec un statusCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un statusCode correct
     */

    $cs->setCode("TEST");
    $this->setStatusCode($cs);
    $tabTest[] = $this->sample("Test avec un statusCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode();
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un moodCode correct
     */

    $this->setMoodCode();
    $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code incorrect, séquence invalide
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

    return $tabTest;
  }
}