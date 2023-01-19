<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDA_base_cs;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClass;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMAct;

/**
 * POCD_MT000040_Section Class
 */
class CCDAPOCD_MT000040_Section extends CCDARIMAct {

  /**
   * @var CCDA_base_cs
   */
  public $identifier;

  /**
   * @var CCDAPOCD_MT000040_Subject
   */
  public $subject;

  /**
   * @var CCDAPOCD_MT000040_Author[]
   */
  public $author = array();

  /**
   * @var CCDAPOCD_MT000040_Informant12[]
   */
  public $informant = array();

  /**
   * @var CCDAPOCD_MT000040_Entry[]
   */
  public $entry = array();

  /**
   * @var CCDAPOCD_MT000040_Component5[]
   */
  public $component = array();

  /** @var string */
  public $_function_name;

  /**
   * Setter id
   *
   * @param CCDAII $inst CCDAII
   *
   * @return void
   */
  function setId(CCDAII $inst) {
    $this->id = $inst;
  }

  /**
   * Getter id
   *
   * @return CCDAII
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
   * Setter title
   *
   * @param CCDAST $inst CCDAST
   *
   * @return void
   */
  function setTitle(CCDAST $inst) {
    $this->title = $inst;
  }

  /**
   * Getter title
   *
   * @return CCDAST
   */
  function getTitle() {
    return $this->title;
  }

  /**
   * Setter text
   *
   * @param CCDAED $inst CCDAStrucDoc_Text
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
   * Setter subject
   *
   * @param CCDAPOCD_MT000040_Subject $inst CCDAPOCD_MT000040_Subject
   *
   * @return void
   */
  function setSubject(CCDAPOCD_MT000040_Subject $inst) {
    $this->subject = $inst;
  }

  /**
   * Getter subject
   *
   * @return CCDAPOCD_MT000040_Subject
   */
  function getSubject() {
    return $this->subject;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Author $inst CCDAPOCD_MT000040_Author
   *
   * @return void
   */
  function appendAuthor(CCDAPOCD_MT000040_Author $inst) {
    array_push($this->author, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListAuthor() {
    $this->author = array();
  }

  /**
   * Getter author
   *
   * @return CCDAPOCD_MT000040_Author[]
   */
  function getAuthor() {
    return $this->author;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Informant12 $inst CCDAPOCD_MT000040_Informant12
   *
   * @return void
   */
  function appendInformant(CCDAPOCD_MT000040_Informant12 $inst) {
    array_push($this->informant, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListInformant() {
    $this->informant = array();
  }

  /**
   * Getter informant
   *
   * @return CCDAPOCD_MT000040_Informant12[]
   */
  function getInformant() {
    return $this->informant;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Entry $inst CCDAPOCD_MT000040_Entry
   *
   * @return void
   */
  function appendEntry(CCDAPOCD_MT000040_Entry $inst) {
    array_push($this->entry, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListEntry() {
    $this->entry = array();
  }

  /**
   * Getter entry
   *
   * @return CCDAPOCD_MT000040_Entry[]
   */
  function getEntry() {
    return $this->entry;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Component5 $inst CCDAPOCD_MT000040_Component5
   *
   * @return void
   */
  function appendComponent(CCDAPOCD_MT000040_Component5 $inst) {
    array_push($this->component, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListComponent() {
    $this->component = array();
  }

  /**
   * Getter component
   *
   * @return CCDAPOCD_MT000040_Component5[]
   */
  function getComponent() {
    return $this->component;
  }

  /**
   * Setter ID
   *
   * @param String $inst String
   *
   * @return void
   */
  function setIdentifier($inst) {
    if (!$inst) {
      $this->identifier = null;
      return;
    }
    $cs = new CCDA_base_cs();
    $cs->setData($inst);
    $this->identifier = $cs;
  }

  /**
   * Getter ID
   *
   * @return CCDA_base_cs
   */
  function getIdentifier() {
    return $this->identifier;
  }

  /**
   * Assigne classCode à DOCSET
   *
   * @return void
   */
  function setClassCode() {
    $actClass = new CCDAActClass();
    $actClass->setData("DOCSECT");
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
    $props["id"]                  = "CCDAII xml|element max|1";
    $props["code"]                = "CCDACE xml|element max|1";
    $props["title"]               = "CCDAST xml|element max|1";
    $props["text"]                = "CCDAStrucDoc_Text xml|element max|1";
    $props["confidentialityCode"] = "CCDACE xml|element max|1";
    $props["languageCode"]        = "CCDACS xml|element max|1";
    $props["subject"]             = "CCDAPOCD_MT000040_Subject xml|element max|1";
    $props["author"]              = "CCDAPOCD_MT000040_Author xml|element";
    $props["informant"]           = "CCDAPOCD_MT000040_Informant12 xml|element";
    $props["entry"]               = "CCDAPOCD_MT000040_Entry xml|element";
    $props["component"]           = "CCDAPOCD_MT000040_Component5 xml|element";
    $props["identifier"]          = "CCDA_base_cs xml|attribute";
    $props["classCode"]           = "CCDAActClass xml|attribute fixed|DOCSECT";
    $props["moodCode"]            = "CCDAActMood xml|attribute fixed|EVN";
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
     * Test avec un ID correct
     */

    $this->setIdentifier("TEST");
    $tabTest[] = $this->sample("Test avec un ID correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un moodCode correct
     */

    $this->setMoodCode();
    $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

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
    $this->setId($ii);
    $tabTest[] = $this->sample("Test avec un Id incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un Id correct
     */

    $ii->setRoot("1.2.250.1.213.1.1.9");
    $this->setId($ii);
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
     * Test avec un title incorrect
     */

    $st = new CCDAST();
    $st->setLanguage(" ");
    $this->setTitle($st);
    $tabTest[] = $this->sample("Test avec un title incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un title correct
     */

    $st->setLanguage("TEST");
    $this->setTitle($st);
    $tabTest[] = $this->sample("Test avec un title correct", "Document valide");

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

    $ce->setCode("SYNTH");
    $this->setConfidentialityCode($ce);
    $tabTest[] = $this->sample("Test avec un confidentialityCode correct", "Document valide");

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
     * Test avec un subject correct
     */

    $sub = new CCDAPOCD_MT000040_Subject();
    $relatedSub = new CCDAPOCD_MT000040_RelatedSubject();
    $relatedSub->setTypeId();
    $sub->setRelatedSubject($relatedSub);
    $this->setSubject($sub);
    $tabTest[] = $this->sample("Test avec un subject correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un author correct
     */

    $auth = new CCDAPOCD_MT000040_Author();
    $ts = new CCDATS();
    $ts->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
    $auth->setTime($ts);

    $assigned = new CCDAPOCD_MT000040_AssignedAuthor();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assigned->appendId($ii);
    $auth->setAssignedAuthor($assigned);
    $this->appendAuthor($auth);
    $tabTest[] = $this->sample("Test avec un author correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un informant correct
     */

    $infor = new CCDAPOCD_MT000040_Informant12();
    $assigned = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assigned->appendId($ii);
    $infor->setAssignedEntity($assigned);
    $this->appendInformant($infor);
    $tabTest[] = $this->sample("Test avec un informant correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un entry correct
     */

    $ent = new CCDAPOCD_MT000040_Entry();
    $ac = new CCDAPOCD_MT000040_Act();
    $cd = new CCDACD();
    $cd->setCode("SYNTH");
    $ac->setCode($cd);
    $ac->setClassCode("ACT");
    $ac->setMoodCode("INT");
    $ent->setAct($ac);
    $this->appendEntry($ent);
    $tabTest[] = $this->sample("Test avec un entry correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un entry correct
     */

    $comp = new CCDAPOCD_MT000040_Component5();
    $sec = new CCDAPOCD_MT000040_Section();
    $sec->setClassCode();
    $comp->setSection($sec);
    $this->appendComponent($comp);
    $tabTest[] = $this->sample("Test avec un entry correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}
