<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Convention\Convention;
use Ox\Mediboard\Jfse\Domain\Convention\Correspondence;
use Ox\Mediboard\Jfse\Domain\Convention\Grouping;

class ConventionMapper extends AbstractMapper
{
    public function makeStoreArrayFromConvention(Convention $convention): array
    {
        $data = [
            "updateConvention" => [
                "idJfse" => $convention->getJfseId(),
            ],
        ];

        $this->addOptionalValue(
            "idConvention",
            $convention->getConventionId(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "numOrganismeSignataire",
            $convention->getSignerOrganizationNumber(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "typeConvention",
            $convention->getConventionType(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "critereSecondaire",
            $convention->getSecondaryCriteria(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "typeAccord",
            $convention->getAgreementType(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "libOrganismeSignataire",
            $convention->getSignerOrganizationLabel(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "numAMC",
            $convention->getAmcNumber(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "libelleAMC",
            $convention->getAmcLabel(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "operateurReglement",
            $convention->getStatutoryOperator(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "codeRoutage",
            $convention->getRoutingCode(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "identifiantHote",
            $convention->getHostId(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "nomDomaine",
            $convention->getDomainName(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "codeAiguillageSTS",
            $convention->getStsReferralCode(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "indicateurConventionGroupe",
            $convention->getGroupConventionFlag(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "indicateurUsageAttestation",
            $convention->getCertificateUseFlag(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "indicateurDesactivationSTS",
            $convention->getStsDisabledFlag(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "gestionDAnnulation",
            $convention->getCancelManagement(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "gestiondReRectification",
            $convention->getRectificationManagement(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "applicationConvention",
            $convention->getConventionApplication(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "applicationSystematique",
            $convention->getSystematicApplication(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "dateApplicationConvention",
            $convention->getConventionApplicationDate(),
            $data["updateConvention"]
        );
        $this->addOptionalValue(
            "idEtablissement",
            $convention->getGroupId(),
            $data["updateConvention"]
        );

        if ($convention->getService() !== null) {
            $data['service'] = $convention->getService();
        }

        if ($convention->getTeleservice() !== null) {
            $data['teleservice'] = $convention->getTeleservice();
        }

        return $data;
    }

    public static function makeArrayFromConvention(Convention $convention): array
    {
        $data = [
            'id' => $convention->getConventionId() ?? '',
            'noAMCCorrespondance' => '',
            'numOrganismeSignataire' => $convention->getSignerOrganizationNumber() ?? '',
            'typeConvention' => $convention->getConventionType(),
            'critereSecondaire' => $convention->getSecondaryCriteria() ?? '',
            'typeAccord' => $convention->getAgreementType() ?? '',
            'libOrganismeSignataire' => $convention->getSignerOrganizationLabel()
                ? substr($convention->getSignerOrganizationLabel(), 0, 30) : '',
            'numAMC' => $convention->getAmcNumber() ?? '',
            'libelleAMC' => $convention->getAmcLabel() ?? '',
            'operateurReglement' => $convention->getStatutoryOperator() ?? '',
            'codeRoutage' => $convention->getRoutingCode() ?? '',
            'identifiantHote' => $convention->getHostId() ?? '',
            'nomDomaine' => $convention->getDomainName() ?? '',
            'codeAiguillageSTS' => $convention->getStsReferralCode() ?? '',
            'indicateurConventionGroupe' => $convention->getGroupConventionFlag(),
            'indicateurUsageAttestation' => $convention->getCertificateUseFlag(),
            'indicateurDesactivationSTS' => $convention->getStsDisabledFlag(),
            'gestionDAnnulation' => $convention->getCancelManagement(),
            'gestiondReRectification' => $convention->getRectificationManagement(),
        ];

        if ($convention->getService() !== null) {
            $data['service'] = $convention->getService();
        }

        if ($convention->getTeleservice() !== null) {
            $data['teleservice'] = $convention->getTeleservice();
        }

        return $data;
    }

    public function makeStoreArrayFromGrouping(Grouping $grouping): array
    {
        //TODO : Test makeStoreArrayFromGrouping

        return [
            "updateRegroupement" => [
                "idRegroupement"        => $grouping->getGroupingId(),
                "numeroAMC"             => $grouping->getAmcNumber(),
                "libelleOrganismeAMC"   => $grouping->getAmcLabel(),
                "typeConvention"        => $grouping->getConventionType(),
                "libelleTypeConvention" => $grouping->getConventionTypeLabel(),
                "critereSecondaire"     => $grouping->getSecondaryCriteria() ?? '',
                "numeroSignataire"      => $grouping->getSignerOrganizationNumber(),
                "idEtablissement"       => $grouping->getGroupId(),
                "idJfse"                => $grouping->getJfseId(),
            ],
        ];
    }

    public function makeStoreArrayFromCorrespondence(Correspondence $correspondence): array
    {
        //TODO : Test makeStoreArrayFromCorrespondence

        return [
            "updateCorrespondance" => [
                "idCorrespondance" => $correspondence->getCorrespondenceId(),
                "numMutuelle"      => $correspondence->getHealthInsuranceNumber(),
                "codeRegime"       => $correspondence->getRegimeCode(),
                "numeroAMC"        => $correspondence->getAmcNumber(),
                "libelleAMC"       => $correspondence->getAmcLabel(),
                "idEtablissement"  => $correspondence->getGroupId(),
            ],
        ];
    }

    /**
     * @param $response
     *
     * @return array
     */
    public static function getGroupingsFromResponse(Response $response): array
    {
        $groupings = [];
        $data      = CMbArray::get($response->getContent(), 'lstRegroupements', []);
        foreach ($data as $grouping) {
            $groupings[] = [
                "grouping_id"                => CMbArray::get($grouping, "idRegroupement"),
                "amc_number"                 => CMbArray::get($grouping, "numeroAMC"),
                "amc_label"                  => CMbArray::get($grouping, "libelleOrganismeAMC"),
                "convention_type"            => CMbArray::get($grouping, "typeConvention"),
                "convention_type_label"      => CMbArray::get($grouping, "libelleTypeConvention"),
                "secondary_criteria"         => CMbArray::get($grouping, "critereSecondaire"),
                "signer_organization_number" => CMbArray::get($grouping, "numeroSignataire"),
            ];
        }

        return $groupings;
    }

    public static function getCorrespondencesFromResponse(Response $response): array
    {
        $correspondences = [];

        $data = CMbArray::get($response->getContent(), 'lstCorrespondances', []);
        foreach ($data as $correspondence) {
            $correspondences[] = [
                "correspondence_id"       => intval(CMbArray::get($correspondence, "idCorrespondance")),
                "health_insurance_number" => CMbArray::get($correspondence, "numMutuelle"),
                "regime_code"             => CMbArray::get($correspondence, "codeRegime"),
                "amc_number"              => CMbArray::get($correspondence, "numeroAMC"),
                "amc_label"               => CMbArray::get($correspondence, "libelleAMC"),
                "group_id"                => intval(CMbArray::get($correspondence, "idEtablissement")),
            ];
        }

        return $correspondences;
    }

    public static function getConventionsFromResponse(Response $response): array
    {
        $conventions = [];
        $data        = CMbArray::get($response->getContent(), 'lstConventions', []);
        foreach ($data as $convention) {
            $conventions[] = [
                "convention_id"               => intval(CMbArray::get($convention, "id")),
                "signer_organization_number"  => CMbArray::get($convention, "numOrganismeSignataire"),
                "convention_type"             => CMbArray::get($convention, "typeConvention"),
                "secondary_criteria"          => CMbArray::get($convention, "critereSecondaire"),
                "agreement_type"              => CMbArray::get($convention, "typeAccord"),
                "signer_organization_label"   => CMbArray::get($convention, "libOrganismeSignatiare"),
                "amc_number"                  => CMbArray::get($convention, "numAMC"),
                "amc_label"                   => CMbArray::get($convention, "libelleAMC"),
                "statutory_operator"          => CMbArray::get($convention, "operateurReglement"),
                "routing_code"                => CMbArray::get($convention, "codeRoutage"),
                "host_id"                     => CMbArray::get($convention, "identifiantHote"),
                "domain_name"                 => CMbArray::get($convention, "nomDomaine"),
                "sts_referral_code"           => CMbArray::get($convention, "codeAiguillageSTS"),
                "group_convention_flag"       => intval(CMbArray::get($convention, "indicateurConventionGroupe")),
                "certificate_use_flag"        => intval(CMbArray::get($convention, "indicateurUsageAttestation")),
                "sts_disabled_flag"           => intval(CMbArray::get($convention, "indicateurDesactivationSTS")),
                "cancel_management"           => intval(CMbArray::get($convention, "gestionDAnnulation")),
                "rectification_management"    => intval(CMbArray::get($convention, "gestiondReRectification")),
                "convention_application"      => intval(CMbArray::get($convention, "applicationConvention")),
                "systematic_application"      => intval(CMbArray::get($convention, "applicationSystematique")),
                "convention_application_date" => CMbArray::get($convention, "dateApplicationConvention"),
                "group_id"                    => intval(CMbArray::get($convention, "idEtablissement")),
                "jfse_id"                     => intval(CMbArray::get($convention, "idJfse")),
                "number_amc_correspondence"   => CMbArray::get($convention, 'noAMCCorrespondance'),
                "service"                     => CMbArray::get($convention, 'service'),
                "teleservice"                 => CMbArray::get($convention, 'teleservice'),
            ];
        }

        return $conventions;
    }

    public static function getConventionFromResponse(array $response): Convention
    {
        return Convention::hydrate([
            "convention_id"               => intval(CMbArray::get($response, "id")),
            "signer_organization_number"  => CMbArray::get($response, "numOrganismeSignataire"),
            "convention_type"             => CMbArray::get($response, "typeConvention"),
            "secondary_criteria"          => CMbArray::get($response, "critereSecondaire"),
            "agreement_type"              => CMbArray::get($response, "typeAccord"),
            "signer_organization_label"   => CMbArray::get($response, "libOrganismeSignataire"),
            "amc_number"                  => CMbArray::get($response, "numAMC"),
            "amc_label"                   => CMbArray::get($response, "libelleAMC"),
            "statutory_operator"          => CMbArray::get($response, "operateurReglement"),
            "routing_code"                => CMbArray::get($response, "codeRoutage", ''),
            "host_id"                     => CMbArray::get($response, "identifiantHote"),
            "domain_name"                 => CMbArray::get($response, "nomDomaine"),
            "sts_referral_code"           => CMbArray::get($response, "codeAiguillageSTS"),
            "group_convention_flag"       => intval(CMbArray::get($response, "indicateurConventionGroupe")),
            "certificate_use_flag"        => intval(CMbArray::get($response, "indicateurUsageAttestation")),
            "sts_disabled_flag"           => intval(CMbArray::get($response, "indicateurDesactivationSTS")),
            "cancel_management"           => intval(CMbArray::get($response, "gestionDAnnulation")),
            "rectification_management"    => intval(CMbArray::get($response, "gestiondReRectification")),
            "convention_application"      => intval(CMbArray::get($response, "applicationConvention")),
            "systematic_application"      => intval(CMbArray::get($response, "applicationSystematique")),
            "convention_application_date" => CMbArray::get($response, "dateApplicationConvention"),
            "group_id"                    => intval(CMbArray::get($response, "idEtablissement")),
            "jfse_id"                     => intval(CMbArray::get($response, "idJfse")),
            "number_amc_correspondence"   => CMbArray::get($response, 'noAMCCorrespondance'),
            "service"                     => CMbArray::get($response, 'service'),
            "teleservice"                 => CMbArray::get($response, 'teleservice'),
        ]);
    }

    public static function getTypeConventionFromResponse(Response $response): array
    {
        $types_conventions = [];

        $data = CMbArray::get($response->getContent(), 'lstTypeConvention', []);
        foreach ($data as $type) {
            $types_conventions[] = [
                "code"  => CMbArray::get($type, 'code'),
                "label" => CMbArray::get($type, 'libelle'),
            ];
        }

        return $types_conventions;
    }

    public static function getConventionsToInstallFromResponse(Response $response): array
    {
        $conventions      = [];
        $data_conventions = CMbArray::get($response->getContent()["lstDetails"][0], 'lstReferentielConventions', []);
        foreach ($data_conventions as $convention) {
            if (CMbArray::get($convention, 'codeAction') !== "#ERREUR#") {
                $conventions[] = [
                    "signer_organization_number" => CMbArray::get(
                        $convention["maConvention"],
                        "numOrganismeSignataire"
                    ),
                    "convention_type"            => CMbArray::get($convention["maConvention"], "typeConvention"),
                    "secondary_criteria"         => CMbArray::get($convention["maConvention"], "critereSecondaire"),
                    "agreement_type"             => CMbArray::get($convention["maConvention"], "typeAccord"),
                    "signer_organization_label"  => CMbArray::get(
                        $convention["maConvention"],
                        "libOrganismeSignatiare"
                    ),
                    "amc_number"                 => CMbArray::get($convention["maConvention"], "numAMC"),
                    "amc_label"                  => CMbArray::get($convention["maConvention"], "libelleAMC"),
                    "statutory_operator"         => CMbArray::get($convention["maConvention"], "operateurReglement"),
                    "routing_code"               => CMbArray::get($convention["maConvention"], "codeRoutage"),
                    "host_id"                    => CMbArray::get($convention["maConvention"], "identifiantHote"),
                    "domain_name"                => CMbArray::get($convention["maConvention"], "nomDomaine"),
                    "sts_referral_code"          => CMbArray::get($convention["maConvention"], "codeAiguillageSTS"),
                    "group_convention_flag"      => intval(
                        CMbArray::get($convention["maConvention"], "indicateurConventionGroupe")
                    ),
                    "certificate_use_flag"       => intval(
                        CMbArray::get($convention["maConvention"], "indicateurUsageAttestation")
                    ),
                    "sts_disabled_flag"          => intval(
                        CMbArray::get($convention["maConvention"], "indicateurDesactivationSTS")
                    ),
                    "cancel_management"          => intval(
                        CMbArray::get($convention, "indicateurAcceptationDREAnnulation")
                    ),
                    "rectification_management"   => intval(
                        CMbArray::get($convention, "indicateurAcceptationDRERectification")
                    ),
                ];
            }
        }

        return $conventions;
    }

    public static function getGroupingsToInstallFromResponse(Response $response): array
    {
        $groupings = [];
        $data      = CMbArray::get($response->getContent()["lstDetails"][0], 'lstReferentielRegroupements', []);
        foreach ($data as $grouping) {
            $groupings[] = [
                "amc_number"                 => CMbArray::get(
                    $grouping["monRegroupement"],
                    "numeroOrganismeComplementaire"
                ),
                "amc_label"                  => CMbArray::get(
                    $grouping["monRegroupement"],
                    "libelleOrganismeComplementaire"
                ),
                "convention_type"            => CMbArray::get($grouping["monRegroupement"], "typeConvention"),
                "convention_type_label"      => CMbArray::get($grouping["monRegroupement"], "libelleTypeConvention"),
                "secondary_criteria"         => CMbArray::get($grouping["monRegroupement"], "critereSecondaire"),
                "signer_organization_number" => CMbArray::get(
                    $grouping["monRegroupement"],
                    "identifiantOrganismeSignataire"
                ),
            ];
        }

        return $groupings;
    }
}
