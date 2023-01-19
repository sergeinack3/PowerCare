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
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminer;
use Ox\Interop\Cda\Rim\CCDARIMPerson;

/**
 * POCD_MT000040_SubjectPerson Class
 */
class CCDAPOCD_MT000040_SubjectPerson extends CCDARIMPerson {

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPN $inst CCDAPN
   *
   * @return void
   */
  function appendName(CCDAPN $inst) {
    array_push($this->name, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListName() {
    $this->name = array();
  }

  /**
   * Getter name
   *
   * @return CCDAPN[]
   */
  function getName() {
    return $this->name;
  }

  /**
   * Setter administrativeGenderCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setAdministrativeGenderCode(CCDACE $inst) {
    $this->administrativeGenderCode = $inst;
  }

  /**
   * Getter administrativeGenderCode
   *
   * @return CCDACE
   */
  function getAdministrativeGenderCode() {
    return $this->administrativeGenderCode;
  }

  /**
   * Setter birthTime
   *
   * @param CCDATS $inst CCDATS
   *
   * @return void
   */
  function setBirthTime(CCDATS $inst) {
    $this->birthTime = $inst;
  }

  /**
   * Getter birthTime
   *
   * @return CCDATS
   */
  function getBirthTime() {
    return $this->birthTime;
  }

  /**
   * Assigne classCode à PSN
   *
   * @return void
   */
  function setClassCode() {
    $entityclass = new CCDAEntityClass();
    $entityclass->setData("PSN");
    $this->classCode = $entityclass;
  }

  /**
   * Getter classCode
   *
   * @return CCDAEntityClass
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
    $determiner = new CCDAEntityDeterminer();
    $determiner->setData("INSTANCE");
    $this->determinerCode = $determiner;
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
    $props["typeId"]                   = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["name"]                     = "CCDAPN xml|element";
    $props["administrativeGenderCode"] = "CCDACE xml|element max|1";
    $props["birthTime"]                = "CCDATS xml|element max|1";
    $props["classCode"]                = "CCDAEntityClass xml|attribute fixed|PSN";
    $props["determinerCode"]           = "CCDAEntityDeterminer xml|attribute fixed|INSTANCE";
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
     * Test avec un name incorrect
     */

    $pn = new CCDAPN();
    $pn->setUse(array("TESTTEST"));
    $this->appendName($pn);
    $tabTest[] = $this->sample("Test avec un name incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name correct
     */

    $pn->setUse(array("C"));
    $this->resetListName();
    $this->appendName($pn);
    $tabTest[] = $this->sample("Test avec un name correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un administrativeGenderCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setAdministrativeGenderCode($ce);
    $tabTest[] = $this->sample("Test avec un administrativeGenderCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un administrativeGenderCode correct
     */

    $ce->setCode("TESTTEST");
    $this->setAdministrativeGenderCode($ce);
    $tabTest[] = $this->sample("Test avec un administrativeGenderCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un birthTime incorrect
     */

    $ts = new CCDATS();
    $ts->setValue("TESTTEST");
    $this->setBirthTime($ts);
    $tabTest[] = $this->sample("Test avec un birthTime incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un birthTime correct
     */

    $ts->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
    $this->setBirthTime($ts);
    $tabTest[] = $this->sample("Test avec un birthTime correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
