<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use DateTime;
use DateTimeImmutable;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Mediboard\Jfse\Api\Message;
use Ox\Mediboard\Jfse\Api\Question;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Adri\DeclaredWorkAccident;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathDoctor;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathEnum;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePathStatusEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\AnonymizationEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\CommonLawAccident;
use Ox\Mediboard\Jfse\Domain\Invoicing\Acs;
use Ox\Mediboard\Jfse\Domain\Invoicing\Complement;
use Ox\Mediboard\Jfse\Domain\Invoicing\ComplementAct;
use Ox\Mediboard\Jfse\Domain\Invoicing\InsuredParticipationAct;
use Ox\Mediboard\Jfse\Domain\Invoicing\Invoice;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoiceUserInterface;
use Ox\Mediboard\Jfse\Domain\Invoicing\Prescription;
use Ox\Mediboard\Jfse\Domain\Invoicing\RuleForcing;
use Ox\Mediboard\Jfse\Domain\Invoicing\ComplementaryHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Invoicing\SecuringModeEnum;
use Ox\Mediboard\Jfse\Domain\Invoicing\ThirdPartyPaymentAssistant;
use Ox\Mediboard\Jfse\Domain\Invoicing\TreatmentTypeEnum;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PhysicianOriginEnum;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmo;
use Ox\Mediboard\Jfse\Domain\Vital\AdditionalHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\AmoServicePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvContext;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\CoverageCodePeriod;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\HealthInsurance;
use Ox\Mediboard\Jfse\Domain\Vital\Patient;
use Ox\Mediboard\Jfse\Domain\Vital\Period;
use stdClass;

class InvoicingMapper extends AbstractMapper
{
    private static function arrayToBeneficiary(array $data, array $adri_data = null): Beneficiary
    {
        $health_insurance = null;
        if (CMbArray::get($data, 'droitsMutuelleCV')) {
            $health_insurance = self::getHealthInsuranceFromResponse(CMbArray::get($data, 'droitsMutuelleCV'));
        }
        $additional_insurance = null;
        if (CMbArray::get($data, 'droitsAMCCV')) {
            $additional_insurance = self::getAdditionalHealthInsuranceFromResponse(CMbArray::get($data, 'droitsAMCCV'));
        }

        $accidents = [];
        if (CMbArray::get($data, 'donneesAT')) {
            $accidents = self::getWorkAccidentsFromResponse(CMbArray::get($data, 'donneesAT'));
        }

        $prescribing_physician_top = null;
        if (
            $adri_data && CMbArray::get($adri_data, 'beneficiaireDeSoins')
            && CMbArray::get($adri_data['beneficiaireDeSoins'], 'topMedecinTraitant') === '0'
        ) {
            $prescribing_physician_top = true;
        }

        $apcv = false;
        $apcv_context = null;
        if (CMbArray::get($data, 'apCV') && CMbArray::get($data, 'contexteApCV')) {
            $apcv = true;
            $apcv_context = (new ApCvMapper())->getApCvContextFromResponse($data['contexteApCV']);
        }

        return Beneficiary::hydrate(
            [
                'nir'                           => CMbArray::get($data, 'immatriculation'),
                "patient"                       => self::arrayToPatient($data),
                "certified_nir"                 => substr($data["nircertifie"], 0, -2),
                "certified_nir_key"             => substr($data["nircertifie"], -2),
                "nir_certification_date"        => new DateTimeImmutable($data["dateNirCertifie"]),
                "amo_service_period"            => self::arrayToAmoServicePeriod(
                    CMbArray::get($data, "droitsAMO", [])
                ),
                "coverage_code_periods"         => self::arrayToCoverageCodePeriod(
                    CMbArray::get($data, "droitsAMO", [])
                ),
                'health_insurance'              => $health_insurance,
                'additional_health_insurance'   => $additional_insurance,
                'declared_work_accidents'       => $accidents,
                'prescribing_physician_top'     => $prescribing_physician_top,
                'apcv'                          => $apcv,
                'apcv_context'                  => $apcv_context,
            ]
        );
    }

    private static function arrayToPatient(array $data): Patient
    {
        $birth = $data["dateNaissance"];
        $birth = substr($birth, 0, 4) . '-' . substr($birth, 4, 2) . '-' . substr($birth, 6, 2);

        return Patient::hydrate(
            [
                "last_name"  => $data["nom"],
                "birth_date" => $birth,
                "first_name" => $data["prenom"],
                "birth_rank" => (int)$data["rangGemellaire"],
            ]
        );
    }

    private static function arrayToAmoServicePeriod(array $data): AmoServicePeriod
    {
        return AmoServicePeriod::hydrate(
            [
                "codeService" => $data["codeService"],
                "begin_date"  => new DateTimeImmutable($data["dateDebut"]),
                "end_date"    => new DateTimeImmutable($data["dateFin"]),
            ]
        );
    }

    private static function arrayToCoverageCodePeriod(array $data): CoverageCodePeriod
    {
        return CoverageCodePeriod::hydrate(
            [
                "situation_code" => $data["codeCouverture"],
                "begin_date"     => new DateTimeImmutable($data["dateDebut"]),
                "end_date"       => new DateTimeImmutable($data["dateFin"]),
            ]
        );
    }

    private static function arrayToInsured(array $data): Insured
    {
        $patient      = $data["patient"];
        $insured_data = $data["assure"];
        $amo_rights = CMbArray::get($patient, 'droitsAMO', []);

        return Insured::hydrate(
            [
                "nir"             => substr($patient["immatriculation"], 0, -2),
                "nir_key"         => substr($patient["immatriculation"], -2),
                "first_name"      => $insured_data["prenom"],
                "last_name"       => $insured_data["nom"],
                "birth_name"      => CMbArray::get($insured_data, 'nomPatronymique'),
                "regime_code"     => CMbArray::get($amo_rights, "codeRegime"),
                "managing_fund"   => CMbArray::get($amo_rights, "codeCaisse"),
                "managing_center" => CMbArray::get($amo_rights, "codeCentre"),
                "managing_code"   => CMbArray::get($amo_rights, "codeGestion"),
            ]
        );
    }

    public function makeSetAccidentDcArrayFromCommonLawAccident(
        CommonLawAccident $common_law_accident,
        string $invoice_id
    ): array {
        return [
            "idFacture"  => $invoice_id,
            "accidentDC" => self::getCommonLawAccidentArrayFromEntity($common_law_accident)
        ];
    }

    private static function getCommonLawAccidentArrayFromEntity(CommonLawAccident $accident): array
    {
        $data = [
            'accidentDC' => (int)$accident->getCommonLawAccident()
        ];
        self::addOptionalValue("dateAccident", $accident->getDateString(), $data);

        return $data;
    }

    public function makeRemoveCotationArrayFromLstCotations(array $lst_cotations, string $invoice_id): array
    {
        $data = [
            "idFacture"    => $invoice_id,
            "lstCotations" => [],
        ];
        foreach ($lst_cotations as $medical_act) {
            if ($medical_act instanceof MedicalAct) {
                $data["lstCotations"][] = ["id" => $medical_act->getId()];
            }
        }

        return $data;
    }

    public function makeGetListeActesArrayFromData(string $invoice_id, array $data): array
    {
        $lst_acts = [
            "idFacture"     => $invoice_id,
            "getListeActes" => [],
        ];

        self::addOptionalValue("filtre", $data["filtre"], $lst_acts["getListeActes"]);
        self::addOptionalValue("dateExecution", $data["dateExecution"], $lst_acts["getListeActes"]);
        self::addOptionalValue("typeActe", $data["typeActe"], $lst_acts["getListeActes"]);
        self::addOptionalValue(
            "filtreSpecialite",
            $data["filtreSpecialite"],
            $lst_acts["getListeActes"]
        );

        return $lst_acts;
    }

    /**
     * @param string     $invoice_id
     * @param Question[] $data
     *
     * @return array
     */
    public function makeSetReponseQuestionsArrayFromData(string $invoice_id, array $data): array
    {
        $lst_answers = [
            "idFacture"           => $invoice_id,
            "lstReponseQuestions" => [],
        ];

        foreach ($data as $question) {
            if ($question instanceof Question) {
                $lst_answers["lstReponseQuestions"][] = [
                    "id"      => $question->getId(),
                    "reponse" => $question->getAnswer(),
                ];
            }
        }

        return $lst_answers;
    }

    public function makeSetOrganismeComplementaireFromEntity(
        string $invoice_id,
        ComplementaryHealthInsurance $complementary_health_insurance
    ): array {
        $data = [
            "idFacture"               => $invoice_id,
            "organismeComplementaire" => [
                "tiersPayantAMC" => intval($complementary_health_insurance->getThirdPartyAmc()),
            ],
        ];

        self::addOptionalValue(
            "tiersPayantAMO",
            intval($complementary_health_insurance->getThirdPartyAmo()),
            $data["organismeComplementaire"]
        );
        self::addOptionalValue(
            "victimeAttentat",
            intval($complementary_health_insurance->getAttackVictim()),
            $data["organismeComplementaire"]
        );
        self::addOptionalValue(
            "tiersPayantSNCF",
            intval($complementary_health_insurance->getThirdPartySncf()),
            $data["organismeComplementaire"]
        );

        if ($complementary_health_insurance->getAmoService()) {
            $data['organismeComplementaire']['serviceAMO'] = self::getArrayFromServiceAmo(
                $complementary_health_insurance->getAmoService()
            );
        }

        if ($complementary_health_insurance->getHealthInsurance()) {
            $data['organismeComplementaire']['mutuelle'] =
                self::getArrayFromHealthInsurance($complementary_health_insurance->getHealthInsurance());
        }

        if ($complementary_health_insurance->getAdditionalHealthInsurance()) {
            $data['organismeComplementaire']['AMC'] = self::getArrayFromAdditionalHealthInsurance(
                $complementary_health_insurance->getAdditionalHealthInsurance()
            );
        }

        self::addOptionalValue(
            "ACS",
            $complementary_health_insurance->getAcs(),
            $data["organismeComplementaire"]
        );

        if ($complementary_health_insurance->getConvention()) {
            $data["organismeComplementaire"]['convention'] =
                ConventionMapper::makeArrayFromConvention($complementary_health_insurance->getConvention());
        }

        if ($complementary_health_insurance->getFormula()) {
            $data["organismeComplementaire"]['formule'] =
                FormulaMapper::makeArrayFromFormula($complementary_health_insurance->getFormula());
        }

        return $data;
    }

    public function makeGetConventionsArrayFromConvention(string $invoice_id, array $convention_data = []): array
    {
        $data = [
            "idFacture" => $invoice_id,
        ];

        if (count($convention_data)) {
            $data["getConventions"] = [
                "AMO" => $convention_data["AMO"],
            ];

            if (array_key_exists("AMC", $convention_data)) {
                $data["getConventions"]["AMC"] = [
                    "numeroComplementaireB2" => $convention_data["AMC"]["numeroComplementaireB2"] ?? '',
                ];
                self::addOptionalValue(
                    "critereSecondaire",
                    $convention_data["AMC"]["critereSecondaire"],
                    $data["getConventions"]["AMC"]
                );
                self::addOptionalValue(
                    "typeConvention",
                    $convention_data["AMC"]["typeConvention"],
                    $data["getConventions"]["AMC"]
                );
            }
            if (array_key_exists("mutuelle", $convention_data)) {
                $data["getConventions"]["mutuelle"] = $convention_data["mutuelle"];
            }
        }

        return $data;
    }

    public function makeInitFactureArrayFromInvoice(Invoice $invoice, bool $initialize = false): array
    {
        $data = [
            "FSE" => [
                "facture" => [
                    "securisation"               => $invoice->getSecuring()->getValue(),
                    "suppressionFactureAutorise" => intval($invoice->getAutomaticDeletion()),
                    'checkVitaleCard'            => 0,
                ],
            ],
        ];

        self::addOptionalValue("modePapier", (int)$invoice->getPaperMode(), $data["FSE"]["facture"]);

        if ($invoice->getFspMode() >= 0) {
            self::addOptionalValue("modeFSP", $invoice->getFspMode(), $data["FSE"]["facture"]);
        }

        if ($invoice->getCarePath() && $invoice->getCarePath()->getIndicator()) {
            $data["FSE"]["facture"]['parcoursSoins'] = CarePathMapper::getArrayFromEntity($invoice->getCarePath());
        }

        if ($invoice->getAnonymize()) {
            $data["FSE"]["facture"]['anonymiser'] = ['anonymisation' => $invoice->getAnonymize()->getValue()];
        }

        self::addOptionalValue(
            "alsaceMoselle",
            (int)$invoice->getAlsaceMoselle(),
            $data["FSE"]["facture"]
        );

        if ($invoice->getCreationDate()) {
            $data["FSE"]["facture"]['dateElaboration'] = $invoice->getCreationDate()->format('Ymd');
        }

        self::addOptionalValue(
            "idIntegrateur",
            $invoice->getIntegratorId(),
            $data["FSE"]["facture"]
        );
        self::addOptionalValue(
            "differerEnvoi",
            (int)$invoice->getDelayTransmission(),
            $data["FSE"]["facture"]
        );

        if ($invoice->getCommonLawAccident()) {
            $data['FSE']['facture']['accidentDC'] = self::getCommonLawAccidentArrayFromEntity(
                $invoice->getCommonLawAccident()
            );
        }

        // Attributs ajoutés par InvoiceDummy
        self::addOptionalValue("desactivationSTS", (int)$invoice->getStsDisabled(), $data["FSE"]["facture"]);
        self::addOptionalValue("idModele", $invoice->getTemplateId(), $data["FSE"]["facture"]);
        self::addOptionalValue(
            "affichageBandeauBenef",
            (int)$invoice->getBeneficiaryBannerDisplay(),
            $data["FSE"]["facture"]
        );
        if ($invoice->getUserInterface()) {
            self::addOptionalValue(
                "blocageActes",
                (int)$invoice->getUserInterface()->getActsLock(),
                $data["FSE"]["facture"]
            );
        }

        // Noeuds Facultatifs
        if ($invoice->getInsurance()) {
            $data["FSE"]["facture"]["natureAssurance"] = InsuranceTypeMapper::getArrayFromInsurance(
                $invoice->getInsurance()
            );
        }

        if ($invoice->getTreatmentType() && $invoice->getTreatmentType()->getValue() > 0) {
            $data["FSE"]["facture"]['typeTraitement'] = ['typeTraitement' => $invoice->getTreatmentType()->getValue()];
        }

        if ($invoice->getMedicalActs()) {
            self::addOptionalValue(
                "lstCotations",
                self::arrayFromMedicalActs($invoice->getMedicalActs(), $initialize),
                $data["FSE"]["facture"]
            );
        }

        if ($invoice->getRuleForcing()) {
            $data["FSE"]["facture"]['forcageRegle']['regleSerialId'] = $invoice->getRuleForcing()->getSerialId();
        }

        // Entités facultatives
        if (
            $invoice->getBeneficiary() && $invoice->getBeneficiary()->getInsured()
            && $invoice->getBeneficiary()->getInsured()->getNir()
        ) {
            $beneficiary = $invoice->getBeneficiary();

            $birth_date = $beneficiary->getPatient()->getBirthDate();
            if (strpos($birth_date, '-') !== false) {
                $birth_date = str_replace('-', '', $birth_date);
            }

            $beneficiary_data = [
                "immatriculation" => $beneficiary->getInsured()->getNir() . $beneficiary->getInsured()->getNirKey(),
                "qualite"         => $beneficiary->getQuality(),
                "dateNaissance"   => $birth_date,
                "rangGemellaire"  => $beneficiary->getPatient()->getBirthRank(),
            ];

            if ($beneficiary->getIntegratorId()) {
                $beneficiary_data['idExterne'] = $beneficiary->getIntegratorId();
            }

            if ($beneficiary->getCertifiedNir()) {
                $beneficiary_data['nirCertifie'] = $beneficiary->getCertifiedNir() . $beneficiary->getCertifiedNirKey();
            }

            if ($beneficiary->getPatient()->getLastName()) {
                $beneficiary_data['nom'] = $beneficiary->getPatient()->getLastName();
            }

            if ($beneficiary->getPatient()->getFirstName()) {
                $beneficiary_data['prenom'] = $beneficiary->getPatient()->getFirstName();
            }

            if (($invoice->areAmoInformationsNeeded() || $beneficiary->getApcv()) && $beneficiary->getInsured()) {
                $beneficiary_data['AMO'] = [
                    'codeRegime'  => $beneficiary->getInsured()->getRegimeCode(),
                    'codeCaisse'  => $beneficiary->getInsured()->getManagingFund(),
                    'codeCentre'  => $beneficiary->getInsured()->getManagingCenter(),
                    'codeGestion' => $beneficiary->getInsured()->getManagingCode(),
                ];

                /* We set a default value of the code because it is mandatory */
                if (is_null($beneficiary_data['AMO']['codeGestion'])) {
                    $beneficiary_data['AMO']['codeGestion'] = '10';
                }

                if ($beneficiary->getInsured()->getSituationCode()) {
                    $beneficiary_data['AMO']['codeSituation'] = $beneficiary->getInsured()->getSituationCode();
                }
            }

            if ($beneficiary->getApcv() && $beneficiary->getApcvContext()) {
                $beneficiary_data['apCV'] = 1;
                $beneficiary_data['contexteApCV'] = (new ApCvMapper())->arrayFromApCvContext(
                    $beneficiary->getApcvContext()
                );
            }

            $data["FSE"]["beneficiaire"] = $beneficiary_data;
        }

        if ($invoice->getPrescription()) {
            $prescription          = $invoice->getPrescription();
            $prescribing_physician = $prescription->getPrescriber();

            $data["FSE"]["prescripteur"] = [
                "datePrescription" => $prescription->getDate() ? $prescription->getDate()->format('Ymd') : '',
            ];

            if ($prescribing_physician) {
                $data["FSE"]["prescripteur"]["medecin"] = [
                    "nom"           => $prescribing_physician->getLastName(),
                    "prenom"        => $prescribing_physician->getFirstName(),
                    "noFacturation" => $prescribing_physician->getInvoicingNumber(),
                    "specialite"    => $prescribing_physician->getSpeciality(),
                    "type"          => $prescribing_physician->getType(),
                ];
                self::addOptionalValue(
                    "rpps",
                    $prescribing_physician->getNationalId(),
                    $data["FSE"]["prescripteur"]["medecin"]
                );
                self::addOptionalValue(
                    "noStructure",
                    $prescribing_physician->getStructureId(),
                    $data["FSE"]["prescripteur"]["medecin"]
                );
            }

            if ($prescription->getOrigin()) {
                $data["FSE"]["prescripteur"]['originePrescription'] = $prescription->getOrigin()->getValue();
            }
        }

        if ($invoice->getInsured()) {
            $assure = $invoice->getInsured();

            $data["FSE"]["assure"] = [
                "nom"             => $assure->getLastName(),
                "prenom"          => $assure->getFirstName(),
                "immatriculation" => $assure->getNir() . $assure->getNirKey(),
                "nomPatronymique" => $assure->getBirthName(),
            ];
        }

        if ($invoice->getComplementaryHealthInsurance()) {
            $complementary_health_insurance = $invoice->getComplementaryHealthInsurance();

            $data["FSE"]["organismeComplementaire"] = [
                "tiersPayantAMC" => (int)$complementary_health_insurance->getThirdPartyAmc(),
            ];
            self::addOptionalValue(
                "tiersPayantAMO",
                (int)$complementary_health_insurance->getThirdPartyAmo(),
                $data["FSE"]["organismeComplementaire"]
            );
            self::addOptionalValue(
                "victimeAttentat",
                (int)$complementary_health_insurance->getAttackVictim(),
                $data["FSE"]["organismeComplementaire"]
            );
            self::addOptionalValue(
                "tiersPayantSNCF",
                (int)$complementary_health_insurance->getThirdPartySncf(),
                $data["FSE"]["organismeComplementaire"]
            );

            if ($complementary_health_insurance->getConvention()) {
                $data["organismeComplementaire"]['convention'] =
                    ConventionMapper::makeArrayFromConvention($complementary_health_insurance->getConvention());
            }

            if (
                $complementary_health_insurance->getHealthInsurance()
                && $complementary_health_insurance->getThirdPartyAmc()
            ) {
                $data['FSE']['organismeComplementaire']['mutuelle'] =
                    self::getArrayFromHealthInsurance($complementary_health_insurance->getHealthInsurance());
            } elseif (
                $complementary_health_insurance->getAdditionalHealthInsurance()
                && $complementary_health_insurance->getThirdPartyAmc()
            ) {
                $data['FSE']['organismeComplementaire']['AMC'] = self::getArrayFromAdditionalHealthInsurance(
                    $complementary_health_insurance->getAdditionalHealthInsurance()
                );
            }

            if ($complementary_health_insurance->getAcs()) {
                $data['FSE']['organismeComplementaire']['ACS'] = [
                    'modeGestion' => $complementary_health_insurance->getAcs()->getManagementMode(),
                    'typeContrat' => $complementary_health_insurance->getAcs()->getContractType(),
                ];
            }

            if (
                $complementary_health_insurance->getAmoService()
                && $complementary_health_insurance->getAmoService()->getCode() !== '00'
                && $complementary_health_insurance->getAmoService()->getCode() !== null
            ) {
                $amo_service = $complementary_health_insurance->getAmoService();
                $data['FSE']['organismeComplementaire']['serviceAMO'] = [
                    'code' => $amo_service->getCode()
                ];
                if ($amo_service->getBeginDate() && $amo_service->getEndDate()) {
                    $data['FSE']['organismeComplementaire']['serviceAMO']['dateDebut'] =
                        $amo_service->getBeginDate()->format('Ymd');
                    $data['FSE']['organismeComplementaire']['serviceAMO']['dateFin'] =
                        $amo_service->getEndDate()->format('Ymd');
                }
            }

            if ($complementary_health_insurance->getFormula()) {
                $data["organismeComplementaire"]['formule'] =
                    FormulaMapper::makeArrayFromFormula($complementary_health_insurance->getFormula());
            }
        }

        return $data;
    }

    public function arrayFromProofAmo(ProofAmo $proof_amo): array
    {
        $data = [
            'nature' => (int)$proof_amo->getNature()
        ];


        self::addOptionalValue("date", $proof_amo->getDate() ? $proof_amo->getDate()->format('Ymd') : null, $data);
        self::addOptionalValue("origine", $proof_amo->getOrigin(), $data);

        return $data;
    }

    /**
     * @param MedicalAct[] $acts
     * @param bool         $initialize
     *
     * @return array
     */
    public function arrayFromMedicalActs(array $acts, bool $initialize = false): array
    {
        $cotations = [];
        foreach ($acts as $act) {
            $cotations[] = MedicalActMapper::getArrayFromMedicalAct($act, $initialize);
        }

        if (count($cotations) === 1) {
            $cotations = reset($cotations);
        }

        return $cotations;
    }

    public function makeAssistantAcsArrayFromEntity(Acs $acs): array
    {
        return [
            "assistantACS" => [
                "modeGestion" => $acs->getManagementMode(),
                "typeContrat" => $acs->getContractType(),
            ],
        ];
    }

    public function makeSetForceReglesFromEntity(string $invoice_id, RuleForcing $rule_forcing): array
    {
        return [
            "idFacture"    => $invoice_id,
            "forcageRegle" => [
                "regleSerialId" => $rule_forcing->getSerialId(),
            ],
        ];
    }

    public static function getInvoiceFromResponse(Response $response): Invoice
    {
        $response = $response->getContent();

        $fse_response = array_key_exists('fsedata', $response) ? CMbArray::get($response, 'fsedata', []) : $response;
        $invoice_response = CMbArray::get($fse_response, 'facture', []);

        $securing = (int)CMbArray::get($invoice_response, 'securisation');
        $anonymize = (int)CMbArray::get($invoice_response, 'anonymiser');
        $treatment_type = (int)CMbArray::get($invoice_response, 'acteIsoleSerie');
        $medical_acts = CMbArray::get($invoice_response, 'lstCotations', []);
        $adri_data = CMbArray::get($fse_response, 'adri') ?: null;

        $data = [
            'id' =>  CMbArray::get($invoice_response, 'keyFacture'),
            'securing' => SecuringModeEnum::isValid($securing) ? new SecuringModeEnum($securing) : null,
            'alsace_moselle' => (bool)CMbArray::get($invoice_response, 'alsaceMoselle'),
            'creation_date' => DateTime::createFromFormat(
                'Ymd',
                CMbArray::get($invoice_response, 'dateElaboration')
            ),
            'integrator_id' => (int)CMbArray::get($invoice_response, 'idIntegrateur'),
            'delay_transmission' => (bool)CMbArray::get($invoice_response, 'differerEnvoi'),
            'anonymize' => AnonymizationEnum::isValid($anonymize) ? new AnonymizationEnum($anonymize) : null,
            'paper_mode' => (bool)CMbArray::get($invoice_response, 'modePapier'),
            'fsp_mode' => (int)CMbArray::get($invoice_response, 'modeFSP'),
            'total_amount' => (float)CMbArray::get($invoice_response, 'totalMontants'),
            'total_insured' => (float)CMbArray::get($invoice_response, 'totalAssure'),
            'total_amo' => (float)CMbArray::get($invoice_response, 'totalAMO'),
            'total_amc' => (float)CMbArray::get($invoice_response, 'totalAMC'),
            'invoice_number' => (int)CMbArray::get($invoice_response, 'numeroFacture'),
            'amount_owed_amo' => (float)CMbArray::get($invoice_response, 'duAMO'),
            'amount_owed_amc' => (float)CMbArray::get($invoice_response, 'duAMC'),
            'treatment_type' => TreatmentTypeEnum::isValid($treatment_type) ?
                new TreatmentTypeEnum($treatment_type) : null,
            'forcing_amo' => (bool)CMbArray::get($invoice_response, 'forcageAMO'),
            'forcing_amc' => (bool)CMbArray::get($invoice_response, 'forcageAMC'),
            'c2s_maximum_amount' => (float)CMbArray::get($invoice_response, 'valeurPlafondCMU'),
            'amo_right_status' => CMbArray::get($invoice_response, 'etatDroitsAMO'),
            'check_vital_card' => (bool)CMbArray::get($invoice_response, 'checkVitaleCard'),
            'global_rate' => CMbArray::get($invoice_response, 'tauxGlobal'),
            'correct_or_recycle' => CMbArray::get($invoice_response, 'corrigerOuRecycler'),
            'invoice_complements' => (bool)CMbArray::get($invoice_response, 'complementsFacture', false),
            'adri'                => array_key_exists('adri', $fse_response),
            'care_path' => self::getCarePathFromResponse(CMbArray::get($invoice_response, 'parcoursSoins', [])),
            'common_law_accident' => self::getCommonLawAccidentFromResponse(
                CMbArray::get($invoice_response, 'accidentDC', [])
            ),
            'insurance' => InsuranceTypeMapper::getInsuranceTypeFromResponse(
                CMbArray::get($invoice_response, 'natureAssurance', [])
            ),
            'medical_acts' => MedicalActMapper::medicalActsFromResponse(
                CMbArray::get($invoice_response, 'lstCotations', [])
            ),
            'proof_amo' => self::getProofAmoFromResponse(CMbArray::get($invoice_response, 'pieceJustificativeAMO', [])),
            'prescription' => self::getPrescriptionFromResponse(CMbArray::get($invoice_response, 'prescripteur', [])),
            'complementary_health_insurance' => self::getComplementaryHealthInsuranceFromResponse(
                CMbArray::get($invoice_response, 'organismeComplementaire', [])
            ),
            'insured_participation_acts' => self::getInsuredParticipationActsFromResponse($invoice_response),
            'beneficiary' => self::arrayToBeneficiary(CMbArray::get($fse_response, 'patient', []), $adri_data),
            'insured' => self::arrayToInsured($fse_response),
            'practitioner' => UserMapper::getUserFromData(CMbArray::get($fse_response, 'ps', [])),
            'messages' => self::getMessagesFromResponse(CMbArray::get($fse_response, 'lstMessages', [])),
            'questions' => self::getQuestionsFromResponse(CMbArray::get($fse_response, 'lstQuestions', [])),
            'user_interface' => self::getInvoiceUserInterfaceFromResponse(CMbArray::get($fse_response, 'ihm', [])),
        ];

        return Invoice::hydrate($data);
    }

    private static function getCarePathFromResponse(array $response): ?CarePath
    {
        $care_path = null;
        $doctor = CMbArray::get($response, 'medecin', []);

        if (CMbArray::get($response, 'indicateur', -1) !== -1) {
            $declaration = (int)CMbArray::get($response, 'declaration');

            $doctor_data = [
                'last_name'     => CMbArray::get($doctor, 'nom', ''),
                'first_name'    => CMbArray::get($doctor, 'prenom', ''),
            ];

            if (CMbArray::get($doctor, 'noIdentification')) {
                $doctor_data['invoicing_id'] = CMbArray::get($doctor, 'noIdentification');
            }

            $care_path = CarePath::hydrate([
                'indicator' => CarePathEnum::isValid(CMbArray::get($response, 'indicateur')) ?
                    new CarePathEnum(CMbArray::get($response, 'indicateur')) : null,
                'install_date' => CMbArray::get($doctor, 'dateInstallation') ?
                    new DateTimeImmutable(CMbArray::get($doctor, 'dateInstallation')) : null,
                'poor_md_zone_install_date' => CMbArray::get($doctor, 'dateInstallationZoneSousMedicalisee') ?
                    new DateTimeImmutable(CMbArray::get($doctor, 'dateInstallationZoneSousMedicalisee')) : null,
                'declaration' => $declaration === 0 ? null : ($declaration === 1),
                'status' => CarePathStatusEnum::isValid(CMbArray::get($response, 'statut')) ?
                    new CarePathStatusEnum(CMbArray::get($response, 'statut')) : null,
                'doctor' => CarePathDoctor::hydrate($doctor_data)
            ]);
        }

        return $care_path;
    }

    private static function getCommonLawAccidentFromResponse(array $response): CommonLawAccident
    {
        /* No bool conversion because the field must be null if empty or equal to -1 in the response */
        switch (CMbArray::get($response, 'accidentDC')) {
            case '1':
                $common_law_accident = true;
                break;
            case '0':
                $common_law_accident = false;
                break;
            default:
                $common_law_accident = null;
        }

        return CommonLawAccident::hydrate([
                'common_law_accident' => $common_law_accident,
                'date' => CMbArray::get($response, 'dateAccident') ?
                    new DateTime(CMbArray::get($response, 'dateAccident')) : null,
            ]);
    }

    private static function getProofAmoFromResponse(array $response): ?ProofAmo
    {
        $proof_amo = null;

        if (CMbArray::get($response, 'nature', -1) !== -1) {
            $proof_amo = ProofAmo::hydrate([
                'nature' => CMbArray::get($response, 'nature'),
                'date'   => CMbArray::get($response, 'date') ? new DateTime(CMbArray::get($response, 'date')) : null,
                'origin' => CMbArray::get($response, 'origine')
            ]);
        }

        return $proof_amo;
    }

    private static function getPrescriptionFromResponse(array $response): ?Prescription
    {
        $prescription = null;
        if (CMbArray::get($response, 'originePrescription', '') !== '') {
            $prescription = Prescription::hydrate([
                'date' => CMbArray::get($response, 'datePrescription') ?
                    new DateTimeImmutable(CMbArray::get($response, 'datePrescription')) : null,
                'origin' => PhysicianOriginEnum::isValid(CMbArray::get($response, 'originePrescription')) ?
                    new PhysicianOriginEnum(CMbArray::get($response, 'originePrescription')) : null,
                'prescriber' => CMbArray::get($response, 'medecin') ?
                    PrescribingPhysicianMapper::getPhysicianFromResponse(
                        CMbArray::get($response, 'medecin')
                    ) : null
            ]);
        }

        return $prescription;
    }

    private static function getComplementaryHealthInsuranceFromResponse(array $response): ?ComplementaryHealthInsurance
    {
        $complementary_insurance = null;
        if (count($response)) {
            $data = [
                'third_party_amo' => (bool)CMbArray::get($response, 'tiersPayantAMO', false),
                'third_party_amc' => (int)CMbArray::get($response, 'tiersPayantAMC', false),
                'attack_victim' => (bool)CMbArray::get($response, 'victimeAttentat', false),
                'third_party_sncf' => (bool)CMbArray::get($response, 'tiersPayantSNCF', false),
            ];

            $service_period = CMbArray::get($response, 'serviceAMO', null);
            if ($service_period) {
                $data['amo_service'] = AmoServicePeriod::hydrate([
                    'code' => CMbArray::get($service_period, 'code'),
                    'label' => CMbArray::get($service_period, 'libelle'),
                    'begin_date' => CMbArray::get($service_period, 'dateDebut', '') !== '' ?
                        new DateTimeImmutable(CMbArray::get($service_period, 'dateDebut')) : null,
                    'end_date' => CMbArray::get($service_period, 'dateFin', '') !== '' ?
                        new DateTimeImmutable(CMbArray::get($service_period, 'dateFin')) : null,
                ]);
            }

            $health_insurance = CMbArray::get($response, 'mutuelle', null);
            if ($health_insurance) {
                $data['health_insurance'] = self::getHealthInsuranceFromResponse($health_insurance);
            }

            $additional_health_insurance = CMbArray::get($response, 'AMC', null);
            if ($additional_health_insurance) {
                $data['additional_health_insurance'] =
                    self::getAdditionalHealthInsuranceFromResponse($additional_health_insurance);
            }

            $convention = CMbArray::get($response, 'convention', null);
            if ($convention) {
                $data['convention'] = ConventionMapper::getConventionFromResponse($convention);
            }

            $formula = CMbArray::get($response, 'formule', null);
            if ($formula) {
                $data['formula'] = FormulaMapper::getFormulaFromResponse($formula);
            }

            $assistant = CMbArray::get($response, 'assistant', null);
            if ($assistant) {
                $data['assistant'] = self::getThirdPartyPaymentAssistantFromResponse($assistant);
            }

            $complementary_insurance = ComplementaryHealthInsurance::hydrate($data);
        }

        return $complementary_insurance;
    }

    private static function getHealthInsuranceFromResponse(array $response): HealthInsurance
    {
        $begin_date = null;
        if (CMbArray::get($response, 'dateDebut')) {
            $begin_date = new DateTimeImmutable(CMbArray::get($response, 'dateDebut'));
        } elseif (CMbArray::get($response, 'dateDebutDroits')) {
            $begin_date = new DateTimeImmutable(CMbArray::get($response, 'dateDebutDroits'));
        }
        $end_date = null;
        if (CMbArray::get($response, 'dateFin')) {
            $end_date = new DateTimeImmutable(CMbArray::get($response, 'dateFin'));
        } elseif (CMbArray::get($response, 'dateFinDroits')) {
            $end_date = new DateTimeImmutable(CMbArray::get($response, 'dateFinDroits'));
        }

        return HealthInsurance::hydrate([
            'id' => CMbArray::get($response, 'identification'),
            'effective_guarantees' => CMbArray::get($response, 'garantiesEffectives'),
            'treatment_indicator' => CMbArray::get($response, 'indicateurTraitement'),
            'associated_services_type' => CMbArray::get($response, 'typeServicesAssocies'),
            'associated_services' => CMbArray::get($response, 'servicesAssocies'),
            'referral_sts_code' => CMbArray::get($response, 'codeAiguillageSTS'),
            'health_insurance_periods_rights' => Period::hydrate([
                'begin_date' => $begin_date,
                'end_date' => $end_date,
            ]),
            'contract_type' => (int)CMbArray::get($response, 'typeContrat'),
            'pec' => CMbArray::get($response, 'pec'),
            'paper_mode' => (bool)CMbArray::get($response, 'attestationPapier'),
            'rights_forcing' => (bool)CMbArray::get($response, 'forcageDroits'),
            'adri_origin' => (bool)CMbArray::get($response, 'issuAdri'),
            'label' => CMbArray::get($response, 'libelleInfos'),
            'type' => CMbArray::get($response, 'typeMutuelle'),
        ]);
    }

    private static function getArrayFromHealthInsurance(HealthInsurance $insurance): array
    {
        $data = [
            'idExterne' => '',
            'identification' => $insurance->getId(),
            'garantiesEffectives' => (string)$insurance->getEffectiveGuarantees(),
            'indicateurTraitement' => (string)$insurance->getTreatmentIndicator(),
            'typeServicesAssocies' => (string)$insurance->getAssociatedServicesType(),
            'servicesAssocies' => (string)$insurance->getAssociatedServices(),
            'codeAiguillageSTS' => $insurance->getReferralStsCode() ?? '',
            'dateDebutDroits' => $insurance->getHealthInsurancePeriodsRights()
                && $insurance->getHealthInsurancePeriodsRights()->getBeginDate()
                ? $insurance->getHealthInsurancePeriodsRights()->getBeginDate()->format('Ymd') : '',
            'dateFinDroits' => $insurance->getHealthInsurancePeriodsRights()
                && $insurance->getHealthInsurancePeriodsRights()->getEndDate()
                ? $insurance->getHealthInsurancePeriodsRights()->getEndDate()->format('Ymd') : '',
            'attestationPapier' => (int)$insurance->getPaperMode()
        ];

        if ($insurance->getContractType()) {
            $data['typeContrat'] = $insurance->getContractType();
        }

        if ($insurance->getRightsForcing()) {
            $data['forcageDroits'] = (int)$insurance->getRightsForcing();
        }

        if ($insurance->getPec()) {
            $data['pec'] = $insurance->getPec();
        }

        return $data;
    }

    private static function getAdditionalHealthInsuranceFromResponse(array $response): AdditionalHealthInsurance
    {
        $data = [
            'number_b2'                     => CMbArray::get($response, 'numeroComplementaireB2'),
            'subscriber_number'             => CMbArray::get($response, 'numeroAdherent'),
            'treatment_indicator'           => CMbArray::get($response, 'indicateurTraitement'),
            'begin_date'                    => CMbArray::get($response, 'dateDebutDroits') ?
                new DateTimeImmutable(CMbArray::get($response, 'dateDebutDroits')) : null,
            'end_date'                      => CMbArray::get($response, 'dateFinDroits') ?
                new DateTimeImmutable(CMbArray::get($response, 'dateFinDroits')) : null,
            'routing_code'                  => CMbArray::get($response, 'codeRoutage'),
            'host_id'                       => CMbArray::get($response, 'identifiantHote'),
            'domain_name'                   => CMbArray::get($response, 'nomDomaine'),
            'referral_sts_code'             => CMbArray::get($response, 'codeAiguillageSTS'),
            'services_type'                 => CMbArray::get($response, 'typeServices'),
            'associated_services_contract'  => CMbArray::get($response, 'servicesAssocies'),
            'contract_type'                 => CMbArray::get($response, 'typeContrat'),
            'pec'                           => CMbArray::get($response, 'pec'),
            'secondary_criteria'            => CMbArray::get($response, 'critereSecondaire'),
            'convention_type'               => CMbArray::get($response, 'typeConvention'),
            'paper_mode'                    => (bool)CMbArray::get($response, 'attestationPapier'),
            'rights_forcing'                => CMbArray::get($response, 'forcageDroits'),
            'label'                         => CMbArray::get($response, 'libelleInfos'),
            'type'                          => (int)CMbArray::get($response, 'typeAMC'),
            'id'                            => CMbArray::get($response, 'identifiantAMC'),
            'reference_date'                => (int)CMbArray::get($response, 'dateReference'),
        ];

        return AdditionalHealthInsurance::hydrate($data);
    }

    public static function getArrayFromAdditionalHealthInsurance(AdditionalHealthInsurance $insurance): array
    {
        return [
            'idExterne'              => '',
            'numeroComplementaireB2' => $insurance->getNumberB2() ?? '',
            'numeroAdherent'         => $insurance->getSubscriberNumber() ?? '',
            'indicateurTraitement'   => $insurance->getTreatmentIndicator() ?? '',
            'dateDebutDroits'        => $insurance->getBeginDate() ? $insurance->getBeginDate()->format('Ymd') : '',
            'dateFinDroits'          => $insurance->getEndDate() ? $insurance->getEndDate()->format('Ymd') : '',
            'codeRoutage'            => $insurance->getRoutingCode() ?? '',
            'identifiantHote'        => $insurance->getHostId() ?? '',
            'nomDomaine'             => $insurance->getDomainName() ?? '',
            'codeAiguillageSTS'      => $insurance->getReferralStsCode() ?? '',
            'typeServices'           => $insurance->getServicesType() ?? '',
            'servicesAssocies'       => $insurance->getAssociatedServicesContract() ?? '',
            'typeContrat'            => $insurance->getContractType() ?? '',
            'pec'                    => $insurance->getPec() ?? '',
            'critereSecondaire'      => $insurance->getSecondaryCriteria() ?? '',
            'typeConvention'         => $insurance->getConventionType() ?? '',
            'attestationPapier'      => (int)$insurance->getPaperMode(),
            'forcageDroits'          => (int)$insurance->getRightsForcing(),
            'libelleInfos'           => $insurance->getLabel() ?? '',
            'typeAMC'                => $insurance->getType() ?? '',
            'identifiantAMC'         => $insurance->getId() ?? '',
            'dateReference'          => $insurance->getReferenceDate() ?? '',
        ];
    }

    private static function getArrayFromServiceAmo(AmoServicePeriod $amo_service): array
    {
        $data = [
            'code' => $amo_service->getCode()
        ];

        if ($amo_service->getBeginDate() && $amo_service->getEndDate()) {
            $data['dateDebut'] = $amo_service->getBeginDate()->format('Ymd');
            $data['dateFin'] = $amo_service->getEndDate()->format('Ymd');
        }

        return $data;
    }

    private static function getWorkAccidentsFromResponse(array $data): array
    {
        $accidents = [];
        for ($i = 1; $i <= 3; $i++) {
            $organism = CMbArray::get($data, $i === 1 ? "organismeDestinataireAT{$i}" : "organismeGestionnaireAT{$i}");
            $id = CMbArray::get($data, "identifiantAT{$i}");
            if ($organism || $id) {
                $accident = [
                    'number'   => $i,
                    'organism' => $organism
                ];

                $code = CMbArray::get($data, "codeAT{$i}");
                if ($code && $code != '') {
                    $accident['code'] = $code;
                }

                if ($id && $id != '000000000') {
                    $accident['id'] = str_pad($id, 9, '0', STR_PAD_LEFT);
                }

                $accidents[$i] = DeclaredWorkAccident::hydrate($accident);
            }
        }

        return $accidents;
    }

    /**
     * @param array $response
     *
     * @return Message[]
     */
    private static function getMessagesFromResponse(array $response): array
    {
        $messages = [];
        foreach ($response as $item) {
            $messages[] = Message::map($item);
        }

        return $messages;
    }

    /**
     * @param array $response
     *
     * @return Question[]
     */
    private static function getQuestionsFromResponse(array $response): array
    {
        $questions = [];
        foreach ($response as $item) {
            $questions[] = Question::map($item);
        }

        return $questions;
    }

    private static function getInvoiceUserInterfaceFromResponse(array $response): InvoiceUserInterface
    {
        return InvoiceUserInterface::hydrate([
            'proof_amo' => (bool)CMbArray::get($response, 'pieceJustificative', false),
            'alsace_moselle' => (bool)CMbArray::get($response, 'alsaceMoselle', false),
            'beneficiary' => (bool)CMbArray::get($response, 'bandeauBenef', false),
            'prescriber' => (bool)CMbArray::get($response, 'medecinPrescripteur', false),
            'ame' => (bool)CMbArray::get($response, 'ameBase', false),
            'maternity_exoneration' => (bool)CMbArray::get($response, 'forcageExoMaternite', false),
            'sncf' => (bool)CMbArray::get($response, 'tpSNCF', false),
            'amc_third_party_payment' => (bool)CMbArray::get($response, 'horsTPAMC', false),
            'pharmacy' => (bool)CMbArray::get($response, 'pharmacie', false),
            'care_path' => (bool)CMbArray::get($response, 'parcoursDeSoins', false),
            'ccam_acts' => (bool)CMbArray::get($response, 'lstActesCCAM', false),
            'medical_acts' => (bool)CMbArray::get($response, 'lstActesCotations', false),
            'cnda_mode' => (bool)CMbArray::get($response, 'modeCNDA', false),
            'acts_lock' => (bool)CMbArray::get($response, 'blocageActes', false),
            'amendment_27_consultation_help' => (bool)CMbArray::get($response, 'av27_aideConsultation', false),
            'amendment_27_referring_physician' => (bool)CMbArray::get($response, 'av27_medTraitant', false),
            'amendment_27_enforceable_tariff' => (bool)CMbArray::get($response, 'av27_tarifsOpposables', false),
            'recompute_clc' => (bool)CMbArray::get($response, 'relancerCalculCLC', false),
            'adri_activation' => (bool)CMbArray::get($response, 'activationADRI', false),
            'imti_activation' => (bool)CMbArray::get($response, 'activationIMTI', false),
            'amc_directory_activation' => (bool)CMbArray::get($response, 'activationAnnuaireAMC', false),
            'display_pav' => (bool)CMbArray::get($response, 'affichageEcranPav', false),
            'anonymize' => (bool)CMbArray::get($response, 'anonymisation', false),
        ]);
    }

    private static function getThirdPartyPaymentAssistantFromResponse(array $response): ThirdPartyPaymentAssistant
    {
        $conventions = [];
        foreach (CMbArray::get($response, 'lstConventionsApplicables', []) as $convention_data) {
            $conventions[] = ConventionMapper::getConventionFromResponse($convention_data);
        }

        $formulas = [];
        $messages = [];
        if (CMbArray::get($response, 'lstFormulesApplicables')) {
            $formulas = FormulaMapper::getFormulasFromResponse(CMbArray::get($response, 'lstFormulesApplicables', []));
            foreach (CMbArray::get($response['lstFormulesApplicables'], 'lstMessages') as $message) {
                $messages[] = Message::map($message);
            }
        }

        return ThirdPartyPaymentAssistant::hydrate([
            'conventions' => $conventions,
            'formulas' => $formulas,
            'messages' => $messages,
            'action' => (int)CMbArray::get($response, 'actionAttendue'),
            'choice' => (int)CMbArray::get($response, 'choix'),
            'transformation' => (bool)CMbArray::get($response, 'transformation'),
            'transformation_label' => CMbArray::get($response, 'libelleTransformation'),
            'conventions_service_message' => CMbArray::get($response, 'messageConventionsTeleservice'),
            'formulas_service_message' => CMbArray::get($response, 'messageFormulesTeleservice'),
            'idb_urls' => CMbArray::get($response, 'lstUrlsIdb'),
            'clc_urls' => CMbArray::get($response, 'lstUrlsClc'),
        ]);
    }

    protected static function getInsuredParticipationActsFromResponse(array $response): array
    {
        $acts = [];
        if (CMbArray::get($response, 'pav') && CMbArray::get($response['pav'], 'lstActesPav')) {
            foreach ($response['pav']['lstActesPav'] as $pav_act) {
                $acts[] = InsuredParticipationAct::hydrate([
                    'date' => new DateTime(CMbArray::get($pav_act, 'dateActe')),
                    'code' => CMbArray::get($pav_act, 'codeActe'),
                    'index' => (int)CMbArray::get($pav_act, 'indexActe'),
                    'add_insured_participation' => (bool)CMbArray::get($pav_act, 'ajoutPav'),
                    'amo_amount_reduction' => (bool)CMbArray::get($pav_act, 'diminutionMontantAmo'),
                    'amount' => (float)CMbArray::get($pav_act, 'montantPAV'),
                ]);
            }
        }

        return $acts;
    }

    public static function getComplementFromResponse(Response $response): ?Complement
    {
        $response = $response->getContent();

        $complement = null;
        if (count($response)) {
            $complement_acts = [];
            foreach (CMbArray::get($response, 'lstActes', []) as $act) {
                $d = CMbArray::get($act, 'date');
                $complement_acts[] = ComplementAct::hydrate([
                    'date'           => CMbArray::get($act, 'date') ?
                        new DateTime(CMbDT::dateFromLocale(CMbArray::get($act, 'date'))) : null,
                    'code'           => CMbArray::get($act, 'cotation'),
                    'total'          => (float)CMbArray::get($act, 'facture'),
                    'amo_amount'     => (float)CMbArray::get($act, 'partAMO'),
                    'patient_amount' => (float)CMbArray::get($act, 'rac'),
                ]);
            }

            $type = strpos(CMbArray::get($response, 'type', ''), 'SMG') !== false ? 'smg' : 'at';

            $complement = Complement::hydrate([
                'type'                    => $type,
                'amo_third_party_payment' => (bool)CMbArray::get($response, 'tiersPayantAMO'),
                'montantPec'              => (float)CMbArray::get($response, 'pec_amount'),
                'total'                   => (float)CMbArray::get($response, 'total'),
                'amo_total'               => (float)CMbArray::get($response, 'totalAMO'),
                'patient_total'           => (float)CMbArray::get($response, 'totalAssure'),
                'amount_owed_amo'         => (float)CMbArray::get($response, 'duAMO'),
                'acts'                    => $complement_acts
            ]);
        }

        return $complement;
    }
}
