<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\BundleBuilder;

use Ox\Core\CMbSecurity;
use Ox\Interop\Fhir\Controllers\CFHIRController;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Bundle\CFHIRDataTypeBundleRequest;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;
use Ox\Interop\Fhir\Interactions\CFHIRInteraction;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;

/**
 * Description
 */
class BuilderBatchTransaction extends BundleBuilder
{
    /**
     * @param CFHIRInteraction $interaction
     *
     * @return $this
     */
    public function addRequest(CFHIRInteraction $interaction): self
    {
        // todo gestion des conditional
        $resource = $interaction->getResource();
        if ($interaction instanceof CFHIRInteractionCreate) {
            $full_url = $resource->getResourceId() ?: CMbSecurity::generateUUID();
        } else {
            $full_url = null;
            if ($interaction instanceof CFHIRInteractionUpdate && $resource) {
                $full_url = CFHIRController::getUrl(
                    'fhir_read',
                    [
                        'resource' => $resource->getResourceType(),
                        'resource_id' => $resource->getResourceId(),
                    ]
                );
            }
        }

        $request = (new CFHIRDataTypeBundleRequest())
            ->setUrl($interaction->getBasePath())
            ->setMethod($interaction::METHOD);

        $entry = $this->addEntry()
            ->setRequestElement($request);

        if ($full_url) {
            $entry->setFullUrl($full_url);
        }

        if (in_array(get_class($interaction), [CFHIRInteractionCreate::class, CFHIRInteractionUpdate::class])) {
            $entry->setResourceElement(new CFHIRDataTypeResource($interaction->getResource()));
        }

        return $this;
    }
}
