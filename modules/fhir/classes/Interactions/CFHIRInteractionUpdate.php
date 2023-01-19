<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\Exception\CFHIRExceptionRequired;

/**
 * The update interaction creates a new current version for an existing resource or creates an initial version if no
 * resource already exists for the given id
 */
class CFHIRInteractionUpdate extends CFHIRInteraction
{
    /** @var string Interaction name */
    public const NAME = "update";

    /** @var string */
    public const METHOD = 'PUT';

    /**
     * @param array|null|string $data
     *
     * @return string|null
     */
    public function getBody($data): ?string
    {
        if (is_string($data) || !$data) {
            return $data ?: null;
        }

        return CMbArray::get($data, 0);
    }

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        if ((!$resource_id = $this->resource->getResourceId())) {
            $interaction_name = $this::NAME;
            throw new CFHIRExceptionRequired("Element 'resource_id' is missing in interaction '$interaction_name'");
        }

        // TODO Gérer le cas d'un conditional update
        return $this->resourceType . '/' . $resource_id;
    }
}
