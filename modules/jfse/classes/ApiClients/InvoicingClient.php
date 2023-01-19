<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ApiClients;

use Ox\Mediboard\Jfse\Api\Question;
use Ox\Mediboard\Jfse\Api\Request;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Invoicing\CommonLawAccident;
use Ox\Mediboard\Jfse\Domain\Invoicing\Acs;
use Ox\Mediboard\Jfse\Domain\Invoicing\InsuredParticipationAct;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\RuleForcing;
use Ox\Mediboard\Jfse\Domain\Invoicing\ComplementaryHealthInsurance;
use Ox\Mediboard\Jfse\Mappers\InvoicingMapper;

/**
 * Class InvoicingClient
 *
 * @package Ox\Mediboard\Jfse\ApiClients
 */
class InvoicingClient extends AbstractApiClient
{
    public function setAccidentDC(CommonLawAccident $common_law_accident, string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setAccidentDC',
                (new InvoicingMapper())->makeSetAccidentDcArrayFromCommonLawAccident(
                    $common_law_accident,
                    $invoice_id
                ),
            )
        );
    }

    public function initialiserFacture(Invoice $invoice): Response
    {
        $data    = (new InvoicingMapper())->makeInitFactureArrayFromInvoice($invoice, true);
        $request = Request::forge('FDS-initialiserFacture', $data)->setForceObject(false);

        return self::sendRequest($request, 60);
    }

    public function validerFacture(string $invoice_id, string $callback = null): Response
    {
        $data    = [
            "idFacture"   => $invoice_id
        ];

        if ($callback) {
            $data["callbackUrl"] = $callback;
        }

        $request = Request::forge('FDS-validerFacture', $data);

        return self::sendRequest($request, 600);
    }

    public function annulerFacture(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-annulerFacture',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function supprimerFacture(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'TAB-deleteFactures',
                ['deleteFactures' => ["lstIdFactures" => ['idFacture' => $invoice_id]]]
            )
        );
    }

    public function getDonneesFacture(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-getDonneesFacture',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function controlerFacture(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-controlerFacture',
                ["idFacture" => $invoice_id]
            ), 300
        );
    }

    public function setForcageReglesSTD(string $invoice_id, RuleForcing $rule_forcing): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setForcageReglesSTD',
                (new InvoicingMapper())->makeSetForceReglesFromEntity($invoice_id, $rule_forcing)
            )->setForceObject(false)
        );
    }

    public function setForcageReglesCC(string $invoice_id, RuleForcing $rule_forcing): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setForcageReglesCC',
                (new InvoicingMapper())->makeSetForceReglesFromEntity($invoice_id, $rule_forcing)
            )->setForceObject(false)
        );
    }

    public function removeCotation(array $lst_cotations, string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-removeCotation',
                [(new InvoicingMapper())->makeRemoveCotationArrayFromLstCotations($lst_cotations, $invoice_id)]
            )
        );
    }

    public function getConventions(string $invoice_id, array $convention_data): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-getConventions',
                [(new InvoicingMapper())->makeGetConventionsArrayFromConvention($invoice_id, $convention_data)]
            )
        );
    }

    public function setOrganismeComplementaire(
        string $invoice_id,
        ComplementaryHealthInsurance $organisation
    ): Response {
        /* Added a timeout because this method can trigger Jfse to call a webservice for getting the conventions */
        return self::sendRequest(
            Request::forge(
                'FDS-setOrganismeComplementaire',
                (new InvoicingMapper())->makeSetOrganismeComplementaireFromEntity($invoice_id, $organisation)
            )->setForceObject(false),
            300
        );
    }

    public function annulerModification(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-annulerModification',
                ["idFacture" => $invoice_id]
            )
        );
    }

    /**
     * @param string $invoice_id
     * @param Question[]  $data
     *
     * @return Response
     */
    public function setReponseQuestions(string $invoice_id, array $data): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setReponseQuestions',
                (new InvoicingMapper())->makeSetReponseQuestionsArrayFromData($invoice_id, $data)
            )->setForceObject(false)
        );
    }

    public function setTypeTraitement(string $invoice_id, int $treatment_type): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setTypeTraitement',
                [
                    "idFacture"      => $invoice_id,
                    "typeTraitement" => [
                        "typeTraitement" => $treatment_type,
                    ],
                ]
            )
        );
    }

    public function setForcageMontants(
        string $invoice_id,
        int $act_id,
        string $forcing_type,
        int $forcing_type_choice,
        float $amount
    ): Response {
        return self::sendRequest(
            Request::forge(
                'FDS-setForcageMontants',
                [
                    "idFacture"       => $invoice_id,
                    "forcageMontants" => [
                        "idActe"  => $act_id,
                        "type"    => $forcing_type,
                        "choix"   => $forcing_type_choice,
                        "montant" => $amount,
                    ],
                ]
            )
        );
    }

    public function setPav(string $invoice_id, InsuredParticipationAct $participation): Response
    {
        return self::sendRequest(Request::forge('FDS-setPAV', [
            'idFacture' => $invoice_id,
            'setPAV'    => [
                'indexActe'            => $participation->getIndex(),
                'ajoutPav'             => (int)$participation->getAddInsuredParticipation(),
                'diminutionMontantAmo' => (int)$participation->getAmoAmountReduction(),
            ]
        ]));
    }

    public function setGestionSMG(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setGestionSMG',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function setGestionAPIAS(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setGestionAPIAS',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function setGestionComplementATetVictimeAttentat(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setGestionComplementATetVictimeAttentat',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function setGestionsComplement(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setGestionsComplement',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function setForcageExoMaternite(string $invoice_id, bool $forcing): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setForcageExoMaternite',
                [
                    "idFacture"           => $invoice_id,
                    "forcageExoMaternite" => [
                        "forcage" => $forcing,
                    ],
                ]
            )
        );
    }

    public function getPeriodesDroitsAMO(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-getPeriodesDroitsAMO',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function getServiceAmo(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('FDS-getServiceAMO', [
            'idFacture' => $invoice_id
        ]));
    }

    public function getAideConsultationEnfant(
        string $invoice_id,
        string $reference_date,
        bool $tarif_opposables = null,
        bool $medecin_traitant = null
    ): Response {
        $data = [
            'idFacture' => $invoice_id,
            'getAideConsultationEnfant' => [
                'dateRef' => $reference_date
            ]
        ];

        if (!is_null($tarif_opposables)) {
            $data['getAideConsultationEnfant']['tarifsOpposables'] = (int)$tarif_opposables;
        }

        if (!is_null($medecin_traitant)) {
            $data['getAideConsultationEnfant']['medecinTraitant'] = (int)$medecin_traitant;
        }

        return self::sendRequest(Request::forge('FDS-getAideConsultationEnfant', $data));
    }

    public function setAnonymiser(string $invoice_id, int $anonymisation_type): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setAnonymiser',
                [
                    "idFacture"  => $invoice_id,
                    "anonymiser" => [
                        "anonymisation" => $anonymisation_type,
                    ],
                ]
            )
        );
    }

    public function getListeActes(string $invoice_id, array $data): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-getListeActes',
                [(new InvoicingMapper())->makeGetListeActesArrayFromData($invoice_id, $data)]
            )
        );
    }

    public function assistantACS(Acs $acs): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-assistantACS',
                [(new InvoicingMapper())->makeAssistantAcsArrayFromEntity($acs)]
            )
        );
    }

    public function setPlafondCMU(string $invoice_id, float $value): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setPlafondCMU',
                [
                    "idFacture"  => $invoice_id,
                    "plafondCMU" => [
                        "valeurPlafondCMU" => $value,
                    ],
                ]
            )
        );
    }

    public function getListeAnonymisations(string $invoice_id): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-getListeAnonymisations',
                ["idFacture" => $invoice_id]
            )
        );
    }

    public function setDiffererEnvoi(string $invoice_id, bool $is_delayed): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setDiffererEnvoi',
                [
                    "idFacture"     => $invoice_id,
                    "differerEnvoi" => [
                        "differerEnvoi" => $is_delayed,
                    ],
                ]
            )
        );
    }

    public function setCodeCouverture(string $invoice_id, int $coverage_code): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setCodeCouverture',
                [
                    "idFacture"         => $invoice_id,
                    "setCodeCouverture" => [
                        "codeCouverture" => $coverage_code,
                    ],
                ]
            )
        );
    }

    public function setCheckVitaleCard(string $invoice_id, int $check_vital_card): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setCheckVitaleCard',
                [
                    "idFacture"          => $invoice_id,
                    "setCheckVitaleCard" => [
                        "checkVitaleCard" => $check_vital_card,
                    ],
                ]
            )
        );
    }

    public function setDesactivationSTS(string $invoice_id, int $sts_enable): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setDesactivationSTS',
                [
                    "idFacture"           => $invoice_id,
                    "setDesactivationSTS" => [
                        "desactivationSTS" => $sts_enable,
                    ],
                ]
            )
        );
    }

    public function setTauxGlobaleFSP(string $invoice_id, int $global_rate): Response
    {
        return self::sendRequest(
            Request::forge(
                'FDS-setTauxGlobaleFSP',
                [
                    "idFacture"         => $invoice_id,
                    "setTauxGlobaleFSP" => [
                        "tauxGlobal" => $global_rate,
                    ],
                ]
            )
        );
    }

    public function getListStsReferralCodes(): Response
    {
        return self::sendRequest(Request::forge('TP-getListeCodeAiguillageSTS', []));
    }

    public function getListTreatmentIndicators(string $type): Response
    {
        return self::sendRequest(Request::forge('TP-getListeIndicateursTraitements', [
            'getListeIndicateursTraitements' => [
                'codeAMCMutuelle' => $type
            ]
        ]));
    }

    public function getListExonerationCodes(string $invoice_id): Response
    {
        return self::sendRequest(Request::forge('EXO-getListeExonerations', ['idFacture' => $invoice_id]));
    }

    public function getListSituationCodes(string $regime_code): Response
    {
        return self::sendRequest(Request::forge('SIT-getListeSituations', [
            'getListeSituations' => ['codeRegime' => $regime_code]
        ]));
    }
}
