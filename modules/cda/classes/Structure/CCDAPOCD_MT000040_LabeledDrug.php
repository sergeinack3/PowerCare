<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAEN;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityClassManufacturedMaterial;
use Ox\Interop\Cda\Datatypes\Voc\CCDAEntityDeterminerDetermined;
use Ox\Interop\Cda\Rim\CCDARIMMaterial;

/**
 * POCD_MT000040_LabeledDrug Class
 */
class CCDAPOCD_MT000040_LabeledDrug extends CCDARIMMaterial {

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
   * Setter name
   *
   * @param CCDAEN $inst CCDAEN
   *
   * @return void
   */
  function setName(CCDAEN $inst) {
    $this->name = $inst;
  }

  /**
   * Getter name
   *
   * @return CCDAEN
   */
  function getName() {
    return $this->name;
  }

  /**
   * Assigne classCode à MMAT
   *
   * @return void
   */
  function setClassCode() {
    $entityClass = new CCDAEntityClassManufacturedMaterial();
    $entityClass->setData("MMAT");
    $this->classCode = $entityClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDAEntityClassManufacturedMaterial
   */
  function getClassCode() {
    return $this->classCode;
  }

  /**
   * Assigne determinerCode à KIND
   *
   * @return void
   */
  function setDeterminerCode() {
    $determiner = new CCDAEntityDeterminerDetermined();
    $determiner->setData("KIND");
    $this->determinerCode = $determiner;
  }

  /**
   * Getter determinerCode
   *
   * @return CCDAEntityDeterminerDetermined
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
    $props["typeId"]         = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["code"]           = "CCDACE xml|element max|1";
    $props["name"]           = "CCDAEN xml|element max|1";
    $props["classCode"]      = "CCDAEntityClassManufacturedMaterial xml|attribute fixed|MMAT";
    $props["determinerCode"] = "CCDAEntityDeterminerDetermined xml|attribute fixed|KIND";
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
     * Test avec un name incorrect
     */

    $en = new CCDAEN();
    $en->setUse(array("TESTTEST"));
    $this->setName($en);
    $tabTest[] = $this->sample("Test avec un name incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un name correct
     */

    $en->setUse(array("C"));
    $this->setName($en);
    $tabTest[] = $this->sample("Test avec un name correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
