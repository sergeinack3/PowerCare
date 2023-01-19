<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Structure;

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAINT;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAST;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActClinicalDocument;
use Ox\Interop\Cda\Datatypes\Voc\CCDAActMood;
use Ox\Interop\Cda\Rim\CCDARIMDocument;

/**
 * POCD_MT000040_ClinicalDocument Class
 */
class CCDAPOCD_MT000040_ClinicalDocument extends CCDARIMDocument
{

    /** @var CCDAPOCD_MT000040_RecordTarget */
    public $recordTarget = [];
    /** @var CCDAPOCD_MT000040_Author */
    public $author = [];
    /** @var CCDAPOCD_MT000040_DataEnterer */
    public $dataEnterer;
    /** @var CCDAPOCD_MT000040_Informant12 */
    public $informant = [];
    /** @var CCDAPOCD_MT000040_Custodian */
    public $custodian;
    /** @var CCDAPOCD_MT000040_InformationRecipient */
    public $informationRecipient = [];
    /** @var CCDAPOCD_MT000040_LegalAuthenticator */
    public $legalAuthenticator;
    /** @var CCDAPOCD_MT000040_Authenticator */
    public $authenticator = [];
    /** @var CCDAPOCD_MT000040_Participant1 */
    public $participant = [];
    /** @var CCDAPOCD_MT000040_InFulfillmentOf */
    public $inFulfillmentOf = [];
    /** @var CCDAPOCD_MT000040_DocumentationOf */
    public $documentationOf = [];
    /** @var CCDAPOCD_MT000040_RelatedDocument */
    public $relatedDocument = [];
    /** @var CCDAPOCD_MT000040_Authorization */
    public $authorization = [];
    /** @var CCDAPOCD_MT000040_Component1 */
    public $componentOf;
    /** @var CCDAPOCD_MT000040_Component2 */
    public $component;

    /**
     * Getter id
     *
     * @return CCDAII
     */
    public function getId(): CCDAII
    {
        return $this->id;
    }

    /**
     * Getter code
     *
     * @return CCDACE
     */
    public function getCode(): CCDACE
    {
        return $this->code;
    }

    /**
     * Getter title
     *
     * @return CCDAST
     */
    public function getTitle(): CCDAST
    {
        return $this->title;
    }

    /**
     * Getter effectiveTime
     *
     * @return CCDATS
     */
    public function getEffectiveTime(): CCDATS
    {
        return $this->effectiveTime;
    }

    /**
     * Getter confidentialityCode
     *
     * @return CCDACE
     */
    public function getConfidentialityCode(): CCDACE
    {
        return $this->confidentialityCode;
    }

    /**
     * Getter languageCode
     *
     * @return CCDACS
     */
    public function getLanguageCode(): CCDACS
    {
        return $this->languageCode;
    }

    /**
     * Getter setId
     *
     * @return CCDAII
     */
    public function getSetId(): CCDAII
    {
        return $this->setId;
    }

    /**
     * Getter versionNumber
     *
     * @return CCDAINT
     */
    public function getVersionNumber(): CCDAINT
    {
        return $this->versionNumber;
    }

    /**
     * Getter copyTime
     *
     * @return CCDATS
     */
    public function getCopyTime(): CCDATS
    {
        return $this->copyTime;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListRecordTarget()
    {
        $this->recordTarget = [];
    }

    /**
     * Getter recordTarget
     *
     * @return CCDAPOCD_MT000040_RecordTarget[]
     */
    function getRecordTarget()
    {
        return $this->recordTarget;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListAuthor()
    {
        $this->author = [];
    }

    /**
     * Getter author
     *
     * @return CCDAPOCD_MT000040_Author[]
     */
    function getAuthor()
    {
        return $this->author;
    }

    /**
     * Getter dataEnterer
     *
     * @return CCDAPOCD_MT000040_DataEnterer
     */
    function getDataEnterer()
    {
        return $this->dataEnterer;
    }

    /**
     * Setter dataEnterer
     *
     * @param CCDAPOCD_MT000040_DataEnterer $inst CCDAPOCD_MT000040_DataEnterer
     *
     * @return void
     */
    function setDataEnterer(CCDAPOCD_MT000040_DataEnterer $inst)
    {
        $this->dataEnterer = $inst;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListInformant()
    {
        $this->informant = [];
    }

    /**
     * Getter informant
     *
     * @return CCDAPOCD_MT000040_Informant12[]
     */
    function getInformant()
    {
        return $this->informant;
    }

    /**
     * Getter custodian
     *
     * @return CCDAPOCD_MT000040_Custodian
     */
    function getCustodian()
    {
        return $this->custodian;
    }

    /**
     * Setter custodian
     *
     * @param CCDAPOCD_MT000040_Custodian $inst CCDAPOCD_MT000040_Custodian
     *
     * @return void
     */
    function setCustodian(CCDAPOCD_MT000040_Custodian $inst)
    {
        $this->custodian = $inst;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListInformationRecipient()
    {
        $this->informationRecipient = [];
    }

    /**
     * Getter informationRecipient
     *
     * @return CCDAPOCD_MT000040_InformationRecipient[]
     */
    function getInformationRecipient()
    {
        return $this->informationRecipient;
    }

    /**
     * Getter legalAuthenticator
     *
     * @return CCDAPOCD_MT000040_LegalAuthenticator
     */
    function getLegalAuthenticator()
    {
        return $this->legalAuthenticator;
    }

    /**
     * Setter legalAuthenticator
     *
     * @param CCDAPOCD_MT000040_LegalAuthenticator $inst CCDAPOCD_MT000040_LegalAuthenticator
     *
     * @return void
     */
    function setLegalAuthenticator(CCDAPOCD_MT000040_LegalAuthenticator $inst)
    {
        $this->legalAuthenticator = $inst;
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_Authenticator $inst CCDAPOCD_MT000040_Authenticator
     *
     * @return void
     */
    function appendAuthenticator(CCDAPOCD_MT000040_Authenticator $inst)
    {
        array_push($this->authenticator, $inst);
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListAuthenticator()
    {
        $this->authenticator = [];
    }

    /**
     * Getter authenticator
     *
     * @return CCDAPOCD_MT000040_Authenticator[]
     */
    function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListParticipant()
    {
        $this->participant = [];
    }

    /**
     * Getter participant
     *
     * @return CCDAPOCD_MT000040_Participant1[]
     */
    function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListInFulfillmentOf()
    {
        $this->inFulfillmentOf = [];
    }

    /**
     * Getter inFulfillmentOf
     *
     * @return CCDAPOCD_MT000040_InFulfillmentOf[]
     */
    function getInFulfillmentOf()
    {
        return $this->inFulfillmentOf;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListDocumentationOf()
    {
        $this->documentationOf = [];
    }

    /**
     * Getter documentationOf
     *
     * @return CCDAPOCD_MT000040_DocumentationOf[]
     */
    function getDocumentationOf()
    {
        return $this->documentationOf;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListRelatedDocument()
    {
        $this->relatedDocument = [];
    }

    /**
     * Getter relatedDocument
     *
     * @return CCDAPOCD_MT000040_RelatedDocument[]
     */
    function getRelatedDocument()
    {
        return $this->relatedDocument;
    }

    /**
     * Efface le tableau
     *
     * @return void
     */
    function resetListAuthorization()
    {
        $this->authorization = [];
    }

    /**
     * Getter authorization
     *
     * @return CCDAPOCD_MT000040_Authorization[]
     */
    function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * Getter componentOf
     *
     * @return CCDAPOCD_MT000040_Component1
     */
    function getComponentOf()
    {
        return $this->componentOf;
    }

    /**
     * Setter componentOf
     *
     * @param CCDAPOCD_MT000040_Component1 $inst CCDAPOCD_MT000040_Component1
     *
     * @return void
     */
    function setComponentOf(CCDAPOCD_MT000040_Component1 $inst)
    {
        $this->componentOf = $inst;
    }

    /**
     * Getter component
     *
     * @return CCDAPOCD_MT000040_Component2
     */
    function getComponent()
    {
        return $this->component;
    }

    /**
     * Setter component
     *
     * @param CCDAPOCD_MT000040_Component2 $inst CCDAPOCD_MT000040_Component2
     *
     * @return void
     */
    function setComponent(CCDAPOCD_MT000040_Component2 $inst)
    {
        $this->component = $inst;
    }

    /**
     * Getter classCode
     *
     * @return CCDAActClinicalDocument
     */
    function getClassCode()
    {
        return $this->classCode;
    }

    /**
     * Getter moodCode
     *
     * @return CCDAActMood
     */
    function getMoodCode()
    {
        return $this->moodCode;
    }

    /**
     * Retourne les propriétés
     *
     * @return array
     */
    function getProps()
    {
        $props                         = parent::getProps();
        $props["typeId"]               = "CCDAPOCD_MT000040_InfrastructureRoot_typeId xml|element required";
        $props["id"]                   = "CCDAII xml|element required";
        $props["code"]                 = "CCDACE xml|element required";
        $props["title"]                = "CCDAST xml|element max|1";
        $props["effectiveTime"]        = "CCDATS xml|element required";
        $props["confidentialityCode"]  = "CCDACE xml|element required";
        $props["languageCode"]         = "CCDACS xml|element max|1";
        $props["setId"]                = "CCDAII xml|element max|1";
        $props["versionNumber"]        = "CCDAINT xml|element max|1";
        $props["copyTime"]             = "CCDATS xml|element max|1";
        $props["recordTarget"]         = "CCDAPOCD_MT000040_RecordTarget xml|element min|1";
        $props["author"]               = "CCDAPOCD_MT000040_Author xml|element min|1";
        $props["dataEnterer"]          = "CCDAPOCD_MT000040_DataEnterer xml|element max|1";
        $props["informant"]            = "CCDAPOCD_MT000040_Informant12 xml|element";
        $props["custodian"]            = "CCDAPOCD_MT000040_Custodian xml|element required";
        $props["informationRecipient"] = "CCDAPOCD_MT000040_InformationRecipient xml|element";
        $props["legalAuthenticator"]   = "CCDAPOCD_MT000040_LegalAuthenticator xml|element max|1";
        $props["authenticator"]        = "CCDAPOCD_MT000040_Authenticator xml|element";
        $props["participant"]          = "CCDAPOCD_MT000040_Participant1 xml|element";
        $props["inFulfillmentOf"]      = "CCDAPOCD_MT000040_InFulfillmentOf xml|element";
        $props["documentationOf"]      = "CCDAPOCD_MT000040_DocumentationOf xml|element";
        $props["relatedDocument"]      = "CCDAPOCD_MT000040_RelatedDocument xml|element";
        $props["authorization"]        = "CCDAPOCD_MT000040_Authorization xml|element";
        $props["componentOf"]          = "CCDAPOCD_MT000040_Component1 xml|element max|1";
        $props["component"]            = "CCDAPOCD_MT000040_Component2 xml|element required";
        $props["classCode"]            = "CCDAActClinicalDocument xml|attribute fixed|DOCCLIN";
        $props["moodCode"]             = "CCDAActMood xml|attribute fixed|EVN";

        return $props;
    }

    /**
     * Fonction permettant de tester la classe
     *
     * @return array
     */
    function test()
    {
        $tabTest = parent::test();

        /**
         * Test avec deux templateId correcte
         */

        $ii = new CCDAII();
        $ii->setRoot("1.2.5");
        $this->appendTemplateId($ii);
        $tabTest[] = $this->sample("Test avec deux templateId correct", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec trois templateId correcte
         */

        $ii = new CCDAII();
        $ii->setRoot("1.2.5.6");
        $this->appendTemplateId($ii);
        $tabTest[] = $this->sample("Test avec trois templateId correct", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un author correcte
         */

        $auth = new CCDAPOCD_MT000040_Author();
        $ts   = new CCDATS();
        $ts->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
        $auth->setTime($ts);

        $assigned = new CCDAPOCD_MT000040_AssignedAuthor();
        $ii       = new CCDAII();
        $ii->setRoot("1.2.5");
        $assigned->appendId($ii);
        $auth->setAssignedAuthor($assigned);
        $this->appendAuthor($auth);
        $tabTest[] = $this->sample("Test avec un author correct", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un custodian correcte
         */

        $custo    = new CCDAPOCD_MT000040_Custodian();
        $assign   = new CCDAPOCD_MT000040_AssignedCustodian();
        $custoOrg = new CCDAPOCD_MT000040_CustodianOrganization();
        $ii       = new CCDAII();
        $ii->setRoot("1.25.2");
        $custoOrg->appendId($ii);
        $assign->setRepresentedCustodianOrganization($custoOrg);
        $custo->setAssignedCustodian($assign);
        $this->setCustodian($custo);
        $tabTest[] = $this->sample("Test avec un custodian correct", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un recordTarget correcte
         */

        $reco        = new CCDAPOCD_MT000040_RecordTarget();
        $rolePatient = new CCDAPOCD_MT000040_PatientRole();
        $ii          = new CCDAII();
        $ii->setRoot("1.2.250.1.213.1.1.9");
        $rolePatient->appendId($ii);
        $reco->setPatientRole($rolePatient);
        $this->appendRecordTarget($reco);
        $tabTest[] = $this->sample("Test avec un recordTarget correct", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un effectiveTime incorrect
         */

        $this->setEffectiveTime("TEST");
        $tabTest[] = $this->sample("Test avec un effectiveTime incorrect", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un effectiveTime correct
         */

        $this->setEffectiveTime(CMbDT::dateTime());
        $tabTest[] = $this->sample("Test avec un effectiveTime correct", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un component correcte
         */

        $comp   = new CCDAPOCD_MT000040_Component2();
        $nonXML = new CCDAPOCD_MT000040_NonXMLBody();
        $ed     = new CCDAED();
        $ed->setLanguage("TEST");
        $nonXML->setText($ed);
        $comp->setNonXMLBody($nonXML);
        $this->setComponent($comp);
        $tabTest[] = $this->sample("Test avec un component correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un classCode correcte
         */

        $this->setClassCode();
        $tabTest[] = $this->sample("Test avec un classCode correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un moodCode correcte
         */

        $this->setMoodCode();
        $tabTest[] = $this->sample("Test avec un moodCode correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un title correct
         */
        $this->setTitle("TEST");
        $tabTest[] = $this->sample("Test avec un title correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un languageCode incorrect
         */

        // $this->setLanguage prend un string !
        /*$cs = new CCDACS();
        $cs->setCode(" ");
        $this->setLanguageCode($cs);
        $tabTest[] = $this->sample("Test avec un languageCode incorrect", "Document invalide");*/

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un languageCode correct
         */

        /*$cs->setCode("TEST");
        $this->setLanguageCode($cs);
        $tabTest[] = $this->sample("Test avec un languageCode correct", "Document valide");*/

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un setId incorrect
         */
        $this->setSetId("4TESTTEST");
        $tabTest[] = $this->sample("Test avec un setId incorrect", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un setId correct
         */
        $this->setSetId("1.25.4");
        $tabTest[] = $this->sample("Test avec un setId correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un copyTime incorrect
         */

        $ts = new CCDATS();
        $ts->setValue("TEST");
        $this->setCopyTime($ts);
        $tabTest[] = $this->sample("Test avec un copyTime incorrect", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un copyTime correct
         */

        $ts->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
        $this->setCopyTime($ts);
        $tabTest[] = $this->sample("Test avec un copyTime correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un dataEnterer correct
         */

        $data   = new CCDAPOCD_MT000040_DataEnterer();
        $assign = new CCDAPOCD_MT000040_AssignedEntity();
        $ii     = new CCDAII();
        $ii->setRoot("1.2.5");
        $assign->appendId($ii);
        $data->setAssignedEntity($assign);
        $this->setDataEnterer($data);
        $tabTest[] = $this->sample("Test avec un dataEnterer correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un informant correct
         */

        $infor    = new CCDAPOCD_MT000040_Informant12();
        $assigned = new CCDAPOCD_MT000040_AssignedEntity();
        $ii       = new CCDAII();
        $ii->setRoot("1.2.5");
        $assigned->appendId($ii);
        $infor->setAssignedEntity($assigned);
        $this->appendInformant($infor);
        $tabTest[] = $this->sample("Test avec un informant correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un informationRecipient correct
         */

        $inforReci = new CCDAPOCD_MT000040_InformationRecipient();
        $inten     = new CCDAPOCD_MT000040_IntendedRecipient();
        $inten->setTypeId();
        $inforReci->setIntendedRecipient($inten);
        $this->appendInformationRecipient($inforReci);
        $tabTest[] = $this->sample("Test avec un informationRecipient correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un legalAuthenticator correct
         */

        $legal = new CCDAPOCD_MT000040_LegalAuthenticator();
        $ts    = new CCDATS();
        $ts->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
        $legal->setTime($ts);

        $cs = new CCDACS();
        $cs->setCode("TEST");
        $legal->setSignatureCode($cs);

        $assigned = new CCDAPOCD_MT000040_AssignedEntity();
        $ii       = new CCDAII();
        $ii->setRoot("1.2.5");
        $assigned->appendId($ii);
        $legal->setAssignedEntity($assigned);
        $tabTest[] = $this->sample("Test avec un legalAuthenticator correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un authenticator correct
         */

        $authen = new CCDAPOCD_MT000040_Authenticator();
        $ts     = new CCDATS();
        $ts->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
        $authen->setTime($ts);

        $cs = new CCDACS();
        $cs->setCode("TEST");
        $authen->setSignatureCode($cs);

        $assigned = new CCDAPOCD_MT000040_AssignedEntity();
        $ii       = new CCDAII();
        $ii->setRoot("1.2.5");
        $assigned->appendId($ii);
        $authen->setAssignedEntity($assigned);
        $tabTest[] = $this->sample("Test avec un authenticator correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un participant correct
         */

        $part       = new CCDAPOCD_MT000040_Participant1();
        $associated = new CCDAPOCD_MT000040_AssociatedEntity();
        $associated->setClassCode("RoleClassPassive");
        $part->setAssociatedEntity($associated);

        $part->setTypeCode("CST");
        $this->appendParticipant($part);
        $tabTest[] = $this->sample("Test avec un participant correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un inFulfillmentOf correct
         */

        $inFull = new CCDAPOCD_MT000040_InFulfillmentOf();
        $ord    = new CCDAPOCD_MT000040_Order();
        $ii     = new CCDAII();
        $ii->setRoot("1.2.5");
        $ord->appendId($ii);
        $inFull->setOrder($ord);
        $this->appendInFulfillmentOf($inFull);
        $tabTest[] = $this->sample("Test avec un inFulfillmentOf correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un documentationOf correct
         */

        $doc   = new CCDAPOCD_MT000040_DocumentationOf();
        $event = new CCDAPOCD_MT000040_ServiceEvent();
        $event->setMoodCode();
        $doc->setServiceEvent($event);
        $this->appendDocumentationOf($doc);
        $tabTest[] = $this->sample("Test avec un documentationOf correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un relatedDocument correct
         */

        $rela   = new CCDAPOCD_MT000040_RelatedDocument();
        $parent = new CCDAPOCD_MT000040_ParentDocument();
        $ii     = new CCDAII();
        $ii->setRoot("1.2.5");
        $parent->appendId($ii);
        $rela->setParentDocument($parent);
        $rela->setTypeCode("RPLC");
        $this->appendRelatedDocument($rela);
        $tabTest[] = $this->sample("Test avec un relatedDocument correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un authorization correct
         */

        $autho      = new CCDAPOCD_MT000040_Authorization();
        $pocConsent = new CCDAPOCD_MT000040_Consent();
        $cs         = new CCDACS();
        $cs->setCode(" ");
        $pocConsent->setStatusCode($cs);
        $autho->setConsent($pocConsent);
        $autho->setTypeCode();

        $cs->setCode("TEST");
        $pocConsent->setStatusCode($cs);
        $autho->setConsent($pocConsent);
        $this->appendAuthorization($autho);
        $tabTest[] = $this->sample("Test avec un authorization correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec un componentOf correct
         */

        $compOf = new CCDAPOCD_MT000040_Component1();
        $encou  = new CCDAPOCD_MT000040_EncompassingEncounter();
        $ivl_ts = new CCDAIVL_TS();
        $hi     = new CCDAIVXB_TS();
        $hi->setValue("75679245900741.869627871786625715081550660290154484483335306381809807748522068");
        $ivl_ts->setHigh($hi);
        $encou->setEffectiveTime($ivl_ts);
        $compOf->setEncompassingEncounter($encou);
        $this->setComponentOf($compOf);
        $tabTest[] = $this->sample("Test avec un componentOf correct", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        return $tabTest;
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_Author $inst CCDAPOCD_MT000040_Author
     *
     * @return void
     */
    function appendAuthor(CCDAPOCD_MT000040_Author $inst)
    {
        array_push($this->author, $inst);
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_RecordTarget $inst CCDAPOCD_MT000040_RecordTarget
     *
     * @return void
     */
    function appendRecordTarget(CCDAPOCD_MT000040_RecordTarget $inst)
    {
        array_push($this->recordTarget, $inst);
    }

    /**
     * Setter effectiveTime
     *
     * @param CCDATS $inst CCDATS
     *
     * @return void
     */
    public function setEffectiveTime(string $date_creation): void
    {
        $ts = new CCDATS();
        $ts->setValue($date_creation);

        $this->effectiveTime = $ts;
    }

    /**
     * Assigne classCode à DOCCLIN
     *
     * @return void
     */
    function setClassCode()
    {
        $actClinic = new CCDAActClinicalDocument();
        $actClinic->setData("DOCCLIN");
        $this->classCode = $actClinic;
    }

    /**
     * Assigne moodCode à EVN
     *
     * @return void
     */
    function setMoodCode()
    {
        $mood = new CCDAActMood();
        $mood->setData("EVN");
        $this->moodCode = $mood;
    }

    /**
     * Setter title
     *
     * @param string $title Title
     *
     * @return void
     */
    function setTitle(string $title)
    {
        $st    = new CCDAST();
        $title = CMbString::htmlSpecialChars($title);
        $st->setData($title);

        $this->title = $st;
    }

    /**
     * Setter languageCode
     *
     * @param string $langage Language
     *
     * @return void
     */
    public function setLanguageCode(string $langage): void
    {
        $cs = new CCDACS();
        $cs->setCode($langage);

        $this->languageCode = $cs;
    }

    /**
     * Setter setId
     *
     * @param string $id_cda_lot ID lot CDA
     *
     * @return void
     */
    public function setSetId(string $id_cda_lot): void
    {
        $ii = new CCDAII();
        $ii->setRoot($id_cda_lot);

        $this->setId = $ii;
    }

    /**
     * Setter copyTime
     *
     * @param CCDATS $inst CCDATS
     *
     * @return void
     */
    function setCopyTime(CCDATS $inst)
    {
        $this->copyTime = $inst;
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_Informant12 $inst CCDAPOCD_MT000040_Informant12
     *
     * @return void
     */
    function appendInformant(CCDAPOCD_MT000040_Informant12 $inst)
    {
        array_push($this->informant, $inst);
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_InformationRecipient $inst CCDAPOCD_MT000040_InformationRecipient
     *
     * @return void
     */
    function appendInformationRecipient(CCDAPOCD_MT000040_InformationRecipient $inst)
    {
        array_push($this->informationRecipient, $inst);
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_Participant1 $inst CCDAPOCD_MT000040_Participant1
     *
     * @return void
     */
    function appendParticipant(?CCDAPOCD_MT000040_Participant1 $inst)
    {
        array_push($this->participant, $inst);
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_InFulfillmentOf $inst CCDAPOCD_MT000040_InFulfillmentOf
     *
     * @return void
     */
    function appendInFulfillmentOf(CCDAPOCD_MT000040_InFulfillmentOf $inst)
    {
        array_push($this->inFulfillmentOf, $inst);
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_DocumentationOf $inst CCDAPOCD_MT000040_DocumentationOf
     *
     * @return void
     */
    function appendDocumentationOf(CCDAPOCD_MT000040_DocumentationOf $inst)
    {
        array_push($this->documentationOf, $inst);
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_RelatedDocument $inst CCDAPOCD_MT000040_RelatedDocument
     *
     * @return void
     */
    function appendRelatedDocument(CCDAPOCD_MT000040_RelatedDocument $inst)
    {
        array_push($this->relatedDocument, $inst);
    }

    /**
     * Ajoute l'instance spécifié dans le tableau
     *
     * @param CCDAPOCD_MT000040_Authorization $inst CCDAPOCD_MT000040_Authorization
     *
     * @return void
     */
    function appendAuthorization(CCDAPOCD_MT000040_Authorization $inst)
    {
        array_push($this->authorization, $inst);
    }

    /**
     * Setter id
     *
     * @param string $id_cda ID CDA
     *
     * @return void
     */
    public function setId(string $id_cda): void
    {
        $ii = new CCDAII();
        $ii->setRoot($id_cda);

        $this->id = $ii;
    }

    /**
     * Setter code
     *
     * @param array $code Code
     *
     * @return void
     */
    public function setCode(array $code): void
    {
        $ce = new CCDACE();
        $ce->setCode(CMbArray::get($code, "code"));
        $ce->setCodeSystem(CMbArray::get($code, "codeSystem"));
        $ce->setDisplayName(CMbArray::get($code, "displayName"));

        $this->code = $ce;
    }

    /**
     * Setter confidentialityCode
     *
     * @param CCDACE $inst CCDACE
     *
     * @return void
     */
    public function setConfidentialityCode(array $confidentialite): void
    {
        $ce = new CCDACE();
        $ce->setCode(CMbArray::get($confidentialite, "code"));
        $ce->setCodeSystem(CMbArray::get($confidentialite, "codeSystem"));
        $ce->setDisplayName(CMbArray::get($confidentialite, "displayName"));

        $this->confidentialityCode = $ce;
    }

    /**
     * Setter versionNumber
     *
     * @param int $version Version
     *
     * @return void
     */
    public function setVersionNumber(int $version): void
    {
        $int = new CCDAINT();
        $int->setValue($version);

        $this->versionNumber = $int;
    }
}
