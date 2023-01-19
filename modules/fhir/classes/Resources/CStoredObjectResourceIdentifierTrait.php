<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources;

use Exception;
use Ox\Core\CAppUI;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;

/**
 * Description
 */
trait CStoredObjectResourceIdentifierTrait
{
    use CStoredObjectResourceTrait;

    /**
     * Map property identifier
     *
     * @return array
     * @throws Exception
     */
    public function mapIdentifier(): array
    {
        if (!$this->object || !$this->object->_id) {
            return [];
        }

        $type = (new CFHIRDataTypeCodeableConcept())
            ->setCoding(
                (new CFHIRDataTypeCoding())
                    ->setSystem('http://terminology.hl7.org/CodeSystem/v2-0203')
                    ->setCode('RI')
                    ->setDisplay('Resource identifier'),
                (new CFHIRDataTypeCoding())
                    ->setSystem('http://interopsante.org/fhir/CodeSystem/fr-v2-0203')
                    ->setCode('INTRN')
                    ->setDisplay('Identifiant interne')
            );

        $identifiers[] = (new CFHIRDataTypeIdentifier())
            ->setSystem(CAppUI::conf('mb_oid'))
            ->setType($type)
            ->setValue($this->object->_id);

        return $identifiers;
    }
}
