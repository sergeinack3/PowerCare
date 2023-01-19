<?php

/**
 * @package Mediboard\Fhir\Objects
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\Helper;

use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Resources\R4\Encounter\CFHIRResourceEncounter;

class SejourHelper
{
    /**
     * @return CFHIRDataTypeCoding
     */
    public static function getTypeCodingNDA(): CFHIRDataTypeCoding
    {
        return CFHIRDataTypeCoding::addCoding(
            'http://interopsante.org/fhir/ValueSet/fr-encounter-identifier-type',
            'VN',
            'Visit Number'
        );
    }

    /**
     * Search NDA identifier in resource encounter
     *
     * @param CFHIRResourceEncounter $encounter
     * @param string                 $nda_oid
     *
     * @return string|null
     */
    public static function getNDA(CFHIRResourceEncounter $encounter, string $nda_oid): ?string
    {
        $coding_nda = self::getTypeCodingNDA();

        foreach ($encounter->getIdentifier() as $identifier) {
            // search coding for NDA (system_oid and code)
            if (!$identifier->type->getCoding($coding_nda->system->getValue(), $coding_nda->code->getValue())) {
                continue;
            }

            if (!$identifier->isSystemMatch($nda_oid)) {
                continue;
            }

            return $identifier->value->getValue();
        }

        return null;
    }
}
