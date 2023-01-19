<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

/**
 * FHIR data type
 */
class CFHIRDataTypeString extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'String';

    /**
     * @param string|null $value
     */
    public function setValue($value): self
    {
        if ($value !== null) {
            $value = utf8_encode($value);
        }

        parent::setValue($value);

        return $this;
    }
}
