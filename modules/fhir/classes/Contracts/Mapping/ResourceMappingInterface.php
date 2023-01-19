<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeMeta;

/**
 * Description
 */
interface ResourceMappingInterface
{
    /**
     * Map property Id
     *
     * @return CFHIRDataTypeString|null
     */
    public function mapId(): ?CFHIRDataTypeString;

    /**
     * Map property Meta
     *
     * @return CFHIRDataTypeMeta|null
     */
    public function mapMeta(): ?CFHIRDataTypeMeta;

    /**
     * Map property ImplicitRules
     *
     * @return CFHIRDataTypeUri|null
     */
    public function mapImplicitRules(): ?CFHIRDataTypeUri;

    /**
     * Map property Language
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapLanguage(): ?CFHIRDataTypeCode;
}
