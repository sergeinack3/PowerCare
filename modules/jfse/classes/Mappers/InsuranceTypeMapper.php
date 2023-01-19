<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\InsuranceType\AbstractInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\Insurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MaternityInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\MedicalInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;

/**
 * Class InsuranceTypeMapper
 *
 * @package Ox\Mediboard\Jfse\Mappers
 */
class InsuranceTypeMapper extends AbstractMapper
{
    public static function getTypesFromResponse(Response $response): array
    {
        $types = [];
        $data = CMbArray::get($response->getContent(), 'lst', []);

        foreach ($data as $index => $type) {
            $types[$index] = [
                'code' => CMbArray::get($type, 'code'),
                'label' => CMbArray::get($type, 'libelle'),
            ];
        }

        return $types;
    }

    /**
     * @param AbstractInsurance $insurance
     * @param string            $invoice_id
     *
     * @return array
     */
    public function getArrayFromInsuranceType(AbstractInsurance $insurance, string $invoice_id): array
    {
        $data = [
            "idFacture"       => $invoice_id,
            "natureAssurance" => [],
        ];

        if ($insurance instanceof MedicalInsurance) {
            $data["natureAssurance"]['maladie'] = self::medicalInsuranceToArray($insurance);
        } elseif ($insurance instanceof WorkAccidentInsurance) {
            $data["natureAssurance"]['AT'] = self::workAccidentInsuranceToArray($insurance);
        } elseif ($insurance instanceof MaternityInsurance) {
            $data["natureAssurance"]['maternite'] = self::maternityInsuranceToArray($insurance);
        } elseif ($insurance instanceof FmfInsurance) {
            $data["natureAssurance"]['SMG'] = self::smgInsuranceToArray($insurance);
        }

        return $data;
    }

    /**
     * @param MedicalInsurance $type
     *
     * @return array
     */
    private static function medicalInsuranceToArray(MedicalInsurance $type): array
    {
        return [
            "codeExoneration" => $type->getCodeExonerationDisease(),
        ];
    }

    /**
     * @param WorkAccidentInsurance $type
     *
     * @return array
     */
    private static function workAccidentInsuranceToArray(WorkAccidentInsurance $type): array
    {
        $data = [
            "date"             => $type->getDate()->format('Ymd'),
            "presenceFeuillet" => (int)$type->getHasPhysicalDocument(),
        ];

        self::addOptionalValue("numero", $type->getNumber(), $data);
        self::addOptionalValue("refCaisseSupport", $type->getOrganisationSupport(), $data);
        self::addOptionalValue("caisseIdentiqueAMO", (int)$type->getIsOrganisationIdenticalAmo(), $data);
        if ($type->getOrganisationVital()) {
            $data['refCaisseCV'] = $type->getOrganisationVital();
        }
        self::addOptionalValue("priseEnChargeArmateur", (int)$type->getShipownerSupport(), $data);
        self::addOptionalValue("montantPECApias", $type->getAmountApias(), $data);

        return $data;
    }


    /**
     * @param MaternityInsurance $type
     *
     * @return array
     */
    private static function maternityInsuranceToArray(MaternityInsurance $type): array
    {
        $data = [
            "date" => $type->getDate()->format('Ymd'),
        ];

        self::addOptionalValue("forcage", (int)$type->getForceExoneration(), $data);

        return $data;
    }

    /**
     * @param FmfInsurance $type
     *
     * @return array
     */
    private static function smgInsuranceToArray(FmfInsurance $type): array
    {
        $data = [
            "existencePEC" => (int)$type->getSupportedFmfExistence(),
        ];

        self::addOptionalValue("montantPEC", $type->getSupportedFmfExpense(), $data);

        return $data;
    }

    public static function getInsuranceTypeFromResponse(array $response): Insurance
    {
        return Insurance::hydrate([
            'selected_insurance_type' => CMbArray::get($response, 'natureAssurance'),
            'medical_insurance'       => self::getMedicalInsuranceFromResponse(CMbArray::get($response, 'maladie', [])),
            'maternity_insurance' => self::getMaternityInsuranceFromResponse(CMbArray::get($response, 'maternite', [])),
            'work_accident_insurance' => self::getWorkAccidentInsuranceFromResponse(CMbArray::get($response, 'AT', [])),
            'fmf_insurance' => self::getFmfInsuranceFromResponse(CMbArray::get($response, 'SMG', []))
        ]);
    }

    private static function getMedicalInsuranceFromResponse(array $response): MedicalInsurance
    {
        return MedicalInsurance::hydrate([
            'code_exoneration_disease' => (int)CMbArray::get($response, 'codeExoneration')
        ]);
    }

    private static function getWorkAccidentInsuranceFromResponse(array $response): WorkAccidentInsurance
    {
        $at =  WorkAccidentInsurance::hydrate([
            'date' => CMbArray::get($response, 'date') ?
                new DateTimeImmutable(CMbArray::get($response, 'date')) : null,
            'has_physical_document' => (bool)CMbArray::get($response, 'presenceFeuillet'),
            'number' => CMbArray::get($response, 'numero') ? CMbArray::get($response, 'numero') : null,
            'organisation_support' => CMbArray::get($response, 'refCaisseSupport')
                ? CMbArray::get($response, 'refCaisseSupport') : null,
            'is_organisation_identical_amo' => (bool)CMbArray::get($response, 'caisseIdentiqueAMO'),
            'organisation_vital' => CMbArray::get($response, 'refCaisseCV'),
            'amount_apias' => CMbArray::get($response, 'montantPECApias')
                ? (float)CMbArray::get($response, 'montantPECApias') : null,
            'shipowner_support' => (bool)CMbArray::get($response, 'priseEnChargeArmateur'),
        ]);

        return $at;
    }

    private static function getFmfInsuranceFromResponse(array $response): FmfInsurance
    {
        return FmfInsurance::hydrate([
            'supported_fmf_existence' => (bool)CMbArray::get($response, 'existencePEC'),
            'supported_fmf_expense'   => (float)CMbArray::get($response, 'montantPEC'),
        ]);
    }

    private static function getMaternityInsuranceFromResponse(array $response): MaternityInsurance
    {
        return MaternityInsurance::hydrate([
            'date' => CMbArray::get($response, 'date') ?
                new DateTimeImmutable(CMbArray::get($response, 'date')) : null,
            'force_exoneration' => (bool)CMbArray::get($response, 'forcageExoMaternite'),
        ]);
    }

    public static function getArrayFromInsurance(Insurance $insurance): array
    {
        $data = [];

        switch ($insurance->getSelectedInsuranceType()) {
            case MedicalInsurance::CODE:
                $data['maladie'] = self::medicalInsuranceToArray($insurance->getMedicalInsurance());
                break;
            case MaternityInsurance::CODE:
                $data['maternite'] = self::maternityInsuranceToArray($insurance->getMaternityInsurance());
                break;
            case WorkAccidentInsurance::CODE:
                $data['AT'] = self::workAccidentInsuranceToArray($insurance->getWorkAccidentInsurance());
                break;
            case FmfInsurance::CODE:
                $data['SMG'] = self::smgInsuranceToArray($insurance->getFmfInsurance());
                break;
            default:
        }

        return $data;
    }
}
