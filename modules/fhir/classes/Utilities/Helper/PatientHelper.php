<?php

/**
 * @package Mediboard\Fhir\Objects
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\Helper;

use Exception;
use Ox\Interop\Eai\Resolver\Identifiers\PIIdentifierResolver;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;

class PatientHelper
{
    /** @var string  */
    public const INS_NIR = CPatientINSNIR::OID_INS_NIR;
    /** @var string  */
    public const INS_NIA = CPatientINSNIR::OID_INS_NIA;

    /** @var string */
    private const TYPE_INS_SYSTEM = 'http://interopsante.org/CodeSystem/fr-v2-0203';

    /** @var string[] */
    private const MAPPING_INS_TYPE_CODE = [
        self::INS_NIR => 'INS-NIR',
        self::INS_NIA => 'INS-NIA',
    ];

    /**
     * Search INS (type) of patient in resource fhir
     *
     * @param CFHIRResourcePatient $resource_patient
     * @param string               $ins_type
     *
     * @return string|null
     * @throw CFHIRException|Exception
     */
    public static function getINS(CFHIRResourcePatient $resource_patient, string $ins_type = self::INS_NIR): ?string
    {
        if (!in_array($ins_type, [self::INS_NIR, self::INS_NIA])) {
            throw CFHIRException::tr('PatientHelper-msg-type INS invalid', $ins_type);
        }

        foreach ($resource_patient->getIdentifier() ?? [] as $identifier) {
            if (!$identifier->isSystemMatch($ins_type) || !$identifier->type) {
                continue;
            }

            foreach ($identifier->type->coding ?? [] as $type_identifier) {
                if (!$type_identifier->system || !$type_identifier->system->isSystemMatch(self::TYPE_INS_SYSTEM)) {
                    continue;
                }

                $type_code = $type_identifier->code ? $type_identifier->code->getValue() : null;
                if ($identifier->value && ($type_code === self::MAPPING_INS_TYPE_CODE[$ins_type])) {
                    return $identifier->value->getValue();
                }
            }
        }

        return null;
    }

    /**
     * Search IPP of patient in resource fhir
     *
     * @param CFHIRResourcePatient $resource_patient
     * @param string|null          $group_id
     *
     * @return string|null
     * @throws Exception
     */
    public static function getIPP(CFHIRResourcePatient $resource_patient, ?string $group_id): ?string
    {
        $identifier_resolver = (new PIIdentifierResolver())
            ->setGroup(CGroups::get($group_id))
            ->setModeOID();

        foreach ($resource_patient->getIdentifier() ?? [] as $identifier) {
            $system    = $identifier->system ? $identifier->system->getValue() : null;
            $id_number = $identifier->value ? $identifier->value->getValue() : null;

            // for now $type_code is required
            foreach ($identifier->type->coding ?? [] as $type_coding) {
                $type_code = $type_coding->code ? $type_coding->code->getValue() : null;

                if ($ipp = $identifier_resolver->resolve($id_number, $system, $type_code)) {
                    return $ipp;
                }
            }
        }

        return null;
    }

    public static function getTypeCodingIPP(): CFHIRDataTypeCoding
    {
        return CFHIRDataTypeCoding::addCoding(
            'http://interopsante.org/CodeSystem/fr-v2-0203',
            'PI',
            'Patient internal identifier'
        );
    }

    public static function getTypeCodingINS(): CFHIRDataTypeCoding
    {
        return CFHIRDataTypeCoding::addCoding(
            'http://interopsante.org/CodeSystem/fr-v2-0203',
            'INS-NIR',
            'NIR'
        );
    }

    /**
     * Search patient infos in resource and map on object CPatient
     *
     * @param CFHIRResourcePatient $resource_patient
     *
     * @return CPatient|null
     * @throws Exception
     */
    public static function primaryMapping(CFHIRResourcePatient $resource_patient): ?CPatient
    {
        $patient = new CPatient();

        // birthdate
        $patient->naissance = $resource_patient->getBirthDate() ? $resource_patient->getBirthDate()->getDate() : null;

        if ($resource_name = self::getOfficialName($resource_patient)) {
            // First name
            if ($resource_name->family && !$resource_name->family->isNull()) {
                $patient->nom = $resource_name->family->getValue();
            }

            // Given name
            if ($resource_name->given) {
                foreach ($resource_name->given as $key => $given) {
                    if ($key > 3) {
                        break;
                    }

                    $key = $key === 0 ? 'prenom' : ('_prenom_' . ($key + 1));

                    $patient->{$key} = $given->getValue();
                }
            }
        }

        $patient->deces = $resource_patient->getDeceased() instanceof CFHIRDataTypeDateTime
            ? $resource_patient->getDeceased()->getValue() : null;

        $patient->sexe = self::mapGender($resource_patient->getGender());

        return $patient;
    }

    /**
     * Search official name in the resource
     *
     * @param CFHIRResourcePatient $resource_patient
     *
     * @return CFHIRDataTypeHumanName|null
     */
    public static function getOfficialName(CFHIRResourcePatient $resource_patient): ?CFHIRDataTypeHumanName
    {
        foreach ($resource_patient->getName() ?? [] as $name) {
            if ($name->use && $name->use->getValue() === 'official') {
                return $name;
            }
        }

        return null;
    }

    /**
     * Map gender of patient
     *
     * @param CFHIRDataTypeCode|null $code
     *
     * @return string|null
     * @throws Exception
     */
    public static function mapGender(?CFHIRDataTypeCode $code): ?string
    {
        if (!$code || !$code->getValue()) {
            return null;
        }

        switch ($code->getValue()) {
            case 'male':
                return 'm';
            case 'female':
                return 'f';
            case 'unknown':
            default:
                return 'i';
        }
    }
}
