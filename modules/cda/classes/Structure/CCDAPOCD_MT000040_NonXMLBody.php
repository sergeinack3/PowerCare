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
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMAct;

/**
 * POCD_MT000040_NonXMLBody Class
 */
class CCDAPOCD_MT000040_NonXMLBody extends CCDARIMAct {

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
   * Setter confidentialityCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setConfidentialityCode(CCDACE $inst) {
    $this->confidentialityCode = $inst;
  }

  /**
   * Getter confidentialityCode
   *
   * @return CCDACE
   */
  function getConfidentialityCode() {
    return $this->confidentialityCode;
  }

  /**
   * Setter languageCode
   *
   * @param CCDACS $inst CCDACS
   *
   * @return void
   */
  function setLanguageCode(CCDACS $inst) {
    $this->languageCode = $inst;
  }

  /**
   * Getter languageCode
   *
   * @return CCDACS
   */
  function getLanguageCode() {
    return $this->languageCode;
  }

  /**
   * Assigne classCode à DOCBODY
   *
   * @return void
   */
  function setClassCode() {
    $actClass = new CCDAActClass();
    $actClass->setData("DOCBODY");
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
    $props["typeId"]              = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["text"]                = "CCDAED xml|element required";
    $props["confidentialityCode"] = "CCDACE xml|element max|1";
    $props["languageCode"]        = "CCDACS xml|element max|1";
    $props["classCode"]           = "CCDAActClass xml|attribute fixed|DOCBODY";
    $props["moodCode"]            = "CCDAActMood xml|attribute fixed|EVN";
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

    $ed->setLanguage("TEST");
    $this->setText($ed);
    $tabTest[] = $this->sample("Test avec un text correct", "Document valide");

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
     * Test avec un languageCode incorrect
     */

    $cs = new CCDACS();
    $cs->setCode(" ");
    $this->setLanguageCode($cs);
    $tabTest[] = $this->sample("Test avec un languageCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un languageCode correct
     */

    $cs->setCode("TEST");
    $this->setLanguageCode($cs);
    $tabTest[] = $this->sample("Test avec un languageCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un confidentialityCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setConfidentialityCode($ce);
    $tabTest[] = $this->sample("Test avec un confidentialityCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un confidentialityCode correct
     */

    $ce->setCode("TEST");
    $this->setConfidentialityCode($ce);
    $tabTest[] = $this->sample("Test avec un confidentialityCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}