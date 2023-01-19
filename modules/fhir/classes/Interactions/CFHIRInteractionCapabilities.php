<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;

/**
 * The capabilities interaction retrieves the information about a server's capabilitie
 */
class CFHIRInteractionCapabilities extends CFHIRInteraction
{
    /** @var string Interaction name */
    public const NAME = "capabilities";

    public function __construct(
        $resourceType = CFHIRResourceCapabilityStatement::class,
        ?string $format = null
    ) {
        parent::__construct($resourceType, $format);
    }

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        return 'metadata';
    }
}
