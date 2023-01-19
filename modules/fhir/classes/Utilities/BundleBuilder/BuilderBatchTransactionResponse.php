<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\BundleBuilder;

use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleEntry;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleResponse;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Ox\Interop\Fhir\Resources\CFHIRResource;

/**
 * Description
 */
class BuilderBatchTransactionResponse extends BuilderCollection
{
    /**
     * @param CFHIRDataTypeBundleResponse $response
     * @param CFHIRResource|null          $resource
     *
     * @return $this
     */
    public function addResponse(CFHIRDataTypeBundleResponse $response, CFHIRResource $resource = null): self
    {
        $entry = new CFHIRDataTypeBundleEntry();
        if ($resource) {
            $route_params = [
                'resource'    => $resource->getResourceType(),
                'resource_id' => $resource->getResourceId(),
            ];
            $full_url = CFHIRController::getUrl('fhir_read', $route_params);

            $entry->setFullUrl($full_url)
                ->setResourceElement(new CFHIRDataTypeResource($resource));
        }

        $entry->setResponseElement($response);

        $this->bundle->addEntry($entry);

        return $this;
    }
}
