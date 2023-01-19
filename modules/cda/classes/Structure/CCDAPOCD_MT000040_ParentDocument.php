<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClinicalDocument;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMContextStructure;

/**
 * POCD_MT000040_ParentDocument Class
 */
class CCDAPOCD_MT000040_ParentDocument extends CCDARIMContextStructure {

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
   * @param CCDACD $inst CCDACD
   *
   * @return void
   */
  function setCode(CCDACD $inst) {
    $this->code = $inst;
  }

  /**
   * Getter code
   *
   * @return CCDACD
   */
  function getCode() {
    return $this->code;
  }

  /**
   * Setter text
   *
   * @param CCDAED $inst CCDAED
   *
   * @return void
   */
  function setText(CCDAED $inst) {
    $this->text = $inst;
  }

  /**
   * Getter text
   *
   * @return CCDAED
   */
  function getText() {
    return $this->text;
  }

  /**
   * Setter setId
   *
   * @param CCDAII $inst CCDAII
   *
   * @return void
   */
  function setSetId(CCDAII $inst) {
    $this->setId = $inst;
  }

  /**
   * Getter setId
   *
   * @return CCDAII
   */
  function getSetId() {
    return $this->setId;
  }

  /**
   * Setter versionNumber
   *
   * @param CCDAINT $inst CCDAINT
   *
   * @return void
   */
  function setVersionNumber(CCDAINT $inst) {
    $this->versionNumber = $inst;
  }

  /**
   * Getter versionNumber
   *
   * @return CCDAINT
   */
  function getVersionNumber() {
    return $this->versionNumber;
  }

  /**
   * Assigne classCode à DOCCLIN
   *
   * @return void
   */
  function setClassCode() {
    $actClinical = new CCDAActClinicalDocument();
    $actClinical->setData("DOCCLIN");
    $this->classCode = $actClinical;
  }

  /**
   * Getter classCode
   *
   * @return CCDAActClinicalDocument
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
    $props["typeId"]        = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]            = "CCDAII xml|element min|1";
    $props["code"]          = "CCDACD xml|element max|1";
    $props["text"]          = "CCDAED xml|element max|1";
    $props["setId"]         = "CCDAII xml|element max|1";
    $props["versionNumber"] = "CCDAINT xml|element max|1";
    $props["classCode"]     = "CCDAActClinicalDocument xml|attribute fixed|DOCCLIN";
    $props["moodCode"]      = "CCDAActMood xml|attribute fixed|EVN";
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

    $cd = new CCDACD();
    $cd->setCode(" ");
    $this->setCode($cd);
    $tabTest[] = $this->sample("Test avec un code incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un code correct
     */

    $cd->setCode("SYNTH");
    $this->setCode($cd);
    $tabTest[] = $this->sample("Test avec un code correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un text incorrect
     */

    $ed = new CCDAED();
    $ed->setLanguage(" ");
    $this->setText($ed);
    $tabTest[] = $this->sample("Test avec un text incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un text correct
     */

    $ed->setLanguage("FR");
    $this->setText($ed);
    $tabTest[] = $this->sample("Test avec un text correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un setId correct
     */

    $ii = new CCDAII();
    $ii->setRoot("4TESTTEST");
    $this->setSetId($ii);
    $tabTest[] = $this->sample("Test avec un setId incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un setId correct
     */

    $ii->setRoot("1.2.5");
    $this->setSetId($ii);
    $tabTest[] = $this->sample("Test avec un setId correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un versionNumber correct
     */

    $int = new CCDAINT();
    $int->setValue("10.25");
    $this->setVersionNumber($int);
    $tabTest[] = $this->sample("Test avec un versionNumber incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un versionNumber correct
     */

    $int->setValue("10");
    $this->setVersionNumber($int);
    $tabTest[] = $this->sample("Test avec un versionNumber correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}