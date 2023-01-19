<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Rim\CCDARIMLanguageCommunication;

/**
 * POCD_MT000040_LanguageCommunication Class
 */
class CCDAPOCD_MT000040_LanguageCommunication extends CCDARIMLanguageCommunication {

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
   * Setter modeCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setModeCode(CCDACE $inst) {
    $this->modeCode = $inst;
  }

  /**
   * Getter modeCode
   *
   * @return CCDACE
   */
  function getModeCode() {
    return $this->modeCode;
  }

  /**
   * Setter proficiencyLevelCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setProficiencyLevelCode(CCDACE $inst) {
    $this->proficiencyLevelCode = $inst;
  }

  /**
   * Getter proficiencyLevelCode
   *
   * @return CCDACE
   */
  function getProficiencyLevelCode() {
    return $this->proficiencyLevelCode;
  }

  /**
   * Setter preferenceInd
   *
   * @param CCDABL $inst CCDABL
   *
   * @return void
   */
  function setPreferenceInd(CCDABL $inst) {
    $this->preferenceInd = $inst;
  }

  /**
   * Getter preferenceInd
   *
   * @return CCDABL
   */
  function getPreferenceInd() {
    return $this->preferenceInd;
  }


  /**
   * Retourne les propriétés
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]               = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["languageCode"]         = "CCDACS xml|element max|1";
    $props["modeCode"]             = "CCDACE xml|element max|1";
    $props["proficiencyLevelCode"] = "CCDACE xml|element max|1";
    $props["preferenceInd"]        = "CCDABL xml|element max|1";
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

    $cs->setCode("TESTTEST");
    $this->setLanguageCode($cs);
    $tabTest[] = $this->sample("Test avec un languageCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un modeCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setModeCode($ce);
    $tabTest[] = $this->sample("Test avec un modeCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un modeCode correct
     */

    $ce->setCode("TEST");
    $this->setModeCode($ce);
    $tabTest[] = $this->sample("Test avec un modeCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un proficiencyLevelCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setProficiencyLevelCode($ce);
    $tabTest[] = $this->sample("Test avec un proficiencyLevelCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un proficiencyLevelCode correct
     */

    $ce->setCode("TEST");
    $this->setModeCode($ce);
    $tabTest[] = $this->sample("Test avec un proficiencyLevelCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un preferenceInd incorrect
     */

    $bl = new CCDABL();
    $bl->setValue("TEST");
    $this->setPreferenceInd($bl);
    $tabTest[] = $this->sample("Test avec un preferenceInd incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un preferenceInd correct
     */

    $bl->setValue("true");
    $this->setPreferenceInd($bl);
    $tabTest[] = $this->sample("Test avec un preferenceInd correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
