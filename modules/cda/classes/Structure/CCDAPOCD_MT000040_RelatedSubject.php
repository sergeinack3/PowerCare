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
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_DocumentSubject;
use Ox\Interop\Cda\Rim\CCDARIMRole;

/**
 * POCD_MT000040_RelatedSubject Class
 */
class CCDAPOCD_MT000040_RelatedSubject extends CCDARIMRole {

  /**
   * @var CCDAPOCD_MT000040_SubjectPerson
   */
  public $subject;

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
   * Setter subject
   *
   * @param CCDAPOCD_MT000040_SubjectPerson $inst CCDAPOCD_MT000040_SubjectPerson
   *
   * @return void
   */
  function setSubject(CCDAPOCD_MT000040_SubjectPerson $inst) {
    $this->subject = $inst;
  }

  /**
   * Getter subject
   *
   * @return CCDAPOCD_MT000040_SubjectPerson
   */
  function getSubject() {
    return $this->subject;
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
    $doc = new CCDAx_DocumentSubject();
    $doc->setData($inst);
    $this->classCode = $doc;
  }

  /**
   * Getter classCode
   *
   * @return CCDAx_DocumentSubject
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
    $props["typeId"]    = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["code"]      = "CCDACE xml|element max|1";
    $props["addr"]      = "CCDAAD xml|element";
    $props["telecom"]   = "CCDATEL xml|element";
    $props["subject"]   = "CCDAPOCD_MT000040_SubjectPerson xml|element max|1";
    $props["classCode"] = "CCDAx_DocumentSubject xml|attribute default|PRS";
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
     * Test avec un addr incorrect
     */

    $ad = new CCDAAD();
    $ad->setUse(array("TESTTEST"));
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec un addr incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un addr correct
     */

    $ad->setUse(array("PST"));
    $this->appendAddr($ad);
    $tabTest[] = $this->sample("Test avec un addr correct", "Document valide");

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

    $this->setClassCode("TESTTEST");
    $tabTest[] = $this->sample("Test avec une classCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("PAT");
    $tabTest[] = $this->sample("Test avec une classCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un subject correct
     */

    $subjectPerson = new CCDAPOCD_MT000040_SubjectPerson();
    $subjectPerson->setClassCode();
    $this->setSubject($subjectPerson);
    $tabTest[] = $this->sample("Test avec une subject correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
