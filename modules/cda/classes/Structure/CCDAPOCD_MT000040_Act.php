<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Interop\Cda\Datatypes\Base\CCDA_base_bl;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_ActClassDocumentEntryAct;
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_DocumentActMood;
use Ox\Interop\Cda\Rim\CCDARIMAct;

/**
 * POCD_MT000040_Act Class
 */
class CCDAPOCD_MT000040_Act extends CCDARIMAct {

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
   * Setter effectiveTime
   *
   * @param CCDAIVL_TS $inst CCDAIVL_TS
   *
   * @return void
   */
  function setEffectiveTime(CCDAIVL_TS $inst) {
    $this->effectiveTime = $inst;
  }

  /**
   * Getter effectiveTime
   *
   * @return CCDAIVL_TS
   */
  function getEffectiveTime() {
    return $this->effectiveTime;
  }

  /**
   * Setter priorityCode
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function setPriorityCode(CCDACE $inst) {
    $this->priorityCode = $inst;
  }

  /**
   * Getter priorityCode
   *
   * @return CCDACE
   */
  function getPriorityCode() {
    return $this->priorityCode;
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
    $act = new CCDAx_ActClassDocumentEntryAct();
    $act->setData($inst);
    $this->classCode = $act;
  }

  /**
   * Getter classCode
   *
   * @return CCDAx_ActClassDocumentEntryAct
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
    $actM = new CCDAx_DocumentActMood();
    $actM->setData($inst);
    $this->moodCode = $actM;
  }

  /**
   * Getter moodCode
   *
   * @return CCDAx_DocumentActMood
   */
  function getMoodCode() {
    return $this->moodCode;
  }

  /**
   * Setter negationInd
   *
   * @param String $inst String
   *
   * @return void
   */
  function setNegationInd($inst) {
    if (!$inst) {
      $this->negationInd = null;
      return;
    }
    $bl = new CCDA_base_bl();
    $bl->setData($inst);
    $this->negationInd = $bl;
  }

  /**
   * Getter negationInd
   *
   * @return CCDA_base_bl
   */
  function getNegationInd() {
    return $this->negationInd;
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
    $props["code"]              = "CCDACD xml|element required";
    $props["text"]              = "CCDAED xml|element max|1";
    $props["statusCode"]        = "CCDACS xml|element max|1";
    $props["effectiveTime"]     = "CCDAIVL_TS xml|element max|1";
    $props["priorityCode"]      = "CCDACE xml|element max|1";
    $props["languageCode"]      = "CCDACS xml|element max|1";
    $props["subject"]           = "CCDAPOCD_MT000040_Subject xml|element max|1";
    $props["specimen"]          = "CCDAPOCD_MT000040_Specimen xml|element";
    $props["performer"]         = "CCDAPOCD_MT000040_Performer2 xml|element";
    $props["author"]            = "CCDAPOCD_MT000040_Author xml|element";
    $props["informant"]         = "CCDAPOCD_MT000040_Informant12 xml|element";
    $props["participant"]       = "CCDAPOCD_MT000040_Participant2 xml|element";
    $props["entryRelationship"] = "CCDAPOCD_MT000040_EntryRelationship xml|element";
    $props["reference"]         = "CCDAPOCD_MT000040_Reference xml|element";
    $props["precondition"]      = "CCDAPOCD_MT000040_Precondition xml|element";
    $props["classCode"]         = "CCDAx_ActClassDocumentEntryAct xml|attribute required";
    $props["moodCode"]          = "CCDAx_DocumentActMood xml|attribute required";
    $props["negationInd"]       = "CCDA_base_bl xml|attribute";
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
    $tabTest[] = $this->sample("Test avec un code correct", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode incorrect
     */

    $this->setClassCode("TEST");
    $tabTest[] = $this->sample("Test avec un classCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un classCode correct
     */

    $this->setClassCode("ACT");
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

    $this->setMoodCode("INT");
    $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un negationInd incorrect
     */

    $this->setNegationInd("TEST");
    $tabTest[] = $this->sample("Test avec un negationInd incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un negationInd correct
     */

    $this->setNegationInd("true");
    $tabTest[] = $this->sample("Test avec un negationInd correct", "Document valide");

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
     * Test avec un effectiveTime incorrect
     */

    $ivl_ts = new CCDAIVL_TS();
    $hi = new CCDAIVXB_TS();
    $hi->setValue("TESTTEST");
    $ivl_ts->setHigh($hi);
    $this->setEffectiveTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un effectiveTime incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un effectiveTime correct
     */

    $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
    $ivl_ts->setHigh($hi);
    $this->setEffectiveTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un effectiveTime correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un priorityCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->setPriorityCode($ce);
    $tabTest[] = $this->sample("Test avec un priorityCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un priorityCode correct
     */

    $ce->setCode("TEST");
    $this->setPriorityCode($ce);
    $tabTest[] = $this->sample("Test avec un priorityCode correct", "Document valide");

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

    return $tabTest;
  }
}