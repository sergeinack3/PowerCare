<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use Exception;
use Ox\Core\CMbException;
use Ox\Interop\Cda\CCDARepository;
use Ox\Interop\Dmp\Dsig\CDSIG;
use Ox\Interop\Eai\CReport;
use Ox\Interop\Xds\Structure\CXDSAssociation;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSConfidentiality;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSHealthcareFacilityType;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSPracticeSetting;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSetAuthor;
use Ox\Interop\Xds\Transformer\Serializer\EbXmlSerializer;
use Ox\Interop\Xds\Transformer\XDSTransformer;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileTraceability;

/**
 * CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest
 * Provide and register document set request
 */
class CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest
    extends CHL7v3EventXDSb implements CHL7EventXDSbProvideAndRegisterDocumentSetRequest
{
    /** @var string */
    public $interaction_id = "ProvideAndRegisterDocumentSetRequest";
    /** @var string */
    public $_event_name    = "DocumentRepository_ProvideAndRegisterDocumentSet-b";

    /**
     * @var string Type of XDS
     * @see CXDSSubmissionSet::$type
     */
    public $type;

    // attributs

    /** @var string|null [CDA] : ancienne version du document */
    public $old_version;
    /** @var string|null [CDA] : ancien id du document */
    public $old_id;
    /** @var string|null identifiant du document remplacé */
    public $uuid;
    /**
     * @var string|null masquage a appliquer au document
     * @see self HIDE constants
     */
    public $hide;

    /** @var CXDSSubmissionSetAuthor */
    public $submission_author;
    /** @var CXDSPracticeSetting */
    public $pratice_setting;
    /** @var CXDSHealthcareFacilityType */
    public $healtcare;
    /** @var bool */
    public $not_generate_exchange = false;

    public $sign        = true;
    public $add_doctors = false;

    public $sign_now;
    public $passphrase_certificate;
    public $path_certificate;

    /** @var CReport */
    public $report;
    /** @var CFileTraceability */
    public $file_traceability;

    /**
     * Build ProvideAndRegisterDocumentSetRequest event
     *
     * @param CCompteRendu|CFile $object compte rendu
     *
     * @return void
     * @throws Exception
     * @throws CMbException*@see parent::build()
     *
     */
    function build($object)
    {
        $object->_not_generate_exchange = $this->not_generate_exchange;
        parent::build($object);

        // Repository CDA
        $repo_cda = new CCDARepository($this->type, $object);
        $repo_cda->setOptions(
            [
                'old_version' => $this->old_version,
                'old_id'      => $this->old_id,
                'receiver'    => $this->_receiver,
            ]
        );

        // Generate content file and content cda
        $file_cda     = $repo_cda->getFileCDA();
        $this->report = $repo_cda->getReport();

        // donne le context au file_cda car celui-ci n'est pas store
        $file_cda->_id = $object->_id;

        // stop if cda has errors
        if ($repo_cda->hasErrors()) {
            return;
        }

        $target = $repo_cda->getFactory()->targetObject;

        // Generate content XDS
        $submission_set = new CXDSSubmissionSet($this->type);
        $submission_set->_receiver = $this->_receiver;
        $submission_set->fill($target);

        $document_cda = CXDSDocumentEntry::fromDocument($file_cda);
        $document_cda->_content_file = $repo_cda->getContentCda();

        $submission_set->addDocumentEntry($document_cda);
        $submission_set->addPatientId();
        $this->applyOptions($submission_set, $document_cda, $file_cda);

        // ajout de la balise <signature> en auth. directe et indirecte mais on signe la canonisation qu'en indirecte (DMP)
        // ajout des médecins pour SISRA
        if ($this->sign || $this->add_doctors) {
            $dsig = new CDSIG(null, $this->path_certificate, $this->passphrase_certificate, $this->sign_now);
            $submission_set->sign($dsig, $this->sign_now);
        }

        // set content xds on message

        // append element ProvideAndRegisterDocumentSetRequest
        /** @var EbXmlSerializer $serializer */
        $serializer = XDSTransformer::getSerializer();
        $document_xds = $serializer->getXmlDocument();
        $request_node = $document_xds->createDocumentRepositoryElement(
            $document_xds,
            "ProvideAndRegisterDocumentSetRequest"
        );
        $serializer->setRoot($request_node);

        $this->message = $serializer->serialize($submission_set);
        $this->updateExchange(false);
    }

    /**
     * Modify document
     *
     * @param CXDSSubmissionSet $submission_set
     * @param CXDSDocumentEntry $document_cda
     * @param CFile             $file_cda
     */
    private function applyOptions(CXDSSubmissionSet $submission_set, CXDSDocumentEntry $document_cda, CFile $file_cda)
    {
        // Remplacement de document
        if ($this->uuid) {
            $old_document = new CXDSDocumentEntry();
            $old_document->entryUUID = $this->uuid;

            $submission_set->addAssociation($document_cda, $old_document, CXDSAssociation::TYPE_REPLACE);
        }

        // masquage
        if ($file_cda->masquage_patient) {
            $confidentiality = CXDSConfidentiality::getMasquage("INVISIBLE_PATIENT");
            $document_cda->addConfidentiality($confidentiality);
        }
        if ($file_cda->masquage_praticien) {
            $confidentiality = CXDSConfidentiality::getMasquage( "MASQUE_PS");
            $document_cda->addConfidentiality($confidentiality);
        }
        if ($file_cda->masquage_representants_legaux) {
            $confidentiality = CXDSConfidentiality::getMasquage("INVISIBLE_REPRESENTANTS_LEGAUX");
            $document_cda->addConfidentiality($confidentiality);
        }

        if ($author = $this->submission_author) {
            $author_cda = reset($document_cda->_document_entry_author);

            // On force le mediuser
            if ($author->author_person) {
                $author_cda->setAuthorPerson($author->author_person);
            }

            // on force l'établissement sur le document
            if ($author->author_institution) {
                $author_cda->setAuthorInstitution($author->author_institution);
            }

            // on force la donnée specialty sur le document
            if ($author->author_speciality) {
                $author_cda->setAuthorSpeciality($author->author_speciality);
            }
        }

        // on force la donnée pratice_setting sur le document
        if ($this->pratice_setting) {
            $document_cda->setPracticeSetting($this->pratice_setting);
        }

        // on force la donnée healtcare sur le document
        if ($this->healtcare) {
            $document_cda->setHealthcareFacilityType($this->healtcare);
        }
    }
}
