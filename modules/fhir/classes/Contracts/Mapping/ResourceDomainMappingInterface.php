<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping;

use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeNarrative;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeResource;

/**
 * Description
 */
interface ResourceDomainMappingInterface extends ResourceMappingInterface
{
    /**
     * Map property Text
     *
     * @return CFHIRDataTypeNarrative|null
     */
    public function mapText(): ?CFHIRDataTypeNarrative;

    /**
     * @return CFHIRDataTypeResource[]
     */
    public function mapContained(): array;

    /**
     * Map property Extension
     *
     * @return CFHIRDataTypeExtension[]
     */
    public function mapExtension(): array;

    /**
     * Map property ModifierExtension
     *
     * @return CFHIRDataTypeExtension[]
     */
    public function mapModifierExtension(): array;
}
