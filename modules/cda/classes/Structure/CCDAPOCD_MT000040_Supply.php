<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;


use Ox\Interop\Cda\Datatypes\Base\CCDABL;
use Ox\Interop\Cda\Datatypes\Base\CCDACD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAPQ;
use Ox\Interop\Cda\Datatypes\Base\CCDASXCM_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVL_INT;
use Ox\Interop\Cda\Datatypes\Datatype\CCDAIVXB_INT;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClassSupply;
use Ox\Interop\Cda\Datatypes\Voc\CCDAx_DocumentSubstanceMood;
use Ox\Interop\Cda\Rim\CCDARIMSupply;

/**
 * POCD_MT000040_Supply Class
 */
class CCDAPOCD_MT000040_Supply extends CCDARIMSupply {

  /**
   * Creation de la classe
   */
  function __construct() {
    $this->setClassCode();
  }

  /**
   * @var CCDAPOCD_MT000040_Subject
   */
  public $subject;

  /**
   * @var CCDAPOCD_MT000040_Specimen[]
   */
  public $specimen = array();

  /**
   * @var CCDAPOCD_MT000040_Product
   */
  public $product;

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
   * @var CCDASXCM_TS[]
   */
  public $effectiveTime = array();

  /**
   * @var CCDAPOCD_MT000040_Precondition[]
   */
  public $precondition = array();

  /**
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Ajoute l'instance sp�cifi� dans le tableau
   *
   * @param CCDASXCM_TS $inst CCDASXCM_TS
   *
   * @return void
   */
  function appendEffectiveTime(CCDASXCM_TS $inst) {
    array_push($this->effectiveTime, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListEffectiveTime() {
    $this->effectiveTime = array();
  }

  /**
   * Getter effectiveTime
   *
   * @return CCDASXCM_TS[]
   */
  function getEffectiveTime() {
    return $this->effectiveTime;
  }

  /**
   * Ajoute l'instance sp�cifi� dans le tableau
   *
   * @param CCDACE $inst CCDACE
   *
   * @return void
   */
  function appendPriorityCode(CCDACE $inst) {
    array_push($this->priorityCode, $inst);
  }

  /**
   * Efface le tableau
   *
   * @return void
   */
  function resetListPriorityCode() {
    $this->priorityCode = array();
  }

  /**
   * Getter priorityCode
   *
   * @return CCDACE[]
   */
  function getPriorityCode() {
    return $this->priorityCode;
  }

  /**
   * Setter repeatNumber
   *
   * @param CCDAIVL_INT $inst CCDAIVL_INT
   *
   * @return void
   */
  function setRepeatNumber(CCDAIVL_INT $inst) {
    $this->repeatNumber = $inst;
  }

  /**
   * Getter repeatNumber
   *
   * @return CCDAIVL_INT
   */
  function getRepeatNumber() {
    return $this->repeatNumber;
  }

  /**
   * Setter independentInd
   *
   * @param CCDABL $inst CCDABL
   *
   * @return void
   */
  function setIndependentInd(CCDABL $inst) {
    $this->independentInd = $inst;
  }

  /**
   * Getter independentInd
   *
   * @return CCDABL
   */
  function getIndependentInd() {
    return $this->independentInd;
  }

  /**
   * Setter quantity
   *
   * @param CCDAPQ $inst CCDAPQ
   *
   * @return void
   */
  function setQuantity(CCDAPQ $inst) {
    $this->quantity = $inst;
  }

  /**
   * Getter quantity
   *
   * @return CCDAPQ
   */
  function getQuantity() {
    return $this->quantity;
  }

  /**
   * Setter expectedUseTime
   *
   * @param CCDAIVL_TS $inst CCDAIVL_TS
   *
   * @return void
   */
  function setExpectedUseTime(CCDAIVL_TS $inst) {
    $this->expectedUseTime = $inst;
  }

  /**
   * Getter expectedUseTime
   *
   * @return CCDAIVL_TS
   */
  function getExpectedUseTime() {
    return $this->expectedUseTime;
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
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Setter product
   *
   * @param CCDAPOCD_MT000040_Product $inst CCDAPOCD_MT000040_Product
   *
   * @return void
   */
  function setProduct(CCDAPOCD_MT000040_Product $inst) {
    $this->product = $inst;
  }

  /**
   * Getter product
   *
   * @return CCDAPOCD_MT000040_Product
   */
  function getProduct() {
    return $this->product;
  }

  /**
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Ajoute l'instance sp�cifi� dans le tableau
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
   * Assigne classCode � SPLY
   *
   * @return void
   */
  function setClassCode() {
    $actClass = new CCDAActClassSupply();
    $actClass->setData("SPLY");
    $this->classCode = $actClass;
  }

  /**
   * Getter classCode
   *
   * @return CCDAActClassSupply
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
    $doc= new CCDAx_DocumentSubstanceMood();
    $doc->setData($inst);
    $this->moodCode = $doc;
  }

  /**
   * Getter moodCode
   *
   * @return CCDAx_DocumentSubstanceMood
   */
  function getMoodCode() {
    return $this->moodCode;
  }


  /**
   * Retourne les propri�t�s
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["typeId"]            = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element max|1";
    $props["id"]                = "CCDAII xml|element";
    $props["code"]              = "CCDACD xml|element max|1";
    $props["text"]              = "CCDAED xml|element max|1";
    $props["statusCode"]        = "CCDACS xml|element max|1";
    $props["effectiveTime"]     = "CCDASXCM_TS xml|element";
    $props["priorityCode"]      = "CCDACE xml|element";
    $props["repeatNumber"]      = "CCDAIVL_INT xml|element max|1";
    $props["independentInd"]    = "CCDABL xml|element max|1";
    $props["quantity"]          = "CCDAPQ xml|element max|1";
    $props["expectedUseTime"]   = "CCDAIVL_TS xml|element max|1";
    $props["subject"]           = "CCDAPOCD_MT000040_Subject xml|element max|1";
    $props["specimen"]          = "CCDAPOCD_MT000040_Specimen xml|element";
    $props["product"]           = "CCDAPOCD_MT000040_Product xml|element max|1";
    $props["performer"]         = "CCDAPOCD_MT000040_Performer2 xml|element";
    $props["author"]            = "CCDAPOCD_MT000040_Author xml|element";
    $props["informant"]         = "CCDAPOCD_MT000040_Informant12 xml|element";
    $props["participant"]       = "CCDAPOCD_MT000040_Participant2 xml|element";
    $props["entryRelationship"] = "CCDAPOCD_MT000040_EntryRelationship xml|element";
    $props["reference"]         = "CCDAPOCD_MT000040_Reference xml|element";
    $props["precondition"]      = "CCDAPOCD_MT000040_Precondition xml|element";
    $props["classCode"]         = "CCDAActClassSupply xml|attribute required fixed|SPLY";
    $props["moodCode"]          = "CCDAx_DocumentSubstanceMood xml|attribute required";
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
     * Test avec un moodCode incorrect
     */

    $this->setMoodCode("TEST");
    $tabTest[] = $this->sample("Test avec un moodCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un moodCode correct
     */

    $this->setMoodCode("EVN");
    $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

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
     * Test avec un priorityCode incorrect
     */

    $ce = new CCDACE();
    $ce->setCode(" ");
    $this->appendPriorityCode($ce);
    $tabTest[] = $this->sample("Test avec un priorityCode incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un priorityCode correct
     */

    $ce->setCode("1.2.5");
    $this->resetListPriorityCode();
    $this->appendPriorityCode($ce);
    $tabTest[] = $this->sample("Test avec un priorityCode correct", "Document valide");

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
     * Test avec un quantity incorrect
     */

    $pq = new CCDAPQ();
    $pq->setValue("test");
    $this->setQuantity($pq);
    $tabTest[] = $this->sample("Test avec un quantity incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un quantity correct
     */

    $pq->setValue("10.25");
    $this->setQuantity($pq);
    $tabTest[] = $this->sample("Test avec un quantity correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un independentInd incorrect
     */

    $bl = new CCDABL();
    $bl->setValue("TEST");
    $this->setIndependentInd($bl);
    $tabTest[] = $this->sample("Test avec un independentInd incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un independentInd correct
     */

    $bl->setValue("true");
    $this->setIndependentInd($bl);
    $tabTest[] = $this->sample("Test avec un independentInd correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un repeatNumber incorrect
     */

    $ivlInt = new CCDAIVL_INT();
    $hi = new CCDAIVXB_INT();
    $hi->setInclusive("TESTTEST");
    $ivlInt->setHigh($hi);
    $this->setRepeatNumber($ivlInt);
    $tabTest[] = $this->sample("Test avec un repeatNumber incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un repeatNumber correct
     */

    $hi->setInclusive("true");
    $ivlInt->setHigh($hi);
    $this->setRepeatNumber($ivlInt);
    $tabTest[] = $this->sample("Test avec un repeatNumber correct", "Document valide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un expectedUseTime incorrect
     */

    $ivl_ts = new CCDAIVL_TS();
    $hi = new CCDAIVXB_TS();
    $hi->setValue("TESTTEST");
    $ivl_ts->setHigh($hi);
    $this->setExpectedUseTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un expectedUseTime incorrect", "Document invalide");

    /*-------------------------------------------------------------------------------------*/

    /**
     * Test avec un expectedUseTime correct
     */

    $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
    $ivl_ts->setHigh($hi);
    $this->setExpectedUseTime($ivl_ts);
    $tabTest[] = $this->sample("Test avec un expectedUseTime correct", "Document valide");

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
     * Test avec un product correct
     */

    $prod = new CCDAPOCD_MT000040_Product();
    $manu = new CCDAPOCD_MT000040_ManufacturedProduct();
    $mat = new CCDAPOCD_MT000040_Material();
    $mat->setClassCode();
    $manu->setManufacturedMaterial($mat);
    $prod->setManufacturedProduct($manu);
    $this->setProduct($prod);
    $tabTest[] = $this->sample("Test avec un product correct", "Document valide");

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