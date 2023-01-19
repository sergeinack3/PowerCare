<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTimeImmutable;
use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDay;
use Ox\Core\CMbDT;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsuranceRuf;
use Ox\Mediboard\Jfse\Domain\Vital\AmoFamily;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\CoverageCodePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\InvoicingTla;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\Period;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Domain\Vital\WorkAccident;
use Ox\Mediboard\Jfse\Exceptions\VitalException;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Patients\CPatient;

class VitalCardMapper extends AbstractMapper
{
    public function arrayToVitalCard(array $data): VitalCard
    {
        if (!array_key_exists('donneescv', $data)) {
            throw VitalException::missingData();
        }

        $data = $data["donneescv"];

        $work_accident = (isset($data["donneesAT"])) ? $this->arrayToWorkAccident($data["donneesAT"]) : [];

        $amo_family_data = array_key_exists('serviceAMOFamille', $data) ? $data["serviceAMOFamille"] : [];
        $amo_family      = ($amo_family_data && !empty($amo_family_data)) ? $this->arrayToAmoFamily(
            $amo_family_data
        ) : [];

        $beneficiaries_data = $data["lstDonneesBeneficiaire"];
        $beneficiaries      = ($beneficiaries_data) ? $this->arrayToBeneficiaries($beneficiaries_data) : [];

        $insured = $this->arrayToInsured($data, $beneficiaries);

        $apcv = false;
        $apcv_context = null;
        if (
            array_key_exists('apCV', $data) && $data['apCV']
            && array_key_exists('contexteApCV', $data) && is_array($data['contexteApCV'])
        ) {
            $apcv = true;
            $apcv_context = (new ApCvMapper())->getApCvContextFromResponse($data['contexteApCV']);
        }

        return VitalCard::hydrate(
            [
                "group"                       => (int)$data["groupe"],
                "mode131"                     => (int)$data["mode131"],
                "selected_beneficiary_number" => (int)$data["noBeneficiaireSelectionne"],
                "type"                        => $data["type"],
                "serial_number"               => $data["numeroSerie"],
                "expiration_date"             => $data['dateFinValidite'] != '' ?
                    new DateTimeImmutable($data["dateFinValidite"]) : null,
                "ruf1_administration_data"    => $data["donneesAdministrationRUF1"],
                "ruf2_administration_data"    => $data["donneesAdministrationRUF2"],
                "administration_data"         => $data["donneesAdministration"],
                "insured"                     => $insured,
                "ruf_bearer_type"             => $data["typeRUFPorteur"],
                "ruf_family_data"             => $data["donneesRUFFamille"],
                "regime_label"                => $data["libelleRegime"],
                "fund_label"                  => $data["libelleCaisse"],
                "managing_label"              => $data["libelleGestion"],
                "amo_family_service"          => $amo_family,
                "work_accident_data"          => $work_accident,
                "beneficiaries"               => $beneficiaries,
                'apcv'                        => $apcv,
                'apcv_context'                => $apcv_context,
            ]
        );
    }

    /**
     * @return WorkAccident[]
     */
    private function arrayToWorkAccident(array $raw_data): array
    {
        $at1 = WorkAccident::hydrate(
            [
                "number"                 => 1,
                "group"                  => $raw_data["groupe"],
                "recipient_organisation" => $raw_data["organismeDestinataireAT1"],
                "code"                   => $raw_data["codeAT1"],
                "id"                     => $raw_data["identifiantAT1"],
            ]
        );

        $at2 = WorkAccident::hydrate(
            [
                "number"                 => 2,
                "group"                  => $raw_data["groupe"],
                "recipient_organisation" => $raw_data["organismeDestinataireAT2"],
                "code"                   => $raw_data["codeAT2"],
                "id"                     => $raw_data["identifiantAT2"],
            ]
        );

        $at3 = WorkAccident::hydrate(
            [
                "number"                 => 3,
                "group"                  => $raw_data["groupe"],
                "recipient_organisation" => $raw_data["organismeDestinataireAT3"],
                "code"                   => $raw_data["codeAT3"],
                "id"                     => $raw_data["identifiantAT3"],
            ]
        );

        return [$at1, $at2, $at3];
    }

    public function arrayToAmoFamily(array $data): AmoFamily
    {
        $begin = ($data["dateDebut"] !== "00000000") ? new DateTimeImmutable($data["dateDebut"]) : null;
        $end   = ($data["dateFin"] !== "00000000") ? new DateTimeImmutable($data["dateFin"]) : null;

        return AmoFamily::hydrate(
            [
                "code"       => $data["code"],
                "begin_date" => $begin,
                "end_date"   => $end,
                "group"      => $data["groupe"],
            ]
        );
    }

    /**
     * @return Beneficiary[]
     */
    private function arrayToBeneficiaries(array $data): array
    {
        return array_map(
            function (array $row) {
                return $this->arrayToBeneficiary($row);
            },
            $data
        );
    }

    public function arrayToBeneficiary(array $data): Beneficiary
    {
        $amo_rights = [];
        if (isset($data["lstPeriodeDroitsAMO"]) && count($data["lstPeriodeDroitsAMO"]) > 0) {
            $amo_rights = $this->arrayToPeriods($data["lstPeriodeDroitsAMO"]);
        }

        $coverage_periods = [];
        if (isset($data["lstPeriodeCodeCouverture"]) && count($data["lstPeriodeCodeCouverture"]) > 0) {
            $coverage_periods = $this->arrayToCoverageCodePeriods($data["lstPeriodeCodeCouverture"]);
        }

        $health_insurance = null;
        if (isset($data["donneesMutuelle"]) && count($data["donneesMutuelle"]) > 0) {
            $health_insurance = $this->arrayToHealthInsurance($data);
        }

        $additionnal_health_insurance = null;
        if (isset($data["donneesComplementaire"]) && count($data["donneesComplementaire"]) > 0) {
            $additionnal_health_insurance = $this->arrayToAdditionalHealthInsurance($data["donneesComplementaire"]);
        }

        $amo_service = null;
        if (isset($data["codeServiceAMO"]) && isset($data["dateDebutServiceAMO"])) {
            $amo_service = $this->arrayToAmoServicePeriod($data);
        }

        $apcv = false;
        $apcv_context = null;
        if (
            array_key_exists('apCV', $data) && $data['apCV']
            && array_key_exists('contexteApCV', $data) && is_array($data['contexteApCV'])
        ) {
            $apcv = true;
            $apcv_context = (new ApCvMapper())->getApCvContextFromResponse($data['contexteApCV']);
        }

        return Beneficiary::hydrate(
            [
                "id"                          => $data["id"],
                "group"                       => $data["groupe"],
                "number"                      => $data["numero"],
                "patient"                     => $this->arrayToPatient($data),
                "certified_nir"               => $data["NIRCertifie"],
                "certified_nir_key"           => $data["cleNIRCertifie"],
                "nir_certification_date"      =>
                    ($data["dateCertificationNIR"] && $data["dateCertificationNIR"] != '00000000')
                        ? new DateTimeImmutable($data["dateCertificationNIR"]) : null,
                "quality"                     => $data["qualite"],
                "quality_label"               => $data["libelleQualite"],
                "amo_service"                 => $amo_service,
                "insc_number"                 => $data["INSC"],
                "insc_key"                    => $data["cleINSC"],
                "insc_error"                  => $data["erreurINSC"],
                "acs"                         => $data["ACS"],
                "acs_label"                   => $data["ACSLibelle"],
                "amo_period_rights"           => $amo_rights,
                "coverage_code_periods"       => $coverage_periods,
                "health_insurance"            => $health_insurance,
                "additional_health_insurance" => $additionnal_health_insurance,
                'apcv'                        => $apcv,
                'apcv_context'                => $apcv_context,
            ]
        );
    }

    /**
     * @return Period[]
     */
    private function arrayToPeriods(array $data): array
    {
        return array_map(
            function (array $row): Period {
                return $this->arrayToPeriod($row);
            },
            $data
        );
    }

    /**
     * @throws Exception
     */
    private function arrayToPeriod(array $data): Period
    {
        return Period::hydrate(
            [
                "group"      => $data["groupe"],
                "begin_date" => new DateTimeImmutable($data["dateDebut"]),
                "end_date"   => new DateTimeImmutable($data["dateFin"]),
            ]
        );
    }

    /**
     * @return CoverageCodePeriod[]
     */
    private function arrayToCoverageCodePeriods(array $data): array
    {
        return array_map(
            function (array $row): CoverageCodePeriod {
                $begin = ($row["dateDebut"] && ($row["dateDebut"] !== "00000000" || '' !== $row['dateDebut']))
                    ? new DateTimeImmutable($row["dateDebut"]) : null;
                $end   = ($row["dateFin"] && ($row["dateFin"] !== "00000000" || '' !== $row['dateFin']))
                    ? new DateTimeImmutable($row["dateFin"]) : null;

                return CoverageCodePeriod::hydrate(
                    [
                        "group"                     => $row["groupe"],
                        "begin_date"                => $begin,
                        "end_date"                  => $end,
                        "ald_code"                  => $row["codeALD"],
                        "situation_code"            => $row["codeSituation"],
                        "standard_exoneration_code" => $row["codeExoStandard"],
                        "standard_rate"             => $row["tauxStandard"],
                        "alsace_mozelle_flag"       => $row["flagAlsaceMoselle"],
                    ]
                );
            },
            $data
        );
    }

    private function arrayToHealthInsurance(array $data): HealthInsurance
    {
        $data = $data["donneesMutuelle"];

        $insurance_periods = [];
        if (isset($data["lstPeriodeDroitsMutuelle"]) && is_countable($data["lstPeriodeDroitsMutuelle"])) {
            $insurance_periods = $this->arrayToPeriods($data["lstPeriodeDroitsMutuelle"]);
        }


        return HealthInsurance::hydrate(
            [
                "group"                           => $data["groupe"],
                "id"                              => $data["identificationMutuelle"],
                "effective_guarantees"            => $data["garantiesEffectives"],
                "treatment_indicator"             => $data["indicateurTraitement"],
                "associated_services"             => $data["typeServicesAssocies"],
                "associated_services_contract"    => $data["servicesAssociesContrat"],
                "referral_sts_code"               => $data["codeAiguillageSTS"],
                "label"                           => $data["libelle"],
                "health_insurance_periods_rights" => $insurance_periods,
            ]
        );
    }

    /**
     * @throws Exception
     */
    private function arrayToAdditionalHealthInsurance(array $data): AdditionalHealthInsurance
    {
        $tla = CMbArray::get($data, 'contexteFacturationTLA');

        return AdditionalHealthInsurance::hydrate(
            [
                "group"                        => $data["groupe"],
                "number_b2"                    => $data["numeroComplementaireB2"],
                "number_edi"                   => $data["numeroComplementaireEDI"],
                "subscriber_number"            => $data["numeroAdherent"],
                "treatment_indicator"          => $data["indicateurTraitement"],
                "begin_date"                   => new DateTimeImmutable($data["dateDebut"]),
                "end_date"                     => new DateTimeImmutable($data["dateFin"]),
                "routing_code"                 => $data["codeRoutage"],
                "host_id"                      => $data["identifiantHote"],
                "domain_name"                  => $data["nomDomaine"],
                "referral_sts_code"            => $data["codeAiguillageSTS"],
                "services_type"                => CMbArray::get($data, 'typeServices'),
                "associated_services_contract" => $data["servicesAssociesContrat"],
                "label"                        => $data["libelle"],
                "rufs"                         => $this->arrayToAdditionalHealthInsuranceRufs(
                    $data["lstDonneesRUFComplementaire"]
                ),
                "invoicing_tla"                => $tla,
            ]
        );
    }

    /**
     * @throws Exception
     */
    private function arrayToInvoicingTla(array $data): InvoicingTla
    {
        return InvoicingTla::hydrate(
            [
                "group"                     => $data["groupe"],
                "vital_card_reading_date"   => new DateTimeImmutable($data["dateLectureCarteVitale"]),
                "type"                      => $data["typeContexteFacturation"],
                "third_party_accident"      => $data["accidentDC"],
                "third_party_accident_date" => new DateTimeImmutable($data["dateAccidentDC"]),
                "maternity_date"            => new DateTimeImmutable($data["dateMaternite"]),
                "work_accident_date"        => new DateTimeImmutable($data["dateAT"]),
                "work_accident_number"      => $data["numeroAT"],
                "amc_zone"                  => $data["zoneAMC"],
            ]
        );
    }

    /**
     * @return AdditionalHealthInsuranceRuf[]
     */
    private function arrayToAdditionalHealthInsuranceRufs(array $data): array
    {
        return array_map(
            function (array $row): AdditionalHealthInsuranceRuf {
                return AdditionalHealthInsuranceRuf::hydrate(["group" => $row["groupe"], "data" => $row["donneesRUF"]]);
            },
            $data
        );
    }

    private function arrayToAmoServicePeriod(array $data): AmoServicePeriod
    {
        return AmoServicePeriod::hydrate(
            [
                "group"      => $data["groupe"],
                "code"       => $data["codeServiceAMO"],
                "begin_date" => ($data["dateDebutServiceAMO"] && $data["dateDebutServiceAMO"] != '00000000')
                    ? new DateTimeImmutable($data["dateDebutServiceAMO"]) : null,
                "end_date"   => ($data["dateFinServiceAMO"] && $data["dateFinServiceAMO"] != '00000000')
                    ? new DateTimeImmutable($data["dateFinServiceAMO"]) : null,
                "ruf_data"   => $data["donneesRUFAMO"],
            ]
        );
    }

    public function arrayToPatient(array $data): Patient
    {
        $address = null;
        if (
            $data['adresse1'] !== '' || $data['adresse2'] !== ''
            || $data['adresse3'] !== '' || $data['adresse4'] !== ''
        ) {
            $address = trim(implode(' ', [$data["adresse1"], $data["adresse2"], $data["adresse3"], $data["adresse4"]]));
        }

        $zip_code = null;
        $city = null;
        if ($data['adresse5'] && preg_match("/([0-9]{5}) ([A-Za-z '\-.0-9]+)/", $data['adresse5'], $matches)) {
            $zip_code = $matches[1];
            $city     = $matches[2];
        }
        $birth     = $data["dateNaissance"];

        return Patient::hydrate(
            [
                "last_name"  => CMbArray::get($data, "nomUsuel"),
                "birth_name" => CMbArray::get($data, "nomPatronymique") ?: CMbArray::get($data, "nomUsuel"),
                "birth_date" => substr($birth, 0, 4) . '-' . substr($birth, 4, 2) . '-' . substr($birth, 6, 2),
                "first_name" => $data["prenom"],
                "address"    => $address,
                "birth_rank" => (int)$data["rangGemellaire"],
                'zip_code'   => $zip_code,
                'city'       => $city,
            ]
        );
    }

    private function arrayToInsured(array $data, array $beneficiaries): Insured
    {
        /** @var Beneficiary $beneficiary */
        if (count($beneficiaries) > 1) {
            $beneficiary = array_filter(
                $beneficiaries,
                function (Beneficiary $beneficiary): bool {
                    return $beneficiary->getQuality() == "0";
                }
            );
            $beneficiary = reset($beneficiary);
        } else {
            $beneficiary = reset($beneficiaries);
        }

        $insured =  Insured::hydrate(
            [
                "nir"             => $data["NIR"],
                "nir_key"         => $data["cleNIR"],
                "first_name"      => $beneficiary->getPatient()->getFirstName(),
                "last_name"       => $beneficiary->getPatient()->getLastName(),
                "birth_name"      => $beneficiary->getPatient()->getBirthName(),
                "regime_code"     => $data["codeRegime"],
                "managing_fund"   => $data["caisseGestionnaire"],
                "managing_center" => $data["centreGestionnaire"],
                "managing_code"   => $data["codeGestion"],
                'address'         => $beneficiary->getPatient()->getAddress(),
                'zip_code'        => $beneficiary->getPatient()->getZipCode(),
                'city'            => $beneficiary->getPatient()->getCity(),
            ]
        );

        /** @var Beneficiary $beneficiary */
        foreach ($beneficiaries as $beneficiary) {
            $beneficiary->setInsured($insured);
        }

        return $insured;
    }

    public static function getBeneficiaryFromPatient(
        CPatient $patient,
        bool $amo_informations = false,
        string $situation_code = null
    ): Beneficiary {
        $data = [
            'quality'       => $patient->qual_beneficiaire ? (int)$patient->qual_beneficiaire : 1,
            'integrator_id' => $patient->_guid,
            'patient'       => Patient::hydrate([
                'birth_date' => $patient->naissance ? Utils::dateToJfseFormat($patient->naissance) : null,
                'birth_rank' => (int)$patient->rang_naissance,
                'last_name'  => $patient->nom,
                'first_name' => $patient->prenom,
            ]),
        ];

        $insured_data = ['nir' => $patient->assure_matricule];
        if ($patient->matricule) {
            $data['certified_nir'] = $patient->matricule;
        }

        if ($amo_informations) {
            $insured_data['regime_code'] = $patient->code_regime;
            $insured_data['managing_code'] = $patient->code_gestion;
            $insured_data['managing_fund'] = $patient->caisse_gest;
            $insured_data['managing_center'] = $patient->centre_gest;
            if ($situation_code) {
                $insured_data['situation_code'] = $situation_code;
            }
        }

        $data['insured'] = Insured::hydrate($insured_data);

        return Beneficiary::hydrate($data);
    }

    public static function getBeneficiaryFromPatientDataModel(
        CJfsePatient $data_model,
        bool $amo_informations = false,
        string $situation_code = null
    ): Beneficiary {
        $data_model->loadPatient();

        $data = [
            'quality'       => (int)$data_model->quality,
            'integrator_id' => $data_model->_patient->_guid,
            'patient'       => Patient::hydrate([
                'birth_date' => Utils::dateToJfseFormat($data_model->birth_date),
                'birth_rank' => (int)$data_model->birth_rank,
                'last_name'  => $data_model->last_name,
                'first_name' => $data_model->first_name,
            ]),
        ];

        $insured_data = ['nir' => $data_model->nir];
        if ($data_model->certified_nir) {
            $data['certified_nir'] = $data_model->certified_nir;
        }

        if ($amo_informations) {
            $insured_data['regime_code'] = $data_model->amo_regime_code;
            $insured_data['managing_code'] = $data_model->amo_managing_code;
            $insured_data['managing_fund'] = $data_model->amo_managing_fund;
            $insured_data['managing_center'] = $data_model->amo_managing_center;
            if ($situation_code) {
                $insured_data['situation_code'] = $situation_code;
            }
        }

        $data['insured'] = Insured::hydrate($insured_data);

        return Beneficiary::hydrate($data);
    }

    /**
     * @param string $json
     *
     * @return Beneficiary[]
     */
    public static function getBeneficiariesFromJson(string $json): array
    {
        $beneficiaries = [];

        $data = json_decode($json, true);
        foreach ($data as $datum) {
            $beneficiaries[] = self::getBeneficiaryFromJson(array_map_recursive('utf8_decode', $datum));
        }

        return $beneficiaries;
    }

    private static function getBeneficiaryFromJson(array $beneficiary): Beneficiary
    {
        $beneficiary["patient"] = Patient::hydrate($beneficiary["patient"]);
        $beneficiary['insured'] = Insured::hydrate($beneficiary['insured']);

        /* Sets the Additional Health Insurance */
        if (array_key_exists('additional_health_insurance', $beneficiary)) {
            if (array_key_exists('begin_date', $beneficiary['additional_health_insurance'])) {
                $beneficiary['additional_health_insurance']['begin_date'] = new DateTimeImmutable(
                    $beneficiary['additional_health_insurance']['begin_date']
                );
            }

            if (array_key_exists('end_date', $beneficiary['additional_health_insurance'])) {
                $beneficiary['additional_health_insurance']['end_date'] = new DateTimeImmutable(
                    $beneficiary['additional_health_insurance']['end_date']
                );
            }

            $beneficiary['additional_health_insurance'] = AdditionalHealthInsurance::hydrate(
                $beneficiary['additional_health_insurance']
            );
        }

        /* Sets the Health Insurance */
        if (array_key_exists('health_insurance', $beneficiary)) {
            if (
                array_key_exists('begin_date', $beneficiary['health_insurance'])
                || array_key_exists('end_date', $beneficiary['health_insurance'])
            ) {
                $period = [];
                if (array_key_exists('begin_date', $beneficiary['health_insurance'])) {
                    $period['begin_date'] = new DateTimeImmutable(
                        $beneficiary['health_insurance']['begin_date']
                    );
                }

                if (array_key_exists('end_date', $beneficiary['health_insurance'])) {
                    $period['end_date'] = new DateTimeImmutable(
                        $beneficiary['health_insurance']['end_date']
                    );
                }

                $beneficiary['health_insurance']['health_insurance_periods_rights'] = Period::hydrate($period);
            }

            $beneficiary['health_insurance'] = HealthInsurance::hydrate(
                $beneficiary['health_insurance']
            );
        }

        /* Set the AMO period rights */
        if (
            array_key_exists('amo_period_rights', $beneficiary) && is_array($beneficiary['amo_period_rights'])
            && array_key_exists('begin_date', $beneficiary['amo_period_rights'])
            && array_key_exists('end_date', $beneficiary['amo_period_rights'])
        ) {
            $period = [];
            if (array_key_exists('begin_date', $beneficiary['amo_period_rights'])) {
                $period['begin_date'] = new DateTimeImmutable(
                    $beneficiary['amo_period_rights']['begin_date']
                );
            }

            if (array_key_exists('end_date', $beneficiary['amo_period_rights'])) {
                $period['end_date'] = new DateTimeImmutable(
                    $beneficiary['amo_period_rights']['end_date']
                );
            }

            $beneficiary['amo_period_rights'] = Period::hydrate($period);
        }

        /* Set the coverage periods */
        if (array_key_exists('coverage_code_periods', $beneficiary)) {
            $periods = [];
            /* Handle the case where there is only one period */
            if (array_key_exists('situation_code', $beneficiary['coverage_code_periods'])) {
                $beneficiary['coverage_code_periods']['begin_date'] =
                    ($beneficiary['coverage_code_periods']['begin_date'] !== null
                        && $beneficiary['coverage_code_periods']['begin_date'] !== '') ?
                        new DateTimeImmutable($beneficiary['coverage_code_periods']['begin_date']) : null;
                $beneficiary['coverage_code_periods']['end_date'] =
                    ($beneficiary['coverage_code_periods']['end_date'] !== null
                        && $beneficiary['coverage_code_periods']['end_date'] !== '') ?
                        new DateTimeImmutable($beneficiary['coverage_code_periods']['end_date']) : null;
                    $periods[] = CoverageCodePeriod::hydrate($beneficiary['coverage_code_periods']);
            } elseif (is_array($beneficiary['coverage_code_periods'])) {
                foreach ($beneficiary['coverage_code_periods'] as $period) {
                    $period['begin_date'] = ($period['begin_date'] !== null && $period['begin_date'] !== '') ?
                        new DateTimeImmutable($period['begin_date']) : null;
                    $period['end_date'] = ($period['end_date'] !== null && $period['end_date'] !== '') ?
                        new DateTimeImmutable($period['end_date']) : null;
                    $periods[] = CoverageCodePeriod::hydrate($period);
                }
            }

            $beneficiary['coverage_code_periods'] = $periods;
        }

        return Beneficiary::hydrate($beneficiary);
    }
}
