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
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClassObservation;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMObservation;

/**
 * POCD_MT000040_ObservationMedia Class
 */
class CCDAPOCD_MT000040_ObservationMedia extends CCDARIMObservation {

  /**
   * @var CCDA_base_cs
   */
  public $identifier;

  /**
   * @var CCDAPOCD_MT000040_Subject
   */
  public $subject;

  /**
   * @var CCDAPOCD_MT000040_Specimen[]
   */
  public $specimen = array();

  /**
   * @var CCDAPOCD_MT000040_Performer2[]
   */
  public $performer = array();

  /**
   * @var CCDAPOCD_MT000040_Author[]
   */
  public $author = array();

  /**
   * @var CCDAPOCD_MT000040_Informant12[]
   */
  public $informant = array();

  /**
   * @var CCDAPOCD_MT000040_Participant2[]
   */
  public $participant = array();

  /**
   * @var CCDAPOCD_MT000040_EntryRelationship[]
   */
  public $entryRelationship = array();

  /**
   * @var CCDAPOCD_MT000040_Reference[]
   */
  public $reference = array();

  /**
   * @var CCDAPOCD_MT000040_Precondition[]
   */
  public $precondition = array();

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
   * Setter value
   *
   * @param CCDAED $inst CCDAED
   *
   * @return void
   */
  function setValue(CCDAED $inst) {
    $this->value = $inst;
  }

  /**
   * Getter value
   *
   * @return CCDAED
   */
  function getValue() {
    return $this->value;
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
   * @param CCDAPOCD_MT000040_Specimen $inst CCDAPOCD_MT000040_Specimen
   *
   * @return void
   */
  function appendSpecimen(CCDAPOCD_MT000040_Specimen $inst) {
    array_push($this->specimen, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListSpecimen() {
    $this->specimen = array();
  }

  /**
   * Getter specimen
   *
   * @return CCDAPOCD_MT000040_Specimen[]
   */
  function getSpecimen() {
    return $this->specimen;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Performer2 $inst CCDAPOCD_MT000040_Performer2
   *
   * @return void
   */
  function appendPerformer(CCDAPOCD_MT000040_Performer2 $inst) {
    array_push($this->performer, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListPerformer() {
    $this->performer = array();
  }

  /**
   * Getter performer
   *
   * @return CCDAPOCD_MT000040_Performer2[]
   */
  function getPerformer() {
    return $this->performer;
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
   * @param CCDAPOCD_MT000040_Participant2 $inst CCDAPOCD_MT000040_Participant2
   *
   * @return void
   */
  function appendParticipant(CCDAPOCD_MT000040_Participant2 $inst) {
    array_push($this->participant, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListParticipant() {
    $this->participant = array();
  }

  /**
   * Getter participant
   *
   * @return CCDAPOCD_MT000040_Participant2[]
   */
  function getParticipant() {
    return $this->participant;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_EntryRelationship $inst CCDAPOCD_MT000040_EntryRelationship
   *
   * @return void
   */
  function appendEntryRelationship(CCDAPOCD_MT000040_EntryRelationship $inst) {
    array_push($this->entryRelationship, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListEntryRelationship() {
    $this->entryRelationship = array();
  }

  /**
   * Getter entryRelationship
   *
   * @return CCDAPOCD_MT000040_EntryRelationship[]
   */
  function getEntryRelationship() {
    return $this->entryRelationship;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Reference $inst CCDAPOCD_MT000040_Reference
   *
   * @return void
   */
  function appendReference(CCDAPOCD_MT000040_Reference $inst) {
    array_push($this->reference, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListReference() {
    $this->reference = array();
  }

  /**
   * Getter reference
   *
   * @return CCDAPOCD_MT000040_Reference[]
   */
  function getReference() {
    return $this->reference;
  }

  /**
   * Ajoute l'instance spécifié dans le tableau
   *
   * @param CCDAPOCD_MT000040_Precondition $inst CCDAPOCD_MT000040_Precondition
   *
   * @return void
   */
  function appendPrecondition(CCDAPOCD_MT000040_Precondition $inst) {
    array_push($this->precondition, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListPrecondition() {
    $this->precondition = array();
  }

  /**
   * Getter precondition
   *
   * @return CCDAPOCD_MT000040_Precondition[]
   */
  function getPrecondition() {
    return $this->precondition;
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
    $ob = new CCDAActClassObservation();
    $ob->setData($inst);
    $this->classCode = $ob;
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
   * Setter moodCode
   *
   * @param String $inst String
   *
   * @return void
   */
  function setMoodCode($inst) {
    if (!$inst) {
      $this->moodCode = null;
      return;
    }
    $mood = new CCDAActMood();
    $mood->setData($inst);
    $this->moodCode = $mood;
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
    $props["typeId"]            = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                = "CCDAII xml|element";
    $props["languageCode"]      = "CCDACS xml|element max|1";
    $props["value"]             = "CCDAED xml|element required";
    $props["subject"]           = "CCDAPOCD_MT000040_Subject xml|element max|1";
    $props["specimen"]          = "CCDAPOCD_MT000040_Specimen xml|element";
    $props["performer"]         = "CCDAPOCD_MT000040_Performer2 xml|element";
    $props["author"]            = "CCDAPOCD_MT000040_Author xml|element";
    $props["informant"]         = "CCDAPOCD_MT000040_Informant12 xml|element";
    $props["participant"]       = "CCDAPOCD_MT000040_Participant2 xml|element";
    $props["entryRelationship"] = "CCDAPOCD_MT000040_EntryRelationship xml|element";
    $props["reference"]         = "CCDAPOCD_MT000040_Reference xml|element";
    $props["precondition"]      = "CCDAPOCD_MT000040_Precondition xml|element";
    $props["identifier"]        = "CCDA_base_uid xml|attribute";
    $props["classCode"]         = "CCDAActClassObservation xml|attribute required";
    $props["moodCode"]          = "CCDAActMood xml|attribute required";
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
     * Test avec un classCode incorrect
     */

    $this->setClassCode("TEST");
    $tabTest[] = $this->sample("Test avec un classCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("ALRT");
    $tabTest[] = $this->sample("Test avec un classCode correct", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un moodCode incorrect
     */

    $this->setMoodCode("TEST");
    $tabTest[] = $this->sample("Test avec un moodCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un moodCode correct
     */

    $this->setMoodCode("EVN");
    $tabTest[] = $this->sample("Test avec un moodCode correct", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un value incorrect
     */
    $ed = new CCDAED();
    $ed->setLanguage(" ");
    $this->setValue($ed);
    $tabTest[] = $this->sample("Test avec un value incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un value correct
     */

    $ed->setLanguage("TEST");
    $this->setValue($ed);
    $tabTest[] = $this->sample("Test avec un value correct", "Document valide");

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
     * Test avec un specimen correct
     */

    $spec = new CCDAPOCD_MT000040_Specimen();
    $specimen = new CCDAPOCD_MT000040_SpecimenRole();
    $specimen->setClassCode();
    $spec->setSpecimenRole($specimen);
    $this->appendSpecimen($spec);
    $tabTest[] = $this->sample("Test avec un specimen correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un performer2 correct
     */

    $perf2 = new CCDAPOCD_MT000040_Performer2();
    $assign = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.25.5");
    $assign->appendId($ii);
    $perf2->setAssignedEntity($assign);
    $this->appendPerformer($perf2);
    $tabTest[] = $this->sample("Test avec un performer correct", "Document valide");

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
     * Test avec un informant12 correct
     */

    $inf = new CCDAPOCD_MT000040_Informant12();
    $assigned = new CCDAPOCD_MT000040_AssignedEntity();
    $ii = new CCDAII();
    $ii->setRoot("1.2.5");
    $assigned->appendId($ii);
    $inf->setAssignedEntity($assigned);
    $this->appendInformant($inf);
    $tabTest[] = $this->sample("Test avec un informant correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un participant2 correct
     */

    $part = new CCDAPOCD_MT000040_Participant2();
    $partRole = new CCDAPOCD_MT000040_ParticipantRole();
    $partRole->setTypeId();
    $part->setParticipantRole($partRole);
    $part->setTypeCode("CST");
    $this->appendParticipant($part);
    $tabTest[] = $this->sample("Test avec un particpant correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un entryRelationship correct
     */

    $ent = new CCDAPOCD_MT000040_EntryRelationship();
    $ent->setTypeCode("COMP");

    $ac = new CCDAPOCD_MT000040_Act();
    $cd = new CCDACD();
    $cd->setCode("SYNTH");
    $ac->setCode($cd);
    $ac->setClassCode("ACT");
    $ac->setMoodCode("INT");
    $ent->setAct($ac);
    $this->appendEntryRelationship($ent);
    $tabTest[] = $this->sample("Test avec un entryRelationship correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un reference correct
     */

    $ref = new CCDAPOCD_MT000040_Reference();
    $eAct = new CCDAPOCD_MT000040_ExternalAct();
    $eAct->setMoodCode();
    $ref->setExternalAct($eAct);
    $ref->setTypeCode("SPRT");
    $this->appendReference($ref);
    $tabTest[] = $this->sample("Test avec un reference correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un precondition correct
     */

    $pre = new CCDAPOCD_MT000040_Precondition();
    $crit = new CCDAPOCD_MT000040_Criterion();
    $crit->setMoodCode();
    $pre->setCriterion($crit);
    $this->appendPrecondition($pre);
    $tabTest[] = $this->sample("Test avec un precondition correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un ID correct
     */

    $this->setIdentifier("TEST");
    $tabTest[] = $this->sample("Test avec un ID correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    return $tabTest;
  }
}