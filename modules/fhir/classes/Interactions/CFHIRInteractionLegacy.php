<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

class CFHIRInteractionLegacy extends CFHIRInteraction
{
    /**
     * @param string      $interaction_class
     * @param             $resourceType
     * @param string|null $format
     *
     * @return CFHIRInteraction
     */
    public static function createInteraction(string $interaction_class, $resourceType, ?string $format = null): ?CFHIRInteraction
    {
        if (!is_subclass_of($interaction_class, CFHIRInteraction::class)) {
            return null;
        }

        /** @var CFHIRInteraction $interaction */
        $interaction = new $interaction_class(null, $format);
        $interaction->resourceType = $resourceType;

        return $interaction;
    }
}
