<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\XDSb;

use Ox\Core\CMbException;
use Ox\Interop\Xds\CXDSXmlDocument;
use Ox\Mediboard\Patients\CPatient;

/**
 * CHL7v3EventXDSbUpdateDocumentSet
 * Update document set
 */
class CHL7v3EventXDSbRetrieveDocumentSet extends CHL7v3EventXDSb implements CHL7EventXDSbRetrieveDocumentSet
{
    /** @var string */
    public $interaction_id = "RetrieveDocumentSet";

    public $repository_id;
    public $oid;

    /**
     * Build RetrieveDocumentSetRequest event
     *
     * @param CPatient $object Patient
     *
     * @return void
     * @throws CMbException
     * @see parent::build()
     *
     */
    function build($object)
    {
        parent::build($object);

        $xml              = new CXDSXmlDocument();
        $root             = $xml->createDocumentRepositoryElement($xml, "RetrieveDocumentSetRequest");
        $document_request = $xml->createDocumentRepositoryElement($root, "DocumentRequest");

        $xml->createDocumentRepositoryElement($document_request, "RepositoryUniqueId", $this->repository_id);
        $xml->createDocumentRepositoryElement($document_request, "DocumentUniqueId", $this->oid);

        $this->message = $xml->saveXML();

        $this->updateExchange(false);
    }
}
