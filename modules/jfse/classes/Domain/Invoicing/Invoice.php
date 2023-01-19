<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Invoicing;

use DateTime;
use Exception;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Jfse\Api\Message;
use Ox\Mediboard\Jfse\Api\Question;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\DataModels\CJfseUser;
use Ox\Mediboard\Jfse\Domain\InsuranceType\AbstractInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\FmfInsurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\Insurance;
use Ox\Mediboard\Jfse\Domain\InsuranceType\WorkAccidentInsurance;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalActService;
use Ox\Mediboard\Jfse\Domain\ProofAmo\ProofAmo;
use Ox\Mediboard\Jfse\Domain\UserManagement\User;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\CarePath\CarePath;
use Ox\Mediboard\Jfse\Domain\MedicalAct\MedicalAct;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvService;
use Ox\Mediboard\Jfse\Domain\Vital\Beneficiary;
use Ox\Mediboard\Jfse\Domain\Vital\Insured;
use Ox\Mediboard\Jfse\Domain\Vital\VitalCard;
use Ox\Mediboard\Jfse\Exceptions\VitalException;
use Ox\Mediboard\Jfse\Exceptions\Invoice\InvoiceException;
use Ox\Mediboard\Jfse\Mappers\MedicalActMapper;
use Ox\Mediboard\Jfse\Mappers\VitalCardMapper;
use Ox\Mediboard\Patients\CPatient;

final class Invoice extends AbstractEntity
{
    /** @var string */
    protected $id;

    /** @var SecuringModeEnum */
    protected $securing;

    /** @var bool */
    protected $alsace_moselle;

    /** @var DateTime */
    protected $creation_date;

    /** @var int */
    protected $integrator_id;

    /** @var bool */
    protected $delay_transmission;

    /** @var AnonymizationEnum */
    protected $anonymize;

    /** @var bool */
    protected $paper_mode;

    /** @var int */
    protected $fsp_mode;

    /** @var float */
    protected $total_amount;

    /** @var float */
    protected $total_insured;

    /** @var float */
    protected $total_amo;

    /** @var float */
    protected $total_amc;

    /** @var int */
    protected $invoice_number;

    /** @var float */
    protected $amount_owed_amo;

    /** @var float */
    protected $amount_owed_amc;

    /** @var TreatmentTypeEnum */
    protected $treatment_type;

    /** @var bool */
    protected $forcing_amo;

    /** @var bool */
    protected $forcing_amc;

    /** @var float */
    protected $c2s_maximum_amount;

    /** @var string */
    protected $amo_right_status;

    /** @var bool */
    protected $check_vital_card;

    /** @var float Only use in FSP mode */
    protected $global_rate;

    /** @var int */
    protected $correct_or_recycle;

    /** @var bool Only used in the initialization of the invoice */
    protected $beneficiary_banner_display;

    /** @var bool Only used in the initialization of the invoice */
    protected $automatic_deletion;

    /** @var bool Only used in the initialization of the invoice */
    protected $sts_disabled;

    /** @var int Only used in the initialization of the invoice */
    protected $template_id;

    /** @var RuleForcing */
    protected $rule_forcing;

    /** @var bool */
    protected $invoice_complements;

    /** @var CarePath */
    protected $care_path;

    /** @var CommonLawAccident */
    protected $common_law_accident;

    /** @var Insurance */
    protected $insurance;

    /** @var MedicalAct[] */
    protected $medical_acts;

    /** @var ProofAmo */
    protected $proof_amo;

    /** @var Prescription */
    protected $prescription;

    /** @var ComplementaryHealthInsurance */
    protected $complementary_health_insurance;

    /** @var Beneficiary */
    protected $beneficiary;

    /** @var Insured */
    protected $insured;

    /** @var User */
    protected $practitioner;

    /** @var InsuredParticipationAct[] */
    protected $insured_participation_acts;

    /** @var bool */
    protected $adri;

    /** @var Message[] */
    protected $messages;

    /** @var Question[] */
    protected $questions;

    /** @var InvoiceUserInterface */
    protected $user_interface;

    /** @var CJfseInvoice */
    protected $data_model;

    /** @var CJfseUser */
    protected $user_data_model;

    /** @var CJfsePatient */
    protected $patient_data_model;

    /** @var CConsultation */
    protected $consultation;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return SecuringModeEnum
     */
    public function getSecuring(): ?SecuringModeEnum
    {
        return $this->securing;
    }

    /**
     * @param ?SecuringModeEnum $securing
     *
     * @return static
     */
    public function setSecuring(?SecuringModeEnum $securing = null): self
    {
        $this->securing = $securing;
        $this->setCheckVitalCard();
        return $this;
    }

    /**
     * @return bool
     */
    public function getAlsaceMoselle(): ?bool
    {
        return $this->alsace_moselle;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate(): ?DateTime
    {
        return $this->creation_date;
    }

    /**
     * @return int
     */
    public function getIntegratorId(): ?int
    {
        return $this->integrator_id;
    }

    /**
     * @return bool
     */
    public function getDelayTransmission(): ?bool
    {
        return $this->delay_transmission;
    }

    /**
     * @return AnonymizationEnum
     */
    public function getAnonymize(): ?AnonymizationEnum
    {
        return $this->anonymize;
    }

    /**
     * @return bool
     */
    public function getPaperMode(): ?bool
    {
        return $this->paper_mode;
    }

    /**
     * @return int
     */
    public function getFspMode(): ?int
    {
        return $this->fsp_mode;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): ?float
    {
        return $this->total_amount;
    }

    /**
     * @return float
     */
    public function getTotalInsured(): ?float
    {
        return $this->total_insured;
    }

    /**
     * @return float
     */
    public function getTotalAmo(): ?float
    {
        return $this->total_amo;
    }

    /**
     * @return float
     */
    public function getTotalAmc(): ?float
    {
        return $this->total_amc;
    }

    /**
     * @return int
     */
    public function getInvoiceNumber(): ?int
    {
        return $this->invoice_number;
    }

    /**
     * @return float
     */
    public function getAmountOwedAmo(): ?float
    {
        return $this->amount_owed_amo;
    }

    /**
     * @return float
     */
    public function getAmountOwedAmc(): ?float
    {
        return $this->amount_owed_amc;
    }

    /**
     * @return TreatmentTypeEnum
     */
    public function getTreatmentType(): ?TreatmentTypeEnum
    {
        return $this->treatment_type;
    }

    /**
     * @return bool
     */
    public function getForcingAmo(): ?bool
    {
        return $this->forcing_amo;
    }

    /**
     * @return bool
     */
    public function getForcingAmc(): ?bool
    {
        return $this->forcing_amc;
    }

    /**
     * @return float
     */
    public function getC2sMaximumAmount(): ?float
    {
        return $this->c2s_maximum_amount;
    }

    /**
     * @return string
     */
    public function getAmoRightStatus(): ?string
    {
        return $this->amo_right_status;
    }

    /**
     * @return bool
     */
    public function getCheckVitalCard(): ?bool
    {
        return $this->check_vital_card;
    }

    /**
     * @return self
     */
    public function setCheckVitalCard(): self
    {
        switch ($this->securing) {
            case SecuringModeEnum::DEGRADED():
            case SecuringModeEnum::CARDLESS():
                $this->check_vital_card = false;
                break;
            case SecuringModeEnum::DESYNCHRONIZED():
            case SecuringModeEnum::SECURED():
                $this->check_vital_card = true;
                break;
            default:
                $this->check_vital_card = null;
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getGlobalRate(): ?float
    {
        return $this->global_rate;
    }

    /**
     * @return int
     */
    public function getCorrectOrRecycle(): ?int
    {
        return $this->correct_or_recycle;
    }

    /**
     * @return bool
     */
    public function getInvoiceComplements(): bool
    {
        return $this->invoice_complements;
    }

    /**
     * @return CarePath
     */
    public function getCarePath(): ?CarePath
    {
        return $this->care_path;
    }

    /**
     * @return CommonLawAccident
     */
    public function getCommonLawAccident(): ?CommonLawAccident
    {
        return $this->common_law_accident;
    }

    /**
     * @return Insurance
     */
    public function getInsurance(): ?Insurance
    {
        return $this->insurance;
    }

    /**
     * @return MedicalAct[]
     */
    public function getMedicalActs(): ?array
    {
        return $this->medical_acts;
    }

    public function getMedicalAct(string $id): ?MedicalAct
    {
        $act = null;
        foreach ($this->medical_acts as $_medical_act) {
            if ($_medical_act->getId() === $id) {
                $act = $_medical_act;
                break;
            }
        }

        return $act;
    }

    /**
     * @return Prescription
     */
    public function getPrescription(): ?Prescription
    {
        return $this->prescription;
    }

    /**
     * @return ComplementaryHealthInsurance
     */
    public function getComplementaryHealthInsurance(): ?ComplementaryHealthInsurance
    {
        return $this->complementary_health_insurance;
    }

    /**
     * @return Beneficiary
     */
    public function getBeneficiary(): ?Beneficiary
    {
        return $this->beneficiary;
    }

    /**
     * @return Insured
     */
    public function getInsured(): ?Insured
    {
        return $this->insured;
    }

    /**
     * @return User
     */
    public function getPractitioner(): ?User
    {
        return $this->practitioner;
    }

    /**
     * @return self
     */
    public function setPractitioner(User $user): ?self
    {
        $this->practitioner = $user;
        return $this;
    }

    /**
     * @return bool
     */
    public function getBeneficiaryBannerDisplay(): ?bool
    {
        return $this->beneficiary_banner_display;
    }

    /**
     * @param bool $display
     *
     * @return self
     */
    public function setBeneficiaryBannerDisplay(bool $display): self
    {
        $this->beneficiary_banner_display = $display;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAutomaticDeletion(): ?bool
    {
        return $this->automatic_deletion;
    }

    /**
     * @param bool $automatic_deletetion
     *
     * @return self
     */
    public function setAutomaticDeletion(bool $automatic_deletetion): self
    {
        $this->automatic_deletion = $automatic_deletetion;
        return $this;
    }

    /**
     * @return bool
     */
    public function getStsDisabled(): ?bool
    {
        return $this->sts_disabled;
    }

    /**
     * @param bool $sts_disabled
     *
     * @return self
     */
    public function setStsDisabled(bool $sts_disabled): self
    {
        $this->sts_disabled = $sts_disabled;
        return $this;
    }

    /**
     * @return int
     */
    public function getTemplateId(): ?int
    {
        return $this->template_id;
    }

    /**
     * @param int $template_id
     *
     * @return self
     */
    public function setTemplateId(int $template_id): self
    {
        $this->template_id = $template_id;
        return $this;
    }

    /**
     * @return CJfseInvoice
     */
    public function getDataModel(): CJfseInvoice
    {
        return $this->data_model;
    }

    /**
     * @return RuleForcing
     */
    public function getRuleForcing(): ?RuleForcing
    {
        return $this->rule_forcing;
    }

    /**
     * @return bool
     */
    public function hasRuleForcing(): bool
    {
        return $this->rule_forcing && $this->rule_forcing instanceof RuleForcing;
    }

    /**
     * @param RuleForcing $rule_forcing
     *
     * @return self
     */
    public function setRuleForcing(RuleForcing $rule_forcing): self
    {
        $this->rule_forcing = $rule_forcing;
        return $this;
    }

    public function addMessage(Message $message): self
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return Message[]
     */
    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function hasMessageWithTypeId(string $type_id): bool
    {
        $has_message = false;
        foreach ($this->messages as $message) {
            if ($message->getTypeId() === $type_id) {
                $has_message = true;
                break;
            }
        }

        return $has_message;
    }

    /**
     * @return Question[]
     */
    public function getQuestions(): ?array
    {
        return $this->questions;
    }

    /**
     * @return bool
     */
    public function hasQuestions(): bool
    {
        return is_array($this->questions) && count($this->questions) > 0;
    }

    /**
     * Returns the question that matches the given keyword, or null otherwise
     *
     * @param string $keyword
     *
     * @return Question|null
     */
    public function getQuestionWithKeyword(string $keyword): ?Question
    {
        $result = null;

        foreach ($this->questions as $question) {
            if (strpos(strtolower($question->getQuestion()), strtolower($keyword)) >= 0) {
                $result = $question;
                break;
            }
        }

        return $result;
    }

    /**
     * @return InvoiceUserInterface
     */
    public function getUserInterface(): ?InvoiceUserInterface
    {
        return $this->user_interface;
    }

    /**
     * @return ProofAmo|null
     */
    public function getProofAmo(): ?ProofAmo
    {
        return $this->proof_amo;
    }

    /**
     * @return InsuredParticipationAct[]
     */
    public function getInsuredParticipationActs(): array
    {
        return $this->insured_participation_acts;
    }

    /**
     * @return bool
     */
    public function getAdri(): bool
    {
        return $this->adri;
    }

    /**
     * @return CConsultation
     */
    public function getConsultation(): ?CConsultation
    {
        return $this->consultation;
    }

    /**
     * @param CConsultation $consultation
     *
     * @return self
     */
    public function setConsultation(CConsultation $consultation): self
    {
        $this->consultation = $consultation;
        $this->creation_date = new DateTime($this->consultation->_date);

        if ($jfse_user = CJfseUser::getFromMediuser($this->consultation->loadRefPraticien())) {
            $this->user_data_model = $jfse_user;
        }

        if ($jfse_patient = CJfsePatient::getFromPatient($this->consultation->loadRefPatient())) {
            $this->patient_data_model = $jfse_patient;
        }

        $this->setLongLastingAffectionQuestionFromConsultation();

        return $this;
    }

    /**
     * @return CJfsePatient|null
     */
    public function getPatientDataModel(): ?CJfsePatient
    {
        return $this->patient_data_model;
    }

    /**
     * Loads the data object linked to the Jfse user's id
     *
     * @return CJfseInvoice
     *
     * @throws InvoiceException
     */
    public function loadDataModel(): CJfseInvoice
    {
        if (!$this->data_model || !$this->data_model->_id) {
            $this->data_model          = new CJfseInvoice();
            $this->data_model->jfse_id = $this->id;

            try {
                $this->data_model->loadMatchingObjectEsc();
            } catch (Exception $e) {
                throw InvoiceException::persistenceError($e->getMessage(), $e);
            }
        }

        return $this->data_model;
    }

    /**
     * Stores the given data object.
     *
     * Throws a UserExceptions in case of error
     *
     * @return bool
     *
     * @throws InvoiceException
     */
    protected function storeDataModel(): bool
    {
        try {
            if ($error = $this->data_model->store()) {
                throw InvoiceException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw InvoiceException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }

    /**
     * Creates the data model object, set the jfse user id and stores it
     *
     * @return true
     *
     * @throws Exception
     */
    public function createDataModel(): bool
    {
        $result = false;
        if ($this->consultation instanceof CConsultation && $this->consultation->_id) {
            $this->data_model = new CJfseInvoice();
            $this->data_model->setConsultationId($this->consultation->_id);

            if ($this->id) {
                $this->data_model->jfse_id = $this->id;
            }

            if ($this->user_data_model) {
                $this->data_model->jfse_user_id = $this->user_data_model->_id;

                if ($this->patient_data_model) {
                    $this->data_model->jfse_patient_id = $this->patient_data_model->_id;
                }

                $result =  $this->storeDataModel();
                if ($result) {
                    $this->integrator_id = $this->data_model->_id;
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     * @throws InvoiceException
     */
    public function deleteDataModel(): bool
    {
        $this->loadDataModel();

        try {
            if ($error = $this->data_model->delete()) {
                throw InvoiceException::persistenceError($error);
            }
        } catch (Exception $e) {
            throw InvoiceException::persistenceError($e->getMessage(), $e);
        }

        return true;
    }

    /**
     * Pass the status of the data model as validated
     *
     * @return bool
     *
     * @throws Exception
     */
    public function validate(): bool
    {
        $this->loadDataModel();
        $this->data_model->status = 'validated';
        $this->data_model->invoice_number = $this->invoice_number;
        return $this->storeDataModel();
    }

    public function setDefaultSecuringMode(): self
    {
        if ($this->consultation) {
            $user = CJfseUser::getFromMediuser($this->consultation->loadRefPraticien());

            $this->securing = new SecuringModeEnum(intval($user->securing_mode));
            if (!CJfsePatient::isPatientLinked($this->consultation->loadRefPatient())) {
                $this->securing = SecuringModeEnum::DEGRADED();
                $this->check_vital_card = false;
            } elseif (
                $this->consultation->teleconsultation
                && $this->securing->getValue() === SecuringModeEnum::SECURED()->getValue()
            ) {
                $this->securing = SecuringModeEnum::CARDLESS();
                $this->check_vital_card = false;
            } elseif (
                $this->consultation->teleconsultation
                && $this->securing->getValue() === SecuringModeEnum::DESYNCHRONIZED()->getValue()
            ) {
                $this->securing = SecuringModeEnum::UNSECURED();
            }
        } else {
            throw InvoiceException::consultationNotSet();
        }

        $this->setCheckVitalCard();

        return $this;
    }

    public function setMedicalActsFromConsultation(): self
    {
        $this->medical_acts = [];
        if ($this->consultation) {
            $this->consultation->loadRefsActes();

            foreach ($this->consultation->_ref_actes as $act) {
                if (!$act->countBackRefs('jfse_act_link') && MedicalActService::checkSendAct($act)) {
                    $this->medical_acts[] = MedicalActMapper::medicalActFromCActe($act);
                }
            }
        } else {
            throw InvoiceException::consultationNotSet();
        }

        return $this;
    }

    public function setBeneficiaryFromPatient(
        CPatient $patient,
        string $situation_code = null,
        string $vitale_nir = null
    ): self {
        if ($patient->countBackRefs('jfse_patient')) {
            $this->patient_data_model = CJfsePatient::getFromPatient($patient, $vitale_nir);
            $this->beneficiary = VitalCardMapper::getBeneficiaryFromPatientDataModel(
                $this->patient_data_model,
                $this->areAmoInformationsNeeded(),
                $situation_code
            );
        } else {
            $this->beneficiary = VitalCardMapper::getBeneficiaryFromPatient(
                $patient,
                $this->areAmoInformationsNeeded(),
                $situation_code
            );
        }

        if ($patient->regime_am == '1' && $this->securing->getValue() === SecuringModeEnum::DEGRADED()->getValue()) {
            $this->alsace_moselle = true;
        }

        return $this;
    }

    public function setBeneficiaryFromApCv(CPatient $patient, VitalCard $vital_card): self
    {
        /** @var CJfsePatient $patient_link */
        $patient_link = $patient->loadUniqueBackRef('jfse_patient');
        $this->patient_data_model = $patient_link;

        /* If the real Vital Card of the patient hasn't been read, he may not have a CJfsePatient object */
        if ($patient_link && $patient_link->_id) {
            $birth_date = $patient_link->birth_date;
            $birth_rank = $patient_link->birth_rank;
            $quality = $patient_link->quality;
            $managing_code = $patient_link->amo_managing_code;
        } else {
            $birth_date = $patient->naissance;
            $birth_rank = $patient->rang_naissance;
            $quality = $patient->qual_beneficiaire;
            $managing_code = $patient->code_gestion;
        }

        $beneficiary = $vital_card->getSelectedBeneficiary($birth_date, $birth_rank, $quality);
        /* We set the managing code because it is not provided by the ApCV  */
        $vital_card->getInsured()->setManagingCode($managing_code);

        $beneficiary->setApCvContext($vital_card->getApcvContext());

        if (is_null($beneficiary)) {
            throw VitalException::unknownBeneficiary();
        }

        $this->beneficiary = $beneficiary;
        $this->check_vital_card = false;

        return $this;
    }

    public function changeSecuringMode(SecuringModeEnum $mode): self
    {
        $this->securing = $mode;
        $this->id = null;
        $this->fsp_mode = null;
        if (
            in_array(
                $mode->getValue(),
                [SecuringModeEnum::CARDLESS()->getValue(), SecuringModeEnum::DEGRADED()->getValue()]
            )
        ) {
            $this->check_vital_card = false;
        }

        return $this;
    }

    /**
     * Checks if the AMO informations must be set in the beneficiary entity by checking the securing mode
     *
     * @return bool
     */
    public function areAmoInformationsNeeded(): bool
    {
        return $this->securing->getValue() === SecuringModeEnum::DEGRADED()->getValue()
            || $this->securing->getValue() === SecuringModeEnum::CARDLESS()->getValue()
            || $this->securing->getValue() === SecuringModeEnum::UNSECURED()->getValue();
    }

    /**
     * Check if a complement must be added depending on the s
     *
     * @return bool
     */
    public function isComplementsNeeded(): bool
    {
        $complement = false;
        if (
            $this->complementary_health_insurance->getThirdPartyAmo()
            && ($this->complementary_health_insurance->getAttackVictim()
            || $this->insurance->getSelectedInsuranceType() === WorkAccidentInsurance::CODE
            || $this->insurance->getSelectedInsuranceType() === FmfInsurance::CODE)
        ) {
            $complement_added = false;

            foreach ($this->medical_acts as $act) {
                if (in_array($act->getActCode(), MedicalAct::$complement_list)) {
                    $complement_added = true;
                    break;
                }
            }

            if (!$complement_added) {
                $complement = true;
            }
        }

        return $complement;
    }

    /**
     * If the consultation has a link with a long term disease of the patient, and if there is a question about it,
     * the default answer is set to 1
     *
     * @return void
     */
    public function setLongLastingAffectionQuestionFromConsultation(): void
    {
        if ($this->data_model && !$this->data_model->isValidated() && $this->hasQuestions()) {
            if (!is_null($this->consultation->concerne_ALD) && $question = $this->getQuestionWithKeyword('ald')) {
                $question->setAnswer($this->consultation->concerne_ALD);
            }
        }
    }

    /**
     * Set the concerne_ALD field of the linked consultation
     *
     * @param bool $long_lasting_affliction
     *
     * @throws Exception
     */
    public function setLongLastingAffliction(bool $long_lasting_affliction): void
    {
        $this->loadDataModel();
        $consultation = $this->data_model->loadConsultation();

        $consultation->concerne_ALD = $long_lasting_affliction ? '1' : '0';
        $consultation->store();
    }

    /**
     * Set the concerne_ALD field of the linked consultation
     *
     * @param bool $free_medical_care
     *
     * @throws Exception
     */
    public function setFreeMedicalCare(bool $free_medical_care): void
    {
        $this->loadDataModel();
        $consultation = $this->data_model->loadConsultation();

        $consultation->type_assurance = $free_medical_care ? 'smg' : '';
        $consultation->store();
    }

    public function isApCv(): bool
    {
        return $this->beneficiary instanceof Beneficiary && $this->beneficiary->getApcv();
    }

    public function isApCvContextExpired(): bool
    {
        $is_expired = false;

        if ($this->beneficiary instanceof Beneficiary && $this->beneficiary->getApcvContext()) {
            $is_expired = $this->beneficiary->getApcvContext()->isExpired();
        }

        return $is_expired;
    }
}
