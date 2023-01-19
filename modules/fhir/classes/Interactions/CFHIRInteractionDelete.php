<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Interactions;

use Ox\Interop\Fhir\Exception\CFHIRExceptionRequired;

/**
 * The delete interaction removes an existing resource
 */
class CFHIRInteractionDelete extends CFHIRInteraction
{
    /** @var string Interaction name */
    public const NAME = "delete";

    /** @var string */
    public const METHOD = 'DELETE';

    /** @var string */
    private $resource_id;

    public function __construct($resource = null, ?string $format = '')
    {
        parent::__construct($resource, $format);

        if ($this->resource && ($resource_id = $this->resource->getResourceId())) {
            $this->setResourceId($resource_id);
        }
    }

    /**
     * @param string $resource_id
     *
     * @return CFHIRInteractionDelete
     */
    public function setResourceId(string $resource_id): self
    {
        $this->resource_id = $resource_id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        if (!$this->resource_id) {
            $interaction_name = $this::NAME;
            throw new CFHIRExceptionRequired("Element 'resource_id' is missing in interaction '$interaction_name'");
        }

        return $this->resourceType . '/' . $this->resource_id;
    }
}
