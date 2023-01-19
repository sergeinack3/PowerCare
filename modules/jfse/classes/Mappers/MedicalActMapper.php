<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Cassandra\Date;
use DateTime;
use Ox\Core\CMbArray;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\InsuranceType\Insurance;
use Ox\Mediboard\Jfse\Domain\MedicalAct\CommonPrevention;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Domain\MedicalAct\ExecutingPhysician;
use Ox\Mediboard\Jfse\Domain\MedicalAct\InsuranceAmountForcing;
use Ox\Mediboard\Jfse\Domain\MedicalAct\LppBenefit;
use Ox\Mediboard\Jfse\Domain\MedicalAct\LppTypeEnum;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalActTypeEnum;
use Ox\Mediboard\Jfse\Domain\MedicalAct\Pricing;
use Ox\Mediboard\Jfse\Domain\MedicalAct\PriorAgreement;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CActeCCAM;

class MedicalActMapper extends AbstractMapper
{
    /** @var array The list of Mediboard classes used to hydrate MedicalAct objects */
    protected static $mediboard_act_classes = ['CActeNGAP', 'CActeCCAM', 'CActeLPP'];

    /** @var string[] The list of Ngap codes that have multiple prices */
    protected static $ngap_multiple_pricings = ['VAC', 'TSA', 'TSM'];
    /** @var array A list of codes that must be replaced when sent to Jfse */
    protected static $mediboard_ngap_codes = ['CNPSY', 'VNPSY', 'VAC2', 'TSA1', 'TSA2', 'TSM1', 'TSM2', 'TSM3', 'TSM4'];
    /** @var array The list of replacements codes */
    protected static $sesam_vital_ngap_codes = ['CNP', 'VNP', 'VAC', 'TSA', 'TSA', 'TSM', 'TSM', 'TSM', 'TSM'];

    /** @var string The code for the NGAP code that represents an act outside of the NGAP or CCAM norm */
    protected const NGAP_OUTSIDE_NORM = 'HN';

    public static function getArrayFromMedicalAct(MedicalAct $act, bool $initialize = false): array
    {
        $data = [];

        if ($act->getId()) {
            $data['id'] = $act->getId();
        }

        $data['externalId'] = $act->getExternalIdInJfseFormat();
        $data['date']       = $act->getDate()->format('Ymd');
        $data['codeActe']   = $act->getActCode();

        $pricing = $act->getPricing();

        if ($act->getIsLpp()) {
            if ($pricing->getAdditionalCharge() && $act->getSpendQualifier()) {
                $data['qualificatifDepense'] = $act->getSpendQualifier();
            }

            $data['montantDepassement']  = $pricing->getAdditionalCharge() ?? 0.0;
            $data['montantTotal']        = $pricing->getTotalAmount();
            $data['lieuExecution']       = $act->getExecutionPlace() ?? 0;
            $lpp = $act->getLppBenefit();
            $data['lstPrestationLPP'] = [
                'code'            => $lpp->getCode(),
                'typePrestation'  => $lpp->getType()->getValue(),
                'libelle'         => $lpp->getLabel(),
                'quantite'        => $act->getQuantity() ? $act->getQuantity() : 1,
                'prixLimiteVente' => $lpp->getSellPriceLimit(),
                'prixUnitaireTTC' => $lpp->getUnitPriceTtc(),
                'prixUnitaireRef' => $lpp->getUnitPriceRef(),
                'montantTotalTTC' => $lpp->getTotalPriceTtc(),
            ];

            if ($lpp->getBeginDate()) {
                $data['lstPrestationLPP']['dateDebut'] = $lpp->getBeginDate()->format('Ymd');
            }

            if ($lpp->getEndDate()) {
                $data['lstPrestationLPP']['dateFin'] = $lpp->getEndDate()->format('Ymd');
            }
        } elseif ($act->getType() == MedicalActTypeEnum::CCAM()) {
            $data['codeActivite']        = $act->getActivityCode();
            $data['codePhase']           = $act->getPhaseCode();

            if ($pricing->getExceedingAmount()) {
                $data['montantDepassement']  = $pricing->getExceedingAmount();
            }

            if ($pricing->getTotalAmount()) {
                $data['montantTotal'] = $pricing->getTotalAmount();
            }

            if ($act->getSpendQualifier()) {
                $data['qualificatifDepense'] = $act->getSpendQualifier();
            }

            if ($act->getModifiers()) {
                $data['modificateurs'] = implode('', $act->getModifiers());
            }

            if ($act->getAssociationCode()) {
                $data['codeAssociation'] = $act->getAssociationCode();
            }

            if ($pricing->getExceptionalReimbursement() !== null) {
                $data['remboursementExceptionnel'] = $pricing->getExceptionalReimbursement() ? '1' : '0';
            }

            if ($act->getTeeth() !== null) {
                $data['dents'] = $act->getTeethString();
            }
        } else {
            if ($act->getQuantity()) {
                $data['quantite']            = $act->getQuantity();
            }

            if ($act->getCoefficient()) {
                $data['coefficient'] = $act->getCoefficient();
            }

            if ($act->getSpendQualifier()) {
                $data['qualificatifDepense'] = $act->getSpendQualifier();
            }

            if ($act->getAdditional()) {
                $data['complement'] = $act->getAdditional();
            }

            if ($pricing->getExceedingAmount()) {
                $data['montantDepassement']  = $pricing->getExceedingAmount();
            }

            if ($pricing->getReimbursementBase()) {
                $data['baseRemboursement'] = $pricing->getReimbursementBase();
            }

            if ($pricing->getUnitPrice()) {
                $data['prixUnitaire'] = $pricing->getUnitPrice();
            }

            if ($pricing->getTotalAmount()) {
                $data['montantTotal']        = $pricing->getTotalAmount();
            }

            if ($act->getExecutionPlace()) {
                $data['lieuExecution']       = $act->getExecutionPlace();
            }

            if ($act->getNurseReductionRate() !== null) {
                $data['tauxAbattementIK'] = $act->getNurseReductionRate();
            }
        }

        if ($act->getPriorAgreement() && $act->getPriorAgreement()->getValue() !== null) {
            $data['ententePrealable'] = [
                'valeur' => $act->getPriorAgreement()->getValue(),
            ];

            if ($act->getPriorAgreement()->getSendDate()) {
                $data['ententePrealable']['dateEnvoi'] = $act->getPriorAgreement()->getSendDate()->format('Ymd');
            }
        }

        if ($act->getCommonPrevention() && $act->getCommonPrevention()->getPreventionTop() !== null) {
            $data['preventionCommune'] = [
                'topPrevention' => $act->getCommonPrevention()->getPreventionTop(),
            ];

            if ($act->getCommonPrevention()->getQualifier()) {
                $data['preventionCommune']['qualifiant'] = $act->getCommonPrevention()->getQualifier();
            }
        }

        if ($act->getExonerationUserFees()) {
            $data['exonerationTMParticuliere'] = $act->getExonerationUserFees();
        }

        if ($act->getExecutingPhysician()) {
            $physician = $act->getExecutingPhysician();
            $data['executant'] = [
                'noIdentification'  => $physician->getInvoicingNumber(),
                'specialite'        => $physician->getSpeciality(),
                'convention'        => $physician->getConvention(),
                'zoneTarifaire'     => $physician->getPricingZone(),
                'conditionExercice' => $physician->getPracticeCondition(),
                'rpps'              => $physician->getNationalId() ?? '',
                'noStructure'       => $physician->getStructureId() ?? '',
            ];
        }

        if ($act->getFormula()) {
            $data['formule'] = FormulaMapper::makeArrayFromFormula($act->getFormula());
        }

        if (($act->getAmoAmountForcing() || $act->getAmcAmountForcing()) && !$initialize) {
            $amount_forcing = [];
            if ($act->getAmoAmountForcing()) {
                $amount_forcing[] = self::arrayFromInsuranceAmountForcing($act->getId(), $act->getAmoAmountForcing());
            }

            if ($act->getAmcAmountForcing()) {
                $amount_forcing[] = self::arrayFromInsuranceAmountForcing($act->getId(), $act->getAmcAmountForcing());
            }

            if (count($amount_forcing)) {
                $data['forcageMontants'] = $amount_forcing;
            }
        }

        return $data;
    }

    public function arrayToMedicalActList(array $data): array
    {
        return array_map(
            function (array $row): MedicalAct {
                return $this->arrayToCotation($row);
            },
            $data
        );
    }

    private function arrayToCotation(array $row): MedicalAct
    {
        $amo_forcing = $row["forcageMontantAMO"];
        $amc_forcing = $row["forcageMontantAMC"];

        return MedicalAct::hydrate(
            [
                "type"                   => (int)$row["type"],
                "invoice_id"             => $row["id"],
                "session_id"             => $row["idSeance"],
                "external_id"            => $row["externalId"],
                "date"                   => new DateTime($row["date"]),
                "completion_date"        => self::toDateTimeOrNull($row, "dateAchevement"),
                "act_code"               => $row["codeActe"],
                "key_letter"             => $row["lettreCle"],
                "quantity"               => (int)$row["quantite"],
                "coefficiant"            => (float)$row["coefficient"],
                "spend_qualifier"        => $row["qualificatifDepense"],
                "pricing"                => $this->arrayToPricing($row),
                "execution_place"        => (int)$row["lieuExecution"],
                "additional"             => $row["complement"],
                "activity_code"          => $row["codeActivite"],
                "phase_code"             => $row["codePhase"],
                "modifiers"              => $row["lstModificateurs"],
                "association_code"       => $row["codeAssociation"],
                "teeth"                  => (array)$row["dents"],
                "referential_use"        => $row["utilisationReferentiel"],
                "regrouping_code"        => $row["codeRegroupement"],
                "exoneration_user_fees"  => (string)$row["exonerationTMParticuliere"],
                "unique_exceeding"       => (bool)$row["depassementUniquement"],
                "exoneration_proof_code" => $row["codeJustifExoneration"],
                "locked"                 => (bool)$row["locked"],
                "locked_message"         => $row["lockedMessage"],
                "is_honorary"            => (bool)$row["isHonoraire"],
                "is_lpp"                 => (bool)$row["isLpp"],
                "label"                  => $row["libelle"],
                "authorised_amo_forcing" => (bool)$row["forcageAMOAutorise"],
                "authorised_amc_forcing" => (bool)$row["forcageAMCAutorise"],
                "dental_prosthesis"      => (bool)$row["protheseDentaire"],
                "common_prevention"      => $this->arrayToCommonPrevention($row["preventionCommune"]),
                "executing_physician"    => $this->arrayToExecutingPhysician($row["executant"]),
                "prior_agreement"        => $this->arrayToPriorAgreement($row["ententePrealable"]),
                "amo_amount_forcing"      => $this->arrayToInsuranceForcing(
                    $amo_forcing["choix"],
                    $amo_forcing["partAMO"],
                    $amo_forcing["partAMOSaisie"]
                ),
                "amc_amount_forcing"      => $this->arrayToInsuranceForcing(
                    $amc_forcing["choix"],
                    $amc_forcing["partAMC"],
                    $amc_forcing["partAMCSaisie"]
                ),
                "lpp_benefits"           => $this->arrayToLppBenefits($row["lstPrestationLPP"]),
            ]
        );
    }

    private function arrayToPricing(array $row): Pricing
    {
        return Pricing::hydrate(
            [
                "exceeding_amount"          => (float)$row["montantDepassement"],
                "total_amount"              => (float)$row["montantTotal"],
                "additional_charge"         => (int)$row["supplementCharge"],
                "exceptional_reimbursement" => (int)$row["remboursementExceptionnel"],
                "unit_price"                => (float)$row["prixUnitaire"],
                "reimbursement_base"        => (float)$row["baseRemboursement"],
                "referential_price"         => (float)$row["prixReferentiel"],
                "rate"                      => (int)$row["taux"],
                "invoice_total"             => (float)$row["montantFacture"],
                "total_amo"                 => (float)$row["totalAMO"],
                "total_insured"             => (float)$row["totalAssure"],
                "total_amc"                 => (float)$row["totalAMC"],
                "owe_amo"                   => (float)$row["duAMO"],
                "owe_amc"                   => (float)$row["duAMC"],
            ]
        );
    }

    private function arrayToCommonPrevention(array $row): CommonPrevention
    {
        return CommonPrevention::hydrate(
            [
                "prevention_top" => (int)$row["topPrevention"],
                "qualifier"      => $row["qualifiant"],
            ]
        );
    }

    private function arrayToExecutingPhysician(array $row): ExecutingPhysician
    {
        return ExecutingPhysician::hydrate(
            [
                "id"                 => $row["noIdentification"],
                "speciality"         => $row["specialite"],
                "convention"         => $row["convention"],
                "pricing_zone"       => $row["zoneTarifaire"],
                "practice_condition" => $row["conditionExercice"],
                "national_id"        => $row["rpps"],
                "structure_id"       => $row["noStructure"],
            ]
        );
    }

    private function arrayToPriorAgreement(array $row): PriorAgreement
    {
        return PriorAgreement::hydrate(
            [
                "value"     => $row["valeur"],
                "send_date" => self::toDateTimeOrNull($row, "dateEnvoi"),
            ]
        );
    }

    private function arrayToInsuranceForcing(
        int $choice,
        float $insurance_part,
        float $seized_insurance_part
    ): InsuranceAmountForcing {
        return InsuranceAmountForcing::hydrate(
            [
                "choice"                  => $choice,
                "computed_insurance_part" => $insurance_part,
                "modified_insurance_part" => $seized_insurance_part,
            ]
        );
    }

    private function arrayToLppBenefits(array $data): array
    {
        return array_map(
            function (array $row): LppBenefit {
                return $this->arrayToLppBenefit($row);
            },
            $data
        );
    }

    private function arrayToLppBenefit(array $row): LppBenefit
    {
        $raw_type = $row["typePrestation"];
        $type     = (in_array($raw_type, LppTypeEnum::values())) ? new LppTypeEnum($raw_type) : null;

        return LppBenefit::hydrate(
            [
                "code"             => $row["code"],
                "type"             => $type,
                "label"            => $row["libelle"],
                "quantity"         => (int)$row["quantite"],
                "siret_number"     => $row["noSiret"],
                "unit_price_ref"   => (float)$row["prixUnitaireRef"],
                "unit_price_ttc"   => (float)$row["prixUnitaireTTC"],
                "total_price_ref"  => (float)$row["montantTotalRef"],
                "total_price_ttc"  => (float)$row["montantTotalTTC"],
                "end_date"         => self::toDateTimeOrNull($row, "dateFin"),
                "begin_date"       => self::toDateTimeOrNull($row, "dateDebut"),
                "sell_price_limit" => (float)$row["prixLimiteVente"],
            ]
        );
    }

    /**
     * @param array $acts_data
     *
     * @return MedicalAct[]
     */
    public static function medicalActsFromResponse(array $acts_data): array
    {
        $medical_acts = [];
        foreach ($acts_data as $data) {
            $prior_agreement_data = CMbArray::get($data, 'ententePrealable', []);
            $common_prevention = CMbArray::get($data, 'preventionCommune', []);
            $amo_forcing_data = CMbArray::get($data, 'forcageMontantAMO', []);
            $amc_forcing_data = CMbArray::get($data, 'forcageMontantAMC', []);
            $medical_act = [
                'type'                   => MedicalActTypeEnum::isValid((int)CMbArray::get($data, 'type')) ?
                    new MedicalActTypeEnum((int)CMbArray::get($data, 'type')) : null,
                'id'                     => CMbArray::get($data, 'id'),
                'session_id'             => CMbArray::get($data, 'idSeance'),
                'external_id'            => str_replace(' ', '-', CMbArray::get($data, 'externalId')),
                'date'                   => CMbArray::get($data, 'date') ? new DateTime(
                    CMbArray::get($data, 'date')
                ) : null,
                'completion_date'        => CMbArray::get($data, 'dateAchevement') ?
                    new DateTime(CMbArray::get($data, 'dateAchevement')) : null,
                'act_code'               => CMbArray::get($data, 'codeActe'),
                'key_letter'             => CMbArray::get($data, 'lettreCle'),
                'quantity'               => CMbArray::get($data, 'quantite'),
                'coefficient'            => (float)CMbArray::get($data, 'coefficient'),
                'spend_qualifier'        => CMbArray::get($data, 'qualificatifDepense'),
                'pricing'                => Pricing::hydrate(
                    [
                        'exceeding_amount'          => (float)CMbArray::get($data, 'montantDepassement'),
                        'total_amount'              => (float)CMbArray::get($data, 'montantTotal'),
                        'additional_charge'         => (float)CMbArray::get($data, 'supplementCharge'),
                        'exceptional_reimbursement' => (bool)CMbArray::get($data, 'remboursementExceptionnel'),
                        'unit_price'                => (float)CMbArray::get($data, 'prixUnitaire'),
                        'reimbursement_base'        => (float)CMbArray::get($data, 'baseRemboursement'),
                        'referential_price'         => (float)CMbArray::get($data, 'prixReferentiel'),
                        'rate'                      => (float)CMbArray::get($data, 'taux'),
                        'invoice_total'             => (float)CMbArray::get($data, 'montantFacture'),
                        'total_amo'                 => (float)CMbArray::get($data, 'totalAMO'),
                        'total_insured'             => (float)CMbArray::get($data, 'totalAssure'),
                        'total_amc'                 => (float)CMbArray::get($data, 'totalAMC'),
                        'owe_amo'                   => (float)CMbArray::get($data, 'duAMO'),
                        'owe_amc'                   => (float)CMbArray::get($data, 'duAMC'),
                    ]
                ),
                'execution_place'        => CMbArray::get($data, 'lieuExecution'),
                'additional'             => CMbArray::get($data, 'complement'),
                'activity_code'          => CMbArray::get($data, 'codeActivite'),
                'phase_code'             => CMbArray::get($data, 'codePhase'),
                'modifiers'              => CMbArray::get($data, 'lstModificateurs'),
                'association_code'       => CMbArray::get($data, 'codeAssociation'),
                'teeth'                  => explode('-', CMbArray::get($data, 'dents', '')),
                'referential_use'        => CMbArray::get($data, 'utilisationReferentiel'),
                'regrouping_code'        => CMbArray::get($data, 'codeRegroupement'),
                'exoneration_user_fees'  => CMbArray::get($data, 'exonerationTMParticuliere'),
                'unique_exceeding'       => (bool)CMbArray::get($data, 'depassementUniquement'),
                'exoneration_proof_code' => CMbArray::get($data, 'codeJustifExoneration'),
                'locked'                 => (bool)CMbArray::get($data, 'locked'),
                'locked_message'         => CMbArray::get($data, 'lockedMessage'),
                'is_honorary'            => (bool)CMbArray::get($data, 'isHonoraire'),
                'is_lpp'                 => (bool)CMbArray::get($data, 'isLpp'),
                'label'                  => CMbArray::get($data, 'libelle'),
                'authorised_amo_forcing' => CMbArray::get($data, 'forcageAMOAutorise'),
                'authorised_amc_forcing' => CMbArray::get($data, 'forcageAMCAutorise'),
                'dental_prosthesis'      => (bool)CMbArray::get($data, 'protheseDentaire'),
                'prior_agreement'        => PriorAgreement::hydrate(
                    [
                        'value'     => CMbArray::get($prior_agreement_data, 'valeur'),
                        'send_date' => CMbArray::get($prior_agreement_data, 'dateEnvoi') ?
                            new DateTime(CMbArray::get($prior_agreement_data, 'dateEnvoi')) : null,
                    ]
                ),
                'common_prevention'      => CommonPrevention::hydrate(
                    [
                        'prevention_top' => CMbArray::get($data, 'topPrevention'),
                        'qualifier'      => CMbArray::get($data, 'qualifiant'),
                    ]
                ),
                'amo_amount_forcing'      => InsuranceAmountForcing::hydrate(
                    [
                        'type'                    => 'AMO',
                        'choice'                  => (int)CMbArray::get($amo_forcing_data, 'choix'),
                        'computed_insurance_part' => (float)CMbArray::get($amo_forcing_data, 'partAMO'),
                        'modified_insurance_part' => (float)CMbArray::get($amo_forcing_data, 'partAMOSaisie'),
                    ]
                ),
                'amc_amount_forcing'      => InsuranceAmountForcing::hydrate(
                    [
                        'type'                    => 'AMC',
                        'choice'                  => (int)CMbArray::get($amc_forcing_data, 'choix'),
                        'computed_insurance_part' => (float)CMbArray::get($amc_forcing_data, 'partAMC'),
                        'modified_insurance_part' => (float)CMbArray::get($amc_forcing_data, 'partAMCSaisie'),
                    ]
                ),
                'lpp_benefit'           => self::lppBenefitsFromResponse(
                    CMbArray::get($data, 'lstPrestationLPP', [])
                ),
            ];

            if ($physician_data = CMbArray::get('executant', $data)) {
                $medical_act['executing_physician'] = ExecutingPhysician::hydrate([
                    'national_id'        => CMbArray::get($data, 'noIdentification'),
                    'speciality'         => (int)CMbArray::get($data, 'specialite'),
                    'convention'         => CMbArray::get($data, 'convention'),
                    'pricing_zone'       => CMbArray::get($data, 'zoneTarifaire'),
                    'practice_condition' => CMbArray::get($data, 'conditionExercice'),
                    'structure_id'       => CMbArray::get($data, 'noStructure'),
                ]);
            }

            if (CMbArray::get($data, 'formule')) {
                $medical_act['formula'] = FormulaMapper::arrayToFormula(CMbArray::get($data, 'formule'));
            }

            $medical_acts[] = MedicalAct::hydrate($medical_act);
        }

        return $medical_acts;
    }

    private static function lppBenefitsFromResponse(array $data): LppBenefit
    {
        if (count($data) === 1 && array_key_exists(0, $data)) {
            $data = $data[0];
        }

        return LppBenefit::hydrate([
            'code'             => CMbArray::get($data, 'code'),
            'type'             => CMbArray::get($data, 'typePrestation') ?
                new LppTypeEnum(CMbArray::get($data, 'typePrestation')) : null,
            'label'            => CMbArray::get($data, 'libelle'),
            'quantity'         => CMbArray::get($data, 'quantite'),
            'siret_number'     => CMbArray::get($data, 'noSiret'),
            'unit_price_ref'   => CMbArray::get($data, 'prixUnitaireRef'),
            'unit_price_ttc'   => CMbArray::get($data, 'prixUnitaireTTC'),
            'total_price_ref'  => CMbArray::get($data, 'montantTotalRef'),
            'total_price_ttc'  => CMbArray::get($data, 'montantTotalTTC'),
            'end_date'         => CMbArray::get($data, 'dateFin') ?
                new DateTime(CMbArray::get($data, 'dateFin')) : null,
            'begin_date'       => CMbArray::get($data, 'dateDebut') ?
                new DateTime(CMbArray::get($data, 'dateDebut')) : null,
            'sell_price_limit' => CMbArray::get($data, 'prixLimiteVente'),
        ]);
    }

    public static function medicalActFromCActe(CActe $act): ?MedicalAct
    {
        switch ($act->_class) {
            case 'CActeNGAP':
                /** @var CActeNGAP $act */
                $medical_act = self::medicalActFromCActeNGAP($act);
                break;
            case 'CActeCCAM':
                /** @var CActeCCAM $act */
                $medical_act = self::medicalActFromCActeCCAM($act);
                break;
            case 'CActeLPP':
                /** @var CActeLPP $act */
                $medical_act = self::medicalActFromCActeLPP($act);
                break;
            default:
                $medical_act = null;
        }

        if ($medical_act) {
            $consultation = $act->loadTargetObject();
            if ($consultation && $consultation->_id) {
                $consultation->loadRefPraticien();
                $act->loadRefExecutant();

                if ($act->executant_id !== $consultation->_ref_praticien->_id) {
                    self::setExecutingPhysicianFor($medical_act, $act->_ref_executant);
                }
            }
        }

        return $medical_act;
    }

    private static function medicalActFromCActeNGAP(CActeNGAP $act): MedicalAct
    {
        $code = self::replaceMediboardNgapCode($act->code);

        $pricing_data = ['exceeding_amount' => $act->montant_depassement];
        /* Some codes have multiple possible base amount, we must set the selected amount */
        if (in_array($code, self::$ngap_multiple_pricings)) {
            $pricing_data['reimbursement_base'] = $act->montant_base;
            $pricing_data['unit_price'] = $act->montant_base;
        } elseif ($code === self::NGAP_OUTSIDE_NORM) {
            $pricing_data['unit_price'] = $act->montant_base + $act->montant_depassement;
            unset($pricing_data['exceeding_amount']);
        }

        $data = [
            'external_id'     => $act->_guid,
            'type'            => MedicalActTypeEnum::NGAP(),
            'date'            => new DateTime($act->execution),
            'act_code'        => $code,
            'quantity'        => (int)$act->quantite,
            'coefficient'     => (float)$act->coefficient,
            'spend_qualifier' => self::getSpendQualifierFromAct($act->qualif_depense),
            'pricing'         => Pricing::hydrate($pricing_data),
            'execution_place' => ($act->lieu === 'D' ? 1 : 0),
            'additional'      => $act->complement,
            'prior_agreement' => self::getPriorAgreementFromAct($act),
        ];

        if ($act->_ref_object && $act->_ref_object->concerne_ALD && !$act->ald) {
            $data['exoneration_user_fees'] = 4;
        } elseif ($act->exoneration != 'N') {
            $data['exoneration_user_fees'] = $act->exoneration == '3' ? 31 : (int)$act->exoneration;
        } else {
            $data['exoneration_user_fees'] = -1;
        }

        if ($act->demi) {
            $data['coefficient'] = round($act->coefficient / 2, 2);
        }

        if ($act->isIKInfirmier()) {
            switch ($act->taux_abattement) {
                case 0:
                    $data['nurse_reduction_rate'] = 100;
                    break;
                case 0.50:
                    $data['nurse_reduction_rate'] = 50;
                    break;
                default:
                    $data['nurse_reduction_rate'] = 0;
            }
        }

        return $medical_act = MedicalAct::hydrate($data);
    }

    /**
     * Replace the Ngap code from Mediboard with the corresponding codes from the Sesam Vital specifications
     *
     * @param string $code
     *
     * @return string
     */
    private static function replaceMediboardNgapCode(string $code): string
    {
        return str_replace(self::$mediboard_ngap_codes, self::$sesam_vital_ngap_codes, $code);
    }

    private static function medicalActFromCActeCCAM(CActeCCAM $act): MedicalAct
    {
        $data = [
            'external_id'      => $act->_guid,
            'type'             => MedicalActTypeEnum::CCAM(),
            'date'             => new DateTime($act->execution),
            'act_code'         => $act->code_acte,
            'spend_qualifier'  => self::getSpendQualifierFromAct($act->motif_depassement),
            'pricing'          => Pricing::hydrate(
                [
                    'exceeding_amount'          => $act->montant_depassement,
                    'exceptional_reimbursement' => $act->_rembex ?? null,
                ]
            ),
            'execution_place'  => ($act->lieu === 'D' ? 1 : 0),
            'activity_code'    => (int)$act->code_activite,
            'phase_code'       => (int)$act->code_phase,
            'modifiers'        => $act->_modificateurs ?? null,
            'association_code' => $act->code_association ?? null,
            'teeth'            => $act->position_dentaire ? $act->_dents : null,
            'prior_agreement'  => self::getPriorAgreementFromAct($act),
        ];

        if ($act->gratuit) {
            $data['spend_qualifier'] = 'G';
        }

        if ($act->_ref_object && $act->_ref_object->concerne_ALD && !$act->ald) {
            $data['exoneration_user_fees'] = 4;
        } elseif ($act->exoneration != 'N') {
            $data['exoneration_user_fees'] = $act->exoneration == '3' ? 31 : (int)$act->exoneration;
        } else {
            $data['exoneration_user_fees'] = -1;
        }

        return $medical_act = MedicalAct::hydrate($data);
    }

    private static function medicalActFromCActeLPP(CActeLPP $act): MedicalAct
    {
        $act->updateFormFields();

        return $medical_act = MedicalAct::hydrate(
            [
                'external_id'     => $act->_guid,
                'is_lpp'          => true,
                'date'            => new DateTime($act->execution),
                'act_code'        => $act->code_prestation,
                'spend_qualifier' => self::getSpendQualifierFromAct($act->qualif_depense),
                'quantity'        => (int)$act->quantite,
                'pricing'         => Pricing::hydrate(
                    [
                        'exceeding_amount' => $act->montant_depassement,
                    ]
                ),
                'prior_agreement' => self::getPriorAgreementFromAct($act),
                'lpp_benefit'     => LppBenefit::hydrate([
                    'code'             => $act->code,
                    'type'             => new LppTypeEnum($act->type_prestation),
                    'quantity'         => (int)$act->quantite,
                    'label'            => $act->_code_lpp->name,
                    'siret'            => $act->siret ?? null,
                    'unit_price_ref'   => $act->montant_base,
                    'unit_price_ttc'   => round($act->montant_total / $act->quantite, 2),
                    'total_price_ref'  => $act->montant_final,
                    'total_price_ttc'  => $act->montant_total,
                    'end_date'         => $act->date_fin ? new DateTime($act->date_fin) : null,
                    'begin_date'       => $act->date ? new DateTime($act->date) : null,
                    'sell_price_limit' => $act->_code_lpp->_last_pricing->max_price,
                ]),
            ]
        );
    }

    private static function setExecutingPhysicianFor(MedicalAct $act, CMediusers $user): void
    {
        $user_data_model = CJfseUser::getFromMediuser($user);
        if ($user_data_model) {
            $user = (new UserManagementService())->getUser($user_data_model->jfse_id);
            $situation = $user->getSituation();
            if ($situation) {
                $physician = ExecutingPhysician::hydrate([
                    'invoicing_number' => $situation->getInvoicingNumber() . $situation->getInvoicingNumberKey(),
                    'speciality' => $situation->getSpecialityCode(),
                    'convention' => $situation->getConventionCode(),
                    'pricing_zone' => $situation->getPriceZoneCode(),
                    'practice_condition' => $situation->getPracticeStatus(),
                    'national_id' => $user->getNationalIdentificationNumber(),
                    'structure_id' => substr($situation->getStructureIdentifier(), 0, 14)
                ]);

                $act->setExecutingPhysician($physician);
            }
        } elseif (
            $user->adeli && $user->conv && ExecutingPhysician::checkConvention($user->conv)
            && $user->zisd && ExecutingPhysician::checkPracticeCondition($user->zisd)
            && $user->ik && ExecutingPhysician::checkPricingZone($user->ik)
        ) {
            $national_id = '';
            if ($user->rpps) {
                $national_id = $user->rpps;
            }

            /* Handle the case where the CMediuser has no Jfse account but has all the necessary information */
            $physician = ExecutingPhysician::hydrate([
                'invoicing_number' => $user->adeli,
                'speciality' => $user->spec_cpam_id,
                'convention' => $user->conv,
                'pricing_zone' => $user->ik,
                'practice_condition' => $user->zisd,
                'national_id' => $national_id,
                'structure_id' => substr($user->cab, 0, 14)
            ]);

            $act->setExecutingPhysician($physician);
        }
    }

    public static function getCActeFromMedicalAct(MedicalAct $act): CActe
    {
        if ($act->getIsLpp()) {
            $cacte = self::getCActeLPPFromMedicalAct($act);
        } elseif ($act->getType() === MedicalActTypeEnum::CCAM()) {
            $cacte = self::getCActeCCAMFromMedicalAct($act);
        } else {
            $cacte = self::getCActeNGAPFromMedicalAct($act);
        }

        return $cacte;
    }

    private static function getCActeNGAPFromMedicalAct(MedicalAct $act): CActeNGAP
    {
        $ngap = new CActeNGAP();
        $ngap->code = $act->getActCode();
        $ngap->quantite = $act->getQuantity();
        $ngap->coefficient = $act->getCoefficient();
        $ngap->qualif_depense = $act->getSpendQualifier() ? strtolower($act->getSpendQualifier()) : null;
        $ngap->complement = $act->getAdditional();
        $ngap->lieu = $act->getExecutionPlace() ? 'D' : 'C';
        $ngap->lettre_cle = $act->getKeyLetter() ? '1' : '0';

        if ($act->getExonerationUserFees() == 4) {
            $ngap->ald = '1';
        } elseif (in_array($act->getExonerationUserFees(), [31, 32, 33])) {
            $ngap->exoneration = '3';
        } elseif ($act->getExonerationUserFees() > 0) {
            $ngap->exoneration = $act->getExonerationUserFees();
        }

        $ngap->montant_depassement = $act->getPricing()->getExceedingAmount();
        $ngap->montant_base = $act->getPricing()->getInvoiceTotal() - $act->getPricing()->getExceedingAmount();

        return $ngap;
    }

    private static function getCActeCCAMFromMedicalAct(MedicalAct $act): CActeCCAM
    {
        $ccam = new CActeCCAM();
        $ccam->code_acte = $act->getActCode();
        $ccam->code_activite = $act->getActivityCode();
        $ccam->code_phase = $act->getPhaseCode();
        $ccam->code_association = $act->getAssociationCode();
        $ccam->modificateurs = implode('', $act->getModifiers());
        $ccam->motif_depassement = $act->getSpendQualifier();
        $ccam->lieu = $act->getExecutionPlace() ? 'D' : 'C';
        $ccam->position_dentaire = implode('|', $act->getTeeth());

        if ($act->getExonerationUserFees() == 4) {
            $ccam->ald = '1';
        } elseif (in_array($act->getExonerationUserFees(), [31, 32, 33])) {
            $ccam->exoneration = '3';
        } elseif ($act->getExonerationUserFees() > 0) {
            $ccam->exoneration = $act->getExonerationUserFees();
        }

        $ccam->montant_depassement = $act->getPricing()->getExceedingAmount();
        $ccam->montant_base = $act->getPricing()->getInvoiceTotal() - $act->getPricing()->getExceedingAmount();

        return $ccam;
    }

    private static function getCActeLPPFromMedicalAct(MedicalAct $act): CActeLPP
    {
        $lpp = new CActeLPP();
        $lpp->code_prestation = $act->getActCode();
        $lpp->code = $act->getLppBenefit()->getCode();
        $lpp->type_prestation = $act->getLppBenefit()->getType();
        $lpp->quantite = $act->getLppBenefit()->getQuantity();
        $lpp->siret = $act->getLppBenefit()->getSiretNumber();

        if ($act->getLppBenefit()->getBeginDate()) {
            $lpp->date = $act->getLppBenefit()->getBeginDate()->format('Y-m-d');
        }
        if ($act->getLppBenefit()->getEndDate()) {
            $lpp->date_fin = $act->getLppBenefit()->getEndDate()->format('Y-m-d');
        }

        if ($act->getExonerationUserFees() == 4) {
            $lpp->concerne_ald = '1';
        }

        $lpp->montant_final = $act->getLppBenefit()->getTotalPriceRef();
        $lpp->montant_base = $act->getLppBenefit()->getUnitPriceTtc();
        $lpp->montant_depassement = $act->getPricing()->getExceedingAmount();
        $lpp->montant_total = $act->getLppBenefit()->getTotalPriceTtc();

        return $lpp;
    }

    private static function getPriorAgreementFromAct(CActe $act): ?PriorAgreement
    {
        $prior_agreement = null;
        if ($act->accord_prealable) {
            $data = [];
            switch ($act->reponse_accord) {
                case 'no_answer':
                    $data['value'] = 1;
                    break;
                case 'accepted':
                    $data['value'] = 2;
                    break;
                case 'emergency':
                    $data['value'] = 3;
                    break;
                case 'refused':
                    $data['value'] = 4;
                    break;
                default:
                    $data['value'] = 0;
            }

            if ($act->date_demande_accord) {
                $data['send_date'] = new DateTime($act->date_demande_accord);
            }

            $prior_agreement = PriorAgreement::hydrate($data);
        }

        return $prior_agreement;
    }

    private static function getSpendQualifierFromAct(?string $mediboard_qualifier): ?string
    {
        switch ($mediboard_qualifier) {
            case 'f':
            case 'e':
            case 'd':
            case 'b':
            case 'a':
            case 'g':
            case 'n':
                $qualifier = strtoupper($mediboard_qualifier);
                break;
            default:
                $qualifier = null;
        }

        return $qualifier;
    }

    public static function arrayFromInsuranceAmountForcing(string $act_id, InsuranceAmountForcing $forcing): array
    {
        return [
            'idActe'  => $act_id,
            'type'    => $forcing->getType(),
            'choix'   => $forcing->getChoice(),
            'montant' => $forcing->getInsurancePart()
        ];
    }
}
