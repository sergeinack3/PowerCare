<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDM;

use Exception;
use Ox\Core\CMbException;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3EventXDSb;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSConfidentiality;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSHealthcareFacilityType;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSPracticeSetting;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSetAuthor;
use Ox\Interop\Xds\Transformer\XDSTransformer;
use Ox\Interop\Xds\XDM\CXDMRepository;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * CHL7v3EventXDSbProvideAndRegisterDocumentSetRequest
 * Provide and register document set request
 */
class CHL7v3EventXDMDistributeDocumentSetOnMedia extends CHL7v3EventXDSb implements CHL7EventXDMDistributeSetOnMedia
{
    /** @var string */
    public $interaction_id = "DistributeDocumentSetOnMedia";
    public $old_version;
    public $old_id;
    /** @var string xds type */
    public $type;
    /** @var string */
    public $uuid;
    public $hide;

    /** @var bool */
    public $not_generate_exchange = false;

    /** @var string */
    public $type_cda;
    /** @var CXDSSubmissionSetAuthor */
    public $submission_author;
    /** @var CXDSPracticeSetting */
    public $pratice_setting;
    /** @var CXDSHealthcareFacilityType */
    public $healtcare;

    /**
     * Build ProvideAndRegisterDocumentSetRequest event
     *
     * @param CCompteRendu|CFile|CSejour|COperation|CConsultation|CConsultAnesth $object compte rendu
     *
     * @return void
     * @throws CMbException
     * @throws Exception
     * @see parent::build()
     *
     */
    public function build($object): void
    {
        $object->_not_generate_exchange = $this->not_generate_exchange;
        parent::build($object);

        // cda & xds options
        $options_cda = [
            'old_version'           => $this->old_version,
            'old_id'                => $this->old_id,
            'receiver'              => $this->_receiver,
        ];
        $options_xds = $this->getXDSOptions();

        // generate xdm
        $repo_xdm = new CXDMRepository($object, $this->type_cda, $this->type);
        $repo_xdm->generateXDM($options_cda, $options_xds);

        $submission_set = $repo_xdm->getSubmissionSet();

        // gestion seulement d'un fichier
        $document = reset($submission_set->documents);
        $this->applyOptions($submission_set, $document, $repo_xdm->getRepoCda()->getFileCDA());

        // get xds content
        $this->msg_hl7 = XDSTransformer::serialize($submission_set);
    }

    /**
     * @param $object
     *
     * @return array
     */
    private function getXDSOptions(): array
    {
        $options = [
            'receiver' => $this->_receiver,
            'doc_uuid' => $this->uuid,
            'hide'     => $this->hide,
        ];

        return array_filter(
            $options,
            function ($v) {
                return $v !== null;
            }
        );
    }

    /**
     * @param CXDSSubmissionSet $submission_set
     * @param CXDSDocumentEntry $document
     * @param CFile             $file_cda
     *
     * @return void
     */
    protected function applyOptions(CXDSSubmissionSet $submission_set, CXDSDocumentEntry $document, CFile $file_cda): void
    {
        // remove file content ==> not include document in request
        $document->_content_file = null;

        // masquage
        if ($file_cda->masquage_patient) {
            $document->addConfidentiality(CXDSConfidentiality::getMasquage("INVISIBLE_PATIENT"));
        }
        if ($file_cda->masquage_praticien) {
            $document->addConfidentiality(CXDSConfidentiality::getMasquage( "MASQUE_PS"));
        }
        if ($file_cda->masquage_representants_legaux) {
            $document->addConfidentiality(CXDSConfidentiality::getMasquage("INVISIBLE_REPRESENTANTS_LEGAUX"));
        }

        if ($author = $this->submission_author) {
            $author_cda = reset($submission_set->_submission_set_author);
            if ($author->author_institution) {
                $author_cda->setAuthorInstitution($author->author_institution);
            }

            if ($author->author_role) {
                $author_cda->setAuthorRole($author->author_role);
            }

            if ($author->author_person) {
                $author_cda->setAuthorPerson($author->author_person);
            }

            if ($author->author_speciality) {
                $author_cda->setAuthorSpeciality($author->author_speciality);
            }
        }

        if ($this->pratice_setting) {
            $document->setPracticeSetting($this->pratice_setting);
        }

        if ($this->healtcare) {
            $document->setHealthcareFacilityType($this->healtcare);
        }
    }
}
