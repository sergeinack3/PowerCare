<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure\SubmissionSet;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\Module\CModule;
use Ox\Interop\Dmp\Dsig\CDSIG;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CMbOID;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CXDSValueSet;
use Ox\Interop\Xds\Analyzer\SignatureMetadataAnalyzer;
use Ox\Interop\Xds\CXDSTools;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\CXDSAssociation;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\XDSElementInterface;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CSubmissionLot;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

class CXDSSubmissionSet implements XDSElementInterface
{
    /** @var string */
    public const TYPE_XDS = 'XDS';
    /** @var string */
    public const TYPE_ZEPRA = 'ZEPRA';
    /** @var string */
    public const TYPE_DMP = 'DMP';
    /** @var string */
    public const TYPE_ANS = 'ANS';

    /** @var string[] */
    public const TYPES_AUTHORIZED = [
        self::TYPE_DMP,
        self::TYPE_ZEPRA,
        self::TYPE_XDS,
        self::TYPE_ANS,
    ];

    // Model XDS
    /** @var string */
    public $entryUUID;

    /** @var string */
    public $availabilityStatus;

    /** @var string */
    public $submissionTime;

    /** @var string */
    public $title;

    /** @var string */
    public $comments;

    /** @var array HL7 v2.5-CX<> */
    public $patient_id;

    /** @var string */
    public $source_id;

    /** @var string */
    public $unique_id;

    /** @var CXDSAssociation[] */
    public $associations = [];

    /** @var CXDSDocumentEntry[] */
    public $documents = [];

    /** @var CXDSSubmissionSetAuthor[] */
    public $_submission_set_author = [];

    /** @var CXDSContentType */
    public $_submission_set_content_type;


    // Model MB

    /** @var CInteropReceiver|null */
    public $_receiver;

    /** @var CConsultation|CSejour|COperation */
    public $object;

    /** @var CPatient */
    public $patient;

    /** @var CMediusers */
    public $practicien;

    /** @var CMediusers */
    public $authorPerson;

    /** @var CGroups */
    public $group;

    /** @var string */
    public $_class = 'CXDSSubmissionSet';

    /** @var string (XDS|ZEPRA|DMP) */
    public $type;

    /**
     * @param string|null $type
     *
     * @throws CXDSException
     */
    public function __construct(?string $type = self::TYPE_ANS)
    {
        if (!in_array($type, self::TYPES_AUTHORIZED)) {
            throw CXDSException::invalidTypeSubmissionSet();
        }

        if (($type === self::TYPE_DMP && !CModule::getActive('dmp'))
            || ($type === self::TYPE_ZEPRA && !CModule::getActive('sisra'))
        ) {
            throw CXDSException::moduleNoActif();
        }

        $this->type = $type;
    }

    /**
     * @param CDSIG $DSIG
     * @param bool  $sign_now
     *
     * @return bool
     * @throws CXDSException
     */
    public function sign(CDSIG $DSIG, bool $sign_now = true): bool
    {
        // Prevent sign twice
        if ($this->getSignatureDocument()) {
            return false;
        }

        $signature_anlyzer = new SignatureMetadataAnalyzer();
        $signature_anlyzer->setSubmissionSet($this);

        // create documentEntry signature
        $signature_document_entry = CXDSDocumentEntry::fromDocument(new CFile(), $signature_anlyzer);

        // add document signature
        $this->addDocumentEntry($signature_document_entry);

        // Association signature
        $this->associations[] = (new CXDSAssociation())->associate(
            $signature_document_entry,
            $this,
            CXDSAssociation::TYPE_SIGN
        );

        if ($signature_document_dsig = $DSIG->createSignatureLot($this, $sign_now)) {
            $signature_document_entry->_content_file = $signature_document_dsig->saveXML();
        }

        return true;
    }

    /**
     * @return CXDSAssociation[]
     */
    public function getAssociations(string $type = null): array
    {
        $associations = $this->associations;

        if ($type) {
            $associations = array_filter(
                $associations,
                function (CXDSAssociation $association) use ($type) {
                    return $association->type === $type;
                }
            );
        }

        return $associations;
    }

    public function getSignatureDocument(): ?CXDSDocumentEntry
    {
        if (!$associations_signature = $this->getAssociations(CXDSAssociation::TYPE_SIGN)) {
            return null;
        }
        $association_signature = reset($associations_signature);

        return $association_signature->from;
    }

    /**
     * @param CMbObject $object
     *
     * @return void
     */
    public function map(CMbObject $object): void
    {
        if ($object instanceof CDocumentItem) {
            $object = $object->loadTargetObject();

            if ($object instanceof CConsultAnesth) {
                $object = $object->loadRefConsultation();
            }
        }

        if (!$object instanceof CConsultation && !$object instanceof CSejour && !$object instanceof COperation) {
            throw CXDSException::invalidObjectConstructSubmissionSet();
        }

        $this->object  = $object;
        $this->patient = $object->loadRefPatient();
        $this->patient->loadLastINS();
        $this->patient->loadIPP();

        $this->practicien = $object->loadRefPraticien();
        $this->practicien->loadRefFunction();
        $this->practicien->loadRefOtherSpec();

        // Pour un séjour : On prend l'établissement du séjour et non l'étab de la fonction
        if ($object instanceof CSejour) {
            $this->group = $object->loadRefEtablissement();
        } elseif ($object instanceof COperation) {
            $this->group = $object->loadRefSejour()->loadRefEtablissement();
        } else {
            $this->group = $this->practicien->loadRefFunction()->loadRefGroup();
        }

        // if current user is not doctor, take practitionner
        $this->authorPerson = CMediusers::get();
        if (!$this->authorPerson->isMedecin()) {
            $this->authorPerson = $this->practicien;
        }
    }

    /**
     * @param CMbObject $object
     *
     * @return void
     * @throws CMbException
     * @throws CXDSException
     */
    public function fill(CMbObject $object): void
    {
        $this->map($object);

        // Author (institution, person, role, specialty)
        $this->addSubmissionSetAuthor();

        // SubmissionTime
        $this->submissionTime = CXDSTools::getTimeUtc();

        // Title
        $this->title = 'OX - LDS - '. $this->patient->nom. ' '. $this->patient->prenom. ' - '. CMbDT::date();

        // SourceId
        $this->source_id = CMbOID::getOIDOfInstance($this, $this->_receiver, $this->type == self::TYPE_DMP ? false : true);

        // EntryUUID
        $this->entryUUID = 'urn:uuid:'.CMbSecurity::generateUUID();

        // UniqueId
        $this->setUniqueId();

        // ContentType
        $this->setContentType();
    }

    protected function setContentType(): void
    {
        $content_type = new CXDSContentType();

        switch ($this->type) {
            case self::TYPE_ANS:
            case self::TYPE_DMP:
            case self::TYPE_ZEPRA:
                $entry = CANSValueSet::getContentTypeCode($this->object);
                break;
            default:
                $xds_value_set = new CXDSValueSet();
                $entry         = $xds_value_set->getContentTypeCode($this->object);
        }

        $content_type->setCode(CMbArray::get($entry, 'code'));
        $content_type->setCodeSystem(CMbArray::get($entry, 'codeSystem'));
        $content_type->setDisplayName(CMbArray::get($entry, 'displayName'));

        $this->_submission_set_content_type = $content_type;
    }

    /**
     * @throws CMbException
     */
    protected function setUniqueId(): void
    {
        $oid                      = CMbOID::getOIDFromClass($this, $this->_receiver);
        $cxds_submissionlot       = new CSubmissionLot();
        $cxds_submissionlot->date = "now";
        $cxds_submissionlot->type = $this->type;
        if ($msg = $cxds_submissionlot->store()) {
            throw new CMbException($msg);
        }

        $this->unique_id = "$oid.$cxds_submissionlot->_id";
    }

    /**
     * @return array
     */
    public function getPatientId(): string
    {
        return CXDSTools::serializeHL7v2Components($this->patient_id);
    }

    /**
     * Retourne l'OID du patient
     *
     * @param bool $from_document_entry
     *
     * @return string
     */
    public function addPatientId(?bool $from_document_entry = true): void
    {
        if ($from_document_entry && $this->documents) {

            // We take first document to get patient CDA
            $first_document_entry = reset($this->documents);

            if ($first_document_entry && $first_document_entry->patient_id
                && $this->checkPatientIdCdaCompleted($first_document_entry->patient_id)
            ) {
                $this->patient_id = $first_document_entry->patient_id;
            }
        }

        if ($this->patient_id) {
            return;
        }

        $patient = $this->patient;

        // Default, we take patient in database
        if ($this->type == self::TYPE_DMP) {
            $comp5 = "NH";
            $comp4 = CAppUI::conf("dmp NIR_OID");
            $comp4 = "&$comp4&ISO";
            $comp1 = $patient->getINSNIR();
        } elseif ($this->type == self::TYPE_ZEPRA && $ins = $patient->getINSNIR()) {
            $comp5 = "NH";
            $comp4 = CAppUI::conf("dmp NIR_OID");
            $comp4 = "&$comp4&ISO";
            $comp1 = $ins;
        } else {
            $comp1 = $patient->_IPP ?: $patient->_id;

            $oid = CMbOID::getOIDOfInstance($patient, $this->_receiver, true);
            $comp4 = "&$oid&ISO";
            $comp5 = "^PI";
        }

        $this->patient_id = [
            'CX.1' => $comp1,
            'CX.4' => $comp4,
            'CX.5' => $comp5,
        ];
    }

    /**
     * @param array $patient_id
     *
     * @return bool
     */
    protected function checkPatientIdCdaCompleted(array $patient_id): bool
    {
        if (CMbArray::get($patient_id, 'CX.1') && CMbArray::get($patient_id, 'CX.4')
            && CMbArray::get($patient_id, 'CX.5')
        ) {
            return true;
        }

        return false;
    }

    /**
     *
     * @return void
     */
    protected function addSubmissionSetAuthor(): void
    {
        $submission_set_author = new CXDSSubmissionSetAuthor();

        $this->setAuthorInstitution($submission_set_author);

        $this->setAuthorPerson($submission_set_author);

        $this->setAuthorRole($submission_set_author);

        $this->setAuthorSpecialty($submission_set_author);

        $this->_submission_set_author[] = $submission_set_author;
    }

    /**
     * @param CXDSSubmissionSetAuthor $submission_set_author
     *
     *
     * @return void
     */
    protected function setAuthorSpecialty(CXDSSubmissionSetAuthor $submission_set_author): void
    {
        if (!$this->authorPerson->isMedecin()) {
            return;
        }

        $spec = $this->authorPerson->loadRefOtherSpec();
        if (!$spec->libelle) {
            return;
        }

        $submission_set_author->setAuthorSpeciality(
            [
                'CX.1' => $spec->code,
                'CX.2' => $spec->libelle,
                'CX.3' => $spec->oid,
            ]
        );
    }

    /**
     * @param CXDSSubmissionSetAuthor $submission_set_author
     *
     * @return void
     * @throws CXDSException
     *
     * @throws CMbException|\Exception
     */
    protected function setAuthorRole(CXDSSubmissionSetAuthor $submission_set_author): void
    {
        $patient = $this->patient;
        $author  = $this->authorPerson;

        $authorRole = null;

        // Correspondant
        if ($author && $author->rpps) {
            $medecin       = new CMedecin();
            $ds            = $medecin->getDS();
            $where_medecin = [];
            $ljoin_medecin = [];

            $ljoin_medecin["correspondant"] = "correspondant.medecin_id = medecin.medecin_id";
            $where_medecin['patient_id']    = $ds->prepare(' = ?', $patient->_id);
            $where_medecin['rpps']          = $ds->prepare(' = ?', $author->rpps);
            $where_medecin[]                = 'correspondant.correspondant_id IS NOT NULL';

            if ($medecin->countList($where_medecin, 'medecin.medecin_id', $ljoin_medecin) > 0) {
                $authorRole = 'Correspondant';
            }
        }

        // Médecin traitant
        if ($author->_id && $author->rpps) {
            $medecin_traitant = $patient->loadRefMedecinTraitant();
            if ($medecin_traitant && $medecin_traitant->_id && $medecin_traitant->rpps === $author->rpps) {
                $authorRole = 'Médecin traitant';
            }
        }

        // Référent - Responsable du patient dans la structure de soins
        if ($this->object instanceof CSejour && $author->_id == $this->object->praticien_id) {
            $authorRole = 'Référent - Responsable du patient dans la structure de soins';
        }

        if ($authorRole) {
            $submission_set_author->setAuthorRole($authorRole);
        }
    }

    /**
     * @param CXDSSubmissionSetAuthor $submission_set_author
     *
     * @return void
     * @throws CXDSException
     *
     * @throws CMbException
     */
    protected function setAuthorPerson(CXDSSubmissionSetAuthor $submission_set_author): void
    {
        // Auth directe => On applique le RPPS comme dans le VIHF
        if (CAppUI::loadPref('authentification_directe', $this->authorPerson->_id)
            && CAppUI::gconf("dmp general authentification_directe", $this->group->_id)
        ) {
            $CX1 = '8'.$this->authorPerson->rpps;
            $CX13 = 'EI';
        } else {
            $CX1 = $this->getIdEtablissement($this->group, true) . "/" . $this->authorPerson->_id;
            $CX13 = 'IDNPS';
        }

        $submission_set_author->setAuthorPerson(
            [
                'CX.1'  => $CX1,
                'CX.2'  => $this->authorPerson->_p_last_name,
                'CX.3'  => $this->authorPerson->_p_first_name,
                'CX.9'  => '&1.2.250.1.71.4.2.1&ISO',
                'CX.10' => 'D',
                'CX.13' => $CX13,
            ]
        );
    }

    /**
     * @param CXDSSubmissionSetAuthor $submission_set_author
     *
     * @return void
     * @throws CXDSException
     *
     * @throws CMbException
     */
    protected function setAuthorInstitution(CXDSSubmissionSetAuthor $submission_set_author): void
    {
        $submission_set_author->setAuthorInstitution(
            [
                'CX.1'  => $this->group->text,
                'CX.6'  => '&1.2.250.1.71.4.2.2&ISO',
                'CX.7'  => 'IDNST',
                'CX.10' => $this->getIdEtablissement($this->group),
            ]
        );
    }

    /**
     * @param CGroups $group
     * @param bool    $forPerson
     *
     * @return string
     * @throws CMbException
     * @throws CXDSException
     */
    protected function getIdEtablissement(CGroups $group, bool $forPerson = false): string
    {
        $config_name = 'ans';

        if ($this->type === self::TYPE_ZEPRA) {
            $config_name = 'zepra';
        } elseif ($this->type === self::TYPE_XDS) {
            $config_name = 'xds';
        }

        $config_name = 'use_siret_finess_'.$config_name;

        if (CAppUI::gconf('xds general '. $config_name, $group->_id) == 'siret') {
            $siret = $forPerson ? "5" : "3";
            if (!$group->siret) {
                throw new CMbException("CGroups-msg-None siret");
            }
            return $siret . $group->siret;
        }

        if (CAppUI::gconf('xds general use_siret_finess_ans', $group->_id) == 'finess') {
            $finess = $forPerson ? "3" : "1";
            if (!$group->finess) {
                throw new CMbException("CGroups-msg-None finess");
            }
            return $finess . $group->finess;
        }

        throw CXDSException::missingConfigGroup();
    }

    /**
     * @param CXDSDocumentEntry    $document_entry
     * @param CXDSAssociation|null $association
     *
     * @return void
     */
    public function addDocumentEntry(CXDSDocumentEntry $document_entry, CXDSAssociation $association = null): void
    {
        $this->documents[] = $document_entry;

        if (!$association) {
           $association = (new CXDSAssociation())->associate(
                $this,
                $document_entry,
                CXDSAssociation::TYPE_HAS_MEMBER
            );
        }

        $this->associations[] = $association;
    }

    /**
     * @param XDSElementInterface $source
     * @param XDSElementInterface $target
     * @param string              $type
     */
    public function addAssociation(XDSElementInterface $source, XDSElementInterface $target, string $type): void
    {
        $this->addAssociationEntry((new CXDSAssociation())->associate($source, $target, $type));
    }

    public function addAssociationEntry(CXDSAssociation $association) {
        $this->associations[] = $association;
    }
}
