<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Operations;

use Ox\Interop\Eai\CDomain;
use Ox\Interop\Fhir\Api\Response\CFHIRResponse;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Parameters\CFHIRDataTypeParametersParameter;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeBackbone;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Parameters\CFHIRResourceParameters;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CFHIROperationIhePix extends CFHIRInteraction
{
    /** @var string */
    public const NAME = 'ihe-pix';

    /**
     * Get the resource method name
     *
     * @return string
     */
    public function getResourceMethodName(): string
    {
        $interaction = preg_replace('/[^\w]/', '_', self::NAME);

        return "operation_$interaction";
    }

    public function getBasePath(): ?string
    {
        return parent::getBasePath() . '/$ihe-pix';
    }

    /**
     * @inheritdoc
     *
     * @param CPatient $result
     */
    public function handleResult(CFHIRResource $resource, $result): CFHIRResponse
    {
        $root = new CFHIRResourceParameters();
        if ($result) {
            $domains = CDomain::loadDomainIdentifiers($result);
            foreach ($domains as $_domain) {
                if (empty($result->_returned_oids) || in_array($_domain->OID, $result->_returned_oids)) {
                    $identiifier = CFHIRDataTypeIdentifier::makeIdentifier($_domain->_identifier->id400, $_domain->OID)
                        ->setUse(new CFHIRDataTypeCode('official'));
                    $parameter   = (new CFHIRDataTypeParametersParameter())
                        ->setName("targetIdentifier")
                        ->setValueElement($identiifier);

                    $root->addParameter($parameter);
                }
            }

            $reference = (new CFHIRDataTypeReference())
                ->setReference($resource->getResourceType() . "/$result->patient_id");

            $parameter = (new CFHIRDataTypeParametersParameter())
                ->setName("targetId")
                ->setValueElement($reference);

            $root->addParameter($parameter);
        }

        $this->setResource($root);

        return new CFHIRResponse($this, $this->format);
    }
}
