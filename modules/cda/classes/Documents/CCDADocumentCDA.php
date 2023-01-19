<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Documents;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CPerson;
use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDADomDocument;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Meta\CDAMetaAddress;
use Ox\Interop\Cda\Components\Meta\CDAMetaAssignedAuthor;
use Ox\Interop\Cda\Components\Meta\CDAMetaAssignedEntity;
use Ox\Interop\Cda\Components\Meta\CDAMetaAuthor;
use Ox\Interop\Cda\Components\Meta\CDAMetaPatientRole;
use Ox\Interop\Cda\Components\Meta\CDAMetaTelecom;
use Ox\Interop\Cda\Datatypes\Base\CCDA_adxp_streetAddressLine;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_family;
use Ox\Interop\Cda\Datatypes\Base\CCDA_en_given;
use Ox\Interop\Cda\Datatypes\Base\CCDAAD;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDACS;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDAII;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVL_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAIVXB_TS;
use Ox\Interop\Cda\Datatypes\Base\CCDAON;
use Ox\Interop\Cda\Datatypes\Base\CCDAPN;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Datatypes\Base\CCDATS;
use Ox\Interop\Cda\Levels\Level1\CCDADmp;
use Ox\Interop\Cda\Levels\Level1\CCDALevel1;
use Ox\Interop\Cda\Levels\Level3\CCDALevel3;
use Ox\Interop\Cda\Rim\CCDARIMRole;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedAuthor;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedCustodian;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_AssignedEntity;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Authenticator;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ClinicalDocument;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component1;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component2;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Custodian;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_CustodianOrganization;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_DocumentationOf;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_EncompassingEncounter;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_HealthCareFacility;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Informant12;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_InformationRecipient;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_IntendedRecipient;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_LegalAuthenticator;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Location;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_NonXMLBody;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ParentDocument;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Performer1;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Person;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_RecordTarget;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_RelatedDocument;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_RelatedEntity;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ResponsibleParty;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_ServiceEvent;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_StructuredBody;
use Ox\Interop\Eai\CItemReport;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDestinataireItem;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Loinc\CLoinc;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Regroupe les fonctions pour créer un document CDA
 */
class CCDADocumentCDA
{
    /** @var CCDAFactory */
    protected $factory;

    /**
     * CCDADocumentCDA constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Création du CDA
     *
     * @param CCDAFactory $cda_factory cda factory
     *
     * @return CCDAPOCD_MT000040_ClinicalDocument
     */
    public function generateDocument(): CCDADomDocument
    {
        $clinical_document = $this->prepareDocument();
        $dom               = $clinical_document->toXML("ClinicalDocument", "urn:hl7-org:v3");

        $dom->purgeEmptyElements();

        return $dom;
    }

    /**
     * @return CCDAPOCD_MT000040_ClinicalDocument
     */
    protected function prepareDocument(): CCDAPOCD_MT000040_ClinicalDocument
    {
        $clinical_document = new CCDAPOCD_MT000040_ClinicalDocument();

        /**
         * Création de l'entête
         */
        $this->generateMetadata($clinical_document);

        /**
         * Déclaration Template
         */
        $this->generateTemplatesId($clinical_document);

        /**
         * Création des éléments obligatoire constituant le document
         */
        $this->generateComponents($clinical_document);

        return $clinical_document;
    }

    /**
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return CCDAPOCD_MT000040_ClinicalDocument
     */
    protected function generateComponents(CCDAPOCD_MT000040_ClinicalDocument $document
    ): CCDAPOCD_MT000040_ClinicalDocument {
        //Ajout des patients
        $this->setRecordTarget($document);

        //Ajout de l'établissement
        $this->setCustodian($document);

        // Ajout des informants
        $this->setInformants($document);

        // Ajout des destinataires du document
        $this->setInformationRecipients($document);

        //Ajout des auteurs
        $this->setAuthor($document);

        //Ajout de l'auteur legal
        $this->setLegalAuthenticator($document);

        //Ajout de l'authenticator
        $this->setAuthenticator($document);

        //Ajout des actes médicaux(ccam et cim10)
        $this->setDocumentationOf($document);

        //Ajout de la rencontre(Contexte : séjour, consultation, opération)
        $this->setComponentOf($document);

        //Ajout du document parent
        $this->setRelatedDocument($document);

        /**
         * Création du corp du document
         */
        $document->setComponent($this->buildComponents());

        return $document;
    }

    /**
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return CCDAPOCD_MT000040_ClinicalDocument
     */
    public function generateMetadata(CCDAPOCD_MT000040_ClinicalDocument $document
    ): CCDAPOCD_MT000040_ClinicalDocument {
        $factory = $this->factory;

        // Création de l'Id du document
        $document->setId($factory->id_cda);

        // Création du typeId
        $document->setTypeId();

        // Ajout du realmCode FR
        $document->setRealmCode($factory->realm_code);

        // Ajout du code langage fr-FR
        $document->setLanguageCode($factory->langage);

        // Ajout de la confidentialité du document
        $document->setConfidentialityCode($factory->confidentialite);

        // Ajout de la date de création du document
        $document->setEffectiveTime($factory->date_creation);

        // Ajout du numéro de version
        $document->setVersionNumber($factory->version);

        // Ajout de l'identifiant du lot
        $document->setSetId($factory->id_cda_lot);

        // Ajout du nom du document
        $document->setTitle($factory->nom);

        // Ajout du code du document (Jeux de valeurs)
        $document->setCode($factory->code);

        return $document;
    }

    /**
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return CCDAPOCD_MT000040_ClinicalDocument
     */
    public function generateTemplatesId(CCDAPOCD_MT000040_ClinicalDocument $document
    ): CCDAPOCD_MT000040_ClinicalDocument {
        // Conformité HL7
        $templates_id = $this->factory->templateId;
        foreach ($templates_id as $template_id) {
            $document->appendTemplateId($template_id);
        }

        return $document;
    }

    /**
     * @return CCDAPOCD_MT000040_Component2
     * @throws CMbException
     */
    protected function buildComponents(): CCDAPOCD_MT000040_Component2
    {
        $component2 = new CCDAPOCD_MT000040_Component2();
        if ($this->factory instanceof CCDALevel1) {
            $component2->setNonXMLBody($this->buildComponentsLevel1());
        } else {
            $component2->setStructuredBody($this->buildComponentsLevel3());
        }

        return $component2;
    }

    /**
     * Création d'un corps non structuré
     *
     * @return CCDAPOCD_MT000040_NonXMLBody
     * @throws CMbException
     */
    protected function buildComponentsLevel1(): CCDAPOCD_MT000040_NonXMLBody
    {
        /** @var CCDALevel1 $factory */
        $factory = $this->factory;

        $nonXMLBody = new CCDAPOCD_MT000040_NonXMLBody();
        $ed         = new CCDAED();
        $ed->setMediaType($factory->mediaType);
        $ed->setRepresentation("B64");
        if (!$file = $factory->file_path) {
            throw new CMbException("Aucun fichier renseigné");
        }
        $ed->setData(base64_encode(file_get_contents($file)));

        $nonXMLBody->setText($ed);

        return $nonXMLBody;
    }

    /**
     * @return CCDAPOCD_MT000040_StructuredBody
     */
    protected function buildComponentsLevel3(): CCDAPOCD_MT000040_StructuredBody
    {
        return new CCDAPOCD_MT000040_StructuredBody();
    }

    /**
     * Création de l'auteur
     *
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return void
     */
    protected function setAuthor(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $options = [
            'time' => $this->factory->date_author,
        ];

        $mbObject = $this->factory->mbObject;
        if ($mbObject instanceof CDocumentItem) {
            $author_of = $this->factory->targetObject;
        }

        $doctor          = $this->factory->author;
        $assigned_author = $this->getAssignedAuthor($doctor);
        $author          = (new CDAMetaAuthor($this->factory, $doctor, $options))
            ->setAssignedAuthor($assigned_author)
            ->setAuthorOf($author_of ?? null)
            ->build();

        $document->appendAuthor($author);
    }

    /**
     * Création d'un custodian
     *
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return void
     * @throws Exception
     */
    protected function setCustodian(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $custodian = new CCDAPOCD_MT000040_Custodian();

        // assigned custodian
        $assignedCustodian = new CCDAPOCD_MT000040_AssignedCustodian();

        // custodian organization
        $custodian_organization = $this->getCustodianOrganization();
        $assignedCustodian->setRepresentedCustodianOrganization($custodian_organization);

        $custodian->setAssignedCustodian($assignedCustodian);

        $document->setCustodian($custodian);
    }


    /**
     * Création des informants
     *
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return void
     * @throws Exception
     */
    protected function setInformationRecipients(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $mbObject = $this->factory->mbObject;

        if (!$mbObject instanceof CCompteRendu) {
            return;
        }

        $destinataires = $mbObject->loadRefsDestinataires();

        /** @var CDestinataireItem $_destinataire */
        foreach ($destinataires as $_destinataire) {
            $destinataire = $_destinataire->loadRefDestinataire();

            if (!$destinataire instanceof CMediusers && !$destinataire instanceof CMedecin) {
                continue;
            }

            if (!$destinataire || !$destinataire->_id || !$destinataire->_p_last_name) {
                continue;
            }

            if (!$destinataire->rpps && !$destinataire->adeli) {
                continue;
            }

            $this->setInformationRecipient($document, $destinataire);
        }
    }

    protected function setInformationRecipient(
        CCDAPOCD_MT000040_ClinicalDocument $document,
        CPerson $destinataire
    ): void {
        $information_recipient = new CCDAPOCD_MT000040_InformationRecipient();

        // IntendedRecipient
        $intended_recipient = new CCDAPOCD_MT000040_IntendedRecipient();

        // IDs
        $this->setIdPs($intended_recipient, $destinataire);

        // Addr
        $ad = $this->setAddress($destinataire);
        $intended_recipient->appendAddr($ad);

        // Telecom
        $this->setTelecomForPerson($intended_recipient, $destinataire);

        // InformationRecipient (given name, family name, etc)
        $information_recipient_name = new CCDAPOCD_MT000040_Person();

        $pn = $this->setName($destinataire);
        $information_recipient_name->appendName($pn);

        $intended_recipient->setInformationRecipient($information_recipient_name);

        $information_recipient->setIntendedRecipient($intended_recipient);

        $document->appendInformationRecipient($information_recipient);
    }

    /**
     * Création de l'adresse de la personne passé en paramètre
     *
     * @param CPerson $user CPerson
     *
     * @return CCDAPN
     */
    protected function setName(CPerson $person): CCDAPN
    {
        $pn   = new CCDAPN();
        $enxp = new CCDA_en_family();
        $enxp->setData($person->_p_last_name);
        $pn->append("family", $enxp);
        $enxp = new CCDA_en_given();
        $enxp->setData($person->_p_first_name);
        $pn->append("given", $enxp);

        return $pn;
    }

    /**
     * Création des informants
     *
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     *
     * @return void
     * @throws Exception
     */
    protected function setInformants(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $patient = $this->factory->patient;
        $ds      = $patient->getDS();

        // Ajout personne à prévenir et personne de confiance
        $relations = ['prevenir', 'confiance'];

        foreach ($relations as $_relation) {
            $corres = new CCorrespondantPatient();
            $where  = [];

            $where['relation']   = $ds->prepare("= ?", $_relation);
            $where['patient_id'] = $ds->prepare("= ?", $patient->_id);
            $where[]             = " 'date_fin' IS NULL OR 'date_fin' > '" . CMbDT::date() . "'";

            $correspondants = $corres->loadList($where, 'date_fin DESC');

            if (!$correspondants) {
                continue;
            }

            $this->setInformant($document, reset($correspondants));
        }
    }

    /**
     * Ajout d'un informant au document
     *
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     * @param CCorrespondantPatient              $corres
     *
     * @return void
     * @throws Exception
     */
    protected function setInformant(CCDAPOCD_MT000040_ClinicalDocument $document, CCorrespondantPatient $corres): void
    {
        $informant = new CCDAPOCD_MT000040_Informant12();

        $relatedEntity = new CCDAPOCD_MT000040_RelatedEntity();

        if ($corres->relation == 'prevenir') {
            $relatedEntity->setClassCode('ECON');
        } elseif ($corres->relation == 'confiance') {
            $relatedEntity->setClassCode('NOK');
        }

        // Code
        $entries = CANSValueSet::loadEntriesByDisplayName(
            'roleCode',
            CAppUI::tr(
                'CCorrespondantPatient.parente.' . $corres->parente
            )
        );
        if (CMbArray::get($entries, 'code')) {
            $ccdace = new CCDACE();
            $ccdace->setCode(CMbArray::get($entries, 'code'));
            $ccdace->setCodeSystem(CMbArray::get($entries, 'codeSystem'));
            $ccdace->setDisplayName(CMbArray::get($entries, 'displayName'));
            $relatedEntity->setCode($ccdace);
        }

        // Addr
        if ($corres->adresse && $corres->cp && $corres->ville) {
            $ad     = new CCDAAD();
            $street = new CCDA_adxp_streetAddressLine();
            $street->setData($corres->adresse);
            $ad->append("streetAddressLine", $street);

            $street2 = new CCDA_adxp_streetAddressLine();
            $street2->setData($corres->cp . " " . $corres->ville);
            $ad->append("streetAddressLine", $street2);

            $relatedEntity->appendAddr($ad);
        }

        // Telecom & Mail
        $this->setTelecomForPerson($relatedEntity, $corres);

        // RelatedPerson
        $relatedPerson = new CCDAPOCD_MT000040_Person();
        $pn            = new CCDAPN();
        $enxp          = new CCDA_en_family();
        $enxp->setData($corres->nom);
        $pn->append("family", $enxp);
        $enxp = new CCDA_en_given();
        $enxp->setData($corres->prenom);
        $pn->append("given", $enxp);
        $relatedPerson->appendName($pn);

        $relatedEntity->setRelatedPerson($relatedPerson);
        $informant->setRelatedEntity($relatedEntity);
        $document->appendInformant($informant);
    }

    /**
     * Set telecom for person
     *
     * @param CCDARIMRole $relatedEntity
     * @param CPerson     $person
     */
    protected function setTelecomForPerson(CCDARIMRole $relatedEntity, CPerson $person)
    {
        $telecoms = array_filter(
            [
                CDAMetaTelecom::TYPE_TELECOM_EMAIL  => $person->_p_email,
                CDAMetaTelecom::TYPE_TELECOM_MOBILE => $person->_p_mobile_phone_number,
                CDAMetaTelecom::TYPE_TELECOM_TEL    => $person->_p_phone_number,
            ]
        );
        foreach ($telecoms ?: [CDAMetaTelecom::TYPE_TELECOM_TEL => null] as $type => $value) {
            $telecom = (new CDAMetaTelecom($this->factory, $person, $type))->build();
            $relatedEntity->appendTelecom($telecom);
        }
    }

    /**
     * Création de documentationOf
     *
     * @return void
     */
    protected function setDocumentationOf(CCDAPOCD_MT000040_ClinicalDocument $document)
    {
        $documentationOf = new CCDAPOCD_MT000040_DocumentationOf();
        $documentationOf->setServiceEvent($this->getServiceEvent());

        $document->appendDocumentationOf($documentationOf);
    }

    /**
     * création d'un custodianOrgnaization
     *
     * @return CCDAPOCD_MT000040_CustodianOrganization
     * @throws Exception
     */
    protected function getCustodianOrganization()
    {
        $mbObject = $this->factory->targetObject;

        if ($mbObject instanceof CSejour) {
            $etablissement = $mbObject->loadRefEtablissement();
        } elseif ($mbObject instanceof CConsultation) {
            $etablissement = $mbObject->loadRefPraticien()->loadRefFunction()->loadRefGroup();
        } elseif ($mbObject instanceof COperation) {
            $etablissement = $mbObject->loadRefSejour()->loadRefEtablissement();
        } elseif ($mbObject instanceof CDocumentItem) {
            $target = $mbObject->loadTargetObject();

            if ($target instanceof CConsultation) {
                $etablissement = $target->loadRefGroup();
            } elseif ($target instanceof CSejour) {
                $etablissement = $target->loadRefEtablissement();
            } elseif ($mbObject instanceof COperation) {
                $etablissement = $target->loadRefSejour()->loadRefEtablissement();
            } else {
                $etablissement = CGroups::loadCurrent();
            }
        } else {
            $etablissement = CGroups::loadCurrent();
        }

        $custOrg = new CCDAPOCD_MT000040_CustodianOrganization();
        $this->setIdEtablissement($custOrg, $etablissement);

        $name = $etablissement->_name;

        $on = new CCDAON();
        $on->setData($name);
        $custOrg->setName($on);

        $tel = new CCDATEL();
        $etablissement->tel ? $tel->setValue("tel:$etablissement->tel") : $tel->setNullFlavor('UNK');
        $custOrg->setTelecom($tel);

        $ad = new CCDAAD();
        if (!$etablissement->adresse && !$etablissement->cp && !$etablissement->ville) {
            $ad->setNullFlavor('UNK');
        } else {
            $street = new CCDA_adxp_streetAddressLine();
            $street->setData($etablissement->adresse);
            $ad->append("streetAddressLine", $street);

            $street2 = new CCDA_adxp_streetAddressLine();
            $street2->setData($etablissement->cp . " " . $etablissement->ville);
            $ad->append("streetAddressLine", $street2);
        }

        $custOrg->setAddr($ad);

        return $custOrg;
    }

    /**
     * Création d'une personne
     *
     * @param CMediUsers $mediUser CMediUsers
     *
     * @return CCDAPOCD_MT000040_Person
     */
    protected function getPerson(CMediusers $mediUser)
    {
        $person = new CCDAPOCD_MT000040_Person();

        $pn = new CCDAPN();

        $enxp = new CCDA_en_family();
        $enxp->setData($mediUser->_p_last_name);
        $pn->append("family", $enxp);

        $enxp = new CCDA_en_given();
        $enxp->setData($mediUser->_p_first_name);
        $pn->append("given", $enxp);

        $person->appendName($pn);

        return $person;
    }

    /**
     * Création du recordTarget
     */
    protected function setRecordTarget(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $record       = new CCDAPOCD_MT000040_RecordTarget();
        $patient_role = (new CDAMetaPatientRole($this->factory, $this->factory->patient))->build();
        $record->setPatientRole($patient_role);

        $document->appendRecordTarget($record);
    }

    /**
     * Création d'un legalAuthenticator
     *
     * @return void
     */
    protected function setLegalAuthenticator(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $date_signature     = $this->factory->date_creation;
        $legalAuthenticator = new CCDAPOCD_MT000040_LegalAuthenticator();

        // Ts
        $ts = new CCDATS();
        $ts->setValue($date_signature);
        $legalAuthenticator->setTime($ts);

        // Cs
        $cs = new CCDACS();
        $cs->setCode("S");
        $legalAuthenticator->setSignatureCode($cs);

        $legalAuthenticator->setAssignedEntity($this->getAssignedEntity($this->factory->author));

        $document->setLegalAuthenticator($legalAuthenticator);
    }

    /**
     * Création d'un legalAuthenticator
     *
     * @return void
     */
    protected function setAuthenticator(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $date_attestation = $this->factory->date_creation;
        $authenticator    = new CCDAPOCD_MT000040_Authenticator();

        // Ts
        $ts = new CCDATS();
        $ts->setValue($date_attestation);
        $authenticator->setTime($ts);

        // Cs
        $cs = new CCDACS();
        $cs->setCode("S");
        $authenticator->setSignatureCode($cs);

        $authenticator->setAssignedEntity($this->getAssignedEntity($this->factory->author));

        $document->appendAuthenticator($authenticator);
    }

    /**
     * Création de la location
     *
     * @return CCDAPOCD_MT000040_Location
     */
    protected function getLocation(): CCDAPOCD_MT000040_Location
    {
        $location = new CCDAPOCD_MT000040_Location();

        $location->setHealthCareFacility($this->getHealthCareFacility());

        return $location;
    }

    /**
     * Création du performer
     *
     * @param CMediusers $praticien praticien
     *
     * @return CCDAPOCD_MT000040_Performer1
     */
    protected function getPerformer(CMediusers $praticien): CCDAPOCD_MT000040_Performer1
    {
        $performer = new CCDAPOCD_MT000040_Performer1();
        $performer->setTypeCode("PRF");
        $performer->setAssignedEntity($this->getAssignedEntity($praticien, true));

        return $performer;
    }

    /**
     * Création de l'adresse de la personne passé en paramètre
     *
     * @param CPerson $user CPerson
     *
     * @return CCDAAD
     */
    function setAddress($user)
    {
        $userCity          = $user->_p_city;
        $userPostalCode    = $user->_p_postal_code;
        $userStreetAddress = $user->_p_street_address;

        $ad = new CCDAAD();
        if (!$userCity && !$userPostalCode && !$userStreetAddress) {
            $ad->setNullFlavor("NASK");

            return $ad;
        }

        $ad->setUse(['H']);

        $addresses = preg_split("#[\t\n\v\f\r]+#", $userStreetAddress, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($addresses as $_addr) {
            $street = new CCDA_adxp_streetAddressLine();
            $street->setData($_addr);
            $ad->append("streetAddressLine", $street);
        }

        $street2 = new CCDA_adxp_streetAddressLine();
        $street2->setData($userPostalCode . " " . $userCity);
        $ad->append("streetAddressLine", $street2);

        return $ad;
    }

    /**
     * Retourne le code associé à la situation familiale
     *
     * @param String $status String
     *
     * @return CCDACE
     */
    function getMaritalStatus($status)
    {
        $ce = new CCDACE();
        $ce->setCodeSystem("1.3.6.1.4.1.21367.100.1");
        switch ($status) {
            case "S":
                $ce->setCode("S");
                $ce->setDisplayName("Célibataire");
                break;
            case "M":
                $ce->setCode("M");
                $ce->setDisplayName("Marié");
                break;
            case "G":
                $ce->setCode("G");
                $ce->setDisplayName("Concubin");
                break;
            case "D":
                $ce->setCode("D");
                $ce->setDisplayName("Divorcé");
                break;
            case "W":
                $ce->setCode("W");
                $ce->setDisplayName("Veuf/Veuve");
                break;
            case "A":
                $ce->setCode("A");
                $ce->setDisplayName("Séparé");
                break;
            case "P":
                $ce->setCode("P");
                $ce->setDisplayName("Pacte civil de solidarité (PACS)");
                break;
            default:
                $ce->setCode("U");
                $ce->setDisplayName("Inconnu");
        }

        return $ce;
    }

    /**
     * Retourne le code associé au sexe de la personne
     *
     * @param String $sexe String
     *
     * @return CCDACE
     */
    function getAdministrativeGenderCode($sexe)
    {
        $ce = new CCDACE();
        $ce->setCode(mb_strtoupper($sexe));
        $ce->setCodeSystem("2.16.840.1.113883.5.1");
        switch ($sexe) {
            case "f":
                $ce->setDisplayName("Féminin");
                break;
            case "m":
                $ce->setDisplayName("Masculin");
                break;
            default:
                $ce->setCode("U");
                $ce->setDisplayName("Inconnu");
        }

        return $ce;
    }

    /**
     * Attribution de l'id au PS
     *
     * @param Object           $assigned Object
     * @param CUser|CMediUsers $user     CUser|CMediUsers
     *
     * @return void
     */
    function setIdPs($assigned, $user)
    {
        $factory = $this->factory;

        if (!$user->adeli && !$user->rpps) {
            $factory->report->addData(
                CAppUI::tr('CReport-msg-Doctor doesnt have RPPS and ADELI'),
                CItemReport::SEVERITY_ERROR
            );

            return;
        }

        if ($user->rpps) {
            $ii = new CCDAII();
            $ii->setRoot("1.2.250.1.71.4.2.1");
            $ii->setAssigningAuthorityName("GIP-CPS");
            $ii->setExtension("8$user->rpps");
            $assigned->appendId($ii);

            return;
        }

        if ($user->adeli) {
            $ii = new CCDAII();
            $ii->setRoot("1.2.250.1.71.4.2.1");
            $ii->setAssigningAuthorityName("GIP-CPS");
            $ii->setExtension("0$user->adeli");
            $assigned->appendId($ii);
        }
    }


    /**
     * Affectation id à l'établissement
     *
     * @param CCDAPOCD_MT000040_CustodianOrganization $entite CCDAPOCD_MT000040_CustodianOrganization
     * @param CGroups                                 $etab   CGroups
     *
     * @return void
     */
    function setIdEtablissement($entite, $etab)
    {
        $factory = $this->factory;

        // CDA structuré (1 seul ID) => soit root soit siret soit finess
        if ($factory instanceof CCDALevel3) {
            if ($etab->siret) {
                $ii = new CCDAII();
                $ii->setRoot("1.2.250.1.71.4.2.2");
                $ii->setExtension("3" . $etab->siret);
                $ii->setAssigningAuthorityName("GIP-CPS");
                $entite->appendId($ii);

                return;
            }

            if ($etab->finess) {
                $ii = new CCDAII();
                $ii->setRoot("1.2.250.1.71.4.2.2");
                $ii->setExtension("1" . $etab->finess);
                $ii->setAssigningAuthorityName("GIP-CPS");
                $entite->appendId($ii);

                return;
            }
        }

        // Pour SISRA, il faut mettre obligatoirement le FINESS
        if ($factory::TYPE === CCDAFactory::TYPE_ZEPRA) {
            if (!$etab->finess) {
                $this->factory->report->addData(
                    CAppUI::tr('CGroups-msg-None finess'),
                    CItemReport::SEVERITY_ERROR
                );
            }

            $ii = new CCDAII();
            $ii->setRoot("1.2.250.1.71.4.2.2");
            $ii->setExtension("1" . $etab->finess);
            $ii->setAssigningAuthorityName("GIP-CPS");
            $entite->appendId($ii);

            return;
        }

        if (CAppUI::gconf('xds general use_siret_finess_ans', $etab->_id) == 'siret') {
            if (!$etab->siret) {
                $this->factory->report->addData(
                    CAppUI::tr('CGroups-msg-None siret'),
                    CItemReport::SEVERITY_ERROR
                );
            }

            $ii = new CCDAII();
            $ii->setRoot("1.2.250.1.71.4.2.2");
            $ii->setExtension("3" . $etab->siret);
            $ii->setAssigningAuthorityName("GIP-CPS");
            $entite->appendId($ii);
        }

        if (CAppUI::gconf('xds general use_siret_finess_ans', $etab->_id) == 'finess') {
            if (!$etab->finess) {
                $this->factory->report->addData(
                    CAppUI::tr('CGroups-msg-None finess'),
                    CItemReport::SEVERITY_ERROR
                );
            }

            $ii = new CCDAII();
            $ii->setRoot("1.2.250.1.71.4.2.2");
            $ii->setExtension("1" . $etab->finess);
            $ii->setAssigningAuthorityName("GIP-CPS");
            $entite->appendId($ii);
        }
    }

    /**
     * Création d'un ivl_ts avec une valeur basse et haute
     *
     * @param String  $low        String
     * @param String  $high       String
     * @param Boolean $nullFlavor false
     *
     * @return CCDAIVL_TS
     */
    function createIvlTs($low, $high, $nullFlavor = false)
    {
        $ivlTs = new CCDAIVL_TS();
        if ($nullFlavor && !$low && !$high) {
            $ivlTs->setNullFlavor("UNK");

            return $ivlTs;
        }

        $ivxbL = new CCDAIVXB_TS();
        $ivxbL->setValue($low);
        $ivlTs->setLow($ivxbL);
        $ivxbH = new CCDAIVXB_TS();
        $ivxbH->setValue($high);
        $ivlTs->setHigh($ivxbH);

        return $ivlTs;
    }

    /**
     * @param CStoredObject    $object
     * @param CMediusers|CUser $user_fallback
     *
     * @return CGroups
     * @throws Exception
     */
    protected function determineEtablissement(CStoredObject $object, $user_fallback = null): CGroups
    {
        if ($object instanceof CSejour) {
            $etablissement = $object->loadRefEtablissement();
        } elseif ($object instanceof CConsultation) {
            $etablissement = $object->loadRefPraticien()->loadRefFunction()->loadRefGroup();
        } elseif ($object instanceof COperation) {
            $etablissement = $object->loadRefSejour()->loadRefEtablissement();
        } elseif ($object instanceof CDocumentItem) {
            $target = $object->loadTargetObject();

            if ($target instanceof CConsultation) {
                $etablissement = $target->loadRefGroup();
            } elseif ($target instanceof CSejour) {
                $etablissement = $target->loadRefEtablissement();
            } elseif ($target instanceof COperation) {
                $etablissement = $target->loadRefSejour()->loadRefEtablissement();
            } elseif ($user_fallback && $user_fallback->_id) {
                $etablissement = $user_fallback->loadRefFunction()->loadRefGroup();
            } else {
                $etablissement = CGroups::loadCurrent();
            }
        } elseif ($object instanceof CUser || $object instanceof CMediusers) {
            $etablissement = $object->loadRefFunction()->loadRefGroup();
        } elseif ($user_fallback && $user_fallback->_id) {
            $etablissement = $user_fallback->loadRefFunction()->loadRefGroup();
        } else {
            $etablissement = CGroups::loadCurrent();
        }

        return $etablissement;
    }

    /**
     * Création du role de l'auteur
     *
     * @param CPerson $author
     *
     * @return CCDAPOCD_MT000040_AssignedAuthor
     * @throws Exception
     */
    protected function getAssignedAuthor($author)
    {
        $options = $this->getOptionsForAssigned();

        $group = $this->determineEtablissement($this->factory->mbObject, $author);
        return (new CDAMetaAssignedAuthor($this->factory, $author, $options))
            ->setOrganizationObject($group)
            ->build();
    }

    /**
     * @return array
     */
    private function getOptionsForAssigned(): array
    {
        $assigned_auth_classCode = $this->factory::TYPE === CCDAFactory::TYPE_LDL_EES ||
            $this->factory::TYPE === CCDAFactory::TYPE_LDL_SES;

        $organization_identifier_type = $this->factory instanceof CCDADmp
            ? CAppUI::gconf('xds general use_siret_finess_ans', $this->factory->group->_id)
            : 'siret';
        $organization_identifier_fallback = $this->factory instanceof CCDADmp ? null : 'finess';

        return [
            'classCode'               => $assigned_auth_classCode,
            'code'                    => [
                'required' => true,
            ],
            'representedOrganization' => [
                'id'                        => [
                    'type'     => $organization_identifier_type,
                    'fallback' => $organization_identifier_fallback,
                ],
                'standardIndustryClassCode' => $this->factory->industry_code ?: false,
            ],
        ];
    }

    /**
     * Affectation champ pour les fonction de type assigned
     *
     * @param CCDAPOCD_MT000040_AssignedAuthor $assigned CCDAPOCD_MT000040_AssignedAuthor
     * @param CPerson                          $mediUser Cperson
     *
     * @return void
     */
    protected function setAssigned($assigned, $mediUser)
    {
        if ($mediUser instanceof CUser) {
            $mediUser = $mediUser->loadRefMediuser();
        }
        $mediUser->loadRefFunction();

        $this->setIdPs($assigned, $mediUser);

        $telecoms = array_filter(
            [
                CDAMetaTelecom::TYPE_TELECOM_EMAIL  => $mediUser->_p_email,
                CDAMetaTelecom::TYPE_TELECOM_MOBILE => $mediUser->_p_mobile_phone_number,
                CDAMetaTelecom::TYPE_TELECOM_TEL    => $mediUser->_p_phone_number,
            ]
        );
        foreach ($telecoms ?: [CDAMetaTelecom::TYPE_TELECOM_TEL => null] as $type => $value) {
            $telecom = (new CDAMetaTelecom($this->factory, $mediUser, $type))->build();
            $assigned->appendTelecom($telecom);
        }

        $address = (new CDAMetaAddress($this->factory, $mediUser))->build();
        $assigned->appendAddr($address);
    }

    /**
     * Création de l'assignedEntity
     *
     * @param CUser|CMediUsers $user         CUser|CMediUsers
     * @param boolean          $organization false
     *
     * @return CCDAPOCD_MT000040_AssignedEntity
     */
    protected function getAssignedEntity($user, $organization = false)
    {
        $options = $this->getOptionsForAssigned();

        if ($organization) {
            $organization_object = $this->determineEtablissement($this->factory->mbObject, $user);
        }

        return (new CDAMetaAssignedEntity($this->factory, $user, $options))
        ->setOrganizationObject($organization_object ?? null)
        ->build();
    }

    /**
     * Création de service event
     *
     * @return CCDAPOCD_MT000040_ServiceEvent
     */
    protected function getServiceEvent()
    {
        $service_event = $this->factory->service_event;

        $serviceEvent = new CCDAPOCD_MT000040_ServiceEvent();
        $ce           = new CCDACE();
        $time_start   = $service_event["time_start"];
        $time_stop    = $service_event["time_stop"];
        $ivl          = $this->createIvlTs($time_start, $time_stop);
        $serviceEvent->setEffectiveTime($ivl);
        if (($service_event["code"] ?? false) && ($service_event["oid"] ?? false)) {
            if ($this->factory::TYPE === CCDAFactory::TYPE_LDL_EES || $this->factory::TYPE === CCDAFactory::TYPE_LDL_SES) {
                $ce->setCode($service_event["code"]);
                $ce->setDisplayName($service_event["libelle"]);
                $ce->setCodeSystem($service_event["oid"]);
                $ce->setCodeSystemName("HL7:ActCode");
            } else {
                $code = $service_event["code"];

                // Est-ce que c'est un code LOINC (cas CDA structuré)
                if ($this->factory instanceof CCDALevel3) {
                    $code_loinc = CLoinc::get($code);
                    if ($code_loinc && $code_loinc->_id) {
                        $ce->setCode($service_event["code"]);
                        $ce->setDisplayName($service_event["libelle"]);
                        $ce->setCodeSystem(CLoinc::$oid_loinc);
                        $ce->setCodeSystemName("LOINC");
                    }
                } else {
                    $ce->setCode($service_event["code"]);
                    $ce->setCodeSystem($service_event["oid"]);
                }
            }
        }

        $serviceEvent->appendPerformer($this->getPerformer($service_event["executant"]));
        if (($service_event["code"] ?? false) && ($service_event["oid"] ?? false)) {
            $serviceEvent->setCode($ce);
        }

        return $serviceEvent;
    }

    /**
     * Création componentOf
     *
     * @return void
     */
    protected function setComponentOf(CCDAPOCD_MT000040_ClinicalDocument $document)
    {
        $componentOf = new CCDAPOCD_MT000040_Component1();
        $componentOf->setEncompassingEncounter($this->getEncompassingEncounter());

        $document->setComponentOf($componentOf);
    }


    /**
     * Création encompassingEncounter
     *
     * @return CCDAPOCD_MT000040_EncompassingEncounter
     */
    protected function getEncompassingEncounter()
    {
        $encompassingEncounter = new CCDAPOCD_MT000040_EncompassingEncounter();
        /** @var CSejour|COperation|CConsultation $object CSejour */
        $object = $this->factory->targetObject;
        $ivl    = "";
        switch (get_class($object)) {
            case CSejour::class:
                $low = $object->entree_reelle;
                if (!$low) {
                    $low = $object->entree_prevue;
                }

                $high = $object->sortie_reelle;
                if (!$high) {
                    $high = $object->sortie_prevue;
                }

                $ivl = $this->createIvlTs($low, $high);

                break;
            case COperation::class:
                $ivl = $this->createIvlTs($object->debut_op, $object->fin_op);
                $encompassingEncounter->setEffectiveTime($ivl);

                break;
            case CConsultation::class:
                $object->loadRefPlageConsult();
                $ivl = $this->createIvlTs($object->_datetime, $object->_date_fin, true);
                break;
            default:
        }
        $encompassingEncounter->setEffectiveTime($ivl);

        $this->addCodeEncompassingEncounter($encompassingEncounter);
        $this->addResponsibleParty($encompassingEncounter);

        $encompassingEncounter->setLocation($this->getLocation());

        return $encompassingEncounter;
    }

    protected function addResponsibleParty(CCDAPOCD_MT000040_EncompassingEncounter $encompassingEncounter): void
    {
        $responsibleParty = new CCDAPOCD_MT000040_ResponsibleParty();

        $responsibleParty->setAssignedEntity($this->getAssignedEntity($this->factory->practicien));

        $encompassingEncounter->setResponsibleParty($responsibleParty);
    }

    /**
     * @param CCDAPOCD_MT000040_EncompassingEncounter $encompassingEncounter
     *
     * @return void
     */
    protected function addCodeEncompassingEncounter(CCDAPOCD_MT000040_EncompassingEncounter $encompassingEncounter
    ): void {
        $targetObject = $this->factory->targetObject;

        $entries = null;
        if ($targetObject instanceof CConsultation) {
            $entries = CANSValueSet::loadEntries('typeRencontre', 'EXTERNE');
        }

        if ($targetObject instanceof CSejour) {
            switch ($targetObject->type) {
                case 'urg':
                    $entries = CANSValueSet::loadEntries('actCode', 'EMER');
                    break;
                default :
                    $entries = CANSValueSet::loadEntries('actCode', 'IMP');
            }
        }

        if ($targetObject instanceof COperation) {
            $entries = CANSValueSet::loadEntries('actCode', 'IMP');
        }

        if (!$entries || !CMbArray::get($entries, 'code')) {
            return;
        }

        $ce = CCDADocTools::prepareCodeCE(
            CMbArray::get($entries, 'code'),
            CMbArray::get($entries, 'codeSystem'),
            CMbArray::get($entries, 'displayName')
        );

        $encompassingEncounter->setCode($ce);
    }

    /**
     * Retourne un HealthCareFacility
     *
     * @return CCDAPOCD_MT000040_HealthCareFacility
     */
    protected function getHealthCareFacility()
    {
        $healt  = new CCDAPOCD_MT000040_HealthCareFacility();
        $valeur = $this->factory->healt_care;

        if (
            !CMbArray::get($valeur, 'code') || !CMbArray::get($valeur, 'codeSystem')
            || !CMbArray::get($valeur, 'displayName')
        ) {
            $this->factory->report->addData(
                CAppUI::tr('CGroups-msg-None association CDA'),
                CItemReport::SEVERITY_ERROR
            );

            return new CCDAPOCD_MT000040_HealthCareFacility();
        }

        $ce = new CCDACE();
        $ce->setCode($valeur["code"]);
        $ce->setCodeSystem($valeur["codeSystem"]);
        $ce->setDisplayName($valeur["displayName"]);
        $healt->setCode($ce);

        return $healt;
    }

    /**
     * @param CCDAPOCD_MT000040_ClinicalDocument $document
     */
    protected function setRelatedDocument(CCDAPOCD_MT000040_ClinicalDocument $document): void
    {
        $related = new CCDAPOCD_MT000040_RelatedDocument();
        if ($this->factory->old_version) {
            $related->setTypeCode("RPLC");
            $related->setParentDocument($this->getParentDocument());
            $document->appendRelatedDocument($related);
        }
    }

    /**
     * Création du document parent
     *
     * @return CCDAPOCD_MT000040_ParentDocument
     */
    protected function getParentDocument(): CCDAPOCD_MT000040_ParentDocument
    {
        $parent = new CCDAPOCD_MT000040_ParentDocument();
        $ii     = new CCDAII();
        $ii->setRoot($this->factory->old_version);
        $parent->appendId($ii);

        return $parent;
    }
}
