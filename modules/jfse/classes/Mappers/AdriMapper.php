<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTime;
use DateTimeImmutable;
use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Domain\Adri\Adri;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\CoverageCodePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\Period;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;

class AdriMapper extends AbstractMapper
{
    /** @var VitalCardMapper */
    private $vital_card_mapper;

    public function __construct(VitalCardMapper $vital_card_mapper = null)
    {
        $this->vital_card_mapper = $vital_card_mapper ?? new VitalCardMapper();
    }


    public function beneficiaryToArray(Beneficiary $beneficiary): array
    {
        $patient = $beneficiary->getPatient();
        $insured = $beneficiary->getInsured();

        $birth_date = $patient->getBirthDate();
        $birth_date = str_replace('-', '', $birth_date);

        $data = [
            "codeRegime"      => $insured->getRegimeCode(),
            "immatriculation" => $insured->getNir() . $insured->getNirKey(),
            "dateNaissance"   => $birth_date,
            "rangGemellaire"  => $patient->getBirthRank(),
        ];

        if ($insured->getManagingFund()) {
            $data['codeCaisse'] = substr($insured->getManagingFund(), 0, 3);
        }

        if ($insured->getManagingCenter()) {
            $data['codeCentre'] = substr($insured->getManagingCenter(), 0, 4);
        }

        if ($beneficiary->getFullCertifiedNir()) {
            $data['nirCertifie'] = $beneficiary->getFullCertifiedNir();
        }

        if ($beneficiary->getNirCertificationDate()) {
            $data['dateNirCertifie'] = $beneficiary->getNirCertificationDate()->format('Ymd');
        }

        if ($patient->getLastName()) {
            $data['nom'] = $patient->getLastName();
        }

        if ($patient->getFirstName()) {
            $data['prenom'] = $patient->getFirstName();
        }

        return $data;
    }

    public function arrayToAdri(array $data): Adri
    {
        return Adri::hydrate([
            "response_date" => new DateTimeImmutable($data["dateReponse"]),
            "vital_card"    => $this->arrayToVitalCard(
                $data["infosGlobales"],
                $data["beneficiaireDeSoins"],
                CMbArray::get($data, 'mutuelle', [])
            ),
        ]);
    }

    private function arrayToVitalCard(
        array $global_infos,
        array $beneficiary,
        array $health_insurance
    ): VitalCard {
        $health_insurance_data = null;
        if (count($health_insurance)) {
            $health_insurance_data  = HealthInsurance::hydrate([
                "id"                           => CMbArray::get($health_insurance, "noIdentification"),
                "treatment_indicator"          => CMbArray::get($health_insurance, "indicateurTraitement"),
                "referral_sts_code"            => CMbArray::get($health_insurance, "codeAiguillageSTS"),
                "associated_services"          => CMbArray::get($health_insurance, "typeServicesAssocies"),
                "associated_services_contract" => CMbArray::get($health_insurance, "servicesAssociesAuContrat"),
                'health_insurance_periods_rights' => CMbArray::get($health_insurance, 'periodeMutuelle')
                    ? Period::hydrate([
                        "group"      => CMbArray::get($health_insurance["periodeMutuelle"], "noPeriode"),
                        "begin_date" => self::toDateTimeImmutableOrNull(
                            $health_insurance["periodeMutuelle"],
                            "dateDebut"
                        ),
                        "end_date"   => self::toDateTimeImmutableOrNull(
                            $health_insurance["periodeMutuelle"],
                            "dateFin"
                        ),
                ]) : null,
                "code_presentation_support"    => $global_infos["codePresentationSupportAMO"],
            ]);
        }

        return VitalCard::hydrate([
            "regime_code"     => $global_infos["codeRegime"],
            "managing_fund"   => $global_infos["codeCaisse"],
            "managing_center" => $global_infos["centrePrestation"],
            "beneficiaries"   => [
                Beneficiary::hydrate([
                    "number"                      => 4,
                    "patient"                     => $this->vital_card_mapper->arrayToPatient($beneficiary),
                    "certified_nir"               => $beneficiary["noNIRCertifie"],
                    "quality"                     => $beneficiary["qualite"],
                    "amo_service"                 => AmoServicePeriod::hydrate([
                        "code"       => $beneficiary["codeServiceAMO"],
                        "begin_date" => self::toDateTimeImmutableOrNull(
                            $beneficiary,
                            "dateDebutCodeServiceAMO"
                        ),
                        "end_date"   => self::toDateTimeImmutableOrNull(
                            $beneficiary,
                            "dateFinCodeServiceAMO"
                        ),
                    ]),
                    "additional_health_insurance" => AdditionalHealthInsurance::hydrate([
                        "code_support"    => CMbArray::get($beneficiary, "codeSupportComplementaire"),
                        "guarantees_code" => CMbArray::get($health_insurance, "codeGarantiesComplementaires"),
                    ]),
                    "prescribing_physician_top"   => $beneficiary["topMedecinTraitant"],
                    "amo_period_rights"           => $this->arrayToAmoPeriodRights(
                        CMbArray::get($beneficiary, "lstPeriodeAMO"),
                        $beneficiary["codeServiceAMO"]
                    ),
                    "coverage_code_periods"       => $this->arrayToCoverageCodePeriod(
                        CMbArray::get($beneficiary, "lstPeriodeCodeCouverture")
                    ),
                    'health_insurance'            => $health_insurance_data,
                    'insured' => Insured::hydrate([
                        "regime_code"     => $global_infos["codeRegime"],
                        "managing_fund"   => $global_infos["codeCaisse"],
                        "managing_center" => $global_infos["centrePrestation"],
                    ]),
                ]),
            ],
        ]);
    }

    private function arrayToAmoPeriodRights(?array $period_rights, string $code_service): array
    {
        return array_map(
            function (array $row) use ($code_service): AmoServicePeriod {
                return AmoServicePeriod::hydrate([
                    "begin_date" => new DateTimeImmutable($row["dateDebut"]),
                    "end_date"   => new DateTimeImmutable($row["dateFin"]),
                    "code"       => $code_service,
                ]);
            },
            $period_rights ?? []
        );
    }

    private function arrayToCoverageCodePeriod(?array $period): array
    {
        return array_map(
            function (array $period): CoverageCodePeriod {
                return CoverageCodePeriod::hydrate([
                    "group"          => $period["groupe"],
                    "begin_date"     => self::toDateTimeImmutableOrNull($period, "dateDebut"),
                    "end_date"       => self::toDateTimeImmutableOrNull($period, "dateFin"),
                    "ald_code"       => $period["codeALD"],
                    "situation_code" => $period["codeSituation"],
                ]);
            },
            $period ?? []
        );
    }
}
