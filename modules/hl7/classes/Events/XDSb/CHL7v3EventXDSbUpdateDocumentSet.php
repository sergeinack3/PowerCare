<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use DOMElement;
use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Interop\Xds\Exception\CXDSException;
use Ox\Interop\Xds\Structure\CXDSAssociation;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSConfidentiality;
use Ox\Interop\Xds\Structure\DocumentEntry\CXDSDocumentEntry;
use Ox\Interop\Xds\Structure\SubmissionSet\CXDSSubmissionSet;
use Ox\Interop\Xds\Transformer\XDSTransformer;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;

/**
 * CHL7v3EventXDSbUpdateDocumentSet
 * Update document set
 */
class CHL7v3EventXDSbUpdateDocumentSet extends CHL7v3EventXDSb implements CHL7EventXDSbUpdateDocumentSet
{
    /** @var string */
    public $interaction_id = "UpdateDocumentSet";

    public $type;
    public $uuid;
    public $action;
    public $hide;
    public $metadata;
    /** @var bool  */
    public $not_generate_exchange = false;

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

        // submission set
        $submission_set = new CXDSSubmissionSet($this->type);
        $submission_set->_receiver = $this->_receiver;
        $submission_set->fill($object);
        $submission_set->addPatientId();

        if ($this->action) { // archivage
            $this->manageArchiving($submission_set);
        } else {
            $this->manageMask($submission_set); // gestion masquage
        }

        // Pour la modif du masquage : On remplace le namespace dans le message puisqu'on a "copié/collé" celui de la réponse du registry
        $this->message = XDSTransformer::serialize($submission_set);

        $this->updateExchange(false);
    }

    /**
     * Manage archiving
     *
     * @param CXDSSubmissionSet $submission_set
     *
     * @throws CXDSException
     */
    private function manageArchiving(CXDSSubmissionSet $submission_set): void
    {
        switch ($this->action) {
            case "unpublished":
                $NewStatus      = "urn:asip:ci-sis:2010:StatusType:Deleted";
                $OriginalStatus = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
                break;
            case "archived":
                $NewStatus      = "urn:asip:ci-sis:2010:StatusType:Archived";
                $OriginalStatus = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
                break;
            case "unarchived":
                $NewStatus      = "urn:oasis:names:tc:ebxml-regrep:StatusType:Approved";
                $OriginalStatus = "urn:asip:ci-sis:2010:StatusType:Archived";
                break;
            default:
                throw new CXDSException("Unsupported action for archived : $this->action");
        }

        // fiche document
        $document_entry                     = new CXDSDocumentEntry();
        $document_entry->entryUUID          = $this->uuid;
        $document_entry->availabilityStatus = $OriginalStatus;

        // association archived
        $association = (new CXDSAssociation())->associate(
            $submission_set,
            $document_entry,
            CXDSAssociation::TYPE_UPDATE_AVAILABILITY_STATUS
        );
        $association->new_availabilityStatus = $NewStatus;

        $submission_set->addAssociationEntry($association);
    }

    /**
     * @param CXDSSubmissionSet $submission_set
     *
     * @throws CXDSException
     */
    private function manageMask(CXDSSubmissionSet $submission_set)
    {
        /** @var DOMElement $extrinsicNode */
        $extrinsicNode = CMbArray::get($this->metadata, "extrinsicNode");

        // document
        /** @var CXDSDocumentEntry $document */
        $document             = XDSTransformer::parseNode($extrinsicNode);
        $document->entryUUID  = CMbSecurity::generateUUID();
        $document->logical_id = $this->uuid;

        // Association has_member
        $version = CMbArray::get($this->metadata, "version");
        $association = (new CXDSAssociation())->associate($submission_set, $document);
        $association->previousVersion = $version;

        // Dans le cas d'un masquage, on supprime tous les masques s'il y en a (sauf celui avec la valeur "N")
        foreach ($document->_confidentiality as $key => $confidentiality) {
            if ($confidentiality->code !== 'N') {
                unset($document->_confidentiality[$key]);
            }
        }

        // Ajout du masquage sur l'extrinsicObject
        if ($this->object instanceof CDocumentItem) {
            if ($this->object->masquage_patient) {
                $document->addConfidentiality(CXDSConfidentiality::getMasquage('INVISIBLE_PATIENT'));
            }
            if ($this->object->masquage_praticien) {
                $document->addConfidentiality(CXDSConfidentiality::getMasquage('MASQUE_PS'));
            }
            if ($this->object->masquage_representants_legaux) {
                $document->addConfidentiality(CXDSConfidentiality::getMasquage('INVISIBLE_REPRESENTANTS_LEGAUX'));
            }
        } else {
            // todo gestion masquage pour document level 3
        }

        $submission_set->addDocumentEntry($document, $association);
    }
}
