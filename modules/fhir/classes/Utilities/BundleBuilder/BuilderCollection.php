<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\BundleBuilder;

use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleEntry;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Ox\Interop\Fhir\Resources\CFHIRResource;

/**
 * Description
 */
class BuilderCollection extends BundleBuilder
{
    /**
     * @param CFHIRResource $resource
     *
     * @return $this
     */
    public function addResource(CFHIRResource $resource): self
    {
        $route_params = [
            'resource'    => $resource->getResourceType(),
            'resource_id' => $resource->getResourceId(),
        ];

        $full_url = CFHIRController::getUrl('fhir_read', $route_params);
        $this->bundle->addEntry(
            (new CFHIRDataTypeBundleEntry())
                ->setFullUrl($full_url)
                ->setResourceElement(new CFHIRDataTypeResource($resource))
        );

        return $this;
    }
}
