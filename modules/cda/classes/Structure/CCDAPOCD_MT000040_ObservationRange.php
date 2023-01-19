<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDAANY;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClassObservation;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMObservation;

/**
 * POCD_MT000040_ObservationRange Class
 */
class CCDAPOCD_MT000040_ObservationRange extends CCDARIMObservation {

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
   * Setter value
   *
   * @param CCDAANY $inst CCDAANY
   *
   * @return void
   */
  function setValue(CCDAANY $inst) {
    $this->value = $inst;
  }

  /**
   * Getter value
   *
   * @return CCDAANY
   */
  function getValue() {
    return $this->value;
  }

  /**
   * Setter interpretationCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setInterpretationCode(CCDACE $inst) {
    $this->interpretationCode = $inst;
  }

  /**
   * Getter interpretationCode
   *
   * @return CCDACE
   */
  function getInterpretationCode() {
    return $this->interpretationCode;
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
    $act = new CCDAActClassObservation();
    $act->setData($inst);
    $this->classCode = $act;
  }

  /**
   * Getter classCode
   *
   * @return CCDAActClassObservation
   */
  function getClassCode() {
    return $this->classCode;
  }

  /**
   * Assigne moodCode à EVN.CRT
   *
   * @return void
   */
  function setMoodCode() {
    $actMood = new CCDAActMood();
    $actMood->setData("EVN.CRT");
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
    $props["typeId"]             = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["code"]               = "CCDACD xml|element max|1";
    $props["text"]               = "CCDAED xml|element max|1";
    $props["value"]              = "CCDAANY xml|element max|1 abstract";
    $props["interpretationCode"] = "CCDACE xml|element max|1";
    $props["classCode"]          = "CCDAActClassObservation xml|attribute default|OBS";
    $props["moodCode"]           = "CCDAActMood xml|attribute fixed|EVN.CRT";
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
     * Test avec un value incorrect
     */
    $cd = new CCDACD();
    $cd->setCode(" ");
    $this->setValue($cd);
    $tabTest[] = $this->sample("Test avec un value incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un value correct
     */

    $cd->setCode("TEST");
    $this->setValue($cd);
    $tabTest[] = $this->sample("Test avec un value correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un interpretationCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setInterpretationCode($ce);
    $tabTest[] = $this->sample("Test avec un interpretationCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un interpretationCode correct
     */

    $ce->setCode("TEST");
    $this->setInterpretationCode($ce);
    $tabTest[] = $this->sample("Test avec un interpretationCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un moodCode correct
     */

    $this->setMoodCode();
    $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

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

    $this->setClassCode("CNOD");
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
