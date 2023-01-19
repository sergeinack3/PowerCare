<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Mappers;

use DateTime;
use Ox\Mediboard\Jfse\Domain\MedicalAct\CommonPrevention;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Domain\MedicalAct\ExecutingPhysician;
use Ox\Mediboard\Jfse\Domain\MedicalAct\InsuranceAmountForcing;
use Ox\Mediboard\Jfse\Domain\MedicalAct\LppBenefit;
use Ox\Mediboard\Jfse\Domain\MedicalAct\LppTypeEnum;
use Ox\Mediboard\Jfse\Domain\MedicalAct\Pricing;
use Ox\Mediboard\Jfse\Domain\MedicalAct\PriorAgreement;
use Ox\Mediboard\Jfse\Mappers\MedicalActMapper;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class MedicalActMapperTest extends UnitTestJfse
{
    public function testArrayToMedicalActList(): void
    {
        $json = <<<JSON
[
    {
        "type": 0,
        "id": "1607957736304861010",
        "idSeance": "",
        "externalId": "",
        "date": "20201127",
        "dateAchevement": "",
        "codeActe": "C",
        "lettreCle": "C",
        "quantite": 1,
        "coefficient": 1.0,
        "qualificatifDepense": "",
        "montantDepassement": 0.0,
        "montantTotal": 0.0,
        "lieuExecution": 0,
        "complement": "",
        "codeActivite": "",
        "codePhase": "",
        "lstModificateurs": [],
        "codeAssociation": "",
        "supplementCharge": 0,
        "remboursementExceptionnel": 0,
        "dents": "",
        "prixUnitaire": 23.0,
        "baseRemboursement": 23.0,
        "utilisationReferentiel": 1,
        "codeRegroupement": "",
        "taux": 70,
        "montantFacture": 23.0,
        "exonerationTMParticuliere": "-1",
        "prixReferentiel": 23.0,
        "depassementUniquement": true,
        "codeJustifExoneration": "0",
        "locked": false,
        "lockedMessage": "",
        "isHonoraire": false,
        "isLpp": false,
        "libelle": "Consultation",
        "totalAMO": 16.1,
        "totalAssure": 23.0,
        "totalAMC": 0.0,
        "duAMO": 0.0,
        "duAMC": 0.0,
        "forcageAMOAutorise": true,
        "forcageAMCAutorise": true,
        "protheseDentaire": false,
        "ententePrealable": {
            "valeur": 0,
            "dateEnvoi": ""
        },
        "preventionCommune": {
            "topPrevention": 0,
            "qualifiant": ""
        },
        "executant": {
            "noIdentification": "",
            "specialite": "00",
            "convention": 0,
            "zoneTarifaire": "0",
            "conditionExercice": "00",
            "rpps": "",
            "noStructure": ""
        },
        "forcageMontantAMO": {
            "choix": 0,
            "partAMO": 16.1,
            "partAMOSaisie": 0.0
        },
        "forcageMontantAMC": {
            "choix": 0,
            "partAMC": 0.0,
            "partAMCSaisie": 0.0
        },
        "lstPrestationLPP": [
            {
                "code": "ALZ",
                "typePrestation": "A",
                "libelle": "Label",
                "quantite": 2,
                "noSiret": "123456789",
                "prixUnitaireRef": 23.0,
                "prixUnitaireTTC": 23.0,
                "montantTotalRef": 23.0,
                "montantTotalTTC": 23.0,
                "dateFin": "20201216",
                "dateDebut": "",
                "prixLimiteVente": 75.00
            }
        ]
    }
]
JSON;

        $expected = MedicalAct::hydrate(
            [
                "type"                   => 0,
                "invoice_id"             => "1607957736304861010",
                "session_id"             => "",
                "external_id"            => "",
                "date"                   => new DateTime("2020-11-27"),
                "completion_date"       => null,
                "act_code"               => "C",
                "key_letter"             => "C",
                "quantity"               => 1,
                "coefficiant"            => 1.0,
                "spend_qualifier"        => "",
                "pricing"                => Pricing::hydrate(
                    [
                        "exceeding_amount"          => 0.0,
                        "total_amount"              => 0.0,
                        "additional_charge"         => 0,
                        "exceptional_reimbursement" => 0,
                        "unit_price"                => 23.0,
                        "reimbursement_base"        => 23.0,
                        "referential_price"         => 23.0,
                        "rate"                      => 70,
                        "invoice_total"             => 23.0,
                        "total_amo"                 => 16.1,
                        "total_insured"             => 23.0,
                        "total_amc"                 => 0.0,
                        "owe_amo"                   => 0.0,
                        "owe_amc"                   => 0.0,
                    ]
                ),
                "execution_place"        => 0,
                "additional"             => "",
                "activity_code"          => "",
                "phase_code"             => "",
                "modifiers"              => [],
                "association_code"       => "",
                "teeth"                  => [""],
                "referential_use"        => 1,
                "regrouping_code"        => "",
                "exoneration_user_fees"           => "-1",
                "unique_exceeding"       => true,
                "exoneration_proof_code" => "0",
                "locked"                 => false,
                "locked_message"         => "",
                "is_honorary"            => false,
                "is_lpp"                 => false,
                "label"                  => "Consultation",
                "authorised_amo_forcing" => true,
                "authorised_amc_forcing" => true,
                "dental_prosthesis"      => false,
                "prior_agreement"        => PriorAgreement::hydrate(
                    [
                        "value"     => 0,
                        "send_date" => null,
                    ]
                ),
                "common_prevention"      => CommonPrevention::hydrate(
                    [
                        "prevention_top" => 0,
                        "qualifier"      => "",
                    ]
                ),
                "amo_amount_forcing"     => InsuranceAmountForcing::hydrate(
                    [
                        "choice"                  => 0,
                        "computed_insurance_part" => 16.1,
                        "modified_insurance_part" => 0.0,
                    ]
                ),
                "amc_amount_forcing"     => InsuranceAmountForcing::hydrate(
                    [
                        "choice"                  => 0,
                        "computed_insurance_part" => 0.0,
                        "modified_insurance_part" => 0.0,
                    ]
                ),
                "executing_physician"    => ExecutingPhysician::hydrate(
                    [
                        "id"                 => "",
                        "speciality"         => "00",
                        "convention"         => 0,
                        "pricing_zone"       => "0",
                        "practice_condition" => "00",
                        "national_id"        => "",
                        "structure_id"       => "",
                    ]
                ),
                "lpp_benefits"        => [
                    LppBenefit::hydrate(
                        [
                            "code"             => "ALZ",
                            "type"             => LppTypeEnum::BUY(),
                            "label"            => "Label",
                            "quantity"         => 2,
                            "siret_number"     => "123456789",
                            "unit_price_ref"   => 23.0,
                            "unit_price_ttc"   => 23.0,
                            "total_price_ref"  => 23.0,
                            "total_price_ttc"  => 23.0,
                            "end_date"         => new DateTime("2020-12-16"),
                            "begin_date"       => null,
                            "sell_price_limit" => 75.00,
                        ]
                    )
                ],
            ]
        );

        $this->assertEquals([$expected], (new MedicalActMapper())->arrayToMedicalActList(json_decode($json, true)));
    }
}
