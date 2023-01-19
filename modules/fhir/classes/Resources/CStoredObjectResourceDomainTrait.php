<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceMappingInterface;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeNarrative;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;

/**
 * Description
 */
trait CStoredObjectResourceDomainTrait
{
    use CStoredObjectResourceTrait;
    use CStoredObjectResourceIdentifierTrait;

    /**
     * Map property Extension
     *
     * @return array
     */
    public function mapExtension(): array
    {
        return [];
    }

    /**
     * Map property ModifierExtension
     *
     * @return array
     */
    public function mapModifierExtension(): array
    {
        return [];
    }

    /**
     * Map property Text
     *
     * @return CFHIRDataTypeNarrative|null
     */
    public function mapText(): ?CFHIRDataTypeNarrative
    {
        return null;
    }

    /**
     * Map property Contained
     *
     * @return CFHIRDataTypeResource[]
     */
    public function mapContained(): array
    {
        return [];
    }

    /**
     * @return ResourceMappingInterface
     */
    public function getMapping(): ResourceMappingInterface
    {
        return $this;
    }
}
